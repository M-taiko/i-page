# Architecture Decision Records (ADRs)

All significant architectural decisions are recorded here. Each ADR has: Decision, Status (Accepted/Rejected/Deprecated), Rationale, Consequences, and Date.

---

## ADR-001: Guest Public Channel Access

**Date**: 2026-07-08

**Title**: Guests Can Browse Public Channels Without Authentication

**Status**: Accepted

**Decision**: Unauthenticated guests (users who do not have an account) can:
- Access `/` (home feed)
- Access `/explore` (search organizations)
- Access `/org/{id}` (view organization details)
- Access `/org/{id}/channel/{slug}` (view public channel posts)

Guests **cannot**:
- Access `/admin/*` or `/organization/{id}/dashboard/*`
- Create posts, reactions, or send messages
- View private channels

**Rationale**:
1. **Lower Friction**: Removes registration requirement for first-time visitor. Visitor can scan QR → view relevant content immediately.
2. **Higher Engagement**: "Try before you buy" approach increases likelihood of registration.
3. **QR-First Philosophy**: Aligns with Constitution's emphasis on QR as primary entry point. Guests scan QR, see content, then opt-in to register.
4. **Industry Best Practice**: Hotels, hospitals, schools often have public waiting rooms or lobbies where unauthenticated guests should see relevant info (welcome, policies, events).

**Consequences**:
- **Positive**: Higher engagement rate, lower bounce rate, increased QR-scan-to-register conversion
- **Negative**: Public channels must be carefully designed (no sensitive data); requires content moderation

**Implementation**:
- Route middleware: `Route::middleware('guest')->group()` for public routes
- GuestController handles guest views (home, search, org detail, channel detail)
- Queries filter `where('type', 'public')` for unauthenticated users
- CheckRole middleware redirects guests to `/` if they try `/admin` or tenant routes

**Related**: [[40_PRODUCT_GUARDIAN.md]] "Every public feature must consider Guest users"

---

## ADR-002: Hotel → Organization Rename

**Date**: 2026-07-11

**Title**: Multi-Tenant Entity Named "Organization" Not "Hotel"

**Status**: Accepted

**Decision**: The multi-tenant root entity is called **Organization**, not Hotel.

**Business Terms**:
- Organization (replaces Hotel)
- Branch (replaces Office)
- Department (no equivalent in Hotel model)
- Channel (communication hub)
- Member/Staff/Visitor (replaces Guest in hotel context)

**Rationale**:
1. **Industry Agnostic**: "Hotel" assumes hospitality vertical. Organization generalizes to hospitals, schools, retail, offices, factories, etc.
2. **Compliance with Constitution**: [[00_PROJECT_CONSTITUTION.md]] explicitly states "It is not a Hotel System" and mandates generic terminology.
3. **Future Flexibility**: Renaming now prevents refactoring later when adding non-hospitality tenants.
4. **Terminology Consistency**: All code, docs, and UI use generic terms. Reduces confusion for developers unfamiliar with hotel domain.

**Consequences**:
- **Positive**: Clear intent as multi-tenant platform; easier onboarding for new industries
- **Negative**: Database migrations required (rename tables, FKs, columns)

