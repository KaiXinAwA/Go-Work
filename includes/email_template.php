<?php
/**
 * Email Template Functions
 */

/**
 * Get the HTML template for password reset email
 * 
 * @param string $recipientName The recipient's name
 * @param string $newPassword The new password
 * @return string HTML template
 */
function getPasswordResetEmailTemplate($recipientName, $newPassword) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset - ' . SITE_NAME . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background-color: #0d6efd;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .content {
                background-color: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                color: #666;
            }
            .password-box {
                background-color: #fff;
                border: 1px solid #ddd;
                padding: 15px;
                margin: 20px 0;
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 1px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . SITE_NAME . ' - Password Reset</h1>
            </div>
            <div class="content">
                <p>Dear ' . htmlspecialchars($recipientName) . ',</p>
                <p>Your password has been reset as requested. Your new password is:</p>
                
                <div class="password-box">
                    ' . htmlspecialchars($newPassword) . '
                </div>
                
                <p>For security reasons, we recommend that you log in and change your password as soon as possible.</p>
                
                <p>If you did not request this password reset, please contact our support team immediately.</p>
                
                <p>Thank you,<br>The ' . SITE_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>This is an automated email, please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $html;
}

/**
 * Get the HTML template for job application notification email
 * 
 * @param array $job The job details
 * @param array $applicant The applicant details
 * @return string HTML template
 */
function getJobApplicationEmailTemplate($job, $applicant) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>New Job Application - ' . SITE_NAME . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background-color: #0d6efd;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .content {
                background-color: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                color: #666;
            }
            .job-details {
                background-color: #fff;
                border: 1px solid #ddd;
                padding: 15px;
                margin: 20px 0;
            }
            .applicant-details {
                background-color: #fff;
                border: 1px solid #ddd;
                padding: 15px;
                margin: 20px 0;
            }
            .button {
                display: inline-block;
                background-color: #0d6efd;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . SITE_NAME . ' - New Job Application</h1>
            </div>
            <div class="content">
                <p>Dear Employer,</p>
                <p>You have received a new application for the following job:</p>
                
                <div class="job-details">
                    <h3>' . htmlspecialchars($job['job_title']) . '</h3>
                    <p><strong>Job ID:</strong> ' . $job['job_id'] . '</p>
                    <p><strong>Location:</strong> ' . htmlspecialchars($job['location']) . '</p>
                    <p><strong>Job Type:</strong> ' . htmlspecialchars($job['job_type']) . '</p>
                </div>
                
                <p>Applicant details:</p>
                
                <div class="applicant-details">
                    <p><strong>Name:</strong> ' . htmlspecialchars($applicant['full_name']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($applicant['email']) . '</p>
                </div>
                
                <p>The applicant\'s resume is attached to this email.</p>
                
                <p>You can also view this application in your dashboard:</p>
                
                <a href="' . SITE_URL . '/pages/company/applications.php" class="button">View Application</a>
                
                <p>Thank you for using ' . SITE_NAME . '!</p>
                
                <p>Regards,<br>The ' . SITE_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>This is an automated email, please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $html;
}

/**
 * Get the HTML template for license approval notification email
 * 
 * @param array $company The company details
 * @param string $status The approval status ('Approved' or 'Not Approved')
 * @return string HTML template
 */
function getLicenseApprovalEmailTemplate($company, $status) {
    $statusText = $status === 'Approved' ? 'Approved' : 'Not Approved';
    $statusColor = $status === 'Approved' ? '#198754' : '#dc3545';
    $statusMessage = $status === 'Approved' 
        ? 'Your company license has been approved. You can now post jobs on our platform.' 
        : 'Your company license has not been approved. Please contact our support team for more information.';
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>License Status Update - ' . SITE_NAME . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background-color: #0d6efd;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .content {
                background-color: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                color: #666;
            }
            .status-box {
                background-color: #fff;
                border: 1px solid #ddd;
                padding: 15px;
                margin: 20px 0;
                text-align: center;
            }
            .status {
                font-size: 24px;
                font-weight: bold;
                color: ' . $statusColor . ';
                margin: 10px 0;
            }
            .button {
                display: inline-block;
                background-color: #0d6efd;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . SITE_NAME . ' - License Status Update</h1>
            </div>
            <div class="content">
                <p>Dear ' . htmlspecialchars($company['company_name']) . ',</p>
                
                <div class="status-box">
                    <p>Your company license status has been updated to:</p>
                    <div class="status">' . $statusText . '</div>
                </div>
                
                <p>' . $statusMessage . '</p>
                
                <a href="' . SITE_URL . '/pages/company/dashboard.php" class="button">Go to Dashboard</a>
                
                <p>Thank you for using ' . SITE_NAME . '!</p>
                
                <p>Regards,<br>The ' . SITE_NAME . ' Team</p>
            </div>
            <div class="footer">
                <p>This is an automated email, please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $html;
}

/**
 * Get the text version of an email (for email clients that don't support HTML)
 * 
 * @param string $html The HTML email
 * @return string Plain text version
 */
function getTextVersionFromHtml($html) {
    // Replace common HTML elements with plain text equivalents
    $text = strip_tags($html);
    $text = str_replace(['&nbsp;', '&amp;', '&lt;', '&gt;'], [' ', '&', '<', '>'], $text);
    $text = preg_replace('/[\r\n]{2,}/', "\n\n", $text);
    $text = trim($text);
    
    return $text;
}
