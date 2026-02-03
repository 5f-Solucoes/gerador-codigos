<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CodigoDeProposta;
use App\Models\Cliente;
use App\Models\Parceiro;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $vendedor;

    protected function setUp(): void
    {
        parent::setUp();

        // Usuário ADMIN
        $this->admin = User::factory()->create([
            'perfil' => 'ADMIN',
            'username' => 'admin_user', // Já estava ok
            'email' => 'admin@teste.com'
        ]);

        // Usuário VENDEDOR
        $this->vendedor = User::factory()->create([
            'perfil' => 'VENDEDOR',
            'username' => 'vendedor_user', // Já estava ok
            'email' => 'vendedor@teste.com'
        ]);
    }

    public function test_vendedor_nao_pode_acessar_lista_de_usuarios()
    {
        $response = $this->actingAs($this->vendedor)->get(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_pode_acessar_lista_de_usuarios()
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertViewIs('users.index');
    }

    public function test_admin_pode_criar_usuario_e_sigla_eh_gerada_automaticamente()
    {
        $dados = [
            'name' => 'Roberto Carlos Braga',
            'username' => 'rcarlos',
            'email' => 'roberto@teste.com',
            'celular' => '11999999999',
            'perfil' => 'VENDEDOR',
            'status' => 'ATIVO'
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $dados);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Roberto Carlos Braga',
            'email' => 'roberto@teste.com',
            'sigla' => 'RCB'
        ]);
    }

    public function test_valida_campos_obrigatorios_e_unicos()
    {
        $dados = [
            'name' => 'Teste Duplicado',
            'username' => 'admin_user', 
            'email' => 'admin@teste.com',
            'celular' => '11999999999',
            'perfil' => 'VENDEDOR',
            'status' => 'ATIVO'
        ];

        $response = $this->actingAs($this->admin)->post(route('users.store'), $dados);

        $response->assertSessionHasErrors(['username', 'email']);
    }

    public function test_pode_atualizar_usuario()
    {
        // CORREÇÃO AQUI: Adicionado username
        $usuario = User::factory()->create([
            'name' => 'Antigo Nome',
            'username' => 'antigo_user', 
            'sigla' => 'AN'
        ]);

        $dados = [
            'name' => 'Novo Nome',
            'username' => $usuario->username,
            'email' => $usuario->email,
            'celular' => '11888888888',
            'perfil' => 'GERENTE',
            'status' => 'INATIVO',
            'sigla' => 'NN'
        ];

        $response = $this->actingAs($this->admin)
                         ->put(route('users.update', $usuario->id), $dados);

        $response->assertRedirect(route('users.index'));
        
        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'Novo Nome',
            'perfil' => 'GERENTE',
            'status' => 'INATIVO',
            'sigla' => 'NN'
        ]);
    }

    public function test_nao_pode_excluir_a_si_mesmo()
    {
        $response = $this->actingAs($this->admin)
                         ->delete(route('users.destroy', $this->admin->id));

        $response->assertSessionHas('error', 'Você não pode excluir a si mesmo.');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_exclui_usuario_sem_propostas_com_sucesso()
    {
        // CORREÇÃO AQUI: Adicionado username
        $usuario = User::factory()->create([
            'name' => 'Usuario Limpo',
            'username' => 'usuario_limpo' 
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('users.destroy', $usuario->id));

        $response->assertSessionHas('success', 'Usuário excluído com sucesso.');
        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
    }

   public function test_ao_tentar_excluir_usuario_com_propostas_ele_vira_inativo()
    {
        $usuarioAlvo = User::factory()->create([
            'name' => 'Usuario Com Vendas', 
            'username' => 'usuario_vendas', 
            'status' => 'ATIVO'
        ]);

        $cliente = Cliente::create(['nome_fantasia' => 'C', 'razao_social' => 'R']);
        $parceiro = Parceiro::create(['nome' => 'P', 'sigla' => 'S']);
        $produto = Produto::create(['parceiro_id' => $parceiro->id, 'nome' => 'Pr', 'sigla' => 'Pr']);

        CodigoDeProposta::create([
            'codigo_proposta' => 'CODIGO-TESTE-USER',
            'user_id' => $usuarioAlvo->id,
            'cliente_id' => $cliente->id,
            'parceiro_id' => $parceiro->id,
            'produto_id' => $produto->id,
            'filial_nome' => 'Matriz',
            'data' => now(),
            'descricao' => 'Descrição de Teste Obrigatória' 
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('users.destroy', $usuarioAlvo->id));

        $response->assertSessionHas('error', 'Usuário possui códigos. Status alterado para INATIVO.');

        $this->assertDatabaseHas('users', [
            'id' => $usuarioAlvo->id,
            'status' => 'INATIVO'
        ]);
    }
}