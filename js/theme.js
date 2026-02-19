document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Theme from LocalStorage
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.dataset.theme = savedTheme;

    // 2. Setup Toggle Button Logic
    const themeBtn = document.getElementById('theme-toggle-nav');
    if (themeBtn) {
        const icon = themeBtn.querySelector('i');

        // Set initial icon
        if (savedTheme === 'dark') {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }

        // Click Handler
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.body.dataset.theme;
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            // Apply
            document.body.dataset.theme = newTheme;
            localStorage.setItem('theme', newTheme);

            // Update Icon
            if (newTheme === 'dark') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        });
    }
    // 3. Mobile Menu Logic
    const mobileBtn = document.getElementById('mobile-menu-toggle');
    const closeBtn = document.getElementById('closeMenuBtn');
    const navRight = document.getElementById('navbar-right');
    const overlay = document.getElementById('mobileMenuOverlay');

    if (mobileBtn && navRight && overlay) {

        function openMenu() {
            navRight.classList.add('active');
            overlay.classList.add('active');
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            navRight.classList.remove('active');
            overlay.classList.remove('active');
            // Restore body scroll
            document.body.style.overflow = '';

            // Clear any lingering inline styles
            navRight.style.right = '';
            navRight.style.display = '';
        }

        // Open Click
        mobileBtn.addEventListener('click', openMenu);

        // Close Clicks
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
    }
});
