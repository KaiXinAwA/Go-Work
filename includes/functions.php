<?php
/**
 * Functions file
 * Contains helper functions for the application
 */
require_once 'config.php';
require_once 'database.php';

/**
 * Sanitize user input
 * 
 * @param string $data The data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * 
 * @return boolean True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user has specific type
 * 
 * @param int $userType The user type to check against
 * @return boolean True if user is of the given type, false otherwise
 */
function hasUserType($userType) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['user_type'] == $userType;
}

/**
 * Get current logged in user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE user_id = ?";
    return fetchRow($sql, 'i', [$_SESSION['user_id']]);
}

/**
 * Redirect to another page
 * 
 * @param string $location The URL to redirect to
 * @return void
 */
function redirectTo($location) {
    header("Location: $location");
    exit;
}

/**
 * Display error message
 * 
 * @param string $message The error message
 * @return string HTML for error message
 */
function displayError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Display success message
 * 
 * @param string $message The success message
 * @return string HTML for success message
 */
function displaySuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Generate random password
 * 
 * @param int $length The length of the password
 * @return string Random password
 */
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $password .= $characters[$index];
    }
    
    return $password;
}

/**
 * Hash a password
 * 
 * @param string $password The password to hash
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * 
 * @param string $password The password to verify
 * @param string $hash The hash to verify against
 * @return boolean True if password matches hash, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Upload a file
 * 
 * @param array $file The $_FILES array element
 * @param string $destination The destination directory
 * @param array $allowedTypes Array of allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return string|false The path to the uploaded file or false on failure
 */
function uploadFile($file, $destination, $allowedTypes, $maxSize) {
    // Check if file is valid
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: File not set or upload error");
        return false;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        error_log("File upload error: File too large - " . $file['size'] . " > " . $maxSize);
        return false;
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $fileType = $finfo->file($file['tmp_name']);
    error_log("File upload: Detected MIME type: " . $fileType);
    error_log("File upload: Allowed types: " . implode(", ", $allowedTypes));
    
    // For PDF files, be more lenient
    $isPdf = false;
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'pdf') {
        $isPdf = true;
        $allowedTypes[] = $fileType; // Add detected type to allowed types for PDFs
        error_log("File upload: PDF extension detected, allowing detected MIME type");
    }
    
    if (!in_array($fileType, $allowedTypes)) {
        error_log("File upload error: Invalid file type - " . $fileType);
        return false;
    }
    
    // Generate unique filename
    $uniqueName = uniqid() . '_' . basename($file['name']);
    $uploadPath = $destination . $uniqueName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("File upload: Successfully uploaded to " . $uploadPath);
        return $uniqueName;
    }
    
    error_log("File upload error: Failed to move uploaded file");
    return false;
}

/**
 * Send reset password email
 * 
 * @param string $email The email address
 * @param string $token The reset token
 * @param string $newPassword The new password
 * @return boolean True if email sent successfully, false otherwise
 */
function sendResetPasswordEmail($email, $token, $newPassword) {
    // Get user name if available
    $user = fetchRow("SELECT * FROM users WHERE email = ?", 's', [$email]);
    $userName = $user ? $user['username'] : '';
    
    // Create HTML email using template
    require_once __DIR__ . '/email_template.php';
    $htmlContent = getPasswordResetEmailTemplate($userName, $newPassword);
    $plainTextContent = getTextVersionFromHtml($htmlContent);
    
    // Set email headers and content
    $subject = SITE_NAME . ' - Password Reset';
    
    // If Resend API key is not defined or empty, log the email instead of sending
    if (!defined('RESEND_API_KEY') || empty(RESEND_API_KEY)) {
        error_log("Email would be sent to: $email");
        error_log("Subject: $subject");
        error_log("Message: " . substr($plainTextContent, 0, 100) . "...");
        
        // For debug purposes - log if account doesn't exist
        if (!$user) {
            error_log("Note: No user account found with email: $email");
        }
        
        return true; // Return true so the flow continues
    }
    
    // Use Resend API to send the email
    $url = 'https://api.resend.com/emails';
    $apiKey = RESEND_API_KEY;
    
    $data = [
        'from' => EMAIL_FROM_NAME . ' <' . EMAIL_FROM . '>',
        'to' => [$email],
        'subject' => $subject,
        'html' => $htmlContent,
        'text' => $plainTextContent
    ];
    
    // Log the request for debugging
    error_log("Sending email via Resend API to: $email");
    error_log("Using from address: " . EMAIL_FROM_NAME . ' <' . EMAIL_FROM . '>');
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    // Execute cURL session and get response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    // Close cURL session
    curl_close($ch);
    
    // Log any errors
    if ($httpCode != 200 || $error) {
        error_log("Failed to send email via Resend API. HTTP Code: $httpCode, Error: $error");
        error_log("Response: $response");
        return false;
    }
    
    // Log success
    error_log("Email sent successfully to: $email, HTTP Code: $httpCode");
    error_log("Response: $response");
    
    return true;
}

