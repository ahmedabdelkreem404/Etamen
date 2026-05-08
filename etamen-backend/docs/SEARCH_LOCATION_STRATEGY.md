# Search And Location Strategy

Date: 2026-05-08  
Sprint: 35 - Egypt-Scale Health Super App Architecture + Product Expansion Blueprint

## Goal

Design how Etamen users eventually find:
- doctors by specialty and subspecialty.
- nearest pharmacies.
- labs by test type/package.
- radiology centers by scan type.
- gyms nearby.
- coaches by specialty.
- available providers.
- providers by price, rating, area/city, home service, and insurance later.

This strategy does not claim current search is Egypt-scale. It defines the path.

## Current Search Limitations

Current backend foundations are useful but limited:
- Providers currently support only doctor, pharmacy, and lab types.
- Branches have city/area and latitude/longitude fields.
- Doctor specialties exist, but no full subspecialty taxonomy exists yet.
- No unified marketplace search index exists.
- No typo-tolerant Arabic search engine exists.
- No synonym support exists.
- No geo-radius search service exists.
- No unified ranking model exists.
- Availability and rating summaries must not be computed expensively for every result row.
- Search load has not been benchmarked.

## First Implementation Recommendation

For production MVP, keep search simple and safe:
- Database-backed filters.
- Bounded pagination.
- Indexed provider type/status/city/area/specialty filters.
- Branch city/area filters before geo-radius complexity.
- Optional bounding-box filtering using latitude/longitude for nearby providers.
- Public summary fields only.
- No private file paths or documents in results.
- No expensive availability computation per row.
- Denormalized summary fields only where maintained safely.

Suggested filters:
- provider type.
- specialty/subspecialty.
- city.
- area.
- branch.
- availability date.
- fee/price range.
- rating range when real rating data exists.
- home service flag for labs/radiology/home healthcare later.
- insurance accepted later.

## Later Search Engine Recommendation

At Egypt-wide scale, move marketplace discovery to a search engine:
- Meilisearch if the priority is fast implementation, typo tolerance, and simple Arabic/English search.
- OpenSearch/Elasticsearch if the priority is advanced geo queries, analytics, large-scale operations, and richer ranking.

Recommended search indexes:
- `providers`
- `doctors`
- `pharmacy_products`
- `lab_tests`
- `radiology_scans`
- `gyms`
- `coaches`

The API should remain the gateway. Flutter should not query the search engine directly.

## Location Fields

Existing:
- `provider_branches.city_id`
- `provider_branches.area_id`
- `provider_branches.latitude`
- `provider_branches.longitude`

Future:
- `districts`
- `geo_zones`
- `provider_locations`
- geohash or generated spatial fields if the chosen database/search engine benefits from them.
- delivery zones for pharmacies and home sample collection.
- service radius for labs, radiology, home care, coaches, and gyms.

## Geo-Radius Search Approach

Stage 1:
- Ask user for city/area.
- Use branch city/area filters.
- Show manual location fallback.

Stage 2:
- Store branch latitude/longitude.
- Use bounding box prefilter.
- Sort by approximate distance if database supports it cheaply.
- Keep pagination stable.

Stage 3:
- Use search engine geo point fields.
- Apply radius filters and distance sorting.
- Cache common city/area queries.

Fallback if location permission is denied:
- Ask for city/area manually.
- Use saved default area from account if available.
- Show popular areas.
- Never block booking solely because location is denied.

## Pagination Strategy

Required:
- Cursor or page-based pagination with strict max page size.
- No unbounded lists.
- Stable sorting.
- Avoid deep-offset pagination at scale.

For search engine:
- Use search-after/cursor when result sets become large.
- Hydrate only IDs returned for the current page.

## Caching Strategy

Good cache candidates:
- cities and areas.
- specialties and categories.
- service categories.
- common city/specialty result pages with short TTL.
- public provider summary cards.

Do not cache blindly:
- appointment slots.
- payment state.
- private files.
- patient-specific health data.

Cache invalidation:
- provider updates enqueue search reindex job.
- branch active/status changes invalidate provider search summaries.
- rating summary updates after approved review changes.
- availability summaries update asynchronously if adopted.

## Index Freshness

Recommended:
- Queue-driven indexing.
- Retry failed indexing jobs.
- Admin tool to reindex a provider.
- Nightly consistency job comparing DB and search index counts.
- Search results hide inactive/unapproved providers even if index is stale.

## Arabic And English Search Support

Arabic requirements:
- Normalize Alef variants.
- Normalize Ta Marbuta/Ha where appropriate.
- Normalize Arabic/Western digits.
- Ignore common diacritics.
- Support synonyms.
- Support common colloquial phrases.

English requirements:
- English specialty/product/test names.
- Common medical abbreviations.
- Transliteration aliases for popular searches.

## Synonym Examples

Doctors:
- `دكتور قلب` -> `قلب وأوعية دموية`
- `باطنة` -> `طب باطني`
- `عظام` -> `جراحة عظام`
- `جلدية` -> `أمراض جلدية`

Radiology:
- `أشعة` -> `Radiology`
- `رنين` -> `MRI`
- `مقطعية` -> `CT`
- `سونار` -> `Ultrasound`

Labs:
- `تحليل دم` -> `CBC`
- `صورة دم` -> `CBC`
- `سكر` -> `Glucose`
- `كوليسترول` -> `Lipid profile`

Fitness/Nutrition:
- `تخسيس` -> `weight loss`
- `تغذية` -> `nutrition`
- `زيادة عضل` -> `muscle gain`
- `تأهيل` -> `rehab fitness`

## Ranking Strategy

MVP ranking:
- active/approved providers first.
- exact specialty/category match.
- same city/area.
- available soon if safe summary exists.
- rating only when real approved reviews exist.

Later ranking:
- distance.
- availability.
- conversion rate.
- rating count and average.
- cancellation rate.
- provider responsiveness.
- sponsored placement only if clearly disclosed.

Never rank by medical diagnosis inference from AI chat.

## Privacy And Safety

Search results may expose:
- public provider name.
- public branch city/area.
- public avatar/logo.
- public fee/price summary.
- public rating summary from approved reviews.

Search results must not expose:
- private provider documents.
- payment proofs.
- patient files.
- patient names.
- hidden/rejected reviews.
- private storage paths.

## Rollout Plan

Phase A:
- Keep current doctor/pharmacy/lab filters.
- Add missing indexes only when query plans show need.
- Keep first pilot scope tight.

Phase B:
- Add unified discovery API contract.
- Add city/area/manual location UX.
- Add safe public provider summaries.

Phase C:
- Add search engine.
- Add indexing jobs.
- Add synonyms and Arabic normalization.

Phase D:
- Add geo-radius, ranking experiments, and search analytics.

