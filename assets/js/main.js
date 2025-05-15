/**
 * GoWork - Main JavaScript File
 */

// Document Ready Function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // File input preview for resume and license uploads
    const fileInputs = document.querySelectorAll('.custom-file-input');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileNameField = document.querySelector(`[data-file-name="${this.id}"]`);
            if (fileNameField) {
                if (this.files.length > 0) {
                    fileNameField.textContent = this.files[0].name;
                } else {
                    fileNameField.textContent = 'No file chosen';
                }
            }
        });
    });
    
    // Job search form
    const jobSearchForm = document.getElementById('job-search-form');
    if (jobSearchForm) {
        jobSearchForm.addEventListener('submit', function(e) {
            const keywordsInput = document.getElementById('keywords');
            const locationInput = document.getElementById('location');
            
            // Simple validation - at least one field should be filled
            if (keywordsInput.value.trim() === '' && locationInput.value.trim() === '') {
                e.preventDefault();
                showAlert('Please enter keywords or location to search for jobs.', 'danger');
            }
        });
    }
    
    // Application form
    const applyForms = document.querySelectorAll('.apply-form');
    
    applyForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const jobId = this.dataset.jobId;
            const jobTitle = this.dataset.jobTitle;
            
            // Check if apply button is disabled (meaning no resume)
            const applyButton = this.querySelector('.apply-button');
            if (applyButton && applyButton.disabled) {
                showAlert('You must upload a resume before applying for jobs.', 'warning');
                // Redirect to profile page after a short delay
                setTimeout(() => {
                    window.location.href = siteUrl + '/pages/user/profile.php';
                }, 2000);
                return;
            }
            
            // Confirm application
            if (confirm(`Are you sure you want to apply for "${jobTitle}"?`)) {
                // Submit the form - in a real implementation, this would likely be an AJAX request
                this.submit();
            }
        });
    });
    
    // Password reset form validation
    const resetForm = document.getElementById('reset-password-form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const emailInput = document.getElementById('email');
            if (emailInput.value.trim() === '') {
                e.preventDefault();
                showAlert('Please enter your email address.', 'danger');
            }
        });
    }
    
    // Registration form validation
    const registrationForm = document.getElementById('registration-form');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                showAlert('Passwords do not match!', 'danger');
            }
        });
    }
    
    // License status update
    const statusButtons = document.querySelectorAll('.license-status-btn');
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const companyId = this.dataset.companyId;
            const status = this.dataset.status;
            const companyName = this.dataset.companyName;
            
            if (confirm(`Are you sure you want to mark ${companyName}'s license as "${status}"?`)) {
                // In a real implementation, this would likely be an AJAX request
                document.getElementById('company_id').value = companyId;
                document.getElementById('status').value = status;
                document.getElementById('license-status-form').submit();
            }
        });
    });
});

/**
 * Show an alert message
 * 
 * @param {string} message - The message to display
 * @param {string} type - The alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => {
            alertContainer.removeChild(alert);
        }, 150);
    }, 5000);
}

/**
 * Format salary range
 * 
 * @param {number} min - Minimum salary
 * @param {number} max - Maximum salary
 * @returns {string} Formatted salary range
 */
function formatSalaryRange(min, max) {
    if (!min && !max) {
        return 'Not specified';
    }
    
    if (min && !max) {
        return '$' + formatNumber(min);
    }
    
    if (!min && max) {
        return 'Up to $' + formatNumber(max);
    }
    
    return '$' + formatNumber(min) + ' - $' + formatNumber(max);
}

/**
 * Format number with commas
 * 
 * @param {number} num - The number to format
 * @returns {string} Formatted number
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
