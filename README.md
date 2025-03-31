# GoWork - Job Finding Website

A web-based job finding platform built with HTML, CSS, and JavaScript, using PHP for server-side functionality and MySQL for database storage.

## Overview

GoWork is a comprehensive job finding platform that connects job seekers with employers. It provides a streamlined interface for users to browse and apply for jobs, and for employers to post job openings and manage applicants.

## Features

### For Job Seekers
- **Account Management**: Register, login, and manage your profile
- **Resume Upload**: Upload your resume (PDF format)
- **Job Search**: Browse and search for jobs by keywords and location
- **Application Tracking**: Track the status of your job applications
- **One-Click Apply**: Easily apply to jobs with a single click

### For Employers
- **Company Profile**: Create and manage your company profile
- **License Submission**: Submit your company license for verification
- **Job Posting**: Create and manage job listings
- **Applicant Management**: Review applicants and manage their statuses

### For GoWork Company Workers
- **License Verification**: Review and approve company licenses

### For Admins
- **User Management**: Manage all users on the platform
- **Content Oversight**: Monitor and manage content across the site

## Technical Implementation

- **Front-end**: HTML, CSS, JavaScript, Bootstrap 5
- **Back-end**: PHP
- **Database**: MySQL via XAMPP
- **Email Functionality**: Resend API

## Installation & Setup

1. **Prerequisites**:
   - XAMPP installed on your system (Apache, MySQL, PHP)
   - Web browser

2. **Setup Instructions**:
   - Clone or download this repository to your XAMPP's htdocs folder
   - Start Apache and MySQL services in XAMPP
   - Create a new database named `gowork_db` in phpMyAdmin
   - Import the database schema from `/db/gowork_db.sql`
   - Configure database connection in `/includes/config.php`
   - Configure Resend API key in `/includes/config.php` for email functionality
   - Access the website at `http://localhost/GoWork`

3. **Default Admin Account**:
   - Email: bcs24090019@student.uts.edu.my
   - Password: qwerty1234

## Directory Structure

- `/assets`: CSS, JavaScript, Images, and Fonts
- `/uploads`: User-uploaded files (resumes, licenses, profile pictures)
- `/includes`: PHP utility files and templates
- `/api`: Backend endpoints for AJAX requests
- `/pages`: Main website pages
- `/db`: Database schema

## Usage Flow

1. **For Job Seekers**:
   - Register an account as a job seeker
   - Complete your profile and upload your resume
   - Search for jobs and apply with one click
   - Track your application statuses

2. **For Employers**:
   - Register an account as an employer
   - Submit your company license for approval
   - Once approved, post job listings
   - Review and manage applications

3. **For GoWork Company Workers**:
   - Login with your credentials
   - Review company license submissions
   - Approve or reject licenses

## License Approval Process

1. Employer submits company license (PDF)
2. GoWork company worker reviews the license
3. License is either approved or rejected
4. If approved, employer can start posting jobs
5. If rejected, employer needs to submit a new license

## Notes for Future Development

- Implement advanced search filters
- Add a messaging system between employers and applicants
- Implement job recommendations based on user profiles
- Add analytics dashboard for employers
- Implement email notifications for application updates
