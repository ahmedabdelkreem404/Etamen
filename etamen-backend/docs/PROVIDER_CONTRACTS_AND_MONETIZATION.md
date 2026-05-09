# Provider Contracts And Monetization

Date: 2026-05-08  
Sprint: 36 - Unified Provider Platform Foundation

## Purpose

Sprint 36 adds a provider contract foundation without replacing the existing wallet/commission/payment behavior.

Current MVP payment and wallet flows remain the source of truth for live behavior.

## Table Added

`provider_contracts`

Fields:
- `provider_id`
- `contract_type`
- `commission_rate`
- `fixed_commission_amount`
- `subscription_plan_id`
- `settlement_cycle`
- `pay_at_branch_allowed`
- `online_payment_required`
- `starts_at`
- `ends_at`
- `status`

## Contract Types

Supported:
- `commission_only`
- `subscription_only`
- `hybrid`
- `custom`

## Settlement Cycles

Supported:
- `daily`
- `weekly`
- `biweekly`
- `monthly`

## Public Payment Flags

The patient public API can expose safe payment options only:
- `online_payment_required`
- `pay_at_branch_enabled`

`pay_at_branch_enabled` is true only when:
- provider booking settings allow pay at branch.
- active provider contract allows pay at branch.

The public API does not expose:
- commission rate.
- fixed commission amount.
- subscription plan id.
- internal contract terms.

---

## Sprint 43 Hospital Price Policy

Sprint 43 added hospital booking context to appointments, but did not change the existing payment workflow.

When a booking is made through a validated hospital doctor link:

1. `hospital_doctors.consultation_fee` is used if it is not null.
2. Otherwise `doctor_profiles.consultation_fee` is used.
3. The frontend cannot set or override the price.

The payment created for the appointment uses the backend-resolved appointment price.

Hospital appointment reporting can summarize gross, pending, and verified paid amounts, but it does not expose payment proof files or internal contract terms.

## Admin Management

Admin can create provider contracts through:
- `POST /api/v1/admin/providers/{provider}/contracts`

Filament foundation resource added:
- `ProviderContractResource`

## Existing Payment Safety

Sprint 36 does not:
- verify payments from Flutter.
- trust frontend prices.
- change current payment status flow.
- replace `commission_rules`.
- replace wallet settlement logic.

## Remaining Work

Future sprints:
- integrate provider contracts with wallet/commission rules intentionally.
- contract history/versioning.
- provider-facing contract summary.
- pay-at-branch workflow if product approves it.
- finance reconciliation reports.
