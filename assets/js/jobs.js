/**
 * Jobs page specific JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Clear filters button functionality
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the base URL without query parameters
            const baseUrl = window.location.pathname;
            
            // Preserve only the basic search if it exists
            const urlParams = new URLSearchParams(window.location.search);
            const keywords = urlParams.get('keywords');
            const location = urlParams.get('location');
            
            let newUrl = baseUrl;
            
            // Add back only basic search parameters if they exist
            const params = [];
            if (keywords) params.push(`keywords=${encodeURIComponent(keywords)}`);
            if (location) params.push(`location=${encodeURIComponent(location)}`);
            
            if (params.length > 0) {
                newUrl += '?' + params.join('&');
            }
            
            window.location.href = newUrl;
        });
    }
    
    // Handle filter form submission
    const filterForm = document.getElementById('advanced-filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Remove empty parameters to keep URL clean
            const formElements = this.elements;
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];
                if (element.type !== 'submit' && element.type !== 'button') {
                    if (element.value === '' || element.value === 'any') {
                        // If it's a checkbox array, only remove if none are checked
                        if (element.name.endsWith('[]')) {
                            const checkboxes = document.querySelectorAll(`input[name="${element.name}"]:checked`);
                            if (checkboxes.length === 0) {
                                element.disabled = true;
                            }
                        } else {
                            element.disabled = true;
                        }
                    }
                }
            }
        });
    }
    
    // No auto-submit functionality - form will only submit when the Apply Filters button is clicked
    // Instead, we'll just handle the form submission to clean up the URL parameters
});