/**
 * Send job application notification
 * 
 * @param int $jobId The job ID
 * @param int $userId The user ID
 * @param string $companyEmail The company email
 * @return boolean True if email sent successfully, false otherwise
 */
function sendJobApplicationNotification($jobId, $userId, $companyEmail) {
    // Get job details
    $job = fetchRow("SELECT * FROM jobs WHERE job_id = ?", 'i', [$jobId]);
    if (!$job) {
        return false;
    }
    
    // Get user details
    $user = fetchRow("SELECT * FROM users u JOIN user_profiles p ON u.user_id = p.user_id WHERE u.user_id = ?", 'i', [$userId]);
    if (!$user) {
        return false;
    }
    
    $subject = "New Job Application: {$job['job_title']}";
    $message = "Dear Employer,\n\n";
    $message .= "A new application has been submitted for the position: {$job['job_title']}.\n\n";
    $message .= "Applicant: {$user['full_name']}\n";
    $message .= "Email: {$user['email']}\n\n";
    $message .= "Please login to your GoWork account to view the full application and resume.\n\n";
    $message .= "Regards,\n";
    $message .= SITE_NAME . " Team";
    
    // If Resend API key is not defined, log the email
    if (!defined('RESEND_API_KEY') || empty(RESEND_API_KEY)) {
        error_log("Email would be sent to: $companyEmail");
        error_log("Subject: $subject");
        error_log("Message: $message");
        return true;
    }
    
    // TODO: Implement actual email sending with Resend when API key is available
    return true;
}

/**
 * Gets user profile information
 * 
 * @param int $userId The user ID
 * @return array|null User profile data or null if not found
 */
function getUserProfile($userId) {
    return fetchRow("SELECT * FROM user_profiles WHERE user_id = ?", 'i', [$userId]);
}

/**
 * Gets career history for a user
 * 
 * @param int $userId The user ID
 * @return array Array of career history entries
 */
function getUserCareerHistory($userId) {
    return fetchAll(
        "SELECT * FROM career_history WHERE user_id = ? ORDER BY start_date DESC", 
        'i', 
        [$userId]
    );
}

/**
 * Gets company profile information
 * 
 * @param int $userId The user ID
 * @return array|null Company profile data or null if not found
 */
function getCompanyProfile($userId) {
    return fetchRow("SELECT * FROM companies WHERE user_id = ?", 'i', [$userId]);
}

/**
 * Check if company's license is approved
 * 
 * @param int $companyId The company ID
 * @return boolean True if license is approved, false otherwise
 */
function isCompanyLicenseApproved($companyId) {
    $company = fetchRow("SELECT license_status FROM companies WHERE company_id = ?", 'i', [$companyId]);
    return $company && $company['license_status'] === 'Approved';
}

/**
 * Format salary range
 * 
 * @param float $min Minimum salary
 * @param float $max Maximum salary
 * @return string Formatted salary range
 */
function formatSalaryRange($min, $max) {
    if (!$min && !$max) {
        return 'Not specified';
    }
    
    if ($min && !$max) {
        return 'RM' . number_format($min);
    }
    
    if (!$min && $max) {
        return 'Up to RM' . number_format($max);
    }
    
    return 'RM' . number_format($min) . ' - RM' . number_format($max);
}

/**
 * Get all job types
 * 
 * @return array Job types
 */
function getJobTypes() {
    return [
        'Full-time',
        'Part-time',
        'Contract',
        'Temporary',
        'Internship',
        'Freelance',
        'Volunteer',
        'Remote',
        'Hybrid',
        'On-site',
        'Seasonal',
        'Apprenticeship',
        'Consultant',
        'Per Diem',
        'Work From Home',
        'Gig',
        'Entry-level',
        'Executive',
        'Commission-based',
        'Night Shift',
        'Travel Nursing',
        'Tenure-track',
        'Locum Tenens',
        'Performing Artist',
        'Adjunct'
    ];
}

/**
 * Get human-readable time elapsed string
 * 
 * @param string $datetime The datetime string
 * @return string Human-readable time elapsed
 */
function timeElapsed($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    
    return 'Just now';
}

/**
 * Validate email address
 * 
 * @param string $email The email to validate
 * @return boolean True if email is valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if user exists by email
 * 
 * @param string $email The email to check
 * @return boolean True if user exists, false otherwise
 */
function userExistsByEmail($email) {
    $user = fetchRow("SELECT user_id FROM users WHERE email = ?", 's', [$email]);
    return $user !== null;
}

/**
 * Get jobs by company ID
 * 
 * @param int $companyId The company ID
 * @return array Array of jobs
 */
function getJobsByCompany($companyId) {
    return fetchAll("SELECT * FROM jobs WHERE company_id = ? ORDER BY posted_date DESC", 'i', [$companyId]);
}

/**
 * Get applications by job ID
 * 
 * @param int $jobId The job ID
 * @return array Array of applications
 */
