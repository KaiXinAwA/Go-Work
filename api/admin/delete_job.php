<?php
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Get and validate job ID
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($jobId <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
    exit;
}

// Get database connection
$conn = getDbConnection();

try {
    // Get job information first (to know which company to return to)
    $job = fetchRow("SELECT job_id, company_id FROM jobs WHERE job_id = ?", "i", [$jobId]);

    if (!$job) {
        $_SESSION['error'] = 'Job not found';
        redirectTo(SITE_URL . '/pages/admin/companies.php');
        exit;
    }

    $companyId = $job['company_id'];

    // Start transaction directly
    $conn->begin_transaction();

    // Check if the job has any applications
    $checkAppsQuery = "SELECT COUNT(*) as app_count FROM applications WHERE job_id = ?";
    $appCount = fetchRow($checkAppsQuery, "i", [$jobId]);

    // Delete all applications associated with this job if they exist
    if ($appCount && $appCount['app_count'] > 0) {
        $deleteAppsQuery = "DELETE FROM applications WHERE job_id = ?";
        executeQuery($deleteAppsQuery, "i", [$jobId]);
    }

    // Delete the job
    $deleteJobQuery = "DELETE FROM jobs WHERE job_id = ?";
    executeQuery($deleteJobQuery, "i", [$jobId]);

    // If we got here, commit the transaction directly
    $conn->commit();
    $_SESSION['success'] = 'Job and all associated applications deleted successfully';
    redirectTo(SITE_URL . '/pages/admin/view_company.php?id=' . $companyId);

} catch (Exception $e) {
    // An error occurred, rollback and show error
    try {
        $conn->rollback();
    } catch (Exception $rollbackError) {
        // Ignore rollback errors
    }
    
    $_SESSION['error'] = 'Failed to delete job: ' . $e->getMessage();
    redirectTo(SITE_URL . '/pages/admin/view_job.php?id=' . $jobId);
}
?> 