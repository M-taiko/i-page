# Feature Specifications

## Purpose

Define complete specifications for each feature **before** implementation. This document is the master list of all planned features with their requirements, business rules, and acceptance criteria.

**Process**: For each new feature, add an entry to this document and update status as implementation progresses.

---

## Status Legend

- **Planned**: Queued for implementation
- **In Design**: Specification being finalized
- **In Development**: Code underway
- **Testing**: QA phase
- **Released**: Live in production
- **Deprecated**: No longer supported

---

---

## CORE FEATURES (Planned / In Development)

---

### Feature: Multi-Tenant Organization Management

**Status**: In Development

**Purpose**: Allow super admins to create and manage multiple organizations (tenants), each with independent data, settings, and users.

**Business Rules**:
- Super admin creates organization + assigns max_channels limit
- Organization owner (admin user) invited via email
- One user can belong to multiple organizations with different roles
- Organization admin can invite other admins and staff
- Organization can be deactivated (soft delete) without losing data

**Database Dependencies**:
- organizations table (with is_active, max_channels)
- organization_user pivot (with role: admin, staff, member, guest)
- Users with organization_id filtering in all queries

**API/Routes**:
- GET /admin/organizations — list all (super admin only)
- POST /admin/organizations — create org + owner user (super admin only)
- GET /admin/organizations/{id} — show org details + stats
- PUT /admin/organizations/{id} — update settings (max_channels, is_active)
- DELETE /admin/organizations/{id} — soft delete
- GET /organization/{id}/dashboard — tenant dashboard (members of org)

**Permissions Required**:
- super_admin: full CRUD
- admin (in org): view own org, update settings
- member: view own org (read-only)
- guest: none

**UI Components**:
- Organization list table (name, channels, users, posts, status, actions)
- Organization detail card (info, location, contact)
- Create organization form (name, max_channels, owner email/password)
- Edit organization form (settings, branding, channel limit)

**Acceptance Criteria**:
- [ ] Super admin can list all organizations
- [ ] Super admin can create organization + auto-create owner user with admin role
- [ ] Organization admin can view/edit own organization settings
- [ ] max_channels limit enforced (admin cannot create more than limit)
- [ ] Organization soft delete preserves all child data (channels, posts, users)
- [ ] Non-members get 403 when accessing org dashboard
- [ ] User dashboard redirects to first org (if multiple)

**Analytics Tracked**:
- organization_created_at
- organizations_per_user (count)
- avg_users_per_organization
- channels_per_organization (distribution)

---

### Feature: QR Code Platform

**Status**: Planned

**Purpose**: Generate trackable, branded QR codes for organizations, channels, and events. Each QR points to a guest registration or channel access flow.

**Business Rules**:
- Super admin generates organization-level QR
- Organization admin generates channel-specific QRs
- Each QR has unique code + tracking
- QR expires after date (optional)
- Guest scans QR → registers + auto-joins channel
- QR scan logged for analytics (IP, user agent, location if available)
- QR can be white-labeled with organization logo/colors

**Database Dependencies**:
- qr_codes table (code, owner_type, owner_id, target_url, is_active, expires_at)
- qr_scan_logs table (qr_code_id, user_id, ip_address, user_agent, created_at)

**API/Routes**:
- GET /api/qr/{code}/generate — generate QR image (PNG)
- POST /api/qr/create — create new QR code (admin)
- GET /api/qr/{code}/scans — view scan analytics (admin)
- GET /qr/{code} — redirect to guest register form or channel (guest flow)

**Permissions Required**:
- super_admin: generate org QRs
- admin (in org): generate channel QRs
- staff: view scan analytics
- guest: scan and register

**UI Components**:
- QR code display card (image + download button)
- QR analytics dashboard (scan count, unique users, geography)
- Create QR modal (select entity: org/channel, set expiration, preview)
- QR history table (created_at, scans, active/expired status, actions)

**Acceptance Criteria**:
- [ ] Admin can generate QR code for channel
- [ ] QR code generates unique PNG image
- [ ] Guest can scan QR → see registration form pre-filled with channel/org info
- [ ] QR scan logged to qr_scan_logs
- [ ] Expired QR code returns 410 Gone
- [ ] QR scan count increments on each scan
- [ ] Admin can view scan analytics (count, dates, device info)
- [ ] QR code can be downloaded as PNG