function getApplicationsByJob($jobId) {
    return fetchAll(
        "SELECT a.*, u.email, u.username, p.full_name 
        FROM applications a 
        JOIN users u ON a.user_id = u.user_id
        LEFT JOIN user_profiles p ON u.user_id = p.user_id
        WHERE a.job_id = ? 
        ORDER BY a.applied_date DESC", 
        'i', 
        [$jobId]
    );
}

/**
 * Get applications by user ID
 * 
 * @param int $userId The user ID
 * @return array Array of applications
 */
function getApplicationsByUser($userId) {
    return fetchAll(
        "SELECT a.*, j.job_title, c.company_name 
        FROM applications a 
        JOIN jobs j ON a.job_id = j.job_id
        JOIN companies c ON j.company_id = c.company_id
        WHERE a.user_id = ? 
        ORDER BY a.applied_date DESC", 
        'i', 
        [$userId]
    );
}

/**
 * Normalize job type string for consistent matching
 * 
 * @param string $jobType The job type string to normalize
 * @return string Normalized job type
 */
function normalizeJobType($jobType) {
    $jobType = strtolower($jobType);
    
    // Replace spaces and hyphens with empty string
    $jobType = str_replace([' ', '-'], '', $jobType);
    
    return $jobType;
}

/**
 * Check if a search term is specifically looking for a job type
 * 
 * @param string $term The search term
 * @return array|null ['job_type' => string, 'variations' => array] or null if not a job type search
 */
function identifyJobTypeSearch($term) {
    $term = strtolower(trim($term));
    
    // Map of specific job types and their variations
    $jobTypeMap = [
        'full-time' => ['full time', 'full-time', 'fulltime', 'full'],
        'part-time' => ['part time', 'part-time', 'parttime', 'part'],
        'remote' => ['remote', 'remote work', 'work remotely'],
        'work from home' => ['work from home', 'wfh', 'work-from-home', 'working from home'],
        'on-site' => ['on site', 'on-site', 'onsite', 'on location'],
        'hybrid' => ['hybrid', 'hybrid work'],
        'internship' => ['internship', 'intern', 'internships'],
        'contract' => ['contract', 'contractor'],
        'temporary' => ['temporary', 'temp'],
        'freelance' => ['freelance', 'freelancer']
    ];
    
    // Check if term exactly matches any variation
    foreach ($jobTypeMap as $jobType => $variations) {
        if (in_array($term, $variations)) {
            return [
                'job_type' => $jobType,
                'variations' => $variations,
                'exact_match' => true
            ];
        }
    }
    
    // Check if term contains specific job type indicators
    foreach ($jobTypeMap as $jobType => $variations) {
        foreach ($variations as $variation) {
            // Check if search is specifically targeting this job type
            if ($term === $variation || 
                (strlen($term) > 3 && stripos($term, $variation) !== false)) {
                return [
                    'job_type' => $jobType,
                    'variations' => $variations,
                    'exact_match' => false
                ];
            }
        }
    }
    
    // Special check for time-based searches
    if (stripos($term, 'time') !== false) {
        if (stripos($term, 'full') !== false) {
            return [
                'job_type' => 'full-time',
                'variations' => $jobTypeMap['full-time'],
                'exact_match' => false
            ];
        } else if (stripos($term, 'part') !== false) {
            return [
                'job_type' => 'part-time',
                'variations' => $jobTypeMap['part-time'],
                'exact_match' => false
            ];
        }
    }
    
    return null;
}

/**
 * Search for jobs with filter criteria
 * 
 * @param string $keywords Keywords to search for
 * @param string $location Location to filter by
 * @param array $jobTypes Job types to filter by
 * @param array $categories Categories to filter by
 * @param int $minSalary Minimum salary to filter by
 * @param int $maxSalary Maximum salary to filter by
 * @param string $datePosted Date filter (any, today, week, month)
 * @param string $sort Sorting method (newest, salary_high, salary_low, company)
 * @return array Array of jobs matching the search criteria
 */
