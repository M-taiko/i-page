# i-Page Enterprise Architecture Review

**Type:** Audit & Gap Analysis (not a rebuild plan)
**Scope:** Compare the current i-Page implementation against the enterprise communication-platform vision ("The Digital Front Door for Every Organization") and produce an extension roadmap that preserves all existing functionality.
**Ground rule:** every recommendation below names the real file, table, or class it extends. Nothing here proposes replacing `Organization`, `Brand`, `Location`, `Department`, `Channel`, `Post`, `Ticket`, or the existing RBAC model.

---

## 1. Current Architecture Analysis

### 1.1 Stack
- Laravel (Breeze scaffolding) + server-rendered Blade. No SPA framework (no React/Vue/Inertia/Livewire).
- Single shared MySQL database, single `web` session guard. No Sanctum/API guard, no `routes/api.php`.
- Two coexisting UI generations: legacy Bootstrap 5 (`layouts/admin.blade.php`, `layouts/app.blade.php`, `layouts/tenant.blade.php`) and a newer custom design-system (`layouts/admin-modern.blade.php`, `layouts/tenant-modern.blade.php`, `layouts/mobile-shell.blade.php`, `resources/css/design-system.css`, `resources/css/components.css`).

### 1.2 Tenancy model
- **Row-level, shared-schema multi-tenancy.** `Organization` is the root tenant; nearly every domain table carries `organization_id` (channels, posts, tickets, brands, locations, departments, groups, audience_segments, sla_rules, qr_codes).
- No global tenant-scoping trait — every model/controller manually filters by `organization_id`.
- Current-tenant resolution is session-based: `User::getCurrentOrganizationAttribute()` reads `session('current_organization_id')`, with a super-admin special case (no membership rows, defaults to first org alphabetically or last-selected). `TenantDashboardController::switchOrganization` changes it.
- Two middleware enforce it: `CheckOrganizationAccess` (`organization.access` — resolves `{organization}` route param, shares `currentOrganization` to views) and `EnsureOrganizationActive` (`organization.active` — blocks writes when org is suspended/cancelled, bypassed by super admins).

### 1.3 Hierarchy today
```
Organization (tenant root)
 └── Brand (sub-brand, has `colors` JSON for theming)
      └── Location (renamed from `branches`; has type/lat-long/timezone)
           └── Department (self-referential parent_department_id)
Organization ── OrganizationMembership ── User   (the real RBAC/tenant-membership join)
Organization ── LocationMembership ── User        (per-location role)
```
`Branch` still exists as a deprecated class extending `Location`, kept only for backward compatibility.

### 1.4 Identity & RBAC (dual system, both real and in active use)
1. **Spatie `laravel-permission`** (`HasRoles` on `User`, `config/permission.php` with `teams => false`, i.e. global, not org-scoped). Global roles seeded: `super_admin, organization_admin, manager, moderator, staff, member`.
2. **`organization_memberships.role`** — a second, per-organization role column, the *de facto* multi-tenant RBAC layer since Spatie teams are off. `User::getDisplayRoleAttribute()` already prefers this over the global Spatie role.
3. **Laravel Policies** (`OrganizationPolicy`, `ChannelPolicy`, `PostPolicy`, `TicketPolicy`, etc.) gate most actions via `$this->authorize()` / route `can:` middleware — using ability names that look like Spatie permissions but are checked as policy methods, not `hasPermissionTo()`.

This is inelegant but functional; Section 4 recommends consolidation, not replacement.

