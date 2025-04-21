-- Update Applications Table to Support Withdrawn Status
-- This script makes all necessary changes to support the "Withdrawn" status feature

-- 1. Modify the status column to be VARCHAR(20) to support longer status values
ALTER TABLE applications MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Pending';

-- 2. Add the is_hidden_from_user column if it doesn't exist (for backward compatibility)
-- Note: This column is no longer used but might be referenced in old code
SET @dbname = DATABASE();
SET @tablename = "applications";
SET @columnname = "is_hidden_from_user";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 0;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Update any applications that might be hidden from user to have 'Withdrawn' status
UPDATE applications SET status = 'Withdrawn' WHERE is_hidden_from_user = 1;

-- Done!
SELECT 'Applications table updated successfully to support Withdrawn status' AS 'Status'; 