function searchJobs($keywords = '', $location = '', $jobTypes = [], $categories = [], $minSalary = 0, $maxSalary = 0, $datePosted = 'any', $sort = 'newest') {
    $sql = "SELECT j.*, c.company_name 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.company_id 
            WHERE j.is_active = 1";
    $types = '';
    $params = [];
    
    // Add keyword search
    if (!empty($keywords)) {
        // Handle common job type variations
        $lowerKeywords = strtolower($keywords);
        
        // Check if the search is specifically for a job type
        $jobTypeInfo = identifyJobTypeSearch($keywords);
        
        // If we have a job type search
        if ($jobTypeInfo) {
            $jobType = $jobTypeInfo['job_type'];
            $isExactMatch = $jobTypeInfo['exact_match'];
            
            // Full-time and part-time need special handling to avoid overlap
            if ($jobType === 'full-time') {
                $sql .= " AND (
                    /* Title, description, requirements match */
                    (j.job_title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ?) 
                    OR 
                    /* Exact job type match with exclusion of part-time */
                    (
                        (LOWER(j.job_type) = ? OR LOWER(j.job_type) = ?) AND
                        LOWER(j.job_type) NOT LIKE ?
                    )
                    OR
                    /* Contains full and time, but not part */
                    (
                        LOWER(j.job_type) LIKE ? AND
                        LOWER(j.job_type) LIKE ? AND
                        LOWER(j.job_type) NOT LIKE ?
                    )
                    OR
                    /* Categories match */
                    j.categories LIKE ?
                )";
                $types .= 'sssssssssss';
                $params = array_merge(
                    $params, 
                    [
                        '%' . $keywords . '%',                 // Title match
                        '%' . $keywords . '%',                 // Description match 
                        '%' . $keywords . '%',                 // Requirements match
                        'full-time',                          // Exact job type match
                        'full time',                          // Alternative job type format
                        '%part%',                             // Exclude part-time
                        '%full%',                             // Contains "full"
                        '%time%',                             // Contains "time"
                        '%part%',                             // Exclude part-time
                        '%' . $keywords . '%'                 // Categories match
                    ]
                );
            } 
            else if ($jobType === 'part-time') {
                $sql .= " AND (
                    /* Title, description, requirements match */
                    (j.job_title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ?) 
                    OR 
                    /* Exact job type match with exclusion of full-time */
                    (
                        (LOWER(j.job_type) = ? OR LOWER(j.job_type) = ?) AND
                        LOWER(j.job_type) NOT LIKE ?
                    )
                    OR
                    /* Contains part and time, but not full */
                    (
                        LOWER(j.job_type) LIKE ? AND
                        LOWER(j.job_type) LIKE ? AND
                        LOWER(j.job_type) NOT LIKE ?
                    )
                    OR
                    /* Categories match */
                    j.categories LIKE ?
                )";
                $types .= 'sssssssssss';
                $params = array_merge(
                    $params, 
                    [
                        '%' . $keywords . '%',                 // Title match
                        '%' . $keywords . '%',                 // Description match 
                        '%' . $keywords . '%',                 // Requirements match
                        'part-time',                          // Exact job type match
                        'part time',                          // Alternative job type format
                        '%full%',                             // Exclude full-time
                        '%part%',                             // Contains "part"
                        '%time%',                             // Contains "time"
                        '%full%',                             // Exclude full-time
                        '%' . $keywords . '%'                 // Categories match
                    ]
                );
            }
            // Remote needs to exclude onsite
            else if ($jobType === 'remote' || $jobType === 'work from home') {
                $sql .= " AND (
                    /* Title, description, requirements match */
                    (j.job_title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ?) 
                    OR 
                    /* Exact job type match */
                    (LOWER(j.job_type) = ? OR LOWER(j.job_type) = ?) 
                    OR
                    /* Categories match */
                    j.categories LIKE ?
                )";
                $types .= 'ssssss';
                $params = array_merge(
                    $params, 
                    [
                        '%' . $keywords . '%',                 // Title match
                        '%' . $keywords . '%',                 // Description match 
                        '%' . $keywords . '%',                 // Requirements match
                        'remote',                             // Exact job type match
                        'work from home',                     // Alternative job type format
                        '%' . $keywords . '%'                 // Categories match
                    ]
                );
            }
            // Standard job type search for others
            else {
                $sql .= " AND (
                    /* Title, description, requirements match */
                    j.job_title LIKE ? OR 
                    j.description LIKE ? OR 
                    j.requirements LIKE ? OR
                    /* Exact job type match */
                    LOWER(j.job_type) = ? OR
                    /* Categories match */
                    j.categories LIKE ?
                )";
                $types .= 'sssss';
                $params = array_merge(
                    $params, 
                    [
                        '%' . $keywords . '%',                // Title match
                        '%' . $keywords . '%',                // Description match 
                        '%' . $keywords . '%',                // Requirements match
                        $jobType,                            // Exact job type match
                        '%' . $keywords . '%'                // Categories match
                    ]
                );
            }
        } 
        // General search (not specific to job type)
        else {
            // Build comprehensive job type patterns for general search
            $jobTypePatterns = [];
            $jobTypeParams = [];
            
            // Standard keyword search
            $jobTypePatterns[] = "j.job_title LIKE ?";
            $jobTypePatterns[] = "j.description LIKE ?";
            $jobTypePatterns[] = "j.requirements LIKE ?";
            $jobTypePatterns[] = "j.categories LIKE ?";
            $jobTypeParams[] = '%' . $keywords . '%';
            $jobTypeParams[] = '%' . $keywords . '%';
            $jobTypeParams[] = '%' . $keywords . '%';
            $jobTypeParams[] = '%' . $keywords . '%';
            
            // Only include job type searching if keywords is long enough 
            // or contains specific job-related terms
            $jobTypeTerms = ['job', 'work', 'career', 'position', 'employment', 'time', 'remote', 'onsite', 'site', 'hybrid'];
            $containsJobTerm = false;
            
            foreach ($jobTypeTerms as $term) {
                if (stripos($keywords, $term) !== false) {
                    $containsJobTerm = true;
                    break;
                }
            }
            
            if (strlen($keywords) > 3 || $containsJobTerm) {
                // Normalized with wildcards
                $normalizedKeyword = str_replace([' ', '-'], '%', $keywords);
                $keywordParam = '%' . $normalizedKeyword . '%';
                
                // Pattern 1: Direct match
                $jobTypePatterns[] = "LOWER(j.job_type) LIKE LOWER(?)";
                $jobTypeParams[] = '%' . $keywords . '%';
                
                // Pattern 2: Normalized with wildcards - only for multi-word searches
                if (strpos($keywords, ' ') !== false || strpos($keywords, '-') !== false) {
                    $jobTypePatterns[] = "LOWER(j.job_type) LIKE LOWER(?)";
                    $jobTypeParams[] = $keywordParam;
                    
                    // Pattern 3: Replace hyphens with spaces
                    $jobTypePatterns[] = "REPLACE(LOWER(j.job_type), '-', ' ') LIKE LOWER(?)";
                    $jobTypeParams[] = '%' . str_replace('-', ' ', $keywords) . '%';
                    
                    // Pattern 4: Remove spaces and hyphens - only for specific job type terms
                    if (strlen($keywords) > 4) {
                        $jobTypePatterns[] = "REPLACE(LOWER(j.job_type), '-', '') LIKE REPLACE(LOWER(?), ' ', '')";
                        $jobTypeParams[] = '%' . str_replace(' ', '', $keywords) . '%';
                    }
                }
            }
            
            // Combine all patterns
            $sql .= " AND (" . implode(" OR ", $jobTypePatterns) . ")";
            $types .= str_repeat('s', count($jobTypeParams));
            $params = array_merge($params, $jobTypeParams);
        }
    }
    
    // Add location search
    if (!empty($location)) {
        $sql .= " AND j.location LIKE ?";
        $types .= 's';
        $params[] = '%' . $location . '%';
    }
    
    // Filter by job types
    if (!empty($jobTypes)) {
        $jobTypeConditions = [];
        foreach ($jobTypes as $type) {
            // Make job type matching case-insensitive and allow fuzzy/partial matching
            // Convert spaces/hyphens to allow flexible matching
            $cleanType = str_replace([' ', '-'], '%', $type);
            
            // Multiple ways to match job types
            $jobTypeConditions[] = "LOWER(j.job_type) LIKE LOWER(?)";
            $types .= 's';
            $params[] = '%' . $cleanType . '%';
            
            // Also match when spaces and hyphens are different
            $jobTypeConditions[] = "REPLACE(LOWER(j.job_type), '-', '') LIKE REPLACE(LOWER(?), ' ', '')";
            $types .= 's';
            $params[] = '%' . str_replace(' ', '', $type) . '%';
            
            // Direct equality match
            $jobTypeConditions[] = "LOWER(j.job_type) = LOWER(?)";
            $types .= 's';
            $params[] = $type;
        }
        $sql .= " AND (" . implode(" OR ", $jobTypeConditions) . ")";
    }
    
    // Filter by categories
    if (!empty($categories)) {
        $categoryConditions = [];
        foreach ($categories as $category) {
            // Use LIKE for matching categories in comma-separated list with spaces
            $categoryConditions[] = "j.categories LIKE ?";
            $types .= 's';
            $params[] = '%' . $category . '%';
        }
        $sql .= " AND (" . implode(" OR ", $categoryConditions) . ")";
    }
    
    // Filter by minimum salary
    if (!empty($minSalary) && $minSalary > 0) {
        $sql .= " AND j.salary_min >= ?";
        $types .= 'd';
        $params[] = $minSalary;
    }
    
    // Filter by maximum salary
    if (!empty($maxSalary) && $maxSalary > 0) {
        $sql .= " AND j.salary_max <= ?";
        $types .= 'd';
        $params[] = $maxSalary;
    }
    
    // Filter by date posted
    if ($datePosted !== 'any') {
        $now = date('Y-m-d H:i:s');
        switch ($datePosted) {
            case 'today':
                $sql .= " AND j.posted_date >= DATE_SUB(?, INTERVAL 1 DAY)";
                break;
            case 'week':
                $sql .= " AND j.posted_date >= DATE_SUB(?, INTERVAL 1 WEEK)";
                break;
            case 'month':
                $sql .= " AND j.posted_date >= DATE_SUB(?, INTERVAL 1 MONTH)";
                break;
            case 'three_months':
                $sql .= " AND j.posted_date >= DATE_SUB(?, INTERVAL 3 MONTH)";
                break;
            case 'six_months':
                $sql .= " AND j.posted_date >= DATE_SUB(?, INTERVAL 6 MONTH)";
                break;
        }
        $types .= 's';
        $params[] = $now;
    }
    
    // Add sorting
    switch ($sort) {
        case 'salary_high':
            $sql .= " ORDER BY j.salary_max DESC";
            break;
        case 'salary_low':
            $sql .= " ORDER BY j.salary_min ASC";
            break;
        case 'company':
            $sql .= " ORDER BY c.company_name ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY j.posted_date DESC";
            break;
    }
    
    return fetchAll($sql, $types, $params);
}

