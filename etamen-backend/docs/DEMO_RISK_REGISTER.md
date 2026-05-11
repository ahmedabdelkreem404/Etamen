# Demo Risk Register

This register is for local investor/client demo rehearsal only. It does not approve staging or production.

| Risk | Impact | Mitigation | What to do during demo |
| --- | --- | --- | --- |
| Local server down | App cannot load API data | Start backend before demo and check health | Pause, restart local server, switch to screenshots |
| Emulator API issue | App shows connection error | Build with `http://10.0.2.2:8000/api/v1` and verify health | Use screenshot pack, avoid debugging live |
| Proof upload picker issue | Payment proof demo stalls | Prepare proof image and test picker before demo | Show pre-captured proof screenshot |
| Seed data missing | Lists are empty | Run seed commands before demo | Stop and reseed if technical audience; otherwise use screenshots |
| Login/session stale | Wrong account or workspace appears | Clear app data or logout before demo | Use QA local buttons only in local build |
| Production explanation misunderstood | Audience thinks app is launched | Say local-only at opening and closing | Repeat no staging/no production/no external users |
| Payment misunderstanding | Audience thinks money moves live | Explain manual proof only | Say no live Paymob and no live refund gateway |
| Medical safety misunderstanding | Audience thinks AI/support diagnoses | Use safety note explicitly | Stop any diagnostic claim and correct it |
| Screenshots fallback unavailable | Demo depends on live app only | Keep screenshot folder ready | Open docs walkthrough if screenshots fail |
| Privacy/security risk appears | Trust risk | Never show raw API/secrets/private paths | Stop demo and reschedule if needed |

## Fallback order

1. Live local app.
2. Sprint 63 screenshot pack.
3. Local demo walkthrough docs.
4. Stop and reschedule if privacy/security risk appears.

## Stop conditions

- Any secret appears.
- Any raw private path appears.
- Any stack trace/debug page appears.
- A medical diagnosis claim is accidentally implied.
- Audience asks to invite real users before staging.
