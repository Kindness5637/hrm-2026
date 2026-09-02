<?php
/*class Cron_job_cnt extends CI_Controller { 
    public function __construct() {
            parent::__construct();
            $this->load->model("Timesheet_model");
			$this->load->helper(array('form', 'url'));
            $this->load->library("pagination");        
    }*/
    
    defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_job_cnt extends MY_Controller {

	public function __construct() {
		parent::__construct();
		//load the model
		$this->load->model("Timesheet_model");
		$this->load->model("Employees_model");
		$this->load->model("Xin_model");
		$this->load->library('email');
		$this->load->model("Department_model");
		$this->load->model("Designation_model");
		$this->load->model("Roles_model");
		$this->load->model("Project_model");
		$this->load->model("Location_model");
	}
    
    function cron_job()
    {
        
        $employee = $this->Timesheet_model->get_all_employees_active();
        foreach($employee->result() as $r) {
            $full_name = $r->first_name.' '.$r->last_name;
            $date_of_birth = $r->date_of_birth;
            
            hrsale_mail('hrmsystem@stalis.co.ke',
	             'Stalis Consulting',
	            'softwareadmin@stalis.co.ke',
	            'Cron Test Birthday',
	            'Hello '.$full_name
                );
        }
        

    }
}
?>
