# Website Landing Parity Report

Date: 2026-05-07

## Old Website Reference

Primary reference:

- `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website-wait.png`

The old website had a dark contact strip, white navigation, orange `Join As Doctor` CTA, peach/teal split hero, large `Find A Doctor!` headline, rounded search input, and a real medical hero image.

## New Landing Page

Implemented in:

- `I:/Etamen/etamen-backend/resources/views/welcome.blade.php`
- `I:/Etamen/etamen-backend/public/legacy-doctorfinder/doctor-finder-hero.jpg`

Captured screenshot:

- `I:/Etamen/.tmp/sprint30-new-screenshots/13-website-landing.png`

## What Matches

- Dark top strip and white navigation.
- Orange CTA button.
- Split peach/medical-visual hero structure.
- Large search-first doctor headline.
- Rounded white search box with orange action circle.
- Medical hero image from the inspected old website asset.
- Service cards below the hero.
- Arabic-first public copy while retaining the Doctor Finder visual mood.

## What Does Not Match Exactly

- It is a lightweight Laravel Blade landing, not the full old public website.
- It has no marketing CMS, doctor registration workflow, public doctor search backend, SEO content system, cookie banner, or public auth flow.
- Copy/content is adapted for Etamen pilot and not an exact clone of the old English marketing copy.
- The old site's full section sequence is not fully rebuilt.

## Asset Note

The reused visual asset is `banner-bg-1.jpg` from the inspected old website public assets, copied to `public/legacy-doctorfinder/doctor-finder-hero.jpg`. It is used as a decorative website hero reference only. No screenshots, private user photos, secrets, API URLs, old auth code, old payment code, or old backend controllers were copied.

## Parity Decision

- Before Sprint 30: 0% for Etamen public landing parity because no matching public marketing landing existed.
- After Sprint 30: about 84% visual parity for the first public landing viewport.
- Pilot blocker: no, if the pilot is app-first and supervised.
- Public launch blocker: yes, because content, SEO, legal pages, real CTA targets, and final licensed marketing assets still need review.

## Sprint 31 Website Clone Pass

Sprint 31 tightened the landing first viewport further toward the old website screenshot.

Updated file:

- `I:/Etamen/etamen-backend/resources/views/welcome.blade.php`

New captured screenshot:

- `I:/Etamen/.tmp/sprint31-new-screenshots/09-website-landing.png`

### What Now Matches Better

- Old-style dark top strip remains.
- White navigation now uses old labels: `Home`, `About Us`, `Specialist`, `Doctors`.
- Orange CTA now reads closer to old: `Join As Doctor +`.
- Hero headline now uses the old visual phrase `Find A Doctor!`.
- Search pill and orange circular action are placed in the first viewport.
- Peach/teal split and medical image dominate the first viewport like the old screenshot.
- Service/category cards sit directly below the hero.

### Remaining Gap

- It is still a lightweight public landing, not the full old website.
- Public doctor search, doctor join workflow, CMS content, SEO pages, analytics/cookie/legal public launch details, and final licensed marketing assets are not complete.
- The landing is Arabic-first around the old English visual labels, so it is not a literal text clone.

### Parity

- Before Sprint 31: **84%** first-viewport website parity.
- After Sprint 31: **90%** first-viewport website parity.
- Public launch readiness: **not ready** without content/legal/SEO/search workflow review.
- Supervised pilot readiness: acceptable as a visual landing if the pilot remains controlled and app-first.
