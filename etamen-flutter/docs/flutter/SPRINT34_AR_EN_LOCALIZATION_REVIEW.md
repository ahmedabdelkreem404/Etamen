# Sprint 34 Arabic/English Localization Review

Date: 2026-05-08

## Arabic Default Status

PASS on emulator.

- App launched in Arabic by default after fresh install / cleared app data.
- RTL layout was visible across login, home, doctors, booking, payment, services, health, and account.
- Account screen shows language as Arabic by default.
- Login after logout reopened in Arabic.

## English Support Status

Supported, not removed.

- Existing language selector remains available from Account.
- New tests verify Arabic is the default before a saved preference exists.
- Website supports English via `/?lang=en`.
- Flutter English runtime smoke was not fully screenshot-captured in Sprint 34; this remains a secondary QA check, not a blocker for Arabic-first emulator polish.

## Copy Improved In Sprint 34

| Area | Change |
| --- | --- |
| Home greeting | Arabic greeting adjusted to feel natural and patient-facing. |
| Home search | Arabic placeholder changed to direct doctor search wording. |
| Home booking promo | Hero copy now emphasizes booking nearby doctors by specialty. |
| Empty upcoming appointment | Friendly Arabic text keeps doctor search primary. |
| Specialty labels | Short Arabic labels: dentist, heart, pharmacy, lab. |
| Website | Arabic is default; English copy remains available through query param. |

## Copy Intentionally Left Formal

- Legal pages.
- Medical disclaimers.
- AI assistant safety disclaimers.
- Refund/cancellation/legal/support entry points.

These areas should remain formal until product/legal review signs off.

## Remaining Copy Needing Owner/Legal Review

- Final Arabic landing marketing tone.
- Public launch legal copy.
- Provider-facing CTA wording on the landing page.
- English Flutter screenshots for a final bilingual visual pass.

