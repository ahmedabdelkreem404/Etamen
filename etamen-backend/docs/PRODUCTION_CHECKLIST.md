# Production Checklist

## Environment

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` generated and private.
- Database credentials configured securely.
- Queue worker supervised.
- Scheduler cron configured.
- `medical_private` storage writable by the app user.

## Credentials

Required before live launch:

- Paymob live credentials and HMAC secret.
- AI provider credentials if AI is enabled.
- Notification provider credentials if real push/email/SMS/WhatsApp is enabled.

## Security

- No real secrets committed.
- Admin users reviewed.
- Sanctum token handling verified.
- CORS restricted to expected clients.
- Private files served only through authorized controllers.

## Money

- Paymob live callback URLs configured.
- Manual payment review SOP documented.
- Commission rules configured.
- Withdrawal approval SOP documented.
- Refund process remains manual/deferred.

## Medical Safety

- AI safety prompts reviewed.
- Red-flag/refusal tests passing.
- Medication and care plan disclaimers preserved.
- Push notification payloads reviewed for privacy.
