---
name: Domain & Hosting Panel
description: Reseller renewal-first operations panel — calm zinc neutrals with indigo primary and tiered status semantics.
colors:
  primary-50: "#f2f3fd"
  primary-100: "#e4e6fb"
  primary-300: "#aeb4f3"
  primary-400: "#858ceb"
  primary-500: "#6467e3"
  primary-600: "#4f46d8"
  primary-700: "#4339be"
  primary-800: "#38309a"
  primary-950: "#1e1a4a"
  neutral-50: "#fafafa"
  neutral-100: "#f4f4f5"
  neutral-200: "#e4e4e7"
  neutral-400: "#a1a1aa"
  neutral-500: "#71717a"
  neutral-600: "#52525b"
  neutral-700: "#3f3f46"
  neutral-800: "#27272a"
  neutral-900: "#18181b"
  neutral-950: "#09090b"
  surface: "#ffffff"
  success: "#10b981"
  success-deep: "#059669"
  danger: "#f43f5e"
  danger-deep: "#e11d48"
  urgent: "#ef4444"
  urgent-deep: "#b91c1c"
  warning: "#f59e0b"
  warning-deep: "#d97706"
  info-sky: "#0ea5e9"
  info-sky-soft: "#38bdf8"
  brand-gradient-start: "#6366e3"
  brand-gradient-end: "#4339be"
typography:
  display:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif"
    fontSize: "1.875rem"
    fontWeight: 700
    lineHeight: 1.2
  title:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.333
    letterSpacing: "-0.025em"
  body:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.428
  label:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.333
    letterSpacing: "0.05em"
    textTransform: "uppercase"
rounded:
  sm: "8px"
  md: "12px"
  lg: "16px"
  full: "9999px"
spacing:
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary-600}"
    textColor: "#ffffff"
    rounded: "{rounded.sm}"
    height: "40px"
    padding: "16px 16px"
  button-primary-hover:
    backgroundColor: "{colors.primary-500}"
  button-primary-active:
    backgroundColor: "{colors.primary-700}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.neutral-700}"
    rounded: "{rounded.sm}"
    height: "40px"
  button-danger:
    backgroundColor: "{colors.danger}"
    textColor: "#ffffff"
    rounded: "{rounded.sm}"
    height: "40px"
  button-ghost:
    textColor: "{colors.neutral-600}"
    rounded: "{rounded.sm}"
    height: "36px"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.neutral-900}"
    rounded: "{rounded.md}"
  card-dark:
    backgroundColor: "{colors.neutral-900}"
    textColor: "{colors.neutral-100}"
    rounded: "{rounded.md}"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.neutral-900}"
    rounded: "{rounded.sm}"
    height: "42px"
  input-dark:
    backgroundColor: "{colors.neutral-800}"
    textColor: "{colors.neutral-100}"
    rounded: "{rounded.sm}"
    height: "42px"
  badge:
    rounded: "{rounded.full}"
---

## Overview

A web-only operations panel (Laravel + Livewire + Tailwind CSS v4) for a solo domain & hosting reseller. The language is a calm Indigo-on-Zinc system: soft warm-cool neutrals carry the structure, a single indigo family marks brand and active state, and tiered status color pairs (success / warning / danger) carry urgency. Everything exists to be scanned during a working day — hierarchy over decoration, white surfaces over color fields, and a first-class dark mode that mirrors the light system color-for-role. The visual identity is otherwise unexpressed: no distinctive mascot, motif, or typographic voice yet, so the system's personality is restraint plus a recognizable indigo brand accent.

## Colors

**The Zinc-Neutral Rule.** Neutrals carry 90% of chrome and are drawn exclusively from the zinc scale in the light theme and shifted one step darker in dark mode. Surfaces are pure white (`#ffffff`); page background is zinc-50 (`#fafafa`); borders are zinc-200/80 (`#e4e4e7`); secondary text is zinc-500 (`#71717a`); primary text is zinc-900 (`#18181b`). In dark mode the page flips to zinc-950 (`#09090b`), cards to zinc-900 (`#18181b`), fields to zinc-800 (`#27272a`), and text to zinc-100/400. Color roles, not hues, carry meaning.

**The Indigo Brand Accent.** All brand voice lives in one primary scale (`primary-600` `#4f46d8` as the resting default). It marks active nav (primary-100 wash + primary-700 text, or primary-500/10 + primary-300 in dark), primary buttons, sort indicators, links, number avatars, and selection highlighting. The wordmark is a small gradient tile from `#6366e3` to `#4339be`. Do not sedate or brighten this scale; it is the project's only permanent hue.

