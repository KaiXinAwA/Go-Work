# GoWork - Job Finding Website

A comprehensive job finding platform built with HTML, CSS, JavaScript, PHP, and MySQL.

## Overview

GoWork is a full-featured job finding platform that connects job seekers with employers. The platform streamlines the job application process while providing robust management tools for employers, staff, and administrators.

## Completed Features

### For Job Seekers
- **User Account Management**: Register, login, update profile, and reset password
- **Resume Management**: Upload and manage resumes in PDF format
- **Advanced Job Search**: Filter jobs by keywords, location, job type, and salary
- **Application Tracking**: Monitor application status (pending, viewed, shortlisted, rejected)
- **Cultural Fit Quiz**: Take a quiz to find companies matching your work style
- **One-Click Apply**: Apply to jobs with a single click

### For Employers
- **Company Profile**: Create and customize your company profile
- **License Verification**: Submit company license for official verification
- **Job Management**: Post, edit, and manage job listings
- **Applicant Review**: Review applicants and update their application status
- **Company Culture**: Define your company culture to attract suitable candidates

### For GoWork Staff
- **License Review**: Approve or reject company license submissions
- **Company Management**: Monitor company profiles and activities

### For Administrators
- **User Management**: Manage all users including regular users and staff
- **Content Oversight**: Monitor and manage content across the platform
- **Platform Statistics**: View key metrics about platform usage

## Technical Implementation

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Font Awesome icons
- **Backend**: PHP 8.2
- **Database**: MySQL via XAMPP
- **Email System**: Resend API for password resets and notifications
- **Security**: Password hashing, input sanitization, CSRF protection
- **Responsive Design**: Mobile-friendly interface

## Installation & Setup

1. **Prerequisites**:
   - XAMPP (Apache, MySQL, PHP)
   - Web browser
   - Resend API account for email functionality

2. **Setup Instructions**:
   - Clone this repository to your XAMPP's htdocs folder
   - Start Apache and MySQL services in XAMPP
   - Create a database named `gowork_db` in phpMyAdmin
   - Import the database schema from `/db/gowork_db.sql`
   - Configure database connection in `/includes/config.php`
   - Configure your Resend API key in `/includes/config.php`
   - Access the website at `http://localhost`

3. **Default Admin Account**:
   - Email: bcs24090019@student.uts.edu.my
   - Password: qwerty1234

## Directory Structure

- `/api`: Backend endpoints for data processing
- `/assets`: CSS, JavaScript, images, and fonts
- `/db`: Database schema and setup scripts
- `/includes`: PHP utility files, templates, and configuration
- `/pages`: Main website pages organized by user type
- `/uploads`: User-uploaded files (organized in subdirectories)

## Usage Guide

### For Job Seekers
1. Register/login with your account
2. Complete your profile and upload your resume
3. Take the cultural fit quiz to find matching companies
4. Search for jobs using filters
5. Apply to positions and track your applications

### For Employers
1. Register/login with your company account
2. Submit your company license for verification
3. Complete your company profile and culture information
4. Post job listings with detailed descriptions
5. Review and manage applications

### For Admin/Staff
1. Login with your credentials
2. Review company licenses and user accounts
3. Manage platform content and user issues

## Future Development Roadmap

- **Messaging System**: Direct communication between employers and candidates
- **Advanced Analytics**: Detailed insights for employers and job seekers
- **Mobile Application**: Native mobile apps for iOS and Android
- **Premium Features**: Subscription-based advanced features
- **Integration with Job Boards**: Import/export job listings from other platforms

---

*This project was developed as a school assignment and demonstrates skills in web development, database management, and application security.*
# Redeploy
# Redeploy
