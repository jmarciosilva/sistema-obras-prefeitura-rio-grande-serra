<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Relatório de Processos Administrativos — Prefeitura de Rio Grande da Serra</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e293b;
            background: #fff;
            margin-left: 50px;
            margin-right: 50px;
            margin-top: 20px;
            margin-bottom: 32px;
        }

        /* ── CAPA — idêntica ao relatório de Obras, mesma identidade visual ── */
        .capa {
            background: #1e3a5f;
            color: white;
            padding: 40px 58px 34px;
            margin: -20px -50px 24px -50px;
        }

        .capa-barra {
            height: 6px;
            background: #3b82f6;
            margin: -40px -58px 30px -58px;
        }

        .capa h1 {
            font-size: 19px;
            font-weight: bold;
            letter-spacing: 0.8px;
            margin-bottom: 5px;
        }

        .capa h2 {
            font-size: 11px;
            font-weight: normal;
            opacity: 0.8;
            margin-bottom: 3px;
        }

        .capa .meta {
            font-size: 8px;
            opacity: 0.6;
            margin-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 10px;
        }

        .capa .meta span {
            margin-right: 20px;
        }

        /* ── SEÇÃO ─────────────────────────────────────────────── */
        .secao {
            margin-bottom: 20px;
        }

        .secao-titulo {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        /* ── KPI CARDS ──────────────────────────────────────────── */
        .kpi-wrap {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-bottom: 16px;
        }

        .kpi-cell {
            display: table-cell;
            width: 25%;
            padding: 10px 12px;
            border-radius: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .kpi-lbl {
            font-size: 7.5px;
            margin-bottom: 4px;
        }

        .kpi-val {
            font-size: 13px;
            font-weight: bold;
        }

        /* ── GRADE 2 COLUNAS ────────────────────────────────────── */
        .g2 {
            display: table;
            width: 100%;
        }

        .g2l {
            display: table-cell;
            width: 42%;
            vertical-align: top;
            padding-right: 12px;
        }

        .g2r {
            display: table-cell;
            width: 58%;
            vertical-align: top;
            padding-left: 12px;
            border-left: 1px solid #e2e8f0;
        }

        /* ── TÍTULO DE GRÁFICO ──────────────────────────────────── */
        .gt {
            font-size: 8px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 8px;
            text-align: center;
        }

        /* ══════════════════════════════════════════════════════════
           GRÁFICO DE PIZZA — mesma técnica HTML pura do relatório de Obras
           (tabela de uma linha, cada célula é uma fatia colorida com
           largura proporcional ao valor — compatível com dompdf, sem JS)
        ══════════════════════════════════════════════════════════ */
        .pizza-wrap {
            width: 100%;
            margin-bottom: 4px;
        }

        .pizza-bar-lg {
            display: table;
            width: 100%;
            height: 26px;
            border-radius: 4px;
            overflow: hidden;
        }

        .pizza-fatia {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            color: white;
            overflow: hidden;
        }

        .pizza-centro {
            text-align: center;
            margin-top: 4px;
            font-size: 8.5px;
            font-weight: bold;
            color: #1e293b;
        }

        .pizza-centro span {
            font-size: 7px;
            color: #64748b;
            font-weight: normal;
        }

        .leg {
            margin-top: 6px;
        }

        .leg-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .leg-cor-cell {
            display: table-cell;
            width: 11px;
            vertical-align: middle;
        }

        .leg-cor {
            width: 9px;
            height: 9px;
            border-radius: 2px;
            display: inline-block;
        }

        .leg-txt {
            display: table-cell;
            font-size: 7.5px;
            color: #334155;
            vertical-align: middle;
            padding-left: 3px;
        }

        /* ── TABELA DE DADOS ────────────────────────────────────── */
        .dtable {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .dtable thead th {
            background: #1e40af;
            color: white;
            padding: 5px 7px;
            text-align: left;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .dtable tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .dtable tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .dtable tbody td {
            padding: 4px 7px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .dtable tbody td.tc {
            white-space: nowrap;
            text-align: center;
        }

        /* ── BADGE ──────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 8px;
            font-size: 7px;
            font-weight: bold;
            color: white;
        }

        /* ── RODAPÉ ─────────────────────────────────────────────── */
        .rodape {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 18px;
            background: #1e3a5f;
            color: rgba(255, 255, 255, 0.75);
            font-size: 7px;
            padding: 4px 50px;
            display: table;
            width: 100%;
        }

        .re {
            display: table-cell;
            text-align: left;
        }

        .rd {
            display: table-cell;
            text-align: right;
        }

        .pb {
            page-break-after: always;
        }

        .nb {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    {{-- ══════════════════════════════════════
     MACRO: renderiza pizza HTML pura — mesma técnica do relatório de Obras
══════════════════════════════════════ --}}
    @php
        function pizzaProcessos(array $fatias, string $tamanho = 'lg'): string
        {
            $total = array_sum(array_column($fatias, 'valor'));
            if ($total <= 0) {
                return '<div style="text-align:center;color:#94a3b8;font-size:8px;padding:12px">Sem dados</div>';
            }

            $html = '<div class="pizza-wrap"><div class="pizza-bar-lg">';

            foreach ($fatias as $f) {
                $pct = round(($f['valor'] / $total) * 100, 1);
                $cor = htmlspecialchars($f['cor'], ENT_QUOTES);
                $label = $pct >= 8 ? "{$pct}%" : '';
                $html .= "<div class=\"pizza-fatia\" style=\"width:{$pct}%;background:{$cor}\">{$label}</div>";
            }

            $html .= '</div>';
            $html .= "<div class=\"pizza-centro\">{$total} <span>processo(s)</span></div>";
            $html .= '</div>';

            $html .= '<div class="leg">';
            foreach ($fatias as $f) {
                $pct = round(($f['valor'] / $total) * 100, 1);
                $cor = htmlspecialchars($f['cor'], ENT_QUOTES);
                $lbl = htmlspecialchars($f['label'], ENT_QUOTES);
                $html .=
                    '<div class="leg-row">' .
                    "<div class=\"leg-cor-cell\"><div class=\"leg-cor\" style=\"background:{$cor}\"></div></div>" .
                    "<div class=\"leg-txt\"><strong>{$lbl}</strong> &mdash; {$f['valor']} ({$pct}%)</div>" .
                    '</div>';
            }
            $html .= '</div>';

            return $html;
        }
    @endphp

    {{-- RODAPÉ FIXO --}}
    <div class="rodape">
        <div class="re">Prefeitura de Rio Grande da Serra — Relatório de Processos Administrativos • {{ $gerado_em }}</div>
        <div class="rd">Gerado por: {{ $gerado_por }}</div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     CAPA
══════════════════════════════════════════════════════════ --}}
    <div class="capa">
        <div class="capa-barra"></div>
        <h1>RELATÓRIO DE PROCESSOS ADMINISTRATIVOS</h1>
        <h2>Prefeitura Municipal de Rio Grande da Serra — SP</h2>
        <h2>Secretaria de Obras e Planejamento</h2>
        <div class="meta">
            <span>Gerado em: {{ $gerado_em }}</span>
            <span>Por: {{ $gerado_por }}</span>
            <span>Processos: {{ $totalFiltrado }}</span>
            @if (!empty($dataInicio) || !empty($dataFim))
                <span>
                    Período:
                    {{ !empty($dataInicio) ? \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') : 'início' }}
                    até
                    {{ !empty($dataFim) ? \Carbon\Carbon::parse($dataFim)->format('d/m/Y') : 'hoje' }}
                </span>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     KPIs
══════════════════════════════════════════════════════════ --}}
    <div class="secao nb">
        <div class="secao-titulo">INDICADORES GERAIS</div>
        <div class="kpi-wrap">
            <div class="kpi-cell" style="background:#dbeafe;border:1px solid #bfdbfe">
                <div class="kpi-lbl" style="color:#1e40af">Processos no Filtro</div>
                <div class="kpi-val" style="color:#1e40af;font-size:22px">{{ $totalFiltrado }}</div>
            </div>
            <div class="kpi-cell" style="background:#dcfce7;border:1px solid #bbf7d0">
                <div class="kpi-lbl" style="color:#15803d">Abertos</div>
                <div class="kpi-val" style="color:#15803d;font-size:22px">{{ $abertosFiltrado }}</div>
            </div>
            <div class="kpi-cell" style="background:#fee2e2;border:1px solid #fecaca">
                <div class="kpi-lbl" style="color:#b91c1c">Com Pendência</div>
                <div class="kpi-val" style="color:#b91c1c;font-size:22px">{{ $comPendencia->count() }}</div>
            </div>
            <div class="kpi-cell" style="background:#fef9c3;border:1px solid #fde047">
                <div class="kpi-lbl" style="color:#854d0e">Fases Distintas</div>
                <div class="kpi-val" style="color:#854d0e;font-size:22px">{{ $porFase->count() }}</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     PROCESSOS POR FASE — pizza grande + tabela por tipo ao lado
══════════════════════════════════════════════════════════ --}}
    @php
        $fFase = $porFase->map(fn($f) => ['label' => $f['nome'], 'valor' => (int) $f['total'], 'cor' => $f['cor']])->toArray();
        $totalTipo = $porTipo->sum('total');
    @endphp
    <div class="secao nb">
        <div class="secao-titulo">PROCESSOS POR FASE E POR TIPO</div>
        <div class="g2">
            <div class="g2l">
                <div class="gt" style="text-align:left;margin-bottom:10px">Distribuição por Fase</div>
                {!! pizzaProcessos($fFase) !!}
            </div>
            <div class="g2r">
                <div class="gt" style="text-align:left;margin-bottom:10px">Por Tipo de Serviço</div>
                <table class="dtable">
                    <thead>
                        <tr><th>Tipo</th><th class="tc" style="width:60px">Qtd.</th><th class="tc" style="width:50px">%</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($porTipo as $t)
                        <tr>
                            <td>{{ $t['nome'] }}</td>
                            <td class="tc">{{ $t['total'] }}</td>
                            <td class="tc">{{ $totalTipo > 0 ? number_format(($t['total']/$totalTipo)*100, 1) : 0 }}%</td>
                        </tr>
                        @empty
                        <tr><td colspan="3">Nenhum processo encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="pb"></div>

    {{-- ══════════════════════════════════════════════════════════
     PENDÊNCIAS
══════════════════════════════════════════════════════════ --}}
    <div class="secao nb">
        <div class="secao-titulo">PROCESSOS COM PENDÊNCIA</div>
        <table class="dtable">
            <thead>
                <tr>
                    <th style="width:12%">Processo</th>
                    <th style="width:22%">Requerente</th>
                    <th style="width:18%">Fase</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     LISTA GERAL
══════════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">LISTA DE PROCESSOS</div>
        @if ($totalProcessosOriginal > $processos->count())
            <p style="color:#b91c1c; font-size: 8px; margin: 0 0 6px;">
                ⚠ Mostrando os {{ $processos->count() }} processos mais recentes de um total de {{ $totalProcessosOriginal }}
                encontrados com os filtros aplicados. Para a lista completa, use a exportação em Excel.
            </p>
        @endif
        <table class="dtable">
            <thead>
                <tr>
                    <th style="width:11%">Processo</th>
                    <th style="width:18%">Requerente</th>
                    <th style="width:17%">Tipo</th>
                    <th style="width:15%">Fase</th>
                    <th style="width:17%">Responsável</th>
                    <th class="tc" style="width:8%">Entrada</th>
                    <th class="tc" style="width:8%">Situação</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($processos as $p)
                <tr>
                    <td>{{ $p->processo_numero }}</td>
                    <td>{{ $p->requerente }}</td>
                    <td>{{ $p->tipoProcesso->nome ?? '—' }}</td>
                    <td><span class="badge" style="background-color: {{ $p->faseAtual->cor ?? '#64748b' }}">{{ $p->faseAtual->nome ?? '—' }}</span></td>
                    <td>{{ $p->responsavelTecnico->nome ?? '—' }}</td>
                    <td class="tc">{{ $p->data_entrada?->format('d/m/Y') ?? '—' }}</td>
                    <td class="tc">{{ $p->situacao_label }}</td>
                </tr>
                @empty
                <tr><td colspan="7">Nenhum processo encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
