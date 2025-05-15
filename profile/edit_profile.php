<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to edit your profile";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user profile data if it exists
$profile = null;
$query = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}

// Get career history if it exists
$career_history = [];
$query = "SELECT * FROM career_history WHERE user_id = ? ORDER BY start_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $career_history[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = sanitize_input($_POST['name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $bio = sanitize_input($_POST['bio'] ?? '');
    $skills = sanitize_input($_POST['skills'] ?? '');
    $institution = sanitize_input($_POST['institution'] ?? '');
    $completion_status = sanitize_input($_POST['completion_status'] ?? '');
    $education_highlights = sanitize_input($_POST['education_highlights'] ?? '');
    
    if (empty($name)) {
        $_SESSION['error'] = "Name is required";
    } else {
        // Check if profile exists and update or insert accordingly
        if ($profile) {
            // Update existing profile
            $query = "UPDATE user_profiles SET 
                      name = ?, phone = ?, location = ?, bio = ?, 
                      skills = ?, institution = ?, completion_status = ?, education_highlights = ? 
                      WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssssi", $name, $phone, $location, $bio, 
                             $skills, $institution, $completion_status, $education_highlights, $user_id);
            
            if ($stmt->execute()) {
                // Handle career history updates
                updateCareerHistory($conn, $user_id, $_POST);
                
                $_SESSION['success'] = "Profile updated successfully";
                header("Location: view_profile.php");
                exit();
            } else {
                $_SESSION['error'] = "Error updating profile: " . $conn->error;
            }
        } else {
            // Insert new profile
            $query = "INSERT INTO user_profiles (user_id, name, phone, location, bio, 
                     skills, institution, completion_status, education_highlights) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssssss", $user_id, $name, $phone, $location, $bio, 
                             $skills, $institution, $completion_status, $education_highlights);
            
            if ($stmt->execute()) {
                // Handle career history updates
                updateCareerHistory($conn, $user_id, $_POST);
                
                $_SESSION['success'] = "Profile created successfully";
                header("Location: view_profile.php");
                exit();
            } else {
                $_SESSION['error'] = "Error creating profile: " . $conn->error;
            }
        }
    }
}

// Function to handle career history updates
function updateCareerHistory($conn, $user_id, $post_data) {
    // Remove existing career history entries
    $delete_query = "DELETE FROM career_history WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    
    // Add new career history entries
    $job_count = isset($post_data['job_count']) ? (int)$post_data['job_count'] : 0;
    
    for ($i = 0; $i < $job_count; $i++) {
        if (!empty($post_data["job_title_$i"]) && !empty($post_data["company_name_$i"]) && 
            !empty($post_data["start_date_$i"])) {
            
            $job_title = sanitize_input($post_data["job_title_$i"]);
            $company_name = sanitize_input($post_data["company_name_$i"]);
            $start_date = sanitize_input($post_data["start_date_$i"]);
            $end_date = !empty($post_data["end_date_$i"]) ? sanitize_input($post_data["end_date_$i"]) : null;
            $description = sanitize_input($post_data["description_$i"] ?? '');
            
            $insert_query = "INSERT INTO career_history (user_id, job_title, company_name, start_date, end_date, description) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("isssss", $user_id, $job_title, $company_name, $start_date, $end_date, $description);
            $insert_stmt->execute();
        }
    }
}

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get user information
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

