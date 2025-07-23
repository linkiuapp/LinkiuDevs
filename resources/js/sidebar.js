document.addEventListener('DOMContentLoaded', function() {
    // Variables
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mobileSidebarToggle = document.querySelector('.sidebar-mobile-toggle');
    const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');

    // Funcionalidad del sidebar
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', () => {
            sidebar.classList.remove('show');
        });
    }

    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('show');
        });
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }
});
