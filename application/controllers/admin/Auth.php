<?php
 /**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the HRSALE License
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.stalis.co.ke/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to s@gmail.com so we can send you a copy immediately.
 *
 * @author   HRSALE
 * @author-email  s@gmail.com
 * @copyright  Copyright © stalis.co.ke. All Rights Reserved
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller
{
	
	public function __construct()
     {
          parent::__construct();
			//load the model
			/*	$this->load->library('session');
			$this->load->helper('form');
			$this->load->helper('url');
			$this->load->helper('html');
			$this->load->database();
			$this->load->library('form_validation');*/
		
			$this->load->model('Login_model');
			$this->load->model('Employees_model');
			$this->load->model('Users_model');
			$this->load->library('email');
			$this->load->model("Xin_model");
			$this->load->model("Designation_model");
			$this->load->model("Department_model");
			$this->load->model("Location_model");
     }
	 
	 /*Function to set JSON output*/
	public function output($Return=array()){
		/*Set response header*/
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		/*Final JSON response*/
		exit(json_encode($Return));
	}
	 
	public function login() {
	
		$this->form_validation->set_rules('iusername', 'Username', 'trim|required|xss_clean');
		$this->form_validation->set_rules('ipassword', 'Password', 'trim|required|xss_clean');
		//$Return = array('result'=>'', 'error'=>'', 'csrf_hash'=>'');
		
		/*if ($this->form_validation->run() == FALSE)
		{
				//$this->load->view('myform');
		}*/
		$username = $this->input->post('iusername');
		$password = $this->input->post('ipassword');
		/* Define return | here result is used to return user data and error for error message */
		$Return = array('result'=>'', 'error'=>'', 'csrf_hash'=>'');
		
		$Return['csrf_hash'] = $this->security->get_csrf_hash();
		/* Server side PHP input validation */
		if($username==='') {
			$Return['error'] = $this->lang->line('xin_employee_error_username');
		} elseif($password===''){
			$Return['error'] = $this->lang->line('xin_employee_error_password');
		}
		if($Return['error']!=''){
			$this->output($Return);
		}
		
		$data = array(
			'username' => $username,
			'password' => $password
			);
		$result = $this->Login_model->login($data);	
		
		if ($result == TRUE) {
			
				$result = $this->Login_model->read_user_information($username);
				$session_data = array(
				'user_id' => $result[0]->user_id,
				'username' => $result[0]->username,
				'email' => $result[0]->email,
				);
				// Add user data in session
				$this->session->set_userdata('username', $session_data);
				$this->session->set_userdata('user_id', $session_data);
				$Return['result'] = $this->lang->line('xin_success_logged_in');
				
				// update last login info
				$ipaddress = $this->input->ip_address();
				  
				 $last_data = array(
					'last_login_date' => date('d-m-Y H:i:s'),
					'last_login_ip' => $ipaddress,
					'is_logged_in' => '1'
				); 
				
				$id = $result[0]->user_id; // user id
				  
				$this->Xin_model->login_update_record($last_data, $id);
				$Return['csrf_hash'] = $this->security->get_csrf_hash();
				$this->session->set_flashdata('expire_official_document', 'expire_official_document');
				$this->output($Return);
				
			} else {
				$Return['error'] = $this->lang->line('xin_error_invalid_credentials');
				/*Return*/
				$Return['csrf_hash'] = $this->security->get_csrf_hash();
				$this->output($Return);
			}
	}
	
	// forgot password.	
	public function forgot_password() {
		$data['title'] = $this->lang->line('xin_forgot_password_link');
		$this->load->view('admin/auth/forgot_password', $data);
	}
	
	// unlock user.	
	public function lock() {
		
		//$session_id = $this->session->userdata('user_id');
		$data['title'] = $this->lang->line('xin_lock_user');

		$session = $this->session->userdata('username');
		$this->session->unset_userdata('username');
		$Return['result'] = 'Locked User.';
		$this->load->view('admin/auth/user_lock', $data);
	}
	
	//unlock user.
	public function unlock() {
	
		$this->form_validation->set_rules('ipassword', 'Password', 'trim|required|xss_clean');
		$Return = array('result'=>'', 'error'=>'', 'csrf_hash'=>'');
		
		$Return['csrf_hash'] = $this->security->get_csrf_hash();
		
		$password = $this->input->post('ipassword');
		$session_id = $this->session->userdata('user_id');
		$iresult = $this->Login_model->read_user_info_session_id($session_id['user_id']);
		
		/* Server side PHP input validation */
		if($password===''){
			$Return['error'] = $this->lang->line('xin_employee_error_password');
		}
		if($Return['error']!=''){
			$this->output($Return);
		}
		
		$username = $iresult[0]->username;
		$data = array(
			'username' => $username,
			'password' => $password
			);
		$result = $this->Login_model->login($data);	
		
		if ($result == TRUE) {
			
				$result = $this->Login_model->read_user_information($username);
				$session_data = array(
				'user_id' => $result[0]->user_id,
				'username' => $result[0]->username,
				'email' => $result[0]->email,
				);
				// Add user data in session
				$this->session->set_userdata('username', $session_data);
				$this->session->set_userdata('user_id', $session_data);
				$Return['result'] = $this->lang->line('xin_success_logged_in');
				
				// update last login info
				$ipaddress = $this->input->ip_address();
				  
				$last_data = array(
					'last_login_date' => date('d-m-Y H:i:s'),
					'last_login_ip' => $ipaddress,
					'is_logged_in' => '1'
				); 
				
				$id = $result[0]->user_id; // user id
				  
				$this->Xin_model->login_update_record($last_data, $id);
				$this->output($Return);
				
			} else {
				$Return['error'] = $this->lang->line('xin_error_invalid_credentials');
				/*Return*/
				$this->output($Return);
			}
		}
	
	public static function AlphaNumeric($length)
      {
          $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
          $clen   = strlen( $chars )-1;
          $id  = '';

          for ($i = 0; $i < $length; $i++) {
                  $id .= $chars[mt_rand(0,$clen)];
          }
          return ($id);
      }
	  
	public function forgot_password_send() {
		$Return = array('result'=>'', 'error'=>'', 'csrf_hash'=>'');

		$email = $this->input->post('email');
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			$Return['error'] = 'Please enter a valid email address.';
			$this->output($Return);
			return;
		}

		// Find user by email
		$query = $this->Xin_model->read_user_info_byemail($email);
		if($query->num_rows() == 0){
			$Return['error'] = 'No account found with that email address.';
			$this->output($Return);
			return;
		}

		$user_info = $query->row();
		$token = bin2hex(random_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

		// Store token in xin_users
		$this->db->where('user_id', $user_info->user_id);
		$this->db->update('xin_employees', array(
			'reset_token' => $token,
			'reset_expires' => $expires
		));

		// Send reset email
		$cinfo = $this->Xin_model->read_company_setting_info(1);
		$reset_url = site_url('admin/auth/reset_password?token='.$token);
		$full_name = $user_info->first_name.' '.$user_info->last_name;

		$subject = 'Password Reset - '.$cinfo[0]->company_name;
		$body = '
		<div style="background:#f6f6f6;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:14px;margin:0;padding:20px;">
			<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:30px;">
				<h2 style="color:#333;margin-top:0;">Password Reset Request</h2>
				<p>Hello '.$full_name.',</p>
				<p>You requested a password reset for your account. Click the button below to set a new password:</p>
				<p style="text-align:center;margin:30px 0;">
					<a href="'.$reset_url.'" style="background-color:#3c8dbc;color:#fff;padding:12px 30px;text-decoration:none;border-radius:4px;font-weight:bold;">Reset Password</a>
				</p>
				<p style="color:#999;font-size:12px;">This link will expire in 1 hour. If you did not request this, please ignore this email.</p>
				<p style="color:#999;font-size:12px;">If the button doesn\'t work, copy and paste this URL into your browser:<br>'.$reset_url.'</p>
			</div>
		</div>';

		hrsale_mail($cinfo[0]->email, $cinfo[0]->company_name, $email, $subject, $body);

		$Return['result'] = 'Password reset link has been sent to your email.';
		$this->output($Return);
	}
	
	public function reset_password() {
		$token = $this->input->get('token');
		$data['token'] = $token;
		$data['valid'] = false;

		if(!empty($token)){
			// Check if token is valid and not expired
			$query = $this->db->select('*')->from('xin_employees')
				->where('reset_token', $token)
				->where('reset_expires >', date('Y-m-d H:i:s'))
				->get();
			if($query->num_rows() > 0){
				$data['valid'] = true;
			}
		}

		$data['title'] = 'Reset Password';
		$this->load->view('admin/auth/reset_password', $data);
	}

	public function reset_password_save() {
		$Return = array('result'=>'', 'error'=>'');
		$token = $this->input->post('token');
		$password = $this->input->post('password');
		$password_confirm = $this->input->post('password_confirm');

		if(empty($token)){
			$Return['error'] = 'Invalid reset link.';
			$this->output($Return);
			return;
		}
		if(empty($password) || strlen($password) < 6){
			$Return['error'] = 'Password must be at least 6 characters.';
			$this->output($Return);
			return;
		}
		if($password !== $password_confirm){
			$Return['error'] = 'Passwords do not match.';
			$this->output($Return);
			return;
		}

		// Verify token
		$query = $this->db->select('*')->from('xin_employees')
			->where('reset_token', $token)
			->where('reset_expires >', date('Y-m-d H:i:s'))
			->get();

		if($query->num_rows() == 0){
			$Return['error'] = 'Reset link is invalid or has expired.';
			$this->output($Return);
			return;
		}

		$user = $query->row();
		$options = array('cost' => 12);
		$password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

		// Update password and clear token
		$this->db->where('user_id', $user->user_id);
		$this->db->update('xin_employees', array(
			'password' => $password_hash,
			'reset_token' => NULL,
			'reset_expires' => NULL
		));

		$Return['result'] = 'Password has been reset successfully. You can now login.';
		$this->output($Return);
	}
} 
?>