<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Debug information
echo "<h1>User and Career History Debug</h1>";

// Get user data
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user) {
    echo "<p>Error: User not found.</p>";
    exit;
}

echo "<h2>User Information</h2>";
echo "<p>User ID: {$userId}</p>";
echo "<p>Username: {$user['username']}</p>";
echo "<p>User Type: {$user['user_type']}</p>";
echo "<p>User Type Constant for Jobseeker: " . USER_TYPE_JOBSEEKER . "</p>";
echo "<p>Is Type Equal? " . ($user['user_type'] == USER_TYPE_JOBSEEKER ? 'Yes' : 'No') . "</p>";

// Try getting career history
echo "<h2>Career History</h2>";
$careerHistory = getEnhancedUserCareerHistory($userId);
echo "<p>Career history entries: " . count($careerHistory) . "</p>";

if (empty($careerHistory)) {
    echo "<p>No career history found.</p>";
} else {
    echo "<pre>";
    print_r($careerHistory);
    echo "</pre>";
}

echo "<p><a href='" . SITE_URL . "/pages/admin/edit_user.php?id={$userId}'>Return to Edit User</a></p>";
?> 