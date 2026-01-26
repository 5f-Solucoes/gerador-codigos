<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // Mantenha, mas vamos customizar a lógica
use App\Services\WatchGuardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    // Injetamos o Service aqui
    public function store(Request $request, WatchGuardService $watchGuard)
    {
        // 1. Validar inputs básicos
        $request->validate([
            'login_identifier' => 'required|string', // Aceita username ou email
            'senha' => 'required|string',
        ]);

        $login = $request->input('login_identifier');
        $password = $request->input('senha');

        // 2. Buscar usuário no banco (Lógica do seu legado: Username OU Email)
        $user = User::where('username', $login)
                    ->orWhere('email', $login)
                    ->first();

        // 3. Validar Senha Local
        if (! $user || ! Hash::check($password, $user->password)) {
            // Retorna erro JSON para o Javascript
            return response()->json([
                'message' => 'Credenciais inválidas no sistema local.'
            ], 401);
        }

        // 4. Iniciar WatchGuard Push
        // Armazena ID temporário na sessão para o MfaController usar depois
        Session::put('pending_user_id', $user->id); 
        
        $resultado = $watchGuard->iniciarTransacaoPush($user, $password);

        if (! $resultado['success']) {
            return response()->json([
                'message' => $resultado['error']
            ], 401);
        }

        // 5. Retorna o ID da transação para o JavaScript iniciar o polling
        return response()->json([
            'flow' => 'push',
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