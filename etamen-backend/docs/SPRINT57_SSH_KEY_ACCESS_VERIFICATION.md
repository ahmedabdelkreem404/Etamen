# Sprint 57 - SSH Key Access Verification

Date: 2026-05-11

Scope: SSH key bootstrap and access verification only.

This sprint intentionally did not deploy, migrate, seed, run composer install, read `.env`, clear caches, write staging files, or build APKs.

## Decision

```text
SSH_PUBLIC_KEY_READY_FOR_HOSTINGER
```

The dedicated local SSH key was generated and is ready for the project owner to add to Hostinger. SSH access verification must wait until the public key is installed on the server account.

## Local SSH Folder Check

Initial local SSH folder contents:

- `known_hosts`
- `known_hosts.old`

There was no usable private/public SSH key for the Hostinger staging server.

## Generated Key

Generated dedicated key:

```text
C:\Users\Ahmed Abdelkareem\.ssh\etamen_staging_codex
C:\Users\Ahmed Abdelkareem\.ssh\etamen_staging_codex.pub
```

Key type:

```text
ed25519
```

Fingerprint:

```text
SHA256:d30k0e2CZMALd85KmuTh1S9u2QMU3a1IEPtT2DbiMmM
```

Private key handling:

- Private key was not printed.
- Private key was not copied into the repository.
- Private key was not committed.

Public key handling:

- Public key was shown to the owner in the Sprint 57 report.
- Public key is safe to paste into Hostinger SSH Keys / Authorized Keys for user `u797172084`.

## Owner Action Required

Add the public key from:

```text
C:\Users\Ahmed Abdelkareem\.ssh\etamen_staging_codex.pub
```

to Hostinger SSH Keys / Authorized Keys for:

```text
u797172084
```

Do not upload the private key.

## SSH Verification Result

Not run after key generation because the owner has not yet confirmed the public key was added to Hostinger.

Verification command for the next step:

```text
ssh -i ~/.ssh/etamen_staging_codex -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new -p 65002 u797172084@89.116.147.138 "pwd && php -v"
```

Allowed after verification:

- `pwd`
- `whoami`
- `php -v`
- `composer --version || true`
- `ls -la`
- `git rev-parse --short HEAD` only inside a git repo
- `php artisan --version` only inside Laravel backend

Still not allowed in Sprint 57:

- deploy
- migrate
- seed
- composer install
- cache clear/cache build
- storage link
- reading `.env`
- writing server files

## Safe Server Info

Not available yet because SSH access is not verified.

## Next Sprint Recommendation

After the owner confirms the public key was added, run the access-only verification command.

If it succeeds, the next implementation sprint should be:

```text
Sprint 58 - Backup + Deploy Latest Main + Migrations + Readiness/Data Recovery
```

Sprint 58 must start with database/code/`.env` backup before any deployment or migration.
