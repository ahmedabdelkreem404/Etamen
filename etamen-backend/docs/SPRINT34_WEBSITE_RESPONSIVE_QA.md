# Sprint 34 Website Responsive QA

Date: 2026-05-08

## Environment

- Local Laravel URL: `http://127.0.0.1:8000/`
- Arabic default: `/`
- English version: `/?lang=en`
- Screenshot tool: Chrome headless
- Screenshot folder: `I:/Etamen/.tmp/sprint34-final-polish-screenshots/`

## Viewport Checks

| Viewport | URL | Screenshot | Result | Notes |
| --- | --- | --- | --- | --- |
| 390 mobile | `/` | `12-website-ar-mobile.png` | PASS | Arabic default, RTL, teal CTA; mobile clipping fixed. |
| 1440 desktop | `/` | `13-website-ar-desktop.png` | PASS | Old Doctor Finder-inspired first viewport, teal/cyan, no orange. |
| 390 mobile | `/?lang=en` | `14-website-en-mobile.png` | PASS | English LTR copy and language switch visible. |
| 1440 desktop | `/?lang=en` | `15-website-en-desktop.png` | PASS | English desktop view renders. |

## Fixes Applied

- Removed orange/yellow CTA variable and replaced with teal/dark-teal.
- Added Arabic default `lang="ar"` and `dir="rtl"`.
- Added English support via `?lang=en` with `lang="en"` and `dir="ltr"`.
- Added language switch.
- Fixed mobile horizontal clipping by constraining mobile hero/cards/search widths.
- Fixed search pill button stretching on mobile by overriding flex behavior.

## Remaining Public Launch Gaps

- Landing page is not a full public website.
- No CMS, SEO content, blog, full provider marketing funnel, or production legal copy.
- Current hero image is safe for local visual reference, but public launch still needs approved/licensed final assets.
- No web auth/payment business logic was added in Sprint 34.

