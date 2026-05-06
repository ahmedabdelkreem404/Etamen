# Sprint 26 Current UX Audit

## HomePage

Before Sprint 26, the HomePage acted as an overloaded bottom-tab shell with nine equal destinations:

- Doctors
- Appointments
- Pharmacy
- Labs
- Health
- Medications
- Care Plans
- AI
- Account

This made the patient journey feel like a feature checklist rather than a guided healthcare app.

Sprint 26 changed this to five main destinations:

- Home
- Appointments
- Services
- Health
- Account

Notifications moved to a top app bar badge. AI is reachable from Home and Health instead of competing as a primary tab.

## Bottom Navigation

Problem found:

- Too many destinations for a mobile bottom nav.
- Similar features were separated too early.
- Account route was handled as a special-case navigation jump.

Change made:

- Replaced overcrowded navigation with a 5-tab patient shell.
- Grouped Doctors/Pharmacy/Labs under Services.
- Grouped Vitals/Medications/Care Plans/AI under Health.
- Kept all existing routes and deep links.

## Doctors List

Problems found:

- The list was functional but visually plain.
- Search/filter foundation was not visible.
- Cards lacked a strong booking CTA and scan hierarchy.

Changes made:

- Added prominent search field.
- Added local specialty chips when data contains specialties.
- Redesigned doctor cards with avatar block, status pill, fee/location/experience chips, and "احجز الآن / Book now" CTA.

## Doctor Profile

Problems found:

- Header looked like a basic card.
- Fee/specialty/branch were present but not visually prioritized.
- Slots and booking CTA felt utilitarian.

Changes made:

- Added green doctor hero section.
- Grouped fee/experience/branch chips.
- Wrapped slots in a softer section.
- Added clearer booking CTA and backend-source-of-truth note.

## Appointment Booking

Problems found:

- Booking page showed the correct fields but lacked step clarity.
- Payment reference display used technical wording.

Changes made:

- Added simple step indicator: Slot, Details, Payment, Confirm.
- Grouped slot selection and detail confirmation into cards.
- Reworded payment reference display.
- Kept request DTO unchanged and safe.

## Payment Flow

Problems found:

- Payment page was contract-correct but slightly technical.

Changes made:

- Added trust-oriented banner explaining manual review.
- Strengthened payment summary hierarchy.
- No payment business logic changed.

## Pharmacy / Labs Entry Points

Problems found:

- Pharmacy and Labs were separate bottom tabs, which increased navigation load.

Changes made:

- Added Services tab with clear cards for doctor booking, pharmacy ordering, and lab orders.
- Existing pharmacy/labs pages remain accessible through routes.

## Health / Medications / Care Plans / AI

Problems found:

- Health, Medications, Care Plans, and AI were separate tabs even though patients think of them as personal follow-up.

Changes made:

- Added Health tab that groups vitals, medications, care plans, and safe AI assistant access.
- Kept safety wording visible in the destination feature screens.

## Notifications

Problem found:

- Notifications were floating on Home but not clearly part of the app shell.

Change made:

- Notification badge is now in the MainShell top bar.

## Account

Problem found:

- Account could only be used as a standalone route.

Change made:

- AccountPage now supports `showAppBar: false` for shell embedding, without breaking the existing `/account` route.

## Remaining UX Gaps

- Real iconography/assets should replace generic Material icons before public launch.
- Some feature detail pages still use simple list/card patterns.
- Arabic copy still needs product-owner review across all modules.
- Doctor images are not displayed yet because the current Doctor entity does not expose an image URL.
- Home overview does not fetch aggregated next appointment/pending payment/latest vital to avoid new backend work in Sprint 26.
