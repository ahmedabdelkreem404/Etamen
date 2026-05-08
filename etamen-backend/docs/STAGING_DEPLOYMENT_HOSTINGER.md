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
| `/api/v1/doctors` | 200 | Public doctors respond with seeded/demo data. |
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
