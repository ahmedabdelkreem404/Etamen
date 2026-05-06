# Etamen Android Release Checklist

Sprint 25 prepares Android for pilot builds without committing signing secrets.

## Current Native Status

| Item | Status | Notes |
| --- | --- | --- |
| App label | Ready | `Etamen` in `android/app/src/main/AndroidManifest.xml`. |
| Application id | Ready for pilot | `com.etamen.etamen_app`. |
| Android embedding | Ready | Flutter embedding v2 metadata is present. |
| Internet permission | Ready | `android.permission.INTERNET` is present in main manifest. |
| Image/file permissions | Minimal | `image_picker` is used without broad storage permissions in main manifest. |
| Splash foundation | Ready | Launch background uses Etamen cream color `#FEFDCF`. |
| App icon | Partial | Still uses default launcher icon. Replace before public launch. |
| Cleartext traffic | Local only by backend URL | Use HTTPS for staging/pilot. Do not ship pilot builds pointing to local HTTP. |
| NDK | Ready on this workstation | Pinned to installed `27.0.12077973` for plugin compatibility. |
| Signing | Blocker for Play Store | Release currently uses debug signing for pilot/internal builds only. |

## Debug APK For Real Device Smoke

```powershell
.\scripts\project_flutter.ps1 build apk --debug `
  --dart-define=ETAMEN_API_BASE_URL=http://YOUR_LAN_IP:8000/api/v1 `
  --dart-define=ETAMEN_ENV=local
```

Install:

```powershell
adb install -r build\app\outputs\flutter-apk\app-debug.apk
```

## Release APK

Use this only after selecting the correct backend:

```powershell
.\scripts\project_flutter.ps1 build apk --release `
  --dart-define=ETAMEN_API_BASE_URL=https://api.etamen.example/api/v1 `
  --dart-define=ETAMEN_ENV=production `
  --dart-define=ETAMEN_SUPPORT_EMAIL=support@etamen.example
```

## App Bundle

```powershell
.\scripts\project_flutter.ps1 build appbundle --release `
  --dart-define=ETAMEN_API_BASE_URL=https://api.etamen.example/api/v1 `
  --dart-define=ETAMEN_ENV=production
```

## Signing Note

No real release keystore was generated in Sprint 25. Before Play Store or external pilot distribution:

1. Generate/store a private Android signing key outside git.
2. Configure Gradle signing from ignored local properties or CI secrets.
3. Confirm `git diff --check` and secret scans show no keystore files, passwords, aliases, or signing properties committed.

## Before Pilot APK Sharing

- Confirm API URL points to staging/pilot, not `10.0.2.2` or a LAN IP.
- Confirm `APP_DEBUG=false` on the backend.
- Confirm manual payment methods are configured.
- Confirm support contacts are configured.
- Run the E2E test plan on at least one real Android device.
