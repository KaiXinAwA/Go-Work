<?php
$pageTitle = 'Worker Dashboard';
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

// Get counts for dashboard
$totalCompanies = countUsersByType(USER_TYPE_COMPANY);

// Get pending licenses
$pendingLicenses = getPendingLicenses();

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Worker Dashboard</h2>
            <p class="text-muted">Welcome to the GoWork worker dashboard. Review and approve company licenses.</p>
        </div>
    </div>
    
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Companies</h6>
                            <h2 class="mt-2 mb-0"><?php echo $totalCompanies; ?></h2>
                        </div>
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="<?php echo SITE_URL; ?>/pages/worker/companies.php" class="text-white">View all companies <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pending Licenses</h6>
                            <h2 class="mt-2 mb-0"><?php echo count($pendingLicenses); ?></h2>
                        </div>
                        <i class="fas fa-file-contract fa-2x"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php" class="text-dark">Review licenses <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 mb-4">
            <!-- Pending License Approvals -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pending License Approvals</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pendingLicenses)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingLicenses as $license): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($license['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($license['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($license['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php?id=<?php echo $license['company_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No pending license approvals.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="<?php echo SITE_URL; ?>/pages/worker/companies.php" class="btn btn-success w-100 py-3">
                                <i class="fas fa-building mb-2 d-block fa-2x"></i>
                                View Companies
                            </a>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php" class="btn btn-info w-100 py-3">
                                <i class="fas fa-file-contract mb-2 d-block fa-2x"></i>
                                Review Licenses
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 