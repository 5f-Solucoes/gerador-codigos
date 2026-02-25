<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;

class WatchGuardService
{
    protected $accessId;
    protected $clientSecret;
    protected $authUrl;
    protected $apiBase;
    protected $apiKey;
    protected $accountId;
    protected $resourceId;

    public function __construct()
    {
        $this->accessId = config('services.watchguard.access_id');
        $this->clientSecret = config('services.watchguard.client_secret');
        $this->authUrl = config('services.watchguard.auth_url');
        $this->apiBase = config('services.watchguard.api_base');
        $this->apiKey = config('services.watchguard.api_key');
        $this->accountId = config('services.watchguard.account_id');
        $this->resourceId = config('services.watchguard.resource_id');
    }

    public function iniciarTransacaoPush(User $user, string $password)
    {
        $endpoint = "/accounts/{$this->accountId}/resources/{$this->resourceId}/transactions";

        // 1. Tenta pegar o IP real igual ao código antigo (fallback para $_SERVER)
        $clientIp = request()->ip();
        if ($clientIp == '127.0.0.1' && isset($_SERVER['REMOTE_ADDR'])) {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }

        // Garante que enviamos exatamente o username do banco
        $body = [
            'login' => $user->username, 
            'type' => 'PUSH',
            'password' => $password,
            'originIpAddress' => $clientIp ?? null
        ];

        // Log para debug (igual ao antigo)
        Log::info("WG Auth Iniciando para {$user->username}. IP enviado: {$clientIp}");

        try {
            $response = $this->callApi('POST', $endpoint, $body);

            // Se der erro (401, 403, etc)
            if ($response['status'] < 200 || $response['status'] >= 300) {
                Log::warning("WatchGuard Recusou (HTTP {$response['status']}): " . json_encode($response['body']));
                
                return [
                    'success' => false,
                    'error' => $response['body']['error_description'] ?? $response['body']['error'] ?? 'Falha na autenticação MFA.',
                    'wg_status' => $response['status']
                ];
            }

            // =================================================================
            // SINCRONIZAÇÃO DE SENHA (Igual ao sistema antigo)
            // =================================================================
            // Se chegamos aqui, a WatchGuard disse "Sim, a senha é essa" (Status 200/201)
            // Agora verificamos se o banco local está desatualizado.
            
            if (!Hash::check($password, $user->password)) {
                Log::info("Senha do AD difere da local. Sincronizando para: {$user->username}...");
                
                try {
                    // forceFill ignora o $fillable (proteção de massa)
                    // Hash::make gera o BCrypt compatível com o sistema novo
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();
                    
                    Log::info("Senha sincronizada com sucesso!");
                } catch (Exception $eSync) {
                    Log::error("ERRO AO ATUALIZAR SENHA NO BANCO: " . $eSync->getMessage());
                    // Não paramos o login, pois a WatchGuard já autorizou
                }
            }
            // =================================================================

            $txBody = $response['body'] ?? [];
            $txId = $txBody['transactionId'] ?? $txBody['id'] ?? null;

            return [
                'success' => true,
                'transactionId' => $txId,
                'details' => $txBody
            ];

        } catch (Exception $e) {
            Log::error("Erro no iniciarTransacaoPush: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno de comunicação com MFA.'];
        }
    }

    protected function getToken()
    {
        return Cache::remember('wg_access_token', 3500, function () {
            
            $response = Http::withBasicAuth($this->accessId, $this->clientSecret)
                ->asForm() 
                ->withUserAgent('Dev.5F.AuthPointClient/1.0')
                ->post($this->authUrl, [
                    'grant_type' => 'client_credentials',
                    'scope' => 'api-access'
                ]);

            if (!$response->successful()) {
                throw new Exception("Falha ao obter Token WatchGuard: " . $response->body());
            }

            $json = $response->json();
            return $json['access_token'];
        });
    }

    protected function callApi($method, $path, $body = null)
    {
        $token = $this->getToken();

        $baseUrl = rtrim($this->apiBase, '/');
        $url = $baseUrl . '/authpoint/authentication/v1' . $path;

        $request = Http::withToken($token)
            ->withHeaders([
                'WatchGuard-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'Dev.5F.AuthPointClient/1.0'
            ])
            ->timeout(15);

        if (strtoupper($method) === 'POST') {
            $response = $request->post($url, $body);
        } elseif (strtoupper($method) === 'GET') {
            $response = $request->get($url, $body);
        } else {
            $response = $request->send($method, $url, ['json' => $body]);
        }

        return [
            'status' => $response->status(),
            'body' => $response->json(),
            'raw' => $response->body()
        ];
    }

    public function verificarStatusTransacao($txId)
    {
        $endpoint = "/accounts/{$this->accountId}/resources/{$this->resourceId}/transactions/{$txId}";

        try {
            $response = $this->callApi('GET', $endpoint);

            if ($response['status'] == 202) return 'PENDING';

            if ($response['status'] < 200 || $response['status'] >= 300) {
                return 'ERROR';
            }

            $body = $response['body'] ?? [];
            
            // Lógica de varredura de status (igual ao que já funcionava)
            $statusCandidates = [
                $body['status'] ?? null,
                $body['pushResult'] ?? null,
                $body['authenticationResult'] ?? null,
                $body['result'] ?? null,
                $body['transaction']['status'] ?? null
            ];

            $resultStatus = 'PENDING';
            foreach ($statusCandidates as $c) {
                if (!empty($c) && is_string($c)) {
                    $resultStatus = $c;
                    break;
                }
                if (is_array($c) && isset($c['status'])) {
                    $resultStatus = $c['status'];
                    break;
                }
            }

            $resultNorm = strtoupper(trim($resultStatus));

            if (in_array($resultNorm, ['AUTHORIZED', 'AUTHORISED', 'SUCCESS', 'OK'])) return 'AUTHORIZED';
            if (in_array($resultNorm, ['DENIED', 'FAILED', 'UNAUTHORIZED', 'REJECTED'])) return 'DENIED';

            return 'PENDING';

        } catch (Exception $e) {
            return 'ERROR';
        }
    }
}