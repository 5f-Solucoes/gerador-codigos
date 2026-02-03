<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Filial;
use App\Models\Parceiro;
use App\Models\Produto;
use App\Models\User;
use App\Models\CodigoDeProposta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PropostaStoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'perfil' => 'VENDEDOR',
            'username' => 'vendedor_teste',
            'sigla' => 'VD'
        ]);
    }

    public function test_gera_codigo_completo_corretamente()
    {
        // 1. Congela o tempo para garantir que a data do teste seja fixa
        $agora = Carbon::create(2024, 10, 25, 14, 0, 0); // 25/10/2024
        $this->travelTo($agora);

        // 2. Prepara os dados
        $cliente = Cliente::create(['nome_fantasia' => 'Acme Corp', 'razao_social' => 'Acme']);
        $parceiro = Parceiro::create(['nome' => 'Microsoft', 'sigla' => 'MS']);
        $produto = Produto::create(['parceiro_id' => $parceiro->id, 'nome' => 'Office 365', 'sigla' => 'O365']);

        $dados = [
            'vendedor_sigla' => 'JD', // João Da Silva
            'cliente_id' => $cliente->id,
            'filial' => 'Rio de Janeiro', // Espaços viram Underline na filial
            'parceiro_id' => $parceiro->id,
            'produto_id' => $produto->id,
            'descricao' => 'Renovacao Anual' // Espaços viram Underline na descrição
        ];

        // 3. Executa
        $response = $this->actingAs($this->user)
                         ->post(route('propostas.store'), $dados);

        // 4. Validações
        $response->assertRedirect(route('dashboard'));
        
        // CÁLCULO DO CÓDIGO ESPERADO:
        // Prefixo Data: 5F + 241025 (ymd)
        // Sequencia: 1 (primeiro do dia) + v1
        // Vendedor: JD
        // Cliente: ACMECORP (Remove espaços)
        // Filial: RIO_DE_JANEIRO (Espaço vira underline)
        // Parceiro: MICROSOFT (Remove espaços)
        // Produto: OFFICE365 (Remove espaços)
        // Descrição: RENOVACAO_ANUAL (Espaço vira underline)

        $codigoEsperado = "5F2410251v1-JD-ACMECORP-RIO_DE_JANEIRO-MICROSOFT_OFFICE365-RENOVACAO_ANUAL";

        $this->assertDatabaseHas('codigo_de_propostas', [
            'codigo_proposta' => $codigoEsperado,
            'filial_nome' => 'Rio de Janeiro',
            'user_id' => $this->user->id
        ]);
        
        $response->assertSessionHas('success', "Código gerado: $codigoEsperado");
    }

    public function test_incrementa_sequencia_se_criar_dois_no_mesmo_dia()
    {
        $agora = Carbon::create(2024, 12, 01, 10, 0, 0);
        $this->travelTo($agora);

        // Cria dependências
        $cliente = Cliente::create(['nome_fantasia' => 'Cli', 'razao_social' => 'Rz']);
        $parceiro = Parceiro::create(['nome' => 'Par', 'sigla' => 'P']);
        $produto = Produto::create(['parceiro_id' => $parceiro->id, 'nome' => 'Prod', 'sigla' => 'Pr']);

        $dados = [
            'vendedor_sigla' => 'TE',
            'cliente_id' => $cliente->id,
            'filial' => 'Matriz',
            'parceiro_id' => $parceiro->id,
            'produto_id' => $produto->id,
        ];

        // Cria o PRIMEIRO código
        $this->actingAs($this->user)->post(route('propostas.store'), $dados);

        // Cria o SEGUNDO código (logo em seguida, no mesmo dia)
        $this->actingAs($this->user)->post(route('propostas.store'), $dados);

        // O primeiro deve ser ...1v1
        $this->assertDatabaseHas('codigo_de_propostas', [
            'codigo_proposta' => '5F2412011v1-TE-CLI-MATRIZ-PAR_PROD'
        ]);

        // O segundo deve ser ...2v1 (Incrementado)
        $this->assertDatabaseHas('codigo_de_propostas', [
            'codigo_proposta' => '5F2412012v1-TE-CLI-MATRIZ-PAR_PROD'
        ]);
    }

    public function test_reinicia_sequencia_em_dia_diferente()
    {
        // Dia 1
        $this->travelTo(Carbon::create(2024, 01, 01, 10, 0, 0));
        
        $cliente = Cliente::create(['nome_fantasia' => 'C', 'razao_social' => 'R']);
        $parceiro = Parceiro::create(['nome' => 'P', 'sigla' => 'S']);
        $produto = Produto::create(['parceiro_id' => $parceiro->id, 'nome' => 'X', 'sigla' => 'X']);
        
        $dados = [
            'vendedor_sigla' => 'A', 'cliente_id' => $cliente->id, 'filial' => 'F', 
            'parceiro_id' => $parceiro->id, 'produto_id' => $produto->id
        ];

        // Cria dia 01
        $this->actingAs($this->user)->post(route('propostas.store'), $dados);

        // Dia 2 (Muda a data)
        $this->travelTo(Carbon::create(2024, 01, 02, 10, 0, 0));

        // Cria dia 02
        $this->actingAs($this->user)->post(route('propostas.store'), $dados);

        // O código do dia 02 deve voltar a ser sequencia 1 (...021v1...), e não 2
        $this->assertDatabaseHas('codigo_de_propostas', [
            'codigo_proposta' => '5F2401021v1-A-C-F-P_X'
        ]);
    }

    public function test_valida_campos_obrigatorios()
    {
        $response = $this->actingAs($this->user)
                         ->post(route('propostas.store'), []); // Envia vazio

        $response->assertSessionHasErrors([
            'vendedor_sigla', 'cliente_id', 'filial', 'parceiro_id', 'produto_id'
        ]);
    }
}