**Analytics Tracked**:
- qr_generated_count (per org, per channel)
- qr_scans_total (per QR code)
- qr_unique_users (distinct users who scanned)
- qr_conversion_rate (scans → registrations)
- qr_geography (location of scans)

---

### Feature: Channel Management

**Status**: In Development

**Purpose**: Allow organization admins to create and configure public/private communication channels.

**Business Rules**:
- Admin can create up to max_channels limit
- Channels have type: public (guest-visible), private (members-only), internal (staff-only)
- Channel has admin + optional moderators
- Members auto-added to #general on organization join
- Channel can be archived (soft delete)
- Channel hierarchies supported (subchannels, if implemented)

**Database Dependencies**:
- channels table (organization_id, type, parent_id, admin_user_id)
- channel_user pivot (role: admin, moderator, member, viewer)

**API/Routes**:
- GET /organization/{id}/dashboard/channels — list org channels
- POST /organization/{id}/dashboard/channels — create channel (admin)
- GET /organization/{id}/dashboard/channels/{channel} — show channel + members
- PUT /organization/{id}/dashboard/channels/{channel} — update channel (admin)
- DELETE /organization/{id}/dashboard/channels/{channel} — archive channel

**Permissions Required**:
- admin: create, update, delete channels
- staff: can moderate assigned channels
- member: view public/private channels (if member)
- guest: view public channels only

**UI Components**:
- Channel list grid (icon, name, type badge, member count, actions)
- Channel detail card (description, type, members, admin)
- Create channel form (name, description, type: public/private, icon)
- Channel settings modal (name, description, archive toggle)
- Channel member list (with role + actions)

**Acceptance Criteria**:
- [ ] Admin can create channel up to max_channels limit
- [ ] Creating beyond limit returns error
- [ ] Channel type determines who can see it (public/private/internal)
- [ ] Admin can update channel name/description
- [ ] Admin can add/remove channel members
- [ ] Channel admin set on creation
- [ ] Archive channel (soft delete) hides from UI
- [ ] Public channels show to guests
- [ ] Private channels show only to members

**Analytics Tracked**:
- channels_per_organization
- channel_member_count
- channel_posts_count
- channel_activity_score

---

### Feature: Post Creation & Publishing

**Status**: In Development

**Purpose**: Allow members to create and publish posts (messages, announcements) to channels.

**Business Rules**:
- Only members/staff can create posts
- Posts require title + body (image optional)
- Posts can be draft or published
- Published posts have published_at timestamp
- Staff posts need admin approval (optional workflow)
- Posts can expire (auto-archive after date)
- Posts support audience targeting (channel/members/all)
- Authors can edit/delete own posts
- Admins can delete any post (moderation)

**Database Dependencies**:
- posts table (channel_id, author_id, status, published_at, expires_at)
- Reaction/Comment tables (future)

**API/Routes**:
- GET /organization/{id}/dashboard/feeds — list channel posts
- GET /organization/{id}/dashboard/feeds/create — new post form
- POST /organization/{id}/dashboard/feeds — create post
- PUT /organization/{id}/dashboard/feeds/{post} — update post
- DELETE /organization/{id}/dashboard/feeds/{post} — delete post

**Permissions Required**:
- admin: create, edit, delete any post
- staff: create, edit own posts
- member: create, edit own posts (if allowed)
- viewer: read-only

**UI Components**:
- Post creation form (title, body rich editor, image upload, audience select, publish/draft toggle)
- Post list feed (author avatar, name, channel badge, content preview, timestamp, actions)
- Post detail view (full content, reactions, comments thread)
- Post editor (edit existing post)
- Delete confirmation modal

**Acceptance Criteria**:
- [ ] Member can create post as draft
- [ ] Draft post is not visible to others
- [ ] Member can publish post (sets published_at)
- [ ] Published post shows in channel feed
- [ ] Author can edit own post
- [ ] Author can delete own post
- [ ] Admin can delete any post
- [ ] Expired posts auto-archived
- [ ] Post shows author avatar + name
- [ ] Post shows publish date + "ago" format
- [ ] Images render in posts

