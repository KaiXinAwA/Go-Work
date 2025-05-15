<?php
/**
 * API endpoint for deleting user resume
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to delete resume';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume-section');
}

// Get user data
$user = getCurrentUser();
$userProfile = getUserProfile($user['user_id']);

if (!$userProfile) {
    $_SESSION['error'] = 'User profile not found';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume-section');
}

// Check if user has a resume
if (empty($userProfile['resume_path'])) {
    $_SESSION['error'] = 'No resume found to delete';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume-section');
}

// Delete resume file
$resumePath = RESUME_DIR . $userProfile['resume_path'];
if (file_exists($resumePath)) {
    $deleted = @unlink($resumePath);
    if (!$deleted) {
        error_log('Resume Delete: Failed to delete file: ' . $resumePath);
        $_SESSION['error'] = 'Failed to delete resume file';
        redirectTo(SITE_URL . '/pages/user/profile.php#resume-section');
    }
    error_log('Resume Delete: Successfully deleted file: ' . $resumePath);
}

// Update database to remove resume reference
$updateData = [
    'resume_path' => null
];

$updateResult = updateData('user_profiles', $updateData, 'user_id', $user['user_id']);

if (!$updateResult) {
    error_log('Resume Delete: Database update failed');
    $_SESSION['error'] = 'Failed to update resume information in database';
    redirectTo(SITE_URL . '/pages/user/profile.php#resume-section');
}

// Success
$_SESSION['success'] = 'Resume deleted successfully';
redirectTo(SITE_URL . '/pages/user/profile.php#resume-section'); 