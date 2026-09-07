<?php
defined('BASEPATH') OR exit('No direct script access access');

class Smtp_debug extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->helper('mail');
		$this->load->helper('general');
	}

	public function index() {
		echo "<h2>SMTP Debug</h2>";

		$host = 'smtp.gmail.com';
		$user = 'aleslaikipia@gmail.com';
		$pass = 'gadlduwkjumbzkpw';
		$port = 587;
		$secure = 'tls';

		echo "<p>Host: $host</p>";
		echo "<p>User: $user</p>";
		echo "<p>Port: $port</p>";
		echo "<p>Secure: $secure</p>";

		require_once APPPATH.'third_party/phpmailer/PHPMailerAutoload.php';

		$mail = new PHPMailer(true);
		$mail->SMTPDebug = 2;
		$mail->Debugoutput = function($str, $level) {
			echo "<p style='font-size:11px;color:#666;margin:2px 0'>$str</p>";
		};

		try {
			$mail->isSMTP();
			$mail->Host = $host;
			$mail->SMTPAuth = true;
			$mail->Username = $user;
			$mail->Password = $pass;
			$mail->SMTPSecure = $secure;
			$mail->Port = $port;
			$mail->CharSet = 'UTF-8';
			$mail->SMTPAutoTLS = false;
			$mail->Timeout = 15;
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true,
				)
			);
			$mail->setFrom($user, 'Stalis HRM');
			$mail->addAddress($user);
			$mail->isHTML(true);
			$mail->Subject = 'SMTP Debug Test - ' . date('Y-m-d H:i:s');
			$mail->Body = '<h3>Debug Test</h3><p>Sent from InfinityFree via Gmail SMTP at ' . date('Y-m-d H:i:s') . '</p>';
			$mail->send();
			echo "<p style='color:green;font-size:18px'>EMAIL SENT!</p>";
		} catch (Exception $e) {
			echo "<p style='color:red;font-size:18px'>FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
		}
	}
}
