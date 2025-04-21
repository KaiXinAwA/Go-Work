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
$applicationId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
$status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';

// Validate application ID
if ($applicationId <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
    exit;
}

// Validate status
$validStatuses = ['Pending', 'Reviewed', 'Shortlisted', 'Rejected', 'Hired'];
if (!in_array($status, $validStatuses)) {
    $_SESSION['error'] = 'Invalid status value';
    redirectTo(SITE_URL . '/pages/admin/view_application.php?id=' . $applicationId);
    exit;
}

// Check if application exists
$application = fetchRow("SELECT application_id, job_id FROM applications WHERE application_id = ?", "i", [$applicationId]);

if (!$application) {
    $_SESSION['error'] = 'Application not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
    exit;
}

// Update application status
$sql = "UPDATE applications SET status = ? WHERE application_id = ?";
$success = executeQuery($sql, "si", [$status, $applicationId]);

if ($success) {
    $_SESSION['success'] = 'Application status updated successfully';
    redirectTo(SITE_URL . '/pages/admin/view_application.php?id=' . $applicationId);
} else {
    $_SESSION['error'] = 'Failed to update application status';
    redirectTo(SITE_URL . '/pages/admin/view_application.php?id=' . $applicationId);
}
?> 