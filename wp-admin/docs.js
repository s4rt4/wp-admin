/**
 * docs.js — JavaScript untuk halaman Dokumentasi
 * Menangani: Live Search, Keyboard Navigation
 */

(function () {
    'use strict';

    // ----------------------------------------------------------------
    // 1. INDEX — semua topik yang bisa dicari
    // ----------------------------------------------------------------
    const DOCS_INDEX = [
        // Dashboard
        { title: 'Dashboard Home', section: 'Dashboard', topic: 'dashboard-home', keywords: 'dashboard ringkasan overview widget' },
        // Posts
        { title: 'All Posts', section: 'Posts', topic: 'posts-all', keywords: 'semua postingan daftar artikel' },
        { title: 'Add New Post', section: 'Posts', topic: 'posts-new', keywords: 'tambah postingan baru tulis artikel editor' },
        { title: 'Featured Posts', section: 'Posts', topic: 'posts-featured', keywords: 'postingan unggulan featured' },
        { title: 'Published Posts', section: 'Posts', topic: 'posts-published', keywords: 'postingan terbit published live' },
        { title: 'Draft Posts', section: 'Posts', topic: 'posts-drafts', keywords: 'draft postingan konsep belum terbit' },
        { title: 'Categories', section: 'Posts', topic: 'posts-categories', keywords: 'kategori postingan kelompok' },
        { title: 'Tags', section: 'Posts', topic: 'posts-tags', keywords: 'tag label topik kata kunci' },
        // Media
        { title: 'Media Library', section: 'Media', topic: 'media-library', keywords: 'perpustakaan media gambar foto video file' },
        { title: 'Add New Media', section: 'Media', topic: 'media-new', keywords: 'upload media baru gambar video file' },
        // Pages
        { title: 'All Pages', section: 'Pages', topic: 'pages-all', keywords: 'semua halaman daftar pages statis' },
        { title: 'Add New Page', section: 'Pages', topic: 'pages-new', keywords: 'tambah halaman baru builder pilih' },
        { title: 'GrapesJS Editor', section: 'Pages', topic: 'pages-builder-grapesjs', keywords: 'grapesjs drag drop visual widget builder blok section' },
        { title: 'Editor.js', section: 'Pages', topic: 'pages-builder-editorjs', keywords: 'editorjs block editor blok konten teks' },
        { title: 'Monaco Editor', section: 'Pages', topic: 'pages-builder-monaco', keywords: 'monaco code editor html css js kode' },
        // Appearance
        { title: 'Customize', section: 'Appearance', topic: 'appearance-themes', keywords: 'kustomisasi tampilan warna tema header footer' },
        { title: 'Menus', section: 'Appearance', topic: 'appearance-menus', keywords: 'menu navigasi nav link tautan sidebar' },
        // Settings
        { title: 'General Settings', section: 'Settings', topic: 'settings-general', keywords: 'pengaturan umum judul situs bahasa timezone' },
        { title: 'Writing Settings', section: 'Settings', topic: 'settings-writing', keywords: 'pengaturan penulisan kategori default format' },
        { title: 'Reading Settings', section: 'Settings', topic: 'settings-reading', keywords: 'pengaturan membaca halaman depan feed rss' },
        { title: 'Media Settings', section: 'Settings', topic: 'settings-media', keywords: 'pengaturan media ukuran gambar thumbnail' },
        { title: 'Permalink Settings', section: 'Settings', topic: 'settings-permalinks', keywords: 'permalink url struktur seo friendly' },
        // Users
        { title: 'All Users', section: 'Users', topic: 'users-all', keywords: 'semua pengguna daftar user' },
        { title: 'Add New User', section: 'Users', topic: 'users-new', keywords: 'tambah pengguna baru user register' },
        { title: 'User Profile', section: 'Users', topic: 'users-profile', keywords: 'profil pengguna edit akun password email' },
        // Tools
        { title: 'Database Backup & Restore', section: 'Tools', topic: 'tools-db', keywords: 'backup restore database sql cadangan' },
        { title: 'Import / Export', section: 'Tools', topic: 'tools-io', keywords: 'import export konten xml migrasi pindah' },
        { title: 'Site Health', section: 'Tools', topic: 'tools-health', keywords: 'site health kesehatan situs cek status performa keamanan' },
        { title: 'Snippets', section: 'Tools', topic: 'tools-snippets', keywords: 'snippets kode kustom php css js custom code' },
        { title: 'Tag Manager', section: 'Tools', topic: 'tools-tm', keywords: 'tag manager gtm google analytics facebook pixel tracking script' },
        { title: 'Form Builder', section: 'Tools', topic: 'tools-form-builder', keywords: 'form builder formulir kontak hubungi contact us input field' },
        { title: 'Audit Log', section: 'Tools', topic: 'tools-audit-log', keywords: 'audit log aktivitas history riwayat pengguna perubahan' },
        { title: 'Notification Center', section: 'Tools', topic: 'tools-notifications', keywords: 'notifikasi notification center pesan alert' },
        { title: 'Analytics', section: 'Tools', topic: 'tools-analytics', keywords: 'analytics statistik pengunjung traffic halaman views' },
        // Comments
        { title: 'All Comments', section: 'Comments', topic: 'comments-all', keywords: 'komentar semua daftar moderasi' },
        { title: 'Comment Moderation', section: 'Comments', topic: 'comments-moderation', keywords: 'moderasi komentar approve reject spam pending' },
        // Appearance (extended)
        { title: 'Dark Mode', section: 'Appearance', topic: 'appearance-darkmode', keywords: 'dark mode gelap tema malam night mode toggle' },
        // Settings (extended)
        { title: 'SMTP Email', section: 'Settings', topic: 'settings-smtp', keywords: 'smtp email konfigurasi mail server kirim pengiriman gmail' },
        { title: 'Role Menu Visibility', section: 'Settings', topic: 'settings-role-visibility', keywords: 'role visibility visibilitas menu role pengguna sembunyikan sidebar akses' },
        // Users (extended)
        { title: 'User Roles', section: 'Users', topic: 'users-roles', keywords: 'role pengguna permission izin kapabilitas editor author contributor subscriber' },
        { title: 'Two-Factor Auth (2FA)', section: 'Users', topic: 'users-2fa', keywords: '2fa two factor auth otentikasi dua faktor otp kode keamanan' },
        { title: 'Password Reset', section: 'Users', topic: 'users-password-reset', keywords: 'reset password lupa forgot email tautan link token' },
        // Dashboard (extended)
        { title: 'Dashboard Widgets', section: 'Dashboard', topic: 'dashboard-widgets', keywords: 'widget dashboard statistik world clock sticky notes todo rss cuaca weather kalender kanban' },
        { title: 'Kanban Board', section: 'Dashboard', topic: 'tools-kanban', keywords: 'kanban board tugas task kolom drag drop project' },
        // Posts (extended)
        { title: 'Scheduled Publishing', section: 'Posts', topic: 'posts-scheduled', keywords: 'jadwal terbit scheduled publish posting otomatis waktu' },
        { title: 'Content Lock', section: 'Posts', topic: 'posts-content-lock', keywords: 'content lock kunci konten password proteksi artikel' },
        { title: 'Multi-language', section: 'Posts', topic: 'posts-multilang', keywords: 'multi language bahasa indonesia inggris terjemahan translation bilingual lang flag' },
        { title: 'SEO Settings', section: 'Posts', topic: 'posts-seo', keywords: 'seo meta title description keyword preview google snippet analysis score optimasi pencarian' },
        { title: 'Content Calendar', section: 'Posts', topic: 'posts-calendar', keywords: 'calendar kalender bulan jadwal scheduled published konten rencana plan' },
        { title: 'Custom Fields', section: 'Posts', topic: 'posts-custom-fields', keywords: 'custom fields metadata post meta key value field tambahan data ekstra' },
        { title: 'Bulk SEO Editor', section: 'Posts', topic: 'posts-seo-editor', keywords: 'bulk seo editor spreadsheet meta title description slug focus keyword massal edit batch' },
        { title: 'Trash / Recycle Bin', section: 'Posts', topic: 'posts-trash', keywords: 'trash recycle bin hapus sampah restore pulihkan permanent delete kosongkan' },
        { title: 'Revision Diff Viewer', section: 'Posts', topic: 'posts-diff', keywords: 'revision diff viewer compare bandingkan versi perbandingan side by side riwayat perubahan' },
        { title: 'Related Posts', section: 'Posts', topic: 'posts-related', keywords: 'related posts terkait hubungan relasi artikel saran suggestion link' },
        // Tools (extended - Tier 1 Batch 3)
        { title: 'CSV Import/Export', section: 'Tools', topic: 'tools-csv', keywords: 'csv import export bulk massal post artikel download upload spreadsheet excel' },
        { title: 'Redirects Manager', section: 'Tools', topic: 'tools-redirects', keywords: 'redirect 301 302 url pindah seo alihkan alamat forward rewrite manager' },
        { title: 'Maintenance Mode', section: 'Settings', topic: 'settings-maintenance', keywords: 'maintenance mode pemeliharaan offline 503 under construction tutup sementara' },
        // Users (extended - Tier 1 Batch 3)
        { title: 'User Activity', section: 'Users', topic: 'users-activity', keywords: 'user activity aktivitas online offline last login terakhir aktif status pengguna' },
        { title: 'REST API', section: 'Tools', topic: 'tools-rest-api', keywords: 'rest api token crud endpoint headless json bearer authentication external integrasi' },
        { title: 'Data Explorer', section: 'Tools', topic: 'tools-data-explorer', keywords: 'data explorer database tabel browse query csv export read only admin jelajah' },
        { title: 'Form Analytics', section: 'Tools', topic: 'tools-form-analytics', keywords: 'form analytics submission trends chart grafik formulir respons statistik' },
        { title: 'Audit Dashboard', section: 'Tools', topic: 'tools-audit-dashboard', keywords: 'audit dashboard timeline activity chart grafik aktivitas log user action keamanan' },
        { title: 'Plugins', section: 'Plugins', topic: 'tools-plugins', keywords: 'plugin hook event action filter extend ekstensi tambahan addon developer guide' },
        { title: 'Messages', section: 'Messages', topic: 'tools-messages', keywords: 'messages pesan internal inbox sent compose reply balas chat komunikasi' },
        { title: 'Security Headers', section: 'Settings', topic: 'settings-security', keywords: 'security headers csp hsts x-frame referrer policy keamanan header' },
        { title: 'File Integrity', section: 'Settings', topic: 'settings-integrity', keywords: 'file integrity hash sha256 scan baseline monitor integritas' },
        { title: 'Login Security', section: 'Settings', topic: 'settings-login-security', keywords: 'login security failed attempts ip block session force logout keamanan masuk' },
        { title: 'Updates', section: 'Settings', topic: 'settings-updates', keywords: 'update pembaruan version versi migration migrasi github release' },
        { title: 'Media Editor', section: 'Media', topic: 'media-editor', keywords: 'media editor crop resize rotate flip image gambar edit potong ukuran putar' },
        { title: 'Multi-site', section: 'Settings', topic: 'settings-multisite', keywords: 'multi site multisite beberapa situs domain prefix tabel network kelola' },
        // Media (extended)
        { title: 'Image Optimizer', section: 'Media', topic: 'media-image-optimizer', keywords: 'optimasi gambar compress kompresi resize image optimizer' },
        { title: 'Media Folders', section: 'Media', topic: 'media-folders', keywords: 'folder media virtual organisasi drag drop file library kelola' },
        // Tools (Tier 4)
        { title: 'Automations', section: 'Tools', topic: 'tools-automations', keywords: 'automation workflow trigger action email webhook otomatisasi alur kerja condition send' },
    ];

    // ----------------------------------------------------------------
    // 2. Helpers
    // ----------------------------------------------------------------
    function getCurrentLang() {
        const m = location.search.match(/[?&]lang=([^&]+)/);
        if (m) return m[1];
        // try cookie / session via DOM (lang-switcher active link)
        const active = document.querySelector('.docs-lang-switcher a.active');
        return active ? active.textContent.toLowerCase().trim() : 'id';
    }

    function buildUrl(topic) {
        const lang = getCurrentLang();
        return `docs.php?topic=${topic}&lang=${lang}`;
    }

    function highlight(text, query) {
        if (!query) return text;
        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>');
    }

    function search(query) {
        const q = query.trim().toLowerCase();
        if (!q || q.length < 2) return [];
        return DOCS_INDEX.filter(item => {
            return item.title.toLowerCase().includes(q)
                || item.section.toLowerCase().includes(q)
                || item.keywords.toLowerCase().includes(q);
        }).slice(0, 8);
    }

    // ----------------------------------------------------------------
    // 3. DOM Setup
    // ----------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('docs-search-input');
        const resultsBox = document.getElementById('docs-search-results');

        if (!input || !resultsBox) return;

        let focusedIndex = -1;

        function renderResults(items, query) {
            resultsBox.innerHTML = '';
            if (items.length === 0) {
                resultsBox.innerHTML = `<div class="docs-search-empty">Tidak ditemukan untuk "<strong>${query}</strong>"</div>`;
                resultsBox.classList.add('active');
                return;
            }

            const inner = document.createElement('div');
            inner.className = 'docs-search-results-inner';

            items.forEach((item, i) => {
                const el = document.createElement('a');
                el.className = 'docs-search-result-item';
                el.href = buildUrl(item.topic);
                el.setAttribute('tabindex', '-1');
                el.innerHTML = `
                    <span class="result-title">${highlight(item.title, query)}</span>
                    <span class="result-section">${item.section}</span>
                `;
                el.addEventListener('mouseenter', () => {
                    focusedIndex = i;
                    updateFocus(inner);
                });
                inner.appendChild(el);
            });

            resultsBox.appendChild(inner);
            resultsBox.classList.add('active');
            focusedIndex = -1;
        }

        function updateFocus(inner) {
            const items = inner.querySelectorAll('.docs-search-result-item');
            items.forEach((el, i) => {
                el.classList.toggle('focused', i === focusedIndex);
            });
        }

        function hideResults() {
            resultsBox.classList.remove('active');
            focusedIndex = -1;
        }

        // Input event
        let debounceTimer;
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const q = this.value;
            if (!q.trim() || q.length < 2) {
                hideResults();
                return;
            }
            debounceTimer = setTimeout(() => {
                const results = search(q);
                renderResults(results, q.trim());
            }, 180);
        });

        // Keyboard navigation
        input.addEventListener('keydown', function (e) {
            const inner = resultsBox.querySelector('.docs-search-results-inner');
            if (!inner) return;
            const items = inner.querySelectorAll('.docs-search-result-item');
            const total = items.length;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusedIndex = Math.min(focusedIndex + 1, total - 1);
                updateFocus(inner);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusedIndex = Math.max(focusedIndex - 1, -1);
                updateFocus(inner);
            } else if (e.key === 'Enter') {
                if (focusedIndex >= 0 && items[focusedIndex]) {
                    e.preventDefault();
                    window.location.href = items[focusedIndex].href;
                } else if (this.value.trim()) {
                    // Navigate to first result
                    const results = search(this.value);
                    if (results.length > 0) {
                        window.location.href = buildUrl(results[0].topic);
                    }
                }
            } else if (e.key === 'Escape') {
                hideResults();
                this.blur();
            }
        });

        // Click outside to close
        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
                hideResults();
            }
        });

        // Focus shows results again if there's a query
        input.addEventListener('focus', function () {
            if (this.value.trim().length >= 2) {
                const results = search(this.value);
                if (results.length > 0) renderResults(results, this.value.trim());
            }
        });

        // ----------------------------------------------------------------
        // 4. Keyboard shortcut: / to focus search
        // ----------------------------------------------------------------
        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && document.activeElement !== input
                && document.activeElement.tagName !== 'INPUT'
                && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                input.focus();
                input.select();
            }
        });

        // ----------------------------------------------------------------
        // 5. Auto-open submenu for current page in sidebar
        // ----------------------------------------------------------------
        const currentItems = document.querySelectorAll('.docs-menu .wp-submenu li.current');
        currentItems.forEach(li => {
            const parentLi = li.closest('li.wp-has-submenu');
            if (parentLi) {
                parentLi.classList.add('wp-menu-open');
            }
        });

        // ----------------------------------------------------------------
        // 6. Sidebar Accordion Toggle
        // ----------------------------------------------------------------
        const parentLinks = document.querySelectorAll('.docs-menu li.wp-has-submenu > a');
        parentLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // Mencegah pindah halaman
                const parentLi = this.closest('li.wp-has-submenu');

                // Toggle kelas 'wp-menu-open'
                if (parentLi.classList.contains('wp-menu-open')) {
                    parentLi.classList.remove('wp-menu-open');
                } else {
                    // (Opsional) Tutup menu lain yang sedang terbuka
                    // document.querySelectorAll('.docs-menu li.wp-has-submenu').forEach(li => li.classList.remove('wp-menu-open'));

                    parentLi.classList.add('wp-menu-open');
                }
            });
        });
    });
})();
