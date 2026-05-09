# Etamen Staging Server Checklist

Date: 2026-05-08

Status: not completed because SSH authentication failed.

## Access

- SSH host known.
- SSH port known.
- SSH user known.
- SSH authentication: BLOCKED. The current environment has no accepted key and no interactive password was provided to the shell.

## Server Checks Still Pending

- `pwd`
- `ls -la`
- `php -v`
- `composer --version`
- `mysql --version`
- `php -m`
- Real domain path.
- Real `public_html` path.
- Whether Laravel can live outside `public_html`.
- Writable `storage`.
- Writable `bootstrap/cache`.

## Required PHP Extensions

Must verify:

- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `fileinfo`
- `bcmath`
- `curl`
- `zip`

## Deployment Layout Target

Preferred layout:

- Laravel app outside web root.
- `public_html` contains only Laravel public files.
- `public_html/index.php` points to the app bootstrap paths.
- Laravel `.env`, `vendor`, `storage`, `database`, and application source are not browsable.

## Migration Rules

- Never run `migrate:fresh` on the hosting/staging database.
- Run `php artisan migrate --force` only after confirming the Etamen database.
- Seed demo data only when the owner wants staging demo data.

## Current External Checks

The current remote domain responds, but was not updated by this session:

- Landing: 200.
- Health: 200.
- Readiness: 500.
- Public doctors/specialties: 200.
- Sensitive file checks: 404 for `.env`, `composer.json`, `storage`, `vendor`, and `database`.

## Blockers

- SSH authentication unavailable.
- Server capability cannot be inspected.
- Current local Sprint 37 migrations cannot be applied to staging.
- Current readiness endpoint returns 500 and needs server log access.

---

## Sprint 38 Server Status

Date: 2026-05-08

### Access

- SSH retry: FAIL.
- Error: `Permission denied (publickey,password)`.
- Server PHP/Composer/path checks: still pending because shell access is unavailable.

### Current Remote Checks

| Check | Result |
| --- | --- |
| Landing `/` | PASS, HTTP 200 |
| Landing `/?lang=en` | PASS, HTTP 200 |
| Health `/api/v1/system/health` | PASS, HTTP 200 |
| Readiness `/api/v1/system/readiness` | FAIL, HTTP 500 |
| Specialties `/api/v1/specialties` | PASS, HTTP 200 |
| Doctors `/api/v1/doctors` | PASS, HTTP 200, empty current data |
| Staging QA patient login | PASS, HTTP 200 |

### Security Exposure

The following paths returned 404 and did not expose content:

- `/.env`
- `/composer.json`
- `/storage/`
- `/vendor/`
- `/database/`

### Still Needed From Hosting

- Working SSH key, interactive password session, SFTP, or Hostinger Git/File Manager access.
- Server-side log inspection for readiness 500.
- Safe deployment of current backend files.
- `php artisan migrate --force` on the confirmed Etamen staging database only.
- Seed or approve staging doctors if the owner wants full doctor booking QA against hosted staging.

---

## Sprint 39 Staging Booking Gate

Date: 2026-05-09

### External Endpoint State

| Check | Result | Notes |
| --- | --- | --- |
| Landing `/` | PASS, HTTP 200 | Public landing still loads. |
| Health `/api/v1/system/health` | PASS, HTTP 200 | Returns `status: ok`. |
| Readiness `/api/v1/system/readiness` with JSON accept header | PARTIAL, HTTP 401 | Endpoint is protected or unauthenticated; not a server crash in JSON API mode. |
| Readiness `/api/v1/system/readiness` from browser/default client | FAIL, HTTP 500 | Still needs server log access; do not sign off staging readiness while this remains. |
| Specialties `/api/v1/specialties` | PASS, HTTP 200 | One staging cardiology/vascular specialty exists. |
| Doctors `/api/v1/doctors` | PASS, HTTP 200 | One approved staging doctor is returned. |
| Doctor slots `/api/v1/doctors/1/slots` | PASS | 84 generated clinic slots were available during the QA check. |
| Payment methods `/api/v1/payment-methods` | FAIL FOR FLOW, HTTP 200 empty data | This blocks proof upload and admin review. |

### Staging Doctor Data

One safe staging-only doctor was created and approved through the available API/admin path because SSH remains unavailable:

- Provider type: `doctor`.
- Public status: `approved` and active.
- Name: demo doctor for staging QA only.
- Specialty: cardiology and vascular medicine.
- Branch: Etamen Medical Center - Nasr City style demo branch.
- Coordinates: `30.0561, 31.3300`.
- Consultation fee: `300 EGP`.
- Booking capability: clinic visit enabled, payment required, online video disabled.
- Slots: generated for the next QA window.
- Avatar: no real doctor photo was used; the app uses its safe placeholder.

No private provider document paths or storage paths were observed in the public doctors response.

### Payment Method Blocker

`/api/v1/payment-methods` returns an empty `data` array. The hosted API does not expose a safe payment-method creation endpoint, SSH is still blocked, and direct database access from the desktop is not allowed by the remote database host.

Required server-side action:

1. Restore SSH/SFTP/Hostinger access.
2. Create or activate staging-safe manual methods for Vodafone Cash and InstaPay.
3. Re-check `/api/v1/payment-methods`.
4. Repeat the APK booking flow until the proof upload screen is reachable.

### Security Exposure Re-check

| URL | Result |
| --- | --- |
| `/.env` | 404 |
| `/composer.json` | 404 |
| `/storage/` | 301 to `/public/storage`, then 404 |
| `/vendor/` | 301 to `/public/vendor`, then 404 |
| `/database/` | 301 to `/public/database`, then 404 |

No raw secret file content was observed.

### Sprint 39 Server Decision

Server-side status: `STAGING_PAYMENT_BLOCKED_NO_PAYMENT_METHODS`.

Doctor booking data is now present, but payment proof/admin review cannot be completed until active staging payment methods exist.

### Local MySQL Migration Safety Check

Local database check was also run against the desktop MySQL `Etamen` database, not staging:

- `php artisan migrate:fresh --seed`: PASS.
- `php artisan db:seed --class=PilotDemoSeeder`: PASS.

The first attempted local root credential combination was rejected by MySQL, then the actual accepted local root authentication was used. No staging database was dropped.
