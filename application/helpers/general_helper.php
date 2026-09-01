<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function initialize_elfinder($value=''){
	$CI =& get_instance();
	$CI->load->helper('path');
	$opts = array(
	    //'debug' => true, 
	    'roots' => array(
	      array( 
	        'driver' => 'LocalFileSystem', 
	        'path'   => './uploads/files_manager/', 
	        'URL'    => site_url('uploads/files_manager').'/'
	        // more elFinder options here
	      ) 
	    )
	);
	return $opts;
}

function getWorkingDays($startDate, $endDate)
{
    $begin = strtotime($startDate);
    $end   = strtotime($endDate);
    if ($begin > $end) {

        return 0;
    } else {
        $no_days  = 0;
        while ($begin <= $end) {
            $what_day = date("N", $begin);
            if (!in_array($what_day, [6,7]) ) // 6 and 7 are weekend
                $no_days++;
            $begin += 86400; // +1 day
        };

        return $no_days;
    }
}

if ( ! function_exists('get_employee_leave_category'))
{
	function get_employee_leave_category($id_nums,$employee_id) {
		if (empty($id_nums)) return array();
		$CI =&	get_instance();
		$sql = "select e.leave_categories,e.user_id,l.leave_type_id,l.type_name from xin_employees as e, xin_leave_type as l where l.leave_type_id IN ($id_nums) and e.user_id = $employee_id";
		$query = $CI->db->query($sql);
		$result = $query->result();
		return $result;
	}
}
if ( ! function_exists('get_sub_departments'))
{
	function get_sub_departments($id) {
		$CI =&	get_instance();
		$sql = "select * from xin_sub_departments where department_id = $id";
		$query = $CI->db->query($sql);
		$result = $query->result();
		return $result;
	}
}
if ( ! function_exists('get_main_departments_employees'))
{
	function get_main_departments_employees() {
		$CI =&	get_instance();
		$sql = "select d.*, e.user_id, e.first_name, e.last_name, e.profile_picture, e.designation_id, e.department_id 
				from xin_departments as d 
				inner join xin_employees as e on d.department_id = e.department_id 
				where e.is_active = 1 
				group by d.department_id, e.user_id, e.first_name, e.last_name, e.profile_picture, e.designation_id, e.department_id";
		$query = $CI->db->query($sql);
		$result = $query->result();
		return $result;
	}
}
if ( ! function_exists('get_sub_departments_employees'))
{
	function get_sub_departments_employees($id,$empid) {
		$CI =&	get_instance();
		$sql = "select d.*, e.user_id, e.first_name, e.last_name, e.profile_picture, e.designation_id, e.department_id, e.sub_department_id
				from xin_sub_departments as d
				inner join xin_employees as e on d.sub_department_id = e.sub_department_id
				where e.department_id = '".$id."' and e.user_id != '".$empid."' and e.is_active = 1
				group by d.sub_department_id, e.user_id, e.first_name, e.last_name, e.profile_picture, e.designation_id, e.department_id, e.sub_department_id";
		$query = $CI->db->query($sql);
		$result = $query->result();
		return $result;
	}
}
if ( ! function_exists('get_sub_departments_designations'))
{
	function get_sub_departments_designations($id,$empid,$mainid) {
		$CI =&	get_instance();
		$sql = "select d.*,e.* from xin_designations as d, xin_employees as e where d.designation_id = e.designation_id and e.employee_id!= '".$empid."' and e.employee_id!= '".$mainid."' and e.designation_id = '".$id."'";
		$query = $CI->db->query($sql);
		$result = $query->result();
		return $result;
	}
}
if ( ! function_exists('total_salaries_paid'))
{
	function total_salaries_paid() {
			$CI =&	get_instance();
			$CI->db->from('xin_salary_payslips');
			$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->net_salary;
			}
			return $tinc;
		}else{
			return 0;
		}
	}

}
if ( ! function_exists('count_leaves_info'))
{
	function count_leaves_info($leave_type_id,$employee_id) {
			$CI =&	get_instance();
			$CI->db->from('xin_leave_applications');
			$CI->db->where('employee_id',$employee_id);
			$CI->db->where('leave_type_id',$leave_type_id);
			$CI->db->where('status!=',3);
			$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$ifrom_date =  $inc->from_date;
				$ito_date =  $inc->to_date;

				if (!defined('SATURDAY')) define('SATURDAY', 6);
				if (!defined('SUNDAY')) define('SUNDAY', 0);
			  
			  $start = strtotime($ifrom_date);
			  $end   = strtotime($ito_date);
			  $workdays = 0;
			  for ($i = $start; $i <= $end; $i = strtotime("+1 day", $i)) {
				$day = date("w", $i);  // 0=sun, 1=mon, ..., 6=sat
				$mmgg = date('m-d', $i);
				if ($day != SUNDAY &&
				  !($day == SATURDAY)) {
					$workdays++;
				}
			  }
			  $tinc = $workdays + $tinc;
				
			}
			return $tinc;
		}else{
			return 0;
		}
	}

}
if ( ! function_exists('total_tickets'))
{
	function total_tickets() {
		$CI =&	get_instance();
		$CI->db->from('xin_support_tickets');
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('total_open_tickets'))
{
	function total_open_tickets() {
		$CI =&	get_instance();
		$CI->db->from('xin_support_tickets');
		$CI->db->where('ticket_status',1);
		$query = $CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('total_closed_tickets'))
{
	function total_closed_tickets() {
		$CI =&	get_instance();
		$CI->db->from('xin_support_tickets');
		$CI->db->where('ticket_status',2);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('active_employees'))
{
	function active_employees() {
		$CI =&	get_instance();
		$CI->db->from('xin_employees');
		$CI->db->where('is_active',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('inactive_employees'))
{
	function inactive_employees() {
		$CI =&	get_instance();
		$CI->db->from('xin_employees');
		$CI->db->where('is_active',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('completed_tasks'))
{
	function completed_tasks() {
		$CI =&	get_instance();
		$CI->db->from('xin_tasks');
		$CI->db->where('task_status',2);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('inprogress_tasks'))
{
	function inprogress_tasks() {
		$CI =&	get_instance();
		$CI->db->from('xin_tasks');
		$CI->db->where('task_status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('total_account_balances'))
{
	function total_account_balances() {
			$CI =&	get_instance();
			$CI->db->from('xin_finance_bankcash');
			$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->account_balance;
			}
			return $tinc;
		}else{
			return 0;
		}
	}

}
//after v1.0.11
if ( ! function_exists('system_settings_info'))
{
		function system_settings_info($id) {
			$CI =&	get_instance();
			$CI->db->from('xin_system_setting');
			$CI->db->where('setting_id',$id);
			$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->row();
			return $result;
		}else{
			return "";
		}
	}

}
if ( ! function_exists('xin_company_info'))
{
		function xin_company_info($id) {
			$CI =&	get_instance();
			$CI->db->from('xin_company_info');
			$CI->db->where('company_info_id',$id);
			$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->row();
			return $result;
		}else{
			return "";
		}
	}

}
if ( ! function_exists('read_invoice_record'))
{
		function read_invoice_record($id) {
			$CI =&	get_instance();
			$CI->db->from('xin_hrsale_invoices');
			$CI->db->where('invoice_id',$id);
			$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->row();
			return $result;
		}else{
			return "";
		}
	}
}
if ( ! function_exists('get_invoice_transaction_record'))
{
	function get_invoice_transaction_record($id) {
		$CI =&	get_instance();
		$CI->db->from('xin_finance_transaction');
		$CI->db->where('transaction_type','income');
		$CI->db->where('invoice_id',$id);
		$query=$CI->db->get();
		return $query;
	}
}
if ( ! function_exists('system_currency_sign'))
{
	//set currency sign
	function system_currency_sign($number) {
		
		// get details
		$system_setting = system_settings_info(1);
		// currency code/symbol
		if($system_setting->show_currency=='code'){
			$ar_sc = explode(' -',$system_setting->default_currency_symbol);
			$sc_show = $ar_sc[0];
		} else {
			$ar_sc = explode('- ',$system_setting->default_currency_symbol);
			$sc_show = $ar_sc[1];
		}
		if($system_setting->currency_position=='Prefix'){
			$sign_value = $sc_show.''.$number;
		} else {
			$sign_value = $number.''.$sc_show;
		}
		return $sign_value;
	}
}
//single client 
if ( ! function_exists('clients_invoice_paid_count'))
{
	function clients_invoice_paid_count($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
// all
if ( ! function_exists('all_invoice_paid_count'))
{
	function all_invoice_paid_count() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
// all
if ( ! function_exists('all_invoice_unpaid_count'))
{
	function all_invoice_unpaid_count() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('status',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_invoice_unpaid_count'))
{
	function clients_invoice_unpaid_count($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_project_inprogress'))
{
	function clients_project_inprogress($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_projects');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_project_completed'))
{
	function clients_project_completed($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_projects');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',2);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_project_notstarted'))
{
	function clients_project_notstarted($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_projects');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_project_deffered'))
{
	function clients_project_deffered($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_projects');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',3);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_invoice_paid_amount'))
{
	function clients_invoice_paid_amount($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->grand_total;
			}
			return $tinc;
		}else{
			return 0;
		}
	}
}
// all
if ( ! function_exists('all_invoice_paid_amount'))
{
	function all_invoice_paid_amount() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->grand_total;
			}
			return $tinc;
		}else{
			return 0;
		}
	}
}
// all
if ( ! function_exists('all_invoice_unpaid_amount'))
{
	function all_invoice_unpaid_amount() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('status',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->grand_total;
			}
			return $tinc;
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('clients_invoice_unpaid_amount'))
{
	function clients_invoice_unpaid_amount($cid) {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_invoices');
		$CI->db->where('client_id',$cid);
		$CI->db->where('status',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->grand_total;
			}
			return $tinc;
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('last_client_invoice_info'))
{
	function last_client_invoice_info() {
		$CI =&	get_instance();
		$sql = 'SELECT * FROM xin_hrsale_invoices order by invoice_id desc limit 1';
		$query = $CI->db->query($sql);		
		if ($query->num_rows() > 0) {
			$inv = $query->result();
			if(!is_null($inv)) {
				return $invid = $inv[0]->invoice_id;
			} else {
				return $invid = 0;
			}
		} else {
			return $invid = 0;
		}
	}
}
if ( ! function_exists('total_travel_expense'))
{
	function total_travel_expense() {
		$CI =&	get_instance();
		$CI->db->from('xin_employee_travels');
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			$result = $query->result();
			$tinc = 0;
			foreach($result as $inc){
				$tinc += $inc->actual_budget;
			}
			return $tinc;
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_quoted'))
{
	function cr_quote_quoted() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',0);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_project_created'))
{
	function cr_quote_project_created() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_inprogress'))
{
	function cr_quote_inprogress() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',2);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_project_completed'))
{
	function cr_quote_project_completed() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',3);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_invoiced'))
{
	function cr_quote_invoiced() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',4);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_paid'))
{
	function cr_quote_paid() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',5);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('cr_quote_deffered'))
{
	function cr_quote_deffered() {
		$CI =&	get_instance();
		$CI->db->from('xin_hrsale_quotes');
		$CI->db->where('status',6);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->num_rows();
		}else{
			return 0;
		}
	}
}
if ( ! function_exists('employee_leave_halfday_cal'))
{
	function employee_leave_halfday_cal($leave_type_id,$employee_id) {
		$CI =&	get_instance();
		$CI->db->from('xin_leave_applications');
		$CI->db->where('employee_id',$employee_id);
		$CI->db->where('leave_type_id',$leave_type_id);
		$CI->db->where('is_half_day',1);
		$query=$CI->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		}else{
			return $query->result();
		}
	}
}

/* =====================================================================
 * Notification email routing
 *   - directory overrides (xin_role_email_overrides)
 *   - per module/event rules (xin_notification_rules)
 * ===================================================================== */

// Fetch an override email for a role, checking a specific scope first then global (0).
if ( ! function_exists('hrm_get_role_override'))
{
	function hrm_get_role_override($role_type, $scope_id) {
		$CI =& get_instance();
		if (!$CI->db->table_exists('xin_role_email_overrides')) return null;

		$CI->db->select('override_email')->from('xin_role_email_overrides')
			->where('role_type', $role_type)->where('scope_id', $scope_id)->limit(1);
		$q = $CI->db->get()->row();
		if ($q && !empty($q->override_email)) return $q->override_email;

		$CI->db->select('override_email')->from('xin_role_email_overrides')
			->where('role_type', $role_type)->where('scope_id', 0)->limit(1);
		$q = $CI->db->get()->row();
		return ($q && !empty($q->override_email)) ? $q->override_email : null;
	}
}

// Resolve one role to a list of recipient email addresses.
if ( ! function_exists('hrm_resolve_role_recipients'))
{
	function hrm_resolve_role_recipients($role_type, $payload, $CI) {
		$emails = array();
		$company_id = isset($payload['company_id']) ? (int)$payload['company_id'] : 0;
		$dept_id    = isset($payload['department_id']) ? (int)$payload['department_id'] : 0;
		$emp_id     = isset($payload['employee_id']) ? (int)$payload['employee_id'] : 0;

		switch ($role_type) {
			case 'employee':
				if ($emp_id) {
					$u = $CI->db->select('email')->from('xin_employees')->where('user_id', $emp_id)->limit(1)->get()->row();
					if ($u && !empty($u->email)) $emails[] = $u->email;
				}
				break;

			case 'department_head':
				$override = hrm_get_role_override('department_head', $dept_id);
				if ($override) { $emails[] = $override; break; }
				// Default: xin_departments.employee_id points at the head; guard dangling refs.
				$d = $CI->db->select('employee_id')->from('xin_departments')->where('department_id', $dept_id)->limit(1)->get()->row();
				if ($d && $d->employee_id) {
					$u = $CI->db->select('email')->from('xin_employees')->where('user_id', $d->employee_id)->limit(1)->get()->row();
					if ($u && !empty($u->email)) $emails[] = $u->email;
				}
				break;

			case 'hr_manager':
				$override = hrm_get_role_override('hr_manager', $company_id);
				if ($override) { $emails[] = $override; break; }
				// Default: all employees with role 3 (HR).
				$CI->db->select('email')->from('xin_employees')->where('user_role_id', 3)->where('is_active', 1);
				$rows = $CI->db->get()->result();
				foreach ($rows as $r) { if (!empty($r->email)) $emails[] = $r->email; }
				break;

			case 'ceo':
				$override = hrm_get_role_override('ceo', $company_id);
				if ($override) { $emails[] = $override; break; }
				// Default: employee(s) whose designation is "CEO".
				$CI->db->select('e.email')
					->from('xin_employees e')
					->join('xin_designations d', 'd.designation_id = e.designation_id', 'left')
					->where('d.designation_name', 'CEO')->where('e.is_active', 1);
				$rows = $CI->db->get()->result();
				foreach ($rows as $r) { if (!empty($r->email)) $emails[] = $r->email; }
				break;

			case 'company_admin':
				$override = hrm_get_role_override('company_admin', $company_id);
				if ($override) { $emails[] = $override; break; }
				// Default: all employees with role 1 (Super Admin).
				$CI->db->select('email')->from('xin_employees')->where('user_role_id', 1)->where('is_active', 1);
				$rows = $CI->db->get()->result();
				foreach ($rows as $r) { if (!empty($r->email)) $emails[] = $r->email; }
				break;
		}

		return array_values(array_unique(array_filter($emails)));
	}
}

// Resolve all recipients for a module+event per the routing rules.
if ( ! function_exists('hrm_resolve_recipients'))
{
	function hrm_resolve_recipients($module, $event, $payload) {
		$CI =& get_instance();
		$emails = array();

		$CI->db->select('notify_roles')->from('xin_notification_rules')
			->where('module', $module)->where('event', $event)->where('enabled', 1)->limit(1);
		$rule = $CI->db->get()->row();
		if (!$rule || empty($rule->notify_roles)) return $emails;

		$roles = array_map('trim', explode(',', $rule->notify_roles));
		foreach ($roles as $role) {
			foreach (hrm_resolve_role_recipients($role, $payload, $CI) as $email) {
				$emails[] = $email;
			}
		}
		return array_values(array_unique(array_filter($emails)));
	}
}

// Central notification choke-point: resolve recipients per role, send, return per-recipient results.
// $message may be either a flat array ['subject'=>.., 'body'=>..] (sent to every role),
// or a per-role map ['department_head'=>['subject'=>..,'body'=>..], 'employee'=>[...], ...].
// Only roles present in the routing rule's notify_roles are sent to.
if ( ! function_exists('hrm_notify'))
{
	function hrm_notify($module, $event, $payload, $message, $from_email = null, $from_name = null) {
		$CI =& get_instance();
		if (is_null($from_email)) {
			$from_email = get_smtp('smtp_username');
		}
		if (is_null($from_name)) {
			$ci = $CI->db->select('company_name')->from('xin_company_info')->limit(1)->get()->row();
			$from_name = $ci && !empty($ci->company_name) ? $ci->company_name : 'HRM';
		}

		$CI->db->select('notify_roles')->from('xin_notification_rules')
			->where('module', $module)->where('event', $event)->where('enabled', 1)->limit(1);
		$rule = $CI->db->get()->row();
		if (!$rule || empty($rule->notify_roles)) return array();

		$results = array();
		$roles   = array_map('trim', explode(',', $rule->notify_roles));
		foreach ($roles as $role) {
			// Pick per-role message, else the flat default message.
			$m = null;
			if (isset($message[$role])) {
				$m = $message[$role];
			} elseif (isset($message['subject']) && isset($message['body'])) {
				$m = $message;
			}
			if (!$m || empty($m['subject']) && empty($m['body'])) {
				continue;
			}
			foreach (hrm_resolve_role_recipients($role, $payload, $CI) as $email) {
				$results[] = array(
					'role'  => $role,
					'email' => $email,
					'sent'  => (bool)hrsale_mail($from_email, $from_name, $email, $m['subject'], $m['body']),
				);
			}
		}
		return $results;
	}
}

/* =====================================================================
 * Reusable HTML email templates with action buttons
 * ===================================================================== */

if ( ! function_exists('hrm_email_wrap'))
{
	// Wrap content in a styled email shell with company logo and footer.
	function hrm_email_wrap($company_name, $logo, $content, $footer_text = '') {
		if (empty($footer_text)) $footer_text = 'This is an automated notification from ' . htmlspecialchars($company_name) . '.';
		return '<div style="margin:0;padding:0;background:#f4f4f4;font-family:Verdana,Arial,Helvetica,sans-serif;">'
			. '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:20px 0;">'
			. '<tr><td align="center">'
			. '<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:4px;overflow:hidden;">'
			. '<tr><td style="background:#3e70c9;padding:20px 30px;text-align:center;">'
			. '<img src="' . $logo . '" alt="' . htmlspecialchars($company_name) . '" style="max-height:40px;">'
			. '</td></tr>'
			. '<tr><td style="padding:30px;">' . $content . '</td></tr>'
			. '<tr><td style="background:#f6f6f6;padding:15px 30px;text-align:center;font-size:11px;color:#999;">'
			. $footer_text
			. '</td></tr>'
			. '</table></td></tr></table></div>';
	}
}

if ( ! function_exists('hrm_email_button'))
{
	// Render a styled action button link.
	function hrm_email_button($url, $label, $color = '#3e70c9') {
		return '<a href="' . $url . '" style="display:inline-block;padding:12px 30px;margin:5px;'
			. 'background:' . $color . ';color:#ffffff;text-decoration:none;border-radius:4px;'
			. 'font-weight:bold;font-size:14px;">' . htmlspecialchars($label) . '</a>';
	}
}

if ( ! function_exists('hrm_email_info_row'))
{
	// Render a label: value row for email details.
	function hrm_email_info_row($label, $value) {
		return '<tr><td style="padding:6px 0;font-weight:bold;color:#555;width:140px;vertical-align:top;">'
			. htmlspecialchars($label) . '</td>'
			. '<td style="padding:6px 0;color:#333;">' . $value . '</td></tr>';
	}
}

if ( ! function_exists('hrm_leave_email'))
{
	// Generate leave notification emails with action buttons.
	// $type: 'submitted', 'first_approved', 'second_approved', 'rejected', 'reconciled'
	// $recipient: 'employee', 'department_head', 'hr_manager', 'ceo', 'company_admin'
	function hrm_leave_email($type, $recipient, $data) {
		$company_name = isset($data['company_name']) ? $data['company_name'] : 'HRM';
		$logo         = isset($data['logo']) ? $data['logo'] : '';
		$site_url     = isset($data['site_url']) ? $data['site_url'] : site_url();
		$emp_name     = isset($data['emp_name']) ? $data['emp_name'] : '';
		$from_date    = isset($data['from_date']) ? $data['from_date'] : '';
		$to_date      = isset($data['to_date']) ? $data['to_date'] : '';
		$leave_id     = isset($data['leave_id']) ? $data['leave_id'] : 0;
		$leave_type   = isset($data['leave_type']) ? $data['leave_type'] : '';
		$reason       = isset($data['reason']) ? $data['reason'] : '';
		$approver     = isset($data['approver']) ? $data['approver'] : '';

		$detail_url = $site_url . 'admin/timesheet/leave_details/id/' . $leave_id;
		$approve_url = $site_url . 'admin/timesheet/update_leave_status/id/' . $leave_id;

		// Status-specific content
		$status_colors = array(
			'submitted'      => '#3498db',
			'first_approved' => '#27ae60',
			'second_approved'=> '#27ae60',
			'rejected'       => '#e74c3c',
			'reconciled'     => '#f39c12',
		);
		$status_labels = array(
			'submitted'      => 'Leave Request Submitted',
			'first_approved' => 'Leave Request — First Approved',
			'second_approved'=> 'Leave Approved',
			'rejected'       => 'Leave Request Rejected',
			'reconciled'     => 'Leave Reconciled',
		);
		$status_icons = array(
			'submitted'      => '📋',
			'first_approved' => '✅',
			'second_approved'=> '✅',
			'rejected'       => '❌',
			'reconciled'     => '🔄',
		);

		$title  = isset($status_labels[$type]) ? $status_labels[$type] : 'Leave Notification';
		$color  = isset($status_colors[$type]) ? $status_colors[$type] : '#3e70c9';
		$icon   = isset($status_icons[$type]) ? $status_icons[$type] : '';

		// Greeting
		$greeting = 'Hello,';
		if ($recipient == 'employee') {
			$greeting = 'Hi ' . htmlspecialchars($emp_name) . ',';
		} elseif ($recipient == 'department_head') {
			$greeting = 'Hi ' . htmlspecialchars($approver) . ',';
		} elseif ($recipient == 'hr_manager') {
			$greeting = 'Hi HR Manager,';
		} elseif ($recipient == 'ceo') {
			$greeting = 'Hi CEO,';
		}

		// Message body
		$message = '';
		switch ($type) {
			case 'submitted':
				if ($recipient == 'employee') {
					$message = '<p>Your leave request has been submitted and sent to your department head for approval.</p>';
				} elseif ($recipient == 'department_head') {
					$message = '<p>A leave request from <strong>' . htmlspecialchars($emp_name) . '</strong> is awaiting your approval.</p>';
				} elseif ($recipient == 'hr_manager') {
					$message = '<p>A leave request from <strong>' . htmlspecialchars($emp_name) . '</strong> has been submitted and is pending department head approval.</p>';
				}
				break;
			case 'first_approved':
				if ($recipient == 'employee') {
					$message = '<p>Your leave request has been approved by your department head and forwarded to HR for final approval.</p>';
				} elseif ($recipient == 'hr_manager') {
					$message = '<p>A leave request from <strong>' . htmlspecialchars($emp_name) . '</strong> has been approved by the department head and requires your final approval.</p>';
				}
				break;
			case 'second_approved':
				if ($recipient == 'employee') {
					$message = '<p>Congratulations! Your leave request has been <strong>fully approved</strong>.</p>';
				} elseif ($recipient == 'department_head') {
					$message = '<p>The leave request from <strong>' . htmlspecialchars($emp_name) . '</strong> has been given final approval by HR.</p>';
				}
				break;
			case 'rejected':
				if ($recipient == 'employee') {
					$message = '<p>Unfortunately, your leave request has been <strong>rejected</strong>.</p>';
				} elseif ($recipient == 'department_head') {
					$message = '<p>The leave request from <strong>' . htmlspecialchars($emp_name) . '</strong> has been rejected.</p>';
				}
				break;
			case 'reconciled':
				$message = '<p>Leave days have been reconciled for <strong>' . htmlspecialchars($emp_name) . '</strong>.</p>';
				break;
		}

		// Details table
		$details = '<table cellpadding="0" cellspacing="0" style="width:100%;margin:15px 0;">';
		$details .= hrm_email_info_row('Employee', htmlspecialchars($emp_name));
		$details .= hrm_email_info_row('Leave Type', htmlspecialchars($leave_type));
		$details .= hrm_email_info_row('From', htmlspecialchars($from_date));
		$details .= hrm_email_info_row('To', htmlspecialchars($to_date));
		if (!empty($reason)) {
			$details .= hrm_email_info_row('Reason', htmlspecialchars($reason));
		}
		$details .= '</table>';

		// Action buttons
		$buttons = '';
		if ($recipient == 'department_head' && $type == 'submitted') {
			$buttons = '<div style="text-align:center;margin:25px 0;">'
				. hrm_email_button($detail_url, 'View Details', '#3e70c9')
				. '</div>';
		} elseif ($recipient == 'hr_manager' && $type == 'first_approved') {
			$buttons = '<div style="text-align:center;margin:25px 0;">'
				. hrm_email_button($detail_url, 'Review & Approve', '#27ae60')
				. '</div>';
		} else {
			$buttons = '<div style="text-align:center;margin:25px 0;">'
				. hrm_email_button($detail_url, 'View Details', '#3e70c9')
				. '</div>';
		}

		$content = '<h2 style="color:' . $color . ';margin:0 0 15px;">' . $icon . ' ' . $title . '</h2>'
			. '<p style="color:#555;">' . $greeting . '</p>'
			. $message
			. $details
			. $buttons;

		return hrm_email_wrap($company_name, $logo, $content);
	}
}
?>