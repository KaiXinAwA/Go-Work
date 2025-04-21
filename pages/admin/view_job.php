<?php
$pageTitle = 'View Job';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get job ID from URL
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($jobId <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Get job details joined with company information
$sql = "SELECT j.*, c.company_name, c.company_id, u.email, u.profile_picture
        FROM jobs j 
        JOIN companies c ON j.company_id = c.company_id 
        JOIN users u ON c.user_id = u.user_id
        WHERE j.job_id = ?";
$job = fetchRow($sql, 'i', [$jobId]);

if (!$job) {
    $_SESSION['error'] = 'Job not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Get job applications
$sql = "SELECT a.*, u.email, u.username, u.profile_picture
        FROM applications a 
        JOIN users u ON a.user_id = u.user_id 
        WHERE a.job_id = ? 
        ORDER BY a.application_id DESC";
$applications = fetchAll($sql, 'i', [$jobId]);

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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/view_company.php?id=<?php echo $job['company_id']; ?>">View Company</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Job</li>
                </ol>
            </nav>
            
            <!-- Page actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Job Details</h2>
                <div>
                    <a href="<?php echo SITE_URL; ?>/pages/admin/view_company.php?id=<?php echo $job['company_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Company
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteJobModal">
                        <i class="fas fa-trash"></i> Delete Job
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Job Info -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if ($job['profile_picture']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $job['profile_picture']; ?>" alt="Company Logo" class="rounded-circle me-3" width="60" height="60">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px">
                                <i class="fas fa-building fa-2x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($job['company_name']); ?></h5>
                            <a href="<?php echo SITE_URL; ?>/pages/admin/view_company.php?id=<?php echo $job['company_id']; ?>" class="text-decoration-none">
                                View Company Profile
                            </a>
                        </div>
                    </div>
                    
                    <h4 class="mb-3"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                    
                    <div class="mb-3">
                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($job['job_type']); ?></span>
                        <?php if ($job['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="row">
                                <div class="col-6 text-muted">Location:</div>
                                <div class="col-6 text-end"><?php echo htmlspecialchars($job['location']); ?></div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row">
                                <div class="col-6 text-muted">Salary:</div>
                                <div class="col-6 text-end">
                                    <?php 
                                    if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                        echo '$' . number_format($job['salary_min']) . ' - $' . number_format($job['salary_max']);
                                    } elseif (!empty($job['salary_min'])) {
                                        echo 'From $' . number_format($job['salary_min']);
                                    } elseif (!empty($job['salary_max'])) {
                                        echo 'Up to $' . number_format($job['salary_max']);
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row">
                                <div class="col-6 text-muted">Posted:</div>
                                <div class="col-6 text-end"><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row">
                                <div class="col-6 text-muted">Applications:</div>
                                <div class="col-6 text-end"><?php echo count($applications); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Admin Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Job Status</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/admin/update_job_status.php" method="POST">
                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Job Status</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value="1" <?php echo $job['is_active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$job['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Job Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Job Description</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($job['job_description'])): ?>
                        <div class="job-description">
                            <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No description provided.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Requirements -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Requirements</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($job['requirements'])): ?>
                        <div class="requirements">
                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No requirements specified.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Applications -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Applications (<?php echo count($applications); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($applications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Email</th>
                                        <th>Application ID</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $application): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($application['profile_picture']): ?>
                                                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $application['profile_picture']; ?>" alt="Applicant" class="rounded-circle me-2" width="40" height="40">
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px">
                                                            <i class="fas fa-user fa-lg text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($application['username']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($application['email']); ?></td>
                                            <td>Application #<?php echo $application['application_id']; ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary'; // Default background color
                                                switch ($application['status']) {
                                                    case 'Pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'Reviewed':
                                                        $statusClass = 'bg-info';
                                                        break;
                                                    case 'Shortlisted':
                                                        $statusClass = 'bg-primary';
                                                        break;
                                                    case 'Rejected':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    case 'Hired':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    // If status is empty or NULL, set a default
                                                    default:
                                                        if (empty($application['status'])) {
                                                            $application['status'] = 'Pending';
                                                            $statusClass = 'bg-warning text-dark';
                                                        }
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $application['status']; ?></span>
                                            </td>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/admin/view_application.php?id=<?php echo $application['application_id']; ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($application['resume_path']): ?>
                                                <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $application['resume_path']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No applications have been submitted for this job yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Job Modal -->
<div class="modal fade" id="deleteJobModal" tabindex="-1" aria-labelledby="deleteJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteJobModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the job posting <strong><?php echo htmlspecialchars($job['job_title']); ?></strong>?</p>
                <p class="text-danger">This action cannot be undone and will delete all associated applications.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo SITE_URL; ?>/api/admin/delete_job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-danger">Delete Job</a>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript to make sure the modal works -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if modal is working properly
    const deleteButton = document.querySelector('[data-bs-target="#deleteJobModal"]');
    const deleteModal = document.getElementById('deleteJobModal');
    
    if (deleteButton && deleteModal) {
        // Add click event listener to the delete button for testing
        deleteButton.addEventListener('click', function() {
            console.log('Delete button clicked');
        });
    }
});
</script>

<?php
// Include footer
require_once '../../includes/footer.php';
?> 