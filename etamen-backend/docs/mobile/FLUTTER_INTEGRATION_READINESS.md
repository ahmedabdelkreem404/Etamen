# Flutter Integration Readiness

Backend contract is ready for Flutter implementation when the Sprint 13 contract tests pass.

## Base URL

Use environment-specific base URLs:

- Local: `http://localhost:8000/api/v1`
- Android emulator: `http://10.0.2.2:8000/api/v1`
- Production: configured HTTPS API host

## Recommended Packages

- `dio`
- `flutter_secure_storage`
- `go_router`
- `riverpod` or `bloc`
- `freezed`
- `json_serializable`
- `easy_localization` or `intl`

## Suggested Flutter Feature Modules

- `auth`
- `account`
- `doctors`
- `appointments`
- `payments`
- `pharmacy`
- `labs`
- `health`
- `medications`
- `care_plans`
- `ai`
- `notifications`

## API Client Rules

- Attach `Authorization: Bearer <token>` globally after login.
- Handle `401` globally by clearing secure storage and navigating to login.
- Map `422` field errors to form fields.
- Map `429` to a temporary retry-later message.
- Retry only safe GET requests.
- Never retry payment mutation blindly.
- Always parse the standard envelope before DTO parsing.

## Localization And RTL

- Arabic is the primary UX language.
- English labels exist for many DTOs.
- UI must support RTL for Arabic.
- Do not hardcode backend enum display strings; use local translation maps based on `STATUS_ENUMS_FOR_MOBILE.md`.

## Offline Strategy

Offline sync is not implemented yet. Flutter may cache read-only public catalog lists and authenticated lists carefully, but mutation queues should wait for a later offline sprint.

## Production Concerns Outside Flutter Contract

- Real Paymob live credentials and callback URLs.
- Real FCM/APNS credentials.
- Real AI provider credentials.
- Queue workers and scheduler cron.
- Refund automation remains deferred.

## Current Blocking Status

No backend contract blocker is expected if:

- `php artisan test` passes.
- `docs/api/openapi-mobile-mvp.yaml` remains aligned with critical routes.
- Flutter keeps payment, AI, medical safety, file privacy, and ownership rules described in this folder.
