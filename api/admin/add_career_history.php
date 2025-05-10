<?php
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    sendJsonResponse(['success' => false, 'message' => 'Unauthorized access'], 403);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
    exit;
}

// Get and validate form data
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$jobTitle = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';
$companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
$startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$endDate = isset($_POST['end_date']) && !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate required fields
if ($userId <= 0 || empty($jobTitle) || empty($companyName) || empty($startDate)) {
    $_SESSION['error'] = 'All required fields must be filled out.';
    redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId);
    exit;
}

// Validate user ID
if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Verify that the user exists and is a jobseeker
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$user || $user['user_type'] != USER_TYPE_JOBSEEKER) {
    $_SESSION['error'] = 'Invalid user or user type.';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
    exit;
}

// Insert the career history record
$query = "INSERT INTO career_history (user_id, job_title, company_name, start_date, end_date, description) 
          VALUES (?, ?, ?, ?, ?, ?)";
$params = [$userId, $jobTitle, $companyName, $startDate, $endDate, $description];
$types = 'isssss';

$result = executeQuery($query, $types, $params);

if ($result) {
    $_SESSION['success'] = 'Career history added successfully.';
} else {
    $_SESSION['error'] = 'Failed to add career history.';
}

// Redirect back to the edit user page
redirectTo(SITE_URL . '/pages/admin/edit_user.php?id=' . $userId); 