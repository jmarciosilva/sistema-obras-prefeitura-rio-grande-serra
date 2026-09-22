import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/cache/sync_state.dart';
import '../../../shared/utils/formatters.dart';
import '../../../shared/utils/texto.dart';
import '../../../shared/widgets/aviso_offline.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../data/contratos_api.dart';
import '../models/contrato.dart';
import 'contrato_detalhe_page.dart';
import 'situacao_chip.dart';

/// Lista de contratos com busca e filtro por situação da vigência.
///
/// Offline-first: a lista COMPLETA fica no cache do aparelho (sincronizada
/// em segundo plano); busca e filtro são aplicados localmente.
class ContratosPage extends ConsumerStatefulWidget {
  const ContratosPage({super.key, this.situacaoInicial});

  /// Filtro já aplicado ao abrir (ex.: atalho do alerta no Dashboard).
  final SituacaoVigencia? situacaoInicial;

  @override
  ConsumerState<ContratosPage> createState() => _ContratosPageState();
}

class _ContratosPageState extends ConsumerState<ContratosPage> {
  final _busca = TextEditingController();
  SituacaoVigencia? _situacao;

  @override
  void initState() {
    super.initState();
    _situacao = widget.situacaoInicial;
  }

  @override
  void dispose() {
    _busca.dispose();
    super.dispose();
  }

  void _limparBusca() {
    _busca.clear();
    FocusScope.of(context).unfocus();
    setState(() {});
  }

  void _filtrar(SituacaoVigencia? situacao) =>
      setState(() => _situacao = situacao);

  Future<void> _atualizar() async {
    final ok = await ref.read(contratosProvider.notifier).atualizar();
    if (!ok && mounted) avisarSemConexao(context);
  }

  /// Mesmas regras da API: situação calculada pelo servidor; busca por
  /// número, obra, razão social ou nome fantasia.
  List<Contrato> _aplicarFiltros(List<Contrato> todos) => [
    for (final c in todos)
      if ((_situacao == null || c.situacao == _situacao) &&
          contemBusca(_busca.text, [
            c.numeroContratoAno,
            c.obra?.descricao,
            c.empresa?.razaoSocial,
            c.empresa?.nomeFantasia,
          ]))
        c,
  ];

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
          child: TextField(
            controller: _busca,
            onChanged: (_) => setState(() {}),
            onSubmitted: (_) => FocusScope.of(context).unfocus(),
            textInputAction: TextInputAction.search,
            decoration: InputDecoration(
              hintText: 'Buscar por número, obra ou empresa',
              prefixIcon: const Icon(Icons.search),
              suffixIcon: _busca.text.isEmpty
                  ? null
                  : IconButton(
                      tooltip: 'Limpar busca',
                      icon: const Icon(Icons.clear),
                      onPressed: _limparBusca,
                    ),
              isDense: true,
            ),
          ),
        ),
        _FiltroSituacao(selecionada: _situacao, onSelecionar: _filtrar),
        Expanded(child: _conteudo()),
      ],
    );
  }

  Widget _conteudo() {
    final contratos = ref.watch(contratosProvider);
    return contratos.when(
      skipLoadingOnRefresh: !contratos.hasError,
      loading: () => const LoadingView(),
      error: (e, _) =>
          ErrorView(erro: e, onRetry: () => ref.invalidate(contratosProvider)),
      data: (dados) {
        final lista = _aplicarFiltros(dados.valor);
        final aviso = AvisoOffline(atualizadoEm: dados.atualizadoEm);
        // Cache na tela enquanto a sincronização roda
        final atualizando = dados.doCache && !ref.watch(syncProvider).offline;

        return RefreshIndicator(
          onRefresh: _atualizar,
          child: lista.isEmpty
              ? ListView(
                  // ListView para o "puxar para atualizar" funcionar vazio
                  padding: const EdgeInsets.fromLTRB(12, 4, 12, 16),
                  children: [
                    aviso,
                    const SizedBox(height: 64),
                    const EmptyView(
                      mensagem: 'Nenhum contrato encontrado.',
                      icone: Icons.receipt_long_outlined,
                      detalhe: 'Tente outra busca ou outro filtro de situação.',
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(12, 4, 12, 16),
                  itemCount: lista.length + 1,
                  itemBuilder: (context, i) {
                    if (i == 0) {
                      return Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          aviso,
                          _Contador(
                            total: lista.length,
                            atualizando: atualizando,
                          ),
                        ],
                      );
                    }
                    return _ContratoCard(contrato: lista[i - 1]);
                  },
                ),
        );
      },
    );
  }
}

