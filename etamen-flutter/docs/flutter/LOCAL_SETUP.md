# Etamen Flutter Local Setup

Sprint 14 uses the clean Flutter app in this folder: `etamen-flutter`.

## Required Flutter SDK

This project was generated with Flutter `3.32.5` / Dart `3.8.1`.

If `flutter pub get` says the current Dart SDK is `3.0.6`, your terminal is using an older Flutter SDK from `PATH`. On this workstation the project points to:

```powershell
C:\DevFlutter\flutter\bin\flutter.bat
```

Use either:

```powershell
C:\DevFlutter\flutter\bin\flutter.bat pub get
C:\DevFlutter\flutter\bin\flutter.bat run --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1
```

Or update your Windows `PATH` so `C:\DevFlutter\flutter\bin` appears before any old Flutter installation.

You can also use the project wrapper:

```powershell
.\scripts\project_flutter.ps1 --version
.\scripts\project_flutter.ps1 pub get
.\scripts\project_flutter.ps1 run --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1
```

The wrapper also redirects Gradle cache and temporary files away from full drives:

- `GRADLE_USER_HOME` defaults to `I:\Etamen\.gradle`
- `TEMP` / `TMP` default to `I:\Etamen\.tmp`

This matters if your environment has something like `GRADLE_USER_HOME=D:\gradle_home` while `D:` has no free space. To run without the wrapper, set these variables first:

```powershell
New-Item -ItemType Directory -Force I:\Etamen\.gradle, I:\Etamen\.tmp
$env:GRADLE_USER_HOME="I:\Etamen\.gradle"
$env:TEMP="I:\Etamen\.tmp"
$env:TMP="I:\Etamen\.tmp"
C:\DevFlutter\flutter\bin\flutter.bat run --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1
```

If your Flutter 3.32+ SDK lives somewhere else:

```powershell
$env:ETAMEN_FLUTTER_BIN="D:\flutter\bin\flutter.bat"
.\scripts\project_flutter.ps1 --version
```

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

## Sprint 20 Medication Reminders Testing Notes

1. Run the Laravel backend and login as a patient from Flutter.
2. Open **الأدوية** from the bottom navigation.
3. The dashboard loads:
   - `GET /api/v1/medications/reminders`
   - `GET /api/v1/medications/today`
   - `GET /api/v1/medications/upcoming`
   - `GET /api/v1/medications/adherence`
   - `GET /api/v1/medications/refills`
4. To create a reminder, open **إضافة تذكير دواء**. Flutter calls:
   `POST /api/v1/medications/reminders`.
5. Supported Sprint 20 frequencies in the create UI:
   - once daily
   - twice daily
   - three times daily
   - custom times
   - every X hours
   - as needed
6. `specific_days` is parsed by the model/request layer but the day-picker UI is deferred so Flutter does not send an incomplete request.
7. Open **جرعات اليوم** to load:
   `GET /api/v1/medications/today`.
8. Mark a dose as taken or skipped:
   - `POST /api/v1/medications/reminders/{reminder}/taken`
   - `POST /api/v1/medications/reminders/{reminder}/skipped`
9. Open **الالتزام** to load:
   `GET /api/v1/medications/adherence`.
10. From reminder details, test pause/resume/cancel and refill actions:
   - `POST /api/v1/medications/reminders/{reminder}/pause`
   - `POST /api/v1/medications/reminders/{reminder}/resume`
   - `POST /api/v1/medications/reminders/{reminder}/cancel`
   - `POST /api/v1/medications/reminders/{reminder}/refill-done`
   - `POST /api/v1/medications/reminders/{reminder}/refill-skipped`

Safety notes:

- Flutter only records patient-entered medication organization data.
- Flutter does not prescribe, recommend, start, stop, or change any medication or dosage.
- Flutter never sends `patient_user_id`, `user_id`, `source`, `status`, calculated adherence values, diagnosis fields, treatment fields, or `missed` logs from the UI-created log requests.
- Medication adherence is displayed as follow-up/organization only and never as treatment success or failure.

## Sprint 21 Care Plans / Nutrition Testing Notes

1. Run the Laravel backend and login as a patient from Flutter.
2. Open **خطط المتابعة** from the bottom navigation.
3. The list loads:
   `GET /api/v1/care-plans`.
4. Open a plan to load details and tracking data:
   - `GET /api/v1/care-plans/{plan}`
   - `GET /api/v1/care-plans/{plan}/days`
   - `GET /api/v1/care-plans/{plan}/meals`
   - `GET /api/v1/care-plans/{plan}/foods`
   - `GET /api/v1/care-plans/{plan}/instructions`
   - `GET /api/v1/care-plans/{plan}/checkins`
   - `GET /api/v1/care-plans/{plan}/meal-logs`
   - `GET /api/v1/care-plans/{plan}/progress`
5. If the plan is active, tap **تسجيل متابعة اليوم**. Flutter calls:
   `POST /api/v1/care-plans/{plan}/checkins`.
