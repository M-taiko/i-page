# Database Architecture

## Purpose

Define the complete data model, relationships, multi-tenant isolation strategy, audit requirements, and database design patterns for i-Page.

---

## Scope

- Core entity models (Organization, Branch, Department, Channel, User, Post, etc.)
- Multi-tenant data isolation and query filtering
- Pivot tables and many-to-many relationships
- Audit trail columns and soft deletes
- Indexing and performance optimization
- Database naming conventions

---

## Principles

1. **Multi-Tenant First**: Every query filters by organization_id or organization context
2. **Audit Trail Required**: created_by, updated_at, updated_by, deleted_at, deleted_by on all core tables
3. **No Business Logic in Database**: Use application-level validation and business rules
4. **Soft Deletes Everywhere**: Preserve data for compliance and audit trails
5. **Generic Terminology**: No industry-specific column names (no "hotel_id", use "organization_id")
6. **Polymorphic Where Needed**: QR codes, activity logs can target multiple entity types
7. **Normalized But Pragmatic**: Avoid over-normalization; trade-off for query simplicity

---

## Core Entity Model

### Organization (Tenant Root)

Represents a single tenant organization (hotel, hospital, school, retail store, etc.).

```
organizations
  id (PK)
  name (string, unique) — "City Central Hotel", "St. Mary's Hospital", "Lincoln High School"
  slug (string, unique) — auto-generated from name
  description (text, nullable)
  email (string, nullable)
  phone (string, nullable)
  address (string, nullable)
  city (string, nullable)
  country (string, nullable)
  logo_path (string, nullable) — white-label branding
  is_active (boolean, default true)
  max_channels (integer, default 4) — configurable channel limit per tenant
  qr_path (string, nullable) — organization-level QR code (legacy, may deprecate)
  default_channel_id (FK, nullable) — default landing channel
  created_by (FK → users.id, nullable)
  updated_by (FK → users.id, nullable)
  deleted_by (FK → users.id, nullable)
  created_at (timestamp)
  updated_at (timestamp)
  deleted_at (timestamp, nullable, soft delete)
```

**Indexes**:
- slug (unique)
- is_active
- created_at
- deleted_at

---

### Branch (Hierarchy Level 1)

Represents a physical or logical branch within an Organization (office location, department campus, etc.).

```
branches
  id (PK)
  organization_id (FK → organizations.id, not null)
  name (string)
  slug (string)
  description (text, nullable)
  address (string, nullable)
  city (string, nullable)
  country (string, nullable)
  phone (string, nullable)
  email (string, nullable)
  manager_id (FK → users.id, nullable)
  is_active (boolean, default true)
  created_by (FK → users.id, nullable)
  updated_by (FK → users.id, nullable)
  deleted_by (FK → users.id, nullable)
  created_at (timestamp)
  updated_at (timestamp)
  deleted_at (timestamp, nullable)
  
Foreign Keys:
  organization_id → organizations.id (cascade)
  manager_id → users.id (set null)

Indexes:
  organization_id
  slug (unique within organization via composite key)
  is_active
  deleted_at
```

---

### Department (Hierarchy Level 2)

Represents a functional department within a Branch (IT, HR, Sales, Nursing, etc.).

```
departments
  id (PK)
  organization_id (FK → organizations.id, not null)
  branch_id (FK → branches.id, nullable)
  name (string)
  slug (string)
  description (text, nullable)
  manager_id (FK → users.id, nullable)
  is_active (boolean, default true)
  created_by (FK → users.id, nullable)
  updated_by (FK → users.id, nullable)
  deleted_by (FK → users.id, nullable)
  created_at (timestamp)
  updated_at (timestamp)
  deleted_at (timestamp, nullable)

Foreign Keys:
  organization_id → organizations.id (cascade)
  branch_id → branches.id (set null, nullable since org-level departments exist)
  manager_id → users.id (set null)

Indexes:
  organization_id
  branch_id
  is_active
  deleted_at
```

---

### Channel (Communication Hub)

Represents a public or private communication channel within an Organization.

```
channels
  id (PK)
  organization_id (FK → organizations.id, not null)
  name (string)
  slug (string)
  description (text, nullable)
  type (enum: 'public', 'private', 'internal') — guests see only public; members see private/internal
  icon (string, nullable) — emoji or icon name
  parent_id (FK → channels.id, nullable) — hierarchical channels (subchannels)
  admin_user_id (FK → users.id, nullable) — channel admin
  is_featured (boolean, default false)
  is_archived (boolean, default false)
  member_count_cache (integer, default 0) — denormalized for performance
  post_count_cache (integer, default 0)
  view_count_cache (integer, default 0) — analytics tracking
  last_activity_at (timestamp, nullable)
  created_by (FK → users.id, nullable)
  updated_by (FK → users.id, nullable)
  deleted_by (FK → users.id, nullable)
  created_at (timestamp)
  updated_at (timestamp)
  deleted_at (timestamp, nullable)

Foreign Keys:
  organization_id → organizations.id (cascade)
  parent_id → channels.id (set null)
  admin_user_id → users.id (set null)

Indexes:
  organization_id
  slug (unique within organization)
  type
  is_archived
  deleted_at
  created_at
```

