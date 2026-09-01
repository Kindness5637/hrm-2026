<?php

//Process String
if( !function_exists('hrsale_mail') ){

function hrsale_mail($from,$from_name,$to,$subject,$body){
	  $CI=& get_instance();
	  $type = email_type();
	  $cc   = get_notification_cc();
	  $sent = false;

	  if($type=="codeigniter"){
		$CI->load->library('email');
		$CI->email->set_mailtype("html");
		$CI->email->from($from,$from_name);
		$CI->email->to($to);
		if($cc){ $CI->email->cc($cc); }

		$CI->email->subject($subject);
		$CI->email->message($body);

		$sent = (bool)$CI->email->send();
	  } else if($type=="smtp"){
		// Use PHPMailer for SMTP — supports SMTPOptions for SSL cert flexibility.
		$mailer_dir = APPPATH.'third_party/phpmailer/';
		if(file_exists($mailer_dir.'PHPMailerAutoload.php')){
			require_once $mailer_dir.'PHPMailerAutoload.php';
		}
		if(class_exists('PHPMailer')){
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->Host       = get_smtp("smtp_host");
			$mail->SMTPAuth   = true;
			$mail->Username   = get_smtp("smtp_username");
			$mail->Password   = get_smtp("smtp_password");
			$mail->SMTPSecure = get_smtp_secure();
			$mail->Port       = get_smtp("smtp_port");
			$mail->CharSet    = 'UTF-8';
			$mail->SMTPAutoTLS = false;
			$mail->Timeout    = 30;
			// Relax SSL verification — server cert may not match hostname.
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				)
			);
			$mail->setFrom($from, $from_name);
			$mail->addAddress($to);
			if($cc){
				foreach(array_map('trim', explode(',', $cc)) as $cc_email){
					if(!empty($cc_email) && filter_var($cc_email, FILTER_VALIDATE_EMAIL)){
						$mail->addCC($cc_email);
					}
				}
			}
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = strip_tags($body);
			$sent = $mail->send();
		} else {
			// Fallback to CI3 email library if PHPMailer unavailable.
			$CI->load->library('email');
			$CI->email->set_mailtype("html");
			$config['protocol']    = 'smtp';
			$config['smtp_crypto'] = get_smtp_secure();
			$config['smtp_host']   = get_smtp("smtp_host");
			$config['smtp_port']   = get_smtp("smtp_port");
			$config['smtp_timeout']= '60';
			$config['smtp_user']   = get_smtp("smtp_username");
			$config['smtp_pass']   = get_smtp("smtp_password");
			$config['charset']     = 'utf-8';
			$config['newline']     = "\r\n";
			$config['mailtype']    = "html";
			$config['validation']  = TRUE;
			$CI->email->initialize($config);
			$CI->email->from($from,$from_name);
			$CI->email->to($to);
			if($cc){ $CI->email->cc($cc); }
			$CI->email->subject($subject);
			$CI->email->message($body);
			$sent = (bool)$CI->email->send(FALSE);
		}
	  } else if($type=="phpmail"){

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		// More headers
		$headers .= 'From: ' .$from_name.' <'.$from.'>' . "\r\n";
		if($cc){ $headers .= 'Cc: '.$cc. "\r\n"; }

		$sent = (bool)mail($to,$subject,$body,$headers);
	  }

	  // Outbox echo: log every send attempt so delivery is observable.
	  log_notification_mail($from, $to, $cc, $subject, $sent);

	  return $sent;
	}
}

// company info
if( !function_exists('hrsale_company_name') ){

 function hrsale_company_name(){
  $CI=& get_instance();
  $query =  $CI->db->query("SELECT company_name FROM xin_company_info")->row()->company_name;
  return $query;
 }
}
if( !function_exists('hrsale_company_email') ){

 function hrsale_company_email(){
  $CI=& get_instance();
  $query =  $CI->db->query("SELECT email FROM xin_company_info")->row()->email;
  return $query;
 }
}
//Process String
if( !function_exists('email_type') ){

 function email_type(){
  $CI=& get_instance();
  $query =  $CI->db->query("SELECT email_type FROM xin_email_configuration")->row()->email_type;
  return $query;
 }

}

//Process String
if( !function_exists('get_smtp_secure') ){

 function get_smtp_secure(){
  $CI=& get_instance();
  $query = $CI->db->query("SELECT smtp_secure FROM xin_email_configuration")->row()->smtp_secure;
  return $query;
 }

}

//Process String
if( !function_exists('get_smtp') ){

 function get_smtp($name){
  $CI=& get_instance();
  $query = $CI->db->query("SELECT $name FROM xin_email_configuration")->row()->$name;
  // Decrypt smtp_password on read
  if($name === 'smtp_password' && !empty($query)){
   $query = hrm_decrypt($query);
  }
  return $query;
 }
}

// SMTP password encryption — uses AES-256-CBC with a key derived from the app's encryption_key.
if( !function_exists('hrm_encrypt') ){
 function hrm_encrypt($plain){
  if(empty($plain)) return '';
  $key = hrm_get_encryption_key();
  $iv = openssl_random_pseudo_bytes(16);
  $enc = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
  return base64_encode($iv . $enc);
 }
}

if( !function_exists('hrm_decrypt') ){
 function hrm_decrypt($cipher){
  if(empty($cipher)) return '';
  // Detect if it's still plaintext (no base64, or base64 decode fails)
  $decoded = @base64_decode($cipher, true);
  if($decoded === false || strlen($decoded) < 17){
   return $cipher; // Not encrypted, return as-is
  }
  $key = hrm_get_encryption_key();
  $iv = substr($decoded, 0, 16);
  $enc = substr($decoded, 16);
  $plain = openssl_decrypt($enc, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
  return ($plain !== false) ? $plain : $cipher;
 }
}

if( !function_exists('hrm_get_encryption_key') ){
 function hrm_get_encryption_key(){
  // Use CI's encryption_key if set, otherwise fall back to a salt
  $CI =& get_instance();
  $ci_key = config_item('encryption_key');
  if(!empty($ci_key)){
   return hash('sha256', $ci_key, true);
  }
  return hash('sha256', 'hrsale_notification_salt_2024', true);
 }
}

// Check if a value looks like encrypted data (base64 of 32+ bytes)
if( !function_exists('hrm_is_encrypted') ){
 function hrm_is_encrypted($val){
  if(empty($val)) return false;
  $decoded = @base64_decode($val, true);
  return ($decoded !== false && strlen($decoded) >= 17);
 }
}

// Global observer CC list (notification_cc_emails) — appended to every send.
if( !function_exists('get_notification_cc') ){

 function get_notification_cc(){
  $CI=& get_instance();
  $q = $CI->db->select('notification_cc_emails')->from('xin_email_configuration')->limit(1)->get()->row();
  return ($q && !empty($q->notification_cc_emails)) ? $q->notification_cc_emails : '';
 }
}

// Outbox echo: record every send attempt so delivery is observable / retryable.
if( !function_exists('log_notification_mail') ){

 function log_notification_mail($from, $to, $cc = '', $subject = '', $sent = false){
  $CI=& get_instance();
  if(!$CI->db->table_exists('xin_notification_outbox')){ return; }
  $CI->db->insert('xin_notification_outbox', array(
   'sent_from' => $from,
   'sent_to'   => $to,
   'cc'        => $cc,
   'subject'   => substr($subject, 0, 255),
   'status'    => $sent ? 'sent' : 'failed',
   'created_at'=> date('Y-m-d H:i:s'),
  ));
 }
}