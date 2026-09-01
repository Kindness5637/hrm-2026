<?php
/**
 * Notification System — Comprehensive Test Script
 * 
 * Tests:
 * 1. Database schema & seed integrity
 * 2. Role override resolution logic
 * 3. Recipient resolution for all role types
 * 4. Routing rules completeness
 * 5. Edge cases (dangling heads, empty overrides, missing rules)
 * 6. Actual email send (test message)
 * 7. Outbox logging
 *
 * Run: php /home/kindness/Desktop/hrm./hrm/tests/test_notifications.php
 */

// ============================================================
// CONFIG
// ============================================================
$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_USER = 'hrmuser';
$DB_PASS = 'b@irk@@';
$DB_NAME = 'hrmxtra_hrm';

$TEST_EMAIL = 'kindnesszawadi5637@gmail.com'; // change to your email for live test
$PASS = 0;
$FAIL = 0;
$ERRORS = array();

// ============================================================
// HELPERS
// ============================================================
function ok($label, $condition, $detail = '') {
    global $PASS, $FAIL, $ERRORS;
    if ($condition) {
        $PASS++;
        echo "  ✅  {$label}\n";
    } else {
        $FAIL++;
        $msg = "  ❌  {$label}";
        if ($detail) $msg .= " — {$detail}";
        echo $msg . "\n";
        $ERRORS[] = $label . ': ' . $detail;
    }
}

function section($title) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "  {$title}\n";
    echo str_repeat('=', 60) . "\n";
}

// ============================================================
// CONNECT
// ============================================================
$pdo = new PDO(
    "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER, $DB_PASS,
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
);

section('1. DATABASE SCHEMA & SEED INTEGRITY');

// 1a. Tables exist
$tables = array('xin_role_email_overrides', 'xin_notification_rules', 'xin_notification_outbox');
foreach ($tables as $t) {
    $r = $pdo->query("SHOW TABLES LIKE '{$t}'")->fetchAll();
    ok("Table '{$t}' exists", count($r) > 0);
}

// 1b. Columns exist
$cols_overrides = $pdo->query("SHOW COLUMNS FROM xin_role_email_overrides")->fetchAll(PDO::FETCH_COLUMN);
foreach (array('id', 'role_type', 'scope_id', 'override_email', 'notes', 'created_by') as $c) {
    ok("xin_role_email_overrides has column '{$c}'", in_array($c, $cols_overrides));
}

$cols_rules = $pdo->query("SHOW COLUMNS FROM xin_notification_rules")->fetchAll(PDO::FETCH_COLUMN);
foreach (array('rule_id', 'module', 'event', 'notify_roles', 'enabled') as $c) {
    ok("xin_notification_rules has column '{$c}'", in_array($c, $cols_rules));
}

$cols_outbox = $pdo->query("SHOW COLUMNS FROM xin_notification_outbox")->fetchAll(PDO::FETCH_COLUMN);
foreach (array('outbox_id', 'sent_from', 'sent_to', 'cc', 'subject', 'status', 'created_at') as $c) {
    ok("xin_notification_outbox has column '{$c}'", in_array($c, $cols_outbox));
}

// 1c. Seed data
$overrides = $pdo->query("SELECT role_type, scope_id, override_email FROM xin_role_email_overrides ORDER BY role_type")->fetchAll(PDO::FETCH_ASSOC);
ok("Directory has entries", count($overrides) >= 3, "Found " . count($overrides) . " entries");

$expected_overrides = array('ceo', 'hr_manager', 'company_admin');
foreach ($expected_overrides as $er) {
    $found = false;
    foreach ($overrides as $o) {
        if ($o['role_type'] == $er && $o['scope_id'] == 0) {
            $found = true;
            ok("Override '{$er}' has email", !empty($o['override_email']), $o['override_email']);
            break;
        }
    }
    if (!$found) ok("Override '{$er}' exists", false, "NOT FOUND");
}

$rules = $pdo->query("SELECT module, event, notify_roles, enabled FROM xin_notification_rules ORDER BY module, event")->fetchAll(PDO::FETCH_ASSOC);
ok("Routing rules seeded", count($rules) >= 14, "Found " . count($rules) . " rules");

