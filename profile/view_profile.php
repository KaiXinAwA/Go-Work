<?php
// Start the session
session_start();

// Include configuration and database connection
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to view profiles";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if viewing another user's profile
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profile_user_id = $_GET['id'];
    $is_own_profile = ($profile_user_id == $user_id);
} else {
    $profile_user_id = $user_id;
    $is_own_profile = true;
}

// Get the user profile data
$query = "SELECT up.*, u.username, u.email as user_email, ut.type_name 
          FROM user_profiles up 
          JOIN users u ON up.user_id = u.id 
          JOIN user_types ut ON u.user_type = ut.id 
          WHERE up.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Profile doesn't exist
    $_SESSION['message'] = 'Profile not found';
    $_SESSION['message_type'] = 'error';
    header('Location: ../index.php');
    exit();
}

$profile = $result->fetch_assoc();

// Get career history
$career_query = "SELECT * FROM career_history WHERE user_id = ? ORDER BY start_date DESC";
$career_stmt = $conn->prepare($career_query);
$career_stmt->bind_param("i", $profile_user_id);
$career_stmt->execute();
$career_result = $career_stmt->get_result();
$career_history = [];

while ($row = $career_result->fetch_assoc()) {
    $career_history[] = $row;
}

// Format date helper function
function formatDate($date) {
    if (empty($date)) return '';
    return date('F Y', strtotime($date));
}

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="mb-0">My Profile</h1>
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
                
                <div class="card-body">
                    <?php if ($profile): ?>
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center mb-3" style="width: 150px; height: 150px; overflow: hidden;">
                                    <i class="bi bi-person-fill" style="font-size: 5rem;"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($profile['name']); ?></h3>
                                <p class="text-muted">
                                    Account Type: <?php echo ucfirst($profile['type_name']); ?>
                                </p>
                                <?php if (!empty($profile['location'])): ?>
                                    <p><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($profile['location']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($profile['phone'])): ?>
                                    <p><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($profile['phone']); ?></p>
                                <?php endif; ?>
                                <p><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($profile['user_email']); ?></p>
                            </div>
                            
                            <div class="col-md-8">
                                <?php if (!empty($profile['bio'])): ?>
                                    <div class="mb-4">
                                        <h3>About Me</h3>
                                        <p><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($profile['skills'])): ?>
                                    <div class="mb-4">
                                        <h3>Skills</h3>
                                        <div>
                                            <?php 
                                                $skills = explode(',', $profile['skills']);
                                                foreach ($skills as $skill) {
                                                    $skill = trim($skill);
                                                    if (!empty($skill)) {
                                                        echo '<span class="badge bg-primary me-2 mb-2 p-2">' . htmlspecialchars($skill) . '</span>';
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($profile['institution']) || !empty($profile['education_highlights'])): ?>
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h3>Education</h3>
                                    <?php if (!empty($profile['institution'])): ?>
                                        <h5><?php echo htmlspecialchars($profile['institution']); ?>
                                            <?php if (!empty($profile['completion_status'])): ?>
                                                <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($profile['completion_status']); ?></span>
                                            <?php endif; ?>
                                        </h5>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($profile['education_highlights'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($profile['education_highlights'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($career_history)): ?>
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h3>Work Experience</h3>
                                    <div class="timeline">
                                        <?php foreach ($career_history as $job): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h4><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                                    <h6><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                                    <p class="text-muted">
                                                        <?php echo formatDate($job['start_date']); ?> - 
                                                        <?php echo empty($job['end_date']) ? 'Present' : formatDate($job['end_date']); ?>
                                                    </p>
                                                    <?php if (!empty($job['description'])): ?>
                                                        <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h3>Your profile is not set up yet</h3>
                            <p class="mb-4">Complete your profile to showcase your skills and experience to potential employers.</p>
                            <a href="edit_profile.php" class="btn btn-primary btn-lg">Create Profile</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline .card {
    border-left: 4px solid #0d6efd;
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?> 