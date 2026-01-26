<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CodigoDeProposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function checkAdmin() {
        if (Auth::user()->perfil !== 'ADMIN') {
            abort(403, 'Acesso restrito a administradores.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->checkAdmin();
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        // 1. Validação
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users', // Username obrigatório (Login WatchGuard)
            'email'    => 'required|string|email|max:255|unique:users',
            'celular'  => 'required|string|max:20',
            'perfil'   => 'required|in:VENDEDOR,GERENTE,ADMIN',
            'status'   => 'required|in:ATIVO,INATIVO',
        ]);

        // 2. Gerar Sigla (Primeira letra de cada nome)
        $sigla = '';
        $parts = explode(' ', trim($request->name));
        foreach ($parts as $p) {
            if ($p) $sigla .= strtoupper($p[0]);
        }
        $sigla = substr($sigla, 0, 5);

        // 3. Criar Usuário
        User::create([
            'name'     => $request->name,
            'username' => $request->username, // Salva o login do WatchGuard
            'email'    => $request->email,
            'celular'  => $request->celular,
            'perfil'   => $request->perfil,
            'status'   => $request->status,
            'sigla'    => $sigla,
            // Como a auth é externa, salvamos uma senha padrão interna inútil apenas para satisfazer o DB
            'password' => Hash::make('auth_watchguard_externa'), 
        ]);

        return redirect()->route('users.index')
            ->with('success', "Usuário criado com sucesso!");
    }

    public function edit($id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'celular'  => 'required|string',
            'perfil'   => 'required',
            'status'   => 'required',
            'sigla'    => 'required|string|max:10'
        ]);

        $user->update([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'celular'  => $request->celular,
            'perfil'   => $request->perfil,
            'status'   => $request->status,
            'sigla'    => strtoupper($request->sigla),
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $this->checkAdmin();
        
        if (Auth::id() == $id) {
            return redirect()->back()->with('error', 'Você não pode excluir a si mesmo.');
        }

        $temPropostas = CodigoDeProposta::where('user_id', $id)->exists();
        
        if ($temPropostas) {
            $user = User::find($id);
            $user->update(['status' => 'INATIVO']);
            return redirect()->back()->with('error', 'Usuário possui códigos. Status alterado para INATIVO.');
        }

        User::destroy($id);
        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso.');
    }
}