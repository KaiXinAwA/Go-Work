<?php
$pageTitle = 'Company Profile';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';

// Get company ID from URL
$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$company_id) {
    // Redirect to jobs page if no company ID provided
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Get company data
$company = fetchRow(
    "SELECT c.*, u.profile_picture FROM companies c 
    JOIN users u ON c.user_id = u.user_id 
    WHERE c.company_id = ?",
    'i',
    [$company_id]
);

if (!$company) {
    // Company not found, redirect to jobs page
    redirectTo(SITE_URL . '/pages/jobs.php');
}

// Get company culture information
$companyCulture = getCompanyCulture($company_id);

// Get company's active jobs
$jobs = fetchAll(
    "SELECT * FROM jobs WHERE company_id = ? AND is_active = 1 ORDER BY posted_date DESC",
    'i',
    [$company_id]
);

// Include header
require_once $root_path . 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/jobs.php">Jobs</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($company['company_name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- Company Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if ($company['profile_picture']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $company['profile_picture']; ?>" alt="<?php echo htmlspecialchars($company['company_name']); ?>" class="img-fluid rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 100px; height: 100px;">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <h1 class="mb-1"><?php echo htmlspecialchars($company['company_name']); ?></h1>
                            <div>
                                <?php if ($company['city'] || $company['state'] || $company['country']): ?>
                                    <div class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php
                                            $location = [];
                                            if ($company['city']) $location[] = htmlspecialchars($company['city']);
                                            if ($company['state']) $location[] = htmlspecialchars($company['state']);
                                            if ($company['country']) $location[] = htmlspecialchars($company['country']);
                                            echo implode(', ', $location);
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <h5>About the Company</h5>
                    <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                    
                    <!-- Company Culture Section -->
                    <?php if (!empty($companyCulture) && !empty($companyCulture['values'])): ?>
                        <div class="mt-4">
                            <h5>Company Culture</h5>
                            
                            <?php if (!empty($companyCulture['description'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($companyCulture['description'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="row mt-3">
                                <?php 
                                // Define culture attribute display names
                                $attributeNames = [
                                    'work_environment' => 'Work Environment',
                                    'overtime' => 'Overtime Expectations',
                                    'management' => 'Management Style',
                                    'work_life_balance' => 'Work-Life Balance',
                                    'dress_code' => 'Dress Code',
                                    'communication' => 'Communication Style',
                                    'decision_making' => 'Decision Making',
                                    'innovation' => 'Innovation',
                                    'social_events' => 'Social Events',
                                    'feedback' => 'Feedback Style'
                                ];
                                
                                // Define value display names
                                $valueNames = [
                                    'collaborative' => 'Collaborative',
                                    'focused' => 'Focused',
                                    'flexible' => 'Flexible',
                                    'energetic' => 'Energetic',
                                    'dedicated' => 'Dedicated',
                                    'structured' => 'Structured',
                                    'passionate' => 'Passionate',
                                    'autonomous' => 'Autonomous',
                                    'results-focused' => 'Results-focused',
                                    'balanced' => 'Balanced',
                                    'traditional' => 'Traditional',
                                    'casual' => 'Casual',
                                    'relaxed' => 'Relaxed',
                                    'direct' => 'Direct',
                                    'formal' => 'Formal',
                                    'tech-savvy' => 'Tech-savvy',
                                    'adaptable' => 'Adaptable',
                                    'agile' => 'Agile',
                                    'analytical' => 'Analytical',
                                    'innovative' => 'Innovative',
                                    'incremental' => 'Incremental',
                                    'professional' => 'Professional',
                                    'social' => 'Social',
                                    'independent' => 'Independent',
                                    'considerate' => 'Considerate',
                                    'reflective' => 'Reflective'
                                ];
                                
                                foreach ($companyCulture['values'] as $attribute => $value):
                                    if (isset($attributeNames[$attribute]) && isset($valueNames[$value])):
                                ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 border-light">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-dark me-2"><?php echo htmlspecialchars($attributeNames[$attribute]); ?></span>
                                                </div>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($valueNames[$value]); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Company's Active Jobs -->
            <?php if (!empty($jobs)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Active Job Listings</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($jobs as $job): ?>
                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h6>
                                        <small class="text-muted"><?php echo timeElapsed($job['posted_date']); ?></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($job['job_type']); ?></span>
                                        <?php if ($job['location']): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($job['location']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <?php if ($company['contact_number']): ?>
                        <div class="mb-3">
                            <i class="fas fa-phone me-2"></i>
                            <?php echo htmlspecialchars($company['contact_number']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($company['address']): ?>
                        <div class="mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo nl2br(htmlspecialchars($company['address'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Company Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Active Jobs</span>
                        <span class="badge bg-primary"><?php echo count($jobs); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Member Since</span>
                        <span class="text-muted"><?php echo date('M Y', strtotime($company['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?> 