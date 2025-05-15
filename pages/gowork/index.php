<?php
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a GoWork staff
if (!isLoggedIn() || !hasUserType(USER_TYPE_GOWORK)) {
    $_SESSION['error'] = 'You must be logged in as a GoWork staff to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if user is a worker - if yes, redirect to worker dashboard
$workerId = getWorkerId($_SESSION['user_id']);
if ($workerId) {
    redirectTo(SITE_URL . '/pages/worker/dashboard.php');
} else {
    // Otherwise, redirect to regular GoWork dashboard
    redirectTo(SITE_URL . '/pages/gowork/dashboard.php');
}
?> 