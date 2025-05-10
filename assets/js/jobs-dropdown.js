/**
 * Jobs dropdown filter functionality
 * Handles the filtering dropdown and its controls in complete isolation
 * No live filtering - changes only apply when 'Apply Filters' is clicked
 */

(function() {
    // Use an IIFE to avoid polluting the global namespace
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Jobs dropdown filter script loaded');
        
        // --- DROPDOWN SPECIFIC ELEMENTS ---
        const filterButton = document.getElementById('filterDropdown');
        const dropdownMenu = document.querySelector('.filter-menu');
        
        // Exit if we're not on a page with the filter dropdown
        if (!filterButton || !dropdownMenu) {
            console.log('Filter dropdown elements not found, dropdown filtering not initialized');
            return;
        }
        
        console.log('Dropdown filter elements found');
        
        // --- SUPER AGGRESSIVE EVENT ISOLATION ---
        // Prevent any events inside the dropdown from bubbling up to parent elements
        dropdownMenu.addEventListener('input', function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
        }, true);
        
        dropdownMenu.addEventListener('change', function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
        }, true);
        
        dropdownMenu.addEventListener('click', function(e) {
            // Don't stop propagation for the whole dropdown, but do for form elements
            if (e.target.tagName === 'INPUT' || 
                e.target.tagName === 'SELECT' || 
                e.target.tagName === 'LABEL' || 
                e.target.tagName === 'OPTION' ||
                e.target.classList.contains('accordion-button')) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        }, true);
        
        // --- ENSURE BOOTSTRAP DROPDOWN WORKS PROPERLY ---
        // Remove any existing event listeners to avoid conflicts
        const newButton = filterButton.cloneNode(true);
        filterButton.parentNode.replaceChild(newButton, filterButton);
        
        // Store reference to the new button
        const dropdownButton = newButton;
        
        // Make sure dropdown has proper Bootstrap classes
        dropdownMenu.classList.add('dropdown-menu');
        
        // Reference to Bootstrap dropdown instance
        let bootstrapDropdown = null;
        
        // Force Bootstrap to initialize the dropdown properly
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            try {
                // Create a new dropdown instance with custom options
                bootstrapDropdown = new bootstrap.Dropdown(dropdownButton, {
                    autoClose: false // Disable auto-close behavior
                });
                console.log('Bootstrap dropdown initialized with auto-close disabled');

                // Add event listener for when dropdown is shown
                dropdownButton.addEventListener('shown.bs.dropdown', function() {
                    console.log('Dropdown shown, updating filter counts');
                    // Update filter counts when dropdown is shown
                    updateFilterCount();
                });
            } catch (e) {
                console.error('Error initializing Bootstrap dropdown:', e);
                // Fallback to manual implementation if Bootstrap fails
                setupManualDropdown(dropdownButton, dropdownMenu);
            }
        } else {
            console.log('Bootstrap not found, using manual dropdown');
            // Use a manual implementation if Bootstrap isn't available
            setupManualDropdown(dropdownButton, dropdownMenu);
        }
        
        // Prevent clicks inside dropdown from closing it, but allow form interactions
        dropdownMenu.addEventListener('click', function(e) {
            // Don't stop propagation for filter action buttons
            if (!e.target.closest('.filter-action-btn')) {
                e.stopPropagation();
            }
        }, true);
        
        // Prevent clicks on form elements from closing the dropdown, but allow button clicks
        const filterForm = document.getElementById("advanced-filter-form");
        if (filterForm) {
            filterForm.addEventListener('click', function(e) {
                // Don't stop propagation for filter action buttons
                if (!e.target.closest('.filter-action-btn')) {
                    e.stopPropagation();
                }
            }, true);
        }
        
        // Override Bootstrap's default click-outside behavior
        document.addEventListener('click', function(e) {
            // Only handle clicks on the dropdown button
            if (e.target === dropdownButton || dropdownButton.contains(e.target)) {
                const isExpanded = dropdownButton.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            }
        }, true);
        
        // Reference to manually controlled dropdown
        let manualDropdownControl = null;
        
        // Manual dropdown implementation as fallback
        function setupManualDropdown(button, menu) {
            manualDropdownControl = {
                show: function() {
                    openDropdown();
                },
                hide: function() {
                    closeDropdown();
                }
            };
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Filter button clicked');
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                
                // Toggle dropdown
                if (isExpanded) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!button.contains(e.target) && !menu.contains(e.target)) {
                    closeDropdown();
                }
            });
            
            // Function to open dropdown
            function openDropdown() {
                console.log('Opening dropdown');
                menu.classList.add('show');
                button.setAttribute('aria-expanded', 'true');
                
                // Position the dropdown with better viewport awareness
                positionDropdownWithFooterAwareness();
                
                // Add window resize listener to reposition if needed
                window.addEventListener('resize', positionDropdownWithFooterAwareness);
                
                // Update filter counts when dropdown is opened
                console.log('Manual dropdown opened, updating filter counts');
                updateFilterCount();
            }
            
            // Function to close dropdown
            function closeDropdown() {
                console.log('Closing dropdown');
                menu.classList.remove('show');
                button.setAttribute('aria-expanded', 'false');
                
                // Remove resize listener when dropdown is closed
                window.removeEventListener('resize', positionDropdownWithFooterAwareness);
            }
            
            // New function to position dropdown with footer awareness
            function positionDropdownWithFooterAwareness() {
                const buttonRect = button.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const footer = document.querySelector('footer');
                
                // Set fixed dimensions for the dropdown
                const fixedDropdownHeight = 500; // Fixed height in pixels
                const minSpacingFromFooter = 100; // Increased minimum space between dropdown and footer
                const stickyFooterHeight = 50; // Height of the sticky footer
                
                // Calculate available space
                let availableHeight = viewportHeight - buttonRect.bottom - minSpacingFromFooter;
                
                if (footer) {
                    const footerTop = footer.getBoundingClientRect().top;
                    const footerHeight = footer.offsetHeight;
                    
                    // Calculate the maximum height that would keep the dropdown above the footer
                    const maxHeightBeforeFooter = footerTop - buttonRect.bottom - minSpacingFromFooter;
                    
                    // If the fixed height would cause overlap, reduce it
                    if (fixedDropdownHeight > maxHeightBeforeFooter) {
                        menu.style.height = (maxHeightBeforeFooter - stickyFooterHeight) + 'px';
                    } else {
                        menu.style.height = (fixedDropdownHeight - stickyFooterHeight) + 'px';
                    }
                    
                    // Ensure the dropdown stays above the footer
                    const dropdownBottom = buttonRect.bottom + parseInt(menu.style.height) + stickyFooterHeight;
                    if (dropdownBottom > footerTop - minSpacingFromFooter) {
                        // Adjust the height to prevent overlap
                        const newHeight = footerTop - buttonRect.bottom - minSpacingFromFooter - stickyFooterHeight;
                        menu.style.height = Math.max(200, newHeight) + 'px'; // Minimum height of 200px
                    }
                } else {
                    menu.style.height = (fixedDropdownHeight - stickyFooterHeight) + 'px';
                }
                
                // Set positioning
                menu.style.position = 'fixed';
                menu.style.top = buttonRect.bottom + 'px';
                menu.style.left = buttonRect.left + 'px';
                menu.style.width = buttonRect.width + 'px';
                menu.style.zIndex = '1000';
                menu.style.overflowY = 'auto';
                
                /* Add minimum content height to prevent page collapse
                const jobsContainer = document.querySelector('.jobs-container');
                if (jobsContainer) {
                    const currentHeight = jobsContainer.offsetHeight;
                    const minContentHeight = buttonRect.bottom + parseInt(menu.style.height) + minSpacingFromFooter;
                    if (currentHeight < minContentHeight) {
                        jobsContainer.style.minHeight = minContentHeight + 'px';
                    }
                }
                
                // Add a class to indicate if the dropdown is near the footer
                if (footer) {
                    const dropdownBottom = buttonRect.bottom + parseInt(menu.style.height);
                    if (dropdownBottom > footerTop - minSpacingFromFooter) {
                        menu.classList.add('near-footer');
                    } else {
                        menu.classList.remove('near-footer');
                    }
                }*/
                menu.style.paddingBottom = '0'; // Ensure no bottom padding
                menu.style.marginBottom = '0'; // Ensure no bottom margin
            }
            
            // Add resize handler to maintain positioning
            window.addEventListener('resize', function() {
                if (menu.classList.contains('show')) {
                    positionDropdownWithFooterAwareness();
                }
            });
            
            // Add scroll handler to maintain positioning
            window.addEventListener('scroll', function() {
                if (menu.classList.contains('show')) {
                    positionDropdownWithFooterAwareness();
                }
            });
        }
        
        // Function to keep dropdown open
        function keepDropdownOpen() {
            if (bootstrapDropdown) {
                // If using Bootstrap dropdown
                dropdownMenu.classList.add('show');
                dropdownButton.setAttribute('aria-expanded', 'true');
                
                // Ensure proper positioning with footer awareness
                const viewportHeight = window.innerHeight;
                const buttonRect = dropdownButton.getBoundingClientRect();
                const footer = document.querySelector('footer');
                
                // Calculate available height
                let availableHeight = viewportHeight - buttonRect.bottom - 30; // 30px minimum bottom margin
                
                if (footer) {
                    const footerTop = footer.getBoundingClientRect().top;
                    // Consider the footer position when calculating available height
                    availableHeight = Math.min(availableHeight, footerTop - buttonRect.bottom - 30);
                }
                
                // Calculate dropdown max height as percentage of viewport
                const maxHeightPercent = window.innerWidth <= 767 ? 65 : 75; // Use 65% on mobile, 75% on desktop
                const maxHeightVh = (viewportHeight * maxHeightPercent) / 100;
                
                // Apply custom positioning and height limits
                dropdownMenu.style.maxHeight = Math.min(availableHeight, maxHeightVh) + 'px';
                dropdownMenu.style.marginBottom = '30px';
            } else if (manualDropdownControl) {
                // If using manual dropdown
                manualDropdownControl.show();
            }
        }
        
        // --- BADGE COUNTER FOR SELECTED FILTERS ---
        
        // Improved function to find elements with fallbacks
        function findElement(selector, fallbackSelectors = []) {
            let element = document.querySelector(selector);
            
            // If the primary selector fails, try fallbacks
            if (!element && fallbackSelectors.length > 0) {
                for (const fallbackSelector of fallbackSelectors) {
                    element = document.querySelector(fallbackSelector);
                    if (element) {
                        console.log(`Found element with fallback selector: ${fallbackSelector}`);
                        break;
                    }
                }
            }
            
            // If all attempts fail, try a more direct approach
            if (!element && selector.startsWith('.')) {
                // Try without the dot for class names
                const className = selector.substring(1);
                const elements = document.getElementsByClassName(className);
                if (elements.length > 0) {
                    element = elements[0];
                    console.log(`Found element using getElementsByClassName: ${className}`);
                }
            }
            
            return element;
        }
        
        // Update filter count badges (NO filtering, just visual indicators)
        function updateFilterCount(e) {
            // IMPORTANT: Stop propagation of all events
            if (e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
            
            console.log("updateFilterCount called");
            
            // Verify that we have access to the filter dropdown
            const filterDropdown = document.querySelector('.filter-dropdown');
            if (!filterDropdown) {
                console.error("Filter dropdown container not found!");
                return false;
            }
            console.log("Filter dropdown container found:", filterDropdown);
            
            // First check if form exists
            const filterForm = document.getElementById("advanced-filter-form");
            if (!filterForm) {
                console.error("Filter form not found!");
                return false;
            }
            console.log("Filter form found");
            
            // Get all selected filters with more robust selectors
            const jobTypeCheckboxes = filterForm.querySelectorAll(".job-type-checkbox:checked");
            const jobTypeCount = jobTypeCheckboxes.length;
            console.log("Job type checkboxes found:", jobTypeCheckboxes.length);
            
            const categoryCheckboxes = filterForm.querySelectorAll(".category-checkbox:checked");
            const categoryCount = categoryCheckboxes.length;
            console.log("Category checkboxes found:", categoryCheckboxes.length);
            
            // Get form elements with more robust selectors
            const minSalaryInput = filterForm.querySelector("#min_salary");
            const minSalary = minSalaryInput ? minSalaryInput.value.trim() : '';
            
            const maxSalaryInput = filterForm.querySelector("#max_salary");
            const maxSalary = maxSalaryInput ? maxSalaryInput.value.trim() : '';
            
            const hasSalaryFilter = (minSalary !== '' || maxSalary !== '');
            console.log("Salary filters:", { minSalary, maxSalary, hasSalaryFilter });
            
            const datePostedSelect = filterForm.querySelector("#date_posted");
            const datePosted = datePostedSelect ? datePostedSelect.value : '';
            const hasDateFilter = (datePosted && datePosted !== 'any');
            console.log("Date filter:", { datePosted, hasDateFilter });
            
            // Calculate actual filter counts - each active checkbox counts as one
            let totalFilters = jobTypeCount + categoryCount;
            if (hasSalaryFilter) totalFilters++;
            if (hasDateFilter) totalFilters++;
            
            console.log("Total individual filters:", totalFilters);
            
            // Alternative counting by filter type
            let filterTypeCount = 0;
            if (jobTypeCount > 0) filterTypeCount++;
            if (categoryCount > 0) filterTypeCount++;
            if (hasSalaryFilter) filterTypeCount++;
            if (hasDateFilter) filterTypeCount++;
            
            console.log("Total filter types active:", filterTypeCount);
            
            // IMPORTANT: Choose which counting method to use
            // Set to filterTypeCount for counting by category, or totalFilters for counting individual filters
            const finalCount = totalFilters; // Using totalFilters to count individual selections
            
            // Try multiple approaches to find the filter count badge
            let filterCountBadge = null;
            
            // First try with querySelector using various selectors
            filterCountBadge = findElement(
                ".filter-count", 
                [".filter-dropdown .filter-count", "#filterDropdown .filter-count", ".dropdown-toggle .filter-count"]
            );
            
            // If that fails, try direct DOM traversal from the dropdown button
            if (!filterCountBadge) {
                const dropdownBtn = document.getElementById('filterDropdown');
                if (dropdownBtn) {
                    // Look for the badge within the button
                    const badges = dropdownBtn.getElementsByClassName('filter-count');
                    if (badges.length > 0) {
                        filterCountBadge = badges[0];
                        console.log("Found filter count badge via direct traversal");
                    }
                }
            }
            
            // Last resort - search the entire document
            if (!filterCountBadge) {
                const allFilterCountBadges = document.getElementsByClassName('filter-count');
                if (allFilterCountBadges.length > 0) {
                    filterCountBadge = allFilterCountBadges[0];
                    console.log("Found filter count badge as last resort");
                }
            }
            
            console.log("Filter count badge:", filterCountBadge);
            
            // Update main filter count badge
            if (filterCountBadge) {
                if (finalCount > 0) {
                    filterCountBadge.textContent = finalCount;
                    filterCountBadge.style.display = "inline-block";
                    console.log("Showing filter count badge with count:", finalCount);
                } else {
                    filterCountBadge.style.display = "none";
                    console.log("Hiding filter count badge (no filters)");
                }
            } else {
                console.error("Filter count badge element not found despite multiple attempts!");
            }
            
            // Update job type count badge - use similar robust approach
            let jobTypeCountBadge = null;
            
            // Try querySelector first
            jobTypeCountBadge = findElement(
                ".job-type-count", 
                [".accordion-button .job-type-count"]
            );
            
            // Direct DOM search as backup
            if (!jobTypeCountBadge) {
                const allJobTypeBadges = document.getElementsByClassName('job-type-count');
                if (allJobTypeBadges.length > 0) {
                    jobTypeCountBadge = allJobTypeBadges[0];
                    console.log("Found job type badge via direct search");
                }
            }
            
            if (jobTypeCountBadge) {
                if (jobTypeCount > 0) {
                    jobTypeCountBadge.textContent = jobTypeCount;
                    jobTypeCountBadge.style.display = "inline-block";
                    console.log("Job type badge count:", jobTypeCount);
                } else {
                    jobTypeCountBadge.style.display = "none";
                }
            }
            
            // Update category count badge - use similar robust approach
            let categoryCountBadge = null;
            
            // Try querySelector first
            categoryCountBadge = findElement(
                ".category-count", 
                [".accordion-button .category-count"]
            );
            
            // Direct DOM search as backup
            if (!categoryCountBadge) {
                const allCategoryBadges = document.getElementsByClassName('category-count');
                if (allCategoryBadges.length > 0) {
                    categoryCountBadge = allCategoryBadges[0];
                    console.log("Found category badge via direct search");
                }
            }
            
            if (categoryCountBadge) {
                if (categoryCount > 0) {
                    categoryCountBadge.textContent = categoryCount;
                    categoryCountBadge.style.display = "inline-block";
                    console.log("Category badge count:", categoryCount);
                } else {
                    categoryCountBadge.style.display = "none";
                }
            }
            
            return false;
        }
        
        // Explicitly call updateFilterCount when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Document loaded, initializing filter counts");
            // Wait a bit to ensure all elements are properly loaded
            setTimeout(updateFilterCount, 500);
        });
        
        // --- SETUP FILTER FORM HANDLING ---
        if (filterForm) {
            // Stop any input events from bubbling outside the form
            filterForm.addEventListener('input', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Call updateFilterCount on any input change within the form
                updateFilterCount(e);
            }, true);
            
            filterForm.addEventListener('change', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Call updateFilterCount on any change within the form
                updateFilterCount(e);
            }, true);
            
            // Add direct listeners for checkbox changes - ONLY update counts, NO filtering
            const checkboxes = filterForm.querySelectorAll('.job-type-checkbox, .category-checkbox');
            console.log("Setting up listeners for", checkboxes.length, "checkboxes");
            
            checkboxes.forEach((checkbox, index) => {
                // Remove any existing event listeners to prevent duplicates
                const newCheckbox = checkbox.cloneNode(true);
                checkbox.parentNode.replaceChild(newCheckbox, checkbox);
                
                // Add event listeners to the new checkbox
                newCheckbox.addEventListener('change', function(e) {
                    // Stop propagation to isolate the event
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    console.log(`Checkbox ${index} changed:`, this.checked);
                    
                    // Only update the visual indicators, don't filter
                    setTimeout(function() {
                        updateFilterCount();
                    }, 0);
                    
                    // Return false to prevent any default action
                    return false;
                }, true);
                
                // Add another layer of protection
                newCheckbox.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                }, true);
            });
            
            // Add direct listeners for salary inputs - ONLY update counts, NO filtering
            const salaryInputs = filterForm.querySelectorAll('#min_salary, #max_salary');
            console.log("Setting up listeners for", salaryInputs.length, "salary inputs");
            
            salaryInputs.forEach((input, index) => {
                // Remove any existing event listeners to prevent duplicates
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                
                // Add event listeners to the new input
                ['input', 'change', 'blur'].forEach(eventType => {
                    newInput.addEventListener(eventType, function(e) {
                        // Stop propagation to isolate the event
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        
                        console.log(`Salary input ${index} ${eventType}:`, this.value);
                        
                        // Only update the visual indicators, don't filter
                        setTimeout(function() {
                            updateFilterCount();
                        }, 0);
                        
                        // Return false to prevent any default action
                        return false;
                    }, true);
                });
            });
            
            // Add direct listeners for date dropdown - ONLY update counts, NO filtering
            const dateSelect = filterForm.querySelector('#date_posted');
            if (dateSelect) {
                console.log("Setting up listener for date select");
                
                // Remove any existing event listeners to prevent duplicates
                const newDateSelect = dateSelect.cloneNode(true);
                dateSelect.parentNode.replaceChild(newDateSelect, dateSelect);
                
                // Add event listener to the new select
                newDateSelect.addEventListener('change', function(e) {
                    // Stop propagation to isolate the event
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    console.log("Date select changed:", this.value);
                    
                    // Only update the visual indicators, don't filter
                    setTimeout(function() {
                        updateFilterCount();
                    }, 0);
                    
                    // Return false to prevent any default action
                    return false;
                }, true);
            }
            
            // Function to submit form via AJAX
            function submitFormAjax(formEl, onSuccess, showFeedback = true) {
                // Create a loading indicator if showing feedback
                let statusMessage = null;
                
                if (showFeedback) {
                    // Create status message
                    statusMessage = document.createElement('div');
                    statusMessage.className = 'ajax-status alert alert-info mb-3';
                    statusMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Applying filters...';
                    
                    // Insert before the form buttons
                    const footerEl = formEl.querySelector('.dropdown-sticky-footer');
                    if (footerEl) {
                        formEl.insertBefore(statusMessage, footerEl);
                    } else {
                        formEl.appendChild(statusMessage);
                    }
                }
                
                // Get form data
                const formData = new FormData(formEl);
                
                // Create URL with form data (GET method)
                const queryString = new URLSearchParams(formData).toString();
                const currentUrl = new URL(window.location.href);
                const baseUrl = currentUrl.origin + currentUrl.pathname;
                const targetUrl = baseUrl + '?' + queryString;
                
                // Use fetch API to get filtered results
                fetch(targetUrl)
                    .then(response => response.text())
                    .then(html => {
                        // Parse the HTML response
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Extract the job cards container
                        const newJobCards = doc.querySelector('.col-md-8');
                        
                        if (newJobCards) {
                            // Replace the current job cards with the new ones
                            const currentJobCards = document.querySelector('.col-md-8');
                            if (currentJobCards) {
                                currentJobCards.innerHTML = newJobCards.innerHTML;
                                
                                // Check if there are any job cards after the update
                                const hasJobCards = currentJobCards.querySelector('.job-card') !== null;
                                const jobCount = currentJobCards.querySelectorAll('.job-card').length;
                                
                                console.log("Job count after filter:", jobCount);
                                
                                // Check for job count element and no results message
                                const jobCountElement = currentJobCards.querySelector('.job-count');
                                const noResultsMessage = currentJobCards.querySelector('.no-results-message');
                                
                                // If we have a job count element but no actual job cards, we need to show "no results"
                                if (jobCountElement && jobCount === 0) {
                                    // Update job count to show 0
                                    jobCountElement.textContent = "0 jobs found";
                                    
                                    // If there's no "no results" message, create one
                                    if (!noResultsMessage) {
                                        const noResultsDiv = document.createElement('div');
                                        noResultsDiv.className = 'alert alert-info no-results-message';
                                        noResultsDiv.innerHTML = '<i class="fas fa-info-circle"></i> No jobs found matching your search criteria. Please try different keywords or location.';
                                        
                                        // Find where to insert the no results message
                                        const jobListArea = currentJobCards.querySelector('.d-flex.justify-content-between.align-items-center.mb-3');
                                        if (jobListArea && jobListArea.nextElementSibling) {
                                            // Insert after the job count row
                                            jobListArea.parentNode.insertBefore(noResultsDiv, jobListArea.nextElementSibling);
                                            
                                            // Hide all job cards if they exist
                                            currentJobCards.querySelectorAll('.job-card').forEach(card => {
                                                card.style.display = 'none';
                                            });
                                        } else {
                                            // Fallback - append to the current job cards container
                                            currentJobCards.appendChild(noResultsDiv);
                                        }
                                    } else {
                                        // Show existing no results message
                                        noResultsMessage.style.display = 'block';
                                        
                                        // Hide all job cards if they exist
                                        currentJobCards.querySelectorAll('.job-card').forEach(card => {
                                            card.style.display = 'none';
                                        });
                                    }
                                } else if (noResultsMessage && jobCount > 0) {
                                    // Hide the no results message if we have job cards
                                    noResultsMessage.style.display = 'none';
                                    
                                    // Show all job cards
                                    currentJobCards.querySelectorAll('.job-card').forEach(card => {
                                        card.style.display = '';
                                    });
                                }
                                
                                // IMPORTANT: Always handle the spacer after updating results
                                setTimeout(function() {
                                    console.log("Adding spacer after filter results updated");
                                    handleSpacer(hasJobCards);
                                }, 100);
                                
                                // Update the URL without reloading the page
                                window.history.pushState({}, '', targetUrl);
                                
                                if (showFeedback) {
                                    // Remove status message immediately without success message
                                    if (statusMessage && statusMessage.parentNode) {
                                        statusMessage.parentNode.removeChild(statusMessage);
                                    }
                                }
                                
                                if (onSuccess && typeof onSuccess === 'function') {
                                    onSuccess();
                                }
                            }
                        } else {
                            console.error('Could not find job cards in response');
                            if (statusMessage) {
                                statusMessage.className = 'ajax-status alert alert-danger mb-3';
                                statusMessage.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Failed to update results.';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error applying filters:', error);
                        if (statusMessage) {
                            statusMessage.className = 'ajax-status alert alert-danger mb-3';
                            statusMessage.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Failed to update results.';
                        }
                    });
                
                // Prevent default form submission
                return false;
            }
            
            // Function to handle the spacer element
            function handleSpacer(hasJobCards) {
                console.log("Handling spacer, has job cards:", hasJobCards);
                const jobsContainer = document.querySelector('.jobs-container');
                if (!jobsContainer) {
                    console.error("Jobs container not found for spacer");
                    return;
                }

                // Count the number of job cards
                const jobCards = jobsContainer.querySelectorAll('.job-card');
                const jobCount = jobCards.length;
                console.log("Job count for spacer calculation:", jobCount);

                // Remove any existing spacer
                const existingSpacer = document.querySelector('.dropdown-spacer');
                if (existingSpacer) {
                    console.log("Removing existing spacer");
                    existingSpacer.remove();
                }

                // Create a new spacer element
                const spacer = document.createElement('div');
                spacer.className = 'dropdown-spacer';
                spacer.style.width = '100%';
                spacer.style.display = 'block';
                spacer.style.clear = 'both';

                /* Always add the spacer, but adjust height based on job count*/
                if (jobCount <= 1) {
                    // Use smaller heights to reduce excessive white space
                    spacer.style.height = jobCount === 1 ? '200px' : '300px';
                    console.log(`Adding ${spacer.style.height} spacer for ${jobCount} jobs (reduced height)`);
                } else {
                    // Smaller spacer when multiple jobs exist
                    spacer.style.height = '70px';
                    console.log(`Adding ${spacer.style.height} spacer for ${jobCount} jobs`);
                }

                // Always insert the spacer at the end of the job results container
                const jobResultsContainer = jobsContainer.querySelector('.col-md-8');
                if (jobResultsContainer) {
                    jobResultsContainer.appendChild(spacer);
                    console.log("Spacer appended to job results container");
                } else {
                    // Fallback - add to the jobs container
                    jobsContainer.appendChild(spacer);
                    console.log("Spacer appended to jobs container (fallback)");
                }
            }
            
            // Call handleSpacer on page load (with delay to ensure DOM is ready)
            document.addEventListener('DOMContentLoaded', function() {
                console.log("Document loaded, will add spacer after timeout");
                setTimeout(function() {
                    const jobCards = document.querySelectorAll('.job-card');
                    const hasJobCards = jobCards.length > 0;
                    handleSpacer(hasJobCards);
                    console.log("Initial spacer added");
                }, 500);
            });
            
            // Add window resize handler to maintain spacer
            window.addEventListener('resize', function() {
                console.log("Window resized, updating spacer");
                const jobCards = document.querySelectorAll('.job-card');
                const hasJobCards = jobCards.length > 0;
                handleSpacer(hasJobCards);
            });
            
            // --- ADD APPLY AND CLEAR BUTTONS ---
            if (!filterForm.querySelector('.dropdown-sticky-footer')) {
                // Create footer container
                const stickyContainer = document.createElement("div");
                stickyContainer.className = "dropdown-sticky-footer mt-3 pt-3 border-top d-flex justify-content-between";
                
                // Apply button
                const applyButton = document.createElement("button");
                applyButton.type = "button";
                applyButton.className = "btn btn-primary filter-action-btn filter-apply-btn";
                applyButton.innerHTML = '<i class="fas fa-search me-1"></i> Apply Filters';
                
                // Clear button
                const clearButton = document.createElement("button");
                clearButton.type = "button";
                clearButton.className = "btn btn-outline-secondary filter-action-btn filter-clear-btn ms-3";
                clearButton.innerHTML = '<i class="fas fa-times me-1"></i> Clear';
                
                // Add buttons to container
                stickyContainer.appendChild(applyButton);
                stickyContainer.appendChild(clearButton);
                
                // Add container to the form
                filterForm.appendChild(stickyContainer);
                
                // Add event listener for Apply button
                applyButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Apply filters button clicked');
                    
                    // Keep dropdown open
                    keepDropdownOpen();
                    
                    // Submit form via AJAX
                    submitFormAjax(filterForm, null, true);
                });
                
                // Add event listener for Clear button
                clearButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Clear filters button clicked');
                    
                    // Uncheck all checkboxes
                    filterForm.querySelectorAll(".job-type-checkbox, .category-checkbox").forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    
                    // Reset salary inputs
                    if (filterForm.querySelector("#min_salary")) filterForm.querySelector("#min_salary").value = "";
                    if (filterForm.querySelector("#max_salary")) filterForm.querySelector("#max_salary").value = "";
                    
                    // Reset date posted
                    if (filterForm.querySelector("#date_posted")) filterForm.querySelector("#date_posted").value = "any";
                    
                    // Explicitly update filter counts with multiple approaches to ensure it works
                    setTimeout(function() {
                        console.log('Updating filter counts after clearing');
                        updateFilterCount();
                        
                        // Directly force all badges to be hidden using multiple methods
                        // Method 1: Use querySelector
                        const mainFilterBadge = document.querySelector(".filter-count");
                        if (mainFilterBadge) {
                            mainFilterBadge.textContent = "0";
                            mainFilterBadge.style.display = "none";
                            console.log("Hiding main filter badge via querySelector");
                        }
                        
                        const jobTypeBadge = document.querySelector(".job-type-count");
                        if (jobTypeBadge) {
                            jobTypeBadge.textContent = "0";
                            jobTypeBadge.style.display = "none";
                            console.log("Hiding job type badge via querySelector");
                        }
                        
                        const categoryBadge = document.querySelector(".category-count");
                        if (categoryBadge) {
                            categoryBadge.textContent = "0";
                            categoryBadge.style.display = "none";
                            console.log("Hiding category badge via querySelector");
                        }
                        
                        // Method 2: Direct DOM access via class name
                        const filterBadges = document.getElementsByClassName('filter-count');
                        for (let i = 0; i < filterBadges.length; i++) {
                            filterBadges[i].textContent = "0";
                            filterBadges[i].style.display = "none";
                            console.log("Hiding filter badge via direct DOM access");
                        }
                        
                        const jobTypeBadges = document.getElementsByClassName('job-type-count');
                        for (let i = 0; i < jobTypeBadges.length; i++) {
                            jobTypeBadges[i].textContent = "0";
                            jobTypeBadges[i].style.display = "none";
                            console.log("Hiding job type badge via direct DOM access");
                        }
                        
                        const categoryBadges = document.getElementsByClassName('category-count');
                        for (let i = 0; i < categoryBadges.length; i++) {
                            categoryBadges[i].textContent = "0";
                            categoryBadges[i].style.display = "none";
                            console.log("Hiding category badge via direct DOM access");
                        }
                        
                        // Method 3: Force a secondary update after a brief delay
                        setTimeout(updateFilterCount, 100);
                    }, 0);
                    
                    // Keep dropdown open
                    keepDropdownOpen();
                    
                    // Submit with cleared values via AJAX
                    submitFormAjax(filterForm, null, true);
                });
            }
        }
    });
})(); 