**The Status Semantics.** Urgency is encoded by fixed pairs of tint + deep text: success/emerald (`#10b981` on `#059669`), warning/amber (`#f59e0b` on `#d97706`), danger/rose (`#f43f5e` on `#e11d48`), urgent/red (`#ef4444` on `#b91c1c`), and info/sky (`#0ea5e9` on `#38bdf8`). Badges use a 100-tint background with a 700-deep text in light mode and a `500/15` translucent tint with 300/400 text in dark mode. Upcoming-then-urgent progression steps sky → amber → red → rose to communicate approaching expiry. Profit figures appear in emerald-600 (`#059669`).

## Typography

**The Inter Rule.** A single family, Inter Variable (`@fontsource-variable/inter`), set as the Tailwind `--font-sans` stack, with `font-feature-settings: "cv02", "cv03", "cv04", "cv11"` and `antialiased` on the root. There is no display serif or secondary family; hierarchy comes from size, weight, and letter-spacing alone.

- **Display** — text-3xl (`1.875rem` / `1.2`), font-bold: dashboard stat numbers.
- **Title** — text-2xl (`1.5rem` / `1.333`), font-bold, `-0.025em` tracking: page headings. Modal titles use text-lg bold with the same tight tracking.
- **Body** — text-sm (`0.875rem` / `1.428`): the universal reading and control size; table cells, labels, buttons, cards.
- **Label** — text-xs (`0.75rem`), font-semibold, `0.05em` tracking, uppercase: stat-card heads, table headers, eyebrow text. Numbers inside tables are set at text-sm with default tabularization via Inter's OpenType features.

## Layout

**The Fixed-Shell Layout.** A fixed left sidebar sits at `w-64` (collapsing to `w-[4.5rem]` icon rail, persisted via localStorage), over a sticky top bar with `backdrop-blur-md` at `z-30`, and a centered `max-w-7xl` content column (`px-4 sm:px-6 lg:px-8`, `py-8`). On mobile the sidebar becomes an off-canvas drawer behind a `zinc-950/40 backdrop-blur-sm` scrim. Everything the operator navigates lives behind this shell; list pages never re-skin it.

**The Data-Grid Rhythm.** Index pages follow one template: a `page-heading` row (title + subtitle + right-aligned actions), a filter row (search input + labeled selects + export actions), then a single bordered card wrapping a horizontal-scroll table. Tables keep 16–20px cell padding (`px-5 py-3` headers, `px-5 py-4` cells), uppercase sortable headers, and zebra-free alternating hover-only row treatment.

**The Stat-Grid Rhythm.** Dashboard stats run `grid gap-4 sm:grid-cols-2 xl:grid-cols-4`; the chart and attention list run `grid gap-6 xl:grid-cols-3` with the chart spanning 2 columns. Consistent gutters (gap-4, gap-6) and the same card object keep mixed content pages legible.

## Elevation & Depth

**The Flat-Card Rule.** Depth is mostly flat. Cards are white with a hairline border (`zinc-200/80`) over zinc-50, plus a two-layer soft shadow: `--shadow-card` (`0 1px 2px rgb(24 24 27 / .04), 0 4px 16px -4px rgb(24 24 27 / .08)`). On hover, `--shadow-card-hover` (`0 2px 4px rgb(24 24 27 / .05), 0 12px 28px -8px rgb(24 24 27 / .14)`). In dark mode all colored shadows are dropped and separation comes entirely from borders (`zinc-800`) — shadows keep their light-world meaning and are not mirrored into dark mode.

**The Glow Accent.** One reserved halo, `--shadow-glow` (`0 0 0 1px rgb(99 102 241 / .08), 0 8px 32px -6px rgb(79 70 217 / .18)`), is used for high-emphasis indigo surfaces. Modals, toasts, dropdowns, and off-canvas drawers float with `zinc-950/50` scrims + `backdrop-blur-sm`.

## Shapes

**The Radius Ladder.** Rounded corners are strict and consistent: buttons/inputs/badges small (`8px`), the avatar circle medium (`12px`), the avatar circle full for user marks; cards `12px`; modals `16px`; pills `9999px`. Numbers are never square, never over-rounded — the ladder is the identity. Chart bars use a fixed `6px` radius to match button geometry.

