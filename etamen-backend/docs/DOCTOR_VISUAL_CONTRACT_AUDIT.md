# Doctor Visual Contract Audit

Date: 2026-05-07

Sprint 32 audited the backend doctor visual contract to close remaining visual parity gaps without weakening security.

## Existing Data Found

- Doctor business profile is stored in `doctor_profiles`.
- Provider identity is stored in `providers`.
- Branch/location data is stored in `provider_branches`, with `city_id` and `area_id`.
- Specialties are stored through `doctor_specialties`.
- Reviews already exist in `appointment_reviews` with:
  - `doctor_profile_id`
  - `rating`
  - `comment`
  - `is_visible`
- Private provider documents are stored through `provider_documents` and `uploaded_files` on private disks.

## Missing Fields Before Sprint 32

- No safe public doctor avatar/image field existed.
- Public doctor API did not expose rating summary fields.
- Public doctor API required Flutter to derive primary location from branch arrays only.

## Fields Added Or Exposed

Added nullable backend field:

- `doctor_profiles.avatar_path`

Exposed public doctor API fields:

- `doctor_profile.avatar_url`
- `doctor_profile.rating_average`
- `doctor_profile.reviews_count`
- `primary_branch_name`
- `primary_area_name`
- `primary_city_name`

## Safety Decisions

- `avatar_path` is nullable and not required for booking.
- `avatar_url` is generated only from public-safe relative paths.
- Paths containing `..`, URL schemes, `medical_private`, `medical-private`, `private`, or `provider-documents` are not exposed.
- Provider documents, uploaded file paths, medical private files, and private storage URLs are not exposed.
- Rating summary uses only `appointment_reviews.is_visible = true`.
- Patient names and review bodies are not exposed in public doctor listing/details.
- Booking request now explicitly prohibits visual/rating fields, so Flutter cannot force avatar/rating/price/status.

## Migrations

Migration added:

- `app/Modules/Providers/Database/Migrations/2026_05_07_210000_add_visual_fields_to_doctor_profiles.php`

## Public API Response Changes

Example public doctor profile response now includes:

```json
{
  "doctor_profile": {
    "avatar_url": "http://host/legacy-doctorfinder/demo-doctor-avatar-1.png",
    "rating_average": 4.7,
    "reviews_count": 3
  },
  "primary_area_name": "مدينة نصر",
  "primary_city_name": "القاهرة"
}
```

## Tests Added

- Public doctor listing includes safe `avatar_url`.
- Unsafe/private avatar paths return `null`.
- Rating summary counts visible reviews only.
- Registration/profile update cannot force avatar/rating fields.
- Booking request rejects visual/rating fields.

## Remaining Backend Gaps

- Production needs an admin/provider-approved upload workflow for doctor avatars if real doctor photos are required.
- Production ratings need a product decision on whether to expose review bodies later.
- `next_available_slot_summary` was not added to the listing because it can introduce extra query cost; Flutter still opens profile/booking to choose slots.
