# iPage — Multi-Vertical Communication & CRM SaaS Platform
### Full implementation prompt for Claude Code

---

## 1. Project overview

Build **iPage**, a multi-tenant SaaS communication platform. Any organization (hotel, supermarket, club, university, school) can subscribe as a **tenant**, get its own branded **channel(s)**, publish posts/updates, and let end users **follow** the organization and receive notifications. Each organization gets analytics (views, visits, engagement). The platform is generic at its core and extended per-vertical via a **pluggable modules** system.

Reference UI screenshots and design notes are attached separately (hotel vertical prototype) — use them as inspiration for information architecture, not as a rigid spec, since the UI must generalize across verticals.

---

## 2. Tech stack (mandatory)

- **Framework**: Laravel 11 (latest stable)
- **Auth & permissions**: `spatie/laravel-permission` with **teams enabled** (`team_foreign_key` = `organization_id`) so a single user can hold different roles in different organizations
- **Admin UI**: Filament 3 (preferred, for Super Admin and OrgAdmin panels) — if Filament is not desired, fall back to Blade + Livewire, but default to Filament unless told otherwise
- **Tables**: `yajra/laravel-datatables-oracle` for ALL listing screens with more than ~20 potential rows (posts, followers, feedback, staff, analytics logs) — server-side processing is mandatory, never load full collections client-side
- **Database**: MySQL or PostgreSQL (ask which the environment provides; default to MySQL)
- **Frontend for end users**: Flutter mobile app (separate repo/module — build the Laravel API layer first with this consumer in mind; expose clean REST/JSON endpoints, not just Blade views)
- **Queues**: Laravel queues (database driver is fine to start) for notification dispatch
- **Notifications**: Laravel Notifications (database + FCM push channel stub for Flutter)

---

## 3. Core data model (build these first, in this order)

Use UUIDs for `organizations`, incrementing IDs elsewhere unless stated.

### 3.1 `organizations`
| column | type | notes |
|---|---|---|
| id | uuid pk | |
| name | string | |
| org_type | enum | `hotel`, `supermarket`, `club`, `university`, `school` |
| logo_url | string, nullable | |
| brand_color | string, nullable | hex |
| enabled_modules | json, nullable | array of module keys, e.g. `["evacuation_plan","loyalty"]` |
| subscription_status | enum | `trial`, `active`, `suspended`, `cancelled` |
| timestamps | | |

### 3.2 `channels`
| column | type | notes |
|---|---|---|
| id | uuid pk | |
| organization_id | uuid fk → organizations | cascade delete |
| name | string | |
| visibility | enum | `public`, `business`, `private` |
| timestamps | | |

### 3.3 `posts`
| column | type | notes |
|---|---|---|
| id | uuid pk | |
| channel_id | uuid fk → channels | cascade delete |
| body | text, nullable | |
| image_url | string, nullable | |
| views_count | unsigned int | default 0 |
| timestamps | | |

### 3.4 `follows` (pivot)
| column | type | notes |
|---|---|---|
| user_id | fk → users | |
| organization_id | uuid fk → organizations | |
| created_at | | composite primary key (user_id, organization_id) |

### 3.5 `modules` (module registry)
| column | type | notes |
|---|---|---|
| key | string pk | e.g. `evacuation_plan` |
| label | string | |
| applicable_org_types | json | which org_type values can enable this |
| config_schema | json, nullable | for dynamic settings forms |

### 3.6 `feedback_submissions`
| column | type | notes |
|---|---|---|
| id | pk | |
| organization_id | uuid fk | |
| user_id | fk, nullable | nullable if anonymous |
| type | enum | `complaint`, `review` |
| department | string, nullable | |
| message | text | |
| rating | tinyint, nullable | 1–5, for reviews |
| is_urgent | boolean | default false |
| status | enum | `open`, `in_progress`, `resolved` |
| timestamps | | |

### 3.7 `analytics_events`
| column | type | notes |
|---|---|---|
| id | pk | |
| organization_id | uuid fk | |
| channel_id | uuid fk, nullable | |
| post_id | uuid fk, nullable | |
| event_type | enum | `view`, `visit`, `follow`, `unfollow` |
| user_id | fk, nullable | |
| created_at | | index on (organization_id, created_at) |

Add proper foreign key constraints, indexes on all foreign keys, and soft deletes on `organizations`, `channels`, `posts`.

---

## 4. Roles & permissions (Spatie, teams mode)

Enable teams in `config/permission.php`:
```php
'teams' => true,
'team_foreign_key' => 'organization_id',
```

### Roles
- `super-admin` — global, not team-scoped, full platform access
- `org-admin` — scoped per organization, manages that org fully
- `staff` — scoped per organization, limited (e.g. publish posts, respond to feedback, no billing/settings access)
- `end-user` — the mobile app consumer, not a panel role, just the default authenticated user

