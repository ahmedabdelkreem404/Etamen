# Etamen Flutter Environment Setup

Sprint 25 verifies that Flutter configuration is driven by compile-time values and does not require secrets in the mobile app.

## Required Dart Defines

| Key | Required | Purpose |
| --- | --- | --- |
| `ETAMEN_API_BASE_URL` | yes | Versioned backend API base URL, ending with `/api/v1`. |
| `ETAMEN_ENV` | yes for staging/pilot | `local`, `staging`, or `production`. |
| `ETAMEN_SUPPORT_EMAIL` | recommended | Support email shown in Account and Support pages. |
| `ETAMEN_SUPPORT_PHONE` | optional | Support phone shown only if configured. |
| `ETAMEN_SUPPORT_WHATSAPP_URL` | optional | Support WhatsApp link shown only if configured. |

Do not pass backend secrets, Paymob secrets, AI provider keys, FCM server keys, database credentials, admin tokens, HMAC secrets, or private storage paths to Flutter.

## Local Android Emulator

Use `10.0.2.2` because Android emulator `localhost` points to the emulator itself:

```powershell
.\scripts\project_flutter.ps1 run `
  --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 `
  --dart-define=ETAMEN_ENV=local
```

## Real Android Device On Local Network

Run Laravel bound to all interfaces:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Find the workstation LAN IP, then run:

```powershell
.\scripts\project_flutter.ps1 run `
  --dart-define=ETAMEN_API_BASE_URL=http://YOUR_LAN_IP:8000/api/v1 `
  --dart-define=ETAMEN_ENV=local
```

If the app says "تعذر الاتصال بالسيرفر", check Windows Firewall, the phone and PC being on the same Wi-Fi, and that the backend health endpoint opens from the phone browser.

## iOS Simulator

```bash
flutter run \
  --dart-define=ETAMEN_API_BASE_URL=http://127.0.0.1:8000/api/v1 \
  --dart-define=ETAMEN_ENV=local
```

## Staging

```powershell
.\scripts\project_flutter.ps1 run `
  --dart-define=ETAMEN_API_BASE_URL=https://staging-api.etamen.example/api/v1 `
  --dart-define=ETAMEN_ENV=staging `
  --dart-define=ETAMEN_SUPPORT_EMAIL=support@etamen.example
```

## Pilot / Production

```powershell
.\scripts\project_flutter.ps1 run `
  --dart-define=ETAMEN_API_BASE_URL=https://api.etamen.example/api/v1 `
  --dart-define=ETAMEN_ENV=production `
  --dart-define=ETAMEN_SUPPORT_EMAIL=support@etamen.example
```

## Local Verified Test Account

For the local seeded/dev environment used during Sprint 25 emulator smoke testing:

- Email: `codexpatient@testlocal.com`
- Password: `Password123`

This account is local-only and should not be used for staging or pilot.

## Secret Ownership

All sensitive runtime credentials stay on the backend:

- Paymob secret/HMAC keys.
- DeepSeek/Gemini or any AI provider keys.
- FCM/APNS server credentials.
- Database credentials.
- Admin tokens.
- Private file storage roots.

Flutter only receives public environment values and authenticated user tokens issued by the backend.