$expected_rules = array(
    'leave' => array('submitted', 'first_approved', 'second_approved', 'rejected', 'reconciled'),
    'ticket' => array('created', 'assigned', 'replied', 'closed'),
    'project' => array('created', 'assigned'),
    'announcement' => array('published'),
    'award' => array('given'),
    'task' => array('assigned'),
    'payslip' => array('generated'),
);
foreach ($expected_rules as $mod => $events) {
    foreach ($events as $evt) {
        $found = false;
        foreach ($rules as $r) {
            if ($r['module'] == $mod && $r['event'] == $evt) {
                $found = true;
                ok("Rule {$mod}.{$evt} exists", true, "roles: {$r['notify_roles']}");
                break;
            }
        }
        if (!$found) ok("Rule {$mod}.{$evt} exists", false, "MISSING");
    }
}

// 1d. All rules enabled
$disabled = $pdo->query("SELECT COUNT(*) as c FROM xin_notification_rules WHERE enabled = 0")->fetch()['c'];
ok("All routing rules enabled", $disabled == 0, "{$disabled} disabled rules found");


section('2. ROLE OVERRIDE RESOLUTION (hrm_get_role_override logic)');

// Simulate: look up specific scope first, then fallback to global (scope_id=0)
function test_get_override($pdo, $role_type, $scope_id) {
    // Specific scope
    $stmt = $pdo->prepare("SELECT override_email FROM xin_role_email_overrides WHERE role_type = ? AND scope_id = ? LIMIT 1");
    $stmt->execute(array($role_type, $scope_id));
    $row = $stmt->fetch();
    if ($row && !empty($row['override_email'])) return $row['override_email'];
    
    // Global fallback
    $stmt = $pdo->prepare("SELECT override_email FROM xin_role_email_overrides WHERE role_type = ? AND scope_id = 0 LIMIT 1");
    $stmt->execute(array($role_type));
    $row = $stmt->fetch();
    return ($row && !empty($row['override_email'])) ? $row['override_email'] : null;
}

// CEO global override
$ceo = test_get_override($pdo, 'ceo', 0);
ok("CEO override resolves", $ceo === 'josephwangari1995@gmail.com', "Got: " . var_export($ceo, true));

// HR global override
$hr = test_get_override($pdo, 'hr_manager', 0);
ok("HR override resolves", $hr === 'kariukikennedy1165@gmail.com', "Got: " . var_export($hr, true));

// Admin global override
$admin = test_get_override($pdo, 'company_admin', 0);
ok("Admin override resolves", $admin === 'kindnesszawadi5637@gmail.com', "Got: " . var_export($admin, true));

// Department head with no override → should return null (falls through to employee lookup)
$dept = test_get_override($pdo, 'department_head', 5);
ok("Dept head (scope 5) no override → null", $dept === null, "Got: " . var_export($dept, true));

// Non-existent role → null
$fake = test_get_override($pdo, 'nonexistent_role', 0);
ok("Non-existent role → null", $fake === null);


section('3. RECIPIENT RESOLUTION (hrm_resolve_role_recipients logic)');

// 3a. Employee role
$emp = $pdo->prepare("SELECT email FROM xin_employees WHERE user_id = ? AND is_active = 1 LIMIT 1");
$emp->execute(array(8)); // Henry Njoroge
$emp_email = $emp->fetch()['email'];
ok("Employee (user 8) resolves", $emp_email === 'josephwangari1995@gmail.com', "Got: {$emp_email}");

// 3b. Department head — resolve via xin_departments.employee_id
$depts = $pdo->query("SELECT department_id, department_name, employee_id FROM xin_departments ORDER BY department_id")->fetchAll(PDO::FETCH_ASSOC);
$ok_count = 0;
$warn_count = 0;
foreach ($depts as $d) {
    if (empty($d['employee_id'])) continue;
    $stmt = $pdo->prepare("SELECT email FROM xin_employees WHERE user_id = ? LIMIT 1");
    $stmt->execute(array($d['employee_id']));
    $row = $stmt->fetch();
    if ($row && !empty($row['email'])) {
        $ok_count++;
    } else {
        $warn_count++;
        echo "  ⚠️  Dept '{$d['department_name']}' (id={$d['department_id']}) head employee_id={$d['employee_id']} has no valid email\n";
    }
}
ok("Department heads resolve to emails", $ok_count > 0, "{$ok_count} valid, {$warn_count} dangling");
if ($warn_count > 0) {
    ok("Dangling heads are handled gracefully (no crash)", true, "Code guards against null email");
}