**Analytics Tracked**:
- posts_created_count (per org, per channel, per user)
- posts_published_count
- post_avg_length
- post_engagement (views, reactions, comments)

---

### Feature: Analytics Dashboard

**Status**: Planned

**Purpose**: Provide real-time dashboards showing organization, channel, and user activity metrics.

**Business Rules**:
- Admin sees analytics for own organization
- Staff sees analytics for assigned channels
- Members see limited stats (their own posts, reactions)
- Super admin sees cross-org rollup analytics
- Data refreshed every hour (cached)
- Trends show 7-day, 30-day, 90-day views

**Metrics Tracked**:
- **Organization**: user count, channel count, post count, active users (30-day)
- **Channel**: member count, post count, engagement score (reactions + comments)
- **User**: posts created, posts viewed, reactions given
- **QR**: scans, unique scanners, conversion rate (scans → registered)

**Database Dependencies**:
- Denormalized cache columns on Organization, Channel, Post
- Analytics events table (optional, for detailed tracking)

**API/Routes**:
- GET /organization/{id}/dashboard/overview — org dashboard
- GET /organization/{id}/dashboard/analytics — detailed analytics (admin)
- GET /api/analytics/trends — chart data (7d, 30d, 90d trends)

**Permissions Required**:
- admin: view org analytics
- staff: view channel analytics
- member: view own stats
- guest: none

**UI Components**:
- Dashboard stats grid (users, channels, posts, active rate)
- Activity chart (posts/day, new users/day, engagement trend)
- Top channels by engagement
- Top contributors (most posts, most reactions)
- Funnel (QR scans → registrations)

**Acceptance Criteria**:
- [ ] Admin can view organization overview (users, channels, posts)
- [ ] Admin can view 7-day/30-day/90-day trends
- [ ] Charts update after new posts/signups
- [ ] Staff can view assigned channel analytics
- [ ] Analytics use cached data (update hourly)
- [ ] Export analytics to CSV (admin)

**Analytics Tracked**:
- dashboard_views (per user role)
- analytics_export_count

---

### Feature: Real-Time Channel Chat (Future)

**Status**: Planned

**Purpose**: Enable real-time messaging and reactions within channels using Laravel Reverb.

**Business Rules**:
- Members can send instant messages (lightweight posts)
- Messages appear instantly via WebSocket
- Chat history persists in database
- Typing indicators show who's composing
- Reactions (emoji) are real-time
- Chat can be muted/unmuted per channel

**Infrastructure**:
- Laravel Reverb (WebSocket server)
- Redis pub/sub for broadcasting
- Pusher/alternatives for hosted solution

**Database Dependencies**:
- Chat messages table (future)
- Reaction updates real-time

**API/Routes**:
- WebSocket: App.Channel.{channel_id}
- WebSocket: App.Notification.{user_id}

**Acceptance Criteria**:
- [ ] Member sends message → appears instantly to other members
- [ ] Typing indicator shows when member is composing
- [ ] Reactions emit in real-time
- [ ] Chat history loads on page load
- [ ] Connection handles disconnect/reconnect

---

### Feature: User Invitations & Onboarding

**Status**: In Development

**Purpose**: Allow organization admins to invite users via email and manage onboarding.

**Business Rules**:
- Admin sends invitation email to user
- Invitation has unique code (expires in 7 days)
- User clicks link → registration page pre-filled with role
- User creates password + confirms email
- User auto-joins default channel on organization
- Can invite in bulk (CSV upload)

**Database Dependencies**:
- Invitations table (code, organization_id, email, role, expires_at)

**API/Routes**:
- GET /organization/{id}/dashboard/users — list members
- POST /organization/{id}/dashboard/users — create invitation
- GET /organization/{id}/dashboard/users/create — invite form
- GET /invitation/{code} — accept invitation (pre-fill registration)

**Permissions Required**:
- admin: send invitations
- staff: view members (read-only)
- member: none

