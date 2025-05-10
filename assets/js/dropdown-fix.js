/**
 * GoWork Dropdown Menu Fix
 * This file contains JavaScript fixes for dropdown menu issues
 * Modified to only target navigation menu dropdowns in header.php and index.php
 * Updated to use click-only functionality per user request
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("Navbar dropdown fix loaded - ensuring navbar dropdowns work properly");
    
    // Define a constant to track if dropdowns are initialized
    window.NAVBAR_DROPDOWNS_INITIALIZED = true;
    
    // Make sure Bootstrap's built-in dropdown functionality works
    // This needs to be reliable even if bootstrap is loaded elsewhere
    if (typeof bootstrap !== 'undefined') {
        console.log("Initializing navbar dropdowns with Bootstrap");
        // Only target navbar dropdowns specifically to avoid conflicts with job filter dropdowns
        document.querySelectorAll('nav.navbar .dropdown-toggle').forEach(dropdown => {
            // Dispose of any existing dropdown instance before creating a new one
            const existingDropdown = bootstrap.Dropdown.getInstance(dropdown);
            if (existingDropdown) {
                existingDropdown.dispose();
            }
            
            // Create new dropdown with proper configuration
            new bootstrap.Dropdown(dropdown, {
                // Prevent automatic closing when clicking inside the dropdown
                autoClose: 'outside'
            });
        });
    } else {
        console.warn("Bootstrap not available for dropdowns - waiting for it to load");
        // Wait for Bootstrap to be available
        const checkBootstrap = setInterval(function() {
            if (typeof bootstrap !== 'undefined') {
                console.log("Bootstrap now available - initializing dropdowns");
                document.querySelectorAll('nav.navbar .dropdown-toggle').forEach(dropdown => {
                    new bootstrap.Dropdown(dropdown, {
                        autoClose: 'outside'
                    });
                });
                clearInterval(checkBootstrap);
            }
        }, 100);
    }
    
    // Click functionality for all screen sizes
    document.querySelectorAll('nav.navbar .dropdown-toggle').forEach(toggleBtn => {
        toggleBtn.addEventListener('click', function(e) {
            // Don't prevent default or stop propagation - let Bootstrap handle it
            // Just add extra handling
            
            const dropdown = this.nextElementSibling;
            if (dropdown) {
                console.log("Navbar dropdown clicked:", this.textContent.trim());
                
                // Close all other navbar dropdowns (optional, Bootstrap should handle this)
                document.querySelectorAll('nav.navbar .dropdown-menu').forEach(menu => {
                    if (menu !== dropdown && menu.classList.contains('show')) {
                        menu.classList.remove('show');
                    }
                });
                
                // Explicitly toggle show class for extra reliability
                setTimeout(() => {
                    if (!dropdown.classList.contains('show')) {
                        dropdown.classList.add('show');
                    }
                }, 0);
            }
        });
    });
    
    // Make sure dropdown menus get the show class
    document.querySelectorAll('nav.navbar .dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('shown.bs.dropdown', function() {
            if (this.nextElementSibling) {
                this.nextElementSibling.classList.add('show');
            }
        });
    });
    
    // Close navbar dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Only if not clicking on a dropdown or its toggle
        const isNavbarDropdownOrToggle = e.target.closest('nav.navbar .dropdown-menu') || 
                                          e.target.closest('nav.navbar .dropdown-toggle');
        
        if (!isNavbarDropdownOrToggle) {
            document.querySelectorAll('nav.navbar .dropdown-menu.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
    
    // Make sure dropdowns are always initialized on all pages
    // Reapply after any AJAX page updates
    function ensureDropdownsWork() {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('nav.navbar .dropdown-toggle').forEach(dropdown => {
                if (!bootstrap.Dropdown.getInstance(dropdown)) {
                    new bootstrap.Dropdown(dropdown, {
                        autoClose: 'outside'
                    });
                }
            });
        }
    }
    
    // Run this again after a short delay to catch any late-loading elements
    setTimeout(ensureDropdownsWork, 500);
    
    // Expose the function globally so it can be called after AJAX updates
    window.ensureNavbarDropdownsWork = ensureDropdownsWork;
}); 