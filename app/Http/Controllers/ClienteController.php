<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\CodigoDeProposta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function __construct() {
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        // 1. Sanitização básica (ToUpper)
        $input = $request->all();
        $input['nome_fantasia'] = strtoupper(trim($request->input('nome_fantasia')));
        $input['razao_social'] = strtoupper(trim($request->input('razao_social')));
        $request->merge($input); // Atualiza o request com os dados sanitizados

        // 2. Validação
        $request->validate([
            'nome_fantasia' => [
                'required',
                'string',
                'regex:/^\S*$/u', // Regex: Não permite espaços
                'unique:clientes,nome_fantasia' // Único na tabela clientes
            ],
            'filiais' => 'required|array|min:1',
            'filiais.*.cnpj' => 'required|distinct',
            'filiais.*.localidade' => 'required',
        ], [
            'nome_fantasia.regex' => 'O Nome Fantasia não pode conter espaços.',
            'nome_fantasia.unique' => 'Já existe um cliente com este Nome Fantasia.',
            'filiais.required' => 'Cadastre ao menos uma filial.',
            'filiais.*.cnpj.required' => 'O CNPJ é obrigatório em todas as linhas.',
            'filiais.*.localidade.required' => 'A localidade é obrigatória em todas as linhas.',
        ]);

        // 3. Criação do Cliente
        $cliente = new Cliente();
        $cliente->nome_fantasia = $request->nome_fantasia;
        $cliente->razao_social = $request->razao_social;
        // Campos legados (se existirem na tabela, senão remova)
        // $cliente->data_cadastro = now(); 
        // $cliente->cadastrado_por = Auth::id();
        $cliente->save();

        // 4. Criação das Filiais
        foreach ($request->filiais as $f) {
            $cliente->filiais()->create([
                'cnpj' => strtoupper(trim($f['cnpj'])),
                'localidade' => strtoupper(trim($f['localidade']))
            ]);
        }

        return redirect()->route('admin.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $cliente = Cliente::with('filiais')->findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id); // Isso usa 'id' automaticamente

        // Sanitização
        $input = $request->all();
        $input['nome_fantasia'] = strtoupper(trim($request->input('nome_fantasia')));
        $input['razao_social'] = strtoupper(trim($request->input('razao_social')));
        $request->merge($input);

        // Validação
        $request->validate([
            'nome_fantasia' => [
                'required',
                'regex:/^\S*$/u',
                // CORREÇÃO: Apenas ignore($cliente->id). O Laravel sabe que a coluna é 'id'.
                Rule::unique('clientes', 'nome_fantasia')->ignore($cliente->id),
            ],
            'filiais' => 'required|array|min:1',
            'filiais.*.cnpj' => 'required',
            'filiais.*.localidade' => 'required',
        ], [
            'nome_fantasia.regex' => 'O Nome Fantasia não pode conter espaços.',
        ]);

        // Atualiza Cliente
        $cliente->update([
            'nome_fantasia' => $request->nome_fantasia,
            'razao_social' => $request->razao_social,
        ]);

        // Atualiza Filiais (Estratégia: Apagar tudo e recriar, igual ao legado)
        // Isso é mais simples para lidar com CNPJs que mudaram ou foram removidos
        $cliente->filiais()->delete();

        foreach ($request->filiais as $f) {
            $cliente->filiais()->create([
                'cnpj' => strtoupper(trim($f['cnpj'])),
                'localidade' => strtoupper(trim($f['localidade']))
            ]);
        }

        return redirect()->route('admin.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if (Auth::user()->perfil !== 'ADMIN') {
            abort(403);
        }

        // Verifica se existe algum código de proposta usando este cliente
        $uso = CodigoDeProposta::where('cliente_id', $id)->count();

        if ($uso > 0) {
            // REDIRECIONA COM MENSAGEM DE ERRO
            return redirect()->back()
                ->with('error', "Não é possível excluir: Este cliente possui $uso códigos gerados. Exclua os códigos primeiro.");
        }

        $cliente = Cliente::findOrFail($id);
        $cliente->filiais()->delete();
        $cliente->delete();

        return redirect()->route('admin.index')
            ->with('success', 'Cliente excluído com sucesso.');
    }
}