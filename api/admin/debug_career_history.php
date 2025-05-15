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
echo "<h1>Career History Debug Information for User ID: {$userId}</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .debug-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .error { color: red; }
    .success { color: green; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    .btn { display: inline-block; padding: 8px 16px; background: #4CAF50; color: white; 
           text-decoration: none; border-radius: 4px; margin-right: 8px; }
    .btn-secondary { background: #6c757d; }
</style>";

// Basic user information
echo "<div class='debug-section'>";
echo "<h2>User Information</h2>";
echo "<p><strong>User ID:</strong> {$userId}</p>";
echo "<p><strong>Username:</strong> {$user['username']}</p>";
echo "<p><strong>Email:</strong> {$user['email']}</p>";
echo "<p><strong>User Type (ID):</strong> {$user['user_type']}</p>";
echo "<p><strong>User Type Name:</strong> " . getUserTypeName($user['user_type']) . "</p>";
echo "</div>";

// Check if user is a jobseeker
echo "<div class='debug-section'>";
echo "<h2>Job Seeker Status Check</h2>";
$isJobSeeker = (int)$user['user_type'] === USER_TYPE_JOBSEEKER;
echo "<p><strong>USER_TYPE_JOBSEEKER Constant Value:</strong> " . USER_TYPE_JOBSEEKER . "</p>";
echo "<p><strong>Is Job Seeker (int casting):</strong> " . ($isJobSeeker ? 
    "<span class='success'>YES</span>" : 
    "<span class='error'>NO</span>") . "</p>";
echo "<p><strong>Type Check:</strong> (int){$user['user_type']} === " . USER_TYPE_JOBSEEKER . "</p>";
echo "<p><strong>Raw Comparison:</strong> {$user['user_type']} == " . USER_TYPE_JOBSEEKER . 
    " result: " . ($user['user_type'] == USER_TYPE_JOBSEEKER ? 'true' : 'false') . "</p>";
echo "<p><strong>Strict Comparison:</strong> {$user['user_type']} === " . USER_TYPE_JOBSEEKER . 
    " result: " . ($user['user_type'] === USER_TYPE_JOBSEEKER ? 'true' : 'false') . "</p>";
echo "</div>";

// Examine user type variable in detail
echo "<div class='debug-section'>";
echo "<h2>User Type Variable Examination</h2>";
echo "<p><strong>Data Type:</strong> " . gettype($user['user_type']) . "</p>";
echo "<p><strong>User Type as String:</strong> '" . strval($user['user_type']) . "'</p>";
echo "<p><strong>User Type as Integer:</strong> " . intval($user['user_type']) . "</p>";
echo "<p><strong>Hex Representation:</strong> 0x" . dechex($user['user_type']) . "</p>";
echo "</div>";

// Career history check
echo "<div class='debug-section'>";
echo "<h2>Career History Check</h2>";

// Direct database query
$rawCareerHistory = fetchAll("SELECT * FROM career_history WHERE user_id = ?", 'i', [$userId]);
echo "<p><strong>Raw Career History Entries in Database:</strong> " . count($rawCareerHistory) . "</p>";

if (!empty($rawCareerHistory)) {
    echo "<p><strong>First Raw Entry:</strong></p>";
    echo "<pre>";
    print_r($rawCareerHistory[0]);
    echo "</pre>";
}

// Check with function
try {
    $enhancedCareerHistory = getEnhancedUserCareerHistory($userId);
    echo "<p><strong>Enhanced Career History Function Result Entries:</strong> " . count($enhancedCareerHistory) . "</p>";
    
    if ($enhancedCareerHistory === false) {
        echo "<p class='error'><strong>Error:</strong> Function returned false</p>";
    } elseif (empty($enhancedCareerHistory) && !empty($rawCareerHistory)) {
        echo "<p class='error'><strong>Error:</strong> Function returned empty array but raw data exists</p>";
    } elseif (!empty($enhancedCareerHistory)) {
        echo "<p><strong>First Enhanced Entry:</strong></p>";
        echo "<pre>";
        print_r($enhancedCareerHistory[0]);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>Exception:</strong> " . $e->getMessage() . "</p>";
}
echo "</div>";

// Function verification
echo "<div class='debug-section'>";
echo "<h2>Function Verification</h2>";
echo "<p><strong>getEnhancedUserCareerHistory exists:</strong> " . (function_exists('getEnhancedUserCareerHistory') ? 'Yes' : 'No') . "</p>";

if (function_exists('getEnhancedUserCareerHistory')) {
    $reflection = new ReflectionFunction('getEnhancedUserCareerHistory');
    $filename = $reflection->getFileName();
    $startLine = $reflection->getStartLine();
    echo "<p><strong>Function defined in:</strong> {$filename} (line {$startLine})</p>";
    
    // Check for function declaration issues
    $functionFiles = ['../../includes/functions.php', '../../includes/functions_culture.php'];
    $declarations = [];
    
    foreach ($functionFiles as $file) {
        $contents = file_exists($file) ? file_get_contents($file) : '';
        if ($contents && preg_match('/function\s+getEnhancedUserCareerHistory\s*\(/i', $contents)) {
            $declarations[] = $file;
        }
    }
    
    echo "<p><strong>Function declared in:</strong> " . implode(", ", $declarations) . "</p>";
    if (count($declarations) > 1) {
        echo "<p class='error'><strong>Error:</strong> Function declared in multiple files!</p>";
    }
}
echo "</div>";

// Actions
echo "<div class='debug-section'>";
echo "<h2>Actions</h2>";
echo "<a href='" . SITE_URL . "/pages/admin/edit_user.php?id={$userId}' class='btn'>Back to Edit User</a> ";
echo "<a href='" . SITE_URL . "/pages/admin/users_combined.php' class='btn btn-secondary'>Back to Users</a>";
echo "</div>";
?> 