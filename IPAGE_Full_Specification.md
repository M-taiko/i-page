# IPAGE Hotel Communication Hub — Complete Technical Specification

> A comprehensive, framework-agnostic specification that documents every page, component, interaction, business rule, and reusable element of the current React + Vite + Tailwind + shadcn/ui prototype, plus a full rebuild guide for Laravel 12 (Blade + Bootstrap 5 + Vite + MySQL, MVC + Service + Repository, Spatie Permission, Yajra DataTables).

---

## 1. Project Overview

### 1.1 Purpose
IPAGE Hotel Communication Hub is an **internal hotel communication platform** for Hilton Jeddah (extendable to other Hilton branches). It centralizes:
- Guest-facing announcement channels (In-House Guests, Walk-in Guests).
- Internal team announcement channels (Team, Managers).
- News feed posts (property-wide announcements).
- User and staff (group) directory management.
- Channel creation with QR-code / deep-link distribution.
- Administrative dashboard with KPIs.

### 1.2 Business Goals
- Replace fragmented WhatsApp / email / bulletin-board communication with a single branded hub.
- Broadcast timely operational announcements (maintenance, F&B, VIP arrivals) to the correct audience segments.
- Give management a real-time overview of channels, active users, posts, and groups.
- Provide a repeatable channel-provisioning workflow (name → type → audience → logo → admin → QR).

### 1.3 Target Users
| Persona | Role | Primary Actions |
|---|---|---|
| Hotel Manager (Admin) | Full access | Create channels, manage users/groups, publish feeds, view dashboard, edit settings. |
| Department Manager | Publisher | Publish to their department channels, view team feed. |
| Team Member (Staff) | Reader | Read team channel + news feeds. |
| Guest (In-House / Walk-in) | Reader | Read guest channels via link/QR. |

### 1.4 Main Features
1. Authentication (Login / Forgot Password / Register — UI stubs).
2. Home — three side-by-side live channel feeds.
3. Create Channel — name, type, audience profile, logo upload, audience size, admin, QR code, sub-channels.
4. Users Management — table of guest users, filter, add / bulk-import / remove; embedded Channel List with search.
5. Group Management — staff directory table.
6. News Feeds — compose post (text + image + audience) and feed of published posts.
7. Dashboard Overview — six KPI stat cards.
8. Settings — Profile / Appearance / Notifications tabs.

---

## 2. Complete UI/UX Documentation

### 2.0 Global Layout
- **Shell**: `DashboardLayout` — fixed left sidebar (`w-52`, sidebar navy background) + `main` scrollable content (`p-6`).
- **Sidebar** (`AppSidebar`) contains logo + "IPAGE Menu" title + 7 nav items (Home, Create New Channel, Users, Group, New Feeds, Dashboard, Settings). Active item shows a left accent border (`sidebar-active` — 4-px yellow accent + darker background).
- **Header/Footer**: no dedicated global header or footer; each page owns its H1.
- **Auth pages** (Login) render without the shell (full-screen centered card).
- **Mobile / Responsive**: the sidebar is currently a fixed 208-px column. On viewports < md, the main area still scrolls but the sidebar remains fixed. Cards/grids use `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` where relevant. Channel feeds wrap via `flex-wrap justify-center gap-6`.

### 2.1 Login Page
- **Route**: `/`
- **File**: `src/pages/LoginPage.tsx`
- **Layout**: Full viewport (`min-h-screen`), flex-centered, `bg-background`.
- **Card**: `max-w-md`, rounded, shadow, padded `p-8`, fade-in animation.
- **Sections**:
  - Brand block: `Globe` icon (48 px, primary color) + title **"Welcome to IPAGE"** (3xl, extra-bold).
  - Form: two `Input`s (email, password, `h-12`) + primary `Button` "Login" (full-width, `h-12`).
  - Footer links: "Forgot Password?" and "Create New Account" (primary text-links).
- **Behavior**: Submitting the form navigates to `/dashboard` (no auth check — stub).
- **Responsive**: card scales down naturally; padding remains.

### 2.2 Home Page (Channel Feeds)
- **Route**: `/dashboard`
- **File**: `src/pages/HomePage.tsx`
- **Layout**: Centered H1 "Welcome to Hilton Jeddah Communication Hub" + `flex flex-wrap justify-center gap-6` of three `ChannelFeed` cards.
- **Channels rendered** (static data):
  1. **Jeddah Hilton Guest In-House** — 3 messages (maintenance notice, Italian buffet, pool closure).
  2. **Jeddah Hilton Team** — 4 messages (VIP arrival, employee of the month, elevator notice, policy).
  3. **Jeddah Hilton Walking Guest** — 3 messages (welcome, lobby lounge, city tour).
- **ChannelFeed card** structure:
  - Header bar: `bg-channel-header` (bright blue) with `Menu` icon, channel title (bold, white), `MoreVertical` icon.
  - Body: vertical stack of message bubbles (`bg-secondary/60`, rounded, `p-3`).
  - Each message row: text, then footer row with reactions (`ThumbsUp` accent-yellow, `Heart` destructive-red — both hover-scale) on the left and time-stamp (muted) on the right.
- **Interactions**: reaction icons are visual-only (hover scale, no state).
- **Responsive**: cards stack on narrow widths; each is `max-w-md`.

