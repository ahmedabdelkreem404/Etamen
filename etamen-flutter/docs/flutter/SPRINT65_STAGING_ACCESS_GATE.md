# Sprint 65 Staging Access Gate

Date: 2026-05-11

Sprint 65 was limited to staging access verification. It did not change Flutter code and did not build a staging APK.

## Selected Access Method

SSH key-based access was the only available method from this local environment:

```text
~/.ssh/etamen_staging_codex
```

Target:

```text
u797172084@89.116.147.138:65002
```

No safe Hostinger Terminal, File Manager, or SFTP workflow was available during this sprint.

## SSH Result

Safe command attempted:

```text
ssh -i ~/.ssh/etamen_staging_codex -o IdentitiesOnly=yes -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && whoami && php -v"
```

Result:

```text
Permission denied (publickey,password).
```

No password retry was attempted.

## Safe Server Info

No server info was available because SSH access failed.

Unavailable:

- PHP version.
- Composer version.
- project directory.
- Laravel version.
- deployed commit.

## Backup Feasibility

Backup feasibility could not be checked because access failed.

Next sprint cannot deploy until backup feasibility is confirmed.

## Public API Baseline

Public staging API checked without credentials:

| Endpoint | Status | Safe summary |
| --- | ---: | --- |
| `/api/v1/system/health` | 200 | `data.status=ok` |
| `/api/v1/system/readiness` | 500 | JSON error envelope, body omitted |
| `/api/v1/payment-methods` | 200 | `data_count=0` |
| `/api/v1/doctors` | 200 | `data_count=1` |
| `/api/v1/hospitals` | 404 | JSON error envelope |
| `/api/v1/radiology/scans` | 200 | `data_count=0` |
| `/api/v1/gyms` | 404 | JSON error envelope |
| `/api/v1/coaches` | 404 | JSON error envelope |

Conclusion:

- staging is not ready for phone QA.
- staging data is incomplete.
- provider/fitness routes are still stale or missing.
- payment methods are not active.

## What Was Not Done

- no deployment.
- no migration.
- no seed.
- no Composer install.
- no cache clear.
- no `.env` read.
- no server write.
- no staging APK build.
- no staging readiness claim.
- no production/public/app-store readiness claim.
- no external users invited.

## Final Decision

```text
STAGING_ACCESS_STILL_BLOCKED
```

## Next Sprint Recommendation

Fix Hostinger SSH access first, then run a backup-first staging deployment recovery sprint. Until then, Etamen remains approved for local/internal/client demos only.
