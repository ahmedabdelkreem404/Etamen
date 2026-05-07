# Typography Parity Report

Date: 2026-05-07

## Current Font Approach

The Flutter app currently uses the existing app/theme typography stack and platform-safe fonts. Sprint 31/32 adjusted size, weight, spacing, and hierarchy rather than adding a new font file.

## Old Font Status

The old Doctor Finder UI appears close to a Poppins-style template for English headings, while Arabic depends on platform rendering. No licensed old font asset was found in the new Flutter repo.

## Sprint 32 Decision

No random font files were added. This avoids licensing risk and avoids runtime font loading issues.

What changed instead:

- Home hero title was reduced after real-device screenshot review.
- Doctor cards/profile use stronger old-style heading weights.
- Rating/CTA/slot labels keep tighter visual hierarchy.
- Arabic text remains readable and RTL-safe.

## Remaining Gap

Exact old typography parity requires the product owner to provide or approve a licensed bundled font asset. Until then, typography parity should be considered close but not exact.