### 2.3 Create New Channel
- **Route**: `/dashboard/create-channel`
- **File**: `src/pages/CreateChannelPage.tsx`
- **Layout**: `max-w-2xl` centered container containing two stacked cards.
- **Card 1 — Create Form**:
  - H1 centered: "Create New IPAGE Channel".
  - Fields (top-to-bottom):
    1. **Channel Name** — text input, placeholder "Enter channel name".
    2. **Select Channel Type** — radio group: `public`, `private` (default `public`).
    3. **Select Audience Profile** — radio group: `business`, `public`, `private` (default `business`).
    4. **Upload Channel Logo** — file input (any type — no accept restriction in code).
    5. **Select Number of Channel Audience** — numeric input.
    6. **Enter Admin ID / Phone / Email** — text input.
  - Actions row: primary "Create Channel" (flex-1) + outline "Generate QR Code" with `QrCode` icon.
  - Footer helper text (info color): "After creating the channel, you will get a dedicated link and QR code for your channel."
- **Card 2 — Linked Sub Channels**:
  - H2 centered "Linked Sub Channels".
  - 2×2 grid of tiles ("Sub Channel 1..4") each with an icon (`Link2`, `Share2`, `Globe`, `Users`) and label. Hover changes background.
- **Interactions**: local state only — no persistence yet.

### 2.4 Users (User Management)
- **Route**: `/dashboard/users`
- **File**: `src/pages/UsersPage.tsx`
- **Header row**: H1 "User Management" on the left; outline **Filter** button (`Filter` icon) on the right — placed at top right per user preference.
- **Guest Users table** (primary-color header, hover rows):
  Columns: Name, IPAGE ID, Mobile Number, Email, Date of Birth, Gender, Nationality.
  Rows: 6 seeded records (John Doe, Jane Smith, Michael Brown, Emma Wilson, Chris Evans, Sophia Lee).
- **Action bar** (below table): primary "Add New User" (`UserPlus`), outline "Add New User by Excel Sheet" (`FileSpreadsheet`), destructive "Remove User" (`UserMinus`).
- **Channel List card** (below actions):
  - H2 "Channel List" with `List` icon.
  - Search input with `Search` prefix icon — filters channels client-side by name / type / status (case-insensitive).
  - Table (Channel Name, Type, Members, Status). Status badge: green pill if "Active", muted otherwise. Empty-state row spans 4 cols: "No channels found".
  - Seeded channels: Jeddah Hilton Team (Private / 150), Jeddah Hilton Guest in House (Public / 320), Jeddah Hilton Guests (Public / 580).

### 2.5 Groups (Group Management)
- **Route**: `/dashboard/groups`
- **File**: `src/pages/GroupsPage.tsx`
- **H1**: "Group Management".
- **Table** (primary header, hover rows). Columns: Name, IPAGE ID, Mobile, Email, Branch, Position, Gender, Nationality. Additional record fields present in the data (DOB, joining date) exist in the model but aren't currently rendered as columns.
- **Seeded rows**: 5 staff members (Omar Al-Faisal, Sara Al-Mutairi, James Anderson, Ali Hassan, Fatima Khan) across branches Jeddah / Riyadh / Medina / Dammam / Khobar.

### 2.6 News Feeds
- **Route**: `/dashboard/feeds`
- **File**: `src/pages/NewsFeedsPage.tsx`
- **H1**: "News Feeds — Hilton Jeddah".
- **Composer card**:
  - Label "Create a Post".
  - `Textarea` (min-h 80px) placeholder "Write your announcement...".
  - Row: file `Input` (image upload), native `select` (All / In-House / Team), primary `Button` "Post" with `ImageIcon`.
- **Feed list**: cards each showing published date (muted, small) and post text. Two seeded posts.

