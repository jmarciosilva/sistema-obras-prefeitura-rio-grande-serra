import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../../../core/auth/auth_state.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key, this.aviso});

  /// Mensagem vinda da sessão (ex.: "Sua sessão expirou").
  final String? aviso;

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _senha = TextEditingController();
  bool _enviando = false;
  bool _mostrarSenha = false;
  String? _erro;

  @override
  void dispose() {
    _email.dispose();
    _senha.dispose();
    super.dispose();
  }

  static String _mensagemLogin(ApiException e) => switch (e.statusCode) {
    401 => 'E-mail ou senha inválidos.',
    403 => 'Usuário inativo. Procure o administrador do sistema.',
    422 => 'Informe um e-mail válido e a senha.',
    _ => e.message,
  };

  Future<void> _entrar() async {
    FocusScope.of(context).unfocus();
    if (!_form.currentState!.validate()) return;

    setState(() {
      _enviando = true;
      _erro = null;
    });
    try {
      await ref
          .read(authProvider.notifier)
          .login(_email.text.trim(), _senha.text);
      // Sucesso: a raiz do app troca para o Dashboard.
    } on ApiException catch (e) {
      if (mounted) setState(() => _erro = _mensagemLogin(e));
    } catch (_) {
      if (mounted) setState(() => _erro = 'Não foi possível entrar agora.');
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final aviso = _erro ?? widget.aviso;

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Form(
                key: _form,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Icon(
                      Icons.apartment_rounded,
                      size: 64,
                      color: tema.colorScheme.primary,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Obras RGS',
                      textAlign: TextAlign.center,
                      style: tema.textTheme.headlineSmall,
                    ),
                    Text(
                      'Prefeitura de Rio Grande da Serra\nSecretaria de Obras',
                      textAlign: TextAlign.center,
                      style: tema.textTheme.bodyMedium?.copyWith(
                        color: tema.colorScheme.onSurfaceVariant,
                      ),
                    ),
                    const SizedBox(height: 32),
                    if (aviso != null) ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: tema.colorScheme.errorContainer,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          aviso,
                          style: TextStyle(
                            color: tema.colorScheme.onErrorContainer,
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],
                    TextFormField(
                      controller: _email,
                      enabled: !_enviando,
                      keyboardType: TextInputType.emailAddress,
                      autofillHints: const [AutofillHints.email],
                      textInputAction: TextInputAction.next,
                      decoration: const InputDecoration(
                        labelText: 'E-mail',
                        prefixIcon: Icon(Icons.email_outlined),
                        border: OutlineInputBorder(),
                      ),
                      validator: (v) {
                        final valor = (v ?? '').trim();
                        if (valor.isEmpty) return 'Informe o e-mail.';
                        if (!valor.contains('@')) return 'E-mail inválido.';
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _senha,
                      enabled: !_enviando,
                      obscureText: !_mostrarSenha,
                      autofillHints: const [AutofillHints.password],
                      textInputAction: TextInputAction.done,
                      onFieldSubmitted: (_) => _entrar(),
                      decoration: InputDecoration(
                        labelText: 'Senha',
                        prefixIcon: const Icon(Icons.lock_outline),
                        border: const OutlineInputBorder(),
                        suffixIcon: IconButton(
                          tooltip: _mostrarSenha
                              ? 'Ocultar senha'
                              : 'Mostrar senha',
                          icon: Icon(
                            _mostrarSenha
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                          ),
                          onPressed: () =>
                              setState(() => _mostrarSenha = !_mostrarSenha),
                        ),
                      ),
                      validator: (v) =>
                          (v ?? '').isEmpty ? 'Informe a senha.' : null,
                    ),
                    const SizedBox(height: 24),
                    FilledButton(
                      onPressed: _enviando ? null : _entrar,
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(52),
                      ),
                      child: _enviando
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                              ),
                            )
                          : const Text('Entrar'),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
