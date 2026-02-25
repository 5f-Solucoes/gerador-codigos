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
    
    public function checkStatus(Request $request, $transactionId)
    {
        if (!Session::has('auth.password_confirmed_at') && !Session::has('pending_user_id')) {
             return response()->json(['status' => 'ERROR', 'message' => 'Sessão expirada.']);
        }

        // Chama o Service
        $status = $this->watchGuard->verificarStatusTransacao($transactionId);

        if ($status === 'AUTHORIZED') {
            
            // Efetua o Login Oficial no Laravel
            $userId = Session::get('pending_user_id');
            Auth::loginUsingId($userId);
            
            // Limpa a flag temporária
            Session::forget('pending_user_id');
            Session::regenerate();

            $user = Auth::user();
            session([
                'ID_USUARIO' => $user->id,
                'NOME' => $user->name, 
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