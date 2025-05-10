<?php
$pageTitle = 'Edit User';
require_once '../../includes/init.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user details
$user = fetchRow("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);

if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirectTo(SITE_URL . '/pages/admin/users_combined.php');
}

// Get user profile details
$profile = fetchRow("SELECT * FROM user_profiles WHERE user_id = ?", 'i', [$userId]);

// Check if user is a jobseeker to determine if we should show additional sections
$isJobSeeker = (int)$user['user_type'] === USER_TYPE_JOBSEEKER;

// Get career history if user is a jobseeker
$careerHistory = $isJobSeeker ? getEnhancedUserCareerHistory($userId) : [];

// Set up form data
$formData = [
    'username' => $user['username'],
    'email' => $user['email'],
    'user_type' => $user['user_type'],
    'full_name' => $profile['full_name'] ?? '',
    'phone' => $profile['phone'] ?? '',
    'address' => $profile['address'] ?? '',
    'city' => $profile['city'] ?? '',
    'state' => $profile['state'] ?? '',
    'country' => $profile['country'] ?? '',
    'skills' => $profile['skills'] ?? '',
    'education' => $profile['education'] ?? '',
    'institution' => $profile['institution'] ?? '',
    'completion_status' => $profile['completion_status'] ?? '',
    'education_highlights' => $profile['education_highlights'] ?? ''
];

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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php">Manage Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
            <h2>Edit User</h2>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/admin/update_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($formData['username']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_type" class="form-label">User Type</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="<?php echo USER_TYPE_JOBSEEKER; ?>" <?php echo (int)$formData['user_type'] === USER_TYPE_JOBSEEKER ? 'selected' : ''; ?>>Job Seeker</option>
                                    <option value="<?php echo USER_TYPE_COMPANY; ?>" <?php echo (int)$formData['user_type'] === USER_TYPE_COMPANY ? 'selected' : ''; ?>>Company</option>
                                    <option value="<?php echo USER_TYPE_GOWORK; ?>" <?php echo (int)$formData['user_type'] === USER_TYPE_GOWORK ? 'selected' : ''; ?>>GoWork Staff</option>
                                    <option value="<?php echo USER_TYPE_ADMIN; ?>" <?php echo (int)$formData['user_type'] === USER_TYPE_ADMIN ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <h5 class="mt-4">Profile Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($formData['full_name']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($formData['address']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($formData['city']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($formData['state']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($formData['country']); ?>">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($isJobSeeker): ?>
            <!-- Skills Section - Only available for job seekers -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Skills</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/admin/update_user_skills.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <textarea class="form-control" id="skills" name="skills" rows="4"><?php echo htmlspecialchars($formData['skills']); ?></textarea>
                            <small class="text-muted">Enter skills separated by commas or line breaks.</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Skills</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Education Section - Only available for job seekers -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Education</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/admin/update_user_education.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <div class="mb-3">
                            <label for="education" class="form-label">Education Level/Degree</label>
                            <input type="text" class="form-control" id="education" name="education" value="<?php echo htmlspecialchars($formData['education']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="institution" class="form-label">Institution</label>
                            <input type="text" class="form-control" id="institution" name="institution" value="<?php echo htmlspecialchars($formData['institution']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="completion_status" class="form-label">Completion Status</label>
                            <select class="form-select" id="completion_status" name="completion_status">
                                <option value="">Select Status</option>
                                <option value="Completed" <?php echo $formData['completion_status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="In Progress" <?php echo $formData['completion_status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Not Completed" <?php echo $formData['completion_status'] == 'Not Completed' ? 'selected' : ''; ?>>Not Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="education_highlights" class="form-label">Education Highlights</label>
                            <textarea class="form-control" id="education_highlights" name="education_highlights" rows="3"><?php echo htmlspecialchars($formData['education_highlights']); ?></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Education</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Career History Section - Only for jobseekers -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Career History</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCareerModal">
                        <i class="fas fa-plus"></i> Add Job
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($careerHistory)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No career history found. Use the "Add Job" button to add work experience.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($careerHistory as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                    <td>
                                        <?php 
                                        echo date('M Y', strtotime($job['start_date'])); 
                                        echo ' - ';
                                        echo !empty($job['end_date']) ? date('M Y', strtotime($job['end_date'])) : 'Present';
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-job-btn" 
                                                data-job-id="<?php echo $job['career_id']; ?>"
                                                data-job-title="<?php echo htmlspecialchars($job['job_title']); ?>"
                                                data-company-name="<?php echo htmlspecialchars($job['company_name']); ?>"
                                                data-start-date="<?php echo $job['start_date']; ?>"
                                                data-end-date="<?php echo $job['end_date']; ?>"
                                                data-description="<?php echo htmlspecialchars($job['description']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#editCareerModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?php echo SITE_URL; ?>/api/admin/delete_career_history.php?id=<?php echo $job['career_id']; ?>&user_id=<?php echo $userId; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this job history entry?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- For non-jobseeker users, simply don't display the sections -->
            <?php endif; ?>
            
        </div>
    </div>
</div>

<!-- Add Career History Modal -->
<div class="modal fade" id="addCareerModal" tabindex="-1" aria-labelledby="addCareerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCareerModalLabel">Add Job Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo SITE_URL; ?>/api/admin/add_career_history.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                        <div class="form-text">Leave blank if this is your current job</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Job Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Career History Modal -->
<div class="modal fade" id="editCareerModal" tabindex="-1" aria-labelledby="editCareerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCareerModalLabel">Edit Job Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo SITE_URL; ?>/api/admin/update_career_history.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <input type="hidden" name="career_id" id="edit_career_id">
                    <div class="mb-3">
                        <label for="edit_job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="edit_job_title" name="job_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="edit_company_name" name="company_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        <div class="form-text">Leave blank if this is your current job</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Job Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set up edit job modal
    const editButtons = document.querySelectorAll('.edit-job-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-job-id');
            const jobTitle = this.getAttribute('data-job-title');
            const companyName = this.getAttribute('data-company-name');
            const startDate = this.getAttribute('data-start-date');
            const endDate = this.getAttribute('data-end-date');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit_career_id').value = jobId;
            document.getElementById('edit_job_title').value = jobTitle;
            document.getElementById('edit_company_name').value = companyName;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_end_date').value = endDate || '';
            document.getElementById('edit_description').value = description;
        });
    });
});
</script>

<?php
// Include footer
require_once '../../includes/footer.php';
?> 