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
		$Return = array('result'=>'', 'error'=>'');

		$email = $this->input->post('email');
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			$Return['error'] = 'Please enter a valid email address.';
			$this->output($Return);
			return;
		}

		$query = $this->Xin_model->read_user_info_byemail($email);
		if($query->num_rows() == 0){
			$Return['error'] = 'No account found with that email address.';
			$this->output($Return);
			return;
		}

		$user_info = $query->row();
		$otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
		$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

		$this->db->where('user_id', $user_info->user_id);
		$this->db->update('xin_employees', array(
			'reset_token' => $otp,
			'reset_expires' => $expires
		));

		$cinfo = $this->Xin_model->read_company_setting_info(1);
		$full_name = $user_info->first_name.' '.$user_info->last_name;

		$subject = 'Your Password Reset OTP - '.$cinfo[0]->company_name;
		$body = '
		<div style="background:#f6f6f6;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:14px;margin:0;padding:20px;">
			<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:30px;">
				<h2 style="color:#333;margin-top:0;">Password Reset OTP</h2>
				<p>Hello '.$full_name.',</p>
				<p>Your One-Time Password (OTP) for password reset is:</p>
				<p style="text-align:center;margin:30px 0;">
					<span style="background:#3c8dbc;color:#fff;padding:15px 30px;font-size:32px;font-weight:bold;letter-spacing:8px;border-radius:8px;display:inline-block;">'.$otp.'</span>
				</p>
				<p style="color:#999;font-size:12px;">This OTP expires in 10 minutes. If you did not request this, please ignore this email.</p>
			</div>
		</div>';

		hrsale_mail($cinfo[0]->email, $cinfo[0]->company_name, $email, $subject, $body);

		$Return['result'] = 'OTP has been sent to your email.';
		$this->output($Return);
	}

	public function verify_otp() {
		$data['title'] = 'Verify OTP';
		$data['email'] = $this->input->get('email');
		$this->load->view('admin/auth/verify_otp', $data);
	}

	public function verify_otp_check() {
		$Return = array('result'=>'', 'error'=>'');
		$email = $this->input->post('email');
		$otp = $this->input->post('otp');

		if(empty($otp) || strlen($otp) != 6){
			$Return['error'] = 'Please enter a valid 6-digit OTP.';
			$this->output($Return);
			return;
		}

		$query = $this->db->select('*')->from('xin_employees')
			->where('email', $email)
			->where('reset_token', $otp)
			->where('reset_expires >', date('Y-m-d H:i:s'))
			->get();

		if($query->num_rows() == 0){
			$Return['error'] = 'Invalid or expired OTP.';
			$this->output($Return);
			return;
		}

		$user = $query->row();
		$this->db->where('user_id', $user->user_id);
		$this->db->update('xin_employees', array(
			'reset_expires' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
		));

		$Return['result'] = 'OTP verified successfully.';
		$this->output($Return);
	}

	public function reset_password() {
		$email = $this->input->get('email');
		$data['email'] = $email;
		$data['valid'] = false;

		if(!empty($email)){
			$query = $this->db->select('*')->from('xin_employees')
				->where('email', $email)
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
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$password_confirm = $this->input->post('password_confirm');

		if(empty($email)){
			$Return['error'] = 'Invalid request.';
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

		$query = $this->db->select('*')->from('xin_employees')
			->where('email', $email)
			->where('reset_expires >', date('Y-m-d H:i:s'))
			->get();

		if($query->num_rows() == 0){
			$Return['error'] = 'Session expired. Please start over.';
			$this->output($Return);
			return;
		}

		$user = $query->row();
		$options = array('cost' => 12);
		$password_hash = password_hash($password, PASSWORD_BCRYPT, $options);

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