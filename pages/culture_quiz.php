<?php
$pageTitle = 'Cultural Fit Quiz';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';

// Include header
require_once $root_path . 'includes/header.php';

// Get quiz questions
$quizQuestions = getCultureQuizQuestions();

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$hasResults = false;
$matchingCompanies = [];

// If logged in, check if user has already taken the quiz
if ($isLoggedIn) {
    $userCultureResults = getUserCultureResults($_SESSION['user_id']);
    $hasResults = !empty($userCultureResults);
    
    // Get matching companies if user has results
    if ($hasResults && hasUserType(USER_TYPE_JOBSEEKER)) {
        $matchingCompanies = getMatchingCompanies($_SESSION['user_id']);
    }
}
?>

<style>
.quiz-black-theme {
    background-color: #000;
    color: #fff;
}
.quiz-card {
    border: 1px solid #333;
}
.quiz-card .card-header {
    background-color: #000 !important;
}
.quiz-btn-black {
    background-color: #000;
    color: #fff;
    border-color: #333;
}
.quiz-btn-black:hover {
    background-color: #333;
    color: #fff;
}
.quiz-btn-outline-black {
    color: #000;
    border-color: #000;
}
.quiz-btn-outline-black:hover {
    background-color: #000;
    color: #fff;
}
.progress-bar-black {
    background-color: #000 !important;
}
.badge-black {
    background-color: #000 !important;
}
</style>

<!-- Hero Section -->
<section class="container-fluid py-5 quiz-black-theme">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h1>Cultural Fit Quiz</h1>
                <p class="lead">Find companies that match your work style and values. Take our short quiz to discover where you'll thrive!</p>
            </div>
        </div>
    </div>
</section>

