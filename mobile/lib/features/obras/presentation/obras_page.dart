import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../../dashboard/data/dashboard_api.dart';
import '../data/obras_api.dart';
import '../models/obra.dart';
import 'obra_detalhe_page.dart';

/// Lista paginada de obras com busca (debounce) e filtro de status.
/// Paginação por botão "Carregar mais" (mais simples e estável que scroll
/// infinito).
class ObrasPage extends ConsumerStatefulWidget {
  const ObrasPage({super.key, this.statusInicial});

  /// Filtro de status já aplicado ao abrir (ex.: atalho do Dashboard).
  final int? statusInicial;

  @override
  ConsumerState<ObrasPage> createState() => _ObrasPageState();
}

class _ObrasPageState extends ConsumerState<ObrasPage> {
  final _busca = TextEditingController();
  Timer? _debounce;

  final List<Obra> _obras = [];
  int _paginaAtual = 0;
  bool _temMais = false;
  int _total = 0;
  int? _statusId;

  bool _carregando = false;
  bool _carregandoMais = false;
  Object? _erro;
  Object? _erroMais;

  /// Descarta respostas antigas quando a busca/filtro muda no meio do caminho.
  int _requisicao = 0;

  /// Chip do filtro inicial, para rolá-lo até ficar visível.
  final _chipInicial = GlobalKey();

  @override
  void initState() {
    super.initState();
    _statusId = widget.statusInicial;
    _recarregar();
    if (_statusId != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        final chip = _chipInicial.currentContext;
        if (chip != null) Scrollable.ensureVisible(chip, alignment: 0.5);
      });
    }
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _busca.dispose();
    super.dispose();
  }

  Future<void> _recarregar() async {
    final id = ++_requisicao;
    setState(() {
      _carregando = true;
      _erro = null;
      _erroMais = null;
    });
    try {
      final pagina = await ref
          .read(obrasApiProvider)
          .listar(page: 1, search: _busca.text, statusId: _statusId);
      if (!mounted || id != _requisicao) return;
      setState(() {
        _obras
          ..clear()
          ..addAll(pagina.obras);
        _paginaAtual = pagina.currentPage;
        _temMais = pagina.hasMore;
        _total = pagina.total;
      });
    } catch (e) {
      if (mounted && id == _requisicao) setState(() => _erro = e);
    } finally {
      if (mounted && id == _requisicao) setState(() => _carregando = false);
    }
  }

  Future<void> _carregarMais() async {
    final id = _requisicao;
    setState(() {
      _carregandoMais = true;
      _erroMais = null;
    });
    try {
      final pagina = await ref
          .read(obrasApiProvider)
          .listar(
            page: _paginaAtual + 1,
            search: _busca.text,
            statusId: _statusId,
          );
      if (!mounted || id != _requisicao) return;
      setState(() {
        _obras.addAll(pagina.obras);
        _paginaAtual = pagina.currentPage;
        _temMais = pagina.hasMore;
        _total = pagina.total;
      });
    } catch (e) {
      if (mounted && id == _requisicao) setState(() => _erroMais = e);
    } finally {
      if (mounted) setState(() => _carregandoMais = false);
    }
  }

  void _aoDigitar(String _) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 600), _recarregar);
    setState(() {}); // mostra/oculta o botão de limpar
  }

  void _buscarAgora() {
    _debounce?.cancel();
    FocusScope.of(context).unfocus();
    _recarregar();
  }

  void _limparBusca() {
    _busca.clear();
    _buscarAgora();
  }

  void _filtrarStatus(int? id) {
    if (id == _statusId) return;
    _statusId = id;
    _recarregar();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
          child: TextField(
            controller: _busca,
            onChanged: _aoDigitar,
            onSubmitted: (_) => _buscarAgora(),
            textInputAction: TextInputAction.search,
            decoration: InputDecoration(
              hintText: 'Buscar obra pela descrição',
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
        _FiltroStatus(
          selecionado: _statusId,
          onSelecionar: _filtrarStatus,
          chaveInicial: widget.statusInicial == null ? null : _chipInicial,
        ),
        Expanded(child: _conteudo()),
      ],
    );
  }

  Widget _conteudo() {
    if (_carregando && _obras.isEmpty) return const LoadingView();
    if (_erro != null && _obras.isEmpty) {
      return ErrorView(erro: _erro!, onRetry: _recarregar);
    }

    return RefreshIndicator(
      onRefresh: _recarregar,
      child: _obras.isEmpty
          ? ListView(
              // ListView para o "puxar para atualizar" funcionar vazio
              children: const [
                SizedBox(height: 80),
                EmptyView(
                  mensagem: 'Nenhuma obra encontrada.',
                  icone: Icons.apartment_outlined,
                  detalhe: 'Tente outra busca ou outro filtro de status.',
                ),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.fromLTRB(12, 4, 12, 16),
              itemCount: _obras.length + 2,
              itemBuilder: (context, i) {
                if (i == 0) {
                  return _Contador(total: _total, atualizando: _carregando);
                }
                if (i == _obras.length + 1) return _rodape();
                return _ObraCard(obra: _obras[i - 1]);
              },
            ),
    );
  }

  Widget _rodape() {
    if (_erroMais != null) {
      return Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            Text(mensagemDeErro(_erroMais!), textAlign: TextAlign.center),
            TextButton(
              onPressed: _carregarMais,
              child: const Text('Tentar novamente'),
            ),
          ],
        ),
      );
    }
    if (!_temMais) return const SizedBox(height: 8);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Center(
        child: _carregandoMais
            ? const CircularProgressIndicator()
            : OutlinedButton.icon(
                onPressed: _carregarMais,
                icon: const Icon(Icons.expand_more),
                label: const Text('Carregar mais'),
              ),
      ),
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
          total == 1 ? '1 obra' : '${Fmt.inteiro(total)} obras',
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

