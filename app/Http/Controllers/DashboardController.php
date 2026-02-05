<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CodigoDeProposta;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search');
        
        $query = CodigoDeProposta::with('user');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo_proposta', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhere('filial_nome', 'like', "%{$search}%");
            });
        }

        if (!in_array($user->perfil, ['GERENTE', 'ADMIN'])) {
            $query->where('user_id', $user->id);
        }

        $projetos = $query->orderBy('data', 'desc')->paginate(10); 

        return view('dashboard', compact('projetos'));
        
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!in_array($user->perfil, ['GERENTE', 'ADMIN'])) {
            abort(403, 'Acesso não autorizado');
        }

        $proposta = CodigoDeProposta::findOrFail($id);
        $proposta->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Código excluído com sucesso!');
    }
    
    public function edit($id)
    {
        $proposta = \App\Models\CodigoDeProposta::findOrFail($id);
        
        // Carrega as listas igual ao create
        $clientes = \App\Models\Cliente::orderBy('nome_fantasia')->get();
        $filiais = \App\Models\Filial::orderBy('localidade')->get();
        $parceiros = \App\Models\Parceiro::orderBy('nome')->get();
        $produtos = \App\Models\Produto::orderBy('nome')->get();
        
        // Busca siglas ativas para o dropdown de troca de responsável
        $siglasAtivas = \App\Models\User::where('status', 'ATIVO')
            ->orderBy('sigla')
            ->pluck('sigla'); 

        // Tenta extrair a sigla atual do código string (ex: 5F...-SIGLA-...)
        $parts = explode('-', $proposta->codigo_proposta);
        $siglaNoCodigo = $parts[1] ?? ''; 

        return view('propostas.edit', compact(
            'proposta', 'clientes', 'filiais', 'parceiros', 'produtos', 'siglasAtivas', 'siglaNoCodigo'
        ));
    }

    // Método para SALVAR a edição
    public function update(Request $request, $id)
    {
        $proposta = \App\Models\CodigoDeProposta::findOrFail($id);

        // Validação
        $request->validate([
            'cliente_id' => 'required',
            'filial' => 'required',
            'parceiro_id' => 'required',
            'produto_id' => 'required',
            'descricao' => 'nullable|string'
        ]);

        // Helpers de Limpeza
        $limpar = function($str) { return strtoupper(str_replace(' ', '', $str)); };
        $limparDesc = function($str) { return strtoupper(str_replace(' ', '_', trim($str))); };
        $limparFilial = function($str) { return strtoupper(str_replace(' ', '_', trim($str))); };

        $partesCodigo = explode('-', $proposta->codigo_proposta);
        $prefixoOriginal = $partesCodigo[0]; 

        $novaSigla = $request->vendedor_sigla;
        if (empty($novaSigla)) {
             $novaSigla = $partesCodigo[1] ?? Auth::user()->sigla;
        }
        $siglaFinal = $limpar($novaSigla);

        // Busca Nomes Novos no Banco
        $cliente = \App\Models\Cliente::find($request->cliente_id);
        $parceiro = \App\Models\Parceiro::find($request->parceiro_id);
        $produto = \App\Models\Produto::find($request->produto_id);

        // Remonta o Sufixo
        $nomeCliente = $limpar($cliente->nome_fantasia);
        $nomeFilial = $limparFilial($request->filial);
        $nomeParceiro = $limpar($parceiro->nome);
        $nomeProduto = $limpar($produto->nome);

        $sufixo = "-{$siglaFinal}-{$nomeCliente}-{$nomeFilial}-{$nomeParceiro}_{$nomeProduto}";

        if ($request->descricao) {
            $sufixo .= '-' . $limparDesc($request->descricao);
        }

        // Código Completo Novo
        $codigoCompleto = $prefixoOriginal . $sufixo;

        $proposta->update([
            'codigo_proposta' => $codigoCompleto,
            'descricao'       => $request->descricao,
            'filial_nome'     => $request->filial,
            'cliente_id'      => $request->cliente_id,
            'parceiro_id'     => $request->parceiro_id,
            'produto_id'      => $request->produto_id,
        ]);

        return redirect()->route('dashboard')
            ->with('success', "Código atualizado com sucesso: $codigoCompleto");
    }

    public function create()
    {
        // Busca dados para os selects
        $clientes = \App\Models\Cliente::orderBy('nome_fantasia')->get(['id', 'nome_fantasia', 'site']);
        
        // Busca todas as filiais
        $filiais = \App\Models\Filial::orderBy('localidade')->get(['cliente_id', 'localidade']);
        
        $parceiros = \App\Models\Parceiro::orderBy('nome')->get(['id', 'nome']);
        $produtos = \App\Models\Produto::orderBy('nome')->get(['id', 'nome', 'parceiro_id']);
        
        $vendedores = \App\Models\User::where('status', 'ATIVO')
                        ->orderBy('name')
                        ->get(['id', 'name', 'sigla']);

        return view('propostas.create', compact('clientes', 'filiais', 'parceiros', 'produtos', 'vendedores'));
    }

    public function store(Request $request)
    {
        // Validação
        $request->validate([
            'vendedor_sigla' => 'required',
            'cliente_id' => 'required|exists:clientes,id',
            'filial' => 'required',
            'parceiro_id' => 'required|exists:parceiros,id',
            'produto_id' => 'required|exists:produtos,id',
            'descricao' => 'nullable|string'
        ]);

        $cliente = \App\Models\Cliente::find($request->cliente_id);
        $parceiro = \App\Models\Parceiro::find($request->parceiro_id);
        $produto = \App\Models\Produto::find($request->produto_id);
        
        $limpar = function($str) {
            return mb_strtoupper(str_replace(' ', '', $str));
        };
        $limparDesc = function($str) {
            return mb_strtoupper(str_replace(' ', '_', trim($str)));
        };

        $hoje = now();
        $prefixoData = '5F' . $hoje->format('ymd'); 
        
        // Conta quantos códigos já existem hoje para gerar a sequência
        $qtdHoje = \App\Models\CodigoDeProposta::whereDate('created_at', $hoje->toDateString())->count();
        $sequencia = $qtdHoje + 1;

        $parteSequencial = $prefixoData . $sequencia . 'v1'; 

        
        $sigla = $limpar($request->vendedor_sigla);
        $nomeCliente = $limpar($cliente->nome_fantasia);
        $nomeFilial = mb_strtoupper(str_replace(' ', '_', $request->filial)); 
        
        $nomeParceiro = $limpar($parceiro->nome);
        $nomeProduto = $limpar($produto->nome);
        
        // Monta o sufixo base
        $sufixo = "-{$sigla}-{$nomeCliente}-{$nomeFilial}-{$nomeParceiro}_{$nomeProduto}";

        // Se tiver descrição, adiciona no final
        if ($request->descricao) {
            $sufixo .= '-' . $limparDesc($request->descricao);
        }

        $codigoCompleto = $parteSequencial . $sufixo;

        // Salva no Banco
        \App\Models\CodigoDeProposta::create([
            'codigo_proposta' => $codigoCompleto,
            'data' => $hoje,
            'descricao' => $request->descricao ?? 'Sem descrição',
            'filial_nome' => $request->filial,
            'user_id' => Auth::id(),
            'cliente_id' => $request->cliente_id,
            'parceiro_id' => $request->parceiro_id,
            'produto_id' => $request->produto_id,
        ]);

        return redirect()->route('dashboard')
            ->with('success', "Código gerado: $codigoCompleto");
    }

    
}
