import 'package:flutter/material.dart';

import '../../core/config/app_config.dart';
import '../utils/formatters.dart';

/// Cabeçalho institucional padrão de todas as telas.
/// Telas internas trocam o [titulo] (ex.: "Detalhe da obra") e mantêm o
/// contexto institucional no [subtitulo].
class InstitucionalAppBar extends StatelessWidget
    implements PreferredSizeWidget {
  const InstitucionalAppBar({
    super.key,
    this.titulo = AppConfig.appName,
    this.subtitulo = 'Prefeitura de Rio Grande da Serra · Secretaria de Obras',
    this.actions,
  });

  final String titulo;
  final String subtitulo;
  final List<Widget>? actions;

  static const _altura = kToolbarHeight + 8;

  @override
  Size get preferredSize => const Size.fromHeight(_altura);

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final cor = tema.colorScheme.onPrimary;
    return AppBar(
      toolbarHeight: _altura,
      actions: actions,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            titulo,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: tema.textTheme.titleLarge?.copyWith(
              color: cor,
              fontWeight: FontWeight.w700,
            ),
          ),
          Text(
            subtitulo,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: tema.textTheme.bodySmall?.copyWith(
              color: cor.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }
}

/// Barra de progresso do percentual executado.
/// O texto mostra o valor real; a barra é limitada a 0–100%.
class BarraPercentual extends StatelessWidget {
  const BarraPercentual({
    super.key,
    required this.percentual,
    this.altura = 10,
    this.mostrarTexto = true,
  });

  final double percentual;
  final double altura;
  final bool mostrarTexto;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final valor = (percentual.isFinite ? percentual : 0).clamp(0, 100) / 100;

    final barra = ClipRRect(
      borderRadius: BorderRadius.circular(altura),
      child: LinearProgressIndicator(
        value: valor.toDouble(),
        minHeight: altura,
        backgroundColor: tema.colorScheme.surfaceContainerHighest,
      ),
    );

    if (!mostrarTexto) return barra;

    return Row(
      children: [
        Expanded(child: barra),
        const SizedBox(width: 12),
        SizedBox(
          width: 64,
          child: Text(
            Fmt.percentual(percentual.isFinite ? percentual : 0),
            textAlign: TextAlign.right,
            style: tema.textTheme.titleSmall?.copyWith(
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
    );
  }
}

/// Selo do status da obra. A cor vem da API, mas o texto usa a cor do Theme
/// (contraste garantido mesmo com cores claras, ex.: amarelo) e o ponto
/// colorido reforça a identificação sem depender só da cor.
class StatusChip extends StatelessWidget {
  const StatusChip({super.key, required this.nome, this.cor});

  final String nome;
  final String? cor;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final c = Fmt.cor(cor);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: c.withValues(alpha: 0.45)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(color: c, shape: BoxShape.circle),
          ),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              nome,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: tema.textTheme.labelMedium?.copyWith(
                color: tema.colorScheme.onSurface,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Card com título de seção.
class SecaoCard extends StatelessWidget {
  const SecaoCard({
    super.key,
    required this.titulo,
    required this.child,
    this.icone,
  });

  final String titulo;
  final Widget child;
  final IconData? icone;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                if (icone != null) ...[
                  Icon(icone, size: 20, color: tema.colorScheme.primary),
                  const SizedBox(width: 8),
                ],
                Expanded(
                  child: Text(
                    titulo,
                    style: tema.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            child,
          ],
        ),
      ),
    );
  }
}

/// Título de seção fora de card (ex.: "RESUMO GERAL" no Dashboard),
/// usado para separar blocos sem empilhar cards.
class TituloSecao extends StatelessWidget {
  const TituloSecao(this.texto, {super.key, this.acao});

  final String texto;

  /// Widget opcional à direita (ex.: link "Ver todas").
  final Widget? acao;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.fromLTRB(4, 20, 4, 8),
      child: Row(
        children: [
          Expanded(
            child: Text(
              texto.toUpperCase(),
              style: tema.textTheme.labelLarge?.copyWith(
                color: tema.colorScheme.primary,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.8,
              ),
            ),
          ),
          ?acao,
        ],
      ),
    );
  }
}

