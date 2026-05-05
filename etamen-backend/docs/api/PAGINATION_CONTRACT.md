# Pagination Contract

Etamen uses bounded list responses across mobile-facing APIs. Endpoints that can grow over time accept `page` and `per_page` where supported.

## Query Parameters

| Parameter | Default | Max | Notes |
| --- | ---: | ---: | --- |
| `page` | `1` | endpoint-defined | One-based page number. |
| `per_page` | `15` or `20` | `100` | Values above `100` are clamped by backend helpers. |

## Current Response Shapes

Most current mobile endpoints return a standard response with `data` as a bounded array:

```json
{
  "success": true,
  "message": "Doctors.",
  "data": [
    { "id": 1, "name_en": "Doctor Name" }
  ]
}
```

Some endpoints use Laravel resource collections over paginated queries. They still sit under the standard envelope and may include framework pagination information if the resource collection serializes it:

```json
{
  "success": true,
  "message": "Notifications.",
  "data": [
    { "id": 100, "title": "..." }
  ]
}
```

Flutter should implement infinite scroll by:

1. Sending `page` and `per_page`.
2. Treating `data.length < per_page` as `has_more = false` when explicit links/meta are not present.
3. Respecting endpoint-specific date range caps for trends, AI, health, scheduler, and report-like endpoints.

## Important Bounds

- `per_page` must never exceed `100`.
- Date-range endpoints default to safe ranges and cap large ranges, for example health trends and public doctor slots.
- Public product/catalog endpoints are bounded to avoid unbounded mobile payloads.
