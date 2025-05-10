<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/users.php');
    exit;
}

// Get data from POST
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$education = isset($_POST['education']) ? sanitizeInput($_POST['education']) : '';
$institution = isset($_POST['institution']) ? sanitizeInput($_POST['institution']) : '';
$completionStatus = isset($_POST['completion_status']) ? sanitizeInput($_POST['completion_status']) : '';
$educationHighlights = isset($_POST['education_highlights']) ? sanitizeInput($_POST['education_highlights']) : '';

// Validate user ID
if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Check if the user exists and is a jobseeker
$user = fetchRow("SELECT user_id, user_type FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Ensure user is a jobseeker
if ($user['user_type'] != USER_TYPE_JOBSEEKER) {
    $_SESSION['error'] = 'Education can only be updated for job seeker accounts';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Check if profile exists
$profile = fetchRow("SELECT profile_id FROM user_profiles WHERE user_id = ?", 'i', [$userId]);

// Prepare education data
$educationData = [
    'education' => $education,
    'institution' => $institution,
    'completion_status' => $completionStatus,
    'education_highlights' => $educationHighlights
];

if ($profile) {
    // Update existing profile
    $sql = "UPDATE user_profiles SET 
            education = ?, 
            institution = ?, 
            completion_status = ?, 
            education_highlights = ? 
            WHERE user_id = ?";
    $result = executeQuery($sql, 'ssssi', [
        $education, 
        $institution, 
        $completionStatus, 
        $educationHighlights, 
        $userId
    ]);
} else {
    // Create new profile
    $sql = "INSERT INTO user_profiles 
            (user_id, education, institution, completion_status, education_highlights) 
            VALUES (?, ?, ?, ?, ?)";
    $result = executeQuery($sql, 'issss', [
        $userId, 
        $education, 
        $institution, 
        $completionStatus, 
        $educationHighlights
    ]);
}

if ($result) {
    $_SESSION['success'] = 'Education information updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update education information';
}

redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId); 