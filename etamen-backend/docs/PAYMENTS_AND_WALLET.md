# Payments And Wallet

## Allowed Payment Methods

Only these payment methods are allowed:

- Paymob
- Manual Vodafone Cash
- Manual InstaPay

No Stripe, PayPal, Braintree, Razorpay, Paystack, Rave, Paytm, or other gateways are part of the MVP.

## Payment Safety

- Frontend never marks a payment as verified.
- Paymob callbacks/webhooks require HMAC verification.
- Manual payments require admin proof review.
- Payment verification is idempotent.
- Invoices are idempotent per payment.

## Wallet Ledger Rules

- No mutable provider balance column.
- Balance is derived from `wallet_transactions`.
- Verified paid orders create hold and commission transactions.
- Completion/delivery creates release transactions.
- Withdrawals are allowed only from released available balance.
- Settlements cannot include the same transaction twice.

## Deferred

- Refund automation.
- Paymob transfers to providers.
- Bank transfer integration.
