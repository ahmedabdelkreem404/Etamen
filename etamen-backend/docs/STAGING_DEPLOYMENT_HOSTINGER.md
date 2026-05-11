# Etamen Hostinger Staging Deployment Notes

Date: 2026-05-08

Target staging URL:

- `https://etamen.inolty.com`
- API base URL for the staging APK: `https://etamen.inolty.com/api/v1`

This file intentionally contains no SSH password, database password, APP_KEY, payment secrets, or private tokens.

## Local Pre-Deployment Result

Backend:

- `php artisan test`: PASS, 216 tests, 1734 assertions.
- `git diff --check`: PASS.
- Sprint 37 radiology catalog foundation tests: PASS as part of the full suite.

Flutter:

- `flutter pub get`: PASS.
- `dart format .`: PASS, 0 files changed.
- `flutter analyze`: PASS.
- `flutter test`: PASS, 164 tests.
- Staging debug APK build: PASS after switching Gradle cache away from the full `D:` drive.

## SSH Access Result

SSH command attempted without interactive password entry:

- Host: `89.116.147.138`
- Port: `65002`
- User: `u797172084`

Result:

- FAIL: `Permission denied (publickey,password).`

No server files were changed.
No server `.env` was created or edited.
No server migrations were run.
No database was touched.

## Server Capability Status

Not verified through SSH because authentication failed.

Still required before real deployment:

- Confirm real Hostinger application path.
- Confirm PHP version.
- Confirm Composer availability.
- Confirm required PHP extensions.
- Confirm writable `storage` and `bootstrap/cache`.
- Confirm public web root points safely to Laravel `public`.

## Database Status

Database credentials were provided by the product owner during this thread, but they were not written to Git, docs, or local env files.

No migration was run because SSH access failed.

Before running migrations later:

- Confirm the database belongs only to Etamen staging.
- Do not run `migrate:fresh` on the hosting database.
- Use only `php artisan migrate --force`.
- Seed demo data only if explicitly intended for staging/manual QA.

## External URL Checks Against Current Remote Site

These checks describe the site already responding at the target domain. They do not prove Sprint 37 was deployed in this session.

| URL | Result | Notes |
| --- | --- | --- |
| `/` | 200 | Landing page responds. |
| `/?lang=en` | 200 | English landing route/query responds. |
| `/api/v1/system/health` | 200 | Returns `status: ok`, environment `non-production`, version `local`. |
| `/api/v1/system/readiness` | 500 | Blocker for staging readiness visibility. Needs server log inspection. |
| `/api/v1/specialties` | 200 | Public specialties respond. |
| `/api/v1/doctors` | 200 | Public doctors endpoint responds, but current staging data is empty. |
| `/api/v1/auth/login` demo patient | 200 | Login endpoint responded from local machine; token was logged out immediately. |

## Security Exposure Checks Against Current Remote Site

| URL | Result |
| --- | --- |
| `/.env` | 404 |
| `/composer.json` | 404 |
| `/storage/` | 404 |
| `/vendor/` | 404 |
| `/database/` | 404 |

No secret content was observed from these checks.

## Deployment Decision

Current deployment attempt result:

- `DEPLOYMENT_FAILED`

Reason:

- The current local backend, including Sprint 37, could not be uploaded or migrated because SSH authentication was unavailable.
- The target domain currently responds, but it cannot be treated as updated by this deployment attempt.
- Readiness endpoint currently returns 500 and needs server-side inspection.

## Next Required Action

Provide one of the following for the next deployment attempt:

- SSH key access for `u797172084`.
- A temporary SSH password entered interactively by the owner during a live deployment session.
- Hostinger Git deployment access or file manager/SFTP credentials handled outside Git/docs.

After access is available:

1. Inspect server PHP/Composer/extensions.
2. Deploy backend files safely.
3. Configure server `.env` manually on the server only.
4. Run `php artisan migrate --force`.
5. Re-check health/readiness/security URLs.
6. Rebuild and reinstall the staging APK if the API base or backend state changes.

---

# Sprint 38 Update - Staging Connectivity And APK Login Gate

Date: 2026-05-08

## Access Result

SSH is still blocked.

Command attempted in non-interactive mode:

```text
ssh -o BatchMode=yes -o ConnectTimeout=15 -p 65002 u797172084@89.116.147.138 "pwd"
```

Result:

```text
Permission denied (publickey,password).
```

