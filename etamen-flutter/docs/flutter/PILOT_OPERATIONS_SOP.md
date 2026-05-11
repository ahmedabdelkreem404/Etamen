# Pilot Operations SOP

This Flutter SOP describes how the local app should be used in a future supervised pilot rehearsal. It is not a staging, production, public launch, or app-store approval.

## Participation Rules

- Internal team only until staging is accepted.
- No real patients before legal/privacy review.
- No real payments before payment proof and finance SOP approval.
- No external providers before onboarding and support escalation approval.

## Admin Responsibilities in App

- Use Platform Admin workspace.
- Review manual payment queues.
- Accept or reject proof with confirmation.
- Review provider approvals using safe metadata.
- Reply to support tickets without diagnosis.
- Review refunds/disputes with notes.
- Check audit log after sensitive actions.

## Provider Responsibilities in App

- Use provider workspace switcher.
- Keep schedules, slots, classes, and availability accurate.
- Review booking/order queues.
- Do not give medical diagnosis through support.
- Protect patient data and avoid screenshots with private content.

## Patient Safety in App

- Etamen is not for emergency use.
- App support does not diagnose, prescribe, or stop medication.
- Result screens do not interpret lab/radiology results.
- Emergency Arabic copy: "لو الحالة طارئة أو فيها خطر فوري، اتصل بالإسعاف أو توجه لأقرب طوارئ فورًا."
- Emergency English copy: "If this is an emergency or immediate risk, call emergency services or go to the nearest emergency department now."

## Payment Proof SOP

- Patient uploads proof through the app.
- Flutter never verifies payment.
- Admin reviews proof in Platform Admin workspace.
- Accepted proof updates linked context from backend.
- Rejected proof requires a reason.

## Refund and Dispute SOP

- Refund approval is a platform decision only.
- Refund processing is manual confirmation only.
- No live refund gateway exists.
- Disputes require notes and audit trail.
- Internal notes must remain hidden from patient/provider UI.

## Support SOP

- Patient/provider can create support tickets.
- Replies should be operational and non-diagnostic.
- Medical safety issues must be escalated to doctor/emergency guidance.
- Internal notes are admin-only.
