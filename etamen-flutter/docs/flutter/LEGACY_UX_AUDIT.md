# Sprint 26 Legacy UX Audit

## 1. Old App Found

The old Doctor Finder Flutter app was found at:

`I:/Etamen/doctorfinder_timeslot-main`

It was inspected as visual and flow reference only. No networking, payment, auth, notification, chat, video, or old API code was migrated.

## 2. Screens Found

Important patient-facing screens found:

- `lib/views/HomeScreen.dart`
- `lib/views/HomeScreenNearby.dart`
- `lib/views/DetailsPage.dart`
- `lib/views/MakeAppointment.dart`
- `lib/views/AllAppointments.dart`
- `lib/views/UserAppointmentDetails.dart`
- `lib/views/SpecialityScreen.dart`
- `lib/views/SpecialityDoctorsScreen.dart`
- `lib/views/ProfilePage.dart`
- `lib/views/MoreScreen.dart`

Provider/doctor and unsafe integration areas also exist:

- `lib/views/Doctor/**`
- `lib/VideoCall/**`
- `lib/PaymentGateways/**`
- `lib/notificationHelper.dart`

## 3. Old Navigation Structure

The old app uses a home-first doctor-booking experience with bottom navigation icons for home, appointments/doctor list, chat, profile, and more. It does not treat every product module as an equal bottom-tab destination.

## 4. Old Home / Dashboard Approach

Useful patterns:

- Home starts with a prominent green header.
- Search is high on the page.
- Specialty and nearby-doctor sections come before secondary actions.
- Upcoming appointments are visible from home.
- Cards are visually separated and easy to scan.

Problems:

- Home mixes API calls, FCM setup, location permission, call handling, and UI in one stateful class.
- It uses stored `userId` and old endpoint assumptions.
- It prints raw API responses and technical data.

## 5. Old Doctor Listing Style

Useful patterns:

- Doctor cards use avatar/image, name, specialty, location, rating, and clear tap target.
- Nearby doctors are shown in a grid/card style.
- Specialty chips/sections help discovery.

Avoid:

- Direct old search endpoint calls.
- Location permission as a hidden dependency for browsing.
- Cached network image logic tied to old storage URLs.

## 6. Old Doctor Profile Style

Useful patterns:

- Strong header.
- Doctor image/name/specialty grouped clearly.
- About, address, working time, services, and reviews are separated.
- Sticky/large booking CTA with fee is easy to notice.

Avoid:

- Direct phone/map launch assumptions without current backend contract.
- Old review/fee assumptions.
- Frontend trusting old consultation fee behavior.

## 7. Old Appointment Booking Flow

Useful patterns:

- Date/slot selection is visually central.
- Booking CTA stays visible near the bottom.
- Payment step is conceptually tied to booking.

Avoid:

- Old payment gateway handoff code.
- Old `userId`/`doctor_id` ownership assumptions.
- Braintree/Stripe/Razorpay/Paytm old links.
- Description required behavior if the current contract says optional.

## 8. Colors / Typography / Assets

The old app leans on:

- Green header backgrounds.
- White cards over light backgrounds.
- Rounded image/avatar containers.
- Icon-led navigation.
- Poppins/Google Fonts.

Sprint 26 reused the design ideas, not the implementation. No asset file was copied.

## 9. Unsafe Code / Assets Not Copied

Do not copy:

- ConnectyCube chat/video code.
- Firebase Messaging setup and local notification handling.
- Direct FCM/push assumptions.
- Old payment gateway files.
- Old `SERVER_ADDRESS` API calls.
- Hardcoded `userId`, `doctor_id`, or provider assumptions.
- SharedPreferences auth/session code.
- Old `http`/`dio` request code.

## 10. Replicated In New App

Implemented as safe inspiration:

- Home-first patient journey.
- Search/quick action area.
- Services grouped separately from health tracking.
- Doctor cards with avatar, specialty, location/fee chips, and "Book now" CTA.
- Doctor profile hero and clearer slot/booking structure.
- Booking step indicator.
- Softer cards and visual hierarchy.

## 11. Avoided

- No old API code migrated.
- No old FCM/Firebase production setup added.
- No old payment code migrated.
- No old video/chat code migrated.
- No old assets copied.
- No backend feature added.