**UI Components**:
- User list table (email, name, role, joined date, actions)
- Invite form (email input, select role, send button)
- Bulk invite form (CSV upload, role select)
- Pending invitations list (email, sent date, expiration, resend/cancel)

**Acceptance Criteria**:
- [ ] Admin can send invitation email
- [ ] Invitation email contains unique link
- [ ] Clicking link pre-fills registration form
- [ ] User completes registration → auto-joined to organization
- [ ] Admin can view pending invitations
- [ ] Expired invitations marked (cannot be used)
- [ ] Admin can resend invitation
- [ ] Admin can cancel pending invitation
- [ ] Bulk CSV import works

**Analytics Tracked**:
- invitations_sent_count
- invitations_accepted_count
- invitation_acceptance_rate
- avg_days_to_accept

---

### Feature: Guest QR Registration Flow

**Status**: In Development

**Purpose**: Allow unauthenticated guests to register via QR code scan with minimal friction.

**Business Rules**:
- Guest scans QR → lands on registration page
- Form pre-filled with organization/channel from QR
- Guest enters name + email + optional phone
- System creates user + joins organization/channel
- Welcome email sent
- Guest can continue to view channel

**UI Flow**:
1. Guest scans QR (via mobile/web camera or typed code)
2. Redirected to /qr/{code} route
3. Route validates code + retrieves organization/channel
4. Show lightweight registration form (name, email, phone optional)
5. Submit → create user, join org/channel, redirect to channel view
6. Welcome email sent

**Acceptance Criteria**:
- [ ] QR redirects to registration form
- [ ] Form pre-fills organization/channel info
- [ ] Guest can register with name + email
- [ ] User auto-joined to organization as 'member'
- [ ] User auto-joined to channel as 'member'
- [ ] Welcome email sent with login link
- [ ] After registration, guest can view channel
- [ ] No email verification required (fast-path)

---

### Feature: Dark Mode & Theme Support

**Status**: In Development

**Purpose**: Support dark mode and light mode with user preference persistence.

**Business Rules**:
- Default theme: light
- User can toggle dark/light in settings
- Preference stored in user profile
- Applied on page load
- All components support both themes
- No white-on-white or black-on-black text

**Database Dependencies**:
- users.theme (string: 'light', 'dark')

**CSS/Design**:
- CSS variables for color palette
- @media (prefers-color-scheme) fallback
- data-theme="dark" | "light" attribute on html root

**Acceptance Criteria**:
- [ ] User can toggle theme in settings
- [ ] Theme preference persists across sessions
- [ ] Dark mode readable (sufficient contrast)
- [ ] Light mode readable
- [ ] All components respect theme
- [ ] No flickering on page load

---

### Feature: Valex Dashboard Theme Migration

**Status**: In Development (see ADR-011)

**Purpose**: Give every authenticated page one premium, consistent Valex-based shell, without touching the public guest experience.

**Scope**:
- In: `/admin/*`, `/tenant/*`, `/organizations/*`, settings, users, groups, posts, tickets, audience segments, organization dashboards.
- Out (for now): `/browse*`, `/qr/*`, `/otp/*`, welcome, login/register. Feed/collections/notifications (mobile-shell) pending owner decision.

**Business Rules**:
- Sidebar items remain permission-based and organization-aware (no change in who sees what).
- Generic terminology only (Organization, Branch, Department, Channel, Visitor, Member).
- RTL and LTR both work; Arabic users get the RTL build.
- Light and dark mode both work and persist per user.
- Mobile first: sidebar collapses, tables scroll or stack on small screens.

**Delivery Phases**:
1. Assets + `layouts/valex` + sidebar/header + component wrappers (first working page).
2. Admin and organizations pages.
3. Tenant, channels, posts.
4. Settings, users, groups, dashboards (real charts replace CSS-only ones).
5. Tickets, audience segments, QR admin, profile.
6. Cleanup of dead layouts and duplicate CSS.

**Acceptance Criteria**:
- [ ] Every in-scope route renders with `layouts.valex` and no 500s
- [ ] LTR + RTL verified per route group
- [ ] Light + dark verified per route group
- [ ] Sidebar permissions verified for super_admin, organization admin, member
- [ ] No Bootstrap 5 markup remains in migrated views
- [ ] Guest pages unchanged

