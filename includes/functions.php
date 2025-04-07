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
    // You would implement this with your email service (Resend)
    // This is a placeholder function that would use the Resend API
    
    $subject = SITE_NAME . ' - Password Reset';
    $message = "Your password has been reset. Your new password is: {$newPassword}\n\n";
    $message .= "Please login and change your password immediately.";
    
    // If Resend API key is not defined, log the email
    if (!defined('RESEND_API_KEY') || empty(RESEND_API_KEY)) {
        error_log("Email would be sent to: $email");
        error_log("Subject: $subject");
        error_log("Message: $message");
        return true;
    }
    
    // TODO: Implement actual email sending with Resend when API key is available
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
        return '$' . number_format($min);
    }
    
    if (!$min && $max) {
        return 'Up to $' . number_format($max);
    }
    
    return '$' . number_format($min) . ' - $' . number_format($max);
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
        'Freelance'
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
 * 高级职位搜索函数
 */
function searchJobs($keywords, $location, $workClassifications = [], $workTypes = [], $salaryMin = null, $salaryMax = null, $skills = [], $datePosted = 'any', $sort = 'newest') {
    $params = [];
    $types = '';
    $conditions = ['j.is_active = 1'];
    
    // 关键词搜索
    if (!empty($keywords)) {
        $conditions[] = "(j.job_title LIKE ? OR j.description LIKE ? OR c.company_name LIKE ?)";
        $keyword = "%$keywords%";
        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
        $types .= 'sss';
    }
    
    // 地点搜索
    if (!empty($location)) {
        $conditions[] = "(l.name LIKE ? OR l.region LIKE ?)";
        $locationParam = "%$location%";
        $params[] = $locationParam;
        $params[] = $locationParam;
        $types .= 'ss';
    }
    
    // 工作分类过滤
    if (!empty($workClassifications)) {
        $placeholders = str_repeat('?,', count($workClassifications) - 1) . '?';
        $conditions[] = "j.work_classification_id IN ($placeholders)";
        $params = array_merge($params, $workClassifications);
        $types .= str_repeat('i', count($workClassifications));
    }
    
    // 工作类型过滤
    if (!empty($workTypes)) {
        $placeholders = str_repeat('?,', count($workTypes) - 1) . '?';
        $conditions[] = "j.work_type_id IN ($placeholders)";
        $params = array_merge($params, $workTypes);
        $types .= str_repeat('i', count($workTypes));
    }
    
    // 薪资范围过滤
    if ($salaryMin !== null) {
        $conditions[] = "j.salary_max >= ?";
        $params[] = $salaryMin;
        $types .= 'd';
    }
    if ($salaryMax !== null) {
        $conditions[] = "j.salary_min <= ?";
        $params[] = $salaryMax;
        $types .= 'd';
    }
    
    // 技能要求过滤
    if (!empty($skills)) {
        $placeholders = str_repeat('?,', count($skills) - 1) . '?';
        $conditions[] = "EXISTS (
            SELECT 1 FROM job_skills js 
            WHERE js.job_id = j.job_id 
            AND js.skill_id IN ($placeholders)
        )";
        $params = array_merge($params, $skills);
        $types .= str_repeat('i', count($skills));
    }
    
    // 发布日期过滤
    if ($datePosted != 'any') {
        switch ($datePosted) {
            case 'today':
                $conditions[] = "DATE(j.posted_date) = CURDATE()";
                break;
            case 'week':
                $conditions[] = "j.posted_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $conditions[] = "j.posted_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
        }
    }
    
    // 构建SQL查询
    $sql = "SELECT DISTINCT j.*, c.company_name, wc.name as classification_name, wt.name as work_type_name, l.name as location_name 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.company_id 
            LEFT JOIN work_classifications wc ON j.work_classification_id = wc.classification_id
            LEFT JOIN work_types wt ON j.work_type_id = wt.type_id
            LEFT JOIN locations l ON j.location_id = l.location_id
            WHERE " . implode(' AND ', $conditions);
    
    // 排序
    switch ($sort) {
        case 'salary_high':
            $sql .= " ORDER BY j.salary_max DESC";
            break;
        case 'salary_low':
            $sql .= " ORDER BY j.salary_min ASC";
            break;
        case 'oldest':
            $sql .= " ORDER BY j.posted_date ASC";
            break;
        default: // newest
            $sql .= " ORDER BY j.posted_date DESC";
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
