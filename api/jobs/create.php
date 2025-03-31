<?php
/**
 * Job Creation API Endpoint
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to create jobs';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/company/dashboard.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

// Check if license is approved
if (!$company || $company['license_status'] !== 'Approved') {
    $_SESSION['error'] = 'You need an approved license to create jobs';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get and sanitize input
$job_title = isset($_POST['job_title']) ? sanitizeInput($_POST['job_title']) : '';
$job_type = isset($_POST['job_type']) ? sanitizeInput($_POST['job_type']) : '';
$location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
$salary_min = isset($_POST['salary_min']) && is_numeric($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
$salary_max = isset($_POST['salary_max']) && is_numeric($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
$description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
$requirements = isset($_POST['requirements']) ? sanitizeInput($_POST['requirements']) : '';
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate required fields
if (empty($job_title) || empty($job_type) || empty($location) || empty($description) || empty($requirements)) {
    $_SESSION['error'] = 'All required fields must be filled out';
    redirectTo(SITE_URL . '/pages/company/post_job.php');
}

// Create job data array
$jobData = [
    'company_id' => $company['company_id'],
    'job_title' => $job_title,
    'job_type' => $job_type,
    'location' => $location,
    'description' => $description,
    'requirements' => $requirements,
    'is_active' => $is_active,
    'posted_date' => date('Y-m-d H:i:s')
];

// Add salary data if provided
if ($salary_min !== null) {
    $jobData['salary_min'] = $salary_min;
}

if ($salary_max !== null) {
    $jobData['salary_max'] = $salary_max;
}

// Insert job into database
$job_id = insertData('jobs', $jobData);

if (!$job_id) {
    $_SESSION['error'] = 'Failed to create job. Please try again.';
    redirectTo(SITE_URL . '/pages/company/post_job.php');
}

// Set success message
$_SESSION['success'] = 'Job created successfully!';

// Redirect to job management page
redirectTo(SITE_URL . '/pages/company/jobs.php');
