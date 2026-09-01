<?php
/**
 * Test the new email templates with action buttons.
 */
chdir(dirname(__DIR__));
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['PATH_INFO']   = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once 'index.php';

$CI =& get_instance();
$CI->load->helper(array('mail', 'general'));

$from = get_smtp('smtp_username');
$logo = base_url() . 'uploads/logo/signin/rsz_logo.png';

echo "=== Testing Email Templates with Action Buttons ===\n\n";

// Test 1: Leave Submitted → Employee
echo "1. Leave Submitted → Employee\n";
$body = hrm_leave_email('submitted', 'employee', array(
    'company_name' => 'Stalis Consulting',
    'logo'         => $logo,
    'site_url'     => site_url(),
    'emp_name'     => 'Kindness Zawadi',
    'from_date'    => '2026-09-15',
    'to_date'      => '2026-09-17',
    'leave_id'     => 99,
    'leave_type'   => 'Annual Leave',
    'reason'       => 'Personal matters',
));
$result = hrsale_mail($from, 'Stalis Consulting', 'kindnesszawadi5637@gmail.com', 'Leave Request Submitted — Kindness Zawadi', $body);
echo "  " . ($result ? "✅ SENT" : "❌ FAILED") . "\n\n";

// Test 2: Leave Submitted → Department Head (with Approve button)
echo "2. Leave Submitted → Department Head (with Approve button)\n";
$body2 = hrm_leave_email('submitted', 'department_head', array(
    'company_name' => 'Stalis Consulting',
    'logo'         => $logo,
    'site_url'     => site_url(),
    'emp_name'     => 'Kindness Zawadi',
    'from_date'    => '2026-09-15',
    'to_date'      => '2026-09-17',
    'leave_id'     => 99,
    'leave_type'   => 'Annual Leave',
    'reason'       => 'Personal matters',
    'approver'     => 'Joseph Wangari',
));
$result2 = hrsale_mail($from, 'Stalis Consulting', 'aleslaikipia@gmail.com', 'Leave Request — Kindness Zawadi (Action Required)', $body2);
echo "  " . ($result2 ? "✅ SENT" : "❌ FAILED") . "\n\n";

// Test 3: Leave First Approved → HR (with Review button)
echo "3. Leave First Approved → HR Manager (with Review button)\n";
$body3 = hrm_leave_email('first_approved', 'hr_manager', array(
    'company_name' => 'Stalis Consulting',
    'logo'         => $logo,
    'site_url'     => site_url(),
    'emp_name'     => 'Kindness Zawadi',
    'from_date'    => '2026-09-15',
    'to_date'      => '2026-09-17',
    'leave_id'     => 99,
    'leave_type'   => 'Annual Leave',
));
$result3 = hrsale_mail($from, 'Stalis Consulting', 'kariukikennedy1165@gmail.com', 'Leave First Approved — Kindness Zawadi (Action Required)', $body3);
echo "  " . ($result3 ? "✅ SENT" : "❌ FAILED") . "\n\n";

// Test 4: Leave Approved → Employee (final)
echo "4. Leave Approved (Final) → Employee\n";
$body4 = hrm_leave_email('second_approved', 'employee', array(
    'company_name' => 'Stalis Consulting',
    'logo'         => $logo,
    'site_url'     => site_url(),
    'emp_name'     => 'Kindness Zawadi',
    'from_date'    => '2026-09-15',
    'to_date'      => '2026-09-17',
    'leave_id'     => 99,
    'leave_type'   => 'Annual Leave',
));
$result4 = hrsale_mail($from, 'Stalis Consulting', 'kindnesszawadi5637@gmail.com', 'Leave Approved ✓ — Kindness Zawadi', $body4);
echo "  " . ($result4 ? "✅ SENT" : "❌ FAILED") . "\n\n";

// Test 5: Leave Rejected → Employee
echo "5. Leave Rejected → Employee\n";
$body5 = hrm_leave_email('rejected', 'employee', array(
    'company_name' => 'Stalis Consulting',
    'logo'         => $logo,
    'site_url'     => site_url(),
    'emp_name'     => 'Kindness Zawadi',
    'from_date'    => '2026-09-15',
    'to_date'      => '2026-09-17',
    'leave_id'     => 99,
    'leave_type'   => 'Annual Leave',
    'reason'       => 'Insufficient coverage',
));
$result5 = hrsale_mail($from, 'Stalis Consulting', 'kindnesszawadi5637@gmail.com', 'Leave Rejected — Kindness Zawadi', $body5);
echo "  " . ($result5 ? "✅ SENT" : "❌ FAILED") . "\n\n";

echo "=== Check your Gmail for 5 styled emails with action buttons ===\n";
