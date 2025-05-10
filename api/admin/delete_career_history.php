<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Get and validate request parameters
$careerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($careerId <= 0 || $userId <= 0) {
    $_SESSION['error'] = 'Invalid career history or user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Verify the career history record exists and belongs to the user
$careerEntry = fetchRow("SELECT * FROM career_history WHERE career_id = ? AND user_id = ?", 'ii', [$careerId, $userId]);
if (!$careerEntry) {
    $_SESSION['error'] = 'Career history record not found or does not belong to this user';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Delete the career history record
$query = "DELETE FROM career_history WHERE career_id = ? AND user_id = ?";
$result = executeQuery($query, 'ii', [$careerId, $userId]);

if ($result) {
    $_SESSION['success'] = 'Career history deleted successfully';
} else {
    $_SESSION['error'] = 'Failed to delete career history record';
}

// Redirect back to the edit user page
redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId); 