### 2.7 Dashboard Overview
- **Route**: `/dashboard/overview`
- **File**: `src/pages/DashboardOverviewPage.tsx`
- **Grid**: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` of 6 KPI cards.
- **KPIs** (static): Total Channels 4, Active Users 128, Posts Today 12, Groups 6, VIP Guests 3, Pending Notices 2.
- **Card style**: rounded-xl, shadow-md, white bg, label muted, value 3xl bold primary.

### 2.8 Settings
- **Route**: `/dashboard/settings`
- **File**: `src/pages/SettingsPage.tsx`
- **H1**: "Settings".
- **Tabs** (`Tabs` component): Profile / Appearance / Notifications (all with lucide icons).
- **Profile tab**:
  - Avatar (initials fallback) + "Change Photo" outline button with `Camera` icon.
  - 2-column grid of inputs: First Name, Last Name, Email, Phone Number, Job Title, Department.
  - "Save Profile" primary button with `Save` icon.
- **Appearance tab**:
  - Color Scheme select — `navy` (default) / `dark` / `light`.
  - Font Size select — `small` / `medium` (default) / `large`.
  - Language select — `english` / `arabic` / `french`.
  - Compact Mode toggle (Switch) with description.
  - "Save Appearance" primary button.
- **Notifications tab**:
  - **Delivery Channels** group: Email, Push, SMS toggles.
  - **Notification Types** group: New Guest Check-in, Channel Updates, System Alerts, Weekly Report.
  - "Save Notifications" primary button.

### 2.9 Not Found
- **Route**: `*`
- **File**: `src/pages/NotFound.tsx`. Standard 404 fallback.

### 2.10 Modals / Drawers / Accordions
- The shadcn/ui primitives are installed (Dialog, Sheet, Drawer, Accordion, Popover, HoverCard, ContextMenu, Menubar, NavigationMenu, Command/cmdk, Toast, Sonner, Tooltip, AlertDialog). **None are actively wired** in the current pages; they are available for future workflows (edit user modal, add-channel drawer, confirmation dialogs, etc.).

---

## 3. Functional Requirements

For each feature, the current implementation is UI-only. This section states the *expected* behavior for a full build.

### 3.1 Authentication
- User submits email + password → validate → issue session → redirect to `/dashboard`.
- Forgot Password → email a reset link → user resets password → redirect to Login.
- Create New Account → self-service registration (admin approval optional) → email verification.

### 3.2 Home / Channels
- Fetch channels the user has access to and their latest N (default 10) messages.
- Poll or subscribe (WebSocket / Pusher) for new messages.
- React (👍 / ❤️) toggles per message; counts persisted per user.
- Channel-header "More" menu: mute, mark all read, view members, leave.

### 3.3 Create Channel
- Submit form → create channel row → generate slug + shareable URL + QR image → email admin invite → return to Users → Channel List.
- Logo upload stored (see §14).
- Sub-channels are a many-to-many self-relation (see §5).

### 3.4 Users
- Filter opens a popover: filter by Gender, Nationality, Channel membership, Date range.
- Add New User → modal form matching column set → validation → insert.
- Add via Excel → upload `.xlsx`/`.csv` → parse → preview → confirm → bulk insert.
- Remove User → select rows (checkbox column) → confirmation dialog → soft delete.
- Channel List search filters client-side across name / type / status; server-side pagination once dataset > 50.

### 3.5 Groups
- CRUD for staff records. Assign staff to branches, positions, and groups.

### 3.6 News Feeds
- Compose: text + optional image + audience selector (All / In-House / Team / specific channel).
- Publish → append to feed, broadcast to selected audience channels, timestamp with locale.
- Feed shows most recent first; infinite scroll or pagination.

### 3.7 Dashboard
- KPIs computed from live DB counts.

### 3.8 Settings
- Persist profile, appearance, and notification preferences per user.

---

## 4. Business Logic

### 4.1 Rules
- A user must belong to at least one channel to appear in the Users table filter for that channel.
- Channels are one of `public` / `private`. Private channels require admin approval to join.
- Audience profile (`business` / `public` / `private`) controls default posting permissions:
  - `business` → hotel staff can post.
  - `public` → anyone with the link can post (moderated).
  - `private` → only channel admins can post.
- News Feed audience `All` fans out to every channel; `In-House` and `Team` are named audience groups.
- Employee of the Month is a special post type (tagged, pinned for 30 days).
- VIP guest notices auto-expire on guest checkout date.

### 4.2 Validations (recommended)
- Email: RFC 5322, unique per user table.
- Password: min 8, mix of letters + digits.
- Phone: E.164 (`+<country><subscriber>`).
- IPAGE ID: `^IP\d{6}$`.
- Channel Name: 3–60 chars, unique per property.
- Audience Count: positive integer, ≤ 100 000.
- File uploads: logos `.png/.jpg/.svg` ≤ 2 MB; post images ≤ 5 MB; Excel `.xlsx/.csv` ≤ 10 MB.

### 4.3 Calculations
- Dashboard KPIs:
  - Total Channels = `COUNT(channels)`.
  - Active Users = users with `last_seen_at >= NOW() - INTERVAL 7 DAY`.
  - Posts Today = `COUNT(posts WHERE DATE(created_at) = CURDATE())`.
  - Groups = `COUNT(groups)`.
  - VIP Guests = `COUNT(users WHERE is_vip AND check_out_at >= NOW())`.
  - Pending Notices = `COUNT(posts WHERE status='pending_approval')`.

### 4.4 Edge Cases
- Channel with 0 messages → show empty state, still list.
- User removed while composing a post → discard draft and toast.
- Excel import: duplicate emails skipped with per-row error report.
- Long messages: truncate at 500 chars with "Read more".
- Unicode / RTL: Arabic language toggles `dir="rtl"`.
- Deactivated user cannot log in but historical posts remain.

---

## 5. Database Design (MySQL)

All tables use `id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY`, `created_at`, `updated_at`, and `deleted_at` (soft delete) unless noted.

### 5.1 `users`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| ipage_id | VARCHAR(20) UNIQUE | Format `IP######` |
| first_name | VARCHAR(80) NOT NULL | |
| last_name | VARCHAR(80) NOT NULL | |
| email | VARCHAR(180) UNIQUE NOT NULL | |
| email_verified_at | TIMESTAMP NULL | |
| password | VARCHAR(255) NOT NULL | bcrypt |
| mobile | VARCHAR(24) | E.164 |
| dob | DATE NULL | |
| gender | ENUM('male','female','other') | |
| nationality | VARCHAR(80) | |
| job_title | VARCHAR(120) | |
| department | VARCHAR(120) | |
| branch_id | BIGINT UNSIGNED FK → branches.id | |
| avatar_path | VARCHAR(255) NULL | |
| is_vip | BOOLEAN DEFAULT 0 | |
| check_in_at | TIMESTAMP NULL | |
| check_out_at | TIMESTAMP NULL | |
| last_seen_at | TIMESTAMP NULL | |
| remember_token | VARCHAR(100) | |

Indexes: `email`, `ipage_id`, `branch_id`, `last_seen_at`.

### 5.2 `branches`
`id`, `name` UNIQUE, `city`, `country`, `timezone`.

### 5.3 `groups`
`id`, `name` UNIQUE, `description`, `branch_id` FK.

### 5.4 `group_user` (pivot)
`group_id` FK, `user_id` FK, `position` VARCHAR(120), `joined_at`. PK (group_id, user_id).

### 5.5 `channels`
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(120) NOT NULL | |
| slug | VARCHAR(140) UNIQUE | |
| type | ENUM('public','private') | |
| audience_profile | ENUM('business','public','private') | |
| audience_count | INT UNSIGNED NULL | |
| logo_path | VARCHAR(255) NULL | |
| admin_user_id | BIGINT UNSIGNED FK → users.id | |
| status | ENUM('active','archived') DEFAULT 'active' | |
| qr_path | VARCHAR(255) NULL | |
| share_url | VARCHAR(255) NULL | |

Indexes: `slug`, `admin_user_id`, `status`.

### 5.6 `channel_user` (pivot)
`channel_id`, `user_id`, `role` ENUM('member','moderator','admin') DEFAULT 'member', `joined_at`, `muted_at` NULL.

