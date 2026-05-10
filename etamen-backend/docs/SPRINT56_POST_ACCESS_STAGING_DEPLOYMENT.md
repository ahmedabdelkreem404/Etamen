# Sprint 56 - Post-Access Staging Deployment

Date: 2026-05-11

Scope: staging deployment recovery after the product owner reported access was available.

Target:

```text
https://etamen.inolty.com/api/v1
```

This report intentionally contains no SSH password, DB password, APP_KEY, Paymob keys, tokens, `.env` content, or private file paths.

## Decision

```text
STAGING_DEPLOY_BLOCKED
```

Access was reported as available, but the current Codex environment still cannot authenticate to the server using the safe non-interactive SSH method. No destructive commands were run and no staging data was changed.

## Access Method / Result

Attempted SSH:

```text
ssh -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && php -v && composer --version"
```

Result:

```text
Permission denied (publickey,password).
```

Local SSH context:

- Current branch: `main`.
- Current local commit before attempt: `ad0312c`.
- Local `.ssh` folder has only `known_hosts` files and no private key available for this host.

Evidence:

```text
I:\Etamen\.tmp\sprint56-post-access-staging-deployment\access-result.json
```

No Hostinger Terminal, File Manager, SFTP, or panel-based deployment method was accessible from this Codex session.

## Backup Result

Not run.

Reason:

- Server access is not usable from this session.
- Database credentials were not read, requested, printed, or stored.
- `.env` was not read or printed.

Because backup could not be performed, deployment and migrations were stopped.

## Deployed Commit Before / After

Local commit before attempt:

```text
ad0312c
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

- Could not inspect the server project directory.
- Could not run `git rev-parse --short HEAD` on staging.

## Migration Result

Not run.

Reason:

- Backup did not run.
- SSH/deployment access was not usable.
- Sprint safety rules forbid migrations when access/backup is unclear.

No `migrate:fresh` or destructive command was run.

## Readiness Result

Evidence:

```text
I:\Etamen\.tmp\sprint56-post-access-staging-deployment\http-baseline.json
```

Current staging:

| Endpoint | Result |
| --- | --- |
| `/api/v1/system/health` | HTTP 200 |
| `/api/v1/system/readiness` with JSON Accept | HTTP 401, `Unauthenticated.` |
| `/api/v1/system/readiness` default/browser style | HTTP 500, `Route [login] not defined.` |

Readiness was not fixed because server logs/code/cache could not be inspected or updated.

## Data And Payment Result

Current public staging checks:

| Endpoint | Result |
| --- | --- |
| `/api/v1/doctors` | HTTP 200, one doctor |
| `/api/v1/payment-methods` | HTTP 200, empty data |
| `/api/v1/hospitals` | HTTP 404 |
| `/api/v1/radiology/scans` | HTTP 200, empty data |
| `/api/v1/gyms` | HTTP 404 |
| `/api/v1/coaches` | HTTP 404 |

No staging demo seeder was run.

No `php artisan etamen:ensure-payment-methods --staging` command was run.

## Workspace / Provider Result

Not verified through authenticated staging API.

Reason:

- No valid staging demo login token was available.
- Provider workspace routes cannot be verified while public provider route surface is stale/incomplete.
- No staging seed could be run.

## Limited Staff Guard

Not verified.

Reason:

- Limited staff demo account/token unavailable.
- No access to seed or inspect staff records.

Expected next gate after real access:

- log in limited staff.
- call wrong-provider endpoint.
- verify HTTP 403 safe JSON.

## Security / Privacy Result

Evidence:

```text
I:\Etamen\.tmp\sprint56-post-access-staging-deployment\security-sweep.json
```

Public hardening:

| URL | Result |
| --- | --- |
| `/.env` | 404, no raw secret content |
| `/composer.json` | 404, no raw secret content |
| `/storage` | 404, no raw secret content |
| `/vendor` | 404, no raw secret content |
| `/database` | 404, no raw secret content |
| `/bootstrap` | 404, no raw secret content |
| `/config` | 404, no raw secret content |

This is not a complete authenticated privacy sweep because deployment/authenticated access did not succeed.

## APK Artifact

Not built.

Reason:

- Sprint 56 requires building the staging APK only if staging API passes.
- Current staging API still fails readiness/data/payment gates.

Target path after successful recovery:

```text
I:\Etamen\.tmp\etamen-staging-post-access.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-post-access.apk
```

## Tests / Build

Not rerun in this blocked deployment pass.

Reason:

- No code changes were made.
- Deployment stopped at SSH access before any server or application change.

Before a successful retry, run:

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

- Make the restored access usable from this Codex environment.
- Prefer adding a temporary SSH public key for this machine/user to Hostinger.
- Alternatively provide Hostinger Terminal/File Manager/SFTP workflow that can run commands safely.
- Back up staging database before any migration.
- Back up current server `.env` without printing it.
- Inspect deployed commit and PHP/Composer versions.
- Deploy latest `main`.
- Run safe migrations.
- Fix readiness.
- Run safe demo/payment seed.
- Verify staging public/authenticated/provider routes.
- Build staging APK artifact only after API recovery passes.

## Next Sprint Recommendation

Sprint 57 should start only after access is made usable in the current environment:

1. Install an SSH key for this local Codex machine or open a safe Hostinger Terminal session.
2. Run backup first.
3. Deploy latest `main`.
4. Run migrations and idempotent staging seed.
5. Verify readiness/data/payment/workspaces.
6. Then build the staging APK and schedule real-phone QA.

Do not claim staging phone readiness, production readiness, public launch readiness, or app-store readiness from this result.
