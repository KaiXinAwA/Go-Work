<?php
$pageTitle = 'View Company';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get company ID from URL
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($companyId <= 0) {
    $_SESSION['error'] = 'Invalid company ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Get company details joined with user information
$sql = "SELECT c.*, u.email, u.username, u.profile_picture, u.created_at as user_created_at 
        FROM companies c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.company_id = ?";
$company = fetchRow($sql, 'i', [$companyId]);

if (!$company) {
    $_SESSION['error'] = 'Company not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Get company's job postings
$sql = "SELECT job_id, job_title, location, job_type, posted_date, is_active 
        FROM jobs 
        WHERE company_id = ? 
        ORDER BY posted_date DESC";
$jobs = fetchAll($sql, 'i', [$companyId]);

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/companies.php">Manage Companies</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Company</li>
                </ol>
            </nav>
            
            <!-- Page actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Company Details</h2>
                <div>
                    <a href="<?php echo SITE_URL; ?>/pages/admin/companies.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Companies
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCompanyModal">
                        <i class="fas fa-trash"></i> Delete Company
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Company Info -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($company['profile_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $company['profile_picture']; ?>" alt="Company Logo" class="rounded-circle mb-3" width="150" height="150">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px">
                            <i class="fas fa-building fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($company['company_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($company['email']); ?></p>
                    
                    <div class="d-flex justify-content-center mt-3">
                        <div class="badge bg-primary me-2">
                            <i class="fas fa-calendar-alt"></i> Member since: <?php echo date('M Y', strtotime($company['user_created_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Username:</div>
                            <div class="col-6 text-end"><?php echo htmlspecialchars($company['username']); ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Contact:</div>
                            <div class="col-6 text-end"><?php echo $company['contact_number'] ? htmlspecialchars($company['contact_number']) : 'Not provided'; ?></div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-6 text-muted">Location:</div>
                            <div class="col-6 text-end">
                                <?php 
                                $location = [];
                                if (!empty($company['city'])) $location[] = htmlspecialchars($company['city']);
                                if (!empty($company['state'])) $location[] = htmlspecialchars($company['state']);
                                if (!empty($company['country'])) $location[] = htmlspecialchars($company['country']);
                                echo !empty($location) ? implode(', ', $location) : 'Not provided';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- License Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">License Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="alert <?php 
                            echo $company['license_status'] === 'Approved' ? 'alert-success' : 
                                 ($company['license_status'] === 'Not Approved' ? 'alert-danger' : 'alert-warning'); 
                        ?>">
                            <div class="d-flex align-items-center">
                                <div>
                                    <?php if ($company['license_status'] === 'Approved'): ?>
                                        <i class="fas fa-check-circle fa-2x me-3"></i>
                                    <?php elseif ($company['license_status'] === 'Not Approved'): ?>
                                        <i class="fas fa-times-circle fa-2x me-3"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock fa-2x me-3"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="mb-0">Status: <?php echo $company['license_status']; ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($company['license_path']): ?>
                        <div class="d-grid gap-2">
                            <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $company['license_path']; ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> View License
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <form action="<?php echo SITE_URL; ?>/api/admin/update_license_status.php" method="POST">
                                <input type="hidden" name="company_id" value="<?php echo $company['company_id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="license_status" class="form-label">Update License Status</label>
                                    <select class="form-select" id="license_status" name="license_status" required>
                                        <option value="Pending" <?php echo $company['license_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo $company['license_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Not Approved" <?php echo $company['license_status'] === 'Not Approved' ? 'selected' : ''; ?>>Not Approved</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">Update Status</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            This company has not uploaded a license yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Company Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Company Description</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($company['description'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No description provided.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Address Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Email</h6>
                            <p><?php echo htmlspecialchars($company['email']); ?></p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Phone Number</h6>
                            <p><?php echo $company['contact_number'] ? htmlspecialchars($company['contact_number']) : 'Not provided'; ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Address</h6>
                        <p>
                            <?php 
                            if (!empty($company['address'])) {
                                echo htmlspecialchars($company['address']) . '<br>';
                            }
                            
                            $location = [];
                            if (!empty($company['city'])) $location[] = htmlspecialchars($company['city']);
                            if (!empty($company['state'])) $location[] = htmlspecialchars($company['state']);
                            if (!empty($company['country'])) $location[] = htmlspecialchars($company['country']);
                            
                            echo !empty($location) ? implode(', ', $location) : 'Address not provided';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Job Listings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Job Listings (<?php echo count($jobs); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($jobs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Posted</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['location']); ?></td>
                                            <td><?php echo htmlspecialchars($job['job_type']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                                            <td>
                                                <?php if ($job['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/admin/view_job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            This company hasn't posted any jobs yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Company Modal -->
<div class="modal fade" id="deleteCompanyModal" tabindex="-1" aria-labelledby="deleteCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCompanyModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the company <strong><?php echo htmlspecialchars($company['company_name']); ?></strong>?</p>
                <p class="text-danger">This action cannot be undone and will delete all associated jobs and applications.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo SITE_URL; ?>/api/admin/delete_company.php?id=<?php echo $company['company_id']; ?>" class="btn btn-danger">Delete Company</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?> 