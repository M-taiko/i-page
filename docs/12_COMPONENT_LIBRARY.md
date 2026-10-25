# Component Library

## Purpose

Single reference for the reusable Blade components used by authenticated dashboards. Views never hand-write Valex markup for common patterns; they use these components so the markup lives in one place (see ADR-011).

## Scope

Authenticated pages rendered with `layouts.valex` (`/admin/*`, `/tenant/*`, `/organizations/*`, posts, tickets, audience segments, users, groups, settings). Guest-facing pages (`/browse*`, `/qr/*`, `/otp/*`, auth, feed/mobile shell) keep their own components (`*-modern`, `btn`, `badge`, `form-input`) and must not use these.

## Principles

- **Valex is Bootstrap 4.** Use `data-toggle`/`data-target`, `custom-select`/`form-control`, `badge badge-*`, `ml-/mr-`. Never use Bootstrap 5 attributes in these views.
- **Components over copy-paste.** Repeated markup becomes a component in `resources/views/components/valex/`.
- **Generic terminology.** Organization, Branch, Department, Channel, Visitor, Member.
- **Direction-aware.** `layouts.valex` loads `css-rtl/` for Arabic users and `css/` otherwise. Prefer symmetric utilities (`mx-2`) over `ml-`/`mr-` in components.
- **Dark mode** is `body.dark-theme`, toggled from the header and stored in `localStorage('theme')`.

## Layout

`layouts.valex` yields: `title`, `css`, `page-header`, `content`, `js`; stacks: `styles`, `scripts`. Partials: `layouts/valex/{sidebar,header,footer,flash}.blade.php`. Flash messages (`success`, `error`, `warning`, `info`) and validation errors are rendered by the layout, so views must not repeat them.

## Components

| Component | Props / slots | Notes |
|---|---|---|
| `<x-valex.page-header>` | `title`, `subtitle`, `breadcrumbs[]`, slot `actions` | Breadcrumb header used on every page. |
| `<x-valex.card>` | `title`, `subtitle`, slot `actions` | Standard card with optional header. |
| `<x-valex.stat-card>` | `title`, `value`, `icon` (feather), `variant` | Gradient KPI card. Wrap in `col-*`. |
| `<x-valex.hero>` | `title`, `subtitle`, slot `actions` | Gradient summary banner for detail pages. |
| `<x-valex.badge>` | `variant`, `pill` | Status/priority badges. |
| `<x-valex.empty-state>` | `title`, `message`, `icon`, default slot | Empty list placeholder with optional CTA. |
| `<x-valex.input>` | `name`, `label`, `type`, `value`, `help`, `required` | Handles `old()`, `is-invalid`, error text. Extra attributes pass through. |
| `<x-valex.textarea>` | `name`, `label`, `value`, `rows`, `help`, `required` | Same behavior as input. |
| `<x-valex.select>` | `name`, `label`, `options[value=>label]`, `value`, `placeholder`, `help` | Selected value resolved from `old()` first. |
| `<x-valex.switch>` | `name`, `label`, `checked`, `help` | Emits a hidden `0` plus the checkbox so unchecking is submitted. Use plain checkboxes where the controller relies on `has()`. |

| `<x-valex.kpi-card>` | `kpi` (from `DashboardStatsService`), `days` | KPI with period-over-period change. |
| `<x-valex.range-switch>` | `days` | 7/30/90-day selector, keeps other query params. |
| `<x-valex.engagement>` | `engagement` | Delivered/viewed/read/acknowledged progress bars. |
| `<x-valex.health-badge>` | `health` (from `CrmService::health`) | Organization health label + score. |
| `crm/_activity-table` (partial) | `logs`, `showOrganization` | Shared audit table for both CRMs. |

## Public Page Blocks (Design Templates, ADR-012)

Blocks live in `resources/views/guest/valex/blocks/` and are rendered by `guest/valex/organization.blade.php` in the order defined per design in `config/designs.php`. They receive `$organization`, `$channelGroups`, `$posts`, `$options`, `$design`, `$isFollowing`.

