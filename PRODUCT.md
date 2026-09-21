# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

A solo domain & hosting reseller — one operator running their own reseller business. They open the panel daily to keep track of what they have sold (clients, services, domains, hosting panels/plans) and to make sure nothing they resell silently expires or is left to renew late. Admin-only user management exists behind a role check, but the product is built around a single working operator.

Confirmed by interview: solo reseller.

## Product Purpose

A single place to run a domain & hosting reseller business: hold client records, track every service and its billing cycle (domains and hosting plans/panels), preempt expiring services, record renewals, produce invoices, and see revenue. Success means the operator never misses a renewal and always knows the true state of their book of business.

## Positioning

Renewal-first reseller management. Every client, service, and plan is organized around when it renews and what its status is, with reminders tiered by urgency. "Never miss a renewal" is the guiding outcome of the product, not a side feature.

## Operating Context

- Used in a browser, behind login + email verification; part of the operator's daily routine rather than a one-off visit.
- Dashboard is the landing surface: active services, total clients, monthly revenue, renewals due within 30 days, a 6-month revenue chart, and an attention list of expiring services.
- Renewal reminders are tiered by time-to-expiry (Expired / Urgent / Due soon).
- Services belong to clients; panels/plans form the catalog a service is sold against; renewals are recorded per service.
- Invoices and reports support billing and analysis; an activity log records changes to the book of business.
- Money renders in the operator's configured default currency; revenue, renewal amounts, and invoices all respect it.

## Capabilities and Constraints

- Multi-currency is a hard, unchangeable requirement (confirmed by interview). Billing figures must always render truthfully in the operator's default currency.
- Roles exist (admin gates user management), but the core flow is a single operator.
- Domain services track expiry dates; hosting plans/panels track service status; paid renewals are recorded via ServiceRenewal.
- Reminder tiering is a real service (app/Services/ReminderTierCalculator + app/Enums/ReminderTier).
- Per-user theme preference (system / light / dark) is honored and persisted; sidebar collapse is persisted in localStorage.
- Technical stack is established: Laravel 12, Livewire (Blade views under resources/views/livewire), Tailwind CSS v4, Alpine.js, Chart.js.
- No marketing site lives in this repo.

## Brand Commitments

None confirmed (interview: "no brand"). The app name is a config placeholder; no logo, voice, content, or identity constraint was made binding.

## Evidence on Hand

- Routes in routes/web.php: dashboard, clients, panels, services, reports, users (admin), audit, invoices, settings, profile, auth.
- Dashboard copy and readiness in resources/views/livewire/dashboard.blade.php ("Your reseller business at a glance", "not cancelled", "paid renewals", "within 30 days").
- Renewal tiers: app/Services/ReminderTierCalculator and app/Enums/ReminderTier.
- Currency helper: `auth()->user()->defaultCurrency()`.
- Models: Client, HostingPlan, Panel, Service, ServiceRenewal, User.
- No testimonials, customer logos, pricing, or deployment claims exist; future work must not fabricate them.

## Product Principles

1. **Renewal-first.** Expiry and renewal state must be visible without hunting; reminders lead, records follow.
2. **One-operator efficiency.** Routine paths stay fast and low-friction because the panel is used every day by one person.
3. **Money stays truthful.** Currency, billing cycle, and paid/renewal status always render accurately in the operator's default currency.
4. **The record is the business.** Clients, services, panels, and renewals stay consistent and auditable so the operator can trust the book.
5. **Long sessions stay comfortable.** Theme follows preference, density stays calm, motion stays minimal — the panel is a tool, not a showpiece.

## Accessibility & Inclusion

No product-specific audience requirement was established beyond general web standards and the existing light/dark preference support.