---

### User

Multi-tenant user model. A user can belong to multiple organizations with different roles per organization.

```
users
  id (PK)
  ipage_id (string, unique) — internal identifier (ORG-{random})
  first_name (string)
  last_name (string)
  email (string, unique)
  password (string, hashed)
  mobile (string, nullable)
  avatar_path (string, nullable)
  dob (date, nullable)
  gender (enum: 'M', 'F', 'Other', nullable)
  nationality (string, nullable)
  job_title (string, nullable)
  department (string, nullable) — legacy, should migrate to department_id
  branch_id (FK → branches.id, nullable) — legacy, use organization_user pivot instead
  is_vip (boolean, default false) — VIP member badge
  check_in_at (timestamp, nullable) — visitor check-in time
  check_out_at (timestamp, nullable) — visitor check-out time
  last_seen_at (timestamp, nullable) — last activity
  email_verified_at (timestamp, nullable)
  theme (string, default 'light') — dark/light mode preference
  language (string, default 'en') — user language preference
  is_active (boolean, default true)
  created_at (timestamp)
  updated_at (timestamp)
  deleted_at (timestamp, nullable, soft delete)

Indexes:
  email (unique)
  ipage_id (unique)
  is_active
  deleted_at
```

Note: Global roles (super_admin, guest) assigned via spatie/laravel-permission. Organization-specific roles assigned via organization_user pivot.

---

### Post (Content Item)

Represents a message, announcement, or content item posted to a channel.

```
posts
  id (PK)
  organization_id (FK → organizations.id, not null)
  channel_id (FK → channels.id, not null)
  author_id (FK → users.id, not null)
  title (string, nullable)
  body (text)
  image_path (string, nullable)
  status (enum: 'draft', 'published', 'archived', default 'draft')
  audience (enum: 'all', 'members', 'team', 'channel', default 'channel') — targeting
  published_at (timestamp, nullable)
  expires_at (timestamp, nullable) — auto-archive expired posts
  view_count (integer, default 0) — analytics
  like_count (integer, default 0)
  comment_count (integer, default 0)
  created_by (FK → users.id, nullable)
  updated_by (FK → users.id, nullable)
  deleted_by (FK → users.id, nullable)
  created_at (timestamp)
  updated_at (timestamp)
  deleted_at (timestamp, nullable)

Foreign Keys:
  organization_id → organizations.id (cascade)
  channel_id → channels.id (cascade)
  author_id → users.id (cascade)

Indexes:
  organization_id
  channel_id
  author_id
  status
  published_at
  created_at
  deleted_at
```

---

### QR Code

Trackable QR codes assigned to organizations, branches, departments, or channels.

```
qr_codes
  id (PK)
  organization_id (FK → organizations.id, not null)
  owner_type (string) — 'App\Models\Organization', 'App\Models\Channel', etc. (polymorphic)
  owner_id (integer) — polymorphic FK
  code (string, unique) — QR code unique identifier
  target_url (string) — full URL to scan endpoint
  qr_path (string, nullable) — file path to generated PNG
  is_active (boolean, default true)
  expires_at (timestamp, nullable) — auto-deactivate expired codes
  scan_count (integer, default 0) — analytics
  last_scanned_at (timestamp, nullable)
  metadata (json, nullable) — custom branding, labels, etc.
  created_by (FK → users.id, nullable)
  updated_by (FK → users.id, nullable)
  created_at (timestamp)
  updated_at (timestamp)

Indexes:
  organization_id
  code (unique)
  owner_type + owner_id
  is_active
  created_at
```

---

### QR Scan Log

Analytics table: every QR scan recorded for tracking and reporting.

```
qr_scan_logs
  id (PK)
  organization_id (FK → organizations.id, not null)
  qr_code_id (FK → qr_codes.id, nullable)
  user_id (FK → users.id, nullable) — null for guest scans
  ip_address (string, nullable)
  user_agent (string, nullable)
  location (string, nullable) — geo-location if available
  metadata (json, nullable) — device, referrer, etc.
  created_at (timestamp)

Indexes:
  organization_id
  qr_code_id
  user_id
  created_at
```

Retention: Keep indefinitely for audit; partition by created_at for performance.

---

### Notification

Push/in-app notifications for users.

```
notifications
  id (PK)
  user_id (FK → users.id, cascade delete)
  type (string) — 'channel_invite', 'post_reply', 'mention', etc.
  data (json) — notification payload (post_id, channel_id, actor, etc.)
  read_at (timestamp, nullable) — null = unread
  created_at (timestamp)

Indexes:
  user_id
  read_at
  created_at
```

