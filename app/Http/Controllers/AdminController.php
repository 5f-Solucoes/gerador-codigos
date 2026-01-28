<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Parceiro;
use App\Models\Produto;
use App\Models\CodigoDeProposta;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        if (!in_array(Auth::user()->perfil, ['VENDEDOR', 'GERENTE', 'ADMIN'])) {
            abort(403);
        }

        // Carrega dados para as abas
        $clientes = Cliente::with('filiais')->orderBy('nome_fantasia')->get();
        $parceiros = Parceiro::orderBy('nome')->get();
        $produtos = Produto::with('parceiro')->orderBy('nome')->get();
        
        // Busca os usuários para preencher o select de filtro
        $users = \App\Models\User::orderBy('name')->get();

        // Adicione 'users' dentro do compact()
        return view('admin.index', compact('clientes', 'parceiros', 'produtos', 'users'));
    }

    public function exportCsv(Request $request)
    {
        if (!in_array(Auth::user()->perfil, ['GERENTE', 'ADMIN'])) {
            abort(403, 'Acesso negado.');
        }

        $fileName = 'relatorio_codigos_' . date('Y-m-d_H-i') . '.csv';
        
        // Inicia a Query
        $query = CodigoDeProposta::with(['user', 'cliente', 'parceiro', 'produto'])
                    ->orderBy('data', 'desc');

        
        // Filtro por Usuário/Vendedor
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por Cliente
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtro por Parceiro (Fabricante)
        if ($request->filled('parceiro_id')) {
            $query->where('parceiro_id', $request->parceiro_id);
        }

        // Filtro por Data (Opcional, mas muito útil em relatórios)
        if ($request->filled('data_inicio')) {
            $query->whereDate('data', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data', '<=', $request->data_fim);
        }

        // Executa a query com os filtros aplicados
        $codigos = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($codigos) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) )); 
            
            fputcsv($file, ['ID', 'Data', 'Codigo', 'Descricao', 'Responsavel', 'Sigla', 'Cliente', 'Filial', 'Parceiro', 'Produto'], ';');

            foreach ($codigos as $c) {
                fputcsv($file, [
                    $c->id,
                    \Carbon\Carbon::parse($c->data)->format('d/m/Y'), 
                    $c->codigo_proposta,
                    $c->descricao,
                    $c->user ? $c->user->name : 'N/D',
                    $c->user ? $c->user->sigla : '-',
                    $c->cliente ? $c->cliente->nome_fantasia : 'N/D',
                    $c->filial_nome,
                    $c->parceiro ? $c->parceiro->nome : 'N/D',
                    $c->produto ? $c->produto->nome : 'N/D',
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}