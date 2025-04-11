require('./bootstrap');

// Import Bootstrap and Popper.js
import 'bootstrap';

// Import all of Font Awesome's icons
import '@fortawesome/fontawesome-free/js/all';

// Custom scripts
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    // Toggle sidebar
    document.querySelector('#sidebarToggle')?.addEventListener('click', function() {
        document.querySelector('body').classList.toggle('sidebar-collapsed');
    });
});
