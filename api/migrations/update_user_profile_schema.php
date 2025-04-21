<?php
/**
 * Migration: Update User Profile Schema
 * 
 * Adds education, skills, and career history fields to user profiles
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Get database connection
$conn = getDbConnection();

// Start transaction
$conn->begin_transaction();

try {
    // 1. Add new columns to user_profiles table
    $alterUserProfilesQuery = "
    ALTER TABLE user_profiles 
    ADD COLUMN institution VARCHAR(255) NULL AFTER education,
    ADD COLUMN completion_status VARCHAR(50) NULL AFTER institution,
    ADD COLUMN education_highlights TEXT NULL AFTER completion_status,
    ADD COLUMN skills TEXT NULL AFTER education_highlights;
    ";
    
    $conn->query($alterUserProfilesQuery);
    
    // 2. Create career_history table if it doesn't exist
    $createCareerHistoryQuery = "
    CREATE TABLE IF NOT EXISTS career_history (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        job_title VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NULL,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_user_id (user_id),
        CONSTRAINT fk_career_history_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $conn->query($createCareerHistoryQuery);
    
    // Commit transaction
    $conn->commit();
    
    echo "Migration completed successfully!";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Migration failed: " . $e->getMessage();
} 