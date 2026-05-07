# Sprint 27 APK Testing Notes

## Sprint 28 Build Notes

Sprint 28 used the same debug APK flow. Rebuild after seed/data fixes:

```powershell
cd I:\Etamen\etamen-flutter
.\scripts\project_flutter.ps1 build apk --debug --target-platform android-arm64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local
```

For the Android emulator, an x64 debug APK can also be built and installed:

```powershell
.\scripts\project_flutter.ps1 build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local
adb -s emulator-5554 install -r build\app\outputs\flutter-apk\app-debug.apk
```

After `php artisan migrate:fresh`, clear app data before relogin:

```powershell
adb -s emulator-5554 shell pm clear com.etamen.etamen_app
```

Demo login:

- Email: `pilot.patient@example.test`
- Password: `Password1234`

## Emulator Build Used For Walkthrough

Use Android x64 for the currently running emulator:

```powershell
cd I:\Etamen\etamen-flutter
.\scripts\project_flutter.ps1 build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local
```

Install:

```powershell
adb -s emulator-5554 install -r build\app\outputs\flutter-apk\app-debug.apk
```

## Real Android Device Build

Use ARM64 for most physical Android devices:

```powershell
cd I:\Etamen\etamen-flutter
.\scripts\project_flutter.ps1 build apk --debug --target-platform android-arm64 --dart-define=ETAMEN_API_BASE_URL=http://YOUR_LAN_IP:8000/api/v1 --dart-define=ETAMEN_ENV=local
```

For an emulator, keep `10.0.2.2`. For a physical phone, never use `127.0.0.1` or `10.0.2.2`; use the backend machine LAN IP.

Install:

```powershell
adb install -r build\app\outputs\flutter-apk\app-debug.apk
```

## Sprint 27 Result Template

- Build command: `.\scripts\project_flutter.ps1 build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local`
- Result: PASS.
- APK path: `I:/Etamen/etamen-flutter/build/app/outputs/flutter-apk/app-debug.apk`
- APK size after x64 build: 71,172,981 bytes.
- Device installed on: `emulator-5554`.
- Backend URL: `http://10.0.2.2:8000/api/v1`.
- Notes: Installed successfully, launched, logged in, and Home teal refresh was verified on emulator.

Additional ARM64 debug build:

- Build command: `.\scripts\project_flutter.ps1 build apk --debug --target-platform android-arm64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local`
- Result: PASS.
- APK path: `I:/Etamen/etamen-flutter/build/app/outputs/flutter-apk/app-debug.apk`
- APK size after ARM64 build: 71,173,076 bytes.
- Notes: This build is suitable for ARM64 physical-device installation, but local physical devices should use a LAN IP backend URL instead of `10.0.2.2`.

## Common Failures

- `تعذر الاتصال بالسيرفر`: wrong API base URL for the device type, backend not running, firewall blocked, or Laravel bound only to localhost.
- Gradle disk-space failure: clear old build folders or emulator/app artifacts.
- APK install failure: uninstall older package if signatures differ.
- File upload failure: verify Android image picker permissions and backend file upload limits.

## Signing

Release signing is not configured in this repository and must not be committed as secrets. Use debug APKs for supervised local QA only.
