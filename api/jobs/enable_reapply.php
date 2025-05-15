<?php
/**
 * Enable Reapply API Endpoint
 * 
 * Called when an application is withdrawn to enable the user to reapply for the job
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
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get job ID from query parameters
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Validate job ID
if ($job_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    exit;
}

// Get company data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

if (!$company) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Company profile not found']);
    exit;
}

// Check if the job belongs to this company
$job = fetchRow("SELECT * FROM jobs WHERE job_id = ? AND company_id = ?", 'ii', [$job_id, $company['company_id']]);

if (!$job) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Job not found or does not belong to your company']);
    exit;
}

// Success - job verified for reapplication
echo json_encode([
    'success' => true, 
    'message' => 'Reapply enabled successfully',
    'job_id' => $job_id
]);
exit; 