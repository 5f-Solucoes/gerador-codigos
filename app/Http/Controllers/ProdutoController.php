<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Parceiro;
use App\Models\CodigoDeProposta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProdutoController extends Controller
{
    public function create()
    {
        $parceiros = Parceiro::orderBy('nome')->get();
        $produtos = Produto::all(); 
        return view('produtos.create', compact('parceiros', 'produtos'));
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
            'parceiro_id' => 'required|exists:parceiros,id',
            'nome' => [
                'required',
                'regex:/^\S*$/u',
                Rule::unique('produtos')->where(function ($query) use ($request) {
                    return $query->where('parceiro_id', $request->parceiro_id);
                }),
            ],
            'sigla' => 'nullable|string|max:20',
        ], [
            'nome.regex' => 'O nome não pode conter espaços.',
            'nome.unique' => 'Produto já cadastrado para este parceiro.',
        ]);

        // Salvar
        Produto::create($input);

        return redirect()->route('admin.index', ['tab' => 'produtos'])
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $produto = Produto::findOrFail($id);
        $parceiros = Parceiro::orderBy('nome')->get();
        return view('produtos.edit', compact('produto', 'parceiros'));
    }

    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $input = $request->all();
        $input['nome'] = mb_strtoupper(trim($request->input('nome')));
        $input['sigla'] = mb_strtoupper(trim($request->input('sigla')));
        $request->merge($input);

        $request->validate([
            'parceiro_id' => 'required|exists:parceiros,id',
            'nome' => [
                'required',
                'regex:/^\S*$/u',
                Rule::unique('produtos')->where(function ($query) use ($request) {
                    return $query->where('parceiro_id', $request->parceiro_id);
                })->ignore($produto->id),
            ],
            'sigla' => 'nullable|string|max:20',
        ]);

        $produto->update($input);

        return redirect()->route('admin.index', ['tab' => 'produtos'])
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if (Auth::user()->perfil !== 'ADMIN' && Auth::user()->perfil !== 'GERENTE') {
            abort(403, 'Apenas administradores e gerente podem excluir produtos.');
        }

        $uso = CodigoDeProposta::where('produto_id', $id)->count();

        if ($uso > 0) {
            return redirect()->back()
                ->with('error', "Não é possível excluir: Existem $uso códigos de proposta gerados com este produto.");
        }

        Produto::destroy($id);

        return redirect()->route('admin.index', ['tab' => 'produtos'])
            ->with('success', 'Produto excluído com sucesso.');
    }
}