/**
 * Get all pending company licenses for GoWork worker review
 * 
 * @return array Array of companies with pending licenses
 */
function getPendingLicenses() {
    return fetchAll(
        "SELECT c.*, u.email, u.username 
        FROM companies c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.license_status = 'Pending' AND c.license_path IS NOT NULL 
        ORDER BY c.created_at ASC"
    );
}

/**
 * Approve or reject a company license
 * 
 * @param int $companyId The company ID
 * @param string $status The new status ('Approved' or 'Not Approved')
 * @return boolean True if update successful, false otherwise
 */
function updateLicenseStatus($companyId, $status) {
    if ($status !== 'Approved' && $status !== 'Not Approved') {
        return false;
    }
    
    return updateData('companies', ['license_status' => $status], 'company_id', $companyId);
}

/**
 * Count total jobs
 * 
 * @return int Number of jobs
 */
function countTotalJobs() {
    $result = fetchRow("SELECT COUNT(*) as total FROM jobs WHERE is_active = 1");
    return $result ? $result['total'] : 0;
}

/**
 * Count total users by type
 * 
 * @param int $userType The user type
 * @return int Number of users
 */
function countUsersByType($userType) {
    $result = fetchRow("SELECT COUNT(*) as total FROM users WHERE user_type = ?", 'i', [$userType]);
    return $result ? $result['total'] : 0;
}

