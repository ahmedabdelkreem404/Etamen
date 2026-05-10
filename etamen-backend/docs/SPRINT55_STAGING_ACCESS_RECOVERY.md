# Sprint 55 - Staging Access Recovery

Date: 2026-05-11

Scope: access-first staging deployment recovery for `https://etamen.inolty.com/api/v1`.

This report intentionally contains no SSH passwords, database credentials, APP_KEY, payment keys, tokens, `.env` content, or private file paths.

## Decision

```text
STAGING_ACCESS_BLOCKED
```

Sprint 55 stopped at the access gate. No deployment, backup, migration, seed, cache clear, composer install, or server `.env` operation was performed.

## Access Method And Result

Target:

- Domain: `https://etamen.inolty.com`
- Server IP: `89.116.147.138`
- SSH port: `65002`
- SSH user: `u797172084`

Safe non-interactive SSH test:

```text
ssh -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && php -v"
```

Result:

```text
Permission denied (publickey,password).
```

Local SSH key check found only `known_hosts` files and no private SSH key for this host.

Evidence:

```text
I:\Etamen\.tmp\sprint55-staging-access-recovery\access-result.json
```

## Backup Result

Not run.

Reason:

- No server access.
- No database credentials were read or printed.
- No Hostinger panel/database backup path was available in this session.

Because backup could not even be attempted safely, migrations and seed commands were not run.

## Deployed Commit Before / After

Local code at the start of Sprint 55:

```text
main @ 7cb5d02
```

Server deployed commit before:

```text
unknown
```

Server deployed commit after:

```text
unchanged / not verified
```

Reason:

- SSH/Hostinger deploy access remains blocked.
- The server project directory and deployed git state could not be inspected.

## Migration Result

Not run.

Reason:

- Access blocked.
- Backup not possible.
- Sprint 55 explicitly forbids risky deployment/migration when access is unclear.

No destructive command was run.

## Readiness Before / After

Evidence:

```text
I:\Etamen\.tmp\sprint55-staging-access-recovery\http-baseline.json
```

Before:

| Check | Status | Summary |
| --- | ---: | --- |
| `/api/v1/system/health` | 200 | Health responds. |
| `/api/v1/system/readiness` with JSON accept | 401 | `Unauthenticated.` |
| `/api/v1/system/readiness` default/browser style | 500 | `Route [login] not defined.` |

After:

```text
unchanged
```

Reason:

- Server logs and route/middleware code on staging could not be inspected.
- No readiness fix could be deployed.

## Payment Methods Result

Current staging result:

```text
GET /api/v1/payment-methods -> HTTP 200, data_count=0
```

This blocks doctor proof upload and any real payment review gate on staging.

No `php artisan etamen:ensure-payment-methods --staging` command was run because access is blocked.

## Demo Data Result

Current staging baseline:

| Area | Result |
| --- | --- |
| Doctors | HTTP 200, one approved doctor. |
| Payment methods | HTTP 200, empty data. |
| Hospitals | HTTP 404. |
| Radiology scans | HTTP 200, empty data. |
| Gyms | HTTP 404. |
| Coaches | HTTP 404. |
| Demo patient login | HTTP 401, invalid credentials. |

Evidence:

```text
I:\Etamen\.tmp\sprint55-staging-access-recovery\auth-patient-check.json
```

No staging demo seeder was run.

## Provider Routes And Workspaces

Not verified through authenticated staging API.

Reason:

- Demo credentials are not available on staging.
- Staging appears stale/incomplete because hospitals, gyms, and coaches public routes still return 404.
- Provider workspace dashboard routes could not be tested without a valid token.

## Limited Staff Guard

Not verified.

Reason:

- Limited staff demo login is unavailable.
- No authenticated provider token was available.
- No server-side seed could be run.

Expected next gate after access recovery:

- login limited staff.
- call a wrong-provider workspace endpoint.
- verify HTTP 403 with safe JSON.

## Security And Privacy Result

Evidence:

```text
I:\Etamen\.tmp\sprint55-staging-access-recovery\security-sweep.json
```

Public hardening checks:

| URL | Result |
| --- | --- |
| `/.env` | 404, no raw secret content |
| `/composer.json` | 404, no raw secret content |
| `/storage` | 404, no raw secret content |
| `/vendor` | 404, no raw secret content |
| `/database` | 404, no raw secret content |
| `/bootstrap` | 404, no raw secret content |
| `/config` | 404, no raw secret content |

This is a public URL sweep only. It does not replace authenticated API privacy checks after deployment access is restored.

## APK Artifact

No Sprint 55 staging APK artifact was built.

Reason:

- Sprint 55 requires building the APK only after the staging API is healthy.
- Access is blocked.
- Readiness remains broken.
- Payment methods/demo provider data are not ready.

Previous Sprint 54 staging APK remains an artifact only and is not accepted by real phone QA.

## Tests And Build

Local verification after the Sprint 55 access-gate documentation update:

- Backend `php artisan test`: PASS, 254 tests / 2092 assertions.
- Flutter `flutter pub get`: PASS.
- Flutter `dart format .`: PASS, 0 files changed.
- Flutter `flutter analyze`: PASS.
- Flutter `flutter test`: PASS, 187 tests.

Sprint 55 staging APK build was intentionally not run because the sprint requires building the artifact only after the staging API is healthy. It is not healthy yet:

- readiness still returns 500 by default.
- payment methods are empty.
- required staging demo/provider routes are missing or empty.

Required before retrying a deploy after access is restored:

```text
php artisan test
git diff --check
flutter pub get
dart format .
flutter analyze
flutter test
flutter build apk --debug --target-platform android-arm --dart-define=ETAMEN_API_BASE_URL=https://etamen.inolty.com/api/v1 --dart-define=ETAMEN_ENV=staging
```

## Remaining Blockers

- Restore SSH, Hostinger Git deployment, Hostinger Terminal, SFTP/File Manager, or another safe deployment path.
- Confirm deployed project path and current server commit.
- Back up staging database.
- Back up current server `.env` without printing it.
- Deploy latest `main`.
- Run `composer install --no-dev --optimize-autoloader`.
- Run `php artisan migrate --force`.
- Fix readiness so it never returns 500 or login-route errors.
- Run safe staging demo/payment seed.
- Verify public and authenticated API contracts.
- Build Sprint 55/Sprint 56 staging APK only after API health/data pass.

## Next Sprint Recommendation

Do not start phone QA yet.

Next action is still access recovery:

1. Provide SSH key access or owner-entered SSH password.
2. Alternatively provide Hostinger Git/File Manager/Terminal access.
3. Back up staging database first.
4. Deploy latest `main`.
5. Fix readiness and seed staging data.
6. Then run the real Android phone proof/admin/provider workspace gate as Sprint 56.

Do not claim production readiness, public launch readiness, app-store readiness, or supervised staging pilot readiness from this sprint.