/// Chips de status. Usa a lista de status do dashboard (id, nome, cor),
/// sem precisar de endpoint novo. Se o dashboard não carregou, some.
class _FiltroStatus extends ConsumerWidget {
  const _FiltroStatus({
    required this.selecionado,
    required this.onSelecionar,
    this.chaveInicial,
  });

  final int? selecionado;
  final ValueChanged<int?> onSelecionar;

  /// Chave do chip do filtro inicial (abertura a partir do Dashboard).
  final GlobalKey? chaveInicial;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final status = ref.watch(dashboardProvider).valueOrNull?.status ?? const [];
    if (status.isEmpty) return const SizedBox(height: 4);

    return SizedBox(
      height: 48,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        children: [
          Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              label: const Text('Todas'),
              selected: selecionado == null,
              onSelected: (_) => onSelecionar(null),
            ),
          ),
          for (final s in status)
            Padding(
              key: s.id == selecionado ? chaveInicial : null,
              padding: const EdgeInsets.only(right: 8),
              child: ChoiceChip(
                label: Text('${s.nome} (${s.total})'),
                selected: selecionado == s.id,
                onSelected: (_) => onSelecionar(s.id),
              ),
            ),
        ],
      ),
    );
  }
}

class _ObraCard extends StatelessWidget {
  const _ObraCard({required this.obra});

  final Obra obra;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute<void>(
            builder: (_) =>
                ObraDetalhePage(id: obra.id, titulo: obra.descricao),
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1 — Descrição
              Text(
                obra.descricao,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: tema.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                  height: 1.25,
                ),
              ),
              const SizedBox(height: 10),
              // 2 — Status · 3 — Percentual executado
              Row(
                children: [
                  Expanded(
                    child: Align(
                      alignment: Alignment.centerLeft,
                      child: obra.status == null
                          ? const SizedBox.shrink()
                          : StatusChip(
                              nome: obra.status!.nome,
                              cor: obra.status!.cor,
                            ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    Fmt.percentual(obra.percentualExecutado),
                    style: tema.textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: tema.colorScheme.primary,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              BarraPercentual(
                percentual: obra.percentualExecutado,
                altura: 8,
                mostrarTexto: false,
              ),
              const SizedBox(height: 12),
              // 4 — Contratado · 5 — Medido
              Row(
                children: [
                  Expanded(
                    child: _Valor(
                      rotulo: 'Contratado',
                      valor: Fmt.moeda(obra.valorContratado),
                    ),
                  ),
                  Expanded(
                    child: _Valor(
                      rotulo: 'Medido',
                      valor: Fmt.moeda(obra.valorMedido),
                      alinhamento: CrossAxisAlignment.end,
                    ),
                  ),
                ],
              ),
              // Secundário
              if (obra.endereco != null) ...[
                const SizedBox(height: 8),
                LinhaIcone(
                  icone: Icons.place_outlined,
                  texto: obra.endereco!,
                  maxLinhas: 1,
                ),
              ],
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