### 1.5 Content domain
- **Channel** — `organization_id`, optional `brand_id`, `type` (`public`/`private`), self-referential `channel_channel` (parent/child channels), membership via `channel_user` pivot (`ChannelUser` pivot model: role/joined_at/muted_at).
- **Post** — full CMS: `post_type` (announcement/news/offer/emergency/feedback_request/survey), `priority`, a real approval workflow (`draft → pending_approval → approved/rejected → scheduled → published → expired/archived/cancelled`), `requires_acknowledgment`, `is_emergency`, `scheduled_for`, `pinned_until`, audience targeting via `PostAudience`/`AudienceSegment`, and delivery/engagement receipts (`PostReceipt`: delivered/viewed/read/acknowledged).
- **Comment** — moderation workflow (`approved`/`pending`/`rejected` scopes, `approve()`/`reject()`).
- **Group** — a lighter, older grouping construct that overlaps in purpose with Channel; not removed, just noted as a legacy parallel structure.
- **Ticket** — helpdesk module with `TicketMessage`, `SlaRule`/`SlaEvent` breach tracking.
- **QR** — `QrCode` (polymorphic `ownable`: Organization/Location/Department/Channel), `QrScanLog` (scan analytics). Full backend, `simplesoftwareio/simple-qrcode` installed, but **no admin UI renders/downloads the actual QR image** today — a confirmed gap (see 2.3).
- **ActivityLog** — custom audit trail (`app/Services/ActivityLogService.php` + observers `AuditableModelObserver`, `MembershipAuditObserver`, etc.), polymorphic `subject`, JSON `changes` diff, ip/user_agent already captured.
- **Notification** — custom `notifications` table (`user_id`, `type`, `data` JSON, `read_at`) — not Laravel's default notifiable channel, and `app/Notifications/*` is currently empty (no notification classes written yet).

### 1.6 UI
- Mobile-first shell (`mobile-shell.blade.php`) drives the guest browse flow and authenticated feed, with a floating bottom-nav pill and `.feed-tabs` (Latest/Trending, Channels/Posts) — recently restyled (commits `b46e95a`, `d96ef59`) to be consistent and stable across mobile viewport quirks.
- Admin/tenant consoles have both a legacy Bootstrap version and a "-modern" design-system version live simultaneously.
- Dark mode: `[data-theme]` attribute + `localStorage`, client-side only, not consistently available across all layouts.
- RTL: locale-driven in `mobile-shell`/`app-modern` (`dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"`), but **hardcoded** `dir="rtl"` in `admin-modern.blade.php` and `tenant-modern.blade.php` regardless of the signed-in user's language — a real bug (Phase 1 item, Section 9).

---

## 2. Gap Analysis

| # | Existing Feature | Gap | Recommended Improvement | Priority | Difficulty |
|---|---|---|---|---|---|
| 1 | Guest self-registration (`QrScanController`) | No OTP/phone verification anywhere in the codebase | Add `otp_codes` table + `OtpService`, require verification before a guest/basic account is usable | High | Low |
| 2 | Single flat `User` profile | No Basic/Private/Business profile tiers as distinct, enforced levels | Introduce `profile_level` + validation rules per tier over *existing* columns (see Section 5) | High | Low |
| 3 | `channel_user` pivot direct attach | Private channels have no join-request/approval step | Add approval via the new Workflow Engine (Section 2.6 / Phase 1) | High | Medium |
| 4 | `Organization → Brand → Location → Department` | Fixed 3-level shape; doesn't fit university/hospital/oil & gas shapes | Additive `org_units` generic hierarchy table (Section 3) | High | Medium |
| 5 | Org creation (`OrganizationController@store`) | No templates — every org starts blank | `organization_templates` + `OrganizationTemplateService` (Section 3) | High | Medium |
| 6 | `Channel.type` = public/private only | No corporate/regional/team/project/emergency/etc. channel types | Extend `type` values + `settings` JSON (Phase 2) | Medium | Low |
| 7 | `Post` approval only | No reusable approval mechanism for other modules (channel join, tickets escalation) | Configurable Workflow Engine (Section 2.6) | High | Medium |
| 8 | `notifications` table + `UserPreference` toggles | No SMS/push/Slack/Teams/webhook delivery, only in-app | `NotificationService` + provider adapters (Phase 2) | Medium | Medium |
| 9 | `activity_logs` + `ActivityLogService` | Already captures actor/org/ip/user_agent/diff | Add device/geo enrichment only — not a new system | Low | Low |
| 10 | `QrCode`/`QrCodeService` backend | No admin UI to generate/preview/download QR images | QR management UI in `tenant/channels/show.blade.php` (Phase 2) | Medium | Low |
| 11 | Two tenant-admin route surfaces (`/tenant`+`/organizations` vs legacy `/organization/{organization}/dashboard`) | Duplicated CRUD, divergent auth patterns (`can:` policies vs. custom middleware) | Converge onto the newer surface over 2-3 releases; freeze new features on the legacy one | Medium | High |
| 12 | Dual Bootstrap/design-system UI | Maintenance cost, inconsistent RTL/dark-mode support | Standardize on the design-system stack; retire `-modern` naming once legacy is gone | Medium | High |
| 13 | Spatie global roles + org-pivot role + Policies | Three overlapping authorization mechanisms | Keep Policies (working pattern) + org-pivot role as source of truth; stop introducing new Spatie *permission* checks (Section 4) | Low | Medium |
| 14 | `admin-modern.blade.php`, `tenant-modern.blade.php` | Hardcoded `dir="rtl"` | Locale-driven `dir`, matching `mobile-shell.blade.php` | High | Low |