### 5.7 `channel_channel` (sub-channels self relation)
`parent_channel_id` FK → channels.id, `child_channel_id` FK → channels.id, PK both, UNIQUE.

### 5.8 `posts` (news feed + channel messages)
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| author_id | BIGINT UNSIGNED FK → users.id | |
| channel_id | BIGINT UNSIGNED NULL FK → channels.id | Null for global feed posts |
| audience | ENUM('all','in_house','team','channel') DEFAULT 'channel' | |
| body | TEXT NOT NULL | |
| image_path | VARCHAR(255) NULL | |
| status | ENUM('draft','pending_approval','published','archived') | |
| published_at | TIMESTAMP NULL | |
| pinned_until | TIMESTAMP NULL | |

Indexes: `channel_id`, `author_id`, `published_at`, `status`.

### 5.9 `reactions`
`id`, `post_id` FK, `user_id` FK, `type` ENUM('like','love'), UNIQUE(post_id,user_id,type).

### 5.10 `notifications`
`id`, `user_id` FK, `type`, `data` JSON, `read_at` NULL.

### 5.11 `user_preferences`
`user_id` FK PK, `color_scheme` ENUM('navy','dark','light') DEFAULT 'navy', `font_size` ENUM('small','medium','large') DEFAULT 'medium', `language` ENUM('en','ar','fr') DEFAULT 'en', `compact_mode` BOOL, `email_notifications` BOOL, `push_notifications` BOOL, `sms_notifications` BOOL, `notify_new_guest` BOOL, `notify_channel_updates` BOOL, `notify_system_alerts` BOOL, `notify_weekly_report` BOOL.

### 5.12 Spatie tables
`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` (standard package migrations).

---

## 6. Models

- **User** — `HasRoles`, `HasMany` posts, `BelongsToMany` channels via `channel_user`, `BelongsToMany` groups via `group_user`, `HasOne` preferences. Computed: `full_name`, `initials`.
- **Branch** — `HasMany` users, groups.
- **Group** — `BelongsToMany` users (with `position`), `BelongsTo` branch.
- **Channel** — `BelongsTo` admin (User), `BelongsToMany` users, `BelongsToMany` sub-channels (self via `channel_channel`), `HasMany` posts. Scopes: `active()`, `public()`, `private()`. Computed: `member_count`, `is_pinned`.
- **Post** — `BelongsTo` author, `BelongsTo` channel, `HasMany` reactions. Scopes: `published()`, `today()`, `forAudience($aud)`.
- **Reaction** — `BelongsTo` post, user.
- **UserPreference** — `BelongsTo` user.

Enums: `ChannelType`, `AudienceProfile`, `ChannelRole`, `PostStatus`, `PostAudience`, `Gender`, `ReactionType`, `ColorScheme`, `FontSize`, `Language`.

Statuses: Post {draft, pending_approval, published, archived}; Channel {active, archived}; User {active, deactivated}.

---

## 7. Authentication

- **Login**: email + password, `POST /login`, returns session.
- **Register**: `POST /register` — first/last name, email, password + confirmation, phone; sends verification email.
- **Forgot Password**: `POST /password/email` sends signed reset link; `POST /password/reset` accepts token + new password.
- **Logout**: `POST /logout`.
- **Email verification**: `GET /email/verify/{id}/{hash}` (signed URL).
- **Permissions** (Spatie): `channel.create`, `channel.update`, `channel.delete`, `channel.post`, `user.manage`, `group.manage`, `feed.publish`, `feed.moderate`, `dashboard.view`, `settings.manage`.
- **Roles**: `super_admin` (all), `hotel_manager` (all except role management), `department_manager` (`channel.post`, `feed.publish`, `user.manage:own_dept`), `staff` (read + react), `guest` (read guest channels).
- **Authorization rules**: Policies per model — `ChannelPolicy`, `PostPolicy`, `UserPolicy`, `GroupPolicy`. Gate `view-dashboard` requires `dashboard.view`.

---

## 8. API Documentation

All routes return JSON when `Accept: application/json`; otherwise render Blade views. Prefix authenticated API routes with `/api`.

### 8.1 Auth
| Method | URL | Params | Validation | Response |
|---|---|---|---|---|
| POST | /login | email, password | email required email, password required min:8 | 200 user + token / 422 |
| POST | /register | first_name, last_name, email, password, password_confirmation | all required, email unique, password confirmed min:8 | 201 user |
| POST | /password/email | email | required email exists:users | 200 status |
| POST | /password/reset | token, email, password, password_confirmation | required | 200 |
| POST | /logout | — | auth | 204 |

### 8.2 Channels
| Method | URL | Params | Notes |
|---|---|---|---|
| GET | /api/channels | q, type, status, page | List, filterable |
| POST | /api/channels | name, type, audience_profile, audience_count, admin_contact, logo (file) | Create |
| GET | /api/channels/{id} | — | Show + members |
| PUT | /api/channels/{id} | (same as create) | Update |
| DELETE | /api/channels/{id} | — | Soft delete |
| POST | /api/channels/{id}/qr | — | Regenerate QR |
| POST | /api/channels/{id}/members | user_ids[] | Attach |
| DELETE | /api/channels/{id}/members/{userId} | — | Detach |

### 8.3 Users
| Method | URL | Params |
|---|---|---|
| GET | /api/users | q, gender, nationality, channel_id, page |
| POST | /api/users | first_name, last_name, email, mobile, dob, gender, nationality, ipage_id |
| POST | /api/users/import | file (.xlsx/.csv) |
| PUT | /api/users/{id} | any user field |
| DELETE | /api/users/{id} | — |

### 8.4 Groups
`GET/POST/PUT/DELETE /api/groups`, `POST /api/groups/{id}/members`.

