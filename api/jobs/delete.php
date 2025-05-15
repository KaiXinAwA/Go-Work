<?php
/**
 * Job Deletion API Endpoint
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

// Get job ID from query parameters
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate job ID
if ($job_id <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
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

// Begin transaction
$conn = getDbConnection();
$conn->begin_transaction();

try {
    // Delete related applications first
    $deleteApplications = $conn->query("DELETE FROM applications WHERE job_id = {$job_id}");
    
    if (!$deleteApplications) {
        throw new Exception('Failed to delete related applications');
    }
    
    // Delete the job
    $deleteJob = $conn->query("DELETE FROM jobs WHERE job_id = {$job_id}");
    
    if (!$deleteJob) {
        throw new Exception('Failed to delete job');
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    $_SESSION['success'] = 'Job deleted successfully';
    
    // Redirect to job management page
    redirectTo(SITE_URL . '/pages/company/jobs.php');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message
    $_SESSION['error'] = $e->getMessage();
    
    // Redirect back to job management page
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}
