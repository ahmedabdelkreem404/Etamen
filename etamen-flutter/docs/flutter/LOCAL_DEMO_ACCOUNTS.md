# Local Demo Accounts

These fake accounts are for internal local demo and QA only. They must not be used as production credentials or with real customer data.

Common demo password:

```text
Password1234
```

## Accounts

| Email | Role | Workspace | Demo Use |
| --- | --- | --- | --- |
| `pilot.admin@example.test` | Platform admin | Platform Admin | Admin operations center. |
| `pilot.patient@example.test` | Patient | Patient | Patient super-app flows. |
| `pilot.doctor@example.test` | Provider owner | Doctor | Provider doctor operations. |
| `pilot.hospital@example.test` | Provider owner | Hospital | Hospital operations. |
| `pilot.radiology@example.test` | Provider owner | Radiology | Radiology operations. |
| `pilot.pharmacy@example.test` | Provider owner | Pharmacy | Pharmacy MVP pages. |
| `pilot.lab@example.test` | Provider owner | Lab | Lab MVP pages. |
| `pilot.gym@example.test` | Provider owner | Gym | Gym operations. |
| `pilot.fitness.coach@example.test` | Provider owner | Fitness coach | Coach operations. |
| `pilot.nutrition.coach@example.test` | Provider owner | Nutrition coach | Coach operations with non-medical guardrails. |
| `pilot.provider.staff@example.test` | Limited staff | Doctor provider | Limited staff guard. |

## Local QA Shortcuts

The login page shows shortcut buttons only with `ETAMEN_ENV=local`:

| Button | Email | Role |
| --- | --- | --- |
| Admin QA | `a@b.co` | Platform admin |
| Patient QA | `p@b.co` | Patient |
| Provider QA | `d@b.co` | Doctor provider owner |

These buttons use the normal login flow. They are hidden in staging, production, and missing/unsafe environment fallback.