include_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1>Edit Profile</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="mb-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Personal Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $profile['name'] ?? $user['username'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $profile['phone'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $profile['location'] ?? ''; ?>" placeholder="City, State">
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Tell us about yourself"><?php echo $profile['bio'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="skills" class="form-label">Skills</label>
                            <input type="text" class="form-control" id="skills" name="skills" value="<?php echo $profile['skills'] ?? ''; ?>" placeholder="Separate skills with commas (e.g. HTML, CSS, JavaScript)">
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Education</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="institution" class="form-label">Institution</label>
                            <input type="text" class="form-control" id="institution" name="institution" value="<?php echo $profile['institution'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="completion_status" class="form-label">Completion Status</label>
                            <select class="form-select" id="completion_status" name="completion_status">
                                <option value="">Select status</option>
                                <option value="In Progress" <?php if(($profile['completion_status'] ?? '') == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Graduated" <?php if(($profile['completion_status'] ?? '') == 'Graduated') echo 'selected'; ?>>Graduated</option>
                                <option value="Incomplete" <?php if(($profile['completion_status'] ?? '') == 'Incomplete') echo 'selected'; ?>>Incomplete</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="education_highlights" class="form-label">Education Highlights</label>
                            <textarea class="form-control" id="education_highlights" name="education_highlights" rows="4" placeholder="Degrees, certifications, achievements"><?php echo $profile['education_highlights'] ?? ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Career History</h3>
                        <button type="button" class="btn btn-primary" id="add-job">Add Job</button>
                    </div>
                    <div class="card-body" id="career-history-container">
                        <input type="hidden" name="job_count" id="job-count" value="<?php echo count($career_history); ?>">
                        
                        <?php if (empty($career_history)): ?>
                            <div class="job-entry card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h5>Job #1</h5>
                                        <button type="button" class="btn btn-sm btn-danger remove-job">Remove</button>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Job Title *</label>
                                        <input type="text" class="form-control" name="job_title_0" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" name="company_name_0" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Start Date *</label>
                                            <input type="date" class="form-control" name="start_date_0" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date_0">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input current-job" type="checkbox" id="current_job_0">
                                                <label class="form-check-label" for="current_job_0">Current Position</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description_0" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach($career_history as $index => $job): ?>
                                <div class="job-entry card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h5>Job #<?php echo $index + 1; ?></h5>
                                            <button type="button" class="btn btn-sm btn-danger remove-job">Remove</button>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Job Title *</label>
                                            <input type="text" class="form-control" name="job_title_<?php echo $index; ?>" value="<?php echo $job['job_title']; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Company Name *</label>
                                            <input type="text" class="form-control" name="company_name_<?php echo $index; ?>" value="<?php echo $job['company_name']; ?>" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Start Date *</label>
                                                <input type="date" class="form-control" name="start_date_<?php echo $index; ?>" value="<?php echo $job['start_date']; ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">End Date</label>
                                                <input type="date" class="form-control" name="end_date_<?php echo $index; ?>" value="<?php echo $job['end_date']; ?>">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input current-job" type="checkbox" id="current_job_<?php echo $index; ?>" <?php echo is_null($job['end_date']) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="current_job_<?php echo $index; ?>">Current Position</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description_<?php echo $index; ?>" rows="3"><?php echo $job['description']; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="view_profile.php" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track the number of job entries
    let jobCount = document.getElementById('job-count').value;
    
    // Add job entry
    document.getElementById('add-job').addEventListener('click', function() {
        const container = document.getElementById('career-history-container');
        const newIndex = parseInt(jobCount);
        
        const jobEntry = document.createElement('div');
        jobEntry.className = 'job-entry card mb-3';
        jobEntry.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h5>Job #${newIndex + 1}</h5>
                    <button type="button" class="btn btn-sm btn-danger remove-job">Remove</button>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Job Title *</label>
                    <input type="text" class="form-control" name="job_title_${newIndex}" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Company Name *</label>
                    <input type="text" class="form-control" name="company_name_${newIndex}" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" class="form-control" name="start_date_${newIndex}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date_${newIndex}">
                        <div class="form-check mt-2">
                            <input class="form-check-input current-job" type="checkbox" id="current_job_${newIndex}">
                            <label class="form-check-label" for="current_job_${newIndex}">Current Position</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description_${newIndex}" rows="3"></textarea>
                </div>
            </div>
        `;
        
        container.appendChild(jobEntry);
        jobCount = newIndex + 1;
        document.getElementById('job-count').value = jobCount;
        
        // Add event listener for the new remove button
        jobEntry.querySelector('.remove-job').addEventListener('click', removeJobHandler);
        
        // Add event listener for the new "Current Position" checkbox
        jobEntry.querySelector('.current-job').addEventListener('change', currentJobHandler);
    });
    
    // Remove job entry handler
    function removeJobHandler() {
        this.closest('.job-entry').remove();
        
        // Update job count and re-index job entries
        jobCount--;
        document.getElementById('job-count').value = jobCount;
        
        // Re-label job entries
        const jobEntries = document.querySelectorAll('.job-entry');
        jobEntries.forEach((entry, index) => {
            entry.querySelector('h5').textContent = `Job #${index + 1}`;
        });
    }
    
    // Add event listener to existing remove buttons
    document.querySelectorAll('.remove-job').forEach(button => {
        button.addEventListener('click', removeJobHandler);
    });
    
    // Current position checkbox handler
    function currentJobHandler() {
        const endDateInput = this.closest('.col-md-6').querySelector('input[type="date"]');
        if (this.checked) {
            endDateInput.value = '';
            endDateInput.disabled = true;
        } else {
            endDateInput.disabled = false;
        }
    }
    
    // Add event listener to existing "Current Position" checkboxes
    document.querySelectorAll('.current-job').forEach(checkbox => {
        checkbox.addEventListener('change', currentJobHandler);
        
        // Initialize state
        if (checkbox.checked) {
            const endDateInput = checkbox.closest('.col-md-6').querySelector('input[type="date"]');
            endDateInput.disabled = true;
        }
    });
});
</script>

<?php include_once '../includes/footer.php'; ?> 