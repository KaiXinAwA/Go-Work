-- Fix application status values
-- This SQL will update any NULL or empty status values to 'Pending'

-- Update NULL status values
UPDATE applications 
SET status = 'Pending' 
WHERE status IS NULL;

-- Update empty status values
UPDATE applications 
SET status = 'Pending' 
WHERE status = '';

-- Add a check constraint to ensure status is always one of the allowed values (optional)
-- Note: If your database version supports CHECK constraints, you can uncomment this:
/*
ALTER TABLE applications
ADD CONSTRAINT check_status CHECK (
    status IN ('Pending', 'Reviewed', 'Shortlisted', 'Rejected', 'Hired')
);
*/ 