---

### Feature: Analytics Dashboards (Super Admin + Organization)

**Status**: In Development

**Purpose**: Data-rich dashboards with KPIs, trends and charts so admins see platform and organization health at a glance.

**Business Rules**:
- Super Admin dashboard (`/admin-dashboard`, super_admin only) is platform-wide; reachable from a dedicated sidebar entry under "Admin".
- Organization dashboard (`/organizations/dashboard`) is scoped to the current organization only (multi-tenant isolation).
- Statistics are computed in `App\Services\DashboardStatsService` (never in Blade) and cached for 5 minutes per scope.
- Range selector: 7 / 30 / 90 days (default 30).

**Data / Charts**:
- KPIs with period-over-period change: organizations, users, posts, channels, QR scans, open tickets.
- Line: posts per day vs. users joined per day. Doughnut: organizations by status. Bar: top organizations by posts. Bar: QR scans per day and device split. Progress: post engagement funnel (delivered / viewed / read / acknowledged). Tables: recent activity log, newest organizations, open tickets.

**Acceptance Criteria**:
- [ ] Sidebar shows "Super Admin Dashboard" only for super_admin
- [ ] Every chart renders in light and dark mode and RTL
- [ ] Empty data shows an empty state instead of a broken chart
- [ ] Organization dashboard never shows data from another organization
- [ ] Page loads with a bounded number of queries (cached)

---

### Feature: CRM (Platform CRM + Organization CRM)

**Status**: In Development

**Purpose**: One place to understand everything that happens, in detail and at a glance. Two scopes of the same capability:
- **Platform CRM** (super admin only, `/admin/crm`): oversight of the whole platform. The super admin is responsible for the platform, not for any single organization, so this is read-only monitoring (no content authoring).
- **Organization CRM** (`/organizations/crm`): everything about the organization's relationships, scoped strictly to the current organization.

**Platform CRM screens**:
1. **Organizations 360** (`/admin/crm`): searchable/filterable table (status, health) with users, channels, posts (30d), open tickets, QR scans, last activity and a health score. Row opens **Organization 360** with tabs: Overview, Members, Channels, Posts, Tickets, QR Codes, Activity.
2. **People** (`/admin/crm/people`): every account (staff, visitors, super admins) with organizations, channels, last seen. Row opens **Person 360**: memberships, subscribed channels, followed organizations, tickets, authored posts, QR scans, activity.
3. **Activity Log** (`/admin/crm/activity`): audit trail of every create/update/delete/restore with filters (organization, user, event, date range) and pagination.

**Organization CRM screens**:
1. **Contacts** (`/organizations/crm`): everyone related to the organization — members (any membership status), followers and channel subscribers — with relationship type, role, channels, engagement and last seen. Filters: relationship, search.
2. **Contact 360**: profile, membership/role/status, channels, post engagement (delivered/viewed/read/acknowledged), tickets, QR scans, activity timeline.
3. **Activity** (`/organizations/crm/activity`): audit trail restricted to the organization.
(Tickets, Posts, Audiences, QR remain their own modules and are linked from each 360.)

**Business Rules**:
- Health score (0–100): +30 if posts in last 30 days, +20 if ≥2 active members, +20 if ≥1 channel, +30 if activity in last 14 days. Labels: ≥70 Healthy, 40–69 Attention, <40 At risk.
- Organization CRM never exposes another organization's data (every query is constrained by `organization_id`); organization admins only (`update` policy on the organization).
- Platform CRM is guarded by `CheckRole:super_admin`.
- Statistics logic lives in `App\Services\CrmService`, not in Blade.

**Acceptance Criteria**:
- [ ] Super admin sees CRM section in sidebar; organization users see only their CRM
- [ ] Organization 360 and Person 360 open for every record without errors
- [ ] Filters and pagination keep their query string
- [ ] Organization CRM returns 403/404 for records of other organizations
- [ ] Works in light/dark mode, RTL/LTR, and on mobile

### Feature: Organization Design Templates

**Status**: In Development (ADR-012)

