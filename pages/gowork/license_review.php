<?php
$pageTitle = 'License Review';
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
    redirectTo(SITE_URL . '/pages/worker/review_license.php');
}

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/gowork/dashboard.php">GoWork Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">License Review</li>
                </ol>
            </nav>
            <h2>License Review</h2>
            <p class="text-muted">Review company license submissions</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You need to be a GoWork Worker to review licenses. Please contact an administrator if you need this access.
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 