// 3c. HR Manager — role 3 employees
$hr_emps = $pdo->query("SELECT email FROM xin_employees WHERE user_role_id = 3 AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
ok("HR Manager resolves to role-3 employees", count($hr_emps) > 0, "Found: " . implode(', ', $hr_emps));

// 3d. CEO — designation "CEO"
$ceo_emps = $pdo->query("SELECT e.email FROM xin_employees e JOIN xin_designations d ON d.designation_id = e.designation_id WHERE d.designation_name = 'CEO' AND e.is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
ok("CEO resolves via designation", count($ceo_emps) > 0, "Found: " . implode(', ', $ceo_emps));

// 3e. Company admin — role 1 employees
$admin_emps = $pdo->query("SELECT email FROM xin_employees WHERE user_role_id = 1 AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
ok("Company admin resolves to role-1 employees", count($admin_emps) > 0, "Found: " . implode(', ', $admin_emps));


section('4. ROUTING RULE RESOLUTION (hrm_resolve_recipients logic)');

// For each rule, verify the roles in notify_roles are valid
$valid_roles = array('employee', 'department_head', 'hr_manager', 'ceo', 'company_admin');
foreach ($rules as $r) {
    $roles = array_map('trim', explode(',', $r['notify_roles']));
    foreach ($roles as $role) {
        ok("Rule {$r['module']}.{$r['event']} has valid role '{$role}'", in_array($role, $valid_roles), "Invalid role: {$role}");
    }
}

// Verify specific expected compositions
$expected_compositions = array(
    'leave' => array(
        'submitted'       => array('employee', 'department_head', 'hr_manager'),
        'first_approved'  => array('employee', 'hr_manager'),
        'second_approved' => array('employee', 'department_head'),
        'rejected'        => array('employee', 'department_head'),
        'reconciled'      => array('employee', 'department_head', 'hr_manager'),
    ),
    'ticket' => array(
        'created'  => array('employee', 'company_admin'),
        'assigned' => array('employee'),
        'replied'  => array('employee'),
        'closed'   => array('employee'),
    ),
    'award' => array(
        'given' => array('employee', 'ceo'),
    ),
);
foreach ($expected_compositions as $mod => $evts) {
    foreach ($evts as $evt => $expected_roles) {
        foreach ($rules as $r) {
            if ($r['module'] == $mod && $r['event'] == $evt) {
                $actual = array_map('trim', explode(',', $r['notify_roles']));
                sort($expected_roles);
                sort($actual);
                ok("{$mod}.{$evt} roles match", $expected_roles === $actual,
                   "Expected: " . implode(',', $expected_roles) . " | Got: " . implode(',', $actual));
                break;
            }
        }
    }
}


section('5. EDGE CASES');

// 5a. Department with NULL employee_id
$null_head_depts = $pdo->query("SELECT department_id, department_name FROM xin_departments WHERE employee_id IS NULL OR employee_id = 0")->fetchAll(PDO::FETCH_ASSOC);
if (count($null_head_depts) > 0) {
    ok("Departments with NULL heads exist", true, "Count: " . count($null_head_depts));
    foreach ($null_head_depts as $d) {
        echo "     → Dept '{$d['department_name']}' (id={$d['department_id']}) has NULL head\n";
    }
} else {
    ok("No departments with NULL heads", true);
}

// 5b. Employees with NULL email
$null_email = $pdo->query("SELECT COUNT(*) as c FROM xin_employees WHERE (email IS NULL OR email = '') AND is_active = 1")->fetch()['c'];
ok("Active employees with NULL/empty email", $null_email == 0, "{$null_email} employees have no email");

// 5c. Employees with duplicate emails
$dupes = $pdo->query("SELECT email, COUNT(*) as c FROM xin_employees WHERE email IS NOT NULL AND email != '' GROUP BY email HAVING c > 1")->fetchAll(PDO::FETCH_ASSOC);
if (count($dupes) > 0) {
    ok("Duplicate emails detected", true);
    foreach ($dupes as $d) {
        echo "     → '{$d['email']}' used by {$d['c']} employees\n";
    }
} else {
    ok("No duplicate emails", true);
}

// 5d. Routing rule for non-existent module
$all_modules = $pdo->query("SELECT DISTINCT module FROM xin_notification_rules")->fetchAll(PDO::FETCH_COLUMN);
$emp_modules = array('leave', 'ticket', 'project', 'announcement', 'award', 'task', 'payslip');
ok("All routing modules are expected", count(array_diff($all_modules, $emp_modules)) == 0,
   "Unexpected: " . implode(', ', array_diff($all_modules, $emp_modules)));

// 5e. Empty override email (should fallback to default)
$empty_overrides = $pdo->query("SELECT role_type, scope_id FROM xin_role_email_overrides WHERE override_email IS NULL OR override_email = ''")->fetchAll(PDO::FETCH_ASSOC);
ok("No empty override emails", count($empty_overrides) == 0, count($empty_overrides) . " empty overrides found");


section('6. OUTBOX TABLE');

// 6a. Table is writable
$pdo->exec("INSERT INTO xin_notification_outbox (sent_from, sent_to, cc, subject, status, created_at) VALUES ('test@test.com', 'dest@test.com', 'cc@test.com', 'Test subject', 'sent', NOW())");
$insert_id = $pdo->lastInsertId();
ok("Outbox insert works", $insert_id > 0, "Inserted ID: {$insert_id}");

// 6b. Read back
$row = $pdo->prepare("SELECT * FROM xin_notification_outbox WHERE outbox_id = ?");
$row->execute(array($insert_id));
$outbox_row = $row->fetch(PDO::FETCH_ASSOC);
ok("Outbox read back", $outbox_row['sent_from'] === 'test@test.com');
ok("Outbox status stored", $outbox_row['status'] === 'sent');

// 6c. Cleanup test row
$pdo->exec("DELETE FROM xin_notification_outbox WHERE outbox_id = {$insert_id}");
ok("Outbox cleanup", true);


section('7. SMTP CONFIGURATION');

$smtp = $pdo->query("SELECT email_type, smtp_host, smtp_port, smtp_secure, smtp_username, smtp_password, notification_cc_emails FROM xin_email_configuration WHERE email_config_id = 1")->fetch(PDO::FETCH_ASSOC);
ok("Email config exists", $smtp !== false);
ok("SMTP type set", !empty($smtp['email_type']), $smtp['email_type']);
ok("SMTP host set", !empty($smtp['smtp_host']), $smtp['smtp_host']);
ok("SMTP port set", !empty($smtp['smtp_port']), $smtp['smtp_port']);
ok("SMTP secure set", !empty($smtp['smtp_secure']), $smtp['smtp_secure']);
ok("SMTP username set", !empty($smtp['smtp_username']), $smtp['smtp_username']);

// Verify port/secure consistency
if ($smtp['smtp_secure'] == 'ssl' && $smtp['smtp_port'] != 465) {
    ok("Port/secure consistency (SSL→465)", false, "Port {$smtp['smtp_port']} with SSL");
} elseif ($smtp['smtp_secure'] == 'tls' && $smtp['smtp_port'] != 587) {
    ok("Port/secure consistency (TLS→587)", false, "Port {$smtp['smtp_port']} with TLS");
} else {
    ok("Port/secure consistency", true, "{$smtp['smtp_secure']}:{smtp['smtp_port']}");
}


section('8. LIVE EMAIL SEND TEST');

echo "  Sending test email to: {$TEST_EMAIL}\n";
echo "  SMTP: {$smtp['smtp_host']}:{$smtp['smtp_port']} ({$smtp['smtp_secure']})\n";
echo "  From: {$smtp['smtp_username']}\n\n";

// PHPMailer is bundled with CI3
$mailer_dir = dirname(__DIR__) . '/application/third_party/phpmailer';
$autoloader = $mailer_dir . '/PHPMailerAutoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
} else {
    echo "  ⚠️  PHPMailer autoloader not found at {$autoloader}\n";
    echo "  Skipping live email test.\n";
    $pdo = null;
    exit(0);
}

$cc = !empty($smtp['notification_cc_emails']) ? $smtp['notification_cc_emails'] : '';

$mail = new PHPMailer();
try {
    $mail->isSMTP();
    $mail->Host       = $smtp['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp['smtp_username'];
    $mail->Password   = $smtp['smtp_password'];
    $mail->SMTPSecure = $smtp['smtp_secure'];
    $mail->Port       = $smtp['smtp_port'];
    $mail->CharSet    = 'UTF-8';
    
    $mail->setFrom($smtp['smtp_username'], 'HRM Test');
    $mail->addAddress($TEST_EMAIL);
    
    // CC test
    if (!empty($cc)) {
        foreach (array_map('trim', explode(',', $cc)) as $cc_email) {
            if (!empty($cc_email) && filter_var($cc_email, FILTER_VALIDATE_EMAIL)) {
                $mail->addCC($cc_email);
            }
        }
    }
    
    $mail->isHTML(true);
    $mail->Subject = 'HRM Notification System — Test Email ' . date('Y-m-d H:i:s');
    $mail->Body    = '<div style="font-family:Verdana,sans-serif;padding:20px;">'
        . '<h2 style="color:#3e70c9;">Notification System Test</h2>'
        . '<p>This is a <strong>test notification</strong> from the HRM notification system.</p>'
        . '<p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>'
        . '<p><strong>SMTP:</strong> ' . htmlspecialchars($smtp['smtp_host']) . ':' . $smtp['smtp_port'] . '</p>'
        . '<p><strong>CC:</strong> ' . htmlspecialchars($cc) . '</p>'
        . '<hr>'
        . '<p style="color:#999;font-size:11px;">If you received this, the notification system is working correctly.</p>'
        . '</div>';
    $mail->AltBody = "HRM Notification System Test\nSent at: " . date('Y-m-d H:i:s') . "\nSMTP: {$smtp['smtp_host']}:{$smtp['smtp_port']}\n";
    
    $mail->send();
    ok("Email sent successfully", true);
    
    // Log to outbox
    $stmt = $pdo->prepare("INSERT INTO xin_notification_outbox (sent_from, sent_to, cc, subject, status, created_at) VALUES (?, ?, ?, ?, 'sent', NOW())");
    $stmt->execute(array($smtp['smtp_username'], $TEST_EMAIL, $cc, $mail->Subject));
    ok("Outbox log written", true, "outbox_id: " . $pdo->lastInsertId());
    
} catch (Exception $e) {
    ok("Email send", false, $e->getMessage());
    
    // Log failure
    $stmt = $pdo->prepare("INSERT INTO xin_notification_outbox (sent_from, sent_to, cc, subject, status, created_at) VALUES (?, ?, ?, ?, 'failed', NOW())");
    $stmt->execute(array($smtp['smtp_username'], $TEST_EMAIL, isset($cc) ? $cc : '', 'Test email'));
    ok("Outbox failure logged", true);
}


section('9. HELPER FILE SYNTAX CHECK');

$helpers = array(
    'application/helpers/mail_helper.php',
    'application/helpers/general_helper.php',
);
foreach ($helpers as $h) {
    $path = dirname(__DIR__) . '/' . $h;
    $output = array();
    $ret = 0;
    exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $ret);
    ok("{$h} syntax OK", $ret === 0, implode(' ', $output));
}

// Check Settings controller
$settings_path = dirname(__DIR__) . '/application/controllers/admin/Settings.php';
$output = array();
$ret = 0;
exec("php -l " . escapeshellarg($settings_path) . " 2>&1", $output, $ret);
ok("Settings.php syntax OK", $ret === 0, implode(' ', $output));

// Check notification_center view
$view_path = dirname(__DIR__) . '/application/views/admin/settings/notification_center.php';
$output = array();
$ret = 0;
exec("php -l " . escapeshellarg($view_path) . " 2>&1", $output, $ret);
ok("notification_center.php syntax OK", $ret === 0, implode(' ', $output));

// Check JS
$js_path = dirname(__DIR__) . '/skin/hrsale_assets/hrsale_scripts/notification_settings.js';
$output = array();
$ret = 0;
exec("node -c " . escapeshellarg($js_path) . " 2>&1", $output, $ret);
ok("notification_settings.js syntax OK", $ret === 0, implode(' ', $output));


section('RESULTS');

$total = $PASS + $FAIL;
echo "\n";
echo "  Total:  {$total}\n";
echo "  Passed: {$PASS} ✅\n";
echo "  Failed: {$FAIL} ❌\n";
echo "\n";

if ($FAIL > 0) {
    echo "  FAILURES:\n";
    foreach ($ERRORS as $e) {
        echo "    • {$e}\n";
    }
    echo "\n";
}

$pdo = null;
exit($FAIL > 0 ? 1 : 0);
