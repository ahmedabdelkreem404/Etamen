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
