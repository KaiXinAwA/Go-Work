<?php
$pageTitle = 'Company Culture Settings';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get company information
$company = getCompanyProfile($_SESSION['user_id']);
if (!$company) {
    redirectTo(SITE_URL . '/pages/company/dashboard.php');
}

// Get existing culture info if available
$companyCulture = getCompanyCulture($company['company_id']);

// Process form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_culture'])) {
    // List of possible culture attributes
    $validAttributes = ['work_environment', 'overtime', 'management', 'work_life_balance', 
                       'dress_code', 'communication', 'decision_making', 'innovation', 
                       'social_events', 'feedback'];
    
    // Collect selected values
    $cultureValues = [];
    foreach ($validAttributes as $attribute) {
        if (isset($_POST[$attribute])) {
            $cultureValues[$attribute] = sanitizeInput($_POST[$attribute]);
        }
    }
    
    // Save additional description if provided
    $cultureDescription = '';
    if (isset($_POST['culture_description'])) {
        $cultureDescription = sanitizeInput($_POST['culture_description']);
    }
    
    // Create culture info object
    $cultureInfo = [
        'values' => $cultureValues,
        'description' => $cultureDescription
    ];
    
    // Save to database
    $saveResult = saveCompanyCulture($company['company_id'], $cultureInfo);
    
    if ($saveResult) {
        $_SESSION['success'] = 'Company culture information saved successfully!';
        // Redirect to company profile
        redirectTo(SITE_URL . '/pages/company/profile.php#culture-info');
    } else {
        $_SESSION['error'] = 'Error saving company culture information. Please try again.';
        redirectTo(SITE_URL . '/pages/company/culture_settings.php');
    }
}

// Include header
require_once $root_path . 'includes/header.php';
?>

