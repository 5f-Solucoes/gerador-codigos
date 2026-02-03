<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model; 
use App\Models\User;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\Parceiro;
use App\Models\Produto;
use App\Models\CodigoDeProposta;

class MigrarDadosLegados extends Command
{
    protected $signature = 'migrar:legado';
    protected $description = 'Importa dados do banco legado mantendo IDs de relacionamentos e remapeando usuários';

    public function handle()
    {
        if (!$this->confirm('Isso vai APAGAR todos os dados atuais (fresh) e importar do LEGADO. Tem certeza?')) {
            return;
        }

        $this->info('Iniciando migração inteligente...');


        Model::unguard();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $this->info('Limpando tabelas...');
        CodigoDeProposta::truncate();
        Filial::truncate();
        Produto::truncate();
        Parceiro::truncate();
        Cliente::truncate();
        User::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Migrando Usuários...');
        
        $oldUsers = DB::connection('legacy')->table('USUARIOS')->get();
        $mapaDeIds = []; 

        foreach ($oldUsers as $old) {
            $newUser = User::create([
                'name' => $old->NOME,
                'username' => $old->username ?? strtolower(explode(' ', $old->NOME)[0]), 
                'email' => $old->EMAIL,
                'celular' => $old->CELULAR,
                'sigla' => $old->SIGLA,
                'perfil' => $old->PERFIL,
                'status' => $old->STATUS,
                'password' => $old->SENHA, 
                'created_at' => $old->DATA_CADASTRO ?? now(),
                'updated_at' => now(),
            ]);

            $oldId = $old->ID_USUARIO;
            $newId = $newUser->id;
            $mapaDeIds[$oldId] = $newId;
            
            $this->line("Mapeado Usuário: Antigo [$oldId] -> Novo [$newId] ({$old->NOME})");
        }


        $this->info('Migrando Parceiros...');
        $oldParceiros = DB::connection('legacy')->table('PARCEIROS')->get();
        foreach ($oldParceiros as $old) {
            Parceiro::create([
                'id' => $old->ID_PARCEIRO, 
                'nome' => $old->NOME,
                'sigla' => $old->SIGLA,
                'created_at' => $old->DATA_CADASTRO ?? now(),
                'updated_at' => now(),
            ]);
        }

        $this->info('Migrando Clientes...');
        $oldClientes = DB::connection('legacy')->table('CLIENTES')->get();
        foreach ($oldClientes as $old) {
            $cliente = Cliente::create([
                'id' => $old->ID_CLIENTE, 
                'nome_fantasia' => $old->NOME_FANTASIA,
                'razao_social' => $old->RAZAO_SOCIAL,
                'site' => $old->SITE,
                'created_at' => $old->DATA_CADASTRO ?? now(),
                'updated_at' => now(),
            ]);
            
            if (!empty($old->CNPJ)) {
                Filial::create([
                    'cliente_id' => $cliente->id, 
                    'cnpj' => $old->CNPJ,
                    'localidade' => 'Matriz',
                ]);
            }
        }
        
        if (DB::connection('legacy')->getSchemaBuilder()->hasTable('FILIAIS')) {
            $oldFiliais = DB::connection('legacy')->table('FILIAIS')->get();
            foreach ($oldFiliais as $f) {
                if (Cliente::find($f->CLIENTE_ID)) {
                    Filial::create([
                        'cliente_id' => $f->CLIENTE_ID,
                        'cnpj' => $f->CNPJ,
                        'localidade' => $f->LOCALIDADE,
                    ]);
                }
            }
        }

        $this->info('Migrando Produtos...');
        $oldProdutos = DB::connection('legacy')->table('PRODUTOS')->get();
        foreach ($oldProdutos as $old) {
            if (Parceiro::find($old->PARCEIRO_ID)) {
                Produto::create([
                    'id' => $old->ID_PRODUTO, 
                    'nome' => $old->NOME,
                    'sigla' => $old->SIGLA,
                    'parceiro_id' => $old->PARCEIRO_ID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info('Migrando Códigos...');
        $oldCodigos = DB::connection('legacy')->table('CODIGO_DE_PROPOSTA')->get();
        
        $bar = $this->output->createProgressBar(count($oldCodigos));
        $bar->start();

        foreach ($oldCodigos as $old) {
            
            $idVelhoUsuario = $old->ID_USUARIO ?? null;
            $idNovoUsuario = 1; 

            if ($idVelhoUsuario && isset($mapaDeIds[$idVelhoUsuario])) {
                $idNovoUsuario = $mapaDeIds[$idVelhoUsuario];
            }

            $clienteId = Cliente::find($old->CLIENTE_ID) ? $old->CLIENTE_ID : null;
            $parceiroId = Parceiro::find($old->PARCEIRO_ID) ? $old->PARCEIRO_ID : null;
            $produtoId = Produto::find($old->PRODUTO_ID) ? $old->PRODUTO_ID : null;

            CodigoDeProposta::create([
                'id' => $old->ID_CODIGO_DE_PROPOSTA, 
                
                'codigo_proposta' => $old->CODIGO_DE_PROPOSTA,
                'data' => $old->DATA,
                'descricao' => $old->DESCRICAO ?? '',
                'filial_nome' => $old->FILIAL ?? 'N/A',
                
                'user_id' => $idNovoUsuario, 
                
                'cliente_id' => $clienteId,   
                'parceiro_id' => $parceiroId, 
                'produto_id' => $produtoId,   
                
                'created_at' => $old->DATA,
                'updated_at' => $old->DATA,
            ]);
            $bar->advance();
        }
        $bar->finish();

        Model::reguard();
        
        $this->newLine();
        $this->info('Migração concluída! IDs de Clientes/Parceiros/Produtos foram preservados.');
    }
}