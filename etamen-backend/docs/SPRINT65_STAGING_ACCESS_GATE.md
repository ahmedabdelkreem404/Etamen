# Sprint 65 Staging Access Gate

Date: 2026-05-11

Sprint 65 was an access-only staging gate. No deployment or data operation was allowed.

## Selected Access Method

The only usable method available from the local Codex environment was SSH key-based access using:

```text
~/.ssh/etamen_staging_codex
```

Target:

```text
u797172084@89.116.147.138:65002
```

No Hostinger Terminal, File Manager, or SFTP workflow with a safe terminal path was provided during this sprint.

## SSH Result

Command attempted:

```text
ssh -i ~/.ssh/etamen_staging_codex -o IdentitiesOnly=yes -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && whoami && php -v"
```

Safe result:

```text
Permission denied (publickey,password).
```

No password retry was attempted. No server file was read or changed.

## Safe Server Info

Unavailable because SSH access failed.

Not available:

- `pwd`
- `whoami`
- PHP version
- Composer version
- project directory
- Laravel version
- deployed commit

## Project Directory Result

Project directory was not identified because SSH access failed.

## Deployed Commit Result

Deployed commit was not readable because SSH access failed.

## Backup Feasibility

Backup feasibility is blocked until access is restored.

Not verified:

- database backup command availability
- `mysqldump` availability
- writable backup directory
- current code restore path
- safe `.env` backup path without printing contents

Decision for this area:

```text
STAGING_ACCESS_STILL_BLOCKED
```

## Public API Baseline

Checked from the local machine without credentials:

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

Baseline conclusion:

- staging is reachable over HTTPS for some public API routes.
- readiness is still not healthy.
- payment methods are empty.
- hospital, gym, and coach routes remain missing or stale on staging.
- radiology scans are empty.
- staging data and deployed code are still not verified as current.

## What Was Not Done

The following were intentionally not done:

- no deploy.
- no migration.
- no seed.
- no Composer install.
- no cache clear.
- no storage link.
- no `.env` read.
- no server file writes.
- no staging APK build.
- no production/public/staging readiness claim.
- no external users invited.

## Remaining Blockers

- SSH key access still fails.
- no alternative safe Hostinger terminal/SFTP workflow was available in this sprint.
- staging backup feasibility cannot be confirmed.
- staging cannot proceed to backup-first deployment until access works.

## Final Decision

```text
STAGING_ACCESS_STILL_BLOCKED
```

## Next Sprint Recommendation

Resolve Hostinger access before any deployment sprint:

1. confirm the public key for `~/.ssh/etamen_staging_codex.pub` is installed for user `u797172084`.
2. verify SSH with `IdentitiesOnly=yes`.
3. only after access works, run a backup-first staging recovery sprint.
