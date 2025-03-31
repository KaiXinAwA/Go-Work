<?php
$pageTitle = 'Manage Jobs';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

// Get all jobs for this company
$jobs = [];
if ($company) {
    $jobs = getJobsByCompany($company['company_id']);
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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Jobs</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Manage Jobs</h2>
                    <p class="text-muted">View and manage all your job listings</p>
                </div>
                <?php if ($company && $company['license_status'] === 'Approved'): ?>
                    <div>
                        <a href="<?php echo SITE_URL; ?>/pages/company/post_job.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Post New Job
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <?php if (empty($jobs)): ?>
                <div class="card">
                    <div class="card-body py-5 text-center">
                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                        <h4>No Job Listings Yet</h4>
                        <?php if ($company && $company['license_status'] === 'Approved'): ?>
                            <p>You haven't posted any jobs yet. Click the button below to create your first job listing.</p>
                            <a href="<?php echo SITE_URL; ?>/pages/company/post_job.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> Post New Job
                            </a>
                        <?php else: ?>
                            <p>You need an approved license to post jobs.</p>
                            <a href="<?php echo SITE_URL; ?>/pages/company/profile.php#license" class="btn btn-primary mt-3">
                                Submit License
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Posted Date</th>
                                        <th>Status</th>
                                        <th>Applications</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): 
                                        // Get application count for this job
                                        $applicationCount = fetchRow("SELECT COUNT(*) as count FROM applications WHERE job_id = ?", 'i', [$job['job_id']]);
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="text-decoration-none fw-bold">
                                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                                </a>
                                            </td>
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
                                                <a href="<?php echo SITE_URL; ?>/pages/company/applications.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-users me-1"></i> <?php echo $applicationCount ? $applicationCount['count'] : 0; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $job['job_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $job['job_id']; ?>">
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>">
                                                                <i class="fas fa-eye me-1"></i> View Job
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/company/edit_job.php?id=<?php echo $job['job_id']; ?>">
                                                                <i class="fas fa-edit me-1"></i> Edit Job
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/company/applications.php?job_id=<?php echo $job['job_id']; ?>">
                                                                <i class="fas fa-users me-1"></i> View Applications
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <?php if ($job['is_active']): ?>
                                                            <li>
                                                                <a class="dropdown-item text-warning" href="<?php echo SITE_URL; ?>/api/jobs/toggle_status.php?id=<?php echo $job['job_id']; ?>&status=0">
                                                                    <i class="fas fa-pause-circle me-1"></i> Deactivate
                                                                </a>
                                                            </li>
                                                        <?php else: ?>
                                                            <li>
                                                                <a class="dropdown-item text-success" href="<?php echo SITE_URL; ?>/api/jobs/toggle_status.php?id=<?php echo $job['job_id']; ?>&status=1">
                                                                    <i class="fas fa-play-circle me-1"></i> Activate
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $job['job_id']; ?>">
                                                                <i class="fas fa-trash-alt me-1"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $job['job_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $job['job_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $job['job_id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete the job '<strong><?php echo htmlspecialchars($job['job_title']); ?></strong>'?</p>
                                                                <p>This action cannot be undone. All applications for this job will also be deleted.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="<?php echo SITE_URL; ?>/api/jobs/delete.php?id=<?php echo $job['job_id']; ?>" class="btn btn-danger">Delete Job</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
