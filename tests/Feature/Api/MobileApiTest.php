<?php

namespace Tests\Feature\Api;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Models\StatusObra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * API mobile MVP (Fase 8 — MOB-01).
 */
class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────

    private function secretario(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'email'  => 'secretario@exemplo.com',
            'perfil' => 'secretario',
            'ativo'  => true,
        ], $attrs));
    }

    /** Obra em execução com 1 contrato de 1.000,00 e 2 medições (total 400,00). */
    private function obraComMedicoes(): Obra
    {
        $emExecucao = StatusObra::create(['nome' => 'Em Execução', 'cor' => '#0d6efd', 'ordem' => 1]);
        StatusObra::create(['nome' => 'Concluída', 'cor' => '#198754', 'ordem' => 2]);

        $obra = Obra::create([
            'descricao'      => 'Reforma da Escola Municipal',
            'endereco'       => 'Rua Teste, 100',
            'status_obra_id' => $emExecucao->id,
        ]);

        $empresa = Empresa::create(['razao_social' => 'Construtora Exemplo LTDA', 'cnpj' => '00.000.000/0001-00']);

        $contrato = Contrato::create([
            'obra_id'             => $obra->id,
            'empresa_id'          => $empresa->id,
            'numero_contrato_ano' => '001/2026',
            'vigencia_contrato'   => now()->addDays(10)->toDateString(),
            'valor_contrato'      => 1000,
        ]);

        ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => now()->subDays(90)->toDateString(), 'valor_medido' => 150]);
        ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => now()->subDays(70)->toDateString(), 'valor_medido' => 250]);

        return $obra;
    }

    // ── Autenticação ──────────────────────────────────────────────

    public function test_login_com_sucesso_retorna_token_e_usuario_sem_dados_sensiveis(): void
    {
        $this->secretario();

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'secretario@exemplo.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'perfil']])
            ->assertJsonPath('user.perfil', 'secretario')
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.remember_token');

        $this->assertSame(1, PersonalAccessToken::count());
    }

    public function test_login_invalido_retorna_401(): void
    {
        $this->secretario();

        $this->postJson('/api/v1/login', ['email' => 'secretario@exemplo.com', 'password' => 'errada'])
            ->assertUnauthorized()
            ->assertJsonMissingPath('token');

        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_login_sem_campos_retorna_422(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_usuario_inativo_e_bloqueado(): void
    {
        $this->secretario(['ativo' => false]);

        $this->postJson('/api/v1/login', ['email' => 'secretario@exemplo.com', 'password' => 'password'])
            ->assertForbidden()
            ->assertJsonMissingPath('token');

        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_rota_protegida_sem_token_retorna_401_json(): void
    {
        // Sem header Accept: garante JSON e não redirect para o login web
        $this->get('/api/v1/me')->assertUnauthorized()->assertJson(['message' => 'Unauthenticated.']);
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
        $this->getJson('/api/v1/obras')->assertUnauthorized();
    }

    public function test_token_de_usuario_desativado_depois_do_login_e_revogado(): void
    {
        $user  = $this->secretario();
        $token = $user->createToken('mobile')->plainTextToken;

        $user->update(['ativo' => false]);

        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_me_retorna_apenas_campos_publicos(): void
    {
        $user = $this->secretario();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertExactJson([
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => 'secretario@exemplo.com',
                'perfil' => 'secretario',
            ]);
    }

    public function test_logout_revoga_somente_o_token_atual(): void
    {
        $user  = $this->secretario();
        $token = $user->createToken('mobile:celular')->plainTextToken;
        $outro = $user->createToken('mobile:tablet')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

        $this->assertSame(1, PersonalAccessToken::count());
        $this->assertSame('mobile:tablet', PersonalAccessToken::first()->name);

        // O guard do Sanctum guarda o usuário em memória entre requisições do mesmo teste
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($outro)->getJson('/api/v1/me')->assertOk();
    }

    // ── Dados ─────────────────────────────────────────────────────

    public function test_dashboard_retorna_indicadores_de_obras(): void
    {
        $this->obraComMedicoes();
        Sanctum::actingAs($this->secretario());

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('obras.total', 1)
            ->assertJsonPath('obras.em_execucao', 1)
            ->assertJsonPath('obras.concluidas', 0)
            ->assertJsonPath('obras.valor_contratado', 1000)
            ->assertJsonPath('obras.valor_medido', 400)
            ->assertJsonPath('obras.saldo', 600)
            ->assertJsonPath('obras.percentual_executado', 40)
            ->assertJsonPath('status.0.nome', 'Em Execução')
            ->assertJsonPath('status.0.total', 1)
            ->assertJsonPath('status.1.total', 0)
            ->assertJsonPath('alertas.contratos_vencidos', 0)
            ->assertJsonPath('alertas.contratos_vencendo', 1)
            ->assertJsonPath('alertas.obras_sem_medicao', 1);
    }

    /** Contrato avulso para os cenários de resumo de contratos. */
    private function contratoComMedicoes(Obra $obra, Empresa $empresa, ?string $vigencia, float $valor, array $medicoes = []): Contrato
    {
        $contrato = Contrato::create([
            'obra_id'             => $obra->id,
            'empresa_id'          => $empresa->id,
            'numero_contrato_ano' => '0' . random_int(10, 99) . '/2026',
            'vigencia_contrato'   => $vigencia,
            'valor_contrato'      => $valor,
        ]);

        foreach ($medicoes as $valorMedido) {
            ExecucaoObra::create([
                'contrato_id'  => $contrato->id,
                'data_medicao' => now()->subDays(5)->toDateString(),
                'valor_medido' => $valorMedido,
            ]);
        }

        return $contrato;
    }

    public function test_dashboard_retorna_resumo_de_contratos(): void
    {
        $status  = StatusObra::create(['nome' => 'Em Execução', 'cor' => '#0d6efd', 'ordem' => 1]);
        $obra    = Obra::create(['descricao' => 'Obra X', 'endereco' => 'Rua X', 'status_obra_id' => $status->id]);
        $empresa = Empresa::create(['razao_social' => 'Construtora X LTDA', 'cnpj' => '22.222.222/0001-22']);

        $this->contratoComMedicoes($obra, $empresa, now()->addDays(200)->toDateString(), 1000, [200, 100]); // vigente
        $this->contratoComMedicoes($obra, $empresa, now()->addDays(10)->toDateString(), 500, [100]);        // vence em breve
        $this->contratoComMedicoes($obra, $empresa, now()->subDays(10)->toDateString(), 500);               // vencido
        $this->contratoComMedicoes($obra, $empresa, null, 0, [50]);                                         // sem vigência e sem valor
        Sanctum::actingAs($this->secretario());

        // contratado 2.000; medido 450 (inclui a medição do contrato sem valor)
        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('contratos.total', 4)
            ->assertJsonPath('contratos.vigentes', 1)
            ->assertJsonPath('contratos.vence_em_breve', 1)
            ->assertJsonPath('contratos.vencidos', 1)
            ->assertJsonPath('contratos.sem_vigencia', 1)
            ->assertJsonPath('contratos.valor_contratado', 2000)
            ->assertJsonPath('contratos.valor_medido', 450)
            ->assertJsonPath('contratos.saldo', 1550)
            ->assertJsonPath('contratos.percentual_executado', 22.5)
            // Mesmas regras dos alertas e dos totais de obras
            ->assertJsonPath('alertas.contratos_vencidos', 1)
            ->assertJsonPath('alertas.contratos_vencendo', 1)
            ->assertJsonPath('obras.valor_contratado', 2000)
            ->assertJsonPath('obras.valor_medido', 450);
    }

    public function test_dashboard_contratos_percentual_limitado_a_100_e_saldo_nao_negativo(): void
    {
        $status  = StatusObra::create(['nome' => 'Em Execução', 'cor' => '#0d6efd', 'ordem' => 1]);
        $obra    = Obra::create(['descricao' => 'Obra Y', 'endereco' => 'Rua Y', 'status_obra_id' => $status->id]);
        $empresa = Empresa::create(['razao_social' => 'Construtora Y LTDA', 'cnpj' => '33.333.333/0001-33']);

        $this->contratoComMedicoes($obra, $empresa, now()->addDays(200)->toDateString(), 100, [80, 70]);
        Sanctum::actingAs($this->secretario());

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('contratos.valor_contratado', 100)
            ->assertJsonPath('contratos.valor_medido', 150)
            ->assertJsonPath('contratos.saldo', 0)
            ->assertJsonPath('contratos.percentual_executado', 100);
    }

    public function test_dashboard_sem_contratos_retorna_resumo_zerado(): void
    {
        Sanctum::actingAs($this->secretario());

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('contratos.total', 0)
            ->assertJsonPath('contratos.vigentes', 0)
            ->assertJsonPath('contratos.vence_em_breve', 0)
            ->assertJsonPath('contratos.vencidos', 0)
            ->assertJsonPath('contratos.sem_vigencia', 0)
            ->assertJsonPath('contratos.valor_contratado', 0)
            ->assertJsonPath('contratos.valor_medido', 0)
            ->assertJsonPath('contratos.saldo', 0)
            ->assertJsonPath('contratos.percentual_executado', 0);
    }

    public function test_listagem_de_obras_paginada_com_filtros(): void
    {
        $obra = $this->obraComMedicoes();
        Obra::create(['descricao' => 'Pavimentação Rua B', 'status_obra_id' => StatusObra::where('nome', 'Concluída')->value('id')]);
        Sanctum::actingAs($this->secretario());

        $this->getJson('/api/v1/obras')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'descricao', 'endereco', 'status', 'valor_contratado', 'valor_medido', 'saldo', 'percentual_executado']],
                'meta' => ['current_page', 'last_page', 'total'],
                'links',
            ]);

        $this->getJson('/api/v1/obras?search=escola')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $obra->id)
            ->assertJsonPath('data.0.valor_medido', 400)
            ->assertJsonPath('data.0.saldo', 600)
            ->assertJsonPath('data.0.percentual_executado', 40);

        $this->getJson('/api/v1/obras?status=' . $obra->status_obra_id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status.nome', 'Em Execução');

        $this->getJson('/api/v1/obras?status=abc')->assertUnprocessable();
    }

    public function test_detalhe_da_obra(): void
    {
        $obra = $this->obraComMedicoes();
        Sanctum::actingAs($this->secretario());

        $this->getJson("/api/v1/obras/{$obra->id}")
            ->assertOk()
            ->assertJsonPath('id', $obra->id)
            ->assertJsonPath('descricao', 'Reforma da Escola Municipal')
            ->assertJsonPath('valor_contratado', 1000)
            ->assertJsonPath('percentual_executado', 40)
            ->assertJsonPath('contratos.0.numero_contrato', '001/2026')
            ->assertJsonPath('contratos.0.empresa.nome', 'Construtora Exemplo LTDA')
            ->assertJsonPath('contratos.0.vigencia_contrato', now()->addDays(10)->toDateString())
            ->assertJsonPath('contratos.0.vencido', false)
            ->assertJsonCount(2, 'ultimas_medicoes')
            ->assertJsonPath('ultimas_medicoes.0.valor_medido', 250); // mais recente primeiro
    }

    public function test_detalhe_de_obra_inexistente_retorna_404_json(): void
    {
        Sanctum::actingAs($this->secretario());

        $this->getJson('/api/v1/obras/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Recurso não encontrado.']);
    }
}
