<?php
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get application ID
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($application_id <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Get current user
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    $_SESSION['error'] = 'Company profile not found';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Verify the application exists and belongs to a job posted by this company
$sql = "SELECT a.* FROM applications a 
        JOIN jobs j ON a.job_id = j.job_id 
        WHERE a.application_id = ? 
        AND j.company_id = ? 
        AND a.status = 'Withdrawn'";

$application = fetchRow($sql, 'ii', [$application_id, $company['company_id']]);

if (!$application) {
    $_SESSION['error'] = 'Application not found or cannot be deleted (only withdrawn applications can be deleted)';
    redirectTo(SITE_URL . '/pages/company/applications.php');
}

// Delete the application
$sql = "DELETE FROM applications WHERE application_id = ?";
$result = executeQuery($sql, 'i', [$application_id]);

if ($result) {
    $_SESSION['success'] = 'Application has been permanently deleted';
} else {
    $_SESSION['error'] = 'Failed to delete application';
}

// Redirect back to applications page
$job_id = $application['job_id'];
$redirect_url = SITE_URL . '/pages/company/applications.php';

// If we came from a specific job's applications page, redirect back to it
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'job_id=') !== false) {
    $redirect_url .= '?job_id=' . $job_id;
}

redirectTo($redirect_url); 