# Admin Operations Runbook

This runbook is for the local Platform Admin Operations Center. It does not replace Filament and does not approve staging or production operations.

## Local Admin Login

- Local/demo QA account: `a@b.co`
- This account is local/testing only.
- Do not use fake QA credentials in staging, production, or real user environments.
- The account uses normal login and does not bypass authentication.

## Open Platform Admin Workspace

1. Login as platform admin.
2. Open Account.
3. Open workspace switcher.
4. Select Platform Admin workspace.
5. Confirm dashboard counts and quick actions load.

## Review Pending Payment

1. Open Payment Reviews.
2. Open payment details.
3. Check amount, method, linked context, and proof metadata.
4. Confirm no raw proof path is visible.
5. Accept only if proof is valid.
6. Reject unclear proof with a reason.
7. Confirm linked booking/order status updates through backend rules.

## Review Provider Approval

1. Open Provider Approvals.
2. Open provider details.
3. Review safe provider summary and document checklist metadata.
4. Approve if documents and profile are acceptable.
5. Reject or suspend with a reason if unsafe or incomplete.
6. Never expose raw national ID, tax, commercial, or bank documents in UI/API.

## Support Tickets

1. Open Support Tickets.
2. Open ticket details.
3. Reply to the user when appropriate.
4. Add internal notes for investigation details only.
5. Close only when resolved.
6. Never include medical diagnosis or private internal details in user replies.

## Refund Requests

1. Open Refunds.
2. Open refund details.
3. Mark under review when investigation starts.
4. Approve/reject with admin notes.
5. Mark processed only after manual confirmation.
6. Remember: approved is a platform decision, processed is manual confirmation, no live gateway refund exists locally.

## Disputes

1. Open Disputes.
2. Open dispute details.
3. Assign or investigate.
4. Resolve with a safe note.
5. Close when no further action is needed.
6. Keep internal notes admin-only.

## Audit Log

- Review audit log after payment/provider/support/refund/dispute actions.
- Audit summaries must be safe and must not contain secrets or private file paths.

## Admin Must Never

- Share payment proof files externally.
- Reveal provider private documents.
- Give medical diagnosis or treatment.
- Mark payment verified from Flutter.
- Use fake QA credentials outside local/testing.
- Invite external users before staging, legal, privacy, and support gates are accepted.
