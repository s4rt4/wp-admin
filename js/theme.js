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
    const navRight = document.getElementById('navbar-right');

    if (mobileBtn && navRight) {
        // Initial check
        if (window.innerWidth <= 768) {
            mobileBtn.style.display = 'block';
        }

        // Toggle Click
        mobileBtn.addEventListener('click', () => {
            navRight.classList.toggle('active');
        });

        // Resize Handler
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                mobileBtn.style.display = 'block';
            } else {
                mobileBtn.style.display = 'none';
                navRight.classList.remove('active');
            }
        });
    }
});
