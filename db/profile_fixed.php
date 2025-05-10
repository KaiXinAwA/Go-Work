<!-- This is a partial update for the profile.php file, focusing on fixing the career history section -->

<!-- Career History Card - Modified Section -->
<div class="card mb-4" id="career-history">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Career History</h5>
        <button class="btn btn-outline-light custom-btn" data-bs-toggle="modal" data-bs-target="#addCareerModal">
            <i class="fas fa-plus me-1"></i> Add Position
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($careerHistory)): ?>
            <div class="alert alert-secondary">
                <i class="fas fa-info-circle me-2"></i>
                You haven't added any career history yet. Click the "Add Position" button to get started.
            </div>
        <?php else: ?>
            <div class="accordion" id="careerAccordion">
                <?php foreach ($careerHistory as $index => $position): ?>
                    <?php 
                    // Generate a unique identifier using career_id, defaulting to index if not available
                    $positionId = isset($position['career_id']) ? $position['career_id'] : 'position_' . $index;
                    ?>
                    <div class="accordion-item mb-3 border">
                        <h2 class="accordion-header" id="heading<?php echo $positionId; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $positionId; ?>" aria-expanded="false" aria-controls="collapse<?php echo $positionId; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($position['job_title']); ?></strong> at <?php echo htmlspecialchars($position['company_name']); ?>
                                    </div>
                                    <div class="text-muted small ms-3">
                                        <?php 
                                        echo date('M Y', strtotime($position['start_date'])); 
                                        echo ' - '; 
                                        echo !empty($position['end_date']) ? date('M Y', strtotime($position['end_date'])) : 'Present';
                                        ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $positionId; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $positionId; ?>" data-bs-parent="#careerAccordion">
                            <div class="accordion-body">
                                <p><?php echo nl2br(htmlspecialchars($position['description'])); ?></p>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark custom-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCareerModal"
                                            data-id="<?php echo $positionId; ?>"
                                            data-job-title="<?php echo htmlspecialchars($position['job_title']); ?>"
                                            data-company-name="<?php echo htmlspecialchars($position['company_name']); ?>"
                                            data-start-date="<?php echo $position['start_date']; ?>"
                                            data-end-date="<?php echo $position['end_date']; ?>"
                                            data-description="<?php echo htmlspecialchars($position['description']); ?>">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger custom-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteCareerModal"
                                            data-id="<?php echo $positionId; ?>"
                                            data-job-title="<?php echo htmlspecialchars($position['job_title']); ?>">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div> 