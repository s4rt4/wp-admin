# Custom PHP CMS

A lightweight, custom-built Content Management System (CMS) using native PHP. This project started as a simple admin panel and has evolved into a fully functional CMS with advanced page building capabilities, custom code snippets, a Kanban project board, a drag-and-drop form builder, two-factor authentication, analytics, and a comprehensive built-in documentation system.

## Features

- **Advanced Content Editors:**
    - **GrapesJS:** For visual, drag-and-drop page design (perfect for Landing Pages, features 49+ custom widgets).
    - **Editor.js:** For structured, block-based page building.
    - **Toast UI Editor:** A powerful Markdown and WYSIWYG editor for writing standard Posts and Articles.
    - **Monaco Editor:** A powerful IDE-like code editor for writing raw HTML, CSS, JavaScript, and PHP snippets.
- **Form Builder:**
    - Create fully custom forms (contact, survey, data collection) without writing code.
    - 12 field types: Text, Email, Phone, Textarea, Dropdown, Checkbox, Number, Date, File Upload, Radio, URL, Hidden.
    - Elementor-style widget tile picker for adding fields.
    - Auto-generates a shortcode (`[contact_form id="X"]`) for embedding anywhere.
    - **Submission Management:** View all form responses in a dedicated table.
    - **Kanban Integration:** Each submission can automatically create a Kanban card.
    - **Email Notification:** Receive an email copy of every new submission (via SMTP).
- **Kanban Board:**
    - Visual drag-and-drop project management board.
    - Multiple boards with tab-style navigation.
    - Fully customizable columns and cards (create, edit, delete).
    - **Activity Log:** Every card movement is recorded — who moved it, from where, to where, and when.
    - **Form Builder Integration:** Automatically create cards from form submissions.
- **Email System (SMTP):**
    - Configure outgoing email via any SMTP provider (Gmail, Mailgun, SendGrid, etc.).
    - Supports TLS and SSL encryption with STARTTLS negotiation.
    - Branded HTML email template for all system emails.
    - Send Test Email button for instant configuration verification.
    - Falls back to PHP `mail()` if SMTP is not configured.
- **Two-Factor Authentication (2FA):**
    - OTP (One-Time Password) sent to the user's registered email on every login.
    - 6-digit code with 5-minute expiry and auto-submit on input.
    - 8 single-use backup codes in `XXXX-XXXX` format (SHA-256 hashed in database).
    - Enable/disable 2FA per user from the profile or user edit page.
    - Admins can manage 2FA for any user.
- **Analytics Dashboard:**
    - Traffic overview: daily visitors and page views with 7/30/90-day periods.
    - Percentage change cards vs previous equivalent period.
    - Traffic sources: direct, search, social, other (from HTTP Referrer).
    - Device breakdown: desktop, mobile, tablet (from User-Agent).
    - Top Posts table by view count.
    - Reading Time estimates per post (word count ÷ 200 wpm).
    - Form Conversion Rate (submissions ÷ page views).
    - Kanban Throughput: cards completed per week.
    - Powered by Chart.js, no external analytics service required.
- **Custom Dashboard Widgets:**
    - 11 built-in widgets: Stats Overview, Visitor Chart, Content Chart, Top Articles, Recent Posts, Pending Comments, Quick Draft, Kanban Summary, Form Submissions, Site Health, Recent Activity.
    - Drag-and-drop reordering (SortableJS).
    - Toggle individual widgets on or off.
    - Per-user preferences saved in the database.
    - Quick Draft widget: create a draft post directly from the dashboard.
- **Scheduled Publishing:**
    - Set a future publish date/time for any post.
    - Post automatically goes live when the scheduled time arrives.
- **Content Lock:**
    - Lock posts and pages to prevent simultaneous editing by multiple users.
- **Image Optimizer:**
    - Automatic image compression and resizing on upload.
- **Audit Log:**
    - Records every significant admin action: login, post create/update/delete, media upload/delete, and more.
    - Filter by user, action, module, and date range.
    - Export to CSV. Purge old entries by time range.
    - Before/after diff viewer for detailed change tracking.
- **Notification Center:**
    - In-app notifications for system events.
- **Comment Moderation:**
    - Approve, reject, and spam workflow for all comments.
    - Bulk actions for efficient moderation.
- **Frontend Admin Bar:**
    - Contextual admin bar on public pages for logged-in users — quick edit and preview links.
- **Powerful Shortcode & Snippet System:**
    - Create custom PHP, CSS, or JS snippets in the admin panel.
    - Snippets automatically generate **Shortcodes** that can be embedded anywhere.
    - Navigation Menus also act as shortcodes for flexible placement.
- **Built-in Documentation System:**
    - Comprehensive manual accessible right inside the admin dashboard (`wp-admin/docs.php`).
    - **Bilingual Support** (English & Indonesian).
    - **Live Search** with keyboard navigation (`/` shortcut).
    - Sticky sidebar with an accordion-style hierarchy.
    - Covers all features including Form Builder, Kanban, 2FA, Analytics, SMTP, and more.
- **Admin Dashboard & Management:**
    - Customisable widget dashboard (personalised per user).
    - Post, Page, and Media Library management.
    - Appearance & Menu builder.
    - User Management, Roles & Capabilities Customiser.
- **Security & Structure:**
    - Admin Authentication and Protected API endpoints.
    - Brute-force login protection with lockout.
    - Two-Factor Authentication (OTP via email + backup codes).
    - Dynamic routing (Pretty URLs) and SEO-friendly frontend (Open Graph, Twitter Cards).
    - Database Backup & Restore interface.
    - Export / Import content tools.
    - Audit Log for full admin action history.

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/s4rt4/wp-admin.git
    ```

2.  **Database Setup:**
    - Create a new MySQL database named `wp-admin` (or adjust config).
    - Import the provided SQL file: `wp-admin/wp-admin.sql`.

3.  **Configuration:**
    - Open `wp-admin/db_config.php`.
    - Adjust the database credentials if necessary:
        ```php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'wp-admin');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        ```

4.  **Run:**
    - Serve the project via your preferred web server (Apache/Nginx) or use a local environment like Laragon/XAMPP.
    - Access the admin panel at `/wp-admin/`.
    - Compatible with both subdirectory (`localhost/word-press/`) and virtualhost (`word-press.test/`) setups.

## Default Admin Login

Use the following credentials to access the admin panel:

- **Username:** `admin`
- **Password:** `admin123`

## Tech Stack

- **Backend:** Native PHP (No frameworks).
- **Frontend:** HTML5, CSS3, Vanilla JavaScript.
- **Database:** MySQL.
- **Libraries/Editors:** GrapesJS, Editor.js, Toast UI Editor, Monaco Editor, Chart.js, SortableJS, Font Awesome 6.

## Disclaimer

This project is a hobby and not for commercial use. Any resemblance to WordPress is intentional as a tribute from a fan, and is not intended to be a copy or a competitor.
