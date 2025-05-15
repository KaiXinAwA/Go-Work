<?php
/**
 * API endpoint for changing user password
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to change password';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php#password');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php#password');
    } else {
        redirectTo(SITE_URL);
    }
}

// Get and sanitize input
$current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validate input
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = 'All fields are required';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php#password');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php#password');
    } else {
        redirectTo(SITE_URL);
    }
}

// Validate password match
if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'New passwords do not match';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php#password');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php#password');
    } else {
        redirectTo(SITE_URL);
    }
}

// Validate password strength
if (strlen($new_password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters long';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php#password');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php#password');
    } else {
        redirectTo(SITE_URL);
    }
}

// Get user data
$user = getCurrentUser();

// Verify current password
if (!verifyPassword($current_password, $user['password'])) {
    $_SESSION['error'] = 'Current password is incorrect';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php#password');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php#password');
    } else {
        redirectTo(SITE_URL);
    }
}

// Hash new password
$hashed_password = hashPassword($new_password);

// Update password
$updateData = [
    'password' => $hashed_password
];

$updateResult = updateData('users', $updateData, 'user_id', $user['user_id']);

if (!$updateResult) {
    $_SESSION['error'] = 'Failed to update password';
    
    // Redirect based on user type
    if (hasUserType(USER_TYPE_JOBSEEKER)) {
        redirectTo(SITE_URL . '/pages/user/profile.php#password');
    } else if (hasUserType(USER_TYPE_COMPANY)) {
        redirectTo(SITE_URL . '/pages/company/profile.php#password');
    } else {
        redirectTo(SITE_URL);
    }
}

// Success
$_SESSION['success'] = 'Password changed successfully';

// Redirect based on user type
if (hasUserType(USER_TYPE_JOBSEEKER)) {
    redirectTo(SITE_URL . '/pages/user/profile.php#password');
} else if (hasUserType(USER_TYPE_COMPANY)) {
    redirectTo(SITE_URL . '/pages/company/profile.php#password');
} else {
    redirectTo(SITE_URL);
}
