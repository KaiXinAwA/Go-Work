-- Add categories field to jobs table
ALTER TABLE jobs ADD COLUMN categories TEXT AFTER job_type;

-- Update existing jobs to have an empty categories field
UPDATE jobs SET categories = '' WHERE categories IS NULL; 