### 8.5 Posts / Feed
| Method | URL | Params |
|---|---|---|
| GET | /api/posts | channel_id, audience, page |
| POST | /api/posts | body, image (file), audience, channel_id |
| PUT | /api/posts/{id} | body, image, audience, status |
| DELETE | /api/posts/{id} | — |
| POST | /api/posts/{id}/reactions | type |
| DELETE | /api/posts/{id}/reactions/{type} | — |

### 8.6 Dashboard
`GET /api/dashboard/kpis` → totals object.

### 8.7 Settings
`GET/PUT /api/me/preferences`, `PUT /api/me/profile`, `POST /api/me/avatar`.

Standard responses: 200 OK, 201 Created, 204 No Content, 401 Unauthenticated, 403 Forbidden, 404, 422 Validation.

---

## 9. State Management

Frontend uses React local `useState` per page. TanStack Query (`@tanstack/react-query`) is set up (`QueryClient` in `App.tsx`) to be used for server data caching.
- **Loading states**: skeleton rows for tables, spinner on primary buttons.
- **Error states**: toast via `sonner` + inline error message under field.
- **Success states**: green toast, form reset, redirect where relevant.
- **Empty states**: "No channels found" row example already present.
- **Optimistic updates**: reactions and toggles.

---

## 10. Forms

| Form | Fields (required*) | Defaults | Validation |
|---|---|---|---|
| Login | email*, password* | — | see §8.1 |
| Register | first_name*, last_name*, email*, password*, confirm* | — | unique email, confirmed pw |
| Forgot Password | email* | — | exists |
| Create Channel | channel_name*, channel_type*, audience_profile*, logo, audience_count, admin_contact* | type=public, profile=business | name 3–60, count int>0, admin format email/phone |
| Add User | first, last, email*, mobile, dob, gender, nationality, ipage_id | gender=male | email unique, IP regex |
| Import Users | file* | — | mimetypes xlsx/csv, ≤10MB |
| Compose Post | body*, image, audience* | audience=All | body max 2000, image ≤5MB |
| Profile | first, last, email, phone, job_title, department | current values | email unique |
| Appearance | color_scheme, font_size, language, compact_mode | navy/medium/en/false | enum |
| Notifications | 7 booleans | true/true/false/true/true/true/false | boolean |

---

## 11. User Flows

1. **Login → Dashboard**: `/` → submit → `/dashboard` (home channels).
2. **Create Channel**: Sidebar → Create New Channel → fill form → Create → toast → redirect to Users → Channel List with new row → Generate QR downloads/shows image.
3. **Broadcast Announcement**: Sidebar → New Feeds → compose → select audience → Post → appears in feed and fan-out channels.
4. **Add Guest User (single)**: Sidebar → Users → Add New User → modal → save.
5. **Bulk Import**: Users → Add New User by Excel Sheet → upload → preview modal → confirm → progress toast.
6. **Remove Users**: Users → select checkboxes → Remove User → confirm dialog.
7. **Manage Staff**: Sidebar → Group → view directory → row actions edit/delete.
8. **Adjust Settings**: Sidebar → Settings → tab → change → Save.
9. **Filter Users**: Users → Filter (top-right) → popover → apply → table refetches.
10. **React to Message**: Home → click 👍/❤️ on a message.

---

## 12. Dashboard

Six KPI cards (see §4.3 formulas). Future widgets: line chart of posts/day (Recharts), donut of active users by branch, top channels table, recent VIP arrivals list.

---

## 13. Notifications

- **Toasts**: `sonner` for success/error; shadcn `toaster` for actionable.
- **Emails**: verification, password reset, channel invite (admin), weekly report.
- **In-app alerts**: system alerts card on dashboard.
- **Validation messages**: inline under each form input.

---

## 14. File Upload

- Channel logo: PNG/JPG/SVG ≤ 2 MB, stored in `storage/app/public/channels/logos/{id}.ext`.
- Post image: JPG/PNG/WebP ≤ 5 MB, `storage/app/public/posts/{id}.ext`.
- User avatar: JPG/PNG ≤ 1 MB, `storage/app/public/avatars/{id}.ext`.
- Excel import: XLSX/CSV ≤ 10 MB, streamed via Laravel Excel; temp path deleted on completion.
- All served via `php artisan storage:link` → `/storage/...`.

---

## 15. Security

- **Authentication**: Laravel Breeze / Sanctum (session for web, personal-access tokens for API).
- **Authorization**: Spatie Permission + Policies.
- **Validation**: Form Request classes per action.
- **CSRF**: Laravel middleware for all non-GET Blade forms; `@csrf`.
- **XSS**: Blade `{{ }}` auto-escape; never use `{!! !!}` on user input; sanitize post body with HTMLPurifier.
- **Rate Limiting**: `throttle:60,1` on API, `throttle:5,1` on login/reset.
- **SQL Injection**: Eloquent parameter binding only.
- **File uploads**: MIME whitelist + size limits + random names.
- **HTTPS** enforced in production; secure + httpOnly + sameSite=lax cookies.
- **Password hashing**: bcrypt (cost 12).

---

## 16. Performance

- **Lazy Loading**: route-level code splitting via `React.lazy` (recommended) — currently eager. In Laravel, split Blade partials + Vite chunks; defer JS with `@vite`.
- **Pagination**: 25 rows per page on all tables (Yajra DataTables server-side).
- **Caching**: `cache()->remember` for dashboard KPIs (60 s), channel list (30 s), user preferences (per request).
- **Eager loading**: `Channel::with('admin','members:id')`, `Post::with('author','reactions')`.
- **Assets**: Vite build, gzip/brotli, long-cache hashed filenames.
- **Images**: `loading="lazy"` on `<img>`, responsive `srcset`.
- **Queries**: indexes on FKs and `published_at`, `status`, `last_seen_at`.

