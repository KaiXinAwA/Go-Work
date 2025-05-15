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
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Get data from POST
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$skills = isset($_POST['skills']) ? sanitizeInput($_POST['skills']) : '';

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
    $_SESSION['error'] = 'Skills can only be updated for job seeker accounts';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Check if profile exists
$profile = fetchRow("SELECT profile_id FROM user_profiles WHERE user_id = ?", 'i', [$userId]);

if ($profile) {
    // Update existing profile
    $sql = "UPDATE user_profiles SET skills = ? WHERE user_id = ?";
    $result = executeQuery($sql, 'si', [$skills, $userId]);
} else {
    // Create new profile
    $sql = "INSERT INTO user_profiles (user_id, skills) VALUES (?, ?)";
    $result = executeQuery($sql, 'is', [$userId, $skills]);
}

if ($result) {
    $_SESSION['success'] = 'Skills updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update skills';
}

redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId); 