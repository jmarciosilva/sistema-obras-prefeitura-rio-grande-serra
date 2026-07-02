<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 25px 30px; }
        body { font-family: sans-serif; font-size: 10px; color: #1e293b; }

        .capa { text-align: center; padding: 10px 0 18px; border-bottom: 3px solid #1e40af; margin-bottom: 16px; }
        .capa h1 { font-size: 18px; color: #1e40af; margin: 0 0 4px; }
        .capa p { font-size: 10px; color: #64748b; margin: 2px 0; }

        .kpis { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .kpis td {
            width: 25%; text-align: center; padding: 10px 6px;
            border: 1px solid #e2e8f0; background: #f8fafc;
        }
        .kpis .valor { font-size: 16px; font-weight: bold; color: #1e40af; display: block; }
        .kpis .rotulo { font-size: 8px; color: #64748b; text-transform: uppercase; }

        h2 { font-size: 12px; color: #1e40af; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin: 18px 0 8px; }

        table.dados { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.dados th {
            background: #1e40af; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px;
        }
        table.dados td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        table.dados tr:nth-child(even) td { background: #f8fafc; }

        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 8px; color: #fff; font-size: 8px;
        }

        .barra-fundo { width: 100%; background: #e2e8f0; height: 10px; border-radius: 4px; }
        .barra-preenchida { height: 10px; border-radius: 4px; }

        .rodape { position: fixed; bottom: -15px; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>

    <div class="capa">
        <h1>🗂️ Relatório de Processos Administrativos</h1>
        <p>Prefeitura Municipal de Rio Grande da Serra — SP</p>
        <p>Gerado em {{ $gerado_em }} por {{ $gerado_por }}</p>
    </div>

    <table class="kpis">
        <tr>
            <td><span class="valor">{{ $totalFiltrado }}</span><span class="rotulo">Processos no filtro</span></td>
            <td><span class="valor">{{ $abertosFiltrado }}</span><span class="rotulo">Abertos</span></td>
            <td><span class="valor">{{ $comPendencia->count() }}</span><span class="rotulo">Com Pendência</span></td>
            <td><span class="valor">{{ $porFase->count() }}</span><span class="rotulo">Fases distintas</span></td>
        </tr>
    </table>

    {{-- ═══ POR FASE ═══ --}}
    <h2>📍 Processos por Fase</h2>
    @php $totalFase = $porFase->sum('total'); @endphp
    <table class="dados">
        <tr><th style="width:35%">Fase</th><th style="width:45%">Distribuição</th><th style="width:20%">Quantidade</th></tr>
        @forelse ($porFase as $f)
        <tr>
            <td>{{ $f['nome'] }}</td>
            <td>
                <div class="barra-fundo">
                    <div class="barra-preenchida" style="width: {{ $totalFase > 0 ? ($f['total']/$totalFase)*100 : 0 }}%; background-color: {{ $f['cor'] }};"></div>
                </div>
            </td>
            <td>{{ $f['total'] }} ({{ $totalFase > 0 ? number_format(($f['total']/$totalFase)*100, 1) : 0 }}%)</td>
        </tr>
        @empty
        <tr><td colspan="3">Nenhum processo encontrado.</td></tr>
        @endforelse
    </table>

    {{-- ═══ POR TIPO ═══ --}}
    <h2>🏷️ Processos por Tipo</h2>
    <table class="dados">
        <tr><th>Tipo de Processo</th><th style="width:20%">Quantidade</th><th style="width:20%">% do Total</th></tr>
        @php $totalTipo = $porTipo->sum('total'); @endphp
        @forelse ($porTipo as $t)
        <tr>
            <td>{{ $t['nome'] }}</td>
            <td>{{ $t['total'] }}</td>
            <td>{{ $totalTipo > 0 ? number_format(($t['total']/$totalTipo)*100, 1) : 0 }}%</td>
        </tr>
        @empty
        <tr><td colspan="3">Nenhum processo encontrado.</td></tr>
        @endforelse
    </table>

    <div style="page-break-before: always;"></div>

    {{-- ═══ PENDÊNCIAS ═══ --}}
    <h2>⚠️ Processos com Pendência</h2>
    <table class="dados">
        <tr><th style="width:15%">Processo</th><th style="width:25%">Requerente</th><th style="width:20%">Fase</th><th>Motivo</th></tr>
        @forelse ($comPendencia as $p)
        <tr>
            <td>{{ $p->processo_numero }}</td>
            <td>{{ $p->requerente }}</td>
            <td><span class="badge" style="background-color: {{ $p->faseAtual->cor ?? '#64748b' }}">{{ $p->faseAtual->nome ?? '—' }}</span></td>
            <td>{{ $p->motivo_pendencia }}</td>
        </tr>
        @empty
        <tr><td colspan="4">Nenhum processo com pendência encontrado.</td></tr>
        @endforelse
    </table>

    {{-- ═══ LISTA GERAL ═══ --}}
    <h2>📋 Lista de Processos</h2>
    @if ($totalProcessosOriginal > $processos->count())
        <p style="color:#b91c1c; margin: 0 0 6px;">
            ⚠️ Mostrando os {{ $processos->count() }} processos mais recentes de um total de {{ $totalProcessosOriginal }}
            encontrados com os filtros aplicados. Para a lista completa, use a exportação em Excel.
        </p>
    @endif
    <table class="dados">
        <tr>
            <th style="width:12%">Processo</th>
            <th style="width:20%">Requerente</th>
            <th style="width:18%">Tipo</th>
            <th style="width:16%">Fase</th>
            <th style="width:18%">Responsável</th>
            <th style="width:8%">Entrada</th>
            <th style="width:8%">Situação</th>
        </tr>
        @forelse ($processos as $p)
        <tr>
            <td>{{ $p->processo_numero }}</td>
            <td>{{ $p->requerente }}</td>
            <td>{{ $p->tipoProcesso->nome ?? '—' }}</td>
            <td><span class="badge" style="background-color: {{ $p->faseAtual->cor ?? '#64748b' }}">{{ $p->faseAtual->nome ?? '—' }}</span></td>
            <td>{{ $p->responsavelTecnico->nome ?? '—' }}</td>
            <td>{{ $p->data_entrada?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $p->situacao_label }}</td>
        </tr>
        @empty
        <tr><td colspan="7">Nenhum processo encontrado.</td></tr>
        @endforelse
    </table>

    <div class="rodape">
        Sistema de Processos Administrativos — Prefeitura Municipal de Rio Grande da Serra/SP — Gerado em {{ $gerado_em }}
    </div>

</body>
</html>
