import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../data/contratos_api.dart';
import '../models/contrato.dart';
import 'contrato_detalhe_page.dart';
import 'situacao_chip.dart';

/// Lista paginada de contratos — mesmo padrão da tela de Obras:
/// busca com debounce, filtro por chips e botão "Carregar mais".
class ContratosPage extends ConsumerStatefulWidget {
  const ContratosPage({super.key, this.situacaoInicial});

  /// Filtro já aplicado ao abrir (ex.: atalho do alerta no Dashboard).
  final SituacaoVigencia? situacaoInicial;

  @override
  ConsumerState<ContratosPage> createState() => _ContratosPageState();
}

class _ContratosPageState extends ConsumerState<ContratosPage> {
  final _busca = TextEditingController();
  Timer? _debounce;

  final List<Contrato> _contratos = [];
  int _paginaAtual = 0;
  bool _temMais = false;
  int _total = 0;
  SituacaoVigencia? _situacao;

  bool _carregando = false;
  bool _carregandoMais = false;
  Object? _erro;
  Object? _erroMais;

  /// Descarta respostas antigas quando a busca/filtro muda no meio do caminho.
  int _requisicao = 0;

  @override
  void initState() {
    super.initState();
    _situacao = widget.situacaoInicial;
    _recarregar();
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
          .read(contratosApiProvider)
          .listar(page: 1, search: _busca.text, situacao: _situacao);
      if (!mounted || id != _requisicao) return;
      setState(() {
        _contratos
          ..clear()
          ..addAll(pagina.contratos);
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
          .read(contratosApiProvider)
          .listar(
            page: _paginaAtual + 1,
            search: _busca.text,
            situacao: _situacao,
          );
      if (!mounted || id != _requisicao) return;
      setState(() {
        _contratos.addAll(pagina.contratos);
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

  void _filtrar(SituacaoVigencia? situacao) {
    if (situacao == _situacao) return;
    _situacao = situacao;
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
    if (_carregando && _contratos.isEmpty) return const LoadingView();
    if (_erro != null && _contratos.isEmpty) {
      return ErrorView(erro: _erro!, onRetry: _recarregar);
    }

    return RefreshIndicator(
      onRefresh: _recarregar,
      child: _contratos.isEmpty
          ? ListView(
              // ListView para o "puxar para atualizar" funcionar vazio
              children: const [
                SizedBox(height: 80),
                EmptyView(
                  mensagem: 'Nenhum contrato encontrado.',
                  icone: Icons.receipt_long_outlined,
                  detalhe: 'Tente outra busca ou outro filtro de situação.',
                ),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.fromLTRB(12, 4, 12, 16),
              itemCount: _contratos.length + 2,
              itemBuilder: (context, i) {
                if (i == 0) {
                  return _Contador(total: _total, atualizando: _carregando);
                }
                if (i == _contratos.length + 1) return _rodape();
                return _ContratoCard(contrato: _contratos[i - 1]);
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
