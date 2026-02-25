<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Services\WatchGuardService; 

class AuthenticatedSessionController extends Controller
{
    protected $watchGuardService;

    public function __construct(WatchGuardService $watchGuardService)
    {
        $this->watchGuardService = $watchGuardService;
    }

    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $passwordField = $request->has('senha') ? 'senha' : 'password';

        $request->validate([
            'login_identifier' => 'required|string',
            $passwordField => 'required|string',
        ]);

        $loginValue = $request->input('login_identifier');
        $passwordValue = $request->input($passwordField);

        $user = User::where('username', $loginValue)
                    ->orWhere('email', $loginValue)
                    ->first();

        if (! $user || $user->status !== 'ATIVO') {
            return response()->json([
                'message' => 'Credenciais inválidas ou usuário inativo.'
            ], 422);
        }

        $resultado = $this->watchGuardService->iniciarTransacaoPush($user, $passwordValue);

        if (!$resultado['success']) {
            return response()->json([
                'message' => 'Erro MFA: ' . ($resultado['error'] ?? 'Falha na autenticação.')
            ], 422);
        }

        Session::forget('auth.password_confirmed_at');
        Session::flush(); 
        Session::put('pending_user_id', $user->id);
        
        return response()->json([
            'success' => true,
            'transactionId' => $resultado['transactionId']
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}