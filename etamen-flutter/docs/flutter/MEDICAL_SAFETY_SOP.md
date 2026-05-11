# Medical Safety SOP

This SOP applies to Flutter patient, provider, and admin screens.

## Rules

- Do not show AI or support copy that diagnoses.
- Do not prescribe medicine.
- Do not tell users to stop medication.
- Do not replace doctor or emergency services.
- Do not interpret lab or radiology result metadata medically.
- Do not present nutrition or gym advice as medical treatment.

## Emergency Copy

Arabic:

```text
لو الحالة طارئة أو فيها خطر فوري، اتصل بالإسعاف أو توجه لأقرب طوارئ فورًا. اطمن لا يغني عن الطبيب أو خدمات الطوارئ.
```

English:

```text
If this is an emergency or immediate risk, call emergency services or go to the nearest emergency department now. Etamen does not replace a doctor or emergency services.
```

## UI Guidance

- Result metadata can show title, type, uploaded date, and safe notes.
- Payment/support/refund/dispute screens must stay operational, not clinical.
- Support replies must not expose internal notes.
- Red-flag content should guide the user to urgent medical care.
