# Custom PHP CMS

A lightweight, custom-built Content Management System (CMS) using native PHP. This project started as a simple admin panel and has evolved into a fully functional CMS with advanced page building capabilities, custom code snippets, a Kanban project board, a drag-and-drop form builder, two-factor authentication, analytics, a comprehensive documentation system, a fully personalised per-user dashboard, multi-language content support, virtual media folders, a visual automation/workflow builder, SEO analysis, a content calendar, custom fields, a WordPress-like first-run installer, trash/recycle bin, CSV import/export, maintenance mode, user activity tracking, a redirects manager, revision diff viewer, related posts, a token-authenticated REST API, a built-in media editor, multi-site management, a data explorer, form analytics, and an audit dashboard.

## Features

### Content Editing
- **Advanced Content Editors:**
    - **GrapesJS:** Visual, drag-and-drop page designer with 49+ custom widgets.
    - **Editor.js:** Structured, block-based page building.
    - **Toast UI Editor:** Markdown and WYSIWYG editor for posts and articles.
    - **Monaco Editor:** IDE-like code editor for raw HTML, CSS, JavaScript, and PHP snippets.
- **Scheduled Publishing:** Set a future publish date/time for any post; goes live automatically.
- **Content Lock:** Prevents simultaneous editing by multiple users.
- **SEO Settings:**
    - Live Google snippet preview (desktop & mobile tabs) that updates as you type.
    - Character-length progress bars for SEO title (30–60 chars) and meta description (120–160 chars).
    - **SEO Analysis checklist** with 8 checks: keyword in SEO title, meta description, post title, URL slug, and content body; plus title length, description length, and content word count.
    - Score bar (0–8) with green/yellow/red threshold colours.
    - **Bulk SEO Editor:** Spreadsheet-style page to edit SEO title, meta description, slug, and focus keyword for all posts at once. Auto-saves via AJAX. Character-count indicators for optimal lengths.
- **Content Calendar:** Monthly calendar view of published (green) and scheduled (orange) posts with ← / → month navigation, today highlight, click-to-edit events, and per-month stats.
- **Custom Fields:** Free-form key/value metadata on any post, stored in `post_meta`. Add, edit, and delete rows inline in the sidebar; saved automatically with the post.
- **Trash / Recycle Bin:** Soft-delete for posts and pages — items move to Trash first, with Restore and Delete Permanently actions. "Empty Trash" button to purge all at once.
- **CSV Import/Export:**
    - Export posts to CSV with status and language filters. Includes title, content, categories, tags, SEO fields, and more.
    - Import posts from CSV with duplicate handling (skip, overwrite, or rename). Categories and tags are auto-created if they don't exist.
- **Revision Diff Viewer:** Side-by-side comparison of any two revisions, with red (removed) and green (added) highlighting. Compare button on every revision entry in the sidebar.
- **Related Posts:** Select related articles from a sidebar metabox in the post editor. Stored in `post_relations` table, displayed as suggestions on the frontend.
- **Duplicate Post/Page:** One-click clone of any post or page, including content and settings, created as a draft with a unique slug.
- **Multi-language / i18n:**
    - Per-post and per-page language field (`id` / `en`).
    - Language filter tabs (🇮🇩 / 🇬🇧) in the Posts and Pages list.
    - "Add Translation" row action links posts/pages to their translated counterpart via `translation_of`.
    - Language selector in the publish sidebar when editing.

### Form Builder
- Create fully custom forms (contact, survey, data collection) without writing code.
- 12 field types: Text, Email, Phone, Textarea, Dropdown, Checkbox, Number, Date, File Upload, Radio, URL, Hidden.
- Elementor-style widget tile picker for adding fields.
- Auto-generates a shortcode (`[contact_form id="X"]`) for embedding anywhere.
- **Submission Management:** View all form responses in a dedicated table.
- **Kanban Integration:** Each submission can automatically create a Kanban card.
- **Email Notification:** Receive an email copy of every new submission (via SMTP).

### Kanban Board
- Visual drag-and-drop project management board.
- Multiple boards with tab-style navigation.
- Fully customisable columns and cards (create, edit, delete).
- **Drag-to-scroll** horizontally across columns.
- **Activity Log:** Every card movement is recorded — who moved it, from where, to where, and when.
- **Form Builder Integration:** Automatically create cards from form submissions.

