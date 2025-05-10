/**
 * Jobs search bar filtering functionality
 * Strictly isolated to only handle the search bar inputs
 */

(function() {
    // Use an IIFE to avoid polluting the global namespace
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Jobs search script loaded');
        
        // CRITICALLY IMPORTANT: Only target the main search form elements,
        // never touch dropdown filter elements
        const searchForm = document.getElementById("job-search-form");
        if (!searchForm) {
            console.log("Search form not found, search filtering not initialized");
            return;
        }
        
        // Explicitly target only the search bar elements by their specific IDs
        // and ensure they belong to the search form, not the filter dropdown
        const keywordsInput = searchForm.querySelector("#keywords");
        const locationInput = searchForm.querySelector("#location");
        
        // Only proceed if we're on the jobs listing page with search inputs
        if (!keywordsInput || !locationInput) {
            console.log("Search inputs not found, search filtering not initialized");
            return; 
        }
        
        // Verify we're on the jobs listing page (not single job view)
        const jobCards = document.querySelectorAll(".job-card");
        if (jobCards.length === 0) {
            console.log("No job cards found, search filtering not initialized");
            return;
        }
        
        console.log("Search filter initialized for search bar only");
        
        // Function to filter jobs based on keywords and location
        function filterJobs(event) {
            // IMPORTANT: Only process events from the search form, never from dropdown
            if (event && event.target) {
                // Check if the event originated from the dropdown filter
                const filterForm = document.getElementById("advanced-filter-form");
                if (filterForm && filterForm.contains(event.target)) {
                    console.log("Ignoring event from dropdown filter");
                    return; // Do not process events from the dropdown
                }
            }
            
            console.log("Running search bar filtering");
            
            const keywordsValue = keywordsInput.value.toLowerCase().trim();
            const locationValue = locationInput.value.toLowerCase().trim();
            
            // Get elements for filter display
            const activeFiltersContainer = document.querySelector('.active-search-filters');
            const keywordFilterBadge = document.querySelector('.keyword-filter');
            const locationFilterBadge = document.querySelector('.location-filter');
            const serverSideFilters = document.querySelector('.server-side-filters');
            
            // Hide server-side filters when JS is active
            if (serverSideFilters) {
                serverSideFilters.style.display = 'none';
            }
            
            if (activeFiltersContainer && keywordFilterBadge && locationFilterBadge) {
                // Update filter display only for search bar filters
                const searchFilterActive = keywordsValue !== '' || locationValue !== '';
                activeFiltersContainer.style.display = searchFilterActive ? 'block' : 'none';
                
                // Update search filter badges
                if (keywordsValue) {
                    keywordFilterBadge.textContent = `Keywords: ${keywordsValue}`;
                    keywordFilterBadge.style.display = 'inline-block';
                } else {
                    keywordFilterBadge.style.display = 'none';
                }
                
                if (locationValue) {
                    locationFilterBadge.textContent = `Location: ${locationValue}`;
                    locationFilterBadge.style.display = 'inline-block';
                } else {
                    locationFilterBadge.style.display = 'none';
                }
            }
            
            // For each job card, check if it matches the filters
            let visibleCount = 0;
            
            jobCards.forEach(card => {
                try {
                    const cardText = card.textContent.toLowerCase();
                    const jobDetails = card.querySelectorAll('.job-detail');
                    const jobLocation = jobDetails.length > 0 ? jobDetails[0].textContent.toLowerCase() : '';
                    
                    // Simple matching for search bar filters only
                    const matchesKeyword = keywordsValue === '' || cardText.includes(keywordsValue);
                    const matchesLocation = locationValue === '' || jobLocation.includes(locationValue);
                    
                    // Show/hide based on search filters only
                    if (matchesKeyword && matchesLocation) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error processing job card:', error);
                    // Keep card visible in case of error
                    card.style.display = '';
                    visibleCount++;
                }
            });
            
            // Update job count
            const jobCountElement = document.querySelector('.job-count');
            if (jobCountElement) {
                jobCountElement.textContent = `${visibleCount} jobs found`;
            }
            
            // Show/hide no results message
            let noResultsMessage = document.querySelector('.no-results-message');
            
            // If visible count is 0 and no results message doesn't exist, create it
            if (visibleCount === 0 && !noResultsMessage) {
                console.log("Creating no results message");
                noResultsMessage = document.createElement('div');
                noResultsMessage.className = 'alert alert-info no-results-message';
                noResultsMessage.innerHTML = '<i class="fas fa-info-circle"></i> No jobs found matching your search criteria. Please try different keywords or location.';
                
                // Find the proper place to insert it - after the job count element
                const jobListArea = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-3');
                if (jobListArea && jobListArea.parentNode) {
                    jobListArea.parentNode.insertBefore(noResultsMessage, jobListArea.nextSibling);
                } else {
                    // Fallback - add to the job cards container
                    const jobCardsContainer = document.querySelector('.col-md-8');
                    if (jobCardsContainer) {
                        jobCardsContainer.appendChild(noResultsMessage);
                    }
                }
            }
            
            // If we now have a no results message element, show/hide as needed
            if (noResultsMessage) {
                if (visibleCount === 0) {
                    noResultsMessage.style.display = 'block';
                    
                    // Ensure any existing job cards are actually hidden
                    jobCards.forEach(card => {
                        card.style.display = 'none';
                    });
                    
                    // Hide job card container elements that might still be visible
                    const sortDropdown = document.querySelector('#sortDropdown');
                    if (sortDropdown && sortDropdown.closest('.d-flex')) {
                        sortDropdown.closest('.d-flex').style.display = visibleCount > 0 ? 'flex' : 'none';
                    }
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
            
            // Adjust the spacer if it exists
            const spacer = document.querySelector('.dropdown-spacer');
            if (spacer) {
                if (visibleCount === 0) {
                    spacer.style.height = '300px'; // Larger spacer when no results
                } else if (visibleCount === 1) {
                    spacer.style.height = '200px'; // Medium spacer for single result
                } else {
                    spacer.style.height = '70px';  // Small spacer for multiple results
                }
            }
        }
        
        // IMPORTANT: Use namespaced event handlers to ensure we can remove them if needed
        function handleKeywordsInput(event) {
            // Stop propagation to prevent any global handlers from catching this
            event.stopPropagation();
            filterJobs(event);
        }
        
        function handleLocationInput(event) {
            // Stop propagation to prevent any global handlers from catching this
            event.stopPropagation();
            filterJobs(event);
        }
        
        // Set up event listeners ONLY for the specific search input elements
        // Use explicit reference to the specific elements in the search form
        keywordsInput.addEventListener('input', handleKeywordsInput);
        locationInput.addEventListener('input', handleLocationInput);
        
        // Clear search button - only attach if it exists
        const clearSearchButton = searchForm.querySelector("#clear-search");
        if (clearSearchButton) {
            clearSearchButton.addEventListener("click", function(event) {
                // Stop propagation to prevent any global handlers
                event.stopPropagation();
                
                keywordsInput.value = "";
                locationInput.value = "";
                filterJobs();
            });
        }
        
        // Run initial filter to set up the page, but only for search filters
        filterJobs();
        
        // Disable any global event listeners that might interfere
        window.addEventListener('input', function(event) {
            // If the event originated from the dropdown filter, ignore it
            const filterForm = document.getElementById("advanced-filter-form");
            if (filterForm && filterForm.contains(event.target)) {
                // Do nothing - let the dropdown handle its own events
            }
        }, true); // Use capture phase to intercept events early
    });
    
    // Handle job application form if on a single job page
    document.addEventListener('DOMContentLoaded', function() {
        const applyForm = document.querySelector('.apply-form');
        if (!applyForm) return;
        
        applyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const jobId = this.getAttribute('data-job-id');
            const jobTitle = this.getAttribute('data-job-title');
            
            // Show loading state
            const applyButton = this.querySelector('.apply-button');
            if (applyButton) {
                applyButton.disabled = true;
                applyButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
            }
            
            // Submit the form via AJAX
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    if (applyButton) {
                        applyButton.classList.remove('btn-primary');
                        applyButton.classList.add('btn-success');
                        applyButton.innerHTML = '<i class="fas fa-check"></i> Applied Successfully';
                    }
                    
                    // Optional: Show alert
                    alert('Application submitted successfully for ' + jobTitle);
                    
                    // Disable form
                    const formInputs = this.querySelectorAll('input, textarea, button');
                    formInputs.forEach(input => input.disabled = true);
                    
                } else {
                    // Show error message
                    if (applyButton) {
                        applyButton.disabled = false;
                        applyButton.innerHTML = '<i class="fas fa-paper-plane"></i> Apply Now';
                    }
                    
                    alert('Error: ' + (data.message || 'Failed to submit application'));
                }
            })
            .catch(error => {
                console.error('Error submitting application:', error);
                
                // Reset button
                if (applyButton) {
                    applyButton.disabled = false;
                    applyButton.innerHTML = '<i class="fas fa-paper-plane"></i> Apply Now';
                }
            });
        });
    });
})();
