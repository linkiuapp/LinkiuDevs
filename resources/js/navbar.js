document.addEventListener('DOMContentLoaded', function() {
    // User menu functionality
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');

    if (userMenuButton && userDropdown) {
        userMenuButton.addEventListener('click', function() {
            userDropdown.classList.toggle('hidden');
            this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
        });

        // Close the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
                userMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Theme toggle functionality
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    if (themeToggleBtn) {
        // Check user preference and system theme
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        themeToggleBtn.addEventListener('click', function() {
            // Toggle theme
            document.documentElement.classList.toggle('dark');
            
            // Update localStorage
            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('color-theme', 'dark');
            } else {
                localStorage.setItem('color-theme', 'light');
            }
        });
    }

    // Mobile search toggle
    const mobileSearchBtn = document.querySelector('button[aria-label="Search"]');
    const searchInput = document.getElementById('search');
    
    if (mobileSearchBtn && searchInput) {
        mobileSearchBtn.addEventListener('click', function() {
            searchInput.classList.toggle('hidden');
            searchInput.focus();
        });
    }
});