---

### Pivot Tables

#### organization_user

```
organization_user
  id (PK)
  organization_id (FK → organizations.id, cascade)
  user_id (FK → users.id, cascade)
  role (string) — 'admin', 'staff', 'member', 'guest' (organization-scoped role)
  joined_at (timestamp, default now)
  timestamps()

Composite Unique: (organization_id, user_id)
Indexes: organization_id, user_id
```

#### channel_user

```
channel_user
  id (PK)
  channel_id (FK → channels.id, cascade)
  user_id (FK → users.id, cascade)
  role (string) — 'admin', 'moderator', 'member', 'viewer' (channel-scoped)
  joined_at (timestamp, default now)
  muted_at (timestamp, nullable) — muted until this time
  timestamps()

Composite Unique: (channel_id, user_id)
Indexes: channel_id, user_id, joined_at
```

#### group_user

```
group_user
  id (PK)
  group_id (FK → groups.id, cascade)
  user_id (FK → users.id, cascade)
  position (string, nullable) — 'moderator', 'member', etc.
  joined_at (timestamp)
  timestamps()

Composite Unique: (group_id, user_id)
```

---

## Multi-Tenant Isolation Strategy

### Query-Level Filtering

All queries **must** filter by organization context:

```php
// Repository pattern enforces this
$posts = Post::where('organization_id', $organizationId)->get();

// Relationships use implicit filtering
$org->posts()->get(); // automatically scoped to organization
```

### Middleware

- `CheckOrganizationAccess`: Sets `session['current_organization_id']`
- All `organization/{id}/dashboard/*` routes enforce middleware
- Unauthenticated guests see only public channels (no organization context needed)

### Row-Level Security (Future)

PostgreSQL RLS policies can enforce tenant isolation at database level (future enhancement).

---

## Audit Trail Implementation

### Columns on All Core Tables

Every table has:
- `created_by` (FK → users.id) — user who created the record
- `updated_by` (FK → users.id) — last user to modify
- `deleted_by` (FK → users.id) — user who soft-deleted
- `created_at`, `updated_at`, `deleted_at` (timestamps)

### Trait: Auditable

```php
// Applied to Organization, Channel, User, Post, Branch, Department
use App\Traits\Auditable;

// On model save, middleware/observer auto-fills created_by, updated_by
```

### Activity Logging (Future)

Optional: Add `activity_logs` table via `spatie/laravel-activitylog` to log detailed changes.

---

## Indexing Strategy

### Primary Indexes (Apply Everywhere)

1. **Foreign Keys**: organization_id, user_id, channel_id (for filtering and joins)
2. **Soft Deletes**: deleted_at (to exclude soft-deleted records in queries)
3. **Timestamps**: created_at (for sorting, pagination)
4. **Status Columns**: type, is_active, status (for filtering)

### Secondary Indexes (As Needed)

- Composite: (organization_id, created_at) for paginated lists
- Composite: (organization_id, is_active, deleted_at) for admin dashboards

---

## Standards

### Naming Conventions

- **Tables**: Plural, snake_case (organizations, channel_users)
- **Columns**: Singular, snake_case (user_id, is_active, created_by)
- **Foreign Keys**: {table_singular}_id (organization_id, user_id)
- **Pivot Tables**: {table1}_{table2} in alphabetical order (channel_user, group_user)

### Defaults

- Booleans default to `false` (is_active, is_featured)
- Enums should have a sensible default (status: 'draft', type: 'public')
- Timestamps are nullable only for audit columns (deleted_at, expires_at)

---

## Related Documents

- [[06_PERMISSION_MODEL.md]] — How roles and permissions relate to organization_user and channel_user
- [[27_FEATURE_SPECIFICATIONS.md]] — QR Platform, Analytics engine rely on this schema
- [[13_DEVELOPMENT_STANDARDS.md]] — Repository pattern enforces tenant isolation

---

# Claude Compliance Checklist

Before implementing any feature, verify:

- [ ] All core entities (Organization, Branch, Department, Channel, User, Post) implemented
- [ ] Multi-tenant isolation enforced at query level (repository pattern)
- [ ] Audit columns (created_by, updated_by, deleted_by) on all core tables
- [ ] Soft deletes on Organization, Channel, User, Post, Branch, Department
- [ ] Foreign keys and indexes defined per this document
- [ ] No business logic in migrations or schema
- [ ] Naming conventions follow snake_case for tables/columns
- [ ] Pivot tables use correct naming (channel_user, organization_user)
- [ ] Generic terminology used throughout (Organization, not Hotel; Channel, not Room)
- [ ] This implementation aligns with 00_PROJECT_CONSTITUTION.md and 40_PRODUCT_GUARDIAN.md
