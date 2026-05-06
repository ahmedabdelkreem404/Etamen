# Etamen Flutter Local Setup

Sprint 14 uses the clean Flutter app in this folder: `etamen-flutter`.

## Install Packages

```bash
flutter pub get
```

## Run Laravel Backend

From the backend folder:

```bash
cd ../etamen-backend
php artisan serve --host=0.0.0.0 --port=8000
```

## Configure API Base URL

The app reads:

```bash
--dart-define=ETAMEN_API_BASE_URL=<url>
```

Defaults:

- Android emulator: `http://10.0.2.2:8000/api/v1`
- iOS simulator: `http://127.0.0.1:8000/api/v1`
- Physical device: use your machine LAN IP, for example `http://192.168.1.20:8000/api/v1`

Examples:

```bash
flutter run --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1
flutter run --dart-define=ETAMEN_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

## Useful Checks

```bash
dart format .
flutter analyze
flutter test
```

## Common Issues

- `401`: token is invalid or expired; the app clears it and returns to login.
- `422`: backend validation errors; show field messages.
- `429`: too many requests; wait and retry later.
- Network error on Android emulator: use `10.0.2.2`, not `localhost`.
- Network error on physical device: bind Laravel to `0.0.0.0` and use the computer LAN IP.

## Security Notes

- Do not place backend secrets, Paymob secrets, AI keys, FCM server keys, or admin tokens in Flutter.
- Tokens are stored through `flutter_secure_storage`.
- Flutter must not send `patient_user_id`, `user_id`, trusted price, appointment status, or payment verification flags.

## Sprint 15 Payment Testing Notes

1. Run the Laravel backend and seed an active doctor with a paid appointment slot.
2. Login as a patient from Flutter.
3. Book a paid doctor appointment.
4. If the backend returns `pending_payment`, Flutter opens the payment page using the returned `payment_id`.
5. Manual test:
   - Choose Vodafone Cash or InstaPay.
   - Follow backend-provided instructions.
   - Upload a proof screenshot from the gallery.
   - Wait on the payment status page while the backend/admin reviews the proof.
6. Paymob test:
   - Choose Paymob.
   - Flutter requests a checkout session from the backend.
   - If `checkout_url` is returned, Flutter opens it externally.
   - Returning from checkout is not proof of payment; use the status screen to poll backend state.

Flutter never marks payments verified and never stores Paymob secrets. Paymob checkout requires backend `.env` configuration and real credentials when testing against a live gateway.

## Sprint 16 My Appointments Testing Notes

1. Run the backend and login as a patient.
2. Book an appointment from the Doctors flow.
3. Open **مواعيدي** from the bottom navigation.
4. Pull to refresh to reload the current patient's appointments from:
   `GET /api/v1/appointments`.
5. Tap any appointment to open:
   `GET /api/v1/appointments/{appointment}`.
6. For a paid appointment in `pending_payment`, tap **ادفع الآن** or **متابعة الدفع** to continue the Sprint 15 payment flow.
7. On the appointment details page, payment status is loaded from:
   `GET /api/v1/payments/{payment}/status`.
8. To test cancellation, use a cancellable appointment state such as:
   `pending_payment`, `pending_payment_review`, `confirmed`, or `accepted`.
   The app calls:
   `POST /api/v1/appointments/{appointment}/cancel`.
9. If the backend blocks cancellation for paid/refund-sensitive appointments, Flutter shows the backend message or:
   `لا يمكن إلغاء موعد مدفوع من التطبيق حاليًا، تواصل مع الدعم.`

Flutter never sends `patient_user_id`, `provider_id`, appointment status, payment status, or trusted amount when listing, viewing, paying, or cancelling appointments.

## Sprint 17 Pharmacy Testing Notes

1. Run the Laravel backend and seed an approved active pharmacy with active products.
2. Login as a patient from Flutter.
3. Open **الصيدليات** from the bottom navigation.
4. Choose a pharmacy and open its products:
   `GET /api/v1/pharmacies/{pharmacy}/products`.
5. Add products to the local cart. The local subtotal is display-only; the backend confirms real totals.
6. If any selected product requires a prescription, open **رفع روشتة** and upload an image from the gallery:
   `POST /api/v1/pharmacy/prescriptions`.
7. Create the order from the cart:
   `POST /api/v1/pharmacy/orders`.
8. Open **طلبات الصيدلية** to list current patient orders:
   `GET /api/v1/pharmacy/orders`.
9. Open order details:
   `GET /api/v1/pharmacy/orders/{order}`.
10. If the order is payable, tap **متابعة الدفع**. Flutter calls:
    `POST /api/v1/pharmacy/orders/{order}/pay`
    and then reuses the existing Sprint 15 payment UI.

Known limitations for Sprint 17:

- Prescription upload is image-based through `image_picker`; PDF prescription picking is not enabled yet.
- No delivery tracking map.
- No order cancellation UI.
- No pharmacy provider dashboard.

Flutter never sends `patient_user_id`, trusted prices, order totals, commission fields, provider net fields, `order_status`, or `payment_status` when creating pharmacy orders.

## Sprint 18 Labs Testing Notes

1. Run the Laravel backend and seed an approved active lab with active tests/packages.
2. Login as a patient from Flutter.
3. Open **المعامل** from the bottom navigation.
4. Choose a lab and open tests/packages:
   `GET /api/v1/labs/{lab}/tests`
   and, when available, `GET /api/v1/labs/{lab}/packages`.
5. Add tests/packages to the local lab order cart. The displayed subtotal is local only; the backend calculates final totals.
6. Choose sample collection:
   - **زيارة الفرع**
   - **سحب عينة من المنزل** with a required home address.
7. Create the order:
   `POST /api/v1/lab/orders`.
8. Open **طلبات المعمل** to list current patient orders:
   `GET /api/v1/lab/orders`.
9. Open order details:
   `GET /api/v1/lab/orders/{order}`.
10. If the order is payable, Flutter calls:
    `POST /api/v1/lab/orders/{order}/pay`
    and then reuses the existing payment UI.
11. If a result is ready and visible, download it through:
    `GET /api/v1/lab/results/{result}/download`.

Known limitations for Sprint 18:

- No map tracking.
- No patient lab order cancellation UI.
- Result opening is a foundation only: Flutter downloads the authorized file to a temporary local path and shows that path in-app. A later sprint can add native file opening.

Contract note:

- Current backend routes and Sprint 13 OpenAPI use `/lab/orders` and `/lab/results/{result}/download`. If a future contract aliases `/lab-orders`, update `ApiEndpoints` in one place.

Flutter never sends `patient_user_id`, trusted prices, order totals, commission fields, provider net fields, `order_status`, or `payment_status` when creating lab orders, and never uploads lab results from the patient app.

## Sprint 19 Health / Vitals Testing Notes

1. Run the Laravel backend and login as a patient from Flutter.
2. Open **صحتي** from the bottom navigation.
3. The dashboard loads:
   - `GET /api/v1/health/profile`
   - `GET /api/v1/health/summary`
   - `GET /api/v1/health/vitals/latest`
   - `GET /api/v1/health/vitals/trends`
4. To edit basic profile fields, open **الملف الصحي** then **تعديل الملف**. Flutter calls:
   `PUT /api/v1/health/profile`.
5. To add a vital, tap **إضافة قياس** or one of the quick vital chips. Flutter calls:
   `POST /api/v1/health/vitals`.
6. To list records, open **القياسات الصحية**. Flutter calls:
   `GET /api/v1/health/vitals` with a bounded `per_page`.
7. Supported Sprint 19 vital types:
   - blood pressure
   - blood sugar
   - heart rate
   - oxygen saturation
   - temperature
   - weight
   - sleep
   - mood
   - symptoms note

Safety notes:

- Flutter displays backend flags as non-diagnostic follow-up labels only.
- Flutter does not diagnose, advise treatment, recommend medication, or suggest changing/stopping doses.
- Flutter never sends `patient_user_id`, `user_id`, `source`, `flag`, `unit`, diagnosis fields, treatment fields, or calculated backend fields when creating vitals.
- The current backend health summary route is `/api/v1/health/summary`; Sprint 19 prompt referenced `/api/v1/health/vitals/summary`, so Flutter uses the actual implemented backend route.
