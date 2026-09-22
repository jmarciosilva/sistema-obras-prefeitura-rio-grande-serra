# Obras RGS — app executivo (Flutter)

Aplicativo somente consulta para Prefeito e Secretário de Obras, consumindo a API `/api/v1` do sistema Laravel deste repositório.

```bash
flutter pub get
flutter run                                                    # API de produção (padrão)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000   # Android Emulator + php artisan serve local
flutter test
```

Contexto, endpoints e pendências: ver seção **FASE 8 — Aplicativo Mobile Executivo** no [README principal](../README.md).
