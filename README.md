# Custom PHP CMS

A lightweight, custom-built Content Management System (CMS) using native PHP. This project started as a simple admin panel and has evolved into a fully functional CMS with advanced page building capabilities, custom code snippets, a Kanban project board, a drag-and-drop form builder, two-factor authentication, analytics, a comprehensive documentation system, and a fully personalised per-user dashboard.

## Features

### Content Editing
- **Advanced Content Editors:**
    - **GrapesJS:** Visual, drag-and-drop page designer with 49+ custom widgets.
    - **Editor.js:** Structured, block-based page building.
    - **Toast UI Editor:** Markdown and WYSIWYG editor for posts and articles.
    - **Monaco Editor:** IDE-like code editor for raw HTML, CSS, JavaScript, and PHP snippets.
- **Scheduled Publishing:** Set a future publish date/time for any post; goes live automatically.
- **Content Lock:** Prevents simultaneous editing by multiple users.

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

### Analytics
- Traffic overview: daily visitors and page views with 7/30/90-day periods.
- Percentage change cards vs previous equivalent period.
- Traffic sources: direct, search, social, other (from HTTP Referrer).
- Device breakdown: desktop, mobile, tablet (from User-Agent).
- Top Posts table by view count.
- Reading Time estimates per post (word count ÷ 200 wpm).
- Form Conversion Rate (submissions ÷ page views).
- Kanban Throughput: cards completed per week.
- Powered by Chart.js; no external analytics service required.

### Audit Log
- Records every significant admin action: login, post create/update/delete, media upload/delete, and more.
- Filter by user, action, module, and date range.
- Export to CSV. Purge old entries by time range.
- Before/after diff viewer for detailed change tracking.

### Other Features
- **Image Optimizer:** Automatic image compression and resizing on upload.
- **Comment Moderation:** Approve, reject, and spam workflow with bulk actions.
- **Notification Center:** In-app notifications for system events.
- **Frontend Admin Bar:** Contextual bar on public pages for logged-in users — quick edit and preview links.
- **Shortcode & Snippet System:** Create PHP/CSS/JS snippets and embed them anywhere via auto-generated shortcodes. Navigation Menus also act as shortcodes.
- **Dark Mode:** Toggle between light and dark admin UI; preference saved per browser.
- **Appearance & Menu Builder:** Customise colours and navigation menus.
- **Database Backup & Restore** interface.
- **Export / Import** content tools.

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

2. **Database Setup:**
    - Create a new MySQL database named `wp-admin` (or adjust config).
    - Import the provided SQL file: `wp-admin/wp-admin.sql`.

3. **Configuration:**
    - Open `wp-admin/db_config.php`.
    - Adjust the database credentials if necessary:
        ```php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'wp-admin');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        ```

4. **Run:**
    - Serve via Apache/Nginx or a local environment like Laragon/XAMPP.
    - Access the admin panel at `/wp-admin/`.
    - Compatible with both subdirectory (`localhost/word-press/`) and virtualhost (`word-press.test/`) setups.

---

## Default Admin Login

- **Username:** `admin`
- **Password:** `admin123`

---

## Tech Stack

- **Backend:** Native PHP (no frameworks).
- **Frontend:** HTML5, CSS3, Vanilla JavaScript.
- **Database:** MySQL.
- **Libraries / Editors:** GrapesJS, Editor.js, Toast UI Editor, Monaco Editor, Chart.js, SortableJS.

---

## Disclaimer

This project is a hobby and not for commercial use. Any resemblance to WordPress is intentional as a tribute from a fan, and is not intended to be a copy or a competitor.