**Implementation**:
- Migration: Rename `hotels` table → `organizations`
- Migration: Rename `hotel_user` table → `organization_user`
- Migration: Update all FKs and column names (hotel_id → organization_id)
- Models: Hotel.php deleted; Organization.php introduced
- Controllers: HotelController replaced by OrganizationController
- Views: hotel/* paths replaced by admin/organizations/*
- Routes: /hotel/* replaced by /admin/organizations/* (super-admin scoped)

**Scope Change**: Hotel-specific features (check-in/check-out, room assignments) now framed as:
- Check-in = visitor arrival (channel/department context)
- Room assignments = department/team assignments (generalized)

**Related**: [[00_PROJECT_CONSTITUTION.md]], [[40_PRODUCT_GUARDIAN.md]]

---

## ADR-003: Repository Pattern for Multi-Tenant Isolation

**Date**: 2026-07-11

**Title**: Repository Pattern Enforces Tenant Isolation at Query Level

**Status**: Accepted

**Decision**: All database queries pass through Repository classes that automatically filter by `organization_id`. No direct Eloquent queries in controllers.

**Pattern**:
```php
// app/Repositories/Contracts/PostRepositoryInterface
interface PostRepositoryInterface {
    public function paginate(array $filters);
    public function create(array $data);
    public function update(Model $post, array $data);
}

// app/Repositories/Eloquent/PostRepository
class PostRepository implements PostRepositoryInterface {
    public function paginate(array $filters) {
        $query = Post::query();
        
        // Automatic organization filtering
        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }
        
        // Other filters...
        return $query->paginate();
    }
}
```

**Rationale**:
1. **Security**: Eliminates risk of accidental data leakage (query without organization_id filter)
2. **Consistency**: Ensures all queries follow the same organization-scoping pattern
3. **Testability**: Repositories can be tested with mock filters
4. **Maintainability**: Centralizes query logic; changes to filtering apply everywhere

**Consequences**:
- **Positive**: Strong multi-tenant enforcement; easy to audit queries
- **Negative**: Repository layer adds indirection (not direct Eloquent in controllers)

**Related**: [[05_DATABASE_ARCHITECTURE.md]] Multi-Tenant Isolation Strategy

---

## ADR-004: Soft Deletes + Audit Trail

**Date**: 2026-07-11

**Title**: All Core Tables Use Soft Deletes + Audit Columns (created_by, updated_by, deleted_by)

**Status**: Accepted

**Decision**: All core tables (Organization, Channel, User, Post, Branch, Department) have:
- `deleted_at (timestamp, nullable)` — enables soft delete via SoftDeletes trait
- `created_by (FK → users.id)` — user who created the record
- `updated_by (FK → users.id)` — last user to modify
- `deleted_by (FK → users.id)` — user who performed soft delete

Queries automatically exclude soft-deleted records (via global scope).

**Rationale**:
1. **Compliance**: Audit trail required for SaaS, healthcare (HIPAA), finance (SOX) compliance
2. **Data Recovery**: Soft deletes allow restoration of accidentally deleted records
3. **Forensics**: Audit columns reveal who changed what, when—critical for security reviews
4. **Constitution**: [[00_PROJECT_CONSTITUTION.md]] explicitly requires "created_by/updated_by/deleted_by on every table"

**Implementation**:
- Model trait: `use Auditable` (wraps SoftDeletes + auto-fills audit columns)
- Observer or middleware: On model save/delete, auto-populate created_by, updated_by, deleted_by from `auth()->user()->id`
- Queries: Automatic exclusion of soft-deleted records via global scope
- Migrations: Add columns to existing tables via migration

**Consequences**:
- **Positive**: Full audit trail; compliance-ready; data recovery capability
- **Negative**: Database size grows (soft-deleted records still consume storage); queries must account for deleted_at scope

**Related**: [[05_DATABASE_ARCHITECTURE.md]] Audit Trail Implementation

---

## ADR-005: Roles Stored Across Two Systems

**Date**: 2026-07-11

**Title**: Global Roles via Spatie; Organization/Channel Roles via Pivot Tables

**Status**: Accepted

**Decision**: Two role systems coexist:

1. **Global Roles** (spatie/laravel-permission):
   - super_admin (system operator; all-access)
   - guest (unauthenticated users; no permissions)

2. **Tenant-Scoped Roles** (pivot tables):
   - organization_user.role = 'admin', 'staff', 'member', 'guest'
   - channel_user.role = 'admin', 'moderator', 'member', 'viewer'

No global "admin" role exists. Organization-level admins use `organization_user.role = 'admin'`.

**Rationale**:
1. **Scalability**: Avoids creation of hundreds of permission rows for multi-tenant RBAC
2. **Clarity**: Organization admin ≠ system admin (super_admin). Clear distinction.
3. **Flexibility**: Each tenant can define custom role names if needed (future)
4. **Query Efficiency**: Pivot tables allow fast org/channel membership queries without joining permission tables

**Implementation**:
- CheckRole middleware: Validates global roles (super_admin, guest)
- Policies: Check tenant-scoped roles from pivot tables
- UserRepositoryInterface: Filters by organization context
- Migration: organization_user and channel_user pivots with role column

**Consequences**:
- **Positive**: Efficient multi-tenant RBAC; clear role hierarchy
- **Negative**: Two role systems require developer discipline (not confusing them)

**Related**: [[06_PERMISSION_MODEL.md]], [[05_DATABASE_ARCHITECTURE.md]]

---

## ADR-006: Laravel Reverb for Real-Time (Future)

**Date**: 2026-07-11

**Title**: Real-Time Messaging Uses Laravel Reverb (WebSocket Server)

**Status**: Accepted (Future Implementation)

**Decision**: When real-time channel chat is implemented, use Laravel Reverb (first-party Laravel WebSocket library) instead of third-party services.

**Technology Stack**:
- Laravel Reverb (PHP WebSocket server; runs as separate daemon)
- Redis pub/sub for message broadcasting
- Pusher as fallback (for hosted environments where Reverb daemon unavailable)

**Rationale**:
1. **First-Party**: Reverb is Laravel-native; simplifies integration
2. **Cost**: Self-hosted Reverb cheaper than long-term Pusher/Ably subscription
3. **Control**: Full data ownership; no third-party SaaS dependency
4. **Scalability**: Reverb + Redis scales horizontally

**Consequences**:
- **Positive**: Lower cost-of-ownership; full control
- **Negative**: Requires managing separate process (Supervisor/systemd); adds operational complexity

**Timeline**: Post-MVP; prioritize after core features stable

**Related**: [[27_FEATURE_SPECIFICATIONS.md]] Real-Time Channel Chat feature

---

## ADR-007: Denormalized Analytics Columns

**Date**: 2026-07-11

**Title**: Use Denormalized Cache Columns for Analytics (Not Event Table)

**Status**: Proposed

**Decision**: Analytics are tracked via denormalized columns on core tables, not a separate analytics/events table:
- Organization.users_count, channels_count, posts_count (cached)
- Channel.member_count_cache, post_count_cache, view_count_cache
- Post.view_count, like_count, comment_count

Columns updated on model changes (observer or event listener). Detailed analytics exported nightly to data warehouse if needed.

**Rationale**:
1. **Performance**: Avoids N+1 queries and slow joins; cache columns indexed
2. **Simplicity**: No separate analytics table to maintain; fewer moving parts
3. **Real-Time**: Cache columns updated immediately (no eventual consistency delays)
4. **MVP Scope**: Sufficient for MVP dashboard; detailed analytics added later

**Consequences**:
- **Positive**: Fast dashboard queries; simple mental model
- **Negative**: Risk of cache staleness if observer/listener fails; no granular historical data (deleted counts lost)

**Alternative Rejected**: Separate analytics/events table (slower; requires aggregation queries)

**Timeline**: Short-term use denormalized columns; long-term (post-MVP) evaluate full event store

---

## ADR-008: Mobile-First Responsive Design

**Date**: 2026-07-11

**Title**: All UI Designed Mobile-First; Desktop as Fallback

**Status**: Accepted

**Decision**: Design process starts with mobile (< 640px viewport). Desktop (> 1024px) is enhancement, not primary target.

**Breakpoints**:
- Mobile: < 640px (primary)
- Tablet: 640px – 1024px
- Desktop: > 1024px

**Constraints**:
- No horizontal scrolling (viewport must fit 320px width minimum)
- Touch targets ≥ 44x44px
- Typography scaled for readability on small screens

**Rationale**:
1. **Constitution**: [[40_PRODUCT_GUARDIAN.md]] "Every page must work on Mobile before Desktop"
2. **Market**: ~70% of traffic expected on mobile (guests scanning QR on phones)
3. **QR-First**: QR scanning primarily mobile-first experience
4. **Accessibility**: Mobile-friendly constraints (large touch targets, readable fonts) improve accessibility overall

**Consequences**:
- **Positive**: Better mobile UX; better accessibility; responsive scales naturally to desktop
- **Negative**: Requires discipline in design reviews (mobile constraint enforced early)

**Related**: [[10_UI_DESIGN_SYSTEM.md]], [[40_PRODUCT_GUARDIAN.md]]

---

## ADR-009: Dark Mode Support Required

**Date**: 2026-07-11

**Title**: Dark Mode Support Mandatory for All Components

**Status**: Accepted

**Decision**: All UI components must support both light and dark modes. Theme controlled by user preference (stored in users.theme column) and CSS variables.

**Implementation**:
- CSS variables: `--primary-600`, `--surface-bg`, `--text-primary`, etc. (defined in design-system.css)
- html root attribute: `data-theme="light" | data-theme="dark"`
- Preference stored: `users.theme = 'light' | 'dark'`
- Fallback: Light mode if no preference

**Design Tokens**:
- Light mode: `--surface-bg: #ffffff`, `--text-primary: #000000`
- Dark mode: `--surface-bg: #0a0a0a`, `--text-primary: #ffffff`

**Rationale**:
1. **Constitution**: [[40_PRODUCT_GUARDIAN.md]] "Every Feature must support Dark Mode"
2. **User Preference**: ~40% of users prefer dark mode (based on OS preferences)
3. **Eye Strain**: Reduced eye strain for night-time users; improves accessibility
4. **Inclusivity**: Benefit users with light sensitivity

**Consequences**:
- **Positive**: Better UX for night users; accessibility boost
- **Negative**: Requires designing and testing both modes (initial overhead, but amortized)

---

## ADR-010: RTL + LTR Support

**Date**: 2026-07-11

**Title**: UI Supports Right-to-Left (RTL) and Left-to-Right (LTR) Languages

**Status**: Accepted

**Decision**: App supports both RTL (Arabic, Hebrew, Urdu) and LTR (English, Spanish, French, etc.) languages. Layout automatically flips for RTL.

**Implementation**:
- html root: `dir="rtl" | dir="ltr"` (set by locale middleware)
- CSS: Use logical properties (margin-inline instead of margin-right; padding-inline-start instead of padding-left)
- Blade: Use {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }} for dynamic dir attribute

**Supported Languages** (MVP):
- en (English, LTR)
- ar (Arabic, RTL)

**Rationale**:
1. **Constitution**: [[40_PRODUCT_GUARDIAN.md]] "Every Feature must support RTL"
2. **Market**: Arabic-speaking regions (Middle East, North Africa) are target markets
3. **Global SaaS**: Multi-language support table-stakes for enterprise SaaS
4. **No Added Cost**: Logical CSS properties minimal overhead

**Consequences**:
- **Positive**: Accessible to Arabic-speaking users; positions as global platform
- **Negative**: Requires RTL-aware design (no absolute positioning of UI elements)

**Related**: [[10_UI_DESIGN_SYSTEM.md]], [[29_DESIGN_TOKENS.md]]

---

## ADR-011: Adopt the Valex Theme for Authenticated Dashboards

**Date**: 2026-10-25

**Title**: Valex (Bootstrap 4) Becomes the UI Shell for All Authenticated Pages

**Status**: Accepted

**Decision**: The licensed Valex admin theme replaces the `app-modern` shell (Bootstrap 5 via CDN) for authenticated pages: `/admin/*`, `/tenant/*`, `/organizations/*`, settings, users, groups, posts, tickets, audience segments, organization dashboards. Public guest-facing pages (`/browse*`, `/qr/*`, `/otp/*`, welcome, login/register) keep their current Guest-First mobile shell until a separate decision.

**Implementation**:
- Theme assets are copied (only the plugins in use) to `public/assets/valex/`. The source folder `Valex Dashboard/` is not served and not deployed.
- New layout `layouts/valex.blade.php` with partials for head, sidebar, header, footer and scripts. The permission-based, multi-organization sidebar logic is ported from `sidebar-modern`.
- Direction: LTR loads `css/`, RTL loads `css-rtl/` (Valex ships separate builds), selected from the user's language as today (ADR-010).
- Dark mode: driven by Valex `style-dark.css` / `skin-modes.css`, still persisted in `users.theme` (ADR-009).
- Blade components (`card-modern`, `stat-card`, `btn`, `badge`, `form-input`, ...) are rewritten to emit Valex markup, so views consume components rather than raw theme classes.
- Migration is incremental per route group; old and new layouts coexist until the last group is moved, then dead layouts are removed.

**Rationale**:
1. The owner purchased the theme and wants a single consistent premium look.
2. Reusing a finished theme is cheaper than extending the hand-built design system.
3. Component wrappers keep views decoupled from the theme (white-label readiness, see [[20_WHITE_LABEL.md]]).

**Consequences**:
- **Positive**: Consistent, polished dashboards; ready-made RTL, dark mode, tables, charts.
- **Negative**: Valex is Bootstrap 4 and jQuery based while the app used Bootstrap 5, so every migrated view needs class/attribute conversion (`data-bs-*` to `data-*`, `ms/me` to `ml/mr`, `form-select` to `custom-select`, `badge bg-*` to `badge-*`). The theme's Bootstrap 4 is end-of-life, so it must not leak into guest pages or be used for new public-facing UI.
- Theme license terms must be respected (no redistribution of the theme source).

**Related**: [[10_UI_DESIGN_SYSTEM.md]], [[12_COMPONENT_LIBRARY.md]], [[29_DESIGN_TOKENS.md]], [[27_FEATURE_SPECIFICATIONS.md]]

---

## ADR-012: Organization Design Templates (block-based public page designs)

**Date**: 2026-10-25

**Title**: Each organization can choose an industry-specific Design Template for its public page

**Status**: Accepted

**Decision**: A *design* is an ordered list of reusable *blocks* (hero, channels, posts, about) defined in `config/designs.php`, grouped by the organization template's `industry_key`. An organization stores `design_key` (+ `design_options` JSON for colors/toggles). When `design_key` is set, `/browse/org/{id}` renders through a dedicated Valex-based layout (`layouts.guest-valex`); when null, the existing page is rendered unchanged.

**Rationale**:
1. Valex components need Bootstrap 4, jQuery and Valex CSS, which cannot coexist with the guest `mobile-shell` layout (ADR-011); a separate layout isolates them.
2. Designs as data keep them reusable, configurable and cheap to add (White Label, see [[20_WHITE_LABEL.md]]); no page is duplicated per industry.
3. Null `design_key` keeps every existing organization working with zero migration risk.

**Consequences**:
- **Positive**: Different look per industry; admins can change design without code; new designs are configuration.
- **Negative**: Two public rendering paths (classic + Valex) until the classic page is retired; Valex CSS adds weight on the designed page (only loaded there).

**Implementation**: `organizations.design_key`, `organizations.design_options`; `App\Support\Designs`; blocks in `resources/views/guest/valex/blocks/`; switch in `GuestController::organizationDetail`.

**Addendum (channels)**: Channels and sub-channels can also choose a design (`channels.design_key`, `channels.design_options`). Channel designs are industry-agnostic (`config/designs.php` → `channels`) and reuse the same blocks. Resolution order: the channel's own design, then its parent channel's design, otherwise the classic channel page. Colors fall back channel option → organization option → design accent.

**Related**: [[12_COMPONENT_LIBRARY.md]], [[27_FEATURE_SPECIFICATIONS.md]], ADR-011

---

## ADR-013: Post Formats (interactive post types)

**Date**: 2026-10-25

**Title**: Posts have a format, a card style, a badge and per-format interactions

**Status**: Accepted

**Decision**: A post has a `post_format` (standard, gallery, poll, event, rating) that decides its form fields, rendering and interaction; plus a `card_style`, an optional `badge_key`/`badge_label`, the existing `priority`, and a per-post set of enabled reactions. Formats, card styles, badges and reaction sets are defined in `config/posts.php` (data, not code). Format-specific settings live in `posts.options` (JSON); interactions are stored in dedicated tables (`post_poll_options`, `post_poll_votes`, `post_rsvps`, `post_ratings`); reactions reuse `reactions.type`; gallery images reuse `media` (`usage_type = gallery`).

**Rationale**: New formats become configuration plus one widget partial; interactions need relational integrity (one vote per user, capacity limits) so they are not stored in JSON; existing posts remain valid (`standard`, `classic`).

**Consequences**: Positive: richer engagement without new modules per feature; negative: every place that renders a post must use the shared partials (`posts/_badge`, `posts/_media`, `posts/_widgets`).

**Related**: [[27_FEATURE_SPECIFICATIONS.md]], [[12_COMPONENT_LIBRARY.md]], ADR-012

---

## ADR-014: Channel hierarchy publishing, highlights and per-member channel permissions

**Date**: 2026-11-14

**Title**: Posts cascade down the channel tree at read time; pinned posts are highlights; channel members carry granular permissions

**Status**: Accepted

**Decision**:
1. **Cascade at read time, not by copying.** A channel's feed is its own posts plus posts of every ancestor channel whose `posts.cascade` flag is on. One post row, one set of reactions/comments/receipts, no duplicates to keep in sync. A public channel only inherits from public ancestors, so private content never leaks downward into public pages.
2. **Highlights.** A pinned post (`posts.pinned_until` in the future) is shown in a highlights strip at the top of the channel with a thumbnail (`options.pin_thumb`, else the post's first image). First tap opens a preview sheet (summary); second tap opens the post. Pins cascade like any other post.
3. **Post presentation per channel.** `channels.design_options.posts` holds layout (list / grid / compact), a forced card style, excerpt length and visibility toggles (author, date, views, reactions, comments). A sub-channel without its own settings inherits its parent's.
4. **Granular channel permissions.** `channel_user.can_post / can_edit / can_delete` are set per member when inviting or later. Organization admins/managers, the channel admin and super admins always have all three. All checks go through `Channel::userCan()`.

**Rationale**: Read-time cascade keeps engagement data unified and makes "unpublish from children" a single flag. Permissions on the pivot keep the model simple (no new tables) and are independent of the member's role label.

**Consequences**: Feed queries include ancestor ids (bounded, cached per request); every feed must use `Channel::feedQuery()` instead of `$channel->posts()`.

**Related**: [[27_FEATURE_SPECIFICATIONS.md]], [[06_PERMISSION_MODEL.md]], ADR-012, ADR-013

---

## ADR-015: Brand as a command center

> **Superseded by ADR-016** — brands were removed; the tenant is the command center.

**Date**: 2026-11-15

**Title**: Brand posts, brand managers, brand-scoped statistics, post notifications and member visibility

**Status**: Accepted

**Decision**:
1. **Brand posts** are posts with `brand_id` set, `channel_id` NULL and `brand_audience` in `all | public | private`. They are resolved at read time by `Channel::feedQuery()` for every channel of the brand (public channels never receive `private` audience posts). Brand pins appear in every brand channel's highlights.
2. **Brand managers** live in `brand_user` (role `admin` or `moderator`), assigned by organization admins. A brand admin manages every channel of the brand (all post permissions, member management, brand dashboard); a brand moderator can edit and delete posts in the brand's channels. Neither has rights on other brands.
3. **Notifications**: `PostNotificationService::published()` runs after every channel or brand post. Channel admins/moderators and brand managers get a management notice; approved, non-muted subscribers get a new-post notice. Recipients are de-duplicated and the author is excluded. Rows are bulk-inserted (the host runs the queue synchronously).
4. **Member visibility**: organization admins/managers and brand managers (within their brand) see every channel's members. Everyone else only sees channels they are an approved member of, and only those channels' members.

**Rationale**: Read-time resolution keeps one post row (one set of reactions, comments and receipts) regardless of how many channels show it. A pivot table for brand roles keeps organization roles untouched.

**Consequences**: Feed queries gain one more OR branch (indexed on `brand_id`). Every place that lists channel members must use `Channel::membersVisibleTo()`. Dashboard channel lists use `Channel::scopeVisibleTo()`; channel settings, member management and QR codes require `Channel::canAdminister()` (managers plus the channel's own admins), so plain staff can no longer edit channels they merely belong to. Private-channel and private-audience posts are now openable on the public post page by people with access (`Post::isVisibleTo()`), so notification links never dead-end for them.

**Related**: ADR-014, [[06_PERMISSION_MODEL.md]], [[27_FEATURE_SPECIFICATIONS.md]]

---

## ADR-016: Brand removed — the tenant is the command center

**Status**: Accepted (2026-11-20). Supersedes ADR-015.

**Context**: The brand layer (Organization → Brand → Channels) duplicated what the tenant and the channel tree already express. Owners think in "Hilton → Hilton Cairo → Front Desk / Restaurants → KFC", which is tenant → channel → sub-channel. A second management tier made permissions, statistics and posting harder to explain.

**Decision**:
1. **No brands.** The `brands`, `brand_user` and `brand_followers` tables and every `brand_id` column are dropped. Hierarchy: **Tenant (organization) → Channel → Sub-channel (any depth)**.
2. **Tenant posts**: a post with `organization_id` set and `channel_id NULL`. `posts.placement` decides where it appears:
   - `page`: only on the public tenant page;
   - `all`: every channel of the tenant (public channels only receive it when it is not marked staff-only);
   - `selected`: the channels listed in the `post_channel` pivot; with `cascade` on, also their sub-channels at any depth.
   `posts.show_on_page` controls whether it also appears on the public tenant page. Resolution happens at read time in `Channel::feedQuery()` and `highlights()`; private ancestors never leak into public sub-channels (`inheritableAncestorIds()`).
3. **Inherited channel roles**: an approved `channel_user` role of `admin` or `moderator` applies to the channel **and every sub-channel below it**. `Channel::roleFor()` resolves the strongest own or inherited role. Admin: everything in the subtree (settings, members, QR, post/edit/delete). Moderator: edit/delete/pin. Member: only the granted `can_post/can_edit/can_delete`.
4. **The tenant assigns channel managers** from Organization → Channel managers (org admins/managers only).
5. **Visibility**: org admins/managers see every channel and person; channel admins/moderators see their subtree and its members; staff and visitors only the channels they belong to (`Channel::scopeVisibleTo()`, `membersVisibleTo()`).

**Rationale**: One management tier fewer, the same capabilities (one post, many channels; delegated managers; scoped visibility) with fewer concepts. Generic for any industry: a school (faculty → department), a retailer (branch → section) or a hotel group (hotel → department).

**Consequences**: Irreversible data removal for existing brands (no production data depended on them yet). Notifications (`PostNotificationService`) resolve tenant-post channels from placement. Every brand UI, route and policy is removed.

**Related**: ADR-014, [[06_PERMISSION_MODEL.md]], [[27_FEATURE_SPECIFICATIONS.md]]

---

## Decision Template (Use for Future ADRs)

**Date**: YYYY-MM-DD

**Title**: [Short decision title]

**Status**: Accepted | Rejected | Deprecated

**Decision**: [What decision was made?]

**Rationale**: [Why this decision over alternatives?]

**Consequences**: [What are the positive and negative impacts?]

**Implementation**: [How is this enforced in code?]

**Related**: [Links to related ADRs, docs, or features]

---

# Claude Compliance Checklist

Before recording a new ADR, verify:

- [ ] Decision is significant (affects multiple systems or long-term direction)
- [ ] Rationale explains trade-offs
- [ ] Consequences documented (positive and negative)
- [ ] Related documents linked
- [ ] Status clearly stated (Accepted/Rejected/Deprecated)
- [ ] Date recorded (YYYY-MM-DD)
- [ ] ADR is immutable (not updated after acceptance)
