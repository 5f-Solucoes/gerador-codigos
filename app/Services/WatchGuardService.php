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
        // Carrega as configurações do config/services.php
        $this->accessId = config('services.watchguard.access_id');
        $this->clientSecret = config('services.watchguard.client_secret');
        $this->authUrl = config('services.watchguard.auth_url');
        $this->apiBase = config('services.watchguard.api_base');
        $this->apiKey = config('services.watchguard.api_key');
        $this->accountId = config('services.watchguard.account_id');
        $this->resourceId = config('services.watchguard.resource_id');
    }

    /**
     * Inicia a transação de PUSH (Antigo authenticate_wg.php)
     */
    public function iniciarTransacaoPush(User $user, string $password)
    {
        $endpoint = "/accounts/{$this->accountId}/resources/{$this->resourceId}/transactions";

        $body = [
            'login' => $user->username,
            'type' => 'PUSH',
            'password' => $password,
            'originIpAddress' => request()->ip()
        ];

        try {
            $response = $this->callApi('POST', $endpoint, $body);

            // Verifica erro HTTP
            if ($response['status'] < 200 || $response['status'] >= 300) {
                Log::warning("WatchGuard Auth Failed para {$user->username}: " . json_encode($response));
                return [
                    'success' => false,
                    'error' => $response['body']['error_description'] ?? $response['body']['error'] ?? 'Falha na autenticação MFA.'
                ];
            }

            // Sincroniza senha local se necessário (Feature do seu código antigo)
            if (!Hash::check($password, $user->password)) {
                $user->password = Hash::make($password);
                $user->save();
                Log::info("Senha sincronizada via WatchGuard para: {$user->username}");
            }

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

    /**
     * Obtém o Token de Acesso com Cache Automático (Antigo get_wg_token)
     */
    protected function getToken()
    {
        // Cache::remember faz a mágica:
        // 1. Procura a chave 'wg_access_token'.
        // 2. Se achar, retorna ela.
        // 3. Se NÃO achar, executa a função, pega o token novo, salva no cache por 3500 segundos e retorna.
        return Cache::remember('wg_access_token', 3500, function () {
            
            $response = Http::withBasicAuth($this->accessId, $this->clientSecret)
                ->asForm() // 'grant_type=client_credentials...'
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

    /**
     * Realiza a chamada HTTP para a API (Antigo wg_api_call)
     */
    protected function callApi($method, $path, $body = null)
    {
        $token = $this->getToken();

        // Monta a URL baseada na lógica do seu helper antigo
        $baseUrl = rtrim($this->apiBase, '/');
        $url = $baseUrl . '/authpoint/authentication/v1' . $path;

        // Configura a requisição
        $request = Http::withToken($token)
            ->withHeaders([
                'WatchGuard-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'Dev.5F.AuthPointClient/1.0'
            ])
            ->timeout(15);

        // Executa (GET, POST, etc)
        if (strtoupper($method) === 'POST') {
            $response = $request->post($url, $body);
        } elseif (strtoupper($method) === 'GET') {
            $response = $request->get($url, $body);
        } else {
            // Outros métodos se necessário
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

            // Se for 202 (Accepted), ainda está processando
            if ($response['status'] == 202) {
                return 'PENDING';
            }

            // Se der erro HTTP
            if ($response['status'] < 200 || $response['status'] >= 300) {
                Log::warning("Erro ao verificar status MFA para TxID {$txId}: " . $response['raw']);
                return 'ERROR';
            }

            $body = $response['body'] ?? [];

            // Lógica de normalização de status (Cópia fiel do seu código legado)
            $statusCandidates = [];
            if (isset($body['status'])) $statusCandidates[] = $body['status'];
            if (isset($body['pushResult'])) $statusCandidates[] = $body['pushResult'];
            if (isset($body['authenticationResult'])) $statusCandidates[] = $body['authenticationResult'];
            if (isset($body['result'])) $statusCandidates[] = $body['result'];
            if (isset($body['transaction']['status'])) $statusCandidates[] = $body['transaction']['status'];

            $resultStatus = 'PENDING';
            foreach ($statusCandidates as $c) {
                if ($c === null) continue;
                if (is_string($c) && trim($c) !== '') {
                    $resultStatus = $c;
                    break;
                }
                if (is_array($c) && isset($c['status'])) {
                    $resultStatus = $c['status'];
                    break;
                }
            }

            $resultNorm = strtoupper(trim($resultStatus));

            // Mapeia para status simplificado
            if (in_array($resultNorm, ['AUTHORIZED', 'AUTHORISED', 'SUCCESS', 'OK'])) {
                return 'AUTHORIZED';
            }

            if (in_array($resultNorm, ['DENIED', 'FAILED', 'UNAUTHORIZED', 'REJECTED'])) {
                return 'DENIED';
            }

            return 'PENDING';

        } catch (Exception $e) {
            Log::error("Erro no verificarStatusTransacao: " . $e->getMessage());
            return 'ERROR';
        }
    }
}