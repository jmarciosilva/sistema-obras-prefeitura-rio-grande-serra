<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Contrato;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Models\StatusObra;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Auditoria (Histórico de Atividades) — operações feitas pelas rotas web
 * reais (controllers, transações e middleware incluídos).
 */
class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private StatusObra $planejamento;
    private StatusObra $execucao;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin        = $this->usuario('admin');
        $this->planejamento = StatusObra::create(['nome' => 'Em Planejamento', 'cor' => '#6c757d', 'ordem' => 1]);
        $this->execucao     = StatusObra::create(['nome' => 'Em Execução', 'cor' => '#0d6efd', 'ordem' => 2]);
        $this->empresa      = Empresa::create(['razao_social' => 'Construtora Serra Azul LTDA', 'cnpj' => '11.111.111/0001-11']);
    }

    private function usuario(string $perfil): User
    {
        return User::factory()->create(['perfil' => $perfil, 'ativo' => true]);
    }

    private function obra(array $attrs = []): Obra
    {
        return Obra::create(array_merge([
            'descricao'      => 'Reforma da Escola Municipal',
            'status_obra_id' => $this->planejamento->id,
        ], $attrs));
    }

    private function contrato(Obra $obra, array $attrs = []): Contrato
    {
        return Contrato::create(array_merge([
            'obra_id'             => $obra->id,
            'empresa_id'          => $this->empresa->id,
            'numero_contrato_ano' => '012/2026',
            'valor_contrato'      => 1000,
        ], $attrs));
    }

    private function ultima(): ?Auditoria
    {
        return Auditoria::latest('id')->first();
    }

    // ── Acesso ───────────────────────────────────────────────────

    public function test_admin_e_secretario_acessam_o_historico(): void
    {
        foreach (['admin', 'secretario'] as $perfil) {
            $this->actingAs($this->usuario($perfil))
                ->get('/auditoria')
                ->assertOk()
                ->assertSee('Histórico de Atividades');
        }
    }

    public function test_tecnico_e_operador_recebem_403_mesmo_digitando_a_url(): void
    {
        $this->actingAs($this->admin);
        $this->obra(); // gera uma auditoria para testar também o detalhe
        $id = $this->ultima()->id;

        foreach (['tecnico', 'operador'] as $perfil) {
            $usuario = $this->usuario($perfil);
            $this->actingAs($usuario)->get('/auditoria')->assertForbidden();
            $this->actingAs($usuario)->get("/auditoria/{$id}")->assertForbidden();
        }
    }

    public function test_menu_so_aparece_para_admin_e_secretario(): void
    {
        foreach (['admin', 'secretario'] as $perfil) {
            $this->actingAs($this->usuario($perfil))->get(route('obras.index'))
                ->assertSee('Histórico de Atividades');
        }

        $this->actingAs($this->usuario('operador'))->get(route('obras.index'))
            ->assertDontSee('Histórico de Atividades');

        $this->actingAs($this->usuario('tecnico'))->get(route('obras.index'))
            ->assertDontSee('Histórico de Atividades');
    }

    public function test_visitante_vai_para_o_login(): void
    {
        $this->get('/auditoria')->assertRedirect(route('login'));
    }

    // ── Obras ────────────────────────────────────────────────────

    public function test_criacao_de_obra_registra_usuario_ip_e_dados(): void
    {
        $this->actingAs($this->admin)
            ->withHeader('User-Agent', 'Navegador de Teste')
            ->post(route('obras.store'), [
                'descricao'      => 'Pavimentação da Rua das Flores',
                'status_obra_id' => $this->planejamento->id,
            ])
            ->assertRedirect();

        $obra = Obra::firstOrFail();
        $a    = $this->ultima();

        $this->assertSame(Auditoria::CRIOU, $a->acao);
        $this->assertSame(Obra::class, $a->auditable_type);
        $this->assertSame($obra->id, $a->auditable_id);
        $this->assertSame($this->admin->id, $a->user_id);
        $this->assertSame('127.0.0.1', $a->ip_address);
        $this->assertSame('Navegador de Teste', $a->user_agent);
        $this->assertStringContainsString('Obra criada: Pavimentação da Rua das Flores', $a->descricao);
        $this->assertNull($a->dados_antes);
        $this->assertSame('Pavimentação da Rua das Flores', $a->dados_depois['descricao']);
        $this->assertEquals($this->planejamento->id, $a->dados_depois['status_obra_id']);
        $this->assertArrayNotHasKey('updated_at', $a->dados_depois);
        $this->assertArrayNotHasKey('created_at', $a->dados_depois);
        $this->assertArrayNotHasKey('id', $a->dados_depois);
    }

    public function test_alteracao_de_obra_registra_somente_campos_alterados(): void
    {
        $this->actingAs($this->admin);
        $obra = $this->obra();

        $this->put(route('obras.update', $obra), [
            'descricao'      => 'Reforma da Escola Municipal', // igual
            'status_obra_id' => $this->execucao->id,           // mudou
        ])->assertRedirect();

        $a = $this->ultima();
        $this->assertSame(Auditoria::ALTEROU, $a->acao);
        $this->assertEquals(['status_obra_id' => $this->planejamento->id], $a->dados_antes);
        $this->assertEquals(['status_obra_id' => $this->execucao->id], $a->dados_depois);
        $this->assertStringContainsString('Obra atualizada', $a->descricao);
    }

    public function test_salvar_sem_mudancas_nao_gera_historico(): void
    {
        $this->actingAs($this->admin);
        $obra  = $this->obra();
        $antes = Auditoria::count();

        $this->put(route('obras.update', $obra), [
            'descricao'      => $obra->descricao,
            'status_obra_id' => $obra->status_obra_id,
        ])->assertRedirect();

        $this->assertSame($antes, Auditoria::count());
    }

    public function test_exclusao_de_obra_guarda_snapshot_e_informa_cascata(): void
    {
        $this->actingAs($this->admin);
        $obra     = $this->obra(['endereco' => 'Rua A, 10']);
        $contrato = $this->contrato($obra);
        ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 100]);

        $this->delete(route('obras.destroy', $obra))->assertRedirect(route('obras.index'));

        $a = $this->ultima();
        $this->assertSame(Auditoria::EXCLUIU, $a->acao);
        $this->assertSame($obra->id, $a->auditable_id);
        $this->assertNull($a->dados_depois);
        $this->assertSame('Reforma da Escola Municipal', $a->dados_antes['descricao']);
        $this->assertSame('Rua A, 10', $a->dados_antes['endereco']);
        $this->assertStringContainsString('1 contrato(s) e 1 medição(ões)', $a->descricao);
    }

    public function test_convenios_da_obra_sao_auditados(): void
    {
        $this->actingAs($this->admin);
        $obra = $this->obra();
        $c1   = Convenio::create(['descricao' => 'Convênio Estadual A']);
        $c2   = Convenio::create(['descricao' => 'Convênio Federal B']);

        $this->put(route('obras.convenios.sync', $obra), ['convenios' => [$c1->id, $c2->id]])
            ->assertRedirect();

        $a = $this->ultima();
        $this->assertSame(Auditoria::ALTEROU, $a->acao);
        $this->assertStringContainsString('Convênios da obra atualizados', $a->descricao);
        $this->assertSame(['convenios' => []], $a->dados_antes);
        $this->assertSame(['convenios' => [$c1->id, $c2->id]], $a->dados_depois);

        // Mesmo conjunto de novo → nada muda → sem novo registro
        $total = Auditoria::count();
        $this->put(route('obras.convenios.sync', $obra), ['convenios' => [$c2->id, $c1->id]]);
        $this->assertSame($total, Auditoria::count());

        // No detalhe aparecem os nomes, não os IDs
        $this->get(route('auditoria.show', $a))
            ->assertOk()
            ->assertSee('Convênio Estadual A')
            ->assertSee('Convênio Federal B');
    }

    // ── Contratos ────────────────────────────────────────────────

    public function test_criacao_alteracao_e_exclusao_de_contrato(): void
    {
        $this->actingAs($this->admin);
        $obra = $this->obra();

        $this->post(route('contratos.store'), [
            'obra_id'             => $obra->id,
            'empresa_id'          => $this->empresa->id,
            'numero_contrato_ano' => '045/2026',
            'valor_contrato'      => 100000,
        ])->assertRedirect();
        $contrato = Contrato::firstOrFail();

        $criou = $this->ultima();
        $this->assertSame(Auditoria::CRIOU, $criou->acao);
        $this->assertSame(Contrato::class, $criou->auditable_type);
        $this->assertSame('Contrato criado: 045/2026', $criou->descricao);
        $this->assertEquals(100000, $criou->dados_depois['valor_contrato']);

        $this->put(route('contratos.update', $contrato), [
            'obra_id'             => $obra->id,
            'empresa_id'          => $this->empresa->id,
            'numero_contrato_ano' => '045/2026',
            'valor_contrato'      => 120000,
        ])->assertRedirect();

        $alterou = $this->ultima();
        $this->assertSame(Auditoria::ALTEROU, $alterou->acao);
        $this->assertSame(['valor_contrato'], array_keys($alterou->dados_depois));
        $this->assertEquals(100000, $alterou->dados_antes['valor_contrato']);
        $this->assertEquals(120000, $alterou->dados_depois['valor_contrato']);

        // Detalhe legível: "Valor contratado | R$ 100.000,00 | R$ 120.000,00"
        $this->get(route('auditoria.show', $alterou))
            ->assertOk()
            ->assertSee('Valor contratado')
            ->assertSee('R$ 100.000,00')
            ->assertSee('R$ 120.000,00');

        $this->delete(route('contratos.destroy', $contrato))->assertRedirect();

        $excluiu = $this->ultima();
        $this->assertSame(Auditoria::EXCLUIU, $excluiu->acao);
        $this->assertSame('045/2026', $excluiu->dados_antes['numero_contrato_ano']);
        $this->assertNull($excluiu->dados_depois);
    }

    // ── Medições ─────────────────────────────────────────────────

    public function test_criacao_alteracao_e_exclusao_de_medicao(): void
    {
        $this->actingAs($this->admin);
        $obra     = $this->obra();
        $contrato = $this->contrato($obra);

        $this->post(route('obras.execucoes.store', $obra), [
            'contrato_id'  => $contrato->id,
            'data_medicao' => '2026-06-10',
            'valor_medido' => 250,
        ])->assertRedirect();
        $medicao = ExecucaoObra::firstOrFail();

        $criou = $this->ultima();
        $this->assertSame(Auditoria::CRIOU, $criou->acao);
        $this->assertSame(ExecucaoObra::class, $criou->auditable_type);
        $this->assertSame('Medição criada: 10/06/2026 — R$ 250,00', $criou->descricao);

        $this->put(route('obras.execucoes.update', [$obra, $medicao]), [
            'contrato_id'  => $contrato->id,
            'data_medicao' => '2026-06-10',
            'valor_medido' => 300,
            'motivo'       => 'Valor digitado errado no lançamento',
        ])->assertRedirect();

        $alterou = $this->ultima();
        $this->assertSame(Auditoria::ALTEROU, $alterou->acao);
        $this->assertEquals(250, $alterou->dados_antes['valor_medido']);
        $this->assertEquals(300, $alterou->dados_depois['valor_medido']);
        $this->assertArrayNotHasKey('data_medicao', $alterou->dados_depois); // não mudou
        $this->assertArrayNotHasKey('updated_at', $alterou->dados_depois);

        $this->delete(route('obras.execucoes.destroy', [$obra, $medicao]), ['motivo' => 'Medição lançada em duplicidade'])
            ->assertRedirect();

        $excluiu = $this->ultima();
        $this->assertSame(Auditoria::EXCLUIU, $excluiu->acao);
        $this->assertStringContainsString('Medição excluída: 10/06/2026', $excluiu->descricao);
        $this->assertEquals(300, $excluiu->dados_antes['valor_medido']);
    }

    public function test_correcao_e_exclusao_de_medicao_exigem_motivo_e_o_gravam(): void
    {
        $this->actingAs($this->admin);
        $obra     = $this->obra();
        $contrato = $this->contrato($obra);
        $medicao  = ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 250]);
        $total    = Auditoria::count();

        // Sem motivo → nada muda
        $this->put(route('obras.execucoes.update', [$obra, $medicao]), [
            'contrato_id'  => $contrato->id,
            'data_medicao' => '2026-06-10',
            'valor_medido' => 999,
        ])->assertSessionHasErrors('motivo');
        $this->delete(route('obras.execucoes.destroy', [$obra, $medicao]))->assertSessionHasErrors('motivo');

        $this->assertEquals(250, $medicao->fresh()->valor_medido);
        $this->assertSame($total, Auditoria::count());

        // Com motivo → motivo gravado e exibido no histórico
        $this->put(route('obras.execucoes.update', [$obra, $medicao]), [
            'contrato_id'  => $contrato->id,
            'data_medicao' => '2026-06-10',
            'valor_medido' => 280,
            'motivo'       => 'Boletim corrigido pela fiscalização',
        ])->assertRedirect();

        $alterou = $this->ultima();
        $this->assertSame('Boletim corrigido pela fiscalização', $alterou->motivo);
        $this->get(route('auditoria.show', $alterou))->assertOk()->assertSee('Boletim corrigido pela fiscalização');
        $this->get(route('auditoria.index'))->assertOk()->assertSee('Motivo: Boletim corrigido');

        $this->delete(route('obras.execucoes.destroy', [$obra, $medicao]), ['motivo' => 'Lançada na obra errada'])
            ->assertRedirect();
        $this->assertSame('Lançada na obra errada', $this->ultima()->motivo);
        $this->assertDatabaseMissing('execucao_obras', ['id' => $medicao->id]);

        // Registros fora da correção/exclusão não herdam o motivo
        $this->assertNull(Auditoria::where('acao', Auditoria::CRIOU)->latest('id')->first()->motivo);
    }

    public function test_responsaveis_e_documentos_da_medicao_sao_auditados(): void
    {
        $this->actingAs($this->admin);
        $obra     = $this->obra();
        $contrato = $this->contrato($obra);
        $fiscal   = User::factory()->create(['name' => 'Ana Fiscal', 'perfil' => 'tecnico', 'ativo' => true]);
        $medicao  = ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 250]);
        $medicao->documentos()->create([
            'user_id'       => $this->admin->id,
            'tipo'          => 'medicao',
            'nome_original' => 'boletim-01.pdf',
            'caminho'       => 'documentos/medicoes/x/boletim-01.pdf',
            'mime_type'     => 'application/pdf',
            'tamanho_bytes' => 10,
        ]);

        // Só troca responsáveis (campos da medição iguais) → ainda assim auditado
        $this->put(route('obras.execucoes.update', [$obra, $medicao]), [
            'contrato_id'  => $contrato->id,
            'data_medicao' => '2026-06-10',
            'valor_medido' => 250,
            'responsaveis' => [['user_id' => $fiscal->id, 'papel' => 'fiscal']],
            'motivo'       => 'Fiscal não havia sido informado',
        ])->assertRedirect();

        $a = $this->ultima();
        $this->assertSame(Auditoria::ALTEROU, $a->acao);
        $this->assertSame(['responsaveis' => []], $a->dados_antes);
        $this->assertSame(['responsaveis' => ['Ana Fiscal — Fiscal']], $a->dados_depois);
        $this->assertSame('Fiscal não havia sido informado', $a->motivo);
        $this->get(route('auditoria.show', $a))->assertOk()->assertSee('Responsáveis')->assertSee('Ana Fiscal — Fiscal');

        // Exclusão: snapshot inclui responsáveis e documentos
        $this->delete(route('obras.execucoes.destroy', [$obra, $medicao]), ['motivo' => 'Medição lançada em duplicidade'])
            ->assertRedirect();

        $excluiu = $this->ultima();
        $this->assertSame(Auditoria::EXCLUIU, $excluiu->acao);
        $this->assertSame(['Ana Fiscal — Fiscal'], $excluiu->dados_antes['responsaveis']);
        $this->assertSame(['boletim-01.pdf'], $excluiu->dados_antes['documentos']);
        $this->assertSame(0, $medicao->documentos()->count());
    }

    public function test_tecnico_corrige_mas_somente_admin_exclui_medicao(): void
    {
        $obra     = $this->obra();
        $contrato = $this->contrato($obra);
        $medicao  = ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 250]);
        $dados    = ['contrato_id' => $contrato->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 1, 'motivo' => 'Valor lançado errado pelo técnico'];

        foreach (['secretario', 'operador'] as $perfil) {
            $this->actingAs($this->usuario($perfil));
            $this->get(route('obras.execucoes.edit', [$obra, $medicao]))->assertForbidden();
            $this->put(route('obras.execucoes.update', [$obra, $medicao]), $dados)->assertForbidden();
            $this->delete(route('obras.execucoes.destroy', [$obra, $medicao]), $dados)->assertForbidden();
            $this->get(route('obras.show', $obra))->assertOk()
                ->assertDontSee('Corrigir medição')->assertDontSee('Excluir medição');
        }
        $this->assertEquals(250, $medicao->fresh()->valor_medido);

        // Técnico: corrige (com motivo auditado), mas não exclui
        $tecnico = $this->usuario('tecnico');
        $this->actingAs($tecnico);
        $this->get(route('obras.show', $obra))->assertOk()
            ->assertSee('Corrigir medição')->assertDontSee('Excluir medição');
        $this->get(route('obras.execucoes.edit', [$obra, $medicao]))->assertOk()->assertSee('Motivo da correção');
        $this->put(route('obras.execucoes.update', [$obra, $medicao]), $dados)->assertRedirect();
        $this->assertEquals(1, $medicao->fresh()->valor_medido);
        $this->assertSame($tecnico->id, $this->ultima()->user_id);
        $this->assertSame('Valor lançado errado pelo técnico', $this->ultima()->motivo);
        $this->delete(route('obras.execucoes.destroy', [$obra, $medicao]), $dados)->assertForbidden();
        $this->assertDatabaseHas('execucao_obras', ['id' => $medicao->id]);

        $this->actingAs($this->admin);
        $this->get(route('obras.show', $obra))->assertOk()->assertSee('Excluir medição')->assertSee('Motivo da exclusão');
        $this->get(route('obras.execucoes.edit', [$obra, $medicao]))->assertOk()->assertSee('Motivo da correção');
    }

    public function test_medicao_pode_ser_corrigida_e_excluida_a_partir_do_contrato(): void
    {
        $obra     = $this->obra();
        $contrato = $this->contrato($obra);
        $medicao  = ExecucaoObra::create(['contrato_id' => $contrato->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 250]);

        // Técnico: vê o ✏️ no contrato, não o 🗑; ao salvar volta para o contrato
        $this->actingAs($this->usuario('tecnico'));
        $this->get(route('contratos.show', $contrato))->assertOk()
            ->assertSee(route('obras.execucoes.edit', [$obra, $medicao, 'retorno' => 'contrato']), false)
            ->assertDontSee('Excluir medição');

        $this->put(route('obras.execucoes.update', [$obra, $medicao]), [
            'contrato_id'  => $contrato->id,
            'data_medicao' => '2026-06-10',
            'valor_medido' => 260,
            'motivo'       => 'Correção feita pela tela do contrato',
            'retorno'      => 'contrato',
        ])->assertRedirect(route('contratos.show', $contrato));
        $this->assertSame('Correção feita pela tela do contrato', $this->ultima()->motivo);

        // Admin: vê o 🗑 e o modal com motivo
        $this->actingAs($this->admin);
        $this->get(route('contratos.show', $contrato))->assertOk()
            ->assertSee('Excluir medição')
            ->assertSee('Motivo da exclusão');

        // Operador: nenhuma ação
        $this->actingAs($this->usuario('operador'));
        $this->get(route('contratos.show', $contrato))->assertOk()
            ->assertDontSee('Corrigir medição')
            ->assertDontSee('Excluir medição');
    }

    public function test_medicao_de_outra_obra_nao_pode_ser_alterada_pela_url(): void
    {
        $this->actingAs($this->admin);
        $obraA   = $this->obra();
        $obraB   = $this->obra(['descricao' => 'Outra obra']);
        $medicao = ExecucaoObra::create(['contrato_id' => $this->contrato($obraA)->id, 'data_medicao' => '2026-06-10', 'valor_medido' => 250]);

        $this->delete(route('obras.execucoes.destroy', [$obraB, $medicao]), ['motivo' => 'Tentativa por outra obra'])
            ->assertNotFound();
        $this->assertDatabaseHas('execucao_obras', ['id' => $medicao->id]);
    }

    // ── Robustez ─────────────────────────────────────────────────

    public function test_operacao_desfeita_por_rollback_nao_gera_historico(): void
    {
        $this->actingAs($this->admin);

        try {
            DB::transaction(function () {
                $this->obra(['descricao' => 'Obra que não vai existir']);
                throw new \RuntimeException('falha simulada');
            });
        } catch (\RuntimeException) {
        }

        $this->assertSame(0, Obra::count());
        $this->assertSame(0, Auditoria::count());
    }

    public function test_falha_na_auditoria_nao_interrompe_a_operacao(): void
    {
        Log::spy();
        Schema::drop('auditorias');

        $this->actingAs($this->admin)
            ->post(route('obras.store'), [
                'descricao'      => 'Obra salva mesmo sem auditoria',
                'status_obra_id' => $this->planejamento->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Obra::count());
        Log::shouldHaveReceived('error')
            ->withArgs(fn($mensagem) => $mensagem === 'Falha ao registrar auditoria')
            ->once();
    }

    public function test_campos_sensiveis_nunca_sao_gravados(): void
    {
        $limpo = AuditoriaService::limpar([
            'name'           => 'Fulano',
            'password'       => 'segredo',
            'remember_token' => 'abc',
            'api_token'      => 'xyz',
            'updated_at'     => '2026-09-22',
        ]);

        $this->assertSame(['name' => 'Fulano'], $limpo);
    }

    // ── Listagem e filtros ───────────────────────────────────────

    public function test_filtros_por_usuario_modulo_acao_e_periodo(): void
    {
        $outro = $this->usuario('tecnico');
        $criar = function (User $u, string $acao, string $tipo, string $descricao, string $quando) {
            (new Auditoria([
                'user_id' => $u->id, 'acao' => $acao, 'auditable_type' => $tipo,
                'auditable_id' => 1, 'descricao' => $descricao,
            ]))->forceFill(['created_at' => $quando, 'updated_at' => $quando])->save();
        };

        $criar($this->admin, Auditoria::CRIOU, Obra::class, 'Registro Alfa', '2026-09-01 10:00:00');
        $criar($outro, Auditoria::EXCLUIU, Contrato::class, 'Registro Beta', '2026-09-15 10:00:00');
        $criar($outro, Auditoria::ALTEROU, ExecucaoObra::class, 'Registro Gama', '2026-09-20 10:00:00');

        $this->actingAs($this->admin);

        $this->get('/auditoria')
            ->assertSee('Registro Alfa')->assertSee('Registro Beta')->assertSee('Registro Gama')
            ->assertSeeInOrder(['Registro Gama', 'Registro Beta', 'Registro Alfa']); // mais recentes primeiro

        $this->get('/auditoria?usuario=' . $outro->id)
            ->assertDontSee('Registro Alfa')->assertSee('Registro Beta')->assertSee('Registro Gama');

        $this->get('/auditoria?modulo=contrato')
            ->assertSee('Registro Beta')->assertDontSee('Registro Alfa')->assertDontSee('Registro Gama');

        $this->get('/auditoria?acao=alterou')
            ->assertSee('Registro Gama')->assertDontSee('Registro Alfa')->assertDontSee('Registro Beta');

        $this->get('/auditoria?de=2026-09-10&ate=2026-09-16')
            ->assertSee('Registro Beta')->assertDontSee('Registro Alfa')->assertDontSee('Registro Gama');

        $this->get('/auditoria?modulo=invalido')->assertSessionHasErrors('modulo');
    }

    public function test_detalhe_mostra_nomes_em_vez_de_ids(): void
    {
        $this->actingAs($this->admin);
        $obra = $this->obra();
        $obra->update(['status_obra_id' => $this->execucao->id]);

        $this->get(route('auditoria.show', $this->ultima()))
            ->assertOk()
            ->assertSee('Campos alterados')
            ->assertSee('Status')
            ->assertSeeInOrder(['Em Planejamento', 'Em Execução'])
            ->assertSee('127.0.0.1');
    }
}
