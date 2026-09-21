---
timestamp: 2026-08-28T18-38-25Z
slug: resources-views-livewire-dashboard-blade-php
---
# Critique — Dashboard

Target: `resources/views/livewire/dashboard.blade.php` (with `layouts/app.blade.php` shell)

## Evidence

- **Detector (CLI):** `impeccable detect` exit 2 — 1 true finding.
  - `design-system-font-size` — `layouts/app.blade.php:70`, `text-[15px]` on the sidebar brand span. Off-ramp: 15px is not on the DESIGN.md size ramp (0.75 / 0.875 / 1.5 / 1.875rem).
- **Browser visualization:** ⚠️ DEGRADED: no browser automation tool is exposed in this session, so the live-server + injected-rule browser pass was skipped. CLI structural scan ran instead.
- **Design review (isolated Assessment A):** full heuristic and persona pass over the dashboard + shell.

## Verdict

Partially grounded: renewal-first data is present but compositionally this reads as a generic multi-entity ops dashboard; the renewal heartbeat is one of four equal zinc cards while the retrospective revenue chart owns 2/3 of the row.

## Heuristic scores

1. Visibility of system status — 3
2. Match system ↔ real world — 3
3. User control & freedom — 2
4. Consistency & standards — 4
5. Error prevention — 3
6. Recognition rather than recall — 3
7. Flexibility & efficiency of use — 2
8. Aesthetic & minimalist design — 4
9. Help diagnose/recover from errors — 2
10. Help & documentation — 2

## Priority issues

- **P1 — Renewal is under-weighted for a renewal-first product.** "Renewals due" is an equal-weight, gray, non-clickable div (`dashboard.blade.php:32-39`) while the revenue chart spans 2/3 of the next row (`:44`). It should be the operational focal point, click-through to a tiered list, and gain amber/red emphasis when >0.
- **P1 — Attention list is a black box at scale.** Unbounded rows, no tier filter or "view all", dates not "in 3 days", and no amount-at-stake (`:97-131`). Five renewals due = five page visits to act; the card never surfaces expired-vs-urgent counts.
- **P2 — Chart is inaccessible and low-information.** Raw `<canvas>` with no `aria-label` or text fallback (`:84`), no zero/loading state, no currency/k ticks, no trend, re-animates every load.
- **P2 — Contrast failures, light mode.** `text-zinc-400` labels/captions (`:7, :12, :38`) ≈ 2.4:1 on white — fails AA; repeated on every stat block.
- **P2 — Tier color drift between components.** Dashboard maps `Expired = rose, Urgent = red, DueSoon = amber` (`:114-119`); `x-tier-badge` maps `urgent = rose, expired = zinc` (`components/tier-badge.blade.php`). DESIGN.md documents the component version. The ladder contradicts itself in two places.
- **P3 — Shell accessibility.** Toasts have no `aria-live` (`app.blade.php:157-172`); drawer has no focus trap / `aria-hidden` background / `aria-modal`; collapse toggle lacks `aria-expanded`; no skip-to-content link.

## Persona flags

- **Alex (screen reader / keyboard):** revenue chart opaque (untagged canvas, no fallback); every stat repeats a 2.4:1 violation; toast confirmations never announced; drawer not focus-contained. Positive: tiers conveyed by text, not color only.
- **Sam (power user):** no accelerators — nothing is clickable to jump to renewals due; no tier filter, countdown, amount-at-risk, or "as of" freshness; ~1s Chart.js re-animation on every load.

## Strengths

1. Disciplined token system — one indigo family, zinc chrome, tiered status ladder, flat-card elevation, radius ladder — applied consistently across shell and dashboard.
2. Calm-by-design: real hierarchy, minimal motion, full dark parity — an 8-hour day is plausible.
3. Honest microcopy and truthful states ("not cancelled", "paid renewals", "within 30 days", "All caught up") — the money/status language never overclaims.

## Minor observations

- Stat numbers aren't tabular figures — values jitter width as they change.
- Monograms are all same-zinc and collide on shared initials; the invented 'S' fallback fabricates identity.
- Copy voice split: "in your portfolio" (marketing) vs "not cancelled" (stilted).
- "Revenue — paid renewals" may misdescribe a reseller who books new sales.
- Empty-state says "expire in the next 30 days" but the list can include already-expired items.
- Nav links and stat cards rely on browser-default focus, not the focus-visible ring buttons use.

## Provocative questions

1. If the renewal list is the product's heart, why does it render second-class to a revenue chart you can't act on?
2. When the operator sees 7 renewals due, what is the exact next action you want with zero clicks? (Currently none — the card isn't a link.)
3. Should the headline be "collected this month" (safe, retrospective) or "sum of expiring renewal amounts" (today's exposure)?
4. If the operator can't answer "how many dollars expire in the next 30 days" without five page visits, is the make-or-break outcome actually served?
