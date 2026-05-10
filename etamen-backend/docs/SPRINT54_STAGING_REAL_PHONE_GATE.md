# Sprint 54 - Staging Real Phone Gate

Date: 2026-05-11

Scope: staging deployment and real Android phone gate for `https://etamen.inolty.com/api/v1`.

This report intentionally contains no SSH passwords, database credentials, APP_KEY, payment keys, tokens, or private file paths.

## Decision

```text
STAGING_ACCESS_BLOCKED
```

Sprint 54 could not proceed to deployment, migrations, staging seed data, APK install, or real phone proof upload because server access is still unavailable.

## Access And Deploy Result

Attempted safe non-interactive SSH:

```text
ssh -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && php -v"
```

Result:

```text
Permission denied (publickey,password).
```

No server files were changed. No server `.env` was read or printed. No database backup, migration, cache clear, seeder, composer install, or artisan command was run on staging.

Current local branch/commit at the time of the staging attempt:

```text
main @ 976269d
```

Server deployed commit before/after could not be verified because SSH/Hostinger deployment access is blocked.

## Baseline HTTP Diagnostics

Safe baseline responses were saved at:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\baseline-http.json
```

Summary:

| Endpoint | Status | Safe summary |
| --- | ---: | --- |
| `/` | 200 | Arabic landing page responds. |
| `/?lang=en` | 200 | English landing page responds. |
| `/api/v1/system/health` | 200 | Health responds. |
| `/api/v1/system/readiness` with JSON accept | 401 | Unauthenticated JSON response. |
| `/api/v1/system/readiness` default/browser style | 500 | `Route [login] not defined.` |
| `/api/v1/doctors` | 200 | One approved doctor returned. |
| `/api/v1/payment-methods` | 200 | Empty data; payment proof flow blocked. |
| `/api/v1/hospitals` | 404 | Route/resource unavailable. |
| `/api/v1/radiology/scans` | 200 | Empty scan data. |
| `/api/v1/gyms` | 404 | Route/resource unavailable. |
| `/api/v1/coaches` | 404 | Route/resource unavailable. |

The current staging backend is not current enough for the Sprint 49-53 accepted local super-app behavior.

## Readiness Result

Readiness remains unsafe:

- JSON/API request: HTTP 401 unauthenticated.
- Default/browser-style request: HTTP 500 with safe JSON message `Route [login] not defined.`

Evidence:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\readiness-default.json
```

Because server logs are unavailable, the root cause could not be inspected or fixed. Sprint 54 cannot be accepted while readiness remains unresolved and server access is blocked.

## Staging Demo Data Result

Staging demo data is not ready for Sprint 54.

Observed blockers:

- `/api/v1/payment-methods` returns an empty list.
- `/api/v1/hospitals`, `/api/v1/gyms`, and `/api/v1/coaches` return 404.
- `/api/v1/radiology/scans` returns an empty list.
- Demo account checks for local-only pilot emails failed with invalid credentials or rate limiting.

Safe evidence:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\auth-demo-check.json
```

No staging seeder was run because deployment access is blocked.

## APK And Real Phone Result

Sprint 54 staging APK was built as an artifact, but it was not installed or accepted through phone QA.

APK:

```text
I:\Etamen\.tmp\etamen-staging-real-phone.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-real-phone.apk
```

SHA-256:

```text
98D596E703B45BF4E47C8943E2931542A3BD65594DA1C7B9F95A37A5966BD338
```

Reason:

- The required staging backend deployment did not happen.
- Required staging data is missing.
- Payment methods are empty.
- Provider workspace routes/data are unavailable or unverified.

Testing the real Android phone against this incomplete staging state would only reproduce known backend blockers and could not satisfy the Sprint 54 gate.

Known phone from Sprint 53:

- Infinix X657C
- Android 10

Sprint 54 staging phone QA result:

- Auth/session: NOT RUN.
- Doctor proof/admin accept: NOT RUN.
- Provider workspace: NOT RUN.
- Limited staff guard: NOT RUN.

## Optional Module Smoke

Remote HTTP smoke only:

| Area | Result |
| --- | --- |
| Radiology | PARTIAL; route responds but scans are empty. |
| Gym | BLOCKED; route/resource returns 404. |
| Coach | BLOCKED; route/resource returns 404. |

No Flutter staging smoke was run because access/data blockers prevent a valid gate.

## Security And Privacy Sweep

Public hardening checks were saved at:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\security-sweep.json
```

Checked URLs:

- `https://etamen.inolty.com/.env`
- `https://etamen.inolty.com/composer.json`
- `https://etamen.inolty.com/storage`
- `https://etamen.inolty.com/vendor`
- `https://etamen.inolty.com/database`

Result:

- All returned 404.
- No raw `.env`, Composer file, database directory, vendor directory, storage directory, or secret content was observed.

The privacy sweep is only a public URL hardening check. It does not replace authenticated API and server log inspection after deployment access is restored.

## Tests And Build

Local code verification after the Sprint 54 documentation/deployment-gate pass:

- Backend `php artisan test`: PASS, 254 tests / 2092 assertions.
- Backend `git diff --check`: PASS.
- Flutter `flutter pub get`: PASS.
- Flutter `dart format .`: PASS, 0 files changed.
- Flutter `flutter analyze`: PASS.
- Flutter `flutter test`: PASS, 187 tests.
- Flutter staging `android-arm` debug APK build: PASS.

These results prove the local code still builds/tests. They do not prove staging acceptance because the server deployment, staging data, and real-phone staging QA are blocked.

## Remaining Blockers

- Restore SSH, Hostinger Git, SFTP, File Manager, or another safe server deployment path.
- Verify current deployed commit on server.
- Back up staging database before migrations.
- Deploy latest backend code safely.
- Run `php artisan migrate --force`.
- Run safe staging demo seed/payment-method activation.
- Fix readiness so it returns 200 or structured non-500 not-ready JSON.
- Build and install a staging APK pointing to `https://etamen.inolty.com/api/v1`.
- Complete real Android phone doctor proof upload/admin accept against staging.
- Verify provider workspaces and limited staff guard against staging.

## Next Sprint Recommendation

Sprint 55 should be an access-first staging deployment sprint:

1. Restore verified server access.
2. Back up the staging database.
3. Deploy latest `main`.
4. Run safe migrations and staging demo seed.
5. Fix readiness from server logs.
6. Repeat Sprint 54 phone QA after `/payment-methods`, workspaces, and provider routes are live.

Do not invite external users, claim production readiness, claim public launch readiness, or claim app-store readiness from this result.