---

## 3. Database Improvements

All additive — no destructive changes to existing tables, no data migration required for current tenants.

| New Table | Purpose | Key Columns | Relates To |
|---|---|---|---|
| `otp_codes` | Phone/email verification codes | `user_id` (nullable, pre-account), `channel` (sms/email), `destination`, `code_hash`, `expires_at`, `consumed_at`, `attempts` | `User` |
| `organization_templates` | Seed data per industry | `key` (hotel/university/hospital/retail/corporate/...), `name`, `default_departments` JSON, `default_channels` JSON, `default_roles` JSON, `default_workflows` JSON | Applied at `Organization` creation |
| `org_units` | Generic, org-defined hierarchy (additive alongside Brand/Location/Department) | `organization_id`, `parent_id` (self-ref), `unit_type` (free string: division/region/country/campus/faculty/building/floor/asset/field/platform/crew/...), `name`, `order` | `Organization`; optionally referenced by `Department`/`Location` via nullable `org_unit_id` FK later |
| `workflow_definitions` | Reusable approval workflow templates | `organization_id`, `module` (post/channel_join/ticket/...), `name`, `steps` JSON (`[{order, role, required}]`) | `Organization` |
| `workflow_instances` | A running approval on a specific record | `workflow_definition_id`, `workflowable_type/id` (morph), `status`, `current_step` | Polymorphic target (e.g. `Channel` join request) |
| `workflow_steps` | Per-step decision log | `workflow_instance_id`, `step_order`, `role_required`, `decided_by`, `decision`, `decided_at`, `comment` | `workflow_instances`, `User` |

Column-level additions (all nullable/defaulted, zero-downtime):
- `users.profile_level` (enum: basic/private/business, default `basic`) and `users.identity_verified_at` — formalizes the 3-tier identity without new tables (Section 5).
- `channels.settings` JSON — type-specific behavior (auto-join, visibility rules) without hardcoding in controllers.
- `channel_user.status` (pending/approved/rejected, default `approved` for backward compatibility with existing rows) — only enforced for `type=private` channels going forward.

**Migration strategy:** every new table ships as its own migration; every new column uses `->nullable()` or `->default()` so existing rows and existing code paths are unaffected until each feature is explicitly wired in. No `Schema::table(...)->change()` on existing columns.

---

## 4. Role & Permission Improvements

Do not introduce a fourth authorization system. Consolidate onto what already works:

- **Keep** `organization_memberships.role` as the source of truth for org-scoped roles (Super Admin, Organization Admin, Regional Admin\*, Branch Admin, Department Manager, HR, Employee, Guest — \*Regional Admin becomes meaningful once `org_units` exists, Section 3).
- **Keep** Laravel Policies as the enforcement layer (`$this->authorize()`, `can:` route middleware) — this is already the dominant, working pattern across Organization/Brand/Channel/Post/Ticket/SlaRule.
- **Keep** Spatie `HasRoles` only for the small set of *platform-global* roles (`super_admin`) where "no organization context" is meaningful — do not expand Spatie *permissions* usage, since Policies already cover ability checks and adding a second permission system would duplicate Section 1.4's existing overlap rather than fix it.
- **Public User** (guest, non-member) — already implicit (unauthenticated `GuestController` browsing); no new role row needed, just documented as the zero-membership state.

