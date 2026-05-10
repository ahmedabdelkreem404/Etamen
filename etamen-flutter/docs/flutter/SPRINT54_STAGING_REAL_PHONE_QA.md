# Sprint 54 - Staging Real Phone QA

Date: 2026-05-11

Scope: staging APK and real Android phone gate against:

```text
https://etamen.inolty.com/api/v1
```

## Decision

```text
STAGING_ACCESS_BLOCKED
```

The staging APK/phone gate was not run to acceptance because backend deployment access is blocked and the current staging API is incomplete.

## Backend Access Result

Safe SSH attempt failed:

```text
Permission denied (publickey,password).
```

No staging backend update, migration, seed, cache clear, storage link, or readiness fix was performed.

## Staging API Baseline

Evidence:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\baseline-http.json
```

Important results:

- `/api/v1/system/health`: 200.
- `/api/v1/system/readiness`: 401 with JSON accept, 500 default/browser style.
- `/api/v1/doctors`: 200 with one doctor.
- `/api/v1/payment-methods`: 200 but empty.
- `/api/v1/hospitals`: 404.
- `/api/v1/radiology/scans`: 200 but empty.
- `/api/v1/gyms`: 404.
- `/api/v1/coaches`: 404.

This backend state cannot support the Sprint 54 real-phone gate.

## APK Result

Sprint 54 staging APK was built as an artifact, but it was not installed or accepted through phone QA.

Required target remains:

```text
I:\Etamen\.tmp\etamen-staging-real-phone.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-real-phone.apk
```

SHA-256:

```text
98D596E703B45BF4E47C8943E2931542A3BD65594DA1C7B9F95A37A5966BD338
```

Reason not accepted as a passed phone artifact:

- staging backend was not deployed.
- required staging demo data is missing.
- payment methods are empty.
- provider workspaces are not verifiable.

## Phone Result

Known test phone from Sprint 53:

- Infinix X657C
- Android 10

Sprint 54 staging real phone checks:

| Gate | Result |
| --- | --- |
| Auth/session/logout | NOT RUN; staging demo login/data not ready. |
| Doctor proof upload/admin accept | NOT RUN; payment methods empty and admin/deploy access blocked. |
| Provider workspace dashboards | NOT RUN; staging backend routes/data not current enough. |
| Limited staff guard | NOT RUN; demo credentials unavailable and API gate blocked. |
| Radiology/gym/coach smoke | HTTP only; radiology empty, gym/coach 404. |

## Security Sweep

Evidence:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\security-sweep.json
```

Public hardening checks:

- `/.env`: 404.
- `/composer.json`: 404.
- `/storage`: 404.
- `/vendor`: 404.
- `/database`: 404.

No raw secret content was observed.

## Screenshots

No Sprint 54 real-phone screenshots were captured because the gate stopped before APK install/phone QA.

Diagnostics root:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\
```

## Tests And Build

Local code verification:

- Backend `php artisan test`: PASS, 254 tests / 2092 assertions.
- Backend `git diff --check`: PASS.
- Flutter `flutter pub get`: PASS.
- Flutter `dart format .`: PASS, 0 files changed.
- Flutter `flutter analyze`: PASS.
- Flutter `flutter test`: PASS, 187 tests.
- Flutter staging `android-arm` debug APK build: PASS.

This is not a staging phone acceptance result. The APK points to staging, but the backend access/data blockers still prevent a valid phone gate.

## Remaining Blockers

- Restore staging deployment access.
- Deploy latest backend.
- Fix readiness.
- Seed/verify staging demo data.
- Activate Vodafone Cash and InstaPay.
- Verify staging provider workspaces.
- Build and install a staging APK.
- Complete real phone doctor proof upload/admin accept.

## Next Sprint Recommendation

Run an access-first Sprint 55. Do not spend time on APK QA until `/payment-methods`, readiness, demo login, and provider workspace endpoints are confirmed healthy on staging.
