<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteStoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'perfil' => 'ADMIN',
            'username' => 'admin_teste',
        ]);
    }

    // MUDANÇA: Adicionado o prefixo 'test_' no início do nome
    public function test_pode_criar_um_cliente_com_sucesso()
    {
        $dados = [
            'nome_fantasia' => 'CLIENTE_TESTE',
            'razao_social' => 'Razão Social Teste Ltda',
            'filiais' => [
                [
                    'cnpj' => '12.345.678/0001-90',
                    'localidade' => 'SÃO PAULO'
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('clientes.store'), $dados);

        $response->assertRedirect(route('admin.index'));
        $response->assertSessionHas('success', 'Cliente cadastrado com sucesso!');

        $this->assertDatabaseHas('clientes', [
            'nome_fantasia' => 'CLIENTE_TESTE',
            'razao_social' => 'RAZÃO SOCIAL TESTE LTDA'
        ]);

        $this->assertDatabaseHas('filiais', [
            'cnpj' => '12.345.678/0001-90',
            'localidade' => 'SÃO PAULO'
        ]);
    }

    public function test_converte_automaticamente_nome_e_razao_para_maiusculo()
    {
        $dados = [
            'nome_fantasia' => 'minusc_sem_espaco',
            'razao_social' => 'minusc razao',
            'filiais' => [['cnpj' => '123', 'localidade' => 'sp']]
        ];

        $this->actingAs($this->user)->post(route('clientes.store'), $dados);

        $this->assertDatabaseHas('clientes', [
            'nome_fantasia' => 'MINUSC_SEM_ESPACO',
            'razao_social' => 'MINUSC RAZAO'
        ]);
    }

    public function test_nao_pode_criar_cliente_com_espaco_no_nome_fantasia()
    {
        $dados = [
            'nome_fantasia' => 'CLIENTE COM ESPACO', 
            'razao_social' => 'Teste',
            'filiais' => [['cnpj' => '123', 'localidade' => 'SP']]
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('clientes.store'), $dados);

        $response->assertSessionHasErrors(['nome_fantasia']);
        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_nao_pode_criar_cliente_com_nome_fantasia_duplicado()
    {
        // Cria manual para não depender de Factory
        Cliente::create([
            'nome_fantasia' => 'CLIENTE_UNICO',
            'razao_social' => 'Original',
            'site' => 'teste.com'
        ]);

        $dados = [
            'nome_fantasia' => 'CLIENTE_UNICO',
            'razao_social' => 'Outra Razão',
            'filiais' => [['cnpj' => '999', 'localidade' => 'RJ']]
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('clientes.store'), $dados);

        $response->assertSessionHasErrors(['nome_fantasia']);
    }

    public function test_deve_ter_pelo_menos_uma_filial()
    {
        $dados = [
            'nome_fantasia' => 'SEM_FILIAL',
            'razao_social' => 'Teste',
            'filiais' => [] 
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('clientes.store'), $dados);

        $response->assertSessionHasErrors(['filiais']);
    }

    public function test_valida_dados_obrigatorios_da_filial()
    {
        $dados = [
            'nome_fantasia' => 'FILIAL_INCOMPLETA',
            'razao_social' => 'Teste',
            'filiais' => [
                [
                    'cnpj' => '', 
                    'localidade' => 'SP'
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('clientes.store'), $dados);

        $response->assertSessionHasErrors(['filiais.0.cnpj']);
    }
}