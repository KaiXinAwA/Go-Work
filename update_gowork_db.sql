-- SQL script to update the gowork database structure
-- This script adds tables for user profiles and career history

-- Create user_profiles table if it doesn't exist
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100),
    phone VARCHAR(20),
    location VARCHAR(100),
    bio TEXT,
    skills TEXT,
    institution VARCHAR(100),
    completion_status VARCHAR(50),
    education_highlights TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create career_history table if it doesn't exist
CREATE TABLE IF NOT EXISTS career_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add sample data for testing (optional)
-- Uncomment these lines if you want to add sample data

/*
-- Sample user profile
INSERT INTO user_profiles (user_id, name, phone, location, bio, skills, institution, completion_status, education_highlights)
VALUES (1, 'John Doe', '555-123-4567', 'New York, NY', 'Experienced software developer with a passion for web development', 
'JavaScript,HTML,CSS,PHP,MySQL', 'University of Technology', 'Graduated', 'Bachelor of Computer Science\nHonors Program\nDean\'s List');

-- Sample career history entries
INSERT INTO career_history (user_id, job_title, company_name, start_date, end_date, description)
VALUES 
(1, 'Senior Developer', 'Tech Solutions Inc.', '2020-01-01', NULL, 'Leading a team of developers on various web projects'),
(1, 'Web Developer', 'Digital Agency', '2017-03-15', '2019-12-31', 'Developed responsive websites for clients using modern frameworks');
*/ 

-- Add USER_TYPE_WORKER to system
-- This will be used for GoWork worker accounts that can only approve licenses
-- The value is 5 since USER_TYPE_ADMIN is 4 as seen in config.php

-- First, ensure the gowork_workers table exists (it already does based on checking)
-- We'll add a 'can_manage_users' column to control permissions
ALTER TABLE `gowork_workers` 
ADD COLUMN `can_manage_users` TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Permission flag to indicate if worker can manage users';

-- Update existing workers to default permission (can't manage users)
UPDATE `gowork_workers` SET `can_manage_users` = 0;

-- Update existing worker user's type to be USER_TYPE_GOWORK (3) if they are in the gowork_workers table
-- The current worker user (ID 5) has user_type=1 which is USER_TYPE_JOBSEEKER
UPDATE `users` 
SET `user_type` = 3 
WHERE `user_id` IN (SELECT `user_id` FROM `gowork_workers`);

-- SQL comments for reference:
-- USER_TYPE_JOBSEEKER = 1
-- USER_TYPE_COMPANY = 2
-- USER_TYPE_GOWORK = 3
-- USER_TYPE_ADMIN = 4
-- USER_TYPE_WORKER = 3 (same as GOWORK since gowork_workers table contains the worker info) 