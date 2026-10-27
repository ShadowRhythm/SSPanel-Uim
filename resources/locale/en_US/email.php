<?php

declare(strict_types=1);

return [
    // Verify Email (verify_code.tpl)
    'verify_email_subject' => '%app_name% - Verification Email',
    'verify_email_title' => 'Email Verification',
    'verify_code_text' => 'Your requested email verification code is: %code%',
    'verify_code_expire' => 'This verification code is valid until %expire%.',
    'verify_code_ignore' => 'If you did not apply for this code, please ignore this email.',

    // Password Reset (password_reset.tpl)
    'password_reset_subject' => '%app_name% - Password Reset',
    'password_reset_title' => 'Password Reset',
    'password_reset_desc' => 'You received this email because you requested a password reset on %app_name%. If this was not requested by you, please ignore this email.',
    'click_to_reset_password' => 'Click here to reset your password',

    // New User (new_user.tpl)
    'account_created_title' => 'Account Created',

    // Test Email (test.tpl)
    'test_email_subject' => 'Test Email',
    'test_email_title' => 'Email Delivery Test',
    'test_email_desc' => 'This is a test email. If you receive this, your email configuration is working properly.',

    // Traffic Report (traffic_report.tpl)
    'traffic_report_subject' => '%app_name% - Daily Traffic Report & Announcements',
    'traffic_report_title' => 'Daily Traffic Report',
    'username_label' => 'Username: %username%',
    'email_label' => 'Email: %email%',
    'total_traffic' => 'Total Traffic: %traffic%',
    'used_traffic' => 'Used Traffic: %traffic%',
    'remaining_traffic' => 'Remaining Traffic: %traffic%',
    'today_traffic' => 'Today\'s Traffic: %traffic%',
    'site_announcement' => 'Site Announcement:',
    'good_night' => 'Good night!',

    // Financial Reports (finance.tpl)
    'finance_daily_title' => 'Daily Financial Report',
    'finance_weekly_title' => 'Weekly Financial Report',
    'finance_monthly_title' => 'Monthly Financial Report',
    'finance_last_week_orders' => 'Last week total orders: %count%',
    'finance_last_week_total' => 'Last week total income: %amount%',
    'finance_last_month_orders' => 'Last month total orders: %count%',
    'finance_last_month_total' => 'Last month total income: %amount%',

    // Shared Header / Footer (header.tpl, footer.tpl, warn.tpl)
    'system_notice' => 'System Notice',
    'modify_email_settings' => 'Modify Email Settings',

    // Notifications (Notification.php / Cron.php)
    'class_expired_subject' => '%app_name% - Your account level has expired',
    'free_traffic_reset_subject' => '%app_name% - Free Traffic Reset Notice',
    'free_traffic_reset_body' => 'Hello, your free traffic has been reset to %bandwidth% GB.',
    'low_traffic_subject' => '%app_name% - Your remaining traffic is low',
    'low_traffic_body' => 'Hello, the system found that your remaining traffic is lower than %limit% %unit%.',
    'new_login_subject' => '%app_name% - New Login Notification',
    'new_login_body' => 'Your account logged in to the user panel from IP %ip% at %time%',
    'new_sub_subject' => '%app_name% - New Subscription Notification',
    'new_sub_body' => 'Your account subscribed from IP %ip% at %time%',
    'ticket_replied_subject' => '%app_name% - Ticket Replied',
    'ticket_replied_body' => 'Hello, someone replied to your ticket, please check it.',
    'new_ticket_subject' => '%app_name% - New Ticket Created',
    'new_ticket_body' => 'Admin, someone opened a new ticket, please handle it in time.',
];
