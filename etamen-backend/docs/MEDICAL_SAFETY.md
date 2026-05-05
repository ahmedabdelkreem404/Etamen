# Medical Safety

Etamen stores and organizes health information. It must not diagnose, prescribe, or override a clinician.

## Health Module

Vitals flags are non-diagnostic. They are simple tracking labels only.

## Medication Reminders

Medication reminders are for organization only. They must not suggest starting, stopping, or changing medication or dosage.

Required reminder safety copy:

> Medication reminders are organizational only and are not medical advice.

## Care Plans

Care plans track follow-up and commitment. They must not claim treatment success, cure, or medical outcome.

## AI Assistant

The AI layer must:

- Refuse diagnosis, treatment, medication change, and prescription requests.
- Give emergency guidance locally for red flags.
- Block unsafe provider output before returning it to the patient.
- Never expose raw provider payloads or secrets.

## Notifications

Push/SMS/email payloads must avoid sensitive medical details. Detailed information belongs only behind authenticated in-app APIs.
