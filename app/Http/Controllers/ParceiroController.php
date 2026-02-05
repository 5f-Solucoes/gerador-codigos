<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parceiro;
use App\Models\Produto;
use App\Models\CodigoDeProposta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ParceiroController extends Controller
{
    public function create()
    {
        return view('parceiros.create');
    }

    public function store(Request $request)
    {
        // Sanitização
        $input = $request->all();
        $input['nome'] = mb_strtoupper(trim($request->input('nome')));
        $input['sigla'] = mb_strtoupper(trim($request->input('sigla'))); 
        $request->merge($input);

        // Validação
        $request->validate([
            'nome' => 'required|string|unique:parceiros,nome',
            'sigla' => 'nullable|string|max:10', 
        ], [
            'nome.unique' => 'Já existe um parceiro com este nome.',
            'nome.required' => 'O nome do parceiro é obrigatório.',
        ]);

        // Salvar
        Parceiro::create($input);

        return redirect()->route('admin.index', ['tab' => 'parceiros'])
            ->with('success', 'Parceiro cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $parceiro = Parceiro::findOrFail($id);
        return view('parceiros.edit', compact('parceiro'));
    }

    public function update(Request $request, $id)
    {
        $parceiro = Parceiro::findOrFail($id);

        $input = $request->all();
        $input['nome'] = mb_strtoupper(trim($request->input('nome')));
        $input['sigla'] = mb_strtoupper(trim($request->input('sigla'))); 
        $request->merge($input);

        $request->validate([
            'nome' => [
                'required',
                'string',
                Rule::unique('parceiros', 'nome')->ignore($parceiro->id),
            ],
            'sigla' => 'nullable|string|max:10', 
        ]);

        $parceiro->update($input);

        return redirect()->route('admin.index', ['tab' => 'parceiros'])
            ->with('success', 'Parceiro atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if (Auth::user()->perfil !== 'ADMIN' && Auth::user()->perfil !== 'GERENTE') {
            abort(403, 'Apenas administradores e gerentes podem excluir parceiros.');
        }
        
        // Verifica se tem produtos cadastrados
        $produtosQtd = Produto::where('parceiro_id', $id)->count();
        if ($produtosQtd > 0) {
            return redirect()->back()->with('error', "Não é possível excluir: Este parceiro possui $produtosQtd produtos cadastrados.");
        }

        // Verifica se tem códigos gerados
        $codigosQtd = CodigoDeProposta::where('parceiro_id', $id)->count();
        if ($codigosQtd > 0) {
            return redirect()->back()->with('error', "Não é possível excluir: Existem $codigosQtd códigos de proposta vinculados a este parceiro.");
        }

        $parceiro = Parceiro::findOrFail($id);
        $parceiro->delete();

        return redirect()->route('admin.index', ['tab' => 'parceiros'])
            ->with('success', 'Parceiro excluído com sucesso.');
    }
}