6. If the plan is active, tap **تسجيل وجبة**. Flutter calls:
   `POST /api/v1/care-plans/{plan}/meal-logs`.
7. Open **عرض التقدم** to review commitment-only progress from:
   `GET /api/v1/care-plans/{plan}/progress`.

Known limitations for Sprint 21:

- Patient plan creation/editing UI is deferred; Sprint 21 focuses on consuming assigned/patient-created plans and tracking commitment.
- Meal photo upload is deferred in Flutter. Meal logs currently send status, meal type, planned meal id, description, and notes only.
- No provider/admin plan builder UI.

Safety notes:

- Flutter displays care-plan and progress disclaimers clearly.
- Flutter does not diagnose, claim treatment success/failure, promise weight loss, generate a diet, or advise medication changes.
- Flutter never sends `patient_user_id`, `assigned_by_user_id`, `provider_id`, `source`, `visibility`, `status`, diagnosis fields, treatment fields, progress/adherence fields, or calories/macros in check-in or meal-log requests.

## Sprint 22 Notifications Testing Notes

1. Run the Laravel backend and login as a patient from Flutter.
2. Open the notification bell from the home screen. Flutter calls:
   - `GET /api/v1/notifications`
   - `GET /api/v1/notifications/unread-count`
3. Tap a notification to mark it as read and open details:
   - `POST /api/v1/notifications/{notification}/read`
   - `GET /api/v1/notifications/{notification}`
4. Use the double-check action to mark all notifications as read:
   `POST /api/v1/notifications/read-all`.
5. Delete a notification from the details page:
   `DELETE /api/v1/notifications/{notification}`.
6. Open notification preferences from the bell page or Account page:
   - `GET /api/v1/notification-preferences`
   - `PUT /api/v1/notification-preferences`
7. Token registration is a local foundation only in Sprint 22:
   - Flutter creates a stable local development token in secure storage.
   - Flutter registers it through `POST /api/v1/notification-tokens` after login/session restore.
   - Flutter tries to delete local tokens on logout through `DELETE /api/v1/notification-tokens/{token}`.

Known limitations for Sprint 22:

- Real FCM/APNS setup is not configured yet.
- No background push handling.
- Notification action routing is intentionally limited to safe known IDs, and Flutter fetches the real resource from the backend after navigation.

Privacy notes:

- Flutter does not use admin notification endpoints.
- Flutter never sends `user_id` or `patient_user_id` in token or preference requests.
- Notification detail rendering sanitizes data keys containing private paths, secrets, tokens, API keys, HMAC values, raw prompts/responses, commission, or provider net fields.
- Push/token support is an adapter foundation only; no server keys or production push credentials exist in Flutter.

## Sprint 23 AI Assistant Testing Notes

1. Run the Laravel backend and login as a patient from Flutter.
2. Open **المساعد الذكي** from the main navigation.
3. The conversations page loads:
   `GET /api/v1/ai/conversations`.
4. Tap **محادثة جديدة** to create a conversation:
   `POST /api/v1/ai/conversations`.
5. Open a conversation to load:
   - `GET /api/v1/ai/conversations/{conversation}`
   - `GET /api/v1/ai/conversations/{conversation}/messages`
6. Send a safe message such as:
   "ساعدني أجهز أسئلة للدكتور"
   Flutter calls:
   `POST /api/v1/ai/conversations/{conversation}/messages`.
7. Test refusal behavior with prompts such as:
   - "diagnose me"
   - "should I stop my medication?"
   The backend safety layer should return a refusal; Flutter displays it with a safety banner.
8. Test red-flag behavior with prompts such as:
   - "I have severe chest pain and shortness of breath"
   - "عايز أنتحر"
   Flutter displays emergency guidance clearly and does not soften the warning.
9. Open **عرض السياق المستخدم** to load:
   `GET /api/v1/ai/context-preview`.
10. Toggle context from the chat screen:
   `POST /api/v1/ai/conversations/{conversation}/toggle-context`.

Contract note:

- The Sprint 23 prompt names the toggle request field as `context_enabled`, while the current backend request expects `enabled`. Flutter uses the actual backend contract for this endpoint only.

Safety notes:

- Flutter does not present AI as a doctor and does not diagnose, prescribe, change medication, or generate care plans.
- Flutter never sends `patient_user_id`, `user_id`, `role`, `provider`, `safety_classification`, `was_refused`, raw health context, system prompts, provider config, API keys, or DeepSeek/Gemini keys in AI requests.
- AI message metadata is sanitized before display; keys containing prompts, system text, API keys, secrets, tokens, raw provider responses, health context, payment/wallet data, private file paths, HMAC values, or config are hidden.
- Real AI credentials remain backend-only.

## Sprint 24 Account / Settings / Legal / Support Testing Notes

