<?php
$pageTitle = 'Admin Dashboard';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get counts for dashboard
$totalJobs = countTotalJobs();
$totalJobSeekers = countUsersByType(USER_TYPE_JOBSEEKER);
$totalCompanies = countUsersByType(USER_TYPE_COMPANY);
$totalApplications = countTotalApplications();

// Get recent users
$recentUsers = fetchAll(
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
);

// Get pending licenses
$pendingLicenses = getPendingLicenses();

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">Welcome to the GoWork admin dashboard. Manage users, companies, and platform content.</p>
        </div>
    </div>
    
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Job Seekers</h6>
                            <h2 class="mt-2 mb-0"><?php echo $totalJobSeekers; ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
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
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Jobs</h6>
                            <h2 class="mt-2 mb-0"><?php echo $totalJobs; ?></h2>
                        </div>
                        <i class="fas fa-briefcase fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Applications</h6>
                            <h2 class="mt-2 mb-0"><?php echo $totalApplications; ?></h2>
                        </div>
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <!-- Recent Users -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Users</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentUsers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php
                                                $userTypeText = '';
                                                switch ($user['user_type']) {
                                                    case USER_TYPE_JOBSEEKER:
                                                        $userTypeText = 'Job Seeker';
                                                        break;
                                                    case USER_TYPE_COMPANY:
                                                        $userTypeText = 'Company';
                                                        break;
                                                    case USER_TYPE_GOWORK:
                                                        $userTypeText = 'GoWork';
                                                        break;
                                                    case USER_TYPE_ADMIN:
                                                        $userTypeText = 'Admin';
                                                        break;
                                                }
                                                echo $userTypeText;
                                                ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/admin/edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No recent users.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php?tab=users" class="btn btn-sm btn-outline-primary">View All Users</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
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
                                                <a href="<?php echo SITE_URL; ?>/pages/admin/review_license.php?id=<?php echo $license['company_id']; ?>" class="btn btn-sm btn-outline-primary">
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
                            No pending license approvals.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Admin Tools Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Admin Tools</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-cog fa-3x mb-3 text-info"></i>
                                    <h5>User Type Debugger</h5>
                                    <p class="small text-muted">Diagnose user type related issues</p>
                                    <a href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php" class="btn btn-sm btn-outline-info">Find Users</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-3x mb-3 text-success"></i>
                                    <h5>Career History Tools</h5>
                                    <p class="small text-muted">Fix issues with career history data</p>
                                    <a href="<?php echo SITE_URL; ?>/api/admin/debug_career_history.php?id=2" class="btn btn-sm btn-outline-success">Debug Sample User</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
