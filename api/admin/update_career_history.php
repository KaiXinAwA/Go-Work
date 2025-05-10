<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to perform this action';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Get and validate form data
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$careerId = isset($_POST['career_id']) ? (int)$_POST['career_id'] : 0;
$jobTitle = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';
$companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
$startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$endDate = isset($_POST['end_date']) && !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate required fields
if ($userId <= 0 || $careerId <= 0 || empty($jobTitle) || empty($companyName) || empty($startDate)) {
    $_SESSION['error'] = 'All required fields must be filled out';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Check if user exists and is a jobseeker
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Ensure user is a jobseeker
if ((int)$user['user_type'] !== USER_TYPE_JOBSEEKER) {
    $_SESSION['error'] = 'Career history can only be updated for job seeker accounts';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Verify the career history entry exists and belongs to the user
$careerEntry = fetchRow("SELECT * FROM career_history WHERE career_id = ? AND user_id = ?", 'ii', [$careerId, $userId]);
if (!$careerEntry) {
    $_SESSION['error'] = 'Career history entry not found or does not belong to this user';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Update the career history entry
$query = "UPDATE career_history SET job_title = ?, company_name = ?, start_date = ?, end_date = ?, description = ? WHERE career_id = ? AND user_id = ?";
$params = [$jobTitle, $companyName, $startDate, $endDate, $description, $careerId, $userId];
$types = 'sssssii';

$result = executeQuery($query, $types, $params);

if ($result) {
    $_SESSION['success'] = 'Career history updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update career history';
}

// Redirect back to the edit user page
redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId); 