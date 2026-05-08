# Sprint 34 Color Audit

Date: 2026-05-08

## Goal

Remove yellow/orange as a brand accent and keep the visual system medical, teal/cyan, clean, and Arabic-first.

## Flutter Usages Found

| Area | Previous usage | Sprint 34 replacement | Notes |
| --- | --- | --- | --- |
| Theme tokens | `AppColors.appointmentOrange` | Removed; added `medicalAccent`, `medicalAccentDark`, `medicalAccentSoft` | Brand accent is no longer orange. |
| Page background | Existing soft background | `#F6FFFD` | Lighter medical background. |
| Bottom nav selected state | Teal/orange-influenced active treatment | `medicalAccentDark` / teal surface | Keeps max 5 tabs. |
| Booking selected slot/day | Orange appointment token | `medicalAccent` and teal selected state | No DTO/API change. |
| Doctor card appointment/rating accents | Orange/yellow star/accent | Teal stars and teal appointment icon | Rating is visual teal, not fake yellow stars. |
| Doctor profile rating stars | Yellow/orange-like star treatment | `medicalAccent` | Existing real/fallback rating behavior unchanged. |
| Home speciality/action accents | Warm accent in cards | Teal/cyan/blue-teal variants | No yellow brand accent. |
| Payment manual methods | Vodafone/InstaPay warm accents | `medicalAccentDark` and blue-teal | Consumer-grade but medical. |
| Paymob checkout info surface | Orange-tinted soft surface | `medicalAccentSoft` | Informational, not warning. |
| Payment polling banner | Amber/orange info tone | `medicalAccentSoft` | Avoids raw/backend feel. |

## Laravel Website Usages Found

| Area | Previous usage | Sprint 34 replacement | Notes |
| --- | --- | --- | --- |
| CSS variable | `--orange` | `--accent`, `--accent-dark` | Orange token removed. |
| Website CTA | Orange CTA/search/steps | Teal/dark-teal CTA/search/steps | Matches app identity. |
| Service card accents | Warm numbered accents | Mint/teal numbered accents | No yellow/orange brand accent. |
| Hero first viewport | Peach/orange-inspired accent | Teal/cyan medical surface | Old layout retained, accent changed. |

## Remaining Yellow/Orange

No yellow/orange brand accent remains in the Sprint 34 app or landing source.

Allowed remaining matches:

- `#FFFFFF` / `#fff` white card/background values.
- Test text that asserts the old orange token is no longer used.
- Real warning/error states may still use warning colors in future, but Sprint 34 did not use amber/yellow as branding.

