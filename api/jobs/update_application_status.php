<?php
/**
 * Update Application Status API Endpoint
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
    $_SESSION['error'] = 'You must be logged in as a company to manage applications';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get application ID and status from query parameters
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Validate application ID and status
if ($application_id <= 0 || !in_array($status, ['Pending', 'Viewed', 'Shortlisted', 'Rejected', 'Withdrawn'])) {
    $_SESSION['error'] = 'Invalid parameters';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    $_SESSION['error'] = 'Company profile not found';
    redirectTo(SITE_URL . '/pages/company/dashboard.php');
}

// Check if the application belongs to a job from this company
$sql = "SELECT a.* FROM applications a 
        JOIN jobs j ON a.job_id = j.job_id 
        WHERE a.application_id = ? AND j.company_id = ?";

$application = fetchRow($sql, 'ii', [$application_id, $company['company_id']]);

if (!$application) {
    $_SESSION['error'] = 'Application not found or does not belong to your company';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Update application status
$updated = updateData('applications', ['status' => $status], 'application_id', $application_id);

if (!$updated) {
    $_SESSION['error'] = 'Failed to update application status. Please try again.';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Set success message
$_SESSION['success'] = 'Application status updated successfully';

// Get the referer to redirect back to the same page
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL . '/pages/company/applications.php';

// Redirect back to the applications page
redirectTo($referer);
