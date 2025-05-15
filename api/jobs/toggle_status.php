<?php
/**
 * Job Status Toggle API Endpoint
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
    $_SESSION['error'] = 'You must be logged in as a company to manage jobs';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get job ID and status from query parameters
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? (int)$_GET['status'] : null;

// Validate job ID and status
if ($job_id <= 0 || ($status !== 0 && $status !== 1)) {
    $_SESSION['error'] = 'Invalid parameters';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    $_SESSION['error'] = 'Company profile not found';
    redirectTo(SITE_URL . '/pages/company/dashboard.php');
}

// Check if the job belongs to this company
$job = fetchRow("SELECT * FROM jobs WHERE job_id = ? AND company_id = ?", 'ii', [$job_id, $company['company_id']]);

if (!$job) {
    $_SESSION['error'] = 'Job not found or does not belong to your company';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Update job status
$updated = updateData('jobs', ['is_active' => $status], 'job_id', $job_id);

if (!$updated) {
    $_SESSION['error'] = 'Failed to update job status. Please try again.';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Set success message
$_SESSION['success'] = $status ? 'Job activated successfully' : 'Job deactivated successfully';

// Redirect back to job management page
redirectTo(SITE_URL . '/pages/company/jobs.php');