---

## 5. User Profile System

Maps directly onto **existing** `users` columns — this is a validation/UI layer, not a new schema:

| Tier | Data (already-present columns) | New | Usage |
|---|---|---|---|
| Basic | `first_name, last_name, email, mobile` | `otp_codes` verification, `profile_level='basic'` | Public channel join, offers, general info |
| Private | `dob, gender, nationality, location_id` (all present, currently unenforced) | `profile_level='private'`, validation rule requiring these fields before upgrade | Hotel guest registration, university access |
| Business | `job_title`, `organization_memberships` (role/employee_id/job_title/employment_type) | `profile_level='business'` | Staff/employee channels, HR, corporate communication |

**Verification:** OTP (Section 2/Phase 1) gates Basic. Private requires the extra fields to be present (form validation, not new storage). Business requires an active `organization_membership` — already enforced implicitly by membership creation.
**Upgrade path:** a user starts Basic (self-registration or QR scan), can add Private fields anytime via `SettingsController`, and becomes Business the moment an org admin creates an `OrganizationMembership` for them (already the mechanism today) — no separate "upgrade request" flow needed.
**Security:** `profile_level` is informational/UX (which fields to require/show), not a trust boundary by itself — actual access control stays on Policies + membership status, matching current practice.

---

## 6. Channel System

- **Public/Private** — already implemented (`Channel.type`, `GuestController` only ever queries `type=public`).
- **QR Join** — already implemented end-to-end (`QrScanController`, `QrCodeService`, `QrCode` polymorphic on `Channel`); the only gap is the *admin-facing* generate/preview/download UI (Section 2, item 10, Phase 2).
- **Invitations** — already implemented in `TenantChannelController` (member invite by email, role assignment).
- **Approval Workflow** — currently missing for join (present only for Posts/Comments). Close via the new `workflow_definitions`/`workflow_instances` engine (Section 3), with `channel_user.status` tracking the pending/approved state per member. This is the Phase 1 use case for the workflow engine.
- **Search Visibility / Join Requests** — `type=private` channels are already excluded from `GuestController`'s public queries; a join-request UI just needs to call the same endpoint `ChannelController::subscribe` now creates a `workflow_instance` instead of an immediate pivot attach when `type=private`.
- **Advanced types** (corporate/regional/branch/department/team/project/event/emergency/announcement/community/support) — deferred to Phase 2 as an additive extension of the existing `type` column + new `settings` JSON, not a new table.

---

## 7. Corporate Hierarchy

Reference shape: `Company → Region → Country → City → Branch → Department → Team → Employee`. Current shape: `Organization → Brand → Location → Department`, with `Location` already carrying `city`/`country`/`lat-long`/`timezone`.

Two-tier recommendation:
1. **Default hierarchy (hotel-like orgs, unchanged):** `Organization → Brand → Location(city/country) → Department`. No changes — this already works and existing hotel tenants keep using it as-is.
2. **Custom hierarchy (any org type):** the new `org_units` self-referential table (Section 3) lets an `organization_template` define arbitrary depth/naming — Company→Division→Region→Country→Branch→Department→Team for a corporate tenant, University→Campus→Faculty→Program for education, Hospital→Region→Building→Floor→Department for healthcare, Company→Asset→Field→Platform→Crew for oil & gas — all without a code change, since `unit_type` is just a string and nesting is via `parent_id`.

"Team" specifically can be satisfied by the existing `Group` model (Organization+Location scoped, user membership pivot) rather than inventing a new construct — `Group` already does what "Team" needs; it's simply been under-promoted in the UI.

**Result achieved:** a CEO-level `Post` targeting the top `org_unit`/`Organization`, combined with the existing `AudienceSegment`/`PostAudience` targeting-by-location/department/role machinery, already delivers "one message reaches every employee globally" — no new delivery mechanism required, only audience-segment rules that can target `org_units` once that table exists.

---