### Base permissions to seed
`manage organization settings`, `publish posts`, `delete posts`, `view analytics`, `manage staff`, `respond to feedback`, `manage modules`, `manage billing`

Write a `RolePermissionSeeder` that creates all roles/permissions above and is idempotent (safe to re-run).

**Critical rule**: every controller action and Filament resource that touches an organization-scoped model MUST verify both (a) the permission exists AND (b) the acting user's role is scoped to the *specific* organization_id being modified — never trust a route parameter alone. Write Policies (`OrganizationPolicy`, `PostPolicy`, `ChannelPolicy`, `FeedbackPolicy`) enforcing this, and add automated tests that assert a staff/org-admin from Organization A gets 403 when touching Organization B's resources.

---

## 5. Vertical modules system

Implement a simple registry + feature-flag pattern:

1. Seed the `modules` table with at least:
   - `evacuation_plan` (hotel, school, university)
   - `contact_management` (hotel, club, university, school)
   - `loyalty_program` (hotel, supermarket, club)
   - `weekly_offers` (supermarket)
   - `booking_schedule` (club)
   - `course_announcements` (university)
   - `parent_teacher_messaging` (school)
2. In the OrgAdmin panel, only show module toggles where `org_type` is in that module's `applicable_org_types`.
3. In the Flutter-facing API, expose `GET /api/organizations/{id}/enabled-modules` so the mobile app can conditionally render module-specific screens.
4. Keep each module's business logic in its own namespace (`app/Modules/EvacuationPlan/...`) so verticals can be added later without touching core code.

---

## 6. Admin panels (Filament)

### 6.1 Super Admin panel (`/admin`)
- Organizations CRUD (create tenant, set org_type, assign initial org-admin user, toggle subscription_status)
- Global modules registry management
- Cross-org analytics dashboard (total orgs, total followers platform-wide, growth chart)
- Users management (search across all users, impersonate for support)

### 6.2 OrgAdmin panel (`/org-admin`, tenant-scoped via team context middleware)
- Dashboard: followers count, post views, engagement trend (chart)
- Posts CRUD (per channel) with image upload
- Channels management (create/rename, toggle visibility)
- Staff management (invite staff, assign `staff` role scoped to this org only)
- Feedback inbox — **must use server-side DataTable** with filters for status/urgency/type
- Module settings (only modules applicable to this org_type)

For every listing table in both panels with potentially large row counts, implement it via Yajra DataTables with server-side processing — do not use Filament's built-in table for feedback/posts/followers if row counts could exceed a few thousand; use raw Blade + Yajra for those specifically, and document why in a code comment.

---

## 7. API layer for Flutter mobile app

Build a versioned REST API (`/api/v1/...`) using Laravel Sanctum for auth. Minimum endpoints:

- `POST /api/v1/auth/login`, `/register`, `/logout`
- `GET /api/v1/organizations/discover` — paginated, filterable by `org_type`, for the "explore/follow new organizations" screen
- `POST /api/v1/organizations/{id}/follow`, `DELETE /api/v1/organizations/{id}/follow`
- `GET /api/v1/feed` — posts from followed organizations, paginated, ordered by recency
- `GET /api/v1/organizations/{id}/channels`
- `GET /api/v1/channels/{id}/posts` — paginated
- `POST /api/v1/organizations/{id}/feedback` — submit complaint/review
- `GET /api/v1/organizations/{id}/enabled-modules`
- `GET /api/v1/notifications`, `POST /api/v1/notifications/{id}/read`

Return consistent JSON envelopes (`{ data, meta, links }` for paginated, `{ data }` otherwise) and use API Resources (`OrganizationResource`, `PostResource`, etc.) — never return raw Eloquent models.

---

## 8. Non-functional requirements

- Write feature tests (Pest or PHPUnit) for: role/permission scoping (cross-tenant isolation), post creation, follow/unfollow, feedback submission, module enable/disable.
- Add database indexes for every foreign key and for `analytics_events(organization_id, created_at)`.
- Use Laravel policies + form requests for all validation — no inline validation in controllers.
- Use queued jobs for notification dispatch (never send synchronously in the request cycle).
- Add a `.env.example` with all required keys.
- Add a `README.md` explaining setup, seeding, and the module system.

---

## 9. Suggested build order (do this incrementally, not all at once)

1. Laravel install + Spatie Permission (teams mode) + base migrations (§3)
2. Role/permission seeder + policies + cross-tenant isolation tests
3. Modules registry + seeder
4. Super Admin Filament panel (organizations CRUD, modules registry)
5. OrgAdmin Filament panel (dashboard, channels, posts, staff)
6. Feedback inbox with Yajra server-side DataTable
7. Analytics events tracking + dashboard charts
8. Public API layer (Sanctum) for Flutter consumption
9. Feature test suite pass
10. README + `.env.example` finalization

At the end of each numbered step, stop, summarize what was built, and confirm before proceeding to the next step.
