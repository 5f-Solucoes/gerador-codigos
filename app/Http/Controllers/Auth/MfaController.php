<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WatchGuardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MfaController extends Controller
{
    protected $watchGuard;

    public function __construct(WatchGuardService $watchGuard)
    {
        $this->watchGuard = $watchGuard;
    }

    /**
     * Endpoint chamado via AJAX pelo frontend para checar o status
     */
    public function checkStatus(Request $request, $transactionId)
    {
        // 1. Segurança: Verifica se existe um usuário pendente na sessão
        // (Isso foi setado lá no AuthenticatedSessionController no passo anterior)
        if (!Session::has('auth.password_confirmed_at') && !Session::has('pending_user_id')) {
             // Ajuste conforme sua lógica de login inicial
             return response()->json(['status' => 'ERROR', 'message' => 'Sessão expirada.']);
        }

        // 2. Chama o Service
        $status = $this->watchGuard->verificarStatusTransacao($transactionId);

        if ($status === 'AUTHORIZED') {
            
            // 3. Efetua o Login Oficial no Laravel
            $userId = Session::get('pending_user_id');
            Auth::loginUsingId($userId);
            
            // Limpa a flag temporária
            Session::forget('pending_user_id');
            Session::regenerate();

            // 4. (Opcional) Popula variáveis de sessão LEGADAS
            // Se o seu Dashboard antigo depende de $_SESSION['NOME'], faça isso:
            $user = Auth::user();
            session([
                'ID_USUARIO' => $user->id,
                'NOME' => $user->name, // ou name se migrou o banco
                'SIGLA' => $user->sigla,
                'PERFIL' => $user->perfil,
                'EMAIL' => $user->email,
                'USERNAME' => $user->username
            ]);

            return response()->json(['status' => 'AUTHORIZED', 'redirect' => route('dashboard')]);
        }

        if ($status === 'DENIED') {
            Session::forget('pending_user_id');
            return response()->json(['status' => 'DENIED']);
        }

        return response()->json(['status' => 'PENDING']);
    }
}