/**
 * Count total applications
 * 
 * @return int Number of applications
 */
function countTotalApplications() {
    $result = fetchRow("SELECT COUNT(*) as total FROM applications");
    return $result ? $result['total'] : 0;
}

/**
 * Get latest jobs
 * 
 * @param int $limit Number of jobs to return
 * @return array Array of latest jobs
 */
function getLatestJobs($limit = 10) {
    return fetchAll(
        "SELECT j.*, c.company_name 
        FROM jobs j 
        JOIN companies c ON j.company_id = c.company_id 
        WHERE j.is_active = 1 
        ORDER BY j.posted_date DESC 
        LIMIT ?", 
        'i', 
        [$limit]
    );
}

/**
 * Get human-readable user type name
 * 
 * @param int $userType The user type ID
 * @return string User type name
 */
function getUserTypeName($userType) {
    switch ($userType) {
        case USER_TYPE_JOBSEEKER:
            return 'Job Seeker';
        case USER_TYPE_COMPANY:
            return 'Company';
        case USER_TYPE_GOWORK:
            return 'GoWork Staff';
        case USER_TYPE_ADMIN:
            return 'Admin';
        default:
            return 'Unknown';
    }
}

/**
 * Get worker id from user id
 * 
 * @param int $userId The user id
 * @return int|null The worker id or null if not found
 */
function getWorkerId($userId) {
    $sql = "SELECT worker_id FROM gowork_workers WHERE user_id = ?";
    $result = fetchRow($sql, 'i', [$userId]);
    return $result ? $result['worker_id'] : null;
}

/**
 * Check if worker can manage users
 * 
 * @param int $workerId The worker id
 * @return boolean True if worker can manage users, false otherwise
 */
function canWorkerManageUsers($workerId) {
    $sql = "SELECT can_manage_users FROM gowork_workers WHERE worker_id = ?";
    $result = fetchRow($sql, 'i', [$workerId]);
    return $result ? (bool)$result['can_manage_users'] : false;
}

/**
 * Get all culture quiz questions with their options
 * 
 * @return array Array of questions with options
 */
function getCultureQuizQuestions() {
    $questions = fetchAll("SELECT * FROM culture_quiz_questions ORDER BY question_id");
    
    foreach ($questions as &$question) {
        $question['options'] = fetchAll(
            "SELECT * FROM culture_quiz_options WHERE question_id = ? ORDER BY option_id",
            'i', 
            [$question['question_id']]
        );
    }
    
    return $questions;
}

/**
 * Save user's culture quiz results
 * 
 * @param int $userId The user ID
 * @param array $cultureProfile The culture profile data
 * @return int|bool The result ID or false on failure
 */
function saveUserCultureResults($userId, $cultureProfile) {
    // Check if user already has results
    $existingResult = fetchRow(
        "SELECT * FROM user_culture_results WHERE user_id = ?",
        'i',
        [$userId]
    );
    
    $profileJson = json_encode($cultureProfile);
    
    if ($existingResult) {
        // Update existing results
        return updateData(
            'user_culture_results',
            ['culture_profile' => $profileJson],
            'user_id',
            $userId
        );
    } else {
        // Insert new results
        return insertData(
            'user_culture_results',
            [
                'user_id' => $userId,
                'culture_profile' => $profileJson
            ]
        );
    }
}

