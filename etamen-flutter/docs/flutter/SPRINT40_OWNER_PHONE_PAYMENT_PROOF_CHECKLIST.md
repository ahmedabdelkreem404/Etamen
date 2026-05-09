# Sprint 40 Owner Phone Payment Proof Checklist

Date: 2026-05-09

This checklist must be completed on a real Android phone after staging returns active manual payment methods from:

```text
https://etamen.inolty.com/api/v1/payment-methods
```

Required active methods:

- `manual_vodafone_cash`
- `manual_instapay`

## APK

Use:

```text
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-payment-methods-proof-gate.apk
```

APK details:

- API base: `https://etamen.inolty.com/api/v1`.
- ABIs: `armeabi-v7a`, `arm64-v8a`, `x86_64`.
- SHA-256: `F07C7E0A705F90B266719B92CE3EA839240A7327D231F827ED064C7A65C92C14`.

## Before Testing

1. Uninstall any old Etamen APK from the phone.
2. Install the Sprint 40 APK.
3. Confirm the phone has internet access.
4. Confirm `/api/v1/payment-methods` returns Vodafone Cash and InstaPay.

## Phone Flow

| Step | PASS/FAIL | Screenshot | Notes |
| --- | --- | --- | --- |
| Login to staging account |  |  |  |
| Open Home |  |  |  |
| Open Doctors |  |  |  |
| Confirm staging doctor appears |  |  |  |
| Open doctor profile |  |  |  |
| Choose available slot |  |  |  |
| Submit booking |  |  |  |
| Choose Vodafone Cash or InstaPay |  |  |  |
| Upload a real test image from gallery/camera |  |  |  |
| Confirm pending review state appears |  |  |  |
| Send payment/appointment reference to admin tester |  |  |  |
| Admin accepts the same payment |  |  |  |
| Reopen or refresh app |  |  |  |
| Confirm appointment/payment state updated |  |  |  |
| Logout |  |  |  |
| Reopen app and confirm logged-out state remains |  |  |  |

## Hard Rules

- Do not use real production collection details unless explicitly approved.
- Do not mark payment as paid from Flutter.
- Do not bypass admin review.
- Do not approve pilot until the uploaded proof and admin review are verified on the same payment.