<!-- Page Content -->
<div class="container py-5">
    <div class="row">
        <!-- Main Content (full width) -->
        <div class="col-md-12">
            <div class="mb-4">
                <a href="<?php echo SITE_URL; ?>/pages/company/profile.php#culture-info" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Company Profile
                </a>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Company Culture Settings</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <p>Define your company's work culture to help attract candidates who will be a good cultural fit. This information will be used to match job seekers with your company.</p>
                    
                    <form method="POST" action="">
                        <!-- Culture Description -->
                        <div class="mb-4">
                            <label for="culture_description" class="form-label fw-bold">Describe your company culture:</label>
                            <textarea class="form-control" id="culture_description" name="culture_description" rows="4"><?php echo isset($companyCulture['description']) ? htmlspecialchars($companyCulture['description']) : ''; ?></textarea>
                            <div class="form-text">Provide a brief description of your company's values, atmosphere, and working style.</div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Work Environment Preferences</h5>
                        <p class="text-muted mb-4">Select the options that best describe your company's work culture:</p>
                        
                        <!-- Work Environment -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">1. What type of work environment does your company have?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_environment" id="env_collaborative" value="collaborative" <?php echo (isset($companyCulture['values']['work_environment']) && $companyCulture['values']['work_environment'] == 'collaborative') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="env_collaborative">Open office with collaborative spaces</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_environment" id="env_focused" value="focused" <?php echo (isset($companyCulture['values']['work_environment']) && $companyCulture['values']['work_environment'] == 'focused') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="env_focused">Quiet, individual workspaces</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_environment" id="env_flexible" value="flexible" <?php echo (isset($companyCulture['values']['work_environment']) && $companyCulture['values']['work_environment'] == 'flexible') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="env_flexible">Flexible, with option to work remotely</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_environment" id="env_energetic" value="energetic" <?php echo (isset($companyCulture['values']['work_environment']) && $companyCulture['values']['work_environment'] == 'energetic') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="env_energetic">Dynamic, high-energy environment</label>
                            </div>
                        </div>
                        
                        <!-- Overtime Expectations -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">2. What are your company's expectations around overtime?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="overtime" id="overtime_dedicated" value="dedicated" <?php echo (isset($companyCulture['values']['overtime']) && $companyCulture['values']['overtime'] == 'dedicated') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="overtime_dedicated">Employees often work overtime to meet deadlines</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="overtime" id="overtime_structured" value="structured" <?php echo (isset($companyCulture['values']['overtime']) && $companyCulture['values']['overtime'] == 'structured') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="overtime_structured">Strict 9-5 hours with overtime only in emergencies</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="overtime" id="overtime_flexible" value="flexible" <?php echo (isset($companyCulture['values']['overtime']) && $companyCulture['values']['overtime'] == 'flexible') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="overtime_flexible">Flexible hours that may vary day-to-day</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="overtime" id="overtime_passionate" value="passionate" <?php echo (isset($companyCulture['values']['overtime']) && $companyCulture['values']['overtime'] == 'passionate') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="overtime_passionate">Long hours expected for meaningful work</label>
                            </div>
                        </div>
                        
                        <!-- Management Style -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">3. What management style is common at your company?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="management" id="mgmt_autonomous" value="autonomous" <?php echo (isset($companyCulture['values']['management']) && $companyCulture['values']['management'] == 'autonomous') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mgmt_autonomous">Hands-off, with lots of autonomy</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="management" id="mgmt_structured" value="structured" <?php echo (isset($companyCulture['values']['management']) && $companyCulture['values']['management'] == 'structured') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mgmt_structured">Structured with clear guidance</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="management" id="mgmt_collaborative" value="collaborative" <?php echo (isset($companyCulture['values']['management']) && $companyCulture['values']['management'] == 'collaborative') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mgmt_collaborative">Collaborative and mentoring</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="management" id="mgmt_results" value="results-focused" <?php echo (isset($companyCulture['values']['management']) && $companyCulture['values']['management'] == 'results-focused') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mgmt_results">Results-oriented without micromanagement</label>
                            </div>
                        </div>
                        
                        <!-- Work-Life Balance -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">4. How does your company approach work-life balance?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_life_balance" id="balance_balanced" value="balanced" <?php echo (isset($companyCulture['values']['work_life_balance']) && $companyCulture['values']['work_life_balance'] == 'balanced') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="balance_balanced">High priority on employee personal time</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_life_balance" id="balance_flexible" value="flexible" <?php echo (isset($companyCulture['values']['work_life_balance']) && $companyCulture['values']['work_life_balance'] == 'flexible') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="balance_flexible">Important, but flexibility expected when needed</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_life_balance" id="balance_dedicated" value="dedicated" <?php echo (isset($companyCulture['values']['work_life_balance']) && $companyCulture['values']['work_life_balance'] == 'dedicated') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="balance_dedicated">Work prioritized during busy periods</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="work_life_balance" id="balance_integrated" value="integrated" <?php echo (isset($companyCulture['values']['work_life_balance']) && $companyCulture['values']['work_life_balance'] == 'integrated') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="balance_integrated">Work and personal life blend together</label>
                            </div>
                        </div>
                        
                        <!-- Dress Code -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">5. What is your company's dress code?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="dress_code" id="dress_traditional" value="traditional" <?php echo (isset($companyCulture['values']['dress_code']) && $companyCulture['values']['dress_code'] == 'traditional') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="dress_traditional">Business professional (suits, formal wear)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="dress_code" id="dress_moderate" value="moderate" <?php echo (isset($companyCulture['values']['dress_code']) && $companyCulture['values']['dress_code'] == 'moderate') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="dress_moderate">Business casual</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="dress_code" id="dress_casual" value="casual" <?php echo (isset($companyCulture['values']['dress_code']) && $companyCulture['values']['dress_code'] == 'casual') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="dress_casual">Casual (jeans, t-shirts)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="dress_code" id="dress_relaxed" value="relaxed" <?php echo (isset($companyCulture['values']['dress_code']) && $companyCulture['values']['dress_code'] == 'relaxed') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="dress_relaxed">No dress code/anything goes</label>
                            </div>
                        </div>
                        
                        <!-- Communication Style -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">6. How does your company typically communicate?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="communication" id="comm_direct" value="direct" <?php echo (isset($companyCulture['values']['communication']) && $companyCulture['values']['communication'] == 'direct') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="comm_direct">Direct, face-to-face conversations</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="communication" id="comm_formal" value="formal" <?php echo (isset($companyCulture['values']['communication']) && $companyCulture['values']['communication'] == 'formal') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="comm_formal">Email or written communication</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="communication" id="comm_tech" value="tech-savvy" <?php echo (isset($companyCulture['values']['communication']) && $companyCulture['values']['communication'] == 'tech-savvy') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="comm_tech">Instant messaging and collaboration tools</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="communication" id="comm_adaptable" value="adaptable" <?php echo (isset($companyCulture['values']['communication']) && $companyCulture['values']['communication'] == 'adaptable') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="comm_adaptable">A mix of all communication methods</label>
                            </div>
                        </div>
                        
                        <!-- Decision Making -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">7. How are decisions typically made at your company?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="decision_making" id="decision_agile" value="agile" <?php echo (isset($companyCulture['values']['decision_making']) && $companyCulture['values']['decision_making'] == 'agile') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="decision_agile">Quick decisions, even with incomplete information</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="decision_making" id="decision_analytical" value="analytical" <?php echo (isset($companyCulture['values']['decision_making']) && $companyCulture['values']['decision_making'] == 'analytical') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="decision_analytical">Careful analysis with all available data</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="decision_making" id="decision_collaborative" value="collaborative" <?php echo (isset($companyCulture['values']['decision_making']) && $companyCulture['values']['decision_making'] == 'collaborative') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="decision_collaborative">Collaborative decision-making with team input</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="decision_making" id="decision_balanced" value="balanced" <?php echo (isset($companyCulture['values']['decision_making']) && $companyCulture['values']['decision_making'] == 'balanced') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="decision_balanced">Balance of data and intuition</label>
                            </div>
                        </div>
                        
                        <!-- Innovation -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">8. How important is innovation at your company?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="innovation" id="innovation_innovative" value="innovative" <?php echo (isset($companyCulture['values']['innovation']) && $companyCulture['values']['innovation'] == 'innovative') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="innovation_innovative">Critical - we work on cutting-edge projects</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="innovation" id="innovation_balanced" value="balanced" <?php echo (isset($companyCulture['values']['innovation']) && $companyCulture['values']['innovation'] == 'balanced') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="innovation_balanced">Important, but proven methods also matter</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="innovation" id="innovation_traditional" value="traditional" <?php echo (isset($companyCulture['values']['innovation']) && $companyCulture['values']['innovation'] == 'traditional') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="innovation_traditional">We prefer established processes and stability</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="innovation" id="innovation_incremental" value="incremental" <?php echo (isset($companyCulture['values']['innovation']) && $companyCulture['values']['innovation'] == 'incremental') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="innovation_incremental">We focus on improving existing processes incrementally</label>
                            </div>
                        </div>
                        
                        <!-- Social Events -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">9. What type of company social events do you typically have?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="social_events" id="social_energetic" value="energetic" <?php echo (isset($companyCulture['values']['social_events']) && $companyCulture['values']['social_events'] == 'energetic') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="social_energetic">Active team building and outdoor activities</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="social_events" id="social_professional" value="professional" <?php echo (isset($companyCulture['values']['social_events']) && $companyCulture['values']['social_events'] == 'professional') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="social_professional">Professional networking events</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="social_events" id="social_social" value="social" <?php echo (isset($companyCulture['values']['social_events']) && $companyCulture['values']['social_events'] == 'social') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="social_social">Casual social gatherings like happy hours</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="social_events" id="social_independent" value="independent" <?php echo (isset($companyCulture['values']['social_events']) && $companyCulture['values']['social_events'] == 'independent') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="social_independent">Minimal work social events</label>
                            </div>
                        </div>
                        
                        <!-- Feedback Style -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">10. How does your company typically provide feedback?</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="feedback" id="feedback_direct" value="direct" <?php echo (isset($companyCulture['values']['feedback']) && $companyCulture['values']['feedback'] == 'direct') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="feedback_direct">Direct and straightforward</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="feedback" id="feedback_considerate" value="considerate" <?php echo (isset($companyCulture['values']['feedback']) && $companyCulture['values']['feedback'] == 'considerate') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="feedback_considerate">Private, constructive conversations</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="feedback" id="feedback_reflective" value="reflective" <?php echo (isset($companyCulture['values']['feedback']) && $companyCulture['values']['feedback'] == 'reflective') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="feedback_reflective">Written feedback with time to process</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="feedback" id="feedback_structured" value="structured" <?php echo (isset($companyCulture['values']['feedback']) && $companyCulture['values']['feedback'] == 'structured') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="feedback_structured">Regular, structured performance reviews</label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <button type="submit" name="save_culture" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-save me-2"></i> Save Culture Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?> 