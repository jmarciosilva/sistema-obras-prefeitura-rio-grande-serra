<?php

namespace Tests\Feature\Api;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Models\StatusObra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * API mobile — Contratos (somente leitura).
 */
class ContratosApiTest extends TestCase
{
    use RefreshDatabase;

    private Obra $obra;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $status = StatusObra::create(['nome' => 'Em Execução', 'cor' => '#0d6efd', 'ordem' => 1]);
        $this->obra = Obra::create([
            'descricao'      => 'Pavimentação da Rua das Flores',
            'endereco'       => 'Rua das Flores, s/n',
            'status_obra_id' => $status->id,
        ]);
        $this->empresa = Empresa::create([
            'razao_social'  => 'Construtora Serra Azul LTDA',
            'nome_fantasia' => 'Serra Azul',
            'cnpj'          => '11.111.111/0001-11',
        ]);
    }

    private function logar(): void
    {
        Sanctum::actingAs(User::factory()->create(['perfil' => 'secretario', 'ativo' => true]));
    }

    private function contrato(array $attrs = [], array $medicoes = []): Contrato
    {
        $contrato = Contrato::create(array_merge([
            'obra_id'             => $this->obra->id,
            'empresa_id'          => $this->empresa->id,
            'numero_contrato_ano' => '012/2026',
            'processo_licitacao'  => '500/2025',
            'valor_contrato'      => 1000,
            'vigencia_contrato'   => now()->addDays(200)->toDateString(),
        ], $attrs));

        foreach ($medicoes as $i => $valor) {
            ExecucaoObra::create([
                'contrato_id'  => $contrato->id,
                'data_medicao' => now()->subDays(100 - $i * 10)->toDateString(),
                'valor_medido' => $valor,
            ]);
        }

        return $contrato;
    }

    public function test_contratos_sem_token_retorna_401(): void
    {
        $this->getJson('/api/v1/contratos')->assertUnauthorized();
        $this->getJson('/api/v1/contratos/1')->assertUnauthorized();
    }

    public function test_listagem_retorna_contrato_json_e_calculos_financeiros(): void
    {
        $c = $this->contrato([], [150, 250]);
        $this->logar();

        $this->getJson('/api/v1/contratos')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id', 'numero_contrato_ano', 'processo_licitacao',
                    'obra' => ['id', 'descricao'],
                    'empresa' => ['id', 'razao_social', 'nome_fantasia'],
                    'valor_contrato', 'valor_medido', 'saldo', 'percentual_executado',
                    'data_assinatura', 'ordem_inicio', 'vigencia_contrato', 'situacao_vigencia',
                ]],
                'meta' => ['current_page', 'last_page', 'total'],
                'links',
            ])
            ->assertJsonPath('data.0.id', $c->id)
            ->assertJsonPath('data.0.valor_contrato', 1000)
            ->assertJsonPath('data.0.valor_medido', 400)
            ->assertJsonPath('data.0.saldo', 600)
            ->assertJsonPath('data.0.percentual_executado', 40)
            ->assertJsonPath('data.0.situacao_vigencia', 'vigente')
            ->assertJsonMissingPath('data.0.empresa.cnpj');
    }

    public function test_paginacao(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->contrato(['numero_contrato_ano' => "00{$i}/2026"]);
        }
        $this->logar();

        $this->getJson('/api/v1/contratos?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson('/api/v1/contratos?per_page=2&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_busca_por_numero_obra_e_empresa(): void
    {
        $alvo = $this->contrato(['numero_contrato_ano' => '123/2026']);

        $outraEmpresa = Empresa::create(['razao_social' => 'Engenharia Norte SA', 'cnpj' => '22.222.222/0001-22']);
        $outraObra    = Obra::create(['descricao' => 'Reforma da UBS', 'status_obra_id' => $this->obra->status_obra_id]);
        $this->contrato([
            'numero_contrato_ano' => '999/2025',
            'empresa_id'          => $outraEmpresa->id,
            'obra_id'             => $outraObra->id,
        ]);
        $this->logar();

        foreach (['123/2026', 'Flores', 'Serra Azul LTDA', 'Serra Azul'] as $termo) {
            $this->getJson('/api/v1/contratos?search=' . urlencode($termo))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $alvo->id);
        }
    }

    public function test_busca_combinada_com_situacao_nao_vaza_o_or(): void
    {
        // Mesmo número, situações diferentes: a busca não pode trazer o vigente
        $this->contrato(['numero_contrato_ano' => '777/2026', 'vigencia_contrato' => now()->subDays(5)->toDateString()]);
        $this->contrato(['numero_contrato_ano' => '777/2026']);
        $this->logar();

        $this->getJson('/api/v1/contratos?search=777&situacao=vencido')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.situacao_vigencia', 'vencido');
    }

    public function test_filtros_de_situacao(): void
    {
        $vencido = $this->contrato(['vigencia_contrato' => now()->subDays(10)->toDateString()]);
        $breve   = $this->contrato(['vigencia_contrato' => now()->addDays(10)->toDateString()]);
        $vigente = $this->contrato(['vigencia_contrato' => now()->addDays(200)->toDateString()]);
        $sem     = $this->contrato(['vigencia_contrato' => null]);
        $this->logar();

        $casos = [
            'vencido'        => $vencido,
            'vence_em_breve' => $breve,
            'vigente'        => $vigente,
            'sem_vigencia'   => $sem,
        ];

        foreach ($casos as $situacao => $contrato) {
            $this->getJson("/api/v1/contratos?situacao={$situacao}")
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $contrato->id)
                ->assertJsonPath('data.0.situacao_vigencia', $situacao);
        }

        $this->getJson('/api/v1/contratos?situacao=invalida')->assertUnprocessable();
    }

    public function test_detalhe_do_contrato(): void
    {
        $c = $this->contrato(['data_assinatura' => '2026-01-10', 'ordem_inicio' => '2026-02-01'], [100, 200, 300, 50, 60, 70]);
        $this->logar();

        $this->getJson("/api/v1/contratos/{$c->id}")
            ->assertOk()
            ->assertJsonPath('id', $c->id)
            ->assertJsonPath('numero_contrato_ano', '012/2026')
            ->assertJsonPath('data_assinatura', '2026-01-10')
            ->assertJsonPath('ordem_inicio', '2026-02-01')
            ->assertJsonPath('obra.descricao', 'Pavimentação da Rua das Flores')
            ->assertJsonPath('obra.endereco', 'Rua das Flores, s/n')
            ->assertJsonPath('obra.status.nome', 'Em Execução')
            ->assertJsonPath('empresa.razao_social', 'Construtora Serra Azul LTDA')
            ->assertJsonPath('empresa.nome_fantasia', 'Serra Azul')
            ->assertJsonPath('valor_medido', 780)
            ->assertJsonPath('saldo', 220)
            ->assertJsonPath('percentual_executado', 78)
            ->assertJsonCount(5, 'ultimas_medicoes')
            ->assertJsonPath('ultimas_medicoes.0.valor_medido', 70); // mais recente primeiro
    }

    public function test_contrato_inexistente_retorna_404(): void
    {
        $this->logar();

        $this->getJson('/api/v1/contratos/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Recurso não encontrado.']);
    }

    public function test_contrato_sem_valor_nao_divide_por_zero(): void
    {
        $c = $this->contrato(['valor_contrato' => null], [100]);
        $this->logar();

        $this->getJson("/api/v1/contratos/{$c->id}")
            ->assertOk()
            ->assertJsonPath('valor_contrato', 0)
            ->assertJsonPath('valor_medido', 100)
            ->assertJsonPath('saldo', 0)
            ->assertJsonPath('percentual_executado', 0);
    }

    public function test_medicao_acima_do_contratado_segue_regra_do_sistema(): void
    {
        // Mesma regra do web: percentual limitado a 100 e saldo nunca negativo
        $c = $this->contrato(['valor_contrato' => 1000], [800, 400]);
        $this->logar();

        $this->getJson("/api/v1/contratos/{$c->id}")
            ->assertOk()
            ->assertJsonPath('valor_medido', 1200)
            ->assertJsonPath('saldo', 0)
            ->assertJsonPath('percentual_executado', 100);
    }

    public function test_contrato_sem_vigencia_e_sem_medicoes(): void
    {
        $c = $this->contrato(['vigencia_contrato' => null]);
        $this->logar();

        $this->getJson("/api/v1/contratos/{$c->id}")
            ->assertOk()
            ->assertJsonPath('vigencia_contrato', null)
            ->assertJsonPath('situacao_vigencia', 'sem_vigencia')
            ->assertJsonPath('valor_medido', 0)
            ->assertJsonCount(0, 'ultimas_medicoes');
    }
}
