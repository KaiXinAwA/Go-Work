<?php
// This is a temporary script to fix worker status for a specific user
// Place this file in your root directory and run it directly

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Username of the user you want to check/fix
$username = 'Night'; // Change this to your username

// Step 1: Find the user
$user = fetchRow("SELECT * FROM users WHERE username = ?", 's', [$username]);

if (!$user) {
    echo "<p>Error: User '{$username}' not found in the database.</p>";
    exit;
}

echo "<p>Found user: {$user['username']} (ID: {$user['user_id']})</p>";
echo "<p>Current user type: {$user['user_type']}</p>";

// Step 2: Check if user is already in gowork_workers table
$worker = fetchRow("SELECT * FROM gowork_workers WHERE user_id = ?", 'i', [$user['user_id']]);

if ($worker) {
    echo "<p>User is already in gowork_workers table (worker_id: {$worker['worker_id']})</p>";
} else {
    echo "<p>User is NOT in gowork_workers table. Adding now...</p>";
    
    // Add user to gowork_workers table
    $result = execute(
        "INSERT INTO gowork_workers (user_id, department, created_at) VALUES (?, 'License Verification', NOW())",
        'i',
        [$user['user_id']]
    );
    
    if ($result) {
        echo "<p>Successfully added user to gowork_workers table!</p>";
    } else {
        echo "<p>Error: Failed to add user to gowork_workers table.</p>";
    }
}

// Step 3: Make sure user has correct user_type
if ($user['user_type'] != USER_TYPE_GOWORK) {
    echo "<p>Updating user type from {$user['user_type']} to " . USER_TYPE_GOWORK . " (GoWork)</p>";
    
    $result = execute(
        "UPDATE users SET user_type = ? WHERE user_id = ?",
        'ii',
        [USER_TYPE_GOWORK, $user['user_id']]
    );
    
    if ($result) {
        echo "<p>Successfully updated user type to GoWork!</p>";
    } else {
        echo "<p>Error: Failed to update user type.</p>";
    }
} else {
    echo "<p>User already has correct user_type: " . USER_TYPE_GOWORK . " (GoWork)</p>";
}

echo "<p>User account is now set up correctly as a GoWork worker.</p>";
echo "<p>You should now be able to log in and access worker features.</p>";
echo "<p><a href='" . SITE_URL . "/pages/login.php'>Click here to log in</a></p>";

// Security - delete this file after use
echo "<p style='color:red;'>IMPORTANT: Delete this file after use for security reasons!</p>";
?> 