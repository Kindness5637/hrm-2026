<?php
/**
 * Test hrsale_mail() — the actual CI3 path the app uses.
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
$pwd  = get_smtp('smtp_password');

echo "=== CI3 hrsale_mail() Test ===\n\n";
echo "From: {$from}\n";
echo "Password decrypted: " . (strlen($pwd) > 0 ? 'YES' : 'NO') . "\n";
echo "SMTP Host: " . get_smtp('smtp_host') . "\n";
echo "SMTP Port: " . get_smtp('smtp_port') . "\n";
echo "SMTP Secure: " . get_smtp_secure() . "\n";
echo "CC: " . get_notification_cc() . "\n\n";

$to = 'kindnesszawadi5637@gmail.com';
$subject = 'CI3 hrsale_mail() Test — ' . date('Y-m-d H:i:s');
$body = '<div style="font-family:Verdana,sans-serif;padding:20px;background:#f6f6f6;">'
    . '<h2 style="color:#3e70c9;">CI3 Email Library Test</h2>'
    . '<p>This email was sent using the <code>hrsale_mail()</code> function — the same path the HRM app uses for all notifications.</p>'
    . '<p><strong>Encrypted password:</strong> Decrypted and used successfully.</p>'
    . '<p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>'
    . '<hr>'
    . '<p style="color:#999;font-size:11px;">If you see this, the full notification pipeline works end-to-end.</p>'
    . '</div>';

echo "Sending to: {$to}\n";
$result = hrsale_mail($from, 'HRM System', $to, $subject, $body);
echo "Result: " . ($result ? 'SENT ✅' : 'FAILED ❌') . "\n\n";

// Check outbox
$outbox = $CI->db->select('*')->from('xin_notification_outbox')->order_by('outbox_id', 'DESC')->limit(5)->get()->result();
echo "=== Outbox (last 5) ===\n";
foreach ($outbox as $row) {
    $icon = $row->status === 'sent' ? '✅' : '❌';
    echo "  {$icon} {$row->sent_to} | {$row->subject} | {$row->status} | {$row->created_at}\n";
}
