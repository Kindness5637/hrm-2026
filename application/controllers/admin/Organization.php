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

class Organization extends MY_Controller
{

   /*Function to set JSON output*/
	public function output($Return=array()){
		/*Set response header*/
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		/*Final JSON response*/
		exit(json_encode($Return));
	}
	
	public function __construct()
     {
          parent::__construct();
          //load the login model
          $this->load->model('Company_model');
		  $this->load->model("Department_model");
		  $this->load->model("Designation_model");
		  $this->load->model('Xin_model');
     }
	 
	//chart page //
	public function chart() {
	
		$session = $this->session->userdata('username');
		if(empty($session)){ 
			redirect('admin/');
		}
		$system = $this->Xin_model->read_setting_info(1);
		if($system[0]->module_orgchart!='true'){
			redirect('admin/dashboard');
		}
		$data['title'] = $this->lang->line('xin_org_chart_title').' | '.$this->Xin_model->site_title();
		$data['breadcrumbs'] = $this->lang->line('xin_org_chart_title');
		$data['path_url'] = 'organization_chart';
		$role_resources_ids = $this->Xin_model->user_role_resource();
		if(in_array('96',$role_resources_ids)) {

			// Build org chart data
			$data['orgchart_data'] = $this->_build_orgchart_data();

			$data['subview'] = $this->load->view("admin/orgchart/orgchart", $data, TRUE);
			$this->load->view('admin/layout/layout_main', $data); //page load
		} else {
			redirect('admin/dashboard');
		}
	}

	private function _build_orgchart_data() {
		// Get departments
		$departments = $this->db->where('status', 1)->get('xin_departments')->result();
		$dept_map = array();
		foreach ($departments as $d) {
			$dept_map[$d->department_id] = $d;
		}

		// Get designations
		$designations = $this->db->where('status', 1)->get('xin_designations')->result();
		$desig_map = array();
		foreach ($designations as $ds) {
			$desig_map[$ds->designation_id] = $ds;
		}

		// Get active employees with profile pics
		$employees = $this->db->where('is_active', 1)
			->select('user_id, first_name, last_name, department_id, designation_id, profile_picture')
			->get('xin_employees')->result();
		$emp_map = array();
		foreach ($employees as $e) {
			$emp_map[$e->user_id] = $e;
		}

		// Build tree: CEO at top, then department heads, then staff
		$ceo = null;
		$dept_heads = array();
		$dept_staff = array();

		foreach ($employees as $emp) {
			$desig = isset($desig_map[$emp->designation_id]) ? $desig_map[$emp->designation_id] : null;
			$dept = isset($dept_map[$emp->department_id]) ? $dept_map[$emp->department_id] : null;
			if (!$desig || !$dept) continue;

			$name = trim($emp->first_name . ' ' . $emp->last_name);
			$pic = !empty($emp->profile_picture) ? base_url() . 'uploads/profile/' . $emp->profile_picture : base_url() . 'uploads/profile/default.jpg';
			$node = array(
				'name'    => $name,
				'id'      => 'emp_' . $emp->user_id,
				'title'   => $desig->designation_name,
				'dept'    => $dept->department_name,
				'photo'   => $pic,
				'children'=> array(),
			);

			// CEO goes to top
			if (stripos($desig->designation_name, 'CEO') !== false || stripos($desig->designation_name, 'Managing Director') !== false) {
				$ceo = $node;
				continue;
			}

			// Check if this person is a department head (dept->employee_id matches)
			if ($dept->employee_id == $emp->user_id) {
				$dept->head_node = $node;
				$dept_heads[$emp->department_id] = $node;
			} else {
				$dept_staff[$emp->department_id][] = $node;
			}
		}

		// If no CEO found, use Management dept head
		if (!$ceo && isset($dept_heads[10])) {
			$ceo = $dept_heads[10];
			unset($dept_heads[10]);
		}

		// Assemble: CEO -> dept heads -> staff under each dept
		if ($ceo) {
			foreach ($dept_heads as $did => $head) {
				if (isset($dept_staff[$did])) {
					$head['children'] = $dept_staff[$did];
				}
				$ceo['children'][] = $head;
			}
			return $ceo;
		}

		// Fallback: no CEO found, just return first dept head
		if (!empty($dept_heads)) {
			$first = reset($dept_heads);
			$first_key = key($dept_heads);
			if (isset($dept_staff[$first_key])) {
				$first['children'] = $dept_staff[$first_key];
			}
			return $first;
		}

		return array('name' => 'No Data', 'id' => 'root', 'title' => '', 'photo' => '', 'children' => array());
	}
	
	
	
} 
?>