/**
 * Get user's culture quiz results
 * 
 * @param int $userId The user ID
 * @return array|null The culture profile or null if not found
 */
function getUserCultureResults($userId) {
    $result = fetchRow(
        "SELECT * FROM user_culture_results WHERE user_id = ?",
        'i',
        [$userId]
    );
    
    if ($result) {
        $result['culture_profile'] = json_decode($result['culture_profile'], true);
    }
    
    return $result;
}

/**
 * Save company culture information
 * 
 * @param int $companyId The company ID
 * @param array $cultureInfo The culture information
 * @return bool True on success, false on failure
 */
function saveCompanyCulture($companyId, $cultureInfo) {
    $cultureJson = json_encode($cultureInfo);
    
    return updateData(
        'companies',
        ['company_culture' => $cultureJson],
        'company_id',
        $companyId
    );
}

/**
 * Get company culture information
 * 
 * @param int $companyId The company ID
 * @return array|null The culture information or null if not found
 */
function getCompanyCulture($companyId) {
    $company = fetchRow(
        "SELECT company_culture FROM companies WHERE company_id = ?",
        'i',
        [$companyId]
    );
    
    if ($company && $company['company_culture']) {
        return json_decode($company['company_culture'], true);
    }
    
    return null;
}

/**
 * Calculate culture fit score between user and company
 * 
 * @param int $userId The user ID
 * @param int $companyId The company ID
 * @return array The fit score and matching attributes
 */
function calculateCultureFitScore($userId, $companyId) {
    $userCulture = getUserCultureResults($userId);
    $companyCulture = getCompanyCulture($companyId);
    
    if (!$userCulture || !$companyCulture) {
        return [
            'score' => 0,
            'matches' => [],
            'mismatches' => []
        ];
    }
    
    $userValues = $userCulture['culture_profile']['values'];
    $companyValues = $companyCulture['values'];
    
    $matches = [];
    $mismatches = [];
    $matchCount = 0;
    
    // Debug log to see the actual data formats
    error_log("User culture values: " . json_encode($userValues));
    error_log("Company culture values: " . json_encode($companyValues));
    
    // Compare each user value with company values
    foreach ($userValues as $userAttribute => $userValue) {
        $matched = false;
        
        // User data format: $userAttribute may be something like "collaborative" 
        // and $userValue is also "collaborative"
        // Company data format: key is attribute name like "work_environment" and value is "collaborative"
        
        // Method 1: Direct match if company has the same attribute-value pair
        if (isset($companyValues[$userAttribute]) && $companyValues[$userAttribute] == $userValue) {
            $matches[] = $userAttribute;
            $matchCount++;
            $matched = true;
        } 
        // Method 2: Check if the user's attribute name is a value in any of the company's attributes
        else {
            foreach ($companyValues as $companyAttribute => $companyValue) {
                // If either the attribute name or value from user matches company's value
                if ($userAttribute == $companyValue || $userValue == $companyValue) {
                    $matches[] = $companyAttribute;
                    $matchCount++;
                    $matched = true;
                    break;
                }
            }
        }
        
        if (!$matched && isset($companyValues[$userAttribute])) {
            $mismatches[] = $userAttribute;
        }
    }
    
    // New scoring system: 10 points per match, max 100 points
    $score = min(100, $matchCount * 10);
    
    return [
        'score' => $score,
        'matches' => $matches,
        'mismatches' => $mismatches
    ];
}

/**
 * Get companies that match user's culture profile
 * 
 * @param int $userId The user ID
 * @param int $limit The maximum number of companies to return
 * @param bool $includeAll Whether to include all companies even without a match score
 * @return array Array of companies with match scores
 */
function getMatchingCompanies($userId, $limit = 10, $includeAll = false) {
    $companies = fetchAll("SELECT c.*, u.username FROM companies c JOIN users u ON c.user_id = u.user_id");
    $matches = [];
    $nonMatches = [];
    
    // Get user culture profile
    $userCulture = getUserCultureResults($userId);
    
    foreach ($companies as $company) {
        // Calculate match score
        $matchData = calculateCultureFitScore($userId, $company['company_id']);
        
        // Add match score to company data
        $company['match_score'] = $matchData['score'];
        $company['matches'] = $matchData['matches'];
        $company['mismatches'] = $matchData['mismatches'];
        
        // If company has a match score or includeAll is true
        if ($matchData['score'] > 0) {
            $matches[] = $company;
        } else {
            // For companies without culture data or zero match score
            // Calculate proximity score (lower is better)
            $proximityScore = calculateProximityScore($userCulture, $company);
            $company['proximity_score'] = $proximityScore;
            $nonMatches[] = $company;
        }
    }
    
    // Sort matched companies by match score
    usort($matches, function($a, $b) {
        return $b['match_score'] - $a['match_score'];
    });
    
    // Sort non-matched companies by proximity score
    usort($nonMatches, function($a, $b) {
        return $a['proximity_score'] - $b['proximity_score'];
    });
    
    // Combine matches and non-matches with matches first
    $allCompanies = array_merge($matches, $nonMatches);
    
    // If we need to include all companies
    if ($includeAll) {
        // Ensure we return at least $limit companies total
        return array_slice($allCompanies, 0, $limit);
    } else {
        // Return only matched companies
        return array_slice($matches, 0, $limit);
    }
}

