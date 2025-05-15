<?php
$pageTitle = 'My Applications';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();

// Get all applications for this user
$applications = fetchAll(
    "SELECT a.*, j.job_title, c.company_name 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.job_id
    JOIN companies c ON j.company_id = c.company_id
    WHERE a.user_id = ? 
    ORDER BY a.applied_date DESC", 
    'i', 
    [$user['user_id']]
);

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/user/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Applications</li>
                </ol>
            </nav>
            <h2>My Applications</h2>
            <p class="text-muted">Track all your job applications</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($applications)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You haven't applied to any jobs yet.
                            <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="alert-link">Browse jobs</a> and start applying!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $application): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $application['job_id']; ?>" class="text-decoration-none fw-bold">
                                                    <?php echo htmlspecialchars($application['job_title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($application['company_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($application['applied_date'])); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($application['status']) {
                                                    case 'Pending':
                                                        $statusClass = 'badge bg-warning text-dark';
                                                        break;
                                                    case 'Viewed':
                                                        $statusClass = 'badge bg-info text-dark';
                                                        break;
                                                    case 'Shortlisted':
                                                        $statusClass = 'badge bg-success';
                                                        break;
                                                    case 'Rejected':
                                                        $statusClass = 'badge bg-danger';
                                                        break;
                                                    case 'Withdrawn':
                                                        $statusClass = 'badge bg-secondary';
                                                        break;
                                                    default:
                                                        $statusClass = 'badge bg-secondary';
                                                }
                                                ?>
                                                <span class="<?php echo $statusClass; ?>">
                                                    <?php echo $application['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $application['job_id']; ?>" class="btn btn-sm btn-outline-primary" title="View Job">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($application['status'] !== 'Withdrawn'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Withdraw Application" data-bs-toggle="modal" data-bs-target="#withdrawModal<?php echo $application['application_id']; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $application['job_id']; ?>&reapply=1" class="btn btn-sm btn-outline-success" title="Reapply">
                                                        <i class="fas fa-redo-alt"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Withdraw Modal -->
                                                <div class="modal fade" id="withdrawModal<?php echo $application['application_id']; ?>" tabindex="-1" aria-labelledby="withdrawModalLabel<?php echo $application['application_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="withdrawModalLabel<?php echo $application['application_id']; ?>">Withdraw Application</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to withdraw your application for <strong><?php echo htmlspecialchars($application['job_title']); ?></strong> at <strong><?php echo htmlspecialchars($application['company_name']); ?></strong>?</p>
                                                                <p>This action cannot be undone.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="<?php echo SITE_URL; ?>/api/jobs/withdraw_application.php" method="POST">
                                                                    <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Withdraw Application</button>
                                                                </form>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Application Status Guide</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning text-dark me-2">Pending</span>
                                <span>Your application has been submitted but not yet reviewed by the employer.</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info text-dark me-2">Viewed</span>
                                <span>The employer has viewed your application.</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">Shortlisted</span>
                                <span>You've been shortlisted for the next steps in the hiring process.</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2">Rejected</span>
                                <span>Your application was not selected for this position.</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2">Withdrawn</span>
                                <span>You have withdrawn your application from consideration.</span>
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

<script>
// Add filter and search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add filter controls above the table
    const tableResponsive = document.querySelector('.table-responsive');
    if (tableResponsive) {
        const filterControls = document.createElement('div');
        filterControls.className = 'row mb-3';
        filterControls.innerHTML = `
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search jobs..." id="searchApplications">
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end">
                    <select class="form-select w-auto" id="filterStatus">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Viewed">Viewed</option>
                        <option value="Shortlisted">Shortlisted</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Withdrawn">Withdrawn</option>
                    </select>
                </div>
            </div>
        `;
        
        tableResponsive.parentNode.insertBefore(filterControls, tableResponsive);
        
        // Add filtering functionality
        const searchInput = document.getElementById('searchApplications');
        const statusFilter = document.getElementById('filterStatus');
        const applicationRows = document.querySelectorAll('table tbody tr');
        
        function filterApplications() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            
            applicationRows.forEach(row => {
                // Get job title and company for search
                const jobTitle = row.querySelector('td:first-child').textContent.toLowerCase();
                const company = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                // Get status value
                const statusElement = row.querySelector('td .badge');
                const statusText = statusElement ? statusElement.textContent.trim() : '';
                
                // Check if row matches both filters
                const matchesSearch = searchTerm === '' || 
                                     jobTitle.includes(searchTerm) || 
                                     company.includes(searchTerm);
                const matchesStatus = statusValue === '' || statusText === statusValue;
                
                // Show/hide row based on filters
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }
        
        // Add event listeners
        if (searchInput) {
            searchInput.addEventListener('input', filterApplications);
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', filterApplications);
        }
    }
});
</script>
