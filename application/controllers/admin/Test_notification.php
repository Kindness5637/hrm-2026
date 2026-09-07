<?php
defined('BASEPATH') OR exit('No direct script access access');

class Test_notification extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->helper('mail');
		$this->load->helper('general');
	}

	public function index() {
		echo "<h2>Notification Test</h2>";

		$type = email_type();
		echo "<h3>Email Type: <b>$type</b></h3>";

		$mailer_dir = APPPATH.'third_party/phpmailer/';
		$autoload_exists = file_exists($mailer_dir.'PHPMailerAutoload.php');
		echo "<p>PHPMailerAutoload exists: " . ($autoload_exists ? 'YES' : 'NO') . "</p>";
		if($autoload_exists) {
			require_once $mailer_dir.'PHPMailerAutoload.php';
		}
		$pm_exists = class_exists('PHPMailer');
		echo "<p>PHPMailer class: " . ($pm_exists ? 'YES' : 'NO') . "</p>";

		$host = get_smtp('smtp_host');
		$user = get_smtp('smtp_username');
		$pass = get_smtp('smtp_password');
		$port = get_smtp('smtp_port');
		$secure = get_smtp_secure();
		echo "<p>SMTP Host: $host</p>";
		echo "<p>SMTP User: $user</p>";
		echo "<p>SMTP Port: $port</p>";
		echo "<p>SMTP Secure: " . var_export($secure, true) . "</p>";
		echo "<p>Password (first 4): " . substr($pass, 0, 4) . "***</p>";
		echo "<p>Password length: " . strlen($pass) . "</p>";

		$from = $user;
		$from_name = hrsale_company_name();
		$to = 'kindnesszawadi5637@gmail.com';
		$subject = 'Welcome to Stalis HRM';
		$body = '<div style="font-family:Verdana,Arial,sans-serif;padding:20px;">'
			. '<h2 style="color:#2d385e;">Welcome to Stalis HRM</h2>'
			. '<p>Dear Team,</p>'
			. '<p>We are pleased to welcome you to the <strong>Stalis HRM System</strong>. This platform is designed to streamline our human resource management processes, including employee records, leave management, attendance tracking, and notifications.</p>'
			. '<p>If you have any questions or need assistance, please do not hesitate to reach out to the HR department.</p>'
			. '<p>Best regards,<br><strong>' . htmlspecialchars($from_name) . '</strong></p>'
			. '<p style="color:#999;font-size:11px;">Sent: ' . date('Y-m-d H:i:s') . '</p></div>';

		if ($pm_exists) {
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->Host = $host;
			$mail->SMTPAuth = true;
			$mail->Username = $user;
			$mail->Password = $pass;
			$mail->SMTPSecure = $secure;
			$mail->Port = $port;
			$mail->CharSet = 'UTF-8';
			$mail->SMTPAutoTLS = false;
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true,
				)
			);
			$mail->setFrom($from, $from_name);
			$mail->addAddress($to);
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $body;
			try {
				$sent = $mail->send();
				echo "<p style='color:green;font-size:20px'>EMAIL SENT</p>";
			} catch (Exception $e) {
				echo "<p style='color:red;font-size:20px'>FAILED: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
			}
		} else {
			$sent = hrsale_mail($from, $from_name, $to, $subject, $body);
			echo $sent ? "<p style='color:green'>SENT via CI fallback</p>" : "<p style='color:red'>FAILED via CI fallback</p>";
		}
	}
}
