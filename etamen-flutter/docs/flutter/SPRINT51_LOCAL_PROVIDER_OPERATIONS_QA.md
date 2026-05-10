# Sprint 51 - Local Provider Operations QA

Sprint 51 adds limited provider-facing operational pages inside the same Flutter app.

## Sprint 52 Completion Note

Sprint 51 ended with the Flutter provider operations gate blocked because full emulator screenshots were not completed for every provider type.

Sprint 52 completed that gate:

- doctor, hospital, radiology, pharmacy, lab, gym, coach, and limited staff QA screenshots exist.
- provider workspace switching was fixed.
- coach packages quick action now opens a real operation page.
- Arabic mojibake in provider/hospital context UI was fixed.
- final local APK was rebuilt and installed on emulator.
- final Sprint 52 decision: `LOCAL_PROVIDER_OPERATIONS_MVP_ACCEPTED`.

Full QA report:

```text
etamen-flutter/docs/flutter/SPRINT52_PROVIDER_OPERATIONS_QA_COMPLETION.md
```

This is local-only work against:

```text
http://10.0.2.2:8000/api/v1
```

It does not approve staging, Hostinger, production, public launch, or real-phone readiness.

## Flutter Pages Added

Generic provider operations pages:

- `ProviderOperationListPage`
- `ProviderOperationDetailsPage`

Quick actions from `ProviderDashboardPage` now route to real operational pages when a workspace-scoped backend endpoint exists.

Unsupported actions still show a safe "later" message and do not crash.

## Provider Types Covered

- doctor appointments
- hospital appointments, departments, doctors
- radiology orders
- pharmacy orders/products read-only
- lab orders/catalog read-only
- gym bookings/plans/classes
- coach bookings/availability/session types/packages

## Permission Behavior

Flutter hides or limits actions based on backend-provided dashboard permissions for UX only.

The backend remains authoritative:

- provider id
- provider type
- staff membership
- permissions
- status transitions

If a user lacks a manage permission, details pages show a no-permission message instead of pretending the action is available.

## Local QA Status

Automated checks completed during implementation:

- `flutter analyze`: PASS
- `flutter test test/workspaces_test.dart`: PASS
- `flutter test`: PASS, 187 tests
- `flutter build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local`: PASS

Build note:

- Gradle/Java temp/cache were redirected to `E:\EtamenBuildCache\...` because the existing `D:\gradle_home` volume was full.

APK paths:

```text
I:\Etamen\.tmp\etamen-local-provider-operations.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-provider-operations.apk
```

Emulator QA completed:

- doctor owner login
- Account workspace section
- workspace switcher
- doctor provider dashboard
- doctor appointments operational list
- doctor appointment details operational page

```text
I:\Etamen\.tmp\sprint51-local-provider-operations\
```

Important QA limitation:

- Hospital/radiology/pharmacy/lab/gym/coach owner pages were verified by backend tests and Flutter routing/model tests, but not fully screenshot-tested on the emulator in this pass.
- A hung local Laravel server caused temporary API timeouts on the emulator; restarting `php artisan serve` restored health/login.
- The provider operation route was fixed to encode section keys safely in Flutter routes, for example `doctor__appointments`, then decode internally before API calls.

## Security Notes

Flutter provider pages render only API response fields returned by workspace-scoped provider operation endpoints.

The UI also filters obvious sensitive keys such as:

- path
- config
- secret
- token
- admin notes

This is an additional display guard only. The backend must still prevent leaks.

## Remaining Blockers

- Full emulator QA screenshots are still required for every provider owner workspace before claiming full Sprint 51 acceptance.
- Provider result upload UI is not implemented.
- Pharmacy/lab operations remain intentionally conservative/read-only.
- This is not a full provider portal.
