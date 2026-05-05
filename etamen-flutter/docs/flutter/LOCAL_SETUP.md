# Etamen Flutter Local Setup

Sprint 14 uses the clean Flutter app in this folder: `etamen-flutter`.

## Install Packages

```bash
flutter pub get
```

## Run Laravel Backend

From the backend folder:

```bash
cd ../etamen-backend
php artisan serve --host=0.0.0.0 --port=8000
```

## Configure API Base URL

The app reads:

```bash
--dart-define=ETAMEN_API_BASE_URL=<url>
```

Defaults:

- Android emulator: `http://10.0.2.2:8000/api/v1`
- iOS simulator: `http://127.0.0.1:8000/api/v1`
- Physical device: use your machine LAN IP, for example `http://192.168.1.20:8000/api/v1`

Examples:

```bash
flutter run --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1
flutter run --dart-define=ETAMEN_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

## Useful Checks

```bash
dart format .
flutter analyze
flutter test
```

## Common Issues

- `401`: token is invalid or expired; the app clears it and returns to login.
- `422`: backend validation errors; show field messages.
- `429`: too many requests; wait and retry later.
- Network error on Android emulator: use `10.0.2.2`, not `localhost`.
- Network error on physical device: bind Laravel to `0.0.0.0` and use the computer LAN IP.

## Security Notes

- Do not place backend secrets, Paymob secrets, AI keys, FCM server keys, or admin tokens in Flutter.
- Tokens are stored through `flutter_secure_storage`.
- Flutter must not send `patient_user_id`, `user_id`, trusted price, appointment status, or payment verification flags.