---

## 17. Responsive Design

- Breakpoints (Tailwind defaults, mirror in Bootstrap): sm ≥640, md ≥768, lg ≥1024, xl ≥1280.
- **Desktop (≥lg)**: Sidebar visible, 3-column channel grid, 3-column KPI grid, 2-column form grids.
- **Tablet (md)**: 2-column grids, sidebar remains fixed (208 px).
- **Mobile (<md)**: Recommended enhancement — collapse sidebar into a top hamburger drawer (`Sheet`), stack all grids to 1 column, tables become horizontally scrollable (`overflow-x-auto`).

---

## 18. Theme

### 18.1 Design Tokens (HSL, from `src/index.css`)
- Background `210 20% 95%`, Foreground `213 30% 15%`.
- Card `#fff`. Popover `#fff`.
- **Primary** (Corporate Navy) `213 80% 25%` — used for buttons, headings, table headers.
- Secondary `210 40% 96%`, Muted `210 20% 92%`.
- **Accent** (Hilton Yellow) `50 100% 50%`.
- Destructive `0 84% 60%`, Success `142 70% 45%`, Info `207 90% 55%`.
- Border/Input `210 20% 88%`, Ring = primary.
- Radius `0.5rem`.
- Sidebar: bg `213 80% 30%`, fg white, active accent yellow.
- Channel header: `207 90% 50%` (bright blue), white text.

### 18.2 Typography
- Font: **Inter** (Google Fonts, weights 400–800).
- H1 `text-xl font-bold`, section H2 `text-lg font-bold text-primary`, body `text-sm`, muted `text-xs text-muted-foreground`.

### 18.3 Spacing / Elevation
- Cards: `rounded-xl`, `shadow-md`, padding `p-6` (or `p-8` for hero forms).
- Standard gap `gap-4`/`gap-6`, page padding `p-6`.

### 18.4 Icons
- `lucide-react` throughout (Home, PlusCircle, Users, UsersRound, Newspaper, LayoutDashboard, Settings, Filter, Search, List, UserPlus, UserMinus, FileSpreadsheet, QrCode, Link2, Share2, Globe, ImageIcon, ThumbsUp, Heart, Menu, MoreVertical, User, Palette, Bell, Save, Camera).

### 18.5 Animations
- `animate-fade-in` on page mounts.
- Hover scale on reaction icons.
- `tailwindcss-animate` for shadcn primitives (dialog, dropdown).

### 18.6 Dark / Light Mode
- Tokens are ready for a dark palette; `next-themes` installed but not wired. Add `.dark` variables in `index.css` and a toggle in Settings → Appearance (already scaffolded).

---

## 19. Component Library (reusable)

- `ChannelFeed({ title, messages })` — channel card with header + message list + reactions.
- `AppSidebar` — vertical nav with active state.
- `DashboardLayout` — sidebar + `<Outlet />` shell.
- `NavLink` — thin wrapper around React Router NavLink with `activeClassName` compat.
- shadcn/ui primitives in `src/components/ui/`: `button, input, label, textarea, select, tabs, table, switch, avatar, dialog, sheet, drawer, dropdown-menu, popover, tooltip, toast, sonner, alert-dialog, accordion, checkbox, radio-group, slider, progress, badge, card, calendar, command, context-menu, form, hover-card, input-otp, menubar, navigation-menu, pagination, resizable, scroll-area, separator, skeleton, toggle, toggle-group, aspect-ratio, breadcrumb, carousel, chart, collapsible, sidebar`.

---

## 20. Folder Structure (current)

```
src/
  App.tsx                  # QueryClient + Router + Routes
  main.tsx                 # React root
  index.css                # Design tokens + Tailwind layers
  assets/logo.png
  components/
    AppSidebar.tsx
    DashboardLayout.tsx
    ChannelFeed.tsx
    NavLink.tsx
    ui/                    # shadcn primitives
  pages/
    LoginPage.tsx
    HomePage.tsx
    CreateChannelPage.tsx
    UsersPage.tsx
    GroupsPage.tsx
    NewsFeedsPage.tsx
    DashboardOverviewPage.tsx
    SettingsPage.tsx
    NotFound.tsx
    Index.tsx              # (legacy landing, unused)
  hooks/
    use-mobile.tsx
    use-toast.ts
  lib/utils.ts             # cn() helper
  test/                    # vitest setup + example
public/
  favicon.png, placeholder.svg, robots.txt
index.html                 # <title>IPAGE Hotel Communication Hub</title>
tailwind.config.ts, postcss.config.js
tsconfig*.json, vite.config.ts, vitest.config.ts, eslint.config.js
```

---

## 21. External Libraries (why)

