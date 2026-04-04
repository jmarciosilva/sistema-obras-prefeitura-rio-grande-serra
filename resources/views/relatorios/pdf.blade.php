<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Relatório de Obras — Prefeitura de Rio Grande da Serra</title>
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

        /* ── CAPA ──────────────────────────────────────────────── */
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

        /* ── GRADE 4 COLUNAS ────────────────────────────────────── */
        .g4 {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .g4c {
            display: table-cell;
            width: 25%;
            vertical-align: top;
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

        /* ── GRADE 3 COLUNAS ────────────────────────────────────── */
        .g3 {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
        }

        .g3c {
            display: table-cell;
            width: 33%;
            vertical-align: top;
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
       GRÁFICO DE PIZZA — implementado em HTML puro
       Técnica: tabela de uma linha, cada célula é uma fatia colorida
       com altura fixa e largura proporcional ao valor.
       Abaixo fica a legenda com quadrados coloridos.
    ══════════════════════════════════════════════════════════ */

        /* Container da pizza */
        .pizza-wrap {
            width: 100%;
            margin-bottom: 4px;
        }

        /* A barra "pizza" em si: uma linha com células coloridas */
        .pizza-bar {
            display: table;
            width: 100%;
            height: 20px;
            /* altura das fatias */
            border-radius: 4px;
            overflow: hidden;
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

        /* Círculo interno (efeito donut visual) */
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

        /* Legenda */
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
        }

        .tr {
            text-align: right;
        }

        .tc {
            text-align: center;
        }

        /* ── BARRA PROGRESSO ────────────────────────────────────── */
        .bw {
            background: #e2e8f0;
            border-radius: 3px;
            height: 6px;
            display: inline-block;
        }

        .bf {
            height: 6px;
            border-radius: 3px;
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

        /* ── TORRE (barras verticais em tabela) ─────────────────── */
        .torre-wrap {
            width: 100%;
        }

        .torre-body {
            display: table;
            width: 100%;
            height: 100px;
            vertical-align: bottom;
        }

        .torre-col {
            display: table-cell;
            vertical-align: bottom;
            text-align: center;
            padding: 0 2px;
        }

        .torre-barra-ext {
            background: #bfdbfe;
            border-radius: 2px 2px 0 0;
            width: 100%;
            display: block;
        }

        .torre-barra-int {
            display: block;
            border-radius: 2px 2px 0 0;
            width: 80%;
            margin: 0 auto;
        }

        .torre-label {
            font-size: 6.5px;
            color: #64748b;
            font-weight: bold;
            margin-top: 2px;
        }

        .torre-pct {
            font-size: 6px;
            color: #475569;
        }

        /* ── CORES ──────────────────────────────────────────────── */
        .cv {
            color: #15803d;
        }

        .ca {
            color: #1d4ed8;
        }

        .cr {
            color: #dc2626;
            font-weight: bold;
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
     MACRO: renderiza pizza HTML pura
     compatível com dompdf
══════════════════════════════════════ --}}
    @php
        /**
         * Gera uma pizza horizontal em HTML puro (compatível dompdf).
         *
         * @param  array  $fatias  [['label'=>'', 'valor'=>N, 'cor'=>'#hex'], ...]
         * @param  string $tamanho 'sm' | 'lg'
         * @param  bool   $mostrarPct exibir % dentro das fatias
         * @return string HTML
         */
        function pizza(array $fatias, string $tamanho = 'sm', bool $mostrarPct = true): string
        {
            $total = array_sum(array_column($fatias, 'valor'));
            if ($total <= 0) {
                return '<div style="text-align:center;color:#94a3b8;font-size:8px;padding:12px">Sem dados</div>';
            }

            $classBarra = $tamanho === 'lg' ? 'pizza-bar-lg' : 'pizza-bar';
            $html = "<div class=\"pizza-wrap\">";
            $html .= "<div class=\"{$classBarra}\">";

            foreach ($fatias as $f) {
                $pct = round(($f['valor'] / $total) * 100, 1);
                $cor = htmlspecialchars($f['cor'], ENT_QUOTES);
                // mostra % só se a fatia for larga o suficiente
                $label = $mostrarPct && $pct >= 8 ? "{$pct}%" : '';
                $html .= "<div class=\"pizza-fatia\" style=\"width:{$pct}%;background:{$cor}\">{$label}</div>";
            }

            $html .= '</div>';

            // total centralizado abaixo da barra
            $html .= "<div class=\"pizza-centro\">{$total} <span>total</span></div>";
            $html .= '</div>';

            // legenda
            $html .= "<div class=\"leg\">";
            foreach ($fatias as $f) {
                $pct = round(($f['valor'] / $total) * 100, 1);
                $cor = htmlspecialchars($f['cor'], ENT_QUOTES);
                $lbl = htmlspecialchars($f['label'], ENT_QUOTES);
                $html .=
                    "<div class=\"leg-row\">" .
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
        <div class="re">Prefeitura de Rio Grande da Serra — Relatório de Obras Públicas • {{ $gerado_em }}</div>
        <div class="rd">Gerado por: {{ $gerado_por }}</div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     CAPA
══════════════════════════════════════════════════════════ --}}
    <div class="capa">
        <div class="capa-barra"></div>
        <h1>RELATÓRIO DE OBRAS PÚBLICAS</h1>
        <h2>Prefeitura Municipal de Rio Grande da Serra — SP</h2>
        <h2>Secretaria de Obras e Planejamento</h2>
        <div class="meta">
            <span>Gerado em: {{ $gerado_em }}</span>
            <span>Por: {{ $gerado_por }}</span>
            <span>Obras: {{ $obras->count() }}</span>
            @if (!empty($filtros['data_inicio']) || !empty($filtros['data_fim']))
                <span>
                    Periodo:
                    {{ !empty($filtros['data_inicio']) ? \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') : 'inicio' }}
                    ate
                    {{ !empty($filtros['data_fim']) ? \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') : 'hoje' }}
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
                <div class="kpi-lbl" style="color:#1e40af">Total de Obras</div>
                <div class="kpi-val" style="color:#1e40af;font-size:22px">{{ $obras->count() }}</div>
            </div>
            <div class="kpi-cell" style="background:#dcfce7;border:1px solid #bbf7d0">
                <div class="kpi-lbl" style="color:#15803d">Valor Contratado</div>
                <div class="kpi-val" style="color:#15803d;font-size:11px">R$
                    {{ number_format($valorContratadoTotal, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-cell" style="background:#fef9c3;border:1px solid #fde047">
                <div class="kpi-lbl" style="color:#854d0e">Valor Medido</div>
                <div class="kpi-val" style="color:#854d0e;font-size:11px">R$
                    {{ number_format($valorMedidoTotal, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-cell" style="background:#f0fdf4;border:1px solid #bbf7d0">
                <div class="kpi-lbl" style="color:#166534">% Executado Geral</div>
                <div class="kpi-val"
                    style="font-size:22px;color:{{ $percentualGeral >= 80 ? '#15803d' : ($percentualGeral >= 40 ? '#b45309' : '#1d4ed8') }}">
                    {{ number_format($percentualGeral, 1) }}%
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     BLOCO DE PIZZAS — 4 colunas
     1. Por Status   2. Medido × Saldo   3. Vencimentos   4. Top Empresas
══════════════════════════════════════════════════════════ --}}
    @php
        /* ── PIZZA 1: STATUS ─────────────────────────── */
        $fStatus = $porStatus
            ->map(
                fn($s) => [
                    'label' => $s['nome'],
                    'valor' => (int) $s['total'],
                    'cor' => $s['cor'],
                ],
            )
            ->toArray();

        /* ── PIZZA 2: EXEC. FINANCEIRA (medido vs saldo) */
        $fExec = [];
        if ($valorMedidoTotal > 0) {
            $fExec[] = ['label' => 'Medido', 'valor' => (int) round($valorMedidoTotal / 1000), 'cor' => '#15803d'];
        }
        if ($saldoTotal > 0) {
            $fExec[] = ['label' => 'Saldo', 'valor' => (int) round($saldoTotal / 1000), 'cor' => '#93c5fd'];
        }
        if (empty($fExec)) {
            $fExec[] = ['label' => 'Sem dados', 'valor' => 1, 'cor' => '#e2e8f0'];
        }

        /* ── PIZZA 3: VENCIMENTOS ────────────────────── */
        $nVencidos = $contratosVencidos->count();
        $nVencendo = $contratosVencendo->count();
        $nContratos = \App\Models\Contrato::count();
        $nOk = max($nContratos - $nVencidos - $nVencendo, 0);
        $fVenc = array_values(
            array_filter([
                $nVencidos > 0 ? ['label' => 'Vencidos', 'valor' => $nVencidos, 'cor' => '#dc2626'] : null,
                $nVencendo > 0
                    ? ['label' => "Vencem {$vencimentoDias}d", 'valor' => $nVencendo, 'cor' => '#f59e0b']
                    : null,
                $nOk > 0 ? ['label' => 'Em dia', 'valor' => $nOk, 'cor' => '#15803d'] : null,
            ]),
        );
        if (empty($fVenc)) {
            $fVenc = [['label' => 'Sem contratos', 'valor' => 1, 'cor' => '#e2e8f0']];
        }

        /* ── PIZZA 4: TOP 5 EMPRESAS ─────────────────── */
        $coresEmp = ['#1e40af', '#7c3aed', '#b45309', '#0f766e', '#be123c'];
        $fEmp = $porEmpresa
            ->take(5)
            ->values()
            ->map(
                fn($e, $i) => [
                    'label' => \Illuminate\Support\Str::limit($e['empresa']->razao_social, 20),
                    'valor' => (int) round($e['valor_total'] / 1000),
                    'cor' => $coresEmp[$i] ?? '#94a3b8',
                ],
            )
            ->toArray();
        if (empty($fEmp)) {
            $fEmp = [['label' => 'Sem dados', 'valor' => 1, 'cor' => '#e2e8f0']];
        }
    @endphp

    <div class="secao nb">
        <div class="secao-titulo">GRAFICOS DE PIZZA — PANORAMA GERAL</div>
        <div class="g4">

            <div class="g4c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Por Status</div>
                {!! pizza($fStatus, 'sm') !!}
            </div>

            <div class="g4c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Medido x Saldo</div>
                {!! pizza($fExec, 'sm') !!}
                <div style="font-size:6.5px;color:#94a3b8;margin-top:4px;text-align:center">(em mil R$)</div>
            </div>

            <div class="g4c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Situacao Contratos</div>
                {!! pizza($fVenc, 'sm') !!}
            </div>

            <div class="g4c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Top Empresas</div>
                {!! pizza($fEmp, 'sm') !!}
                <div style="font-size:6.5px;color:#94a3b8;margin-top:4px;text-align:center">(em mil R$)</div>
            </div>

        </div>
    </div>

    <div class="pb"></div>

    {{-- ══════════════════════════════════════════════════════════
     PAGINA 2: PIZZA STATUS (grande) + TORRE EXECUCAO
══════════════════════════════════════════════════════════ --}}
    <div class="secao nb">
        <div class="secao-titulo">OBRAS POR STATUS — DETALHE</div>
        <div class="g2">

            <div class="g2l">
                <div class="gt" style="text-align:left;margin-bottom:10px">Pizza — Distribuicao por Status</div>
                {!! pizza($fStatus, 'lg') !!}

                {{-- segunda pizza: faixa de execucao --}}
                @php
                    $f0 = $execucaoFinanceira->filter(fn($i) => $i['percentual'] == 0)->count();
                    $f40 = $execucaoFinanceira
                        ->filter(fn($i) => $i['percentual'] > 0 && $i['percentual'] < 40)
                        ->count();
                    $f80 = $execucaoFinanceira
                        ->filter(fn($i) => $i['percentual'] >= 40 && $i['percentual'] < 80)
                        ->count();
                    $f100 = $execucaoFinanceira->filter(fn($i) => $i['percentual'] >= 80)->count();
                    $fFaixa = array_values(
                        array_filter([
                            $f0 > 0 ? ['label' => 'Nao iniciada', 'valor' => $f0, 'cor' => '#94a3b8'] : null,
                            $f40 > 0 ? ['label' => 'Menos de 40%', 'valor' => $f40, 'cor' => '#3b82f6'] : null,
                            $f80 > 0 ? ['label' => '40 a 79%', 'valor' => $f80, 'cor' => '#f59e0b'] : null,
                            $f100 > 0 ? ['label' => '80% ou mais', 'valor' => $f100, 'cor' => '#15803d'] : null,
                        ]),
                    );
                @endphp

                <div style="margin-top:16px">
                    <div class="gt" style="text-align:left;margin-bottom:8px">Pizza — Faixa de Execucao</div>
                    {!! pizza($fFaixa, 'lg') !!}
                </div>
            </div>

            <div class="g2r">
                <div class="gt" style="text-align:left;margin-bottom:10px">Torre — Contratado x Medido (Top 8 Obras)
                </div>

                {{-- TORRE em tabela HTML pura --}}
                @php
                    $top8 = $execucaoFinanceira->sortByDesc('valor_contratado')->take(8)->values();
                    $maxVC = $top8->max('valor_contratado') ?: 1;
                    $altMax = 90; // px máximo da barra
                @endphp

                {{-- linha das barras --}}
                <table style="width:100%;border-collapse:collapse;table-layout:fixed">
                    <tbody>
                        {{-- linha 1: percentuais acima --}}
                        <tr style="vertical-align:bottom">
                            @foreach ($top8 as $item)
                                @php
                                    $pct = (float) $item['percentual'];
                                    $cor = $pct >= 80 ? '#15803d' : ($pct >= 40 ? '#f59e0b' : '#3b82f6');
                                @endphp
                                <td
                                    style="text-align:center;padding:0 2px;font-size:6px;font-weight:bold;color:{{ $cor }};padding-bottom:1px">
                                    {{ $pct > 0 ? round($pct) . '%' : '' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- linha 2: barras (usando altura via padding-top) --}}
                        <tr style="vertical-align:bottom;height:{{ $altMax }}px;border-bottom:1px solid #cbd5e1">
                            @foreach ($top8 as $item)
                                @php
                                    $hC = $maxVC > 0 ? round(($item['valor_contratado'] / $maxVC) * $altMax) : 0;
                                    $hM = $maxVC > 0 ? round(($item['valor_medido'] / $maxVC) * $altMax) : 0;
                                    $pct = (float) $item['percentual'];
                                    $cor = $pct >= 80 ? '#15803d' : ($pct >= 40 ? '#f59e0b' : '#3b82f6');
                                @endphp
                                <td style="text-align:center;padding:0 3px;vertical-align:bottom">
                                    {{-- container da barra com posição relativa --}}
                                    <div style="position:relative;display:inline-block;width:100%">
                                        {{-- barra contratado (azul claro) --}}
                                        <div
                                            style="height:{{ $hC }}px;background:#bfdbfe;border-radius:2px 2px 0 0;width:100%">
                                            {{-- barra medido sobreposta (centralizada) --}}
                                            <div
                                                style="height:{{ $hM }}px;background:{{ $cor }};border-radius:2px 2px 0 0;width:70%;margin:0 auto;opacity:0.9">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                        </tr>

                        {{-- linha 3: rótulos O1..O8 --}}
                        <tr>
                            @foreach ($top8 as $idx => $item)
                                <td
                                    style="text-align:center;font-size:7px;font-weight:bold;color:#475569;padding-top:3px">
                                    O{{ $idx + 1 }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                {{-- legenda das cores --}}
                <div style="margin-top:6px;font-size:7px;color:#64748b">
                    <span
                        style="display:inline-block;width:10px;height:8px;background:#bfdbfe;border-radius:1px;margin-right:3px;vertical-align:middle"></span>Contratado
                    &nbsp;&nbsp;
                    <span
                        style="display:inline-block;width:10px;height:8px;background:#15803d;border-radius:1px;margin-right:3px;vertical-align:middle"></span>+80%
                    &nbsp;
                    <span
                        style="display:inline-block;width:10px;height:8px;background:#f59e0b;border-radius:1px;margin-right:3px;vertical-align:middle"></span>40-79%
                    &nbsp;
                    <span
                        style="display:inline-block;width:10px;height:8px;background:#3b82f6;border-radius:1px;margin-right:3px;vertical-align:middle"></span>&lt;40%
                </div>

                {{-- indice --}}
                <div style="margin-top:8px;border-top:1px solid #f1f5f9;padding-top:6px">
                    @foreach ($top8 as $idx => $item)
                        <div style="font-size:6.5px;color:#64748b;margin-bottom:2px;line-height:1.4">
                            <strong style="color:#334155">O{{ $idx + 1 }}:</strong>
                            {{ \Illuminate\Support\Str::limit($item['obra']->descricao, 52) }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     BLOCO 3 PIZZAS: FAIXA EXECUCAO + VENCIMENTOS + EMPRESAS
══════════════════════════════════════════════════════════ --}}
    <div class="secao nb">
        <div class="secao-titulo">ANALISE — EXECUCAO, VENCIMENTOS E EMPRESAS</div>
        <div class="g3">

            <div class="g3c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Faixa de Execucao</div>
                {!! pizza($fFaixa, 'sm') !!}
            </div>

            <div class="g3c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Situacao dos Contratos</div>
                {!! pizza($fVenc, 'sm') !!}
            </div>

            <div class="g3c" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                <div class="gt">Participacao por Empresa</div>
                {!! pizza($fEmp, 'sm') !!}
                <div style="font-size:6px;color:#94a3b8;margin-top:3px;text-align:center">(em mil R$, top 5)</div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     EVOLUCAO MENSAL: TORRE + PIZZA MENSAL
══════════════════════════════════════════════════════════ --}}
    @if ($evolucaoMensal->sum('valor') > 0)
        <div class="secao nb">
            <div class="secao-titulo">EVOLUCAO MENSAL — ULTIMOS 12 MESES</div>
            <div class="g2">

                {{-- TORRE MENSAL --}}
                <div class="g2l">
                    <div class="gt" style="text-align:left;margin-bottom:8px">Torre — Valor Medido por Mes</div>
                    @php
                        $maxMes = $evolucaoMensal->max('valor') ?: 1;
                        $altMes = 80;
                    @endphp

                    <table style="width:100%;border-collapse:collapse;table-layout:fixed">
                        <tbody>
                            {{-- pcts acima --}}
                            <tr style="vertical-align:bottom">
                                @foreach ($evolucaoMensal as $mes)
                                    @php
                                        $r = $maxMes > 0 ? $mes['valor'] / $maxMes : 0;
                                        $cor =
                                            $r >= 0.75
                                                ? '#15803d'
                                                : ($r >= 0.4
                                                    ? '#2563eb'
                                                    : ($r > 0
                                                        ? '#7dd3fc'
                                                        : '#e2e8f0'));
                                    @endphp
                                    <td
                                        style="text-align:center;padding:0 1px;font-size:5.5px;color:{{ $cor }};font-weight:bold;padding-bottom:1px">
                                        {{ $mes['valor'] > 0 ? 'R$' . number_format($mes['valor'] / 1000, 0) . 'k' : '' }}
                                    </td>
                                @endforeach
                            </tr>
                            {{-- barras --}}
                            <tr
                                style="vertical-align:bottom;height:{{ $altMes }}px;border-bottom:1px solid #cbd5e1">
                                @foreach ($evolucaoMensal as $mes)
                                    @php
                                        $h = $maxMes > 0 ? round(($mes['valor'] / $maxMes) * $altMes) : 0;
                                        $r = $maxMes > 0 ? $mes['valor'] / $maxMes : 0;
                                        $cor =
                                            $r >= 0.75
                                                ? '#15803d'
                                                : ($r >= 0.4
                                                    ? '#2563eb'
                                                    : ($r > 0
                                                        ? '#7dd3fc'
                                                        : '#e2e8f0'));
                                    @endphp
                                    <td style="text-align:center;padding:0 1px;vertical-align:bottom">
                                        <div
                                            style="height:{{ $h }}px;background:{{ $cor }};border-radius:2px 2px 0 0;width:100%">
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                            {{-- meses --}}
                            <tr>
                                @foreach ($evolucaoMensal as $mes)
                                    <td style="text-align:center;font-size:5.5px;color:#64748b;padding-top:2px">
                                        {{ $mes['mes'] }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- PIZZA MENSAL --}}
                <div class="g2r">
                    <div class="gt">Pizza — Participacao por Mes no Total</div>
                    @php
                        $coresMes = [
                            '#1e40af',
                            '#2563eb',
                            '#3b82f6',
                            '#60a5fa',
                            '#0f766e',
                            '#0d9488',
                            '#14b8a6',
                            '#65a30d',
                            '#ca8a04',
                            '#c2410c',
                            '#9333ea',
                            '#db2777',
                        ];
                        $fMes = $evolucaoMensal
                            ->filter(fn($m) => $m['valor'] > 0)
                            ->values()
                            ->map(
                                fn($m, $i) => [
                                    'label' => $m['mes'],
                                    'valor' => (int) round($m['valor'] / 1000),
                                    'cor' => $coresMes[$i % 12],
                                ],
                            )
                            ->toArray();
                    @endphp
                    {!! pizza($fMes, 'lg') !!}
                    <div style="font-size:6.5px;color:#94a3b8;margin-top:4px;text-align:center">(em mil R$)</div>
                </div>

            </div>
        </div>
    @endif

    <div class="pb"></div>

    {{-- ══════════════════════════════════════════════════════════
     EXECUCAO FINANCEIRA — TABELA
══════════════════════════════════════════════════════════ --}}
    <div class="secao">
        <div class="secao-titulo">EXECUCAO FINANCEIRA DETALHADA POR OBRA</div>
        <table class="dtable">
            <thead>
                <tr>
                    <th style="width:36%">Obra</th>
                    <th style="width:10%">Status</th>
                    <th class="tr" style="width:14%">Contratado (R$)</th>
                    <th class="tr" style="width:14%">Medido (R$)</th>
                    <th class="tr" style="width:12%">Saldo (R$)</th>
                    <th class="tc" style="width:14%">Execucao</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($execucaoFinanceira as $item)
                    @php
                        $o = $item['obra'];
                        $pct = min((float) $item['percentual'], 100);
                        $cor = $pct >= 80 ? '#15803d' : ($pct >= 40 ? '#b45309' : '#1d4ed8');
                        $bg = $pct >= 80 ? '#15803d' : ($pct >= 40 ? '#d97706' : '#3b82f6');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($o->descricao, 54) }}</strong>
                            @if ($o->processo_execucao)
                                <br><span style="color:#94a3b8;font-size:7px">Proc: {{ $o->processo_execucao }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $o->status->cor ?? '#64748b' }}">
                                {{ $o->status->nome ?? '—' }}
                            </span>
                        </td>
                        <td class="tr">{{ number_format($item['valor_contratado'], 2, ',', '.') }}</td>
                        <td class="tr cv">{{ number_format($item['valor_medido'], 2, ',', '.') }}</td>
                        <td class="tr {{ $item['saldo'] <= 0 ? 'cr' : '' }}">
                            {{ number_format($item['saldo'], 2, ',', '.') }}</td>
                        <td class="tc">
                            <div style="font-size:7.5px;font-weight:bold;color:{{ $cor }};margin-bottom:2px">
                                {{ number_format($pct, 1) }}%</div>
                            <div class="bw" style="width:78px">
                                <div class="bf"
                                    style="width:{{ $pct }}%;background:{{ $bg }}"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:#94a3b8;padding:12px">Nenhuma obra
                            encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     CONTRATOS VENCENDO
══════════════════════════════════════════════════════════ --}}
    @if ($contratosVencendo->isNotEmpty())
        <div class="secao nb">
            <div class="secao-titulo">CONTRATOS VENCENDO EM ATE {{ $vencimentoDias }} DIAS</div>
            <table class="dtable">
                <thead>
                    <tr>
                        <th style="width:12%">Contrato</th>
                        <th style="width:36%">Obra</th>
                        <th style="width:22%">Empresa</th>
                        <th class="tc" style="width:12%">Vigencia</th>
                        <th class="tc" style="width:8%">Dias</th>
                        <th class="tr" style="width:10%">Valor (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contratosVencendo as $item)
                        @php
                            $c = $item['contrato'];
                            $dias = $item['dias_restantes'];
                            $bg = $dias <= 10 ? '#fef2f2' : ($dias <= 30 ? '#fffbeb' : '#ffffff');
                            $dCor = $dias <= 10 ? '#dc2626' : ($dias <= 30 ? '#b45309' : '#1d4ed8');
                        @endphp
                        <tr style="background:{{ $bg }}">
                            <td><strong>{{ $c->numero_contrato_ano ?? '#' . $c->id }}</strong></td>
                            <td>{{ \Illuminate\Support\Str::limit($c->obra->descricao ?? '—', 50) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($c->empresa->razao_social ?? '—', 30) }}</td>
                            <td class="tc">{{ $c->vigencia_contrato?->format('d/m/Y') }}</td>
                            <td class="tc" style="color:{{ $dCor }};font-weight:bold">
                                {{ $dias }}</td>
                            <td class="tr">{{ number_format($c->valor_contrato ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- CONTRATOS VENCIDOS --}}
    @if ($contratosVencidos->isNotEmpty())
        <div class="secao nb">
            <div class="secao-titulo" style="color:#dc2626;border-color:#fca5a5">CONTRATOS VENCIDOS</div>
            <table class="dtable">
                <thead style="background:#dc2626">
                    <tr>
                        <th style="width:12%">Contrato</th>
                        <th style="width:40%">Obra</th>
                        <th style="width:24%">Empresa</th>
                        <th class="tc" style="width:12%">Venceu em</th>
                        <th class="tr" style="width:12%">Valor (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contratosVencidos as $c)
                        <tr style="background:#fef2f2">
                            <td style="color:#dc2626;font-weight:bold">{{ $c->numero_contrato_ano ?? '#' . $c->id }}
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($c->obra->descricao ?? '—', 55) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($c->empresa->razao_social ?? '—', 30) }}</td>
                            <td class="tc cr">{{ $c->vigencia_contrato?->format('d/m/Y') }}</td>
                            <td class="tr">{{ number_format($c->valor_contrato ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
     RANKING EMPRESAS + PIZZA EMPRESAS
══════════════════════════════════════════════════════════ --}}
    @if ($porEmpresa->isNotEmpty())
        <div class="secao nb">
            <div class="secao-titulo">RANKING POR EMPRESA CONTRATADA</div>
            <div class="g2">

                {{-- tabela --}}
                <div class="g2l">
                    @php $maxValEmp = $porEmpresa->max('valor_total') ?: 1; @endphp
                    <table class="dtable">
                        <thead>
                            <tr>
                                <th style="width:6%">#</th>
                                <th style="width:40%">Empresa</th>
                                <th class="tc" style="width:12%">Contr.</th>
                                <th class="tr" style="width:26%">Valor Total (R$)</th>
                                <th class="tc" style="width:16%">Part.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($porEmpresa as $i => $item)
                                @php $part = round($item['valor_total'] / $maxValEmp * 100); @endphp
                                <tr>
                                    <td class="tc" style="font-weight:bold;color:#64748b">{{ $i + 1 }}
                                    </td>
                                    <td><strong>{{ \Illuminate\Support\Str::limit($item['empresa']->razao_social, 36) }}</strong>
                                    </td>
                                    <td class="tc">{{ $item['total_contratos'] }}</td>
                                    <td class="tr ca">{{ number_format($item['valor_total'], 2, ',', '.') }}</td>
                                    <td class="tc">
                                        <div class="bw" style="width:65px">
                                            <div class="bf"
                                                style="width:{{ $part }}%;background:#3b82f6"></div>
                                        </div>
                                        <div style="font-size:6px;color:#64748b;margin-top:1px">{{ $part }}%
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- pizza empresas --}}
                <div class="g2r">
                    <div class="gt">Pizza — Participacao Financeira (Top 5)</div>
                    {!! pizza($fEmp, 'lg') !!}
                    <div style="font-size:6.5px;color:#94a3b8;margin-top:4px;text-align:center">(em mil R$)</div>
                </div>

            </div>
        </div>
    @endif

</body>

</html>
