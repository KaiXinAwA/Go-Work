-- Add created_at column to applications table
-- This SQL file can be run to add a timestamp column to track when applications are created

ALTER TABLE applications
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update existing records to have a timestamp (optional)
-- This will set all existing applications to the current timestamp
-- You can remove this line if you only want new applications to have timestamps
UPDATE applications SET created_at = CURRENT_TIMESTAMP;

-- If you want to require this column in the future:
-- ALTER TABLE applications MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- After adding this column, you can uncomment the following lines in view_job.php:
/*
-- In the SQL query:
ORDER BY a.created_at DESC

-- In the table:
<td><?php echo date('M d, Y', strtotime($application['created_at'])); ?></td>
*/

-- And uncomment this line in view_application.php:
/*
<div class="col-6 text-end"><?php echo date('M d, Y', strtotime($application['created_at'])); ?></div>
*/ 