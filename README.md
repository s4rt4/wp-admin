# Custom PHP CMS

A lightweight, custom-built Content Management System (CMS) using native PHP. This project started as a simple admin panel and has evolved into a fully functional CMS with advanced page building capabilities, custom code snippets, and a built-in comprehensive documentation system.

## Features

- **Advanced Content Editors:**
    - **GrapesJS:** For visual, drag-and-drop page design (perfect for Landing Pages, features 49+ custom widgets).
    - **Editor.js:** For structured, block-based page building.
    - **Toast UI Editor:** A powerful Markdown and WYSIWYG editor for writing standard Posts and Articles.
    - **Monaco Editor:** A powerful IDE-like code editor for writing raw HTML, CSS, JavaScript, and PHP snippets.
- **Powerful Shortcode & Snippet System:**
    - Create custom PHP, CSS, or JS snippets in the admin panel.
    - Snippets automatically generate **Shortcodes** that can be embedded anywhere (in posts, pages, or even inside GrapesJS page builder).
    - Navigation Menus also act as shortcodes for flexible placement.
- **Built-in Documentation System:**
    - Comprehensive manual accessible right inside the admin dashboard (`wp-admin/docs.php`).
    - **Bilingual Support** (English & Indonesian).
    - **Live Search** with keyboard navigation (`/` shortcut).
    - Sticky sidebar with an accordion-style hierarchy.
- **Admin Dashboard & Management:**
    - Visitor Statistics (Page Views, Daily Visitors).
    - Post, Page, and Media Library management.
    - Appearance & Menu builder.
    - User Management & Roles.
- **Security & Structure:**
    - Admin Authentication and Protected API endpoints.
    - Dynamic routing (Pretty URLs) and SEO-friendly frontend.
    - Database Backup & Restore interface.
    - Export / Import content tools.

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

## Default Admin Login

Use the following credentials to access the admin panel:

- **Username:** `admin`
- **Password:** `admin123`

## Tech Stack

- **Backend:** Native PHP (No frameworks).
- **Frontend:** HTML5, CSS3, Vanilla JavaScript.
- **Database:** MySQL.
- **Libraries/Editors:** GrapesJS, Editor.js, Toast UI Editor, Monaco Editor, Chart.js.

## Disclaimer

This project is a hobby and not for commercial use. Any resemblance to WordPress is intentional as a tribute from a fan, and is not intended to be a copy or a competitor.
