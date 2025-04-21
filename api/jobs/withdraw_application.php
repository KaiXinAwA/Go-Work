<?php
/**
 * Withdraw Application API Endpoint
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to withdraw applications';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get application ID from POST request
$application_id = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;

// Validate application ID
if ($application_id <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    redirectTo(SITE_URL . '/pages/user/applications.php');
}

// Get user data
$user = getCurrentUser();

// Check if the application belongs to this user
$sql = "SELECT * FROM applications WHERE application_id = ? AND user_id = ?";
$application = fetchRow($sql, 'ii', [$application_id, $user['user_id']]);

if (!$application) {
    $_SESSION['error'] = 'Application not found or does not belong to you';
    redirectTo(SITE_URL . '/pages/user/applications.php');
}

// Update application status to "Withdrawn"
try {
    $updated = updateData('applications', ['status' => 'Withdrawn'], 'application_id', $application_id);

    if (!$updated) {
        $_SESSION['error'] = 'Failed to withdraw application. Please try again.';
        redirectTo(SITE_URL . '/pages/user/applications.php');
    }

    // Set success message
    $_SESSION['success'] = 'Application withdrawn successfully';

    // Redirect back to the applications page
    redirectTo(SITE_URL . '/pages/user/applications.php');
} catch (Exception $e) {
    // Check if it's a data truncation issue
    if (strpos($e->getMessage(), 'Data truncated') !== false) {
        $_SESSION['error'] = 'Could not withdraw application due to database column length limitations. Please contact the administrator to run the migration scripts.';
    } else {
        $_SESSION['error'] = 'Failed to withdraw application: ' . $e->getMessage();
    }
    redirectTo(SITE_URL . '/pages/user/applications.php');
} 