No server files were changed from this session, and no server logs were readable.

## Readiness 500 Status

`/api/v1/system/readiness` still returns HTTP 500 from the remote staging site.

Because SSH is blocked, the exact Laravel exception could not be inspected in `storage/logs/laravel.log`. This remains a server-side blocker for a clean staging deployment sign-off.

Current expected fix path after access is restored:

1. Inspect Laravel and web server logs.
2. Fix the real readiness failure instead of hiding it.
3. Keep `APP_DEBUG=false`.
4. Return either 200 ready JSON or structured non-500 not-ready JSON.

## External API Checks

| URL | Result | Notes |
| --- | --- | --- |
| `/` | 200 | Arabic landing loads. |
| `/?lang=en` | 200 | English landing loads. |
| `/api/v1/system/health` | 200 | Health endpoint responds. |
| `/api/v1/system/readiness` | 500 | Still unresolved without server logs. |
| `/api/v1/specialties` | 200 | Public taxonomy responds. |
| `/api/v1/doctors` | 200 | Endpoint responds, current `data` count is 0. |
| `/api/v1/auth/login` | 200 | Newly created staging QA patient can login; token was not written to docs. |

## Security Exposure Check

| URL | Result |
| --- | --- |
| `/.env` | 404 |
| `/composer.json` | 404 |
| `/storage/` | 404 |
| `/vendor/` | 404 |
| `/database/` | 404 |

No secret content was exposed by these URL checks.

## Deployment Status

The APK login gate was fixed from the Flutter/staging-client side, but the backend deployment itself was not completed because server access is still blocked.

Strict Sprint 38 deployment decision:

- `STAGING_ACCESS_BLOCKED`

Reason:

- SSH remains unavailable.
- Readiness still returns 500.
- Current staging database has no approved doctors, so doctor profile/booking QA cannot be completed against staging until data is seeded or approved.

---

# Sprint 39 Update - Staging Doctor Booking Data

Date: 2026-05-09

## Access Result

SSH remains blocked. No server shell, Laravel logs, `.env`, or artisan commands were available from this session.

No server migrations were run, and `migrate:fresh` was not used.

## What Was Changed On Staging

Because the hosted API and admin login were reachable, staging doctor data was prepared through the available provider/admin API flow:

- Created/verified one cardiology and vascular medicine specialty.
- Registered one staging-only doctor provider.
- Approved the doctor provider through the admin API.
- Created one main branch with Cairo/Nasr City style demo address and safe latitude/longitude.
- Created a clinic schedule and generated appointment slots for QA.

This data is safe staging data only. No real doctor photo, private document, or provider secret was uploaded.

## Current Hosted API State

| Endpoint | Result |
| --- | --- |
| `/api/v1/system/health` | PASS, HTTP 200 |
| `/api/v1/system/readiness` with JSON accept header | PARTIAL, HTTP 401 |
| `/api/v1/system/readiness` from browser/default request | FAIL, HTTP 500 |
| `/api/v1/specialties` | PASS, one staging specialty |
| `/api/v1/doctors` | PASS, one approved staging doctor |
| `/api/v1/payment-methods` | FAIL FOR FLOW, HTTP 200 empty data |

## Payment Method Blocker

The staging doctor booking path now reaches payment, but proof upload cannot proceed because no active payment methods are returned by the hosted API.

Needed after hosting access is restored:

1. Add or activate staging-safe manual payment methods.
2. Confirm Vodafone Cash and InstaPay appear at `/api/v1/payment-methods`.
3. Repeat Android booking to proof upload.
4. Complete admin accept/reject review for the same uploaded proof.

## Deployment Status

Strict Sprint 39 backend deployment decision:

- `STAGING_PAYMENT_BLOCKED_NO_PAYMENT_METHODS`

This is not public launch readiness and not physical pilot readiness. The current blocker is staging payment-method data/access, not APK login or doctor discovery.

## Local Migration Check

The local desktop MySQL `Etamen` database was reset and seeded for migration verification only:

- `php artisan migrate:fresh --seed`: PASS.
- `php artisan db:seed --class=PilotDemoSeeder`: PASS.

This was local only. No `migrate:fresh` was run on hosting/staging.

---

# Sprint 40 Payment Method Activation Notes

Date: 2026-05-09

## Problem

The staging doctor booking flow reaches the payment step, but:

```text
GET https://etamen.inolty.com/api/v1/payment-methods
```

