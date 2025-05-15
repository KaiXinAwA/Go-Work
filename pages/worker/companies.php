<?php
$pageTitle = 'Companies';
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

// Get all companies
$companies = fetchAll(
    "SELECT c.*, u.email, u.username,
    (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.company_id) as job_count
    FROM companies c 
    JOIN users u ON c.user_id = u.user_id 
    ORDER BY c.company_name ASC"
);

// Include header
require_once '../../includes/header.php';
?>

<style>
.btn-review-license {
    background-color: #fff;
    color:rgb(0, 0, 0);
    border: 1px solidrgb(0, 0, 0);
    transition: all 0.3s ease;
}

.btn-review-license:hover {
    background-color: #ffc107;
    color: #000;
    border-color: #ffc107;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
</style>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/dashboard.php">Worker Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Companies</li>
                </ol>
            </nav>
            <h2>Companies</h2>
            <p class="text-muted">View and manage registered companies</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($companies)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Jobs Posted</th>
                                        <th>License Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companies as $company): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($company['email']); ?></td>
                                            <td><?php echo $company['job_count']; ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($company['license_status']) {
                                                    case 'Approved':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'Pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'Not Approved':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $company['license_status']; ?></span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/worker/view_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <?php if ($company['license_status'] === 'Pending' && $company['license_path']): ?>
                                                    <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php?id=<?php echo $company['company_id']; ?>" class="btn btn-sm btn-review-license">Review License</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No companies found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 