| Block | Valex pattern used |
|---|---|
| `hero.figure` | images (figure/figcaption), avatar |
| `hero.profile` | profile, avatar |
| `channels.cards` | cards |
| `channels.accordion` | accordion (Bootstrap 4 collapse) |
| `channels.carousel` | carousel (Bootstrap 4 carousel) |
| `channels.list` | list-group |
| `posts.timeline` | timeline (`.vtimeline`) |
| `posts.blog` | blog cards |
| `posts.list` | list-group |
| `about.info` | avatar + list |
| `_cover` (partial, used by `hero.figure` and `channel.hero-figure`) | carousel: static image for 1 cover, Bootstrap 4 carousel for 2+ (autoplay, interval, slide/fade, arrows, dots, height) |

Note: the Valex theme has no swiper, blog-details or timelineui demo; carousel, blog cards and timeline cover those needs.

## Post Partials (ADR-013)

Shared by every page that renders a post, so formats look and behave the same everywhere (post page, channel/organization feeds, user feed, dashboard post page).

| Partial | Purpose |
|---|---|
| `posts/partials/_content` | Title, text, media and badges in the post's card style (classic, image, gradient, spotlight). Options: `limit`, `url`, `showWidgets`. |
| `posts/partials/_media` | Single image or a dependency-free carousel for gallery posts (scroll-snap, arrows, dots, autoplay). |
| `posts/partials/_widgets` | Reactions bar plus poll / event RSVP / rating widgets; enforces sign-in via links to login. |
| `posts/partials/_chips` | Compact badge / priority / format chips for list renderings. |
| `posts/partials/_styles` | Self-contained CSS + JS, included once; works on the classic shell and Valex pages. |

Definitions (formats, card styles, badges, reactions, limits) live in `config/posts.php`.

## Examples

```blade
@extends('layouts.valex')
@section('page-header')
    <x-valex.page-header :title="__('Brands')" :subtitle="$organization->name">
        <x-slot:actions><a class="btn btn-primary" href="#">{{ __('New Brand') }}</a></x-slot:actions>
    </x-valex.page-header>
@endsection
@section('content')
    <x-valex.card :title="__('Basic Information')">
        <x-valex.input name="name" :label="__('Brand Name')" required />
        <x-valex.select name="type" :label="__('Type')" :options="['a' => 'A', 'b' => 'B']" />
    </x-valex.card>
@endsection
```

Third-party widgets are loaded per page through `@push('styles')` / `@push('scripts')` from `public/assets/valex/plugins/*` (Chart.js, DataTables, Select2, Dropify, ...), never globally.

## Best Practices

- Pass data to components with `:prop` (bound), text with `__()`.
- Wrap stat cards in `col-sm-6 col-xl-3` rows for mobile-first stacking.
- Use `table-responsive` around every table; keep action buttons icon-only below `md`.
- Confirm destructive actions with `onclick="return confirm(...)"` or a modal.

## Future Considerations

- DataTables wrapper component with server-side pagination.
- Chart component (`<x-valex.chart>`) with a shared dark-mode palette.
- Brand-color theming for white-label tenants (CSS variables over Valex palette).

## Related Documents

[[10_UI_DESIGN_SYSTEM.md]], [[26_ARCHITECTURE_DECISION_RECORDS.md]] (ADR-011), [[27_FEATURE_SPECIFICATIONS.md]], [[29_DESIGN_TOKENS.md]]

## Change Log

| Date | Change |
|---|---|
| 2026-10-25 | Initial Valex component set (ADR-011). |

# Claude Compliance Checklist

Before implementing any feature, verify:

- [ ] This implementation follows the Product Constitution.
- [ ] This implementation aligns with the Product Vision.
- [ ] It is Mobile First.
- [ ] It supports RTL.
- [ ] It supports LTR.
- [ ] It supports Dark Mode.
- [ ] It is accessible.
- [ ] It is reusable.
- [ ] It is configurable.
- [ ] It is scalable.
- [ ] It supports multiple industries.
- [ ] It supports multi-tenancy.
- [ ] It follows Laravel Best Practices.
- [ ] It does not introduce duplicated code.
- [ ] It aligns with the vision:
      "The Digital Front Door for Every Organization."
