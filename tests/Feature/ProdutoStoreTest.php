<?php

namespace Tests\Feature;

use App\Models\Parceiro;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoStoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria usuário ADMIN com username para evitar erro de banco
        $this->user = User::factory()->create([
            'perfil' => 'ADMIN',
            'username' => 'admin_produto',
        ]);
    }

    public function test_pode_criar_um_produto_com_sucesso()
    {
        // Precisa existir um parceiro antes
        $parceiro = Parceiro::create([
            'nome' => 'MICROSOFT',
            'sigla' => 'MS'
        ]);

        $dados = [
            'parceiro_id' => $parceiro->id,
            'nome' => 'windows_11', 
            'sigla' => 'win11'
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('produtos.store'), $dados);

        // Verifica redirecionamento com a aba correta
        $response->assertRedirect(route('admin.index', ['tab' => 'produtos']));
        $response->assertSessionHas('success', 'Produto cadastrado com sucesso!');

        // Verifica no banco 
        $this->assertDatabaseHas('produtos', [
            'parceiro_id' => $parceiro->id,
            'nome' => 'WINDOWS_11',
            'sigla' => 'WIN11'
        ]);
    }

    public function test_nao_pode_criar_produto_sem_parceiro_valido()
    {
        $dados = [
            'parceiro_id' => 99999, 
            'nome' => 'TESTE',
            'sigla' => 'TST'
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('produtos.store'), $dados);

        $response->assertSessionHasErrors(['parceiro_id']);
    }

    public function test_nao_pode_ter_espacos_no_nome_do_produto()
    {
        $parceiro = Parceiro::create(['nome' => 'TESTE', 'sigla' => 'T']);

        $dados = [
            'parceiro_id' => $parceiro->id,
            'nome' => 'WINDOWS SERVER', 
            'sigla' => 'WS'
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('produtos.store'), $dados);

        $response->assertSessionHasErrors(['nome']);
    }

    public function test_valida_unicidade_do_nome_dentro_do_mesmo_parceiro()
    {
        $parceiroA = Parceiro::create(['nome' => 'PARCEIRO A', 'sigla' => 'A']);
        
        Produto::create([
            'parceiro_id' => $parceiroA->id,
            'nome' => 'OFFICE',
            'sigla' => 'OFF'
        ]);

        $dadosDuplicado = [
            'parceiro_id' => $parceiroA->id,
            'nome' => 'OFFICE',
            'sigla' => 'OFF2'
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('produtos.store'), $dadosDuplicado);

        $response->assertSessionHasErrors(['nome']);
        
        $this->assertDatabaseCount('produtos', 1);
    }

    public function test_permite_mesmo_nome_de_produto_em_parceiros_diferentes()
    {
        $parceiroA = Parceiro::create(['nome' => 'PARCEIRO A', 'sigla' => 'A']);
        Produto::create([
            'parceiro_id' => $parceiroA->id,
            'nome' => 'OFFICE',
            'sigla' => 'OFF'
        ]);

        $parceiroB = Parceiro::create(['nome' => 'PARCEIRO B', 'sigla' => 'B']);

        $dadosPermitido = [
            'parceiro_id' => $parceiroB->id,
            'nome' => 'OFFICE', 
            'sigla' => 'OFF_B'
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('produtos.store'), $dadosPermitido);

        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseCount('produtos', 2);
    }

    public function test_sigla_e_opcional()
    {
        $parceiro = Parceiro::create(['nome' => 'TESTE', 'sigla' => 'T']);

        $dados = [
            'parceiro_id' => $parceiro->id,
            'nome' => 'PRODUTO_SEM_SIGLA',
            'sigla' => null
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('produtos.store'), $dados);

        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseHas('produtos', [
            'nome' => 'PRODUTO_SEM_SIGLA',
        ]);
    }
}