- **react 18 + react-dom** — UI runtime.
- **vite + @vitejs/plugin-react-swc** — dev server + fast builds.
- **typescript** — types.
- **tailwindcss + tailwindcss-animate + @tailwindcss/typography** — styling.
- **@radix-ui/*** — accessible primitives (used by shadcn).
- **class-variance-authority + clsx + tailwind-merge** — variant/utility helpers.
- **lucide-react** — icons.
- **react-router-dom** — routing.
- **@tanstack/react-query** — server-state cache.
- **react-hook-form + @hookform/resolvers + zod** — forms + validation.
- **sonner** + shadcn **toast** — notifications.
- **date-fns** — date formatting.
- **recharts** — charts (future dashboard).
- **embla-carousel-react** — carousels (available).
- **cmdk** — command palette.
- **input-otp** — OTP field.
- **next-themes** — dark/light toggle.
- **react-day-picker** — date pickers.
- **react-resizable-panels** — split layouts.
- **vaul** — drawer.
- **vitest + testing-library + jsdom** — tests.
- **eslint + typescript-eslint** — linting.

---

## 22. Environment Variables

Frontend (Vite): none required today (fully static). For a full stack:
- `VITE_API_BASE_URL` — backend base URL.

Backend (Laravel):
- `APP_NAME=IPAGE`, `APP_ENV`, `APP_KEY`, `APP_URL`.
- `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`.
- `FILESYSTEM_DISK=public`.
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`.
- `REDIS_HOST`, `REDIS_PORT`.
- `BROADCAST_DRIVER=pusher`, `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`.
- `SANCTUM_STATEFUL_DOMAINS`.

---

## 23. Deployment

- **Frontend (current)**: `npm run build` → static `dist/` → any static host (Vercel, Netlify, S3+CloudFront, or Lovable Publish).
- **Full-stack Laravel**:
  - Server: PHP 8.3, MySQL 8, Redis, Nginx, Node 20.
  - Steps: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`, restart PHP-FPM, run `php artisan queue:work` as a systemd service, schedule `php artisan schedule:run` in cron.
  - HTTPS via Let's Encrypt. Log rotation via `logrotate`. Backups: nightly `mysqldump` + storage snapshot.

---

## 24. Rebuild Instructions — Laravel 12 (Blade + Bootstrap 5 + Vite + MySQL)

### 24.1 Stack Decisions
- **Framework**: Laravel 12.
- **Views**: Blade + Bootstrap 5.3 (SCSS) + Bootstrap Icons.
- **Bundling**: Vite via `laravel/vite-plugin`.
- **DB**: MySQL 8.
- **Architecture**: MVC + Service Layer + Repository Pattern + Form Requests + Eloquent + Policies.
- **Auth**: Laravel Breeze (Blade stack).
- **RBAC**: `spatie/laravel-permission`.
- **Tables**: `yajra/laravel-datatables` (server-side).
- **QR**: `simplesoftwareio/simple-qrcode`.
- **Excel import**: `maatwebsite/excel`.

### 24.2 Scaffold
```bash
composer create-project laravel/laravel ipage
cd ipage
composer require spatie/laravel-permission yajra/laravel-datatables:^11 maatwebsite/excel simplesoftwareio/simple-qrcode intervention/image
composer require --dev laravel/breeze
php artisan breeze:install blade
npm install bootstrap@5.3 @popperjs/core bootstrap-icons sass datatables.net-bs5 datatables.net-buttons-bs5
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Yajra\DataTables\DataTablesServiceProvider"
php artisan migrate
```

### 24.3 Directory Layout
```
app/
  Enums/                        # ChannelType, AudienceProfile, PostStatus, ...
  Models/                       # User, Channel, Post, Group, Branch, Reaction, UserPreference
  Http/
    Controllers/
      Auth/*                    # Breeze
      DashboardController.php
      HomeController.php
      ChannelController.php
      UserController.php
      GroupController.php
      PostController.php
      SettingsController.php
    Requests/
      Channel/{StoreChannelRequest,UpdateChannelRequest}.php
      User/{StoreUserRequest,UpdateUserRequest,ImportUsersRequest}.php
      Post/{StorePostRequest,UpdatePostRequest}.php
      Settings/{UpdateProfileRequest,UpdatePreferencesRequest}.php
    Middleware/
  Repositories/
    Contracts/{ChannelRepositoryInterface, UserRepositoryInterface, PostRepositoryInterface, GroupRepositoryInterface}.php
    Eloquent/{ChannelRepository, UserRepository, PostRepository, GroupRepository}.php
  Services/
    ChannelService.php
    UserService.php
    PostService.php
    DashboardService.php
    QrCodeService.php
    UserImportService.php
  Policies/
  Providers/RepositoryServiceProvider.php
  DataTables/
    UsersDataTable.php
    ChannelsDataTable.php
    GroupsDataTable.php
resources/
  views/
    layouts/{app.blade.php, auth.blade.php, sidebar.blade.php}
    auth/{login, register, forgot-password, reset-password}.blade.php
    home/index.blade.php
    channels/{create, index, show}.blade.php
    users/{index, partials/filter, partials/import-modal}.blade.php
    groups/index.blade.php
    feeds/index.blade.php
    dashboard/index.blade.php
    settings/{index, partials/profile, partials/appearance, partials/notifications}.blade.php
    components/channel-feed.blade.php
  sass/app.scss                 # Bootstrap overrides + tokens
  js/app.js                     # Bootstrap + DataTables bootstrap
routes/{web.php, api.php}
database/
  migrations/                   # tables from §5
  seeders/{RolesSeeder, DemoDataSeeder}.php
tests/{Feature, Unit}/
```

### 24.4 Bootstrap Theme Tokens (SCSS overrides)
Match §18 palette:
```scss
$primary:   #0b3d7a;   // HSL(213,80%,25%)
$secondary: #eef2f7;
$info:      #1e90ff;
$success:   #22c55e;
$danger:    #ef4444;
$warning:   #ffd700;   // accent yellow
$body-bg:   #eef1f5;
$body-color:#1e2a3a;
$border-radius: .5rem;
$font-family-sans-serif: "Inter", system-ui, sans-serif;
@import "bootstrap/scss/bootstrap";
```
Add `.sidebar { background:#134a95; color:#fff; }` with `.nav-link.active { border-left:4px solid $warning; background:rgba(0,0,0,.2); }`.

### 24.5 Routes (`routes/web.php`)
```php
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class,'showLogin'])->name('login');
    // Breeze routes
});

Route::middleware(['auth','verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [HomeController::class,'index'])->name('home');
    Route::get('create-channel', [ChannelController::class,'create'])->name('channels.create');
    Route::resource('channels', ChannelController::class)->except(['create']);
    Route::resource('users', UserController::class);
    Route::post('users/import', [UserController::class,'import'])->name('users.import');
    Route::resource('groups', GroupController::class);
    Route::resource('feeds', PostController::class);
    Route::get('overview', [DashboardController::class,'index'])->name('overview');
    Route::get('settings', [SettingsController::class,'edit'])->name('settings');
    Route::put('settings/profile',      [SettingsController::class,'updateProfile']);
    Route::put('settings/appearance',   [SettingsController::class,'updateAppearance']);
    Route::put('settings/notifications',[SettingsController::class,'updateNotifications']);
});
```

### 24.6 Repository Pattern (example)
```php
interface ChannelRepositoryInterface {
    public function paginate(array $filters, int $perPage = 25);
    public function create(array $data): Channel;
    public function update(Channel $c, array $data): Channel;
    public function delete(Channel $c): void;
}

class ChannelRepository implements ChannelRepositoryInterface {
    public function paginate(array $filters, int $perPage = 25) {
        return Channel::query()
            ->when($filters['q'] ?? null, fn($q,$v)=>$q->where('name','like',"%$v%"))
            ->when($filters['type'] ?? null, fn($q,$v)=>$q->where('type',$v))
            ->when($filters['status'] ?? null, fn($q,$v)=>$q->where('status',$v))
            ->with('admin')
            ->latest()->paginate($perPage);
    }
    // ...
}
```
Bind in `RepositoryServiceProvider`.

### 24.7 Service Layer (example)
```php
class ChannelService {
    public function __construct(
        private ChannelRepositoryInterface $repo,
        private QrCodeService $qr,
    ) {}

    public function createFromRequest(StoreChannelRequest $req): Channel {
        $data = $req->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(6);
        if ($req->hasFile('logo')) {
            $data['logo_path'] = $req->file('logo')->store('channels/logos','public');
        }
        $channel = $this->repo->create($data);
        $channel->qr_path = $this->qr->generateFor($channel);
        $channel->share_url = route('channels.show', $channel);
        $channel->save();
        return $channel;
    }
}
```

### 24.8 Form Requests (example)
```php
class StoreChannelRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->can('channel.create'); }
    public function rules(): array {
        return [
            'name'             => ['required','string','min:3','max:60','unique:channels,name'],
            'type'             => ['required', Rule::in(['public','private'])],
            'audience_profile' => ['required', Rule::in(['business','public','private'])],
            'audience_count'   => ['nullable','integer','min:1','max:100000'],
            'admin_contact'    => ['required','string','max:180'],
            'logo'             => ['nullable','image','mimes:png,jpg,jpeg,svg','max:2048'],
        ];
    }
}
```

### 24.9 Yajra DataTables (Users example)
```php
class UsersDataTable extends DataTable {
    public function dataTable($query) {
        return datatables()->eloquent($query)
            ->addColumn('actions', fn($u)=>view('users.partials.actions', compact('u'))->render())
            ->rawColumns(['actions']);
    }
    public function query(User $model) {
        return $model->newQuery()->select(['id','ipage_id','first_name','last_name','email','mobile','dob','gender','nationality']);
    }
    protected function getColumns() { /* Name, IPAGE ID, Mobile, Email, DOB, Gender, Nationality, Actions */ }
}
```
Controller: `return $dataTable->render('users.index');`. Blade includes DataTables Bootstrap 5 CSS/JS via Vite.

### 24.10 Spatie Roles Seed
```php
foreach (['channel.create','channel.update','channel.delete','channel.post',
          'user.manage','group.manage','feed.publish','feed.moderate',
          'dashboard.view','settings.manage'] as $p) Permission::firstOrCreate(['name'=>$p]);

