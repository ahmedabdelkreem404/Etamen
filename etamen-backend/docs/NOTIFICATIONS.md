# Notifications

## Foundation

Notifications support:

- In-app notifications.
- Notification tokens.
- Preferences and quiet hours.
- Templates.
- Dispatch queue/audit records.
- Scheduler run monitoring.

## Providers

Current production-safe foundation:

- Fake provider for tests/local.
- FCM placeholder.
- Email placeholder.
- SMS placeholder.
- WhatsApp placeholder.

Real providers must be configured only on the backend. No provider secret belongs in Flutter.

## Payload Safety

The sanitizer removes:

- API keys and secrets.
- Raw private file paths.
- Payment secrets.
- Wallet internal financial metadata from patient payloads.
- AI raw prompts and raw provider responses.

## Jobs

Operational jobs create and dispatch notifications idempotently. They are safe to run repeatedly.