still returns:

```json
{"success":true,"message":"Active payment methods.","data":[],"errors":[]}
```

This blocks proof upload and admin review.

## Code Fix Prepared

The backend now has a safe repeatable activation path:

```text
php artisan etamen:ensure-payment-methods --staging
```

Expected output:

```text
manual_vodafone_cash: active
manual_instapay: active
paymob: inactive
```

The command uses the safe `PaymentMethodSeeder`.

## Deployment Path Needed

Because SSH is still blocked with `Permission denied (publickey,password)`, this session could not update the hosted app directly.

Use one of these paths:

1. Restore SSH access and pull/deploy the latest `main`, then run the command above.
2. Use Hostinger Git/File Manager to deploy the latest backend files, then run the command from a terminal if available.
3. Use the Filament admin Payment Methods create action after deploying the latest code.

Do not run `migrate:fresh` on staging.
Do not commit `.env`, DB credentials, APP_KEY, or payment secrets.
Do not activate Paymob unless its staging credentials are real and verified.

## Verification After Deployment

Run:

```text
GET https://etamen.inolty.com/api/v1/payment-methods
```

Expected:

- `manual_vodafone_cash` appears.
- `manual_instapay` appears.
- no config secrets appear.
- Paymob remains hidden if inactive.

Then repeat the Android booking flow to the proof upload screen.

---

# Sprint 54 Staging Real Phone Gate Update

Date: 2026-05-11

## Access Result

SSH remains blocked.

Safe non-interactive attempt:

```text
ssh -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && php -v"
```

Result:

```text
Permission denied (publickey,password).
```

No staging files were changed. No server `.env` was read or printed. No database backup, migration, seeder, composer install, or cache command was run.

## Current HTTP Baseline

