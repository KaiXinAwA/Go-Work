<?php
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a worker
if (!isLoggedIn() || !hasUserType(USER_TYPE_GOWORK)) {
    $_SESSION['error'] = 'You must be logged in as a worker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if worker exists in gowork_workers table
$workerId = getWorkerId($_SESSION['user_id']);
if (!$workerId) {
    $_SESSION['error'] = 'Worker account not found. Please contact an administrator.';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Redirect to worker dashboard
redirectTo(SITE_URL . '/pages/worker/dashboard.php');
?> 