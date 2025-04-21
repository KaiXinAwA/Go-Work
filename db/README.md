# Database Update Instructions

## Updates Included in `update_career_history.sql`

This update file contains the following changes to the GoWork database schema:

### 1. Updates to `user_profiles` table
- Added `institution` column (VARCHAR(255)) to store educational institution names
- Added `completion_status` column (VARCHAR(50)) to track education completion status
- Added `education_highlights` column (TEXT) for detailed education information
- Added `skills` column (TEXT) to store user skills

### 2. Added new `career_history` table
This table stores users' work experience with the following structure:
- `id`: Auto-increment primary key
- `user_id`: Foreign key linked to users table
- `job_title`: User's position title
- `company_name`: Name of the company worked at
- `start_date`: Employment start date
- `end_date`: Employment end date (nullable for current jobs)
- `description`: Job description text
- `created_at` and `updated_at`: Timestamp fields

## How to Apply Updates

To apply these database changes in XAMPP:

1. Open phpMyAdmin in your browser (http://localhost/phpmyadmin)
2. Select the `gowork_db` database from the left panel
3. Click on the "Import" tab at the top
4. Click "Choose File" and select the `update_career_history.sql` file
5. Click "Go" to execute the SQL commands

Alternatively, you can use the MySQL command line:
```
mysql -u username -p gowork_db < /path/to/update_career_history.sql
```

## Verification

After applying updates, verify that:
- The `user_profiles` table has the new columns: institution, completion_status, education_highlights, and skills
- The `career_history` table exists with all the specified columns
- The foreign key constraint works properly between `career_history.user_id` and `users.user_id` 