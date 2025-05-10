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
            
            // Debug logging
            console.log("Search keywords:", keywordsValue);
            
            // Normalize keywords for job type matching
            // This will help match "part time" with "Part-time" and similar variations
            const normalizedKeywords = keywordsValue
                .replace(/part[ -]*time/gi, "part-time")
                .replace(/full[ -]*time/gi, "full-time")
                .replace(/work[ -]*from[ -]*home/gi, "work from home")
                .replace(/on[ -]*site/gi, "on-site")
                .replace(/night[ -]*shift/gi, "night shift")
                .replace(/per[ -]*diem/gi, "per diem")
                .replace(/entry[ -]*level/gi, "entry-level")
                .replace(/commission[ -]*based/gi, "commission-based")
                .replace(/tenure[ -]*track/gi, "tenure-track")
                .replace(/locum[ -]*tenens/gi, "locum tenens")
                .replace(/performing[ -]*artist/gi, "performing artist")
                .replace(/travel[ -]*nursing/gi, "travel nursing");
            
            // Debug logging
            if (normalizedKeywords !== keywordsValue) {
                console.log("Normalized keywords:", normalizedKeywords);
            }
            
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
            
            // Helper function to normalize job type strings by removing spaces and hyphens
            function normalizeJobType(jobType) {
                // First handle specific job types with multiple words/hyphens
                let normalized = jobType.toLowerCase()
                    .replace(/part[ -]*time/gi, "parttime")
                    .replace(/full[ -]*time/gi, "fulltime")
                    .replace(/work[ -]*from[ -]*home/gi, "workfromhome")
                    .replace(/on[ -]*site/gi, "onsite")
                    .replace(/night[ -]*shift/gi, "nightshift")
                    .replace(/per[ -]*diem/gi, "perdiem")
                    .replace(/entry[ -]*level/gi, "entrylevel")
                    .replace(/commission[ -]*based/gi, "commissionbased")
                    .replace(/tenure[ -]*track/gi, "tenuretrack")
                    .replace(/locum[ -]*tenens/gi, "locumtenens")
                    .replace(/performing[ -]*artist/gi, "performingartist")
                    .replace(/travel[ -]*nursing/gi, "travelnursing");
                    
                // Then remove all remaining spaces and hyphens
                return normalized.replace(/[- ]/g, '');
            }
            
            // Helper function for phrase matching with negative term scoring
            function scoreJobTypeMatch(searchTerm, jobType) {
                if (!searchTerm || !jobType) return 0;
                
                const search = searchTerm.toLowerCase();
                const job = jobType.toLowerCase();
                
                // Direct equality - highest score (10)
                if (job === search) return 10;
                
                // Normalized equality - high score (9)
                if (normalizeJobType(job) === normalizeJobType(search)) return 9;
                
                // Handle specific job type matches to avoid overlapping terms
                
                // Check for part-time related searches
                if (search.includes('part') && search.includes('time')) {
                    // Positive match for part-time
                    if (job.includes('part') && job.includes('time')) return 8;
                    // Negative match for full-time
                    if (job.includes('full') && job.includes('time')) return -5;
                }
                
                // Check for full-time related searches
                if (search.includes('full') && search.includes('time')) {
                    // Positive match for full-time
                    if (job.includes('full') && job.includes('time')) return 8;
                    // Negative match for part-time
                    if (job.includes('part') && job.includes('time')) return -5;
                }
                
                // Check for remote/work from home searches
                if ((search.includes('remote') || (search.includes('work') && search.includes('home')))) {
                    // Positive match for remote work
                    if (job.includes('remote') || (job.includes('work') && job.includes('home'))) return 8;
                    // Negative match for on-site
                    if (job.includes('on') && (job.includes('site') || job.includes('location'))) return -3;
                }
                
                // Check for on-site searches
                if (search.includes('on') && search.includes('site')) {
                    // Positive match for on-site
                    if (job.includes('on') && job.includes('site')) return 8;
                    // Negative match for remote
                    if (job.includes('remote') || (job.includes('work') && job.includes('home'))) return -3;
                }
                
                // For other cases, do substring matching but with context awareness
                if (job.includes(search)) {
                    // If the search term is part of the job type, give a moderate score
                    return 6;
                }
                
                // Check individual terms (with at least 3 chars)
                const searchTerms = search.split(/[ -]+/).filter(t => t.length >= 3);
                const jobTerms = job.split(/[ -]+/);
                
                let termMatches = 0;
                for (const term of searchTerms) {
                    if (jobTerms.includes(term)) {
                        termMatches++;
                    }
                }
                
                // If all search terms match job terms, good score
                if (termMatches === searchTerms.length && searchTerms.length > 0) {
                    return 5;
                }
                
                // If some terms match, lower score
                if (termMatches > 0) {
                    return 3;
                }
                
                // No match
                return 0;
            }
            
            // For each job card, check if it matches the filters
            let visibleCount = 0;
            
            jobCards.forEach(card => {
                try {
                    const cardText = card.textContent.toLowerCase();
                    const jobDetails = card.querySelectorAll('.job-detail');
                    const jobLocation = jobDetails.length > 0 ? jobDetails[0].textContent.toLowerCase() : '';
                    
                    // Special handling for job type - extract job type from the second job-detail
                    let jobType = '';
                    if (jobDetails.length > 1) {
                        jobType = jobDetails[1].textContent.toLowerCase().trim();
                        
                        // Debug log found job type
                        if (keywordsValue && keywordsValue.length > 2) {
                            console.log("Card job type:", jobType);
                        }
                    }
                    
                    // Check for normalized keywords match or regular keywords match
                    const matchesKeyword = 
                        keywordsValue === '' || 
                        (keywordsValue.length > 2 && (
                            cardText.includes(keywordsValue) || 
                            (normalizedKeywords !== keywordsValue && cardText.includes(normalizedKeywords))
                        ));
                        
                    // Special case for job type matching using scoring system
                    let matchesJobType = false;
                    let jobTypeScore = 0;
                    
                    if (keywordsValue === '') {
                        matchesJobType = true;
                    } else if (jobType !== '' && keywordsValue.length > 2) {
                        // Calculate match score
                        jobTypeScore = scoreJobTypeMatch(keywordsValue, jobType);
                        
                        // Consider it a match if the score is positive
                        if (jobTypeScore > 0) {
                            matchesJobType = true;
                            console.log(`✓ Job type match with score: ${jobTypeScore} for "${jobType}"`);
                        } else if (jobTypeScore < 0) {
                            console.log(`✗ Negative match score: ${jobTypeScore} for "${jobType}"`);
                        }
                    }
                    
                    const matchesLocation = locationValue === '' || jobLocation.includes(locationValue);
                    
                    // Show/hide based on search filters only
                    if ((matchesKeyword || matchesJobType) && matchesLocation) {
                        card.style.display = '';
                        visibleCount++;
                        
                        // Log match reason for debugging
                        if (keywordsValue.length > 2 && matchesJobType) {
                            console.log(`Match found for card with job type: ${jobType} (Score: ${jobTypeScore})`);
                        }
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