## 8. Communication System

- **Posts** — already has pinned (`pinned_until`), scheduled (`scheduled_for`), urgent (`is_emergency`, `priority`), announcements (`post_type`), full approval lifecycle, and delivery/read/acknowledge receipts (`PostReceipt`). This is materially ahead of the business reference already.
- **Comments** — already moderated (approve/reject).
- **Likes** — `Reaction` model exists.
- **File Sharing** — `Media` polymorphic model (disk/path/mime/size/metadata) already attached to `Post` and `Ticket`.
- **Notifications** — the real gap: `notifications` table + `UserPreference` toggles exist, but `app/Notifications/*` is empty and there's no multi-channel delivery. Recommendation: `NotificationService` façade over Laravel's notification system, writing to the existing `notifications` table for in-app and adding `Mail`/`Nexmo`(or similar SMS)/push channels as Laravel `Notification` classes — provider-adapter pattern so Slack/Teams/WhatsApp/webhooks can be added later without touching the core service (Phase 2, Section "Unified Notification Center").
- **Pinned/Scheduled/Urgent Posts** — already implemented; only the *workflow that approves* them should later ride the new generic Workflow Engine instead of the current bespoke `status` field, to converge with channel-join approvals (documented as a Phase 2+ migration, not done now, to avoid destabilizing the working Post pipeline).

---

## 9. Dashboard Improvements

| Audience | Current State | Improvement |
|---|---|---|
| System/Super Admin | `admin/dashboard.blade.php`, org CRUD, channel CRUD for any org | Fix hardcoded RTL (Phase 1); add org-template picker to org creation |
| Corporate/Organization Admin | `organizations/dashboard.blade.php`, settings tabs (General/Members/Brands/Locations/SLA) | Add an "Org Units" tab once Section 3's table ships; add a "Templates" indicator showing which template seeded the org |
| Branch/Location Manager | `tenant/dashboard.blade.php`, `tenant/channels/*` | No change needed structurally; benefits from channel-join-approval queue UI |
| HR / Department Manager | Implicit via `organization_memberships.role` | Add an approvals inbox view once the Workflow Engine ships (channel joins, later post approvals) |
| Employees | `feed/index.blade.php`, `dashboard/dashboard-modern.blade.php` | No change needed |
| Guests | `guest/home.blade.php`, `guest/organization-detail.blade.php` | No change needed structurally; benefits from RTL/dark-mode consistency work |

---

## 10. UI/UX Improvements

- Fix hardcoded `dir="rtl"` in `admin-modern.blade.php`/`tenant-modern.blade.php` (Phase 1 — see Section 14 roadmap).
- Extract the copy-pasted `.feed-tabs` styling (currently duplicated inline in `guest/home.blade.php`, `guest/organization-detail.blade.php`, `feed/index.blade.php`) into a shared Blade component — flagged, not required for Phase 1.
- Long-term: converge the legacy Bootstrap layouts onto the design-system ("-modern") stack; do not add new legacy-styled screens going forward.
- No `resources/lang/ar` files exist despite RTL branching — Arabic strings aren't actually translated; flagged for a future localization pass, out of scope for Phase 1.

---

## 11. API Improvements

