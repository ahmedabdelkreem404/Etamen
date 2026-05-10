# Sprint 56 - Staging API Recovery QA

Date: 2026-05-11

Scope: Flutter/staging QA notes for post-access staging deployment recovery.

Target:

```text
https://etamen.inolty.com/api/v1
```

## Decision

```text
STAGING_DEPLOY_BLOCKED
```

No staging APK artifact was built and no real-phone QA was started because staging backend deployment did not proceed.

## Access Result

SSH in the current Codex environment still fails:

```text
Permission denied (publickey,password).
```

No password, SSH key, token, `.env`, DB credential, or payment secret was printed or committed.

## Staging API Current State

Evidence:

```text
I:\Etamen\.tmp\sprint56-post-access-staging-deployment\http-baseline.json
```

Observed:

- `/api/v1/system/health`: HTTP 200.
- `/api/v1/system/readiness` with JSON Accept: HTTP 401.
- `/api/v1/system/readiness` default/browser style: HTTP 500, `Route [login] not defined.`
- `/api/v1/payment-methods`: HTTP 200, empty data.
- `/api/v1/doctors`: HTTP 200, one doctor.
- `/api/v1/hospitals`: HTTP 404.
- `/api/v1/radiology/scans`: HTTP 200, empty data.
- `/api/v1/gyms`: HTTP 404.
- `/api/v1/coaches`: HTTP 404.

This does not pass the staging API recovery gate for Flutter QA.

## APK Artifact

Not built.

Target path after API recovery:

```text
I:\Etamen\.tmp\etamen-staging-post-access.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-post-access.apk
```

Reason:

- APK build is gated on staging API passing readiness/data/payment/workspace checks.
- Current staging API is still stale/incomplete.

## Workspace / Provider QA

Not run.

Reason:

- Provider workspaces require valid staging login and current backend routes.
- Demo data cannot be seeded until server access works.

## Limited Staff QA

Not run.

Reason:

- No limited staff token is available.
- Cannot seed or verify provider staff records.

## Security Sweep

Evidence:

```text
I:\Etamen\.tmp\sprint56-post-access-staging-deployment\security-sweep.json
```

Public hardening checks for `/.env`, `/composer.json`, `/storage`, `/vendor`, `/database`, `/bootstrap`, and `/config` returned 404 and no raw secret content.

## Remaining Flutter Blockers

- Restore usable server access.
- Deploy current backend.
- Fix readiness.
- Activate manual payment methods.
- Seed staging demo data.
- Verify provider workspace routes.
- Build staging APK.
- Then run real-phone Sprint 57/58 QA.

## Next Recommendation

Do not install another staging APK yet. First make the backend current and healthy, then build a fresh APK and run real phone QA.