**The Circular-Mark Rule.** Entity marks (first-letter avatars in tables, stat icons, user menu) are always perfect circles or `rounded-lg` icon tiles; inside search/tables, a `10×10` or `9×9` circle with bold initial + primary/secondary palettes. Raw avatars without images fall back to letter monograms, never borrowed silhouettes or generic heads except the logo tile.

## Components

- **Buttons** — Inline-flex, centered, `gap-2`, `rounded-lg`, `padding 16px 16px`, ~40px tall. Four variants: `primary` (indigo solid, `focus-visible:outline-2 outline-primary-600`, `disabled:opacity-50`), `secondary` (white, zinc-300 border), `danger` (rose solid), `ghost` (text only, 36px). Icons are optional and sized `h-4 w-4`. `btn-primary` with a `plus` icon is the canonical "add a record" gesture on every index page.
- **Inputs / selects** — Same family everywhere: `.input` (white, `zinc-300` border, `focus:border-primary-500 focus:ring-primary-500/30`, ~42px tall), plus `textarea`, `select`, checkboxes from `@tailwindcss/forms` (`checkbox` class), and date inputs normalized to `min-h-[2.6rem]`. `x-input-label` sits above (`text-sm font-medium`), `x-input-error` below (rose).
- **Badges** — `.badge` is `inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold`. The dynamic `x-tier-badge` variant maps ReminderTier → status pair (`expired`=rose, `urgent`=red, `due_soon`=amber, `upcoming`=sky, untracked=`none`=zinc-light), matching the urgency ladder. Badges only ever describe state, never act.
- **Tables** — `x-data-table` emits the shared sortable header (uppercase labels, dual chevron sort indicators tinted primary when active) over rows that hover `zinc-50/80` and link whole-row-sensitive cells with `hover:text-primary-600`.
- **Cards** — `.card` (`rounded-xl border zinc-200/80 bg-white shadow-card`) is used for every containerish surface: stat cards (`p-5`), panels/charts (`p-6`), forms. The dashboard "needs attention" list is a card with `p-6` and `space-y-1` row links.
- **Modal** — Glass-scrimmed centered dialog (`rounded-2xl`, `shadow-card-hover`, `sm:p-8`, `max-w` ladder `sm`→`2xl`), slides/scales from bottom on mobile, uses `x-transition.scale.origin.bottom`. Toggled with Livewire-bound `show`; locks body scroll while open. Destructive confirmations always use `btn-danger` + clear copy, phrased as a question.
- **Empty states** — `x-empty-state`: centered grey icon (`h-10 w-10 zinc-300`), `text-sm` title, `text-xs` hint, optional action slot. Also the dashboard "All caught up" state with a emerald check.
- **Skeletons** — `.skeleton` (`animate-pulse`, `rounded-lg`, `zinc-200/80` / `zinc-800` dark) precedes table loading; 4 placeholder card rows during Livewire loading.
- **Logo** — `x-application-logo`: a `40×40` rounded-xl SVG tile, indigo→violet gradient (`#6366e3→#4339be`), white glyph (double posts "≡" + dot), white 12%-stroke hairline, single `#4f46d8` dot accent.

## Do's and Don'ts

**The Calm-Opens-Rule.** Keep surfaces pale, borders hairline, shadows soft; add color only where it means something (status, active, brand). Do not fill panels with tinted backgrounds — one accent wash per card is the ceiling.

**The Status-Stays-Self-Evident Rule.** Status colors must stay meaning-locked to urgency tiers (sky→amber→red→rose as expiry approaches). Never remap them for decoration, and never invent a new status hue that isn't in the ladder.

**The Shell-Stays-Fixed Rule.** Route pages re-skin — they never rebuild the shell. Preserve: fixed sidebar + sticky blur topbar + `max-w-7xl` content column + mobile drawer + footer tagline.

**The Table-Keeps-Its-Rhythm Rule.** Index tables keep the sortable-header + hover-row pattern and the 16–20px cell rhythm. Do not introduce zebra striping or dissolve the borders between card and table.

**Responsive** — stack stat grids (`sm:grid-cols-2` → `xl:grid-cols-4`), keep filter rows wrapping (`sm:flex-row sm:flex-wrap`), and let tables scroll horizontally rather than truncating columns on mobile. Buttons and inputs never shrink below comfortable tap size.

**Motion** — transitions are `150–200ms` ease-in-out, confined to transform/opacity/color for hovers, theme class flips, and drawer/scale/modal entrances. No entrance choreography on page load; wire:navigate swaps stand in for route transitions.