class _Contador extends StatelessWidget {
  const _Contador({required this.total, required this.atualizando});

  final int total;
  final bool atualizando;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.fromLTRB(4, 4, 4, 8),
    child: Row(
      children: [
        Text(
          total == 1 ? '1 contrato' : '${Fmt.inteiro(total)} contratos',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        if (atualizando) ...[
          const SizedBox(width: 8),
          const SizedBox(
            width: 12,
            height: 12,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
        ],
      ],
    ),
  );
}

class _FiltroSituacao extends StatelessWidget {
  const _FiltroSituacao({
    required this.selecionada,
    required this.onSelecionar,
  });

  final SituacaoVigencia? selecionada;
  final ValueChanged<SituacaoVigencia?> onSelecionar;

  static const _opcoes = <(String, SituacaoVigencia?)>[
    ('Todos', null),
    ('Vigentes', SituacaoVigencia.vigente),
    ('Vencendo', SituacaoVigencia.venceEmBreve),
    ('Vencidos', SituacaoVigencia.vencido),
  ];

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        children: [
          for (final (rotulo, situacao) in _opcoes)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: ChoiceChip(
                label: Text(rotulo),
                selected: selecionada == situacao,
                onSelected: (_) => onSelecionar(situacao),
              ),
            ),
        ],
      ),
    );
  }
}

class _ContratoCard extends StatelessWidget {
  const _ContratoCard({required this.contrato});

  final Contrato contrato;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final c = contrato;
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute<void>(
            builder: (_) => ContratoDetalhePage(id: c.id),
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1 — Número · 2 — Situação
              Row(
                children: [
                  Expanded(
                    child: Text(
                      c.titulo,
                      style: tema.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  SituacaoChip(situacao: c.situacao),
                ],
              ),
              // 3 — Empresa
              if (c.empresa != null) ...[
                const SizedBox(height: 6),
                Text(
                  c.empresa!.nomeExibicao,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: tema.textTheme.bodyLarge?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
              // 4 — Obra
              if (c.obra != null)
                LinhaIcone(
                  icone: Icons.apartment_outlined,
                  texto: c.obra!.descricao,
                  maxLinhas: 1,
                ),
              const SizedBox(height: 12),
              // 5 — Percentual executado
              BarraPercentual(percentual: c.percentualExecutado, altura: 8),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: _Valor(
                      rotulo: 'Contratado',
                      valor: Fmt.moeda(c.valorContrato),
                    ),
                  ),
                  Expanded(
                    child: _Valor(
                      rotulo: 'Medido',
                      valor: Fmt.moeda(c.valorMedido),
                      alinhamento: CrossAxisAlignment.end,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              LinhaIcone(
                icone: Icons.event_outlined,
                texto: c.vigenciaContrato == null
                    ? 'Vigência não informada'
                    : 'Vigência até ${Fmt.data(c.vigenciaContrato)}',
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Valor extends StatelessWidget {
  const _Valor({
    required this.rotulo,
    required this.valor,
    this.alinhamento = CrossAxisAlignment.start,
  });

  final String rotulo;
  final String valor;
  final CrossAxisAlignment alinhamento;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Column(
      crossAxisAlignment: alinhamento,
      children: [
        Text(
          rotulo,
          style: tema.textTheme.bodySmall?.copyWith(
            color: tema.colorScheme.onSurfaceVariant,
          ),
        ),
        FittedBox(
          fit: BoxFit.scaleDown,
          child: Text(
            valor,
            style: tema.textTheme.titleSmall?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
}
