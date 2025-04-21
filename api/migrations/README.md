# Database Migrations

This directory contains database migration scripts that need to be run to update the database schema.

## Running Migrations

### Option 1: Web Interface
1. Log in as an admin user
2. Navigate to each migration file in your browser in the following order:
   - `http://your-domain.com/api/migrations/update_status_column_length.php`
   - `http://your-domain.com/api/migrations/add_is_hidden_from_user_column.php`
3. The migration will check if it needs to be applied and will show a success or error message

### Option 2: Direct SQL Execution
1. Connect to your database using phpMyAdmin, MySQL command line, or any other MySQL client
2. Open the `update_applications_table.sql` file in this directory
3. Execute the SQL statements in this file
4. This will make all necessary changes in one step

## Available Migrations

- `update_status_column_length.php` - Updates the applications table's status column to VARCHAR(20) to support longer status values like "Withdrawn"
- `add_is_hidden_from_user_column.php` - Updates the applications table to support the "Withdrawn" status for applications. This migration ensures any applications that were previously hidden from users are now marked as "Withdrawn".
- `update_applications_table.sql` - A combined SQL script that makes all necessary changes in one step

## Troubleshooting

If you encounter the error "Data truncated for column 'status'" when users attempt to withdraw applications, it means the database schema needs to be updated. Run one of the migration options above to fix this issue.

## Manual Application

If you prefer to run the migrations manually through your database management tool, you can find the SQL queries in each migration file. 