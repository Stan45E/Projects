<?php
// config.php - UPDATED FOR OUTLOOK / MICROSOFT 365

// The email address that receives the alerts
define('SHARED_EMAIL_ADDRESS', 'your-shared-inbox@example.com'); 

// --- SMTP Server Settings for Outlook/Microsoft 365 ---
define('SMTP_HOST', 'smtp.office365.com');                // Microsoft 365 SMTP server
define('SMTP_USERNAME', 'your-sending-email@yourdomain.com'); // The full email address of the account you're sending from
define('SMTP_PASSWORD', 'your-outlook-app-password');        // The App Password you generate, or your regular password if MFA is off
define('SMTP_PORT', 587);                                  // Port for STARTTLS
define('SMTP_SECURE', 'tls');                              // Encryption method (must be 'tls' for port 587)