$admin = Role::firstOrCreate(['name'=>'hotel_manager']);
$admin->syncPermissions(Permission::all());
Role::firstOrCreate(['name'=>'department_manager'])->syncPermissions(['channel.post','feed.publish','user.manage','dashboard.view']);
Role::firstOrCreate(['name'=>'staff']);
Role::firstOrCreate(['name'=>'guest']);
```

### 24.11 Blade Layout Skeleton
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ config('app.name') }} – @yield('title')</title>
  @vite(['resources/sass/app.scss','resources/js/app.js'])
</head>
<body>
  <div class="d-flex min-vh-100">
    @include('layouts.sidebar')
    <main class="flex-grow-1 p-4 overflow-auto">@yield('content')</main>
  </div>
</body></html>
```
Sidebar mirrors §2.0 items via `<a class="nav-link {{ request()->routeIs('dashboard.home') ? 'active' : '' }}">`.

### 24.12 Home (channels) Blade
Loop three `<x-channel-feed :title=".." :messages="[..]"/>` components in a Bootstrap `row g-4`. The `channel-feed` component renders a card with a colored header, message list, and reaction buttons.

### 24.13 Testing
- Feature tests per controller (auth, CRUD, policies).
- Unit tests for Services (channel creation, QR generation, KPI calculation).

### 24.14 Deliverables Checklist (rebuild)
- [ ] All 8 routes with matching Blade views listed in §2.
- [ ] Sidebar with 7 items and active state.
- [ ] Bootstrap theme matching §18.
- [ ] Migrations for all tables in §5 with FKs & indexes.
- [ ] Models with relationships and enums per §6.
- [ ] Auth flows per §7 (Breeze).
- [ ] Spatie roles/permissions seeded.
- [ ] Form Requests for every mutating endpoint.
- [ ] Repositories + Services wired via ServiceProvider.
- [ ] Yajra DataTables for Users, Channels, Groups, Posts.
- [ ] QR generation on channel create.
- [ ] Excel import for users.
- [ ] Dashboard KPI service + cached endpoint.
- [ ] Settings CRUD for profile / appearance / notifications.
- [ ] File upload validation and storage links.
- [ ] Tests + CI.
- [ ] Deployment script per §23.

---

*End of specification.*
