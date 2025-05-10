<?php
$pageTitle = 'Review License';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a worker
if (!isLoggedIn() || !hasUserType(USER_TYPE_GOWORK)) {
    $_SESSION['error'] = 'You must be logged in as a worker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Verify the worker exists in gowork_workers table
$workerId = getWorkerId($_SESSION['user_id']);
if (!$workerId) {
    $_SESSION['error'] = 'Worker account not found. Please contact an administrator.';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get company ID
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no ID is provided, show all pending licenses
if ($companyId === 0) {
    $pendingLicenses = getPendingLicenses();
    
    // Include header
    require_once '../../includes/header.php';
    ?>
    
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/dashboard.php">Worker Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Review Licenses</li>
                    </ol>
                </nav>
                <h2>Review Pending Licenses</h2>
                <p class="text-muted">Approve or reject company license submissions</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($pendingLicenses)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Company ID</th>
                                            <th>Company Name</th>
                                            <th>Email</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingLicenses as $license): ?>
                                            <tr>
                                                <td><?php echo $license['company_id']; ?></td>
                                                <td><?php echo htmlspecialchars($license['company_name']); ?></td>
                                                <td><?php echo htmlspecialchars($license['email']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($license['created_at'])); ?></td>
                                                <td>
                                                    <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php?id=<?php echo $license['company_id']; ?>" class="btn btn-primary btn-sm">Review</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No pending license submissions to review.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Include footer
    require_once '../../includes/footer.php';
    exit;
}

// Get company data
$company = fetchRow(
    "SELECT c.*, u.email, u.username 
     FROM companies c 
     JOIN users u ON c.user_id = u.user_id 
     WHERE c.company_id = ?", 
    'i', 
    [$companyId]
);

// Check if company exists
if (!$company) {
    $_SESSION['error'] = 'Company not found';
    redirectTo(SITE_URL . '/pages/worker/review_license.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';
    
    if ($status !== 'Approved' && $status !== 'Not Approved') {
        $_SESSION['error'] = 'Invalid status';
        redirectTo(SITE_URL . '/pages/worker/review_license.php?id=' . $companyId);
    }
    
    // Update license status
    $result = updateLicenseStatus($companyId, $status);
    
    if ($result) {
        $_SESSION['success'] = 'License status updated to ' . $status;
    } else {
        $_SESSION['error'] = 'Failed to update license status';
    }
    
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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/dashboard.php">Worker Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php">Review Licenses</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Review: <?php echo htmlspecialchars($company['company_name']); ?></li>
                </ol>
            </nav>
            <h2>Review License: <?php echo htmlspecialchars($company['company_name']); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <?php if (isset($company['license_path']) && $company['license_path']): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">License Document</h5>
                    </div>
                    <div class="card-body">
                        <div class="ratio ratio-16x9">
                            <iframe src="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $company['license_path']; ?>" allowfullscreen></iframe>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $company['license_path']; ?>" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>
                        <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $company['license_path']; ?>" download class="btn btn-outline-secondary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i> No license document found for this company.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Company Name</dt>
                        <dd><?php echo htmlspecialchars($company['company_name']); ?></dd>
                        
                        <dt>Email</dt>
                        <dd><?php echo htmlspecialchars($company['email']); ?></dd>
                        
                        <dt>Username</dt>
                        <dd><?php echo htmlspecialchars($company['username']); ?></dd>
                        
                        <dt>Contact Number</dt>
                        <dd><?php echo isset($company['contact_number']) && $company['contact_number'] ? htmlspecialchars($company['contact_number']) : 'Not provided'; ?></dd>
                        
                        <dt>Address</dt>
                        <dd>
                            <?php
                            $address = [];
                            if (isset($company['address']) && $company['address']) $address[] = htmlspecialchars($company['address']);
                            if (isset($company['city']) && $company['city']) $address[] = htmlspecialchars($company['city']);
                            if (isset($company['state']) && $company['state']) $address[] = htmlspecialchars($company['state']);
                            if (isset($company['country']) && $company['country']) $address[] = htmlspecialchars($company['country']);
                            
                            echo !empty($address) ? implode(', ', $address) : 'Not provided';
                            ?>
                        </dd>
                        
                        <dt>Registered On</dt>
                        <dd><?php echo date('F j, Y', strtotime($company['created_at'])); ?></dd>
                        
                        <dt>Current License Status</dt>
                        <dd>
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
                        </dd>
                    </dl>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Review Decision</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/pages/worker/review_license.php?id=<?php echo $companyId; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Update License Status</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="status" id="statusApproved" value="Approved" required 
                                    <?php echo $company['license_status'] === 'Approved' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="statusApproved">
                                    <span class="badge bg-success">Approve</span> - Valid license, allow job posting
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="statusRejected" value="Not Approved" required
                                    <?php echo $company['license_status'] === 'Not Approved' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="statusRejected">
                                    <span class="badge bg-danger">Reject</span> - Invalid or insufficient license
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 