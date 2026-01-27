<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; 
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

    public function store(Request $request, WatchGuardService $watchGuard)
    {
        // Validar inputs básicos
        $request->validate([
            'login_identifier' => 'required|string', 
            'senha' => 'required|string',
        ]);

        $login = $request->input('login_identifier');
        $password = $request->input('senha');

        // Buscar usuário no banco 
        $user = User::where('username', $login)
                    ->orWhere('email', $login)
                    ->first();

        // Validar Senha Local
        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas no sistema local.'
            ], 401);
        }

        // Iniciar WatchGuard Push
        Session::put('pending_user_id', $user->id); 
        
        $resultado = $watchGuard->iniciarTransacaoPush($user, $password);

        if (! $resultado['success']) {
            return response()->json([
                'message' => $resultado['error']
            ], 401);
        }

        // Retorna o ID da transação para o JavaScript iniciar o polling
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