<!-- Quiz Section -->
<section class="container py-5">
    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-info">
            <p><strong>Please <a href="<?php echo SITE_URL; ?>/pages/login.php">login</a> or <a href="<?php echo SITE_URL; ?>/pages/register.php">register</a> to save your quiz results and get matched with companies.</strong></p>
            <p>You can still take the quiz without logging in, but your results won't be saved.</p>
        </div>
    <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
        <div class="alert alert-info">
            <p><strong>Company Profile Culture Settings</strong></p>
            <p>As a company, you can define your company culture in your profile settings instead of taking this quiz.</p>
            <a href="<?php echo SITE_URL; ?>/pages/company/profile.php" class="btn quiz-btn-black mt-2">Go to Company Profile</a>
        </div>
    <?php endif; ?>
    
    <?php if ($hasResults && hasUserType(USER_TYPE_JOBSEEKER)): ?>
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card quiz-card">
                    <div class="card-header quiz-black-theme">
                        <h3 class="mb-0">Your Cultural Profile</h3>
                    </div>
                    <div class="card-body">
                        <p>Based on your responses, here's your work culture profile:</p>
                        <div class="culture-profile mb-4">
                            <?php foreach ($userCultureResults['culture_profile']['values'] as $attribute => $value): ?>
                                <div class="culture-attribute mb-2">
                                    <span class="badge badge-black me-2"><?php echo htmlspecialchars(ucfirst($attribute)); ?></span>
                                    <span class="fw-bold"><?php echo htmlspecialchars(ucfirst($value)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p>Take the quiz again to update your profile or view companies that match your cultural preferences.</p>
                        <button class="btn quiz-btn-black" id="retake-quiz-btn">Retake Quiz</button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($matchingCompanies)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <h2>Companies That Match Your Culture</h2>
                    <p class="text-muted">These companies have work cultures that align with your preferences.</p>
                </div>
                
                <?php foreach ($matchingCompanies as $company): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card quiz-card h-100">
                            <div class="card-header quiz-black-theme">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($company['company_name']); ?></h5>
                                    <span class="badge badge-black"><?php echo $company['match_score']; ?>% Match</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars(substr($company['description'], 0, 150)) . (strlen($company['description']) > 150 ? '...' : ''); ?></p>
                                
                                <?php if (!empty($company['matches'])): ?>
                                    <div class="mb-3">
                                        <h6>Cultural Matches:</h6>
                                        <div>
                                            <?php foreach ($company['matches'] as $match): ?>
                                                <span class="badge bg-light text-dark me-1 mb-1"><?php echo htmlspecialchars(ucfirst($match)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="<?php echo SITE_URL; ?>/pages/company_profile.php?id=<?php echo $company['company_id']; ?>" class="btn quiz-btn-outline-black">View Company</a>
                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?company=<?php echo $company['company_id']; ?>" class="btn quiz-btn-black float-end">See Jobs</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-4">
                <p>No companies currently match your culture profile. Check back later as more companies add their cultural information.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div id="quiz-container" <?php echo (($hasResults && hasUserType(USER_TYPE_JOBSEEKER)) || hasUserType(USER_TYPE_COMPANY)) ? 'style="display:none;"' : ''; ?>>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card quiz-card">
                    <div class="card-header quiz-black-theme">
                        <h3 class="mb-0">Work Culture Quiz</h3>
                    </div>
                    <div class="card-body">
                        <p>Answer these questions honestly to find companies with a culture that matches your preferences.</p>
                        
                        <form id="culture-quiz-form">
                            <?php foreach ($quizQuestions as $index => $question): ?>
                                <div class="question-container mb-4" data-question="<?php echo $index + 1; ?>" <?php echo $index > 0 ? 'style="display:none;"' : ''; ?>>
                                    <h5 class="mb-3">Question <?php echo $index + 1; ?> of <?php echo count($quizQuestions); ?>:</h5>
                                    <p class="fw-bold"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                    
                                    <div class="options-container">
                                        <?php foreach ($question['options'] as $option): ?>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="radio" name="question_<?php echo $question['question_id']; ?>" 
                                                       id="option_<?php echo $option['option_id']; ?>" value="<?php echo $option['option_id']; ?>"
                                                       data-culture-value="<?php echo htmlspecialchars($option['culture_value']); ?>">
                                                <label class="form-check-label" for="option_<?php echo $option['option_id']; ?>">
                                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <?php if ($index > 0): ?>
                                            <button type="button" class="btn quiz-btn-outline-black prev-question-btn">Previous</button>
                                        <?php endif; ?>
                                        
                                        <?php if ($index < count($quizQuestions) - 1): ?>
                                            <button type="button" class="btn quiz-btn-black next-question-btn float-end">Next</button>
                                        <?php else: ?>
                                            <button type="button" class="btn quiz-btn-black submit-quiz-btn float-end">Submit</button>
                                        <?php endif; ?>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </form>
                        
                        <div id="quiz-progress" class="progress mt-4">
                            <div class="progress-bar progress-bar-black" role="progressbar" style="width: 10%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">1/<?php echo count($quizQuestions); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Section (for non-logged in users) -->
    <div id="results-container" style="display:none;">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card quiz-card">
                    <div class="card-header quiz-black-theme">
                        <h3 class="mb-0">Your Cultural Profile Results</h3>
                    </div>
                    <div class="card-body">
                        <p>Based on your responses, here's your work culture profile:</p>
                        <div id="culture-results" class="mb-4"></div>
                        
                        <?php if (!$isLoggedIn): ?>
                            <div class="alert alert-warning">
                                <p><strong>Want to find companies that match your profile?</strong></p>
                                <p>Create an account to save your results and get matched with companies that share your work culture values.</p>
                                <div class="mt-2">
                                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn quiz-btn-black">Create Account</a>
                                    <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn quiz-btn-outline-black ms-2">Login</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <button class="btn quiz-btn-black" id="restart-quiz-btn">Retake Quiz</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('culture-quiz-form');
    const quizContainer = document.getElementById('quiz-container');
    const resultsContainer = document.getElementById('results-container');
    const cultureResults = document.getElementById('culture-results');
    const progressBar = document.querySelector('.progress-bar');
    const totalQuestions = <?php echo count($quizQuestions); ?>;
    
    // Next question buttons
    document.querySelectorAll('.next-question-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const currentQuestion = this.closest('.question-container');
            const questionNum = parseInt(currentQuestion.dataset.question);
            
            // Validate that an option is selected
            const questionId = currentQuestion.querySelector('input').name;
            if (!document.querySelector(`input[name="${questionId}"]:checked`)) {
                alert('Please select an answer before proceeding.');
                return;
            }
            
            // Hide current question and show next
            currentQuestion.style.display = 'none';
            document.querySelector(`.question-container[data-question="${questionNum + 1}"]`).style.display = 'block';
            
            // Update progress bar
            updateProgressBar(questionNum + 1);
        });
    });
    
    // Previous question buttons
    document.querySelectorAll('.prev-question-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const currentQuestion = this.closest('.question-container');
            const questionNum = parseInt(currentQuestion.dataset.question);
            
            // Hide current question and show previous
            currentQuestion.style.display = 'none';
            document.querySelector(`.question-container[data-question="${questionNum - 1}"]`).style.display = 'block';
            
            // Update progress bar
            updateProgressBar(questionNum - 1);
        });
    });
    
    // Submit quiz button
    if (document.querySelector('.submit-quiz-btn')) {
        document.querySelector('.submit-quiz-btn').addEventListener('click', function() {
            // Validate that an option is selected for the last question
            const currentQuestion = this.closest('.question-container');
            const questionId = currentQuestion.querySelector('input').name;
            if (!document.querySelector(`input[name="${questionId}"]:checked`)) {
                alert('Please select an answer before submitting.');
                return;
            }
            
            submitQuiz();
        });
    }
    
    // Retake quiz button
    if (document.getElementById('retake-quiz-btn')) {
        document.getElementById('retake-quiz-btn').addEventListener('click', function() {
            // Reset form and show quiz
            quizForm.reset();
            document.querySelectorAll('.question-container').forEach((question, index) => {
                question.style.display = index === 0 ? 'block' : 'none';
            });
            updateProgressBar(1);
            
            // Hide results section
            document.querySelector('.row.mb-5').style.display = 'none';
            quizContainer.style.display = 'block';
        });
    }
    
    // Restart quiz button (for non-logged in users)
    if (document.getElementById('restart-quiz-btn')) {
        document.getElementById('restart-quiz-btn').addEventListener('click', function() {
            quizForm.reset();
            document.querySelectorAll('.question-container').forEach((question, index) => {
                question.style.display = index === 0 ? 'block' : 'none';
            });
            updateProgressBar(1);
            
            resultsContainer.style.display = 'none';
            quizContainer.style.display = 'block';
        });
    }
    
    function updateProgressBar(currentQuestion) {
        const percentage = Math.round((currentQuestion / totalQuestions) * 100);
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressBar.textContent = currentQuestion + '/' + totalQuestions;
    }
    
    function submitQuiz() {
        // Collect all selected options and their culture values
        const selectedOptions = {};
        const cultureValues = {};
        
        document.querySelectorAll('input[type="radio"]:checked').forEach(option => {
            const questionId = option.name.replace('question_', '');
            const cultureValue = option.dataset.cultureValue;
            
            selectedOptions[questionId] = option.value;
            
            // Organize by culture attribute
            const attribute = cultureValue.split('-')[0];
            cultureValues[attribute] = cultureValue;
        });
        
        // Create results object
        const results = {
            options: selectedOptions,
            values: cultureValues
        };
        
        // If user is logged in and not a company, save results to database
        <?php if ($isLoggedIn && !hasUserType(USER_TYPE_COMPANY)): ?>
        fetch('<?php echo SITE_URL; ?>/api/save_culture_quiz.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: <?php echo $_SESSION['user_id']; ?>,
                culture_profile: results
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated results
                window.location.reload();
            } else {
                alert('Error saving your results. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
        <?php else: ?>
        // Display results for non-logged in users or company accounts
        displayResults(results);
        <?php endif; ?>
    }
    
    function displayResults(results) {
        // Clear previous results
        cultureResults.innerHTML = '';
        
        // Display each culture value
        for (const [attribute, value] of Object.entries(results.values)) {
            const attributeEl = document.createElement('div');
            attributeEl.className = 'culture-attribute mb-2';
            attributeEl.innerHTML = `
                <span class="badge badge-black me-2">${capitalizeFirstLetter(attribute)}</span>
                <span class="fw-bold">${capitalizeFirstLetter(value)}</span>
            `;
            cultureResults.appendChild(attributeEl);
        }
        
        // Hide quiz and show results
        quizContainer.style.display = 'none';
        resultsContainer.style.display = 'block';
    }
    
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
});
</script>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?> 