**Purpose**: After choosing an Organization Template, the admin chooses a Design Template that controls how the organization's public page (`/browse/org/{id}`) looks to visitors and subscribers. Different industries get different designs.

**Business Rules**:
- Designs belong to an industry (`industry_key` of the organization template): hospitality, education, healthcare, retail, corporate — 3 designs each.
- Chosen by the super admin at creation; the organization admin (`update` policy) can change it later from organization settings (design, colors, cover image, toggles) with a preview.
- A design is an ordered list of blocks: hero (figure / profile), channels (cards / accordion / carousel / list), posts (timeline / blog / list), about, stats.
- No design selected (null) = the classic page, unchanged.
- A design must match the organization's industry (validated server-side).

**Cover carousel**: an organization can upload several cover images (up to 8). With 2+ images the cover block renders a carousel; the organization admin controls: autoplay on/off, interval (2-15 s), transition (slide/fade), arrows, indicators, cover height (compact/standard/tall) and image order. One image renders as a static cover. Stored in `design_options` (`cover_paths`, `cover_autoplay`, `cover_interval`, `cover_effect`, `cover_arrows`, `cover_indicators`, `cover_height`); legacy `cover_path` is still read.

**Channel designs**: any channel or sub-channel can pick a design when created/edited (organization admins and super admin). "Inherit" (default) uses the parent channel's design, else the classic channel page. Designs: Spotlight, Notice Board, Directory, Magazine. A parent channel shows its sub-channels with the design's channel block; a sub-channel shows its parent and siblings. Preview via `?preview_design=` for organization admins.

**Database**: `organizations.design_key` (nullable string), `organizations.design_options` (json: `primary_color`, `secondary_color`, `cover_path`, `show_posts`, `show_stats`).

**Acceptance Criteria**:
- [ ] Create form shows only the designs of the selected template's industry
- [ ] Designs from another industry are rejected
- [ ] All 15 designs render with data and with empty data (RTL/LTR, light/dark, mobile)
- [ ] Organizations without a design render exactly as before
- [ ] Only organization admins can change the design; visitors cannot use preview

### Feature: Post Formats, Card Styles, Badges and Interactions

**Status**: In Development (ADR-013)

**Purpose**: Let admins publish different kinds of posts and let visitors interact beyond like and comment.

**Create form** (`/tenant/channels/{id}/posts/create`): choose format, card style, priority, badge and the reactions people may use.

**Formats**:
- Standard: title, text, optional single image.
- Gallery: several images (max 8); 2+ images render as a carousel.
- Poll: question + 2-8 options, single or multiple choice, optional end date, results shown always or after voting.
- Event: start/end date, location, optional capacity; RSVP going / maybe / not going with counts.
- Rating: 1-5 stars, average and count.

**Card styles**: classic, image (large cover), gradient (text on colored background), spotlight (highlighted card).

**Badges** (list): New, Featured, Important, Urgent, Update, Offer, Event, Reminder, Tip; optional custom text; each has a fixed color.

**Priority**: low, medium, high, critical (existing column); high/critical show a marker on the card.

**Reactions**: per post, the admin picks which of like, love, haha, wow, sad, celebrate are available; a user has one reaction per post and can change or remove it.

**Business Rules**:
- All interactions require sign-in; guests get the sign-in prompt.
- Poll: one vote per user for single choice; multiple choice allows several options but never the same option twice; no voting after the end date.
- Event: RSVP can be changed; when capacity is reached, new "going" answers are refused.
- Rating: one rating per user, editable.
- Existing posts behave as standard / classic.

**Acceptance Criteria**:
- [ ] Each format validates its own fields and saves correctly
- [ ] Poll/RSVP/rating/reaction endpoints enforce the rules above
- [ ] Posts render the same widgets on the post page and channel feeds
- [ ] Works in light/dark, RTL/LTR and on mobile

### Feature: Product Landing Page (`/`)

**Status**: In Development

**Purpose**: A public, animated page at the site root that explains i-Page: what it is, how it works, everything built today and the roadmap. It is the first thing a visitor, buyer or investor sees.

