# Sprint 39 Owner Phone Test Checklist

Date: 2026-05-09

API:

```text
https://etamen.inolty.com/api/v1
```

APK:

```text
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-staging-doctor-payment-gate.apk
```

APK details:

- Size: `53.64 MB`.
- SHA-256: `87DFB6A3863C6483CDC6B47BEFB52D311983317E65764BDC33DD2C1817D2FF3A`.
- Includes `armeabi-v7a`, `arm64-v8a`, and `x86_64`.

## Current Important Blocker

Do not mark this checklist fully passed until staging payment methods are active.

Current backend state:

- Doctor booking data: ready.
- Payment methods: blocked, `/api/v1/payment-methods` returns an empty list.
- Proof upload: cannot be completed until payment methods appear.
- Admin review: cannot be completed until a real proof is uploaded.

## Phone Checklist

| Step | Result | Screenshot | Notes |
| --- | --- | --- | --- |
| 1. Uninstall any old Etamen APK from the phone. |  |  |  |
| 2. Install the latest staging APK. |  |  |  |
| 3. Open the app. |  |  |  |
| 4. Login with the staging QA account. |  |  |  |
| 5. Confirm Home loads. |  |  |  |
| 6. Open Doctors. |  |  |  |
| 7. Confirm the staging doctor appears. |  |  |  |
| 8. Open doctor profile. |  |  |  |
| 9. Choose an available slot. |  |  |  |
| 10. Submit booking. |  |  |  |
| 11. Confirm payment methods appear. |  |  | Currently expected to fail until server payment methods are activated. |
| 12. Choose a manual payment method. |  |  |  |
| 13. Upload a real proof image from gallery or camera. |  |  | Do not fake this step. |
| 14. Confirm pending review state. |  |  |  |
| 15. Send payment/appointment reference to the admin tester. |  |  |  |
| 16. Admin accepts payment. |  |  |  |
| 17. Reopen or refresh app. |  |  |  |
| 18. Confirm appointment/payment state updated. |  |  |  |
| 19. Logout. |  |  |  |
| 20. Reopen app and confirm logged-out state remains. |  |  |  |

## Pass Criteria

The phone gate passes only when all of these are true:

- Login works on the real Android phone.
- Doctor appears from staging API.
- Booking succeeds.
- Active manual payment methods appear.
- Real proof upload succeeds from gallery or camera.
- Admin reviews the same proof.
- Flutter shows the updated payment/appointment state.
- Logout/session restore works.
- No private file paths or raw backend statuses appear to the patient.

## If Payment Methods Are Still Empty

Result should be:

```text
FAIL - blocked by staging payment methods
```

Next action:

1. Restore hosting/server access.
2. Add staging-safe Vodafone Cash and InstaPay methods.
3. Reinstall or reopen the same APK.
4. Repeat from step 11.
