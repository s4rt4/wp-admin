document.addEventListener("DOMContentLoaded", function () {

    // === DEFINISI ELEMEN UTAMA ===
    const bodyElement = document.body;
    const allMenuLinks = document.querySelectorAll("#adminmenu > li > a");
    const allSubmenuLinks = document.querySelectorAll("#adminmenu .wp-submenu a");

    // === Tombol Bantuan & Opsi Layar ===
    const helpButton = document.getElementById("contextual-help-link");
    const optionsButton = document.getElementById("show-settings-link");
    const helpPanel = document.getElementById("contextual-help-wrap");
    const optionsPanel = document.getElementById("screen-options-wrap");

    if (helpButton && helpPanel) {
        helpButton.addEventListener("click", function () {
            helpPanel.classList.toggle("hidden");
            if (optionsPanel && !optionsPanel.classList.contains("hidden")) {
                optionsPanel.classList.add("hidden");
            }
        });
    }

    if (optionsButton && optionsPanel) {
        optionsButton.addEventListener("click", function () {
            optionsPanel.classList.toggle("hidden");
            if (helpPanel && !helpPanel.classList.contains("hidden")) {
                helpPanel.classList.add("hidden");
            }
        });
    }

    // === Tombol Collapse (DENGAN PERBAIKAN BUG) ===
    const collapseButton = document.getElementById("collapse-button");

    if (collapseButton && bodyElement) {
        collapseButton.addEventListener("click", function () {
            // 1. Toggle status collapse
            bodyElement.classList.toggle("is-collapsed");

            // --- INI ADALAH PERBAIKAN BUG ---
            // 2. Saat sidebar di-toggle, paksa hapus semua status 'open'/'current'
            //    untuk menutup semua submenu yang mungkin terbuka.
            allMenuLinks.forEach(function (menuLink) {
                menuLink.closest('li').classList.remove("wp-has-current-submenu", "menu-open");
                menuLink.classList.remove("current");
            });
            allSubmenuLinks.forEach(function (submenuLink) {
                submenuLink.classList.remove("current");
            });
            // --- AKHIR PERBAIKAN BUG ---
        });
    }

    // === Logika Klik Menu Utama ===
    allMenuLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {

            const clickedLi = link.closest("li");
            if (!clickedLi) return;

            // Hanya cegah navigasi jika menu ini memiliki submenu
            if (clickedLi.classList.contains("wp-has-submenu")) {
                event.preventDefault();
            } else {
                // Jika tidak punya submenu, biarkan navigasi berjalan normal (seperti Back to Admin)
                return;
            }

            // Jangan lakukan apa-apa jika sidebar sedang ditutup (mode pop-up)
            if (bodyElement.classList.contains("is-collapsed")) {
                return;
            }
            // Cek apakah menu yang diklik SUDAH aktif
            const wasActive = clickedLi.classList.contains("wp-has-current-submenu");

            // 1. Hapus semua status aktif dari SEMUA link (termasuk submenu)
            allMenuLinks.forEach(function (menuLink) {
                menuLink.closest('li').classList.remove("wp-has-current-submenu", "menu-open");
                menuLink.classList.remove("current");
            });
            allSubmenuLinks.forEach(function (submenuLink) {
                submenuLink.classList.remove("current");
            });

            // 2. Jika menu yang diklik tadi BELUM aktif, aktifkan sekarang
            if (!wasActive) {
                clickedLi.classList.add("wp-has-current-submenu");
                link.classList.add("current");

                // Jika punya submenu, buka juga
                if (clickedLi.classList.contains("wp-has-submenu")) {
                    clickedLi.classList.add("menu-open");

                    // Tambahkan .current ke item submenu PERTAMA
                    const firstSubmenuLink = clickedLi.querySelector('.wp-submenu li.wp-first-item a');
                    if (firstSubmenuLink) {
                        firstSubmenuLink.classList.add("current");
                    }
                }
            }
            // Jika menu yang diklik TADI SUDAH aktif, langkah 1 di atas
            // (menghapus semua 'current') sudah cukup untuk menutupnya.
        });
    });

});