Evidence:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\baseline-http.json
```

| Endpoint | Status | Notes |
| --- | ---: | --- |
| `/` | 200 | Landing responds. |
| `/?lang=en` | 200 | English landing responds. |
| `/api/v1/system/health` | 200 | Health responds. |
| `/api/v1/system/readiness` | 401 with JSON accept, 500 by default | Default request returns `Route [login] not defined.` |
| `/api/v1/doctors` | 200 | One approved doctor returned. |
| `/api/v1/payment-methods` | 200 | Empty data; payment proof blocked. |
| `/api/v1/hospitals` | 404 | Missing/stale route. |
| `/api/v1/radiology/scans` | 200 | Empty data. |
| `/api/v1/gyms` | 404 | Missing/stale route. |
| `/api/v1/coaches` | 404 | Missing/stale route. |

## Staging Gate Result

Decision:

```text
STAGING_ACCESS_BLOCKED
```

Reason:

- Server access is unavailable.
- Deployed staging code could not be updated or verified.
- Readiness still fails.
- Staging data is incomplete.
- Active manual payment methods are missing.
- Provider workspace/provider operations endpoints are not live enough for Sprint 54 QA.

## Security Exposure Check

Evidence:

```text
I:\Etamen\.tmp\sprint54-staging-real-phone\security-sweep.json
```

Checked:

- `/.env`
- `/composer.json`
- `/storage`
- `/vendor`
- `/database`

Result:

- All returned 404.
- No raw secret content was observed.

## Required Next Action

Restore a safe deployment path first:

- SSH key/password entered by the owner during a live deployment session, or
- Hostinger Git deployment access, or
- SFTP/File Manager with clear application path and terminal/migration path.

After access is restored:

1. Confirm deployed commit.
2. Back up staging database.
3. Pull/deploy latest `main`.
4. Run safe migrations only: `php artisan migrate --force`.
5. Run staging-safe demo/payment seed.
6. Fix readiness from logs.
7. Rebuild staging APK and repeat real-phone proof/admin/provider workspace QA.

---

# Sprint 55 Access-First Recovery Update

Date: 2026-05-11

## Access Result

SSH remains blocked with the same error:

```text
Permission denied (publickey,password).
```

No Hostinger Git, Terminal, SFTP, or File Manager access was available in this session.

No deployment, backup, migration, seed, composer install, cache clear, storage link, or `.env` operation was performed.

## Current Staging API Diagnostics

Evidence:

```text
I:\Etamen\.tmp\sprint55-staging-access-recovery\http-baseline.json
```

| Endpoint | Status | Notes |
| --- | ---: | --- |
| `/api/v1/system/health` | 200 | Health responds. |
| `/api/v1/system/readiness` with JSON accept | 401 | `Unauthenticated.` |
| `/api/v1/system/readiness` default | 500 | `Route [login] not defined.` |
| `/api/v1/doctors` | 200 | One approved doctor. |
| `/api/v1/payment-methods` | 200 | Empty data. |
| `/api/v1/hospitals` | 404 | Missing/stale route. |
| `/api/v1/radiology/scans` | 200 | Empty data. |
| `/api/v1/gyms` | 404 | Missing/stale route. |
| `/api/v1/coaches` | 404 | Missing/stale route. |

Demo patient login with the local fake pilot email returned HTTP 401 invalid credentials.

## Recovery Decision

```text
STAGING_ACCESS_BLOCKED
```

Sprint 55 remains blocked before backup and deployment.

## Next Required Action

Provide one safe access path:

- SSH key or owner-entered SSH password, or
- Hostinger Git deployment and Terminal, or
- Hostinger File Manager/SFTP plus a safe migration terminal path.

After that, the first commands must be backup/inspection, not migration.

---

# Sprint 56 Post-Access Deployment Attempt

Date: 2026-05-11

## Result

Access was reported as available, but the current Codex environment still cannot authenticate through SSH.

Safe SSH attempt result:

```text
Permission denied (publickey,password).
```

Decision:

```text
STAGING_DEPLOY_BLOCKED
```

No deployment, backup, migration, seed, cache clear, or `.env` operation was performed.

## Current Staging API State

Evidence:

```text
I:\Etamen\.tmp\sprint56-post-access-staging-deployment\http-baseline.json
```

Staging remains stale/incomplete:

- health: 200.
- readiness with JSON accept: 401.
- readiness default: 500 with `Route [login] not defined.`
- payment methods: empty.
- doctors: one doctor.
- hospitals: 404.
- radiology scans: empty.
- gyms: 404.
- coaches: 404.

## Required Access Fix

Make access usable from this local Codex environment before retrying:

- add this machine's SSH public key to the Hostinger SSH account, or
- provide a safe Hostinger Terminal/File Manager/SFTP workflow that can run backup and artisan commands without exposing secrets.

First successful command sequence must start with backup and inspection, not deploy/migrate.

---

# Sprint 57 SSH Key Bootstrap

Date: 2026-05-11

## Result

A dedicated local SSH key was generated for Codex staging access bootstrap.

Decision:

```text
SSH_PUBLIC_KEY_READY_FOR_HOSTINGER
```

Private key path, not printed and not committed:

```text
C:\Users\Ahmed Abdelkareem\.ssh\etamen_staging_codex
```

Public key path:

```text
C:\Users\Ahmed Abdelkareem\.ssh\etamen_staging_codex.pub
```

Fingerprint:

```text
SHA256:d30k0e2CZMALd85KmuTh1S9u2QMU3a1IEPtT2DbiMmM
```

Owner must add the public key to Hostinger SSH Keys / Authorized Keys for user `u797172084`.

No deployment, migration, seed, composer install, `.env` read, cache operation, staging file write, or APK build happened in Sprint 57.

## Next Step

After the owner confirms the public key was added, verify access only:

```text
ssh -i ~/.ssh/etamen_staging_codex -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && php -v"
```

If access works, proceed next sprint to backup-first deployment recovery.

---

# Sprint 65 Staging Access Gate

Date: 2026-05-11

Sprint 65 was access verification only. No deployment or server write was performed.

## Access Method Tried

SSH key-based access with the dedicated local key:

```text
~/.ssh/etamen_staging_codex
```

Target:

```text
u797172084@89.116.147.138:65002
```

## Result

Safe SSH verification failed:

```text
Permission denied (publickey,password).
```

No password retry was attempted. No `.env` was read. No migration, seed, composer install, cache clear, storage link, deploy, or server file write occurred.

## Public API Baseline

Current public staging API baseline:

- health: 200, status ok.
- readiness: 500 JSON error envelope.
- payment methods: 200 but empty.
- doctors: 200 with one public item.
- hospitals: 404.
- radiology scans: 200 but empty.
- gyms: 404.
- coaches: 404.

## Access Gate Decision

```text
STAGING_ACCESS_STILL_BLOCKED
```

Next required action remains Hostinger SSH/access repair. Deployment must not resume until safe access and backup feasibility are confirmed.
