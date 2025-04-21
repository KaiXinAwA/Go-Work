<?php
$pageTitle = 'View Company';
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

// Get company ID
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if ID is provided
if ($companyId === 0) {
    $_SESSION['error'] = 'No company ID provided';
    redirectTo(SITE_URL . '/pages/worker/companies.php');
}

// Get company data
$company = fetchRow(
    "SELECT c.*, u.email, u.username, u.created_at as user_created_at, u.profile_picture
     FROM companies c 
     JOIN users u ON c.user_id = u.user_id 
     WHERE c.company_id = ?", 
    'i', 
    [$companyId]
);

// Check if company exists
if (!$company) {
    $_SESSION['error'] = 'Company not found';
    redirectTo(SITE_URL . '/pages/worker/companies.php');
}

// Get jobs by company
$jobs = getJobsByCompany($companyId);

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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/worker/companies.php">Companies</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($company['company_name']); ?></li>
                </ol>
            </nav>
            <h2>Company: <?php echo htmlspecialchars($company['company_name']); ?></h2>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company Profile</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($company['profile_picture'])): ?>
                        <div class="text-center mb-3">
                            <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $company['profile_picture']; ?>" class="img-fluid rounded" style="max-width: 150px; max-height: 150px;" alt="Profile Picture">
                        </div>
                    <?php endif; ?>
                    
                    <dl>
                        <dt>Company Name</dt>
                        <dd><?php echo htmlspecialchars($company['company_name']); ?></dd>
                        
                        <dt>Description</dt>
                        <dd><?php echo isset($company['description']) && $company['description'] ? nl2br(htmlspecialchars($company['description'])) : 'Not provided'; ?></dd>
                        
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
                        <dd><?php echo date('F j, Y', strtotime($company['user_created_at'])); ?></dd>
                        
                        <dt>License Status</dt>
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
                <div class="card-footer">
                    <?php if ($company['license_status'] === 'Pending'): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/worker/review_license.php?id=<?php echo $companyId; ?>" class="btn btn-warning">
                            <i class="fas fa-file-contract"></i> Review License
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/pages/worker/companies.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Companies
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company Jobs</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($jobs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Salary</th>
                                        <th>Posted</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/jobs/view.php?id=<?php echo $job['job_id']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($job['job_type']); ?></td>
                                            <td><?php echo htmlspecialchars($job['location']); ?></td>
                                            <td><?php echo formatSalaryRange($job['salary_min'], $job['salary_max']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                                            <td>
                                                <?php if ($job['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This company has not posted any jobs yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($company['license_path']) && $company['license_path']): ?>
                <div class="card mt-4">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 