**Business Rules**:
- Served at `/` (route name `home.index`) for everyone; signed-in users see an extra "Open my workspace" button that goes to their role's home (super admin dashboard, organization dashboard or feed). The old automatic redirect at `/` is replaced by this page.
- Bilingual (Arabic default, English) with a language switch (`?lang=ar|en`, remembered in the session); full RTL/LTR support and dark mode (shares the site's `theme` preference).
- Content lives in `config/landing.php` (not in the view) so copy can change without touching markup.
- Live numbers (organizations, public channels, published posts, QR scans) come from the database, cached for 10 minutes; only aggregate counts are exposed, never personal data.
- "Current" items must describe features that exist; "Roadmap" items come from the documented roadmap (`ENTERPRISE_REVIEW.md`, feature specs) and are labeled Next / Later.
- Animations are CSS/vanilla JS only (no libraries), and are disabled under `prefers-reduced-motion`.

**Sections**: Hero (rotating industries, animated QR-to-phone demo), live stats (count-up), How it works, Industries marquee, Platform features, Roles, Design system, Roadmap timeline, Principles and technology, Final call to action.

**Acceptance Criteria**:
- [ ] `/` renders for guests and signed-in users without errors, in both languages
- [ ] RTL, dark mode and mobile widths work; no horizontal scrolling
- [ ] Reduced-motion users get a static page
- [ ] Statistics are aggregate-only and cached

---

---

## FUTURE FEATURES (Planned, Beyond Initial Release)

---

### Feature: AI-Powered Summaries

**Purpose**: Auto-generate summaries of channel activity, trending topics, and discussions.

**Technology**: LLM integration (Claude API)

**Timeline**: Post-MVP

---

### Feature: Polls & Surveys

**Purpose**: Create and analyze polls to gather member feedback.

**Business Rules**:
- Admin creates poll with questions + options
- Members vote once per poll
- Anonymous votes possible
- Results show in real-time
- Can set expiration date

**Acceptance Criteria**:
- [ ] Admin can create poll
- [ ] Member can vote once
- [ ] Results show live count + percentage
- [ ] Expired polls closed for voting

---

### Feature: Advanced Segmentation

**Purpose**: Create user segments for targeted communications.

**Business Rules**:
- Admin defines segment rules (role, department, activity level)
- Can target channels/posts to specific segments
- Segments auto-calculated nightly

---

### Feature: Compliance & Audit Logs

**Purpose**: Full audit trail for compliance (HIPAA, GDPR, SOX).

**Business Rules**:
- All user actions logged (create, read, update, delete)
- Logs immutable and archived
- Access controlled (audit role required)
- Export for compliance audits

---

---

## Feature Status Matrix

| Feature | Status | Start Date | Estimated Ship | Owner |
|---------|--------|------------|-----------------|-------|
| Organization Management | In Dev | 2026-07-08 | 2026-07-20 | Team |
| Channel Management | In Dev | 2026-07-08 | 2026-07-20 | Team |
| Post Creation | In Dev | 2026-07-08 | 2026-07-20 | Team |
| User Invitations | In Dev | 2026-07-08 | 2026-07-18 | Team |
| Dark Mode | In Dev | 2026-07-08 | 2026-07-18 | Team |
| Guest QR Registration | In Dev | 2026-07-08 | 2026-07-22 | Team |
| QR Platform | Planned | — | 2026-07-25 | — |
| Analytics Dashboard | Planned | — | 2026-08-01 | — |
| Real-Time Chat | Planned | — | 2026-08-15 | — |
| Polls & Surveys | Planned | — | 2026-08-25 | — |

---

# Claude Compliance Checklist

Before implementing any feature, verify:

- [ ] Feature spec added to this document
- [ ] Business rules clearly defined
- [ ] Database dependencies listed (tables, relationships)
- [ ] API routes specified
- [ ] Permissions required documented
- [ ] UI components/mockups identified
- [ ] Acceptance criteria listed (testable)
- [ ] Analytics events identified
- [ ] Multi-tenant isolation considered (organization_id filtering)
- [ ] Mobile-first design (responsive UI)
- [ ] Accessibility requirements met (WCAG 2.1 AA)
- [ ] RTL/LTR support considered
- [ ] Dark mode support required
- [ ] Related docs linked ([[...]])