### Dashboard Widgets
- **27 built-in widgets** across four tiers:
    - *Core:* Stats Overview, Monthly Visitors, Monthly Content, Top Articles, Recent Posts & Drafts, Pending Comments, Quick Draft, Kanban Summary, Form Submissions, Site Health, Recent Activity.
    - *Tier 1:* World Clock (custom timezone picker per user), Countdown Timer, Database Size, Media Storage Usage, Backup Status, Top Tags & Categories, Upcoming Scheduled Posts, New Registrations, Active Users.
    - *Tier 2:* Last Error Log, Sticky Notes, Personal Todo List, Content Calendar, Broken Links Checker.
    - *Tier 3:* RSS Feed Reader, Weather (OpenWeather API), Traffic by Device.
- **Fully personalised per user** — each account has its own widget layout stored in the database.
- **Inline customisation directly on the dashboard:**
    - Drag handles on every widget for live reorder (SortableJS).
    - × button to remove a widget without leaving the page.
    - "Add Widget" dropdown to restore hidden widgets.
    - All changes auto-saved via AJAX with a visual save indicator.
- Separate full management page (`widgets.php`) for batch enable/disable/reorder.

### Email System (SMTP)
- Configure outgoing email via any SMTP provider (Gmail, Mailgun, SendGrid, etc.).
- Supports TLS and SSL encryption with STARTTLS negotiation.
- Branded HTML email template for all system emails.
- Send Test Email button for instant configuration verification.
- Falls back to PHP `mail()` if SMTP is not configured.

### Authentication & Security
- **Two-Factor Authentication (2FA):**
    - OTP (One-Time Password) sent to the user's registered email on every login.
    - 6-digit code with 5-minute expiry and auto-submit on input.
    - 8 single-use backup codes in `XXXX-XXXX` format (SHA-256 hashed in database).
    - Enable/disable 2FA per user from the profile or user edit page.
    - SMTP guard: 2FA cannot be enabled unless SMTP is configured and the user has an email address.
    - Admins can manage 2FA for any user.
- **Password Reset via Email:**
    - Self-service password reset — no admin intervention required.
    - Token-based, 1-hour expiry, single-use links.
    - Anti-enumeration: always shows a success message regardless of whether the email exists.
- **Brute-force login protection** with lockout after repeated failed attempts.
- **Role-based Menu Visibility:** Admins can hide specific sidebar sections from non-admin roles without revoking underlying capabilities.
- Admin Authentication and protected API endpoints.
- Dynamic routing (Pretty URLs) and SEO-friendly frontend (Open Graph, Twitter Cards).

### User Management
- User list, add/edit/delete with avatar upload.
- **User Roles & Capabilities Customiser:** create custom roles with granular permissions.
- Per-user email address field (required for 2FA and password reset).
- **User Activity Log:** See who is currently online (green dot), last login time, and last activity. Summary cards for online count, today's logins, and total users.

### Analytics
- Traffic overview: daily visitors and page views with 7/30/90-day periods.
- Percentage change cards vs previous equivalent period.
- Traffic sources: direct, search, social, other (from HTTP Referrer).
- Device breakdown: desktop, mobile, tablet (from User-Agent).
- Top Posts table by view count.
- Reading Time estimates per post (word count ÷ 200 wpm).
- Form Conversion Rate (submissions ÷ page views).
- Kanban Throughput: cards completed per week.
- Powered by Toast UI Chart (local); no external analytics service required.
- **Form Analytics:** Submission trends chart, filter by form, stats cards, tui-grid responses table.
- **Audit Dashboard:** Activity timeline, top actions/users charts, recent activity grid with color-coded action badges.

### Audit Log
- Records every significant admin action: login, post create/update/delete, media upload/delete, and more.
- Filter by user, action, module, and date range.
- Export to CSV. Purge old entries by time range.
- Before/after diff viewer for detailed change tracking.

### Other Features
- **Image Optimizer:** Automatic image compression and resizing on upload.
- **Media Folder Organizer:**
    - Virtual folders stored in the database (`media_folders` + `media_assignments`).
    - Persistent folder tree sidebar alongside the media grid.
    - Drag any media item onto a folder to assign it; drag onto "All Media" to unassign.
    - Create, rename, and delete folders without touching the filesystem.