There is currently no `routes/api.php` / token-guarded API surface — everything is session-based Blade. As the platform grows toward mobile-app parity (per the flowchart's "User Application / Mobile app" layer) and Teams/Slack-style integrations (Section on Unified Notification Center), a versioned `routes/api.php` with Sanctum token auth is the natural extension point — reusing the *same* Policies and service classes (`QrCodeService`, `ActivityLogService`, future `WorkflowService`/`NotificationService`) as thin `Api\*` controllers, not duplicating business logic. Not part of Phase 1; documented here as the intended future entry point so AI/mobile integrations (Section 13) have a clear target.

---

## 12. Performance Improvements

- **Indexes:** every new table in Section 3 should index its FK columns (`organization_id`, `parent_id`, `workflow_definition_id`) and any `status`/`type` columns used in scopes.
- **Caching:** `organization_templates` and `workflow_definitions` are read-heavy/write-rarely — good candidates for cache-on-read (`Cache::remember`) once the feature ships; not needed at current scale.
- **Queue:** OTP delivery (SMS/email) and future notification providers should be queued jobs (`jobs` table already exists), not synchronous — avoids blocking registration/guest requests.
- **N+1 audit:** `AudienceSegment::matchesUser()` and `Post::forAudience()` scopes should be checked for eager-loading once `org_units` targeting is added, to avoid per-post membership re-queries.

---

## 13. Security Improvements

- **OTP verification** closes the current "no verification" gap for Basic-tier identities (Section 2, item 1).
- **Audit logs** — already solid (`ActivityLogService`, ip/user_agent captured); add device fingerprint/geo as enrichment only.
- **Sensitive data** — `dob`/`nationality`/`gender` (Private tier) are already plain columns; no encryption-at-rest currently — flag for a future pass if the target industries (hospital/government) require it, out of scope for Phase 1.
- **Guest self-registration** (`QrScanController::guestRegisterStore`) currently creates throwaway accounts with random passwords and **no verification** — this is the primary security gap Phase 1's OTP work closes.

---

## 14. Laravel Best Practices & Roadmap

### Consolidation debt to track (not Phase 1, but load-bearing for everything else)
- Converge `/tenant`+`/organizations` and legacy `/organization/{organization}/dashboard` route surfaces.
- Extract remaining controller-embedded logic (`PostController`, `TicketController`) into services, following the existing `QrCodeService`/`ActivityLogService` pattern — this is also what Section "AI-Ready Architecture" below depends on.

### Roadmap

**Phase 1 (this pass):**
1. OTP verification (`otp_codes`, `OtpService`, wired into guest QR registration + Breeze registration).
2. Workflow Engine scaffolding (`workflow_definitions`/`workflow_instances`/`workflow_steps`) + first use case: private-channel join approval.
3. Organization Templates (`organization_templates`, `OrganizationTemplateService`, seeded with Hotel/University/Hospital/Retail/Corporate), hooked into org creation.
4. RTL/locale fix in `admin-modern.blade.php`/`tenant-modern.blade.php`.

**Phase 2 (documented, not built now):**
- `org_units` dynamic hierarchy + `AudienceSegment` targeting by org unit.
- Advanced channel types (`channels.settings` JSON).
- QR code management UI (generate/preview/download).
- Unified notification provider adapters (SMS/push/Slack/Teams/webhook).
- Migrate `Post` approval onto the Workflow Engine (after it's proven on channel joins).
- Controller → service extraction for the remaining modules.

**Phase 3+ (future):**
- Versioned `routes/api.php` (Sanctum) for mobile-app parity and third-party integrations.
- UI stack convergence (retire legacy Bootstrap layouts).
- Localization (`resources/lang/ar`) to back the existing RTL branching with real translations.
- Encryption-at-rest for Private-tier PII if healthcare/government tenants require it.

---

## 15. Multi-Industry Architecture

The core nouns are already generic (`Organization`, `Location`, `Department`; `Branch` is a deprecated alias only). The remaining hotel-specific surface is narrow:
- `users.is_vip`, `users.check_in_at`, `users.check_out_at` — hospitality-only fields living on the shared `User` table.
- Recommendation: leave them as nullable columns (removing them would break existing hotel tenants) but stop treating them as canonical fields for new industries — new industry-specific attributes should go into a future `custom_attributes` JSON column (or a template-defined field set) rather than adding more industry-specific columns to `users`/`organizations` over time.

---

## 16. Organization Templates

Covered in Sections 2, 3, 9, 14. Summary: `organization_templates` table + `OrganizationTemplateService::applyToOrganization()`, invoked from `OrganizationController@store`, seeding real `Department` and `Channel` rows (not a parallel structure) from the chosen template's JSON. Organizations remain free to diverge after creation — the template is a one-time seed, not an ongoing constraint.

---

## 17. Dynamic Organizational Hierarchy

Covered in Section 3 (`org_units` table) and Section 7 (mapping to reference shapes per industry). Existing `Brand`/`Location`/`Department` are not removed or restructured; `org_units` is an optional, additive layer for organizations whose template calls for a different hierarchy shape.

---

## 18. Enterprise Digital Identity

Largely already satisfied: `User` + `organization_memberships` (many-to-many, carrying per-org role/status/job_title/department) already provides one identity → many organizations → many roles → many profile levels, with no duplicate accounts required. The one addition: `users.identity_verified_at` (Section 3) to distinguish "this person's identity is verified" (OTP-level) from "this person's membership in org X is active" (already tracked on `organization_memberships.status`) — today those two concepts are conflated.

---

## 19. Advanced Channel Types

Covered in Section 6. `channels.type` extends from `public`/`private` to include corporate/regional/branch/department/team/project/event/emergency/announcement/community/support, with a `settings` JSON column carrying type-specific behavior (auto-join, visibility, escalation rules) so behavior stays configurable rather than hardcoded per type in controllers. Phase 2.

---

## 20. Configurable Workflow Engine

Covered in Sections 2, 3, 6, 14. `workflow_definitions` (org-scoped, JSON steps: role required + order) + `workflow_instances` (polymorphic target) + `workflow_steps` (decision log). First use case in Phase 1 is private-channel join approval; `Post` approval can migrate onto the same engine later once proven, without disrupting the currently-working Post pipeline.

---

## 21. Unified Notification Center

Covered in Sections 2, 8. `NotificationService` over Laravel's `Notification` class system, writing to the existing `notifications` table for in-app delivery and adding channel adapters (email — already available via Laravel Mail; SMS/push next; Slack/Teams/WhatsApp/webhook later) behind a common interface so new providers don't require touching existing notification-triggering code. Phase 2.

---

## 22. Enterprise Audit Trail

Already substantially implemented: `activity_logs` table + `ActivityLogService` + observers (`AuditableModelObserver`, `MembershipAuditObserver`, `LocationMembershipAuditObserver`, `ResourceAuditObserver`) capture actor, organization, polymorphic subject, JSON before/after diff, IP, and user agent. Recommendation: add device-type/geo enrichment fields only — this is not a gap requiring a new system, just minor extension.

---

## 23. AI-Ready Architecture

No AI is implemented now, by design. The path to being AI-ready is architectural discipline already partially present (`QrCodeService`, `ActivityLogService` show the right pattern): keep business logic in services, not controllers, so a future AI layer (recommendation engines, smart audience targeting, anomaly detection on `activity_logs`) has clean, testable entry points to call instead of scraping HTTP controllers. Concretely: extract the logic still embedded in `PostController` and `TicketController` into `PostService`/`TicketService` as part of Phase 2/3 consolidation debt (Section 14).

---

## 24. Future-Proofing Beyond the Reference Document

Beyond what the business reference and flowchart specify, the two structural risks most likely to limit scale if left unaddressed are:
1. **Dual routing surfaces** (`/tenant`+`/organizations` vs. legacy `/organization/{organization}/dashboard`) — every new feature currently risks being built twice or built on the wrong surface. Recommend freezing new development on the legacy surface immediately and converging over the next several releases.
2. **Dual UI stacks** (legacy Bootstrap vs. design-system) — same risk, applied to front-end consistency, RTL, and dark-mode correctness (the hardcoded-RTL bug in Section 2 is a direct symptom of this split).

Neither blocks Phase 1 — both are documented here so they're tracked rather than silently compounding as more industry-specific templates, channel types, and workflow use cases get layered on top of two parallel foundations.

---

## 25. Implementation Roadmap (Detail)

See Section 14 for the phase summary. Phase 1 is implemented immediately following this document, in this repository, as four additive, backward-compatible changes: OTP verification, Workflow Engine + channel-join-approval, Organization Templates, and the RTL/locale bug fix. Each ships as its own migration(s) and does not alter any existing table's existing columns.

---

## Change Log

| Date | Change |
|---|---|
| 2026-07-22 | Initial enterprise architecture review, gap analysis, and Phase 1 roadmap authored, grounded in full codebase exploration (database/models, routes/controllers/auth, UI/frontend). |