/// Linha "rótulo ........ valor".
class LinhaInfo extends StatelessWidget {
  const LinhaInfo({
    super.key,
    required this.rotulo,
    required this.valor,
    this.destaque = false,
  });

  final String rotulo;
  final String valor;
  final bool destaque;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 4,
            child: Text(
              rotulo,
              style: tema.textTheme.bodyMedium?.copyWith(
                color: tema.colorScheme.onSurfaceVariant,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 6,
            child: Text(
              valor,
              textAlign: TextAlign.right,
              style:
                  (destaque
                          ? tema.textTheme.titleSmall
                          : tema.textTheme.bodyMedium)
                      ?.copyWith(fontWeight: destaque ? FontWeight.w700 : null),
            ),
          ),
        ],
      ),
    );
  }
}

/// Ícone discreto + texto (endereço, processo, vigência...).
class LinhaIcone extends StatelessWidget {
  const LinhaIcone({
    super.key,
    required this.icone,
    required this.texto,
    this.maxLinhas,
  });

  final IconData icone;
  final String texto;
  final int? maxLinhas;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final cor = tema.colorScheme.onSurfaceVariant;
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(top: 1),
            child: Icon(icone, size: 18, color: cor),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              texto,
              maxLines: maxLinhas,
              overflow: maxLinhas == null ? null : TextOverflow.ellipsis,
              style: tema.textTheme.bodyMedium?.copyWith(color: cor),
            ),
          ),
        ],
      ),
    );
  }
}

/// Bloco de execução financeira: percentual em destaque, barra e valores.
/// Mesmo formato no Dashboard, no detalhe da obra e no detalhe do contrato.
class ResumoFinanceiro extends StatelessWidget {
  const ResumoFinanceiro({
    super.key,
    required this.percentual,
    required this.contratado,
    required this.medido,
    required this.saldo,
  });

  final double percentual;
  final double contratado;
  final double medido;
  final double saldo;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              Fmt.percentual(percentual.isFinite ? percentual : 0),
              style: tema.textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.w700,
                color: tema.colorScheme.primary,
              ),
            ),
            const SizedBox(width: 8),
            Padding(
              padding: const EdgeInsets.only(bottom: 5),
              child: Text('executado', style: tema.textTheme.bodyMedium),
            ),
          ],
        ),
        const SizedBox(height: 8),
        BarraPercentual(
          percentual: percentual,
          altura: 10,
          mostrarTexto: false,
        ),
        const SizedBox(height: 12),
        LinhaInfo(
          rotulo: 'Valor contratado',
          valor: Fmt.moeda(contratado),
          destaque: true,
        ),
        LinhaInfo(
          rotulo: 'Valor medido',
          valor: Fmt.moeda(medido),
          destaque: true,
        ),
        LinhaInfo(rotulo: 'Saldo', valor: Fmt.moeda(saldo), destaque: true),
      ],
    );
  }
}

/// Estado vazio dentro de uma seção (ex.: "Nenhuma medição registrada.").
class VazioInline extends StatelessWidget {
  const VazioInline({
    super.key,
    required this.texto,
    this.icone = Icons.inbox_outlined,
  });

  final String texto;
  final IconData icone;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final cor = tema.colorScheme.onSurfaceVariant;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icone, size: 20, color: tema.colorScheme.outline),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              texto,
              style: tema.textTheme.bodyMedium?.copyWith(color: cor),
            ),
          ),
        ],
      ),
    );
  }
}

/// Linha de medição: data (e contexto opcional) à esquerda, valor à direita.
class LinhaMedicao extends StatelessWidget {
  const LinhaMedicao({
    super.key,
    required this.data,
    required this.valor,
    this.contexto,
  });

  final DateTime? data;
  final double valor;

  /// Ex.: "Contrato 03/2023" no detalhe da obra.
  final String? contexto;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(
            Icons.event_note_outlined,
            size: 20,
            color: tema.colorScheme.onSurfaceVariant,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(Fmt.data(data), style: tema.textTheme.bodyLarge),
                if (contexto != null)
                  Text(
                    contexto!,
                    style: tema.textTheme.bodySmall?.copyWith(
                      color: tema.colorScheme.onSurfaceVariant,
                    ),
                  ),
              ],
            ),
          ),
          Text(
            Fmt.moeda(valor),
            style: tema.textTheme.titleSmall?.copyWith(
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
