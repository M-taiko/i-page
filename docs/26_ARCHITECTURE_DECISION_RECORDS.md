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
