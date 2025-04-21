<?php
$pageTitle = 'GoWork Dashboard';
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
}

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>GoWork Staff Dashboard</h2>
            <p class="text-muted">Welcome to the GoWork staff dashboard.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> This is the standard GoWork staff dashboard. If you need access to worker features, please contact an administrator.
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 