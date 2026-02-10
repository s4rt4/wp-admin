# Custom PHP CMS

A lightweight, custom-built Content Management System (CMS) using native PHP. This project started as a simple admin panel and has evolved into a fully functional CMS with advanced page building capabilities.

## Features

- **Dual Page Builders:**
    - **GrapesJS:** For visual, drag-and-drop page design (perfect for Landing Pages).
    - **Editor.js:** For structured, block-based content editing (perfect for Articles/Blog posts).
    - **SunEditor:** WYSIWYG editor for standard posts.
- **Admin Dashboard:**
    - Visitor Statistics (Page Views, Daily Visitors).
    - Post & Page Management.
    - User Management (Roles, Profile Pictures).
    - Comment Moderation System.
- **Media Manager:**
    - Upload and manage images.
    - Robust file handling.
- **Frontend:**
    - Dynamic routing (Pretty URLs).
    - SEO-friendly structure.
    - Comment system and Social Share integration.
- **Security:**
    - Admin Authentication.
    - Protected API endpoints.

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

## standard Credentials

- **Username:** `admin`
- **Password:** `admin123`

## Tech Stack

- **Backend:** Native PHP (No heavy frameworks).
- **Frontend:** HTML5, CSS3, JavaScript.
- **Database:** MySQL.
- **Libraries:** GrapesJS, Editor.js, SunEditor, Chart.js, DataTables.
