# Pilot Operations SOP

This SOP prepares Etamen for a future supervised pilot. It is local-only until staging is deployed and accepted. It does not approve production, public launch, app-store release, or external users.

## 1. Pilot Participants

- Internal team only until staging is accepted.
- No real patients before legal, privacy, support, and refund policies are reviewed.
- No real payment collection before the payment proof SOP and finance process are approved.
- No external providers before onboarding, document review, and support escalation rules are approved.

## 2. Platform Admin Responsibilities

- Review manual payment proofs from the admin payment queue.
- Accept only proofs that match the expected amount, account, and booking/payment reference.
- Reject invalid proofs with a clear reason.
- Review provider approval requests using checklist metadata only.
- Handle support tickets without giving medical diagnosis or treatment advice.
- Review refund requests and disputes using the foundation statuses.
- Monitor audit log events for payment, provider, support, refund, and dispute actions.

## 3. Provider Responsibilities

- Keep schedules, availability, gym classes, and coach sessions accurate.
- Review provider booking/order queues regularly.
- Avoid medical diagnosis through support tickets or operational notes.
- Protect patient information and never export patient data outside approved tools.
- Use provider support tickets for operational issues instead of informal channels.

## 4. Patient Safety

- Etamen is not for emergency use.
- Etamen does not replace a doctor, emergency service, or clinical judgment.
- Support and AI experiences must not diagnose, prescribe, or stop medication.
- Emergency copy:
  - Arabic: "لو الحالة طارئة أو فيها خطر فوري، اتصل بالإسعاف أو توجه لأقرب طوارئ فورًا."
  - English: "If this is an emergency or immediate risk, call emergency services or go to the nearest emergency department now."

## 5. Manual Payment Proof SOP

- Admin opens Platform Admin workspace, then payment reviews.
- Admin checks payment context, amount, method, proof metadata, and user summary.
- Valid proof may be accepted; invalid or unclear proof must be rejected with a reason.
- Accept/reject actions must create audit events.
- Manual proof review does not move money automatically.
- Flutter must never verify payment or mark bookings paid locally.

## 6. Refund SOP

Statuses:

- `requested`
- `under_review`
- `approved`
- `rejected`
- `processed`
- `cancelled`

Rules:

- `approved` means platform decision only.
- `processed` means manual confirmation only.
- There is no live gateway refund integration in the local foundation.
- Admin notes are required for approval, rejection, and processing.
- Patient-facing responses must not expose internal admin notes.

## 7. Dispute SOP

Statuses:

- `open`
- `investigating`
- `waiting_user`
- `waiting_provider`
- `resolved`
- `rejected`
- `closed`

Rules:

- Admin assigns or investigates dispute records from the admin operations center.
- Internal notes stay admin-only.
- Provider responses must be scoped to the provider involved.
- All decisions must be logged safely in the audit log.

## 8. Support SOP

Categories:

- `payment`
- `booking`
- `provider`
- `technical`
- `medical_safety`
- `refund`
- `other`

Priorities:

- `low`
- `normal`
- `high`
- `urgent`

Response style:

- Be factual, clear, and non-diagnostic.
- Ask for booking/payment reference when needed.
- Use internal notes for investigation details.
- Escalate medical safety issues to doctor/emergency guidance.
- Do not expose internal notes to patients or providers.
