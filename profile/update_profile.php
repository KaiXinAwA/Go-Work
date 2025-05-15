<?php
// Start the session
session_start();

// Include configuration and database connection
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in to update your profile';
    $_SESSION['message_type'] = 'error';
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get basic profile information
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $bio = sanitize_input($_POST['bio']);
    $phone = sanitize_input($_POST['phone']);
    $location = sanitize_input($_POST['location']);
    
    // Get education information
    $institution = sanitize_input($_POST['institution']);
    $completion_status = sanitize_input($_POST['completion_status']);
    $education_highlights = sanitize_input($_POST['education_highlights']);
    $skills = sanitize_input($_POST['skills']);
    
    // Prepare and execute the profile update query
    $update_profile_query = "UPDATE user_profiles SET 
                            name = ?, 
                            email = ?, 
                            bio = ?, 
                            phone = ?, 
                            location = ?,
                            institution = ?,
                            completion_status = ?,
                            education_highlights = ?,
                            skills = ?,
                            updated_at = NOW()
                            WHERE user_id = ?";
                            
    $stmt = $conn->prepare($update_profile_query);
    $stmt->bind_param("sssssssssi", $name, $email, $bio, $phone, $location, $institution, $completion_status, $education_highlights, $skills, $user_id);
    
    // Process career history entries
    if (isset($_POST['job_title']) && is_array($_POST['job_title'])) {
        // First delete existing career history for this user
        $delete_career = "DELETE FROM career_history WHERE user_id = ?";
        $delete_stmt = $conn->prepare($delete_career);
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        
        // Insert new career history entries
        $job_titles = $_POST['job_title'];
        $company_names = $_POST['company_name'];
        $start_dates = $_POST['start_date'];
        $end_dates = $_POST['end_date'];
        $descriptions = $_POST['job_description'];
        
        // Prepare the insert statement
        $insert_career = "INSERT INTO career_history (user_id, job_title, company_name, start_date, end_date, description, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_career);
        
        // Loop through each career entry and insert
        for ($i = 0; $i < count($job_titles); $i++) {
            if (!empty($job_titles[$i]) && !empty($company_names[$i])) {
                $job_title = sanitize_input($job_titles[$i]);
                $company_name = sanitize_input($company_names[$i]);
                $start_date = sanitize_input($start_dates[$i]);
                $end_date = empty($end_dates[$i]) ? NULL : sanitize_input($end_dates[$i]);
                $description = sanitize_input($descriptions[$i]);
                
                $insert_stmt->bind_param("isssss", $user_id, $job_title, $company_name, $start_date, $end_date, $description);
                $insert_stmt->execute();
            }
        }
    }
    
    // Execute the profile update
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Profile updated successfully!';
        $_SESSION['message_type'] = 'success';
        header('Location: view_profile.php');
        exit();
    } else {
        $_SESSION['message'] = 'Error updating profile: ' . $conn->error;
        $_SESSION['message_type'] = 'error';
    }
}

// Get the user profile data
$query = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create a new profile if it doesn't exist
    $create_profile = "INSERT INTO user_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())";
    $create_stmt = $conn->prepare($create_profile);
    $create_stmt->bind_param("i", $user_id);
    $create_stmt->execute();
    
    // Fetch the newly created profile
    $stmt->execute();
    $result = $stmt->get_result();
}

$profile = $result->fetch_assoc();

// Get career history
$career_query = "SELECT * FROM career_history WHERE user_id = ? ORDER BY start_date DESC";
$career_stmt = $conn->prepare($career_query);
$career_stmt->bind_param("i", $user_id);
$career_stmt->execute();
$career_result = $career_stmt->get_result();
$career_history = [];

while ($row = $career_result->fetch_assoc()) {
    $career_history[] = $row;
}

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4">Update Profile</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>" role="alert">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Education</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="institution" class="form-label">Institution</label>
                    <input type="text" class="form-control" id="institution" name="institution" value="<?php echo htmlspecialchars($profile['institution'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="completion_status" class="form-label">Completion Status</label>
                    <select class="form-select" id="completion_status" name="completion_status">
                        <option value="">Select status</option>
                        <option value="In Progress" <?php echo ($profile['completion_status'] ?? '') === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo ($profile['completion_status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Discontinued" <?php echo ($profile['completion_status'] ?? '') === 'Discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="education_highlights" class="form-label">Education Highlights</label>
                    <textarea class="form-control" id="education_highlights" name="education_highlights" rows="3"><?php echo htmlspecialchars($profile['education_highlights'] ?? ''); ?></textarea>
                    <div class="form-text">Include degrees, majors, notable achievements, etc.</div>
                </div>
                <div class="mb-3">
                    <label for="skills" class="form-label">Skills</label>
                    <textarea class="form-control" id="skills" name="skills" rows="3"><?php echo htmlspecialchars($profile['skills'] ?? ''); ?></textarea>
                    <div class="form-text">List your skills separated by commas.</div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Work Experience</h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-experience">Add Experience</button>
            </div>
            <div class="card-body">
                <div id="career-container">
                    <?php if (empty($career_history)): ?>
                        <!-- Empty template for new user -->
                        <div class="career-entry border p-3 mb-3">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" class="form-control" name="job_title[]">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" class="form-control" name="company_name[]">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date[]">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date[]">
                                    <div class="form-text">Leave blank if current position</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="job_description[]" rows="3"></textarea>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger remove-entry">Remove</button>
                        </div>
                    <?php else: ?>
                        <!-- Display existing career entries -->
                        <?php foreach ($career_history as $career): ?>
                            <div class="career-entry border p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Job Title</label>
                                        <input type="text" class="form-control" name="job_title[]" value="<?php echo htmlspecialchars($career['job_title']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" class="form-control" name="company_name[]" value="<?php echo htmlspecialchars($career['company_name']); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date[]" value="<?php echo htmlspecialchars($career['start_date']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date[]" value="<?php echo htmlspecialchars($career['end_date'] ?? ''); ?>">
                                        <div class="form-text">Leave blank if current position</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="job_description[]" rows="3"><?php echo htmlspecialchars($career['description']); ?></textarea>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger remove-entry">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
            <a href="view_profile.php" class="btn btn-secondary me-md-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new career entry
    document.getElementById('add-experience').addEventListener('click', function() {
        const container = document.getElementById('career-container');
        const template = document.createElement('div');
        template.className = 'career-entry border p-3 mb-3';
        
        template.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job Title</label>
                    <input type="text" class="form-control" name="job_title[]">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" class="form-control" name="company_name[]">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date[]">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date[]">
                    <div class="form-text">Leave blank if current position</div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="job_description[]" rows="3"></textarea>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-entry">Remove</button>
        `;
        
        container.appendChild(template);
        
        // Add event listener to the new remove button
        template.querySelector('.remove-entry').addEventListener('click', function() {
            container.removeChild(template);
        });
    });
    
    // Remove career entry
    document.querySelectorAll('.remove-entry').forEach(button => {
        button.addEventListener('click', function() {
            const entry = this.closest('.career-entry');
            entry.parentNode.removeChild(entry);
        });
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?> 