- **Comment Moderation:** Approve, reject, and spam workflow with bulk actions.
- **Notification Center:** In-app notifications for system events.
- **Frontend Admin Bar:** Contextual bar on public pages for logged-in users — quick edit and preview links.
- **Shortcode & Snippet System:** Create PHP/CSS/JS snippets and embed them anywhere via auto-generated shortcodes. Navigation Menus also act as shortcodes.
- **Dark Mode:** Toggle between light and dark admin UI; preference saved per browser.
- **Automation / Workflows:**
    - Visual builder to create automations without writing code.
    - **Triggers:** Post Published, Form Submitted, User Registered, Kanban Card Moved.
    - **Conditions:** Optional field-level filters (equals, contains, starts with, gt/lt) — all conditions must match.
    - **Actions:** Send Email (with `{{placeholder}}` interpolation) and Webhook (HTTP POST JSON to any URL).
    - Enable/disable automations per-row without deleting them.
    - Execution log with result status and error messages for every run.
- **Appearance & Menu Builder:** Customise colours and navigation menus.
- **Maintenance Mode:** Toggle on/off from General Settings — frontend displays a branded "Under Maintenance" page (503) while admin panel remains accessible. Custom message support.
- **Redirects Manager:** Create and manage 301/302 URL redirects from the admin panel. Hit counter, enable/disable toggle, edit modal. Processed by the front controller before routing.
- **REST API:** Token-authenticated CRUD API for posts, pages, media, categories, tags, and options. Generate and manage API tokens from the admin panel. CORS-enabled, supports Bearer token and query param auth.
- **Media Editor:** Crop, resize, rotate, and flip images directly from the media library using an HTML5 Canvas editor. Changes are saved in-place.
- **Multi-site Manager:** Create and manage multiple sites from a single installation. Each site gets its own set of database tables (prefixed), with shared user authentication. Activate, deactivate, or delete sites.
- **Data Explorer:** Browse any database table via an interactive tui-grid. Read-only, auto-detect columns, export to CSV.
- **Database Backup & Restore** interface.
- **Export / Import** content tools (JSON and CSV).

### Built-in Documentation
- Comprehensive manual accessible inside the admin panel (`wp-admin/docs.php`).
- **Bilingual** (English & Indonesian).
- **Live Search** with keyboard navigation (`/` shortcut) and full search index covering all topics.
- Sticky sidebar with accordion-style hierarchy.
- Cache-busted JS to always serve the latest search index.
- Covers all features: Form Builder, Kanban, 2FA, Password Reset, Role Visibility, Analytics, SMTP, Widgets, and more.

---

## Installation

1. **Clone the repository:**
    ```bash
    git clone https://github.com/s4rt4/wp-admin.git
    ```

2. **Run:**
    - Serve via Apache/Nginx or a local environment like Laragon/XAMPP.
    - Access the admin panel at `/wp-admin/`.
    - Compatible with both subdirectory (`localhost/word-press/`) and virtualhost (`word-press.test/`) setups.

3. **First-run installer:**
    - On first visit, you will be automatically redirected to the installer wizard (`/wp-admin/install.php`).
    - **Step 1 — Language:** Choose Indonesian or English.
    - **Step 2 — Database:** Enter your DB host, name, username, and password. Use the "Test Connection" button to verify before proceeding.
    - **Step 3 — Site Info:** Set your site name, URL, and admin account (username, email, password).
    - Click **Install** — the wizard creates all database tables, inserts default options and roles, and writes `wp-admin/wp-config.php` with your credentials.
    - You will be redirected to the login page when done.

> **Note:** `wp-admin/wp-config.php` is excluded from the repository (`.gitignore`) — credentials never leave your machine.

---

## Default Admin Login

- **Username:** `admin`
- **Password:** `admin123`

---

## Tech Stack

- **Backend:** Native PHP (no frameworks).
- **Frontend:** HTML5, CSS3, Vanilla JavaScript.
- **Database:** MySQL.
- **Libraries / Editors:** GrapesJS, Editor.js, Toast UI Editor, Toast UI Image Editor, Toast UI Grid, Monaco Editor, Chart.js, SortableJS, Fabric.js.
- **Vendored assets:** TUI libraries are bundled locally in `wp-admin/vendor/tui/` for offline use and faster loading.

---

## Disclaimer

This project is a hobby and not for commercial use. Any resemblance to WordPress is intentional as a tribute from a fan, and is not intended to be a copy or a competitor.
