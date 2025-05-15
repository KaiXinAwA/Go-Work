<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
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

// Fetch user data
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Output debug information
echo "<h1>User Type Debug Information</h1>";
echo "<p><strong>User ID:</strong> {$userId}</p>";
echo "<p><strong>Username:</strong> {$user['username']}</p>";
echo "<p><strong>User Type (Raw Value):</strong> {$user['user_type']}</p>";
echo "<p><strong>USER_TYPE_JOBSEEKER Constant:</strong> " . USER_TYPE_JOBSEEKER . "</p>";
echo "<p><strong>Is Job Seeker:</strong> " . ($user['user_type'] == USER_TYPE_JOBSEEKER ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Is Company:</strong> " . ($user['user_type'] == USER_TYPE_COMPANY ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Is GoWork Staff:</strong> " . ($user['user_type'] == USER_TYPE_GOWORK ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Is Admin:</strong> " . ($user['user_type'] == USER_TYPE_ADMIN ? 'YES' : 'NO') . "</p>";

// Check edit_user.php conditional logic
echo "<h2>Verification of edit_user.php Logic</h2>";
echo "<p>In edit_user.php, additional sections are shown when: <code>\$user['user_type'] == USER_TYPE_JOBSEEKER</code></p>";
echo "<p>This condition is currently: <strong>" . ($user['user_type'] == USER_TYPE_JOBSEEKER ? 'TRUE' : 'FALSE') . "</strong></p>";
echo "<p>Therefore, additional sections <strong>" . ($user['user_type'] == USER_TYPE_JOBSEEKER ? 'SHOULD' : 'SHOULD NOT') . "</strong> be displayed.</p>";

// Type comparison test
echo "<h2>Type Comparison Tests</h2>";
echo "<p>Loose comparison (==): " . (($user['user_type'] == USER_TYPE_JOBSEEKER) ? 'TRUE' : 'FALSE') . "</p>";
echo "<p>Strict comparison (===): " . (($user['user_type'] === USER_TYPE_JOBSEEKER) ? 'TRUE' : 'FALSE') . "</p>";
echo "<p>Int cast comparison (int): " . ((int)$user['user_type'] == USER_TYPE_JOBSEEKER ? 'TRUE' : 'FALSE') . "</p>";

// Check user type storage in database
echo "<h2>Database Value Check</h2>";
$databaseValue = fetchRow("SELECT user_type FROM users WHERE user_id = ?", 'i', [$userId]);
echo "<p>Raw Database Value: " . var_export($databaseValue['user_type'], true) . "</p>";
echo "<p>Data Type: " . gettype($databaseValue['user_type']) . "</p>";

// Actions
echo "<h2>Actions</h2>";
echo "<a href='" . SITE_URL . "/pages/admin/edit_user.php?id={$userId}' class='btn btn-primary'>Back to Edit User</a> ";
echo "<a href='" . SITE_URL . "/pages/admin/users_combined.php' class='btn btn-secondary'>Back to Users</a>";
?> 