# AI Assistant Contract

The AI assistant is a safe organizational and educational helper. It is not a doctor.

Required safety line:

"أنا لست طبيبًا ولا أستطيع التشخيص أو وصف علاج. أقدر أساعدك في تنظيم المعلومات وفهمها بشكل عام، لكن القرار الطبي لازم يكون مع طبيب مختص."

## Endpoints

- `GET /api/v1/ai/conversations`
- `POST /api/v1/ai/conversations`
- `GET /api/v1/ai/conversations/{conversation}`
- `PUT /api/v1/ai/conversations/{conversation}`
- `DELETE /api/v1/ai/conversations/{conversation}`
- `GET /api/v1/ai/conversations/{conversation}/messages`
- `POST /api/v1/ai/conversations/{conversation}/messages`
- `POST /api/v1/ai/ask`
- `GET /api/v1/ai/context-preview`
- `POST /api/v1/ai/conversations/{conversation}/toggle-context`

## Safe Message Request

```json
{
  "content": "ساعدني أجهز أسئلة للدكتور",
  "language": "ar"
}
```

Flutter must not send `role`, `provider`, `patient_user_id`, `safety_classification`, or `was_refused`.

## Unsafe Prompt Behavior

Diagnosis, treatment, antibiotic, medication stop/change, and emergency red-flag requests are refused locally before provider calls.

Example diagnosis response shape:

```json
{
  "success": true,
  "message": "Assistant response.",
  "data": {
    "role": "assistant",
    "was_refused": true,
    "safety_classification": "diagnosis_request",
    "content": "أنا لست طبيبًا ولا أستطيع التشخيص..."
  }
}
```

Emergency guidance must include:

"لو عندك أعراض خطيرة مثل ألم صدر شديد، ضيق تنفس، فقدان وعي، نزيف شديد، ضعف مفاجئ في جانب من الجسم، أو أفكار لإيذاء النفس، تواصل مع الطوارئ فورًا."

## Context Preview

Context includes only safe summaries from the current patient:

- age/gender if available
- latest vitals summary
- chronic diseases names
- allergy names
- current medication names only
- medication adherence high-level
- care plan titles/progress summary
- recent appointment summary
- lab result metadata only

It excludes payments, wallet data, raw files, lab PDFs, provider private notes, and all other users' data.

## Provider Unavailable

If configured AI provider credentials are missing or the provider fails, backend returns a safe temporary message. Flutter should show a retry option later, not a stack trace.

## Rate Limit

Excess messages return `429` standard error envelope. Show a gentle "try again later" message.

## UI Rules

- Do not label the assistant as a doctor.
- Always show safety copy near medical AI features.
- Do not present AI output as diagnosis or treatment.