/**
 * Calculate proximity score for companies without explicit culture match
 * Lower score means closer to the user's preferences
 * 
 * @param array|null $userCulture The user's culture profile
 * @param array $company The company data
 * @return int Proximity score (lower is better)
 */
function calculateProximityScore($userCulture, $company) {
    // Default high score (lower is better)
    $baseScore = 100;
    
    // If no user culture data, return base score
    if (!$userCulture) return $baseScore;
    
    $score = $baseScore;
    
    // Improve score for companies with culture data (even if no match)
    if (!empty($company['company_culture'])) {
        $score -= 20;
    }
    
    // Improve score for verified companies
    if (isset($company['license_status']) && $company['license_status'] === 'Approved') {
        $score -= 10;
    }
    
    // Improve score for companies with recent job postings
    $recentJobs = fetchRow(
        "SELECT COUNT(*) as count FROM jobs 
         WHERE company_id = ? AND posted_date > DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'i',
        [$company['company_id']]
    );
    
    if ($recentJobs && $recentJobs['count'] > 0) {
        $score -= min(15, $recentJobs['count'] * 3); // Max 15 points reduction
    }
    
    // Improve score based on application count (popular companies)
    $applications = fetchRow(
        "SELECT COUNT(DISTINCT a.user_id) as count 
         FROM applications a 
         JOIN jobs j ON a.job_id = j.job_id 
         WHERE j.company_id = ?",
        'i',
        [$company['company_id']]
    );
    
    if ($applications && $applications['count'] > 0) {
        $score -= min(15, sqrt($applications['count']) * 3); // Max 15 points reduction
    }
    
    // Randomize slightly to avoid ties and provide variety
    $score += mt_rand(-5, 5);
    
    return max(0, $score);
}

/**
 * Get enhanced career history for a user with error handling
 * 
 * @param int $userId The user ID
 * @return array Array of career history entries with formatted dates
 */
function getEnhancedUserCareerHistory($userId) {
    if (empty($userId)) {
        return [];
    }
    
    // Check if the user is a jobseeker
    $user = fetchRow("SELECT user_type FROM users WHERE user_id = ?", 'i', [$userId]);
    if (!$user) {
        return []; // User not found
    }
    
    // Ensure user type is correctly compared as integer
    $userType = (int)$user['user_type'];
    if ($userType !== USER_TYPE_JOBSEEKER) {
        return []; // Return empty array for non-jobseekers
    }
    
    // Get all career history entries for this user
    $query = "SELECT * FROM career_history WHERE user_id = ? ORDER BY start_date DESC";
    $careerHistory = fetchAll($query, 'i', [$userId]);
    
    if (!$careerHistory) {
        return [];
    }
    
    // Format the career history entries
    $formattedHistory = [];
    foreach ($careerHistory as $entry) {
        // Ensure the career_id exists
        if (!isset($entry['career_id']) && isset($entry['id'])) {
            $entry['career_id'] = $entry['id'];
        }
        
        // Format dates for display
        $startDateFormatted = '';
        $endDateFormatted = 'Present';
        $duration = '';
        
        if (!empty($entry['start_date'])) {
            try {
                $startDate = new DateTime($entry['start_date']);
                $startDateFormatted = $startDate->format('M Y');
                
                $endDate = !empty($entry['end_date']) ? new DateTime($entry['end_date']) : new DateTime();
                if (!empty($entry['end_date'])) {
                    $endDateFormatted = $endDate->format('M Y');
                }
                
                // Calculate duration
                $interval = $startDate->diff($endDate);
                $years = $interval->y;
                $months = $interval->m;
                
                if ($years > 0) {
                    $duration = $years . ' year' . ($years > 1 ? 's' : '');
                    if ($months > 0) {
                        $duration .= ', ' . $months . ' month' . ($months > 1 ? 's' : '');
                    }
                } else {
                    $duration = $months . ' month' . ($months > 1 ? 's' : '');
                }
            } catch (Exception $e) {
                // Handle date formatting errors
                $startDateFormatted = $entry['start_date'];
                $endDateFormatted = $entry['end_date'] ?? 'Present';
                $duration = 'Unknown';
            }
        }
        
        // Add formatted data to the entry
        $entry['start_date_formatted'] = $startDateFormatted;
        $entry['end_date_formatted'] = $endDateFormatted;
        $entry['duration'] = $duration;
        
        $formattedHistory[] = $entry;
    }
    
    return $formattedHistory;
}