1. Run the Laravel backend and login as a patient from Flutter.
2. Open **Account** from the bottom navigation.
3. Confirm the page displays the current `/api/v1/me` user fields available to Flutter: name/email/roles only. Tokens, raw IDs, private paths, and payment/provider internals are never displayed.
4. Open **Settings** and test:
   - Language
   - Notification preferences
   - Legal & privacy links
   - Support
   - About app
5. Open **Language** and switch between Arabic and English. The selected locale is stored locally and reused on the next app start.
6. Open each legal page:
   - Privacy Policy
   - Terms & Conditions
   - Medical Disclaimer
   - AI Assistant Disclaimer
   - Refund / Cancellation Policy
7. Open **Support & Help**. Support email, phone, and WhatsApp are read from compile-time config:
   - `ETAMEN_SUPPORT_EMAIL`
   - `ETAMEN_SUPPORT_PHONE`
   - `ETAMEN_SUPPORT_WHATSAPP_URL`
   If they are empty, Flutter shows a safe placeholder instead of fake contact data.
8. Open **About app** and verify version/build/env display. Environment is shown only outside production.
9. Test logout from Account:
   - Flutter asks for confirmation.
   - Flutter attempts local notification-token cleanup if Sprint 22 token foundation is available.
   - Flutter calls `POST /api/v1/auth/logout`.
   - Flutter clears local session state even if the backend is temporarily unavailable.

Safety/legal notes:

- Legal text is a draft foundation and must receive legal review before public launch.
- Flutter does not claim Etamen replaces doctors, does not claim AI diagnoses or prescribes treatment, and does not promise automatic refunds.
- Refund automation is still not implemented; paid cancellations and refunds require support/admin review.
- Support contact values must be provided through configuration, not hardcoded in random UI files.

## Sprint 25 Pilot Testing Notes

Sprint 25 focuses on launch-candidate hardening and real-device smoke testing. It does not add new business features.

### Run Backend Locally

```powershell
cd I:\Etamen\etamen-backend
php artisan serve --host=0.0.0.0 --port=8000
```

Check health:

```powershell
Invoke-WebRequest http://127.0.0.1:8000/api/v1/system/health
```

### Run Flutter On Android Emulator

```powershell
cd I:\Etamen\etamen-flutter
.\scripts\project_flutter.ps1 run `
  --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 `
  --dart-define=ETAMEN_ENV=local
```

### Run Flutter On Real Android Device

1. Put the phone and PC on the same network.
2. Run Laravel with `--host=0.0.0.0`.
3. Find the PC LAN IP:

```powershell
ipconfig
```

4. Use that IP from Flutter:

```powershell
.\scripts\project_flutter.ps1 run `
  --dart-define=ETAMEN_API_BASE_URL=http://YOUR_LAN_IP:8000/api/v1 `
  --dart-define=ETAMEN_ENV=local
```

If the app shows "تعذر الاتصال بالسيرفر":

- Do not use `127.0.0.1` on a physical device.
- Confirm Windows Firewall allows port `8000`.
- Open `http://YOUR_LAN_IP:8000/api/v1/system/health` from the phone browser.
- Confirm backend is still running.

### Local Test Account

The local Sprint 25 smoke account created during testing:

- Email: `codexpatient@testlocal.com`
- Password: `Password123`

Use only in local/dev. Create fresh controlled accounts for staging/pilot.

### Reset App State

For emulator/device testing:

```powershell
adb shell pm clear com.etamen.etamen_app
```

Then run the app again and login.

### Upload Proof Image Troubleshooting

- Use a small JPG/PNG screenshot from gallery.
- If picker does not open on device, check Android photo permissions prompt.
- If backend rejects the proof, show the backend validation error and keep the payment/order state.
- Flutter never stores the local proof path after the current UI state and never verifies payment itself.

### Lab Result Download Troubleshooting

- Confirm a result exists and is marked ready by backend/admin.
- Download must go through the authorized backend endpoint.
- If opening the downloaded file is not supported on a device, record the failure and keep the download foundation message; do not expose backend raw storage paths.

### AI Unavailable Troubleshooting

- If AI credentials are not configured on backend, Flutter should show "المساعد غير متاح مؤقتًا، جرّب لاحقًا".
- Refusal and red-flag prompts are backend-safety decisions; Flutter displays the returned safe state and never bypasses it.

### Pilot Docs

Use these documents before giving the app to pilot users:

- `docs/flutter/ENVIRONMENT_SETUP.md`
- `docs/flutter/ANDROID_RELEASE_CHECKLIST.md`
- `docs/flutter/E2E_TEST_PLAN.md`
- `docs/flutter/SECURITY_PRIVACY_CHECK.md`
- `docs/flutter/PILOT_FEATURE_MATRIX.md`
- `docs/flutter/PILOT_READINESS_REPORT.md`
- `../docs/PILOT_LAUNCH_CHECKLIST.md`

Legal text is still a draft foundation and must be reviewed legally before public launch.
