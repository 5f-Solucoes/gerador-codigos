<?php

namespace Tests\Feature;

use App\Models\Parceiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParceiroStoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria usuário ADMIN 
        $this->user = User::factory()->create([
            'perfil' => 'ADMIN',
            'username' => 'admin_parceiro',
        ]);
    }

    public function test_pode_criar_um_parceiro_com_sucesso()
    {
        $dados = [
            'nome' => 'Dell Technologies',
            'sigla' => 'DELL'
        ];

        // A rota espera redirecionar para a tab=parceiros
        $response = $this->actingAs($this->user)
                         ->post(route('parceiros.store'), $dados);

        // Verifica o redirecionamento com o parâmetro da aba
        $response->assertRedirect(route('admin.index', ['tab' => 'parceiros']));
        $response->assertSessionHas('success', 'Parceiro cadastrado com sucesso!');

        // Verifica no banco 
        $this->assertDatabaseHas('parceiros', [
            'nome' => 'DELL TECHNOLOGIES',
            'sigla' => 'DELL'
        ]);
    }

    public function test_converte_nome_e_sigla_para_maiusculo()
    {
        $dados = [
            'nome' => 'parceiro são paulo',
            'sigla' => 'psp'
        ];

        $this->actingAs($this->user)->post(route('parceiros.store'), $dados);

        $this->assertDatabaseHas('parceiros', [
            'nome' => 'PARCEIRO SÃO PAULO', 
            'sigla' => 'PSP'
        ]);
    }

    public function test_nao_pode_criar_parceiro_com_nome_duplicado()
    {
        // Cria um parceiro existente
        Parceiro::create([
            'nome' => 'MICROSOFT',
            'sigla' => 'MS'
        ]);

        // Tenta criar outro igual 
        $dados = [
            'nome' => 'Microsoft', 
            'sigla' => 'MSFT'
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('parceiros.store'), $dados);

        $response->assertSessionHasErrors(['nome']);
        
        // Garante que continua existindo apenas 1 Microsoft
        $this->assertDatabaseCount('parceiros', 1);
    }

    public function test_valida_tamanho_maximo_da_sigla()
    {
        $dados = [
            'nome' => 'Parceiro Teste',
            'sigla' => '12345678901' 
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('parceiros.store'), $dados);

        $response->assertSessionHasErrors(['sigla']);
    }

    public function test_sigla_e_opcional()
    {
        $dados = [
            'nome' => 'Parceiro Sem Sigla',
            'sigla' => null
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('parceiros.store'), $dados);

        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseHas('parceiros', [
            'nome' => 'PARCEIRO SEM SIGLA',
            'sigla' => ''
    ]);
  }
}