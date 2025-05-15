<?php
/**
 * Fix User Types Script
 * 
 * This script updates the user_type field in the users table for GoWork workers and administrators
 * who might have been incorrectly assigned other user types.
 */

// Include database connection functions
require_once '../includes/config.php';
require_once '../includes/database.php';

// Display header
echo "<html><head><title>Fix User Types</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px}
.success{color:green;font-weight:bold} .error{color:red;font-weight:bold}
table{width:100%;border-collapse:collapse;margin:20px 0}
th,td{padding:8px;text-align:left;border:1px solid #ddd}
th{background-color:#f2f2f2}</style>";
echo "</head><body>";
echo "<h1>Fix User Types Tool</h1>";

// Function to get user type name
function getUserTypeName($type) {
    switch ($type) {
        case 1: return 'Job Seeker';
        case 2: return 'Company';
        case 3: return 'GoWork Worker';
        case 4: return 'Administrator';
        default: return 'Unknown';
    }
}

try {
    // 1. Get current users with their types
    echo "<h2>Current User Types</h2>";
    $users = fetchAll("SELECT user_id, username, email, user_type FROM users ORDER BY user_id");
    
    echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Current Type</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>{$user['user_id']}</td><td>{$user['username']}</td><td>{$user['email']}</td>
              <td>" . getUserTypeName($user['user_type']) . " ({$user['user_type']})</td></tr>";
    }
    echo "</table>";
    
    // 2. Check which users are in GoWork workers table
    $goworkWorkers = fetchAll("SELECT gw.worker_id, gw.user_id, u.username, u.email, u.user_type 
                              FROM gowork_workers gw 
                              JOIN users u ON gw.user_id = u.user_id");
    
    // 3. Check which users are in admin table
    $admins = fetchAll("SELECT a.admin_id, a.user_id, u.username, u.email, u.user_type 
                       FROM admin a 
                       JOIN users u ON a.user_id = u.user_id");
    
    // 4. Display mismatched users
    echo "<h2>Users with Incorrect Types</h2>";
    $hasIncorrectTypes = false;
    
    if (!empty($goworkWorkers)) {
        foreach ($goworkWorkers as $worker) {
            if ($worker['user_type'] != 3) {
                if (!$hasIncorrectTypes) {
                    echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Current Type</th><th>Should Be</th></tr>";
                    $hasIncorrectTypes = true;
                }
                echo "<tr><td>{$worker['user_id']}</td><td>{$worker['username']}</td><td>{$worker['email']}</td>
                      <td>" . getUserTypeName($worker['user_type']) . " ({$worker['user_type']})</td>
                      <td>GoWork Worker (3)</td></tr>";
            }
        }
    }
    
    if (!empty($admins)) {
        foreach ($admins as $admin) {
            if ($admin['user_type'] != 4) {
                if (!$hasIncorrectTypes) {
                    echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Current Type</th><th>Should Be</th></tr>";
                    $hasIncorrectTypes = true;
                }
                echo "<tr><td>{$admin['user_id']}</td><td>{$admin['username']}</td><td>{$admin['email']}</td>
                      <td>" . getUserTypeName($admin['user_type']) . " ({$admin['user_type']})</td>
                      <td>Administrator (4)</td></tr>";
            }
        }
    }
    
    if (!$hasIncorrectTypes) {
        echo "<p>No users with incorrect types found.</p>";
    } else {
        echo "</table>";
        
        // 5. Fix the user types
        echo "<h2>Fixing User Types</h2>";
        
        // Update GoWork workers
        $sql = "UPDATE users SET user_type = 3 WHERE user_id IN (SELECT user_id FROM gowork_workers)";
        $stmt = executeQuery($sql);
        $goworkUpdated = $stmt->affected_rows;
        $stmt->close();
        
        // Update administrators
        $sql = "UPDATE users SET user_type = 4 WHERE user_id IN (SELECT user_id FROM admin)";
        $stmt = executeQuery($sql);
        $adminUpdated = $stmt->affected_rows;
        $stmt->close();
        
        echo "<p class='success'>Updated $goworkUpdated GoWork workers and $adminUpdated administrators.</p>";
        
        // 6. Show updated users
        echo "<h2>Updated User Types</h2>";
        $updatedUsers = fetchAll("SELECT user_id, username, email, user_type FROM users ORDER BY user_id");
        
        echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>New Type</th></tr>";
        foreach ($updatedUsers as $user) {
            echo "<tr><td>{$user['user_id']}</td><td>{$user['username']}</td><td>{$user['email']}</td>
                  <td>" . getUserTypeName($user['user_type']) . " ({$user['user_type']})</td></tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='../index.php'>Return to Homepage</a> | <a href='../pages/admin/users_combined.php'>Go to Manage Users</a></p>";
echo "</body></html>"; 