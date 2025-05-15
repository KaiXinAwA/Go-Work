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

// Check if user exists and is a jobseeker
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

if ($user['user_type'] != USER_TYPE_JOBSEEKER) {
    $_SESSION['error'] = 'This utility is only for job seeker accounts';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Debug information
echo "<h1>Career History Debug Information</h1>";
echo "<p>User ID: {$userId}</p>";
echo "<p>Username: {$user['username']}</p>";
echo "<p>User Type: {$user['user_type']}</p>";

// Check if user profile exists
$profile = fetchRow("SELECT * FROM user_profiles WHERE user_id = ?", 'i', [$userId]);
echo "<p>Profile exists: " . ($profile ? 'Yes' : 'No') . "</p>";

// Check if there are any career history entries in the database
$rawCareerEntries = fetchAll("SELECT * FROM career_history WHERE user_id = ?", 'i', [$userId]);
echo "<p>Raw career entries count: " . count($rawCareerEntries) . "</p>";

if (!empty($rawCareerEntries)) {
    echo "<h3>Raw Career Entries:</h3>";
    echo "<pre>";
    print_r($rawCareerEntries);
    echo "</pre>";
}

// Try to get enhanced career history
try {
    $enhancedCareerHistory = getEnhancedUserCareerHistory($userId);
    echo "<p>Enhanced career history entries count: " . count($enhancedCareerHistory) . "</p>";
    
    if (!empty($enhancedCareerHistory)) {
        echo "<h3>Enhanced Career History:</h3>";
        echo "<pre>";
        print_r($enhancedCareerHistory);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p>Error getting enhanced career history: " . $e->getMessage() . "</p>";
}

// Check the function definition
echo "<h3>Function Check:</h3>";
$functionExists = function_exists('getEnhancedUserCareerHistory');
echo "<p>getEnhancedUserCareerHistory function exists: " . ($functionExists ? 'Yes' : 'No') . "</p>";

if ($functionExists) {
    $reflection = new ReflectionFunction('getEnhancedUserCareerHistory');
    $filename = $reflection->getFileName();
    $startLine = $reflection->getStartLine();
    echo "<p>Function defined in: {$filename} (line {$startLine})</p>";
}

// Provide links to go back or fix issues
echo "<div style='margin-top: 20px;'>";
echo "<a href='" . SITE_URL . "/pages/admin/edit_user.php?id={$userId}' class='btn btn-primary'>Back to Edit User</a> ";
echo "</div>";
?> 