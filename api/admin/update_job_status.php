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

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
    exit;
}

// Get and validate form data
$jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

// Validate job ID
if ($jobId <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
    exit;
}

// Validate active status
if ($isActive !== 0 && $isActive !== 1) {
    $_SESSION['error'] = 'Invalid status value';
    redirectTo(SITE_URL . '/pages/admin/view_job.php?id=' . $jobId);
    exit;
}

// Check if job exists
$job = fetchRow("SELECT job_id, company_id FROM jobs WHERE job_id = ?", "i", [$jobId]);

if (!$job) {
    $_SESSION['error'] = 'Job not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
    exit;
}

// Update job status
$sql = "UPDATE jobs SET is_active = ? WHERE job_id = ?";
$success = executeQuery($sql, "ii", [$isActive, $jobId]);

if ($success) {
    $_SESSION['success'] = 'Job status updated successfully';
    redirectTo(SITE_URL . '/pages/admin/view_job.php?id=' . $jobId);
} else {
    $_SESSION['error'] = 'Failed to update job status';
    redirectTo(SITE_URL . '/pages/admin/view_job.php?id=' . $jobId);
}
?> 