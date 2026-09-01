<?php
$session = $this->session->userdata('username');
$system = $this->Xin_model->read_setting_info(1);
$company_info = $this->Xin_model->read_company_setting_info(1);
$user = $this->Xin_model->read_employee_info($session['user_id']);
$theme = $this->Xin_model->read_theme_info(1);
?>
<?php $site_lang = $this->load->helper('language');?>
<?php $wz_lang = $site_lang->session->userdata('site_lang');?>
<?php
if(!empty($wz_lang)):
	$lang_code = $this->Xin_model->get_language_info($wz_lang);
	$flg_icn = $lang_code[0]->language_flag;
	$flg_icn = '<img src="'.base_url().'uploads/languages_flag/'.$flg_icn.'">';
elseif($system[0]->default_language!=''):
	$lang_code = $this->Xin_model->get_language_info($system[0]->default_language);
	$flg_icn = $lang_code[0]->language_flag;
	$flg_icn = '<img src="'.base_url().'uploads/languages_flag/'.$flg_icn.'">';
else:
	$flg_icn = '<img src="'.base_url().'uploads/languages_flag/gb.gif">';	
endif;
?>
<?php
$role_user = $this->Xin_model->read_user_role_info($user[0]->user_role_id);
if(!is_null($role_user)){
	$role_resources_ids = explode(',',$role_user[0]->role_resources);
} else {
	$role_resources_ids = explode(',',0);	
}
//$designation_info = $this->Xin_model->read_designation_info($user_info[0]->designation_id);
// set color
if($theme[0]->is_semi_dark==1):
	$light_cls = 'navbar-semi-dark navbar-shadow';
	$ext_clr = '';
else:
	$light_cls = 'navbar-dark';
	$ext_clr = $theme[0]->top_nav_dark_color;
endif;
// set layout / fixed or static
if($theme[0]->boxed_layout=='true'){
	$lay_fixed = 'container boxed-layout';
} else {
	$lay_fixed = '';
}
if($theme[0]->animation_style == '') {
	$animated = 'animated flipInY';
} else {
	$animated = 'animated '.$theme[0]->animation_style;
}
?>
<header class="main-header">
    <!-- Logo -->
    <a href="<?php echo site_url('admin/dashboard/');?>" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>XTR HRM</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b><?php echo $system[0]->application_name;?></b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
		  <?php if($system[0]->module_chat_box=='true'){?>
          <li class="dropdown messages-menu">
            <a href="<?php echo site_url('admin/chat');?>">
             <i class="fa fa-comments"></i></i>
              <?php $unread_msgs = $this->Xin_model->get_single_unread_message($session['user_id']);?>
              <?php if($unread_msgs > 0) {?><span class="chat-badge label label-aqua" id="msgs_count"><?php echo $unread_msgs;?></span><?php } ?>
            </a>
          </li>
          <?php } ?>
		  <?php  if(in_array('90',$role_resources_ids)) { ?>
           <?php $fcount = 0; $leave_count = 0; $proj_count = 0; $tsk_count = 0;$nst_count = 0; $tkt_count = 0;
				if($user[0]->user_role_id=='1'){
					$leaveapp = $this->Xin_model->get_notify_leave_applications();
					$nproject = $this->Xin_model->get_notify_projects();
					$ntask = $this->Xin_model->get_notify_tasks();
					$nannouncements = $this->Xin_model->get_notify_announcements();
					$ntickets = $this->Xin_model->get_notify_tickets();
					// count
					$leave_count = $this->Xin_model->count_notify_leave_applications();
					$proj_count = $this->Xin_model->count_notify_projects();
					$tsk_count = $this->Xin_model->count_notify_tasks();
					$nst_count = $this->Xin_model->count_notify_announcements();
					$tkt_count = $this->Xin_model->count_notify_tickets();
					//$tsk_count = $this->Xin_model->count_notify_tasks();
					$fcount = $proj_count + $leave_count + $tsk_count + $nst_count + $tkt_count;
				} else {
					$leaveapp = $this->Xin_model->get_last_user_leave_applications($session['user_id']);
					// projects
					if(in_array('318',$role_resources_ids)) {
						$nproject = $this->Xin_model->get_notify_company_projects($user[0]->company_id);
						$proj_count = $this->Xin_model->count_notify_company_projects($user[0]->company_id);
					} else {
						$nproject = $this->Xin_model->get_notify_user_projects($session['user_id']);
						$proj_count = $this->Xin_model->count_notify_user_projects($session['user_id']);
					}
					// tasks
					if(in_array('322',$role_resources_ids)) {
						$ntask = $this->Xin_model->get_notify_company_tasks($user[0]->company_id);
						$tsk_count = $this->Xin_model->count_notify_company_tasks($user[0]->company_id);
					} else {
						$ntask = $this->Xin_model->get_notify_user_tasks($session['user_id']);
						$tsk_count = $this->Xin_model->count_notify_user_tasks($session['user_id']);
					}
					// announcementss
					if(in_array('257',$role_resources_ids)) {
						$nannouncements = $this->Xin_model->get_notify_company_announcements($user[0]->company_id);
						$nst_count = $this->Xin_model->count_notify_company_announcements($user[0]->company_id);
					} else {
						$nannouncements = $this->Xin_model->get_notify_dept_announcements($user[0]->department_id);
						$nst_count = $this->Xin_model->count_notify_dept_announcements($user[0]->department_id);
					}
					// tickets
					if(in_array('309',$role_resources_ids)) {
						$ntickets = $this->Xin_model->get_notify_company_tickets($user[0]->company_id);
						$tkt_count = $this->Xin_model->count_notify_company_tickets($user[0]->company_id);
					} else {
						$ntickets = $this->Xin_model->get_notify_user_tickets($session['user_id']);
						$tkt_count = $this->Xin_model->count_notify_user_tickets($session['user_id']);
					}
					// count
					$leave_count = $this->Xin_model->count_user_notify_leave_applications($session['user_id']);
					$fcount = $proj_count + $leave_count + $tsk_count + $nst_count + $tkt_count;
				}
			 ?>
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
              <i class="fa fa-bell"></i>
              <?php if($fcount > 0){?>
              <span class="label label-danger notification-badge"><?php echo $fcount;?></span>
              <?php } ?>
            </a>
            <?php if($proj_count > 0 || $leave_count > 0 || $tsk_count > 0 || $nst_count > 0 || $tkt_count > 0){?>
            <ul class="dropdown-menu menu <?php echo $animated;?>">
              <li class="header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:4px 4px 0 0;padding:12px 15px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <span><i class="fa fa-bell" style="margin-right:6px;"></i> <?php echo $this->lang->line('xin_notification_center');?></span>
                  <span class="badge" style="background:rgba(255,255,255,0.3);color:#fff;"><?php echo $fcount;?> <?php echo $this->lang->line('xin_new');?></span>
                </div>
              </li>
              <li><ul class="menu" style="max-height:350px;overflow-y:auto;padding:0;">
                <?php if($leave_count > 0){?>
                <li class="notif-section-header" style="background:#f8f9fa;padding:8px 15px;font-size:11px;text-transform:uppercase;font-weight:600;color:#6c757d;letter-spacing:0.5px;">
                  <i class="fa fa-calendar-check-o text-purple" style="margin-right:5px;"></i> <?php echo $this->lang->line('xin_leave_notifications');?> <span class="badge badge-purple pull-right" style="background:#7c3aed;color:#fff;font-size:10px;"><?php echo $leave_count;?></span>
                </li>
                <?php foreach($leaveapp as $leave_notify){?>
                <?php $employee_info = $this->Xin_model->read_user_info($leave_notify->employee_id);?>
                <?php
                    if(!is_null($employee_info)){
                        $emp_name = $employee_info[0]->first_name. ' '.$employee_info[0]->last_name;
                        $emp_profile = $employee_info[0]->profile_picture;
                        $emp_gender = $employee_info[0]->gender;
                    } else {
                        $emp_name = '--';
                        $emp_profile = '';
                        $emp_gender = 'Male';
                    }
                ?>
                <li style="border-bottom:1px solid #f0f0f0;">
                  <a href="<?php echo site_url('admin/timesheet/leave_details/id')?>/<?php echo $leave_notify->leave_id;?>/" style="padding:10px 15px;display:flex;align-items:center;">
                    <div style="flex-shrink:0;margin-right:10px;">
                      <?php if($emp_profile!='' && $emp_profile!='no file') {?>
                      <img src="<?php echo base_url().'uploads/profile/'.$emp_profile;?>" class="img-circle" style="width:35px;height:35px;" alt="">
                      <?php } else {?>
                      <img src="<?php echo base_url().'uploads/profile/'.($emp_gender=='Male'?'default_male.jpg':'default_female.jpg');?>" class="img-circle" style="width:35px;height:35px;" alt="">
                      <?php } ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="font-weight:600;color:#333;font-size:13px;"><?php echo $emp_name;?></div>
                      <div style="color:#888;font-size:12px;"><i class="fa fa-calendar" style="margin-right:3px;"></i> <?php echo $this->lang->line('header_has_applied_for_leave');?></div>
                    </div>
                    <div style="flex-shrink:0;margin-left:8px;">
                      <span style="background:#f3e8ff;color:#7c3aed;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">LEAVE</span>
                    </div>
                  </a>
                </li>
                <?php } ?>
                <?php } ?>
                
                <?php if($proj_count > 0){?>
                <li class="notif-section-header" style="background:#f8f9fa;padding:8px 15px;font-size:11px;text-transform:uppercase;font-weight:600;color:#6c757d;letter-spacing:0.5px;">
                  <i class="fa fa-project-diagram text-success" style="margin-right:5px;"></i> <?php echo $this->lang->line('xin_projects_notifications');?> <span class="badge pull-right" style="background:#10b981;color:#fff;font-size:10px;"><?php echo $proj_count;?></span>
                </li>
                <?php foreach($nproject as $nprj) {?>
                <li style="border-bottom:1px solid #f0f0f0;">
                  <a href="<?php echo site_url('admin/project/detail')?>/<?php echo $nprj->project_id;?>/" style="padding:10px 15px;display:flex;align-items:center;">
                    <div style="flex-shrink:0;margin-right:10px;width:35px;height:35px;border-radius:50%;background:#ecfdf5;display:flex;align-items:center;justify-content:center;">
                      <i class="fa fa-tasks text-success"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="font-weight:600;color:#333;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $nprj->title;?></div>
                      <div style="color:#888;font-size:12px;"><i class="fa fa-calendar" style="margin-right:3px;"></i> <?php echo $this->Xin_model->set_date_format($nprj->end_date);?></div>
                    </div>
                    <div style="flex-shrink:0;margin-left:8px;">
                      <span style="background:#ecfdf5;color:#059669;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">PROJECT</span>
                    </div>
                  </a>
                </li>
                <?php } ?>
                <?php } ?>
                
                <?php if($tsk_count > 0){?>
                <li class="notif-section-header" style="background:#f8f9fa;padding:8px 15px;font-size:11px;text-transform:uppercase;font-weight:600;color:#6c757d;letter-spacing:0.5px;">
                  <i class="fa fa-clipboard text-info" style="margin-right:5px;"></i> <?php echo $this->lang->line('xin_tasks_notifications');?> <span class="badge pull-right" style="background:#0ea5e9;color:#fff;font-size:10px;"><?php echo $tsk_count;?></span>
                </li>
                <?php foreach($ntask as $ntsk) {?>
                <li style="border-bottom:1px solid #f0f0f0;">
                  <a href="<?php echo site_url('admin/timesheet/task_details')?>/id/<?php echo $ntsk->task_id;?>/" style="padding:10px 15px;display:flex;align-items:center;">
                    <div style="flex-shrink:0;margin-right:10px;width:35px;height:35px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;">
                      <i class="fa fa-clipboard text-info"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="font-weight:600;color:#333;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $ntsk->task_name;?></div>
                      <div style="color:#888;font-size:12px;"><i class="fa fa-calendar" style="margin-right:3px;"></i> <?php echo $this->Xin_model->set_date_format($ntsk->end_date);?></div>
                    </div>
                    <div style="flex-shrink:0;margin-left:8px;">
                      <span style="background:#e0f2fe;color:#0284c7;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">TASK</span>
                    </div>
                  </a>
                </li>
                <?php } ?>
                <?php } ?>
                
                <?php if($nst_count > 0){?>
                <li class="notif-section-header" style="background:#f8f9fa;padding:8px 15px;font-size:11px;text-transform:uppercase;font-weight:600;color:#6c757d;letter-spacing:0.5px;">
                  <i class="fa fa-bullhorn text-warning" style="margin-right:5px;"></i> <?php echo $this->lang->line('dashboard_announcements');?> <span class="badge pull-right" style="background:#f59e0b;color:#fff;font-size:10px;"><?php echo $nst_count;?></span>
                </li>
                <?php foreach($nannouncements as $n_annc) {?>
                <li style="border-bottom:1px solid #f0f0f0;">
                  <a href="<?php echo site_url('admin/announcement')?>/?is_notify=1" style="padding:10px 15px;display:flex;align-items:center;">
                    <div style="flex-shrink:0;margin-right:10px;width:35px;height:35px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                      <i class="fa fa-bullhorn text-warning"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="font-weight:600;color:#333;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $n_annc->title;?></div>
                      <div style="color:#888;font-size:12px;"><i class="fa fa-calendar" style="margin-right:3px;"></i> <?php echo $this->Xin_model->set_date_format($n_annc->start_date);?></div>
                    </div>
                    <div style="flex-shrink:0;margin-left:8px;">
                      <span style="background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">ANNOUNCE</span>
                    </div>
                  </a>
                </li>
                <?php } ?>
                <?php } ?>
                
                <?php if($tkt_count > 0){?>
                <li class="notif-section-header" style="background:#f8f9fa;padding:8px 15px;font-size:11px;text-transform:uppercase;font-weight:600;color:#6c757d;letter-spacing:0.5px;">
                  <i class="fa fa-ticket text-danger" style="margin-right:5px;"></i> <?php echo $this->lang->line('left_tickets');?> <span class="badge pull-right" style="background:#ef4444;color:#fff;font-size:10px;"><?php echo $tkt_count;?></span>
                </li>
                <?php foreach($ntickets as $n_ticket) {?>
                <li style="border-bottom:1px solid #f0f0f0;">
                  <a href="<?php echo site_url('admin/tickets/details')?>/<?php echo $n_ticket->ticket_id;?>" style="padding:10px 15px;display:flex;align-items:center;">
                    <div style="flex-shrink:0;margin-right:10px;width:35px;height:35px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;">
                      <i class="fa fa-ticket text-danger"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="font-weight:600;color:#333;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $n_ticket->subject;?></div>
                      <div style="color:#888;font-size:12px;"><i class="fa fa-hashtag" style="margin-right:3px;"></i> <?php echo $n_ticket->ticket_code;?></div>
                    </div>
                    <div style="flex-shrink:0;margin-left:8px;">
                      <span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">TICKET</span>
                    </div>
                  </a>
                </li>
                <?php } ?>
                <?php } ?>
              </ul></li>
              <li class="footer" style="text-align:center;padding:10px;background:#f8f9fa;border-radius:0 0 4px 4px;">
                <a href="<?php echo site_url('admin/timesheet/leave');?>" style="color:#667eea;font-weight:600;font-size:13px;">
                  <i class="fa fa-arrow-right" style="margin-right:4px;"></i> <?php echo $this->lang->line('xin_view_all_notifications');?>
                </a>
              </li>
            </ul>
            <?php } ?>
          </li> 
          <?php } ?>
          <!-- Tasks: style can be found in dropdown.less -->
          <!-- User Account: style can be found in dropdown.less -->
          	<?php  if(in_array('61',$role_resources_ids) || in_array('93',$role_resources_ids) || in_array('63',$role_resources_ids) || in_array('92',$role_resources_ids) || in_array('62',$role_resources_ids) || in_array('94',$role_resources_ids) || in_array('96',$role_resources_ids) || in_array('60',$role_resources_ids) || $user[0]->user_role_id==1 || $system[0]->module_recruitment=='true' || $system[0]->enable_job_application_candidates=='1' || in_array('50',$role_resources_ids) || in_array('393',$role_resources_ids)) { ?>
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="true">
                  <i class="fa fa-asterisk"></i>
                </a>
                <ul class="dropdown-menu <?php echo $animated;?>">
                  <?php if($system[0]->module_recruitment=='true'){?>
				  <?php if($system[0]->enable_job_application_candidates=='1'){?>
                  <?php  if(in_array('50',$role_resources_ids)) { ?>
                  <li role="presentation">
                    <a role="menuitem" tabindex="-1" target="_blank" href="<?php echo site_url();?>jobs/"><i class="fa fa-newspaper-o"></i><?php echo $this->lang->line('header_apply_jobs_frontend');?>
                    </a>
                  </li>
                  <?php  } ?>
                  <?php  } ?>
                  <?php  } ?>
				  <?php  if(in_array('61',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/settings/constants');?>"> <i class="fa fa-align-justify"></i><?php echo $this->lang->line('left_constants');?></a></li>
                  <?php } ?>
                  <?php  if(in_array('393',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/custom_fields');?>"> <i class="fa fa-sliders"></i><?php echo $this->lang->line('xin_hrsale_custom_fields');?></a></li>
                  <?php } ?>
				  <?php  if($user[0]->user_role_id==1) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/roles');?>"> <i class="fa fa-unlock-alt"></i><?php echo $this->lang->line('xin_role_urole');?></a></li>
                  <?php } ?>
                  <?php  if(in_array('93',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/settings/modules');?>"> <i class="fa fa-life-ring"></i><?php echo $this->lang->line('xin_setup_modules');?></a></li>
                  <?php } ?>
                  <?php  if(in_array('63',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/settings/email_template');?>"> <i class="fa fa-envelope"></i><?php echo $this->lang->line('left_email_templates');?></a></li>
                  <?php } ?>
                  <?php  if(in_array('92',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/employees/import');?>"> <i class="fa fa-users"></i><?php echo $this->lang->line('xin_import_employees');?></a></li>
                  <?php } ?>
				  <?php  if(in_array('62',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/settings/database_backup');?>"> <i class="fa fa-database"></i><?php echo $this->lang->line('header_db_log');?></a></li>
                  <?php } ?>
                  <?php  if(in_array('94',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/theme');?>"> <i class="fa fa-columns"></i><?php echo $this->lang->line('xin_theme_settings');?></a></li>
                  <?php } ?>
                  <?php if($system[0]->module_orgchart=='true'){?>
            	  <?php if(in_array('96',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/organization/chart');?>"> <i class="fa fa-sitemap"></i><?php echo $this->lang->line('xin_org_chart_title');?></a></li>
                  <?php } ?>
                  <?php } ?>
                  <?php if(in_array('60',$role_resources_ids)) { ?>
                  <li class="divider"></li>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/settings');?>"> <i class="fa fa-cog text-aqua"></i><?php echo $this->lang->line('header_configuration');?></a></li>
                  <?php } ?>
                </ul>
              </li>
            <?php } ?>  
          	<?php if($system[0]->module_language=='true'){?>
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="true">
                  <?php echo $flg_icn;?>
                </a>
                <ul class="dropdown-menu <?php echo $animated;?>">
                <?php $languages = $this->Xin_model->all_languages();?>
				<?php foreach($languages as $lang):?>
                <?php $flag = '<img src="'.base_url().'uploads/languages_flag/'.$lang->language_flag.'">';?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/dashboard/set_language/').$lang->language_code;?>"><?php echo $flag;?> &nbsp; <?php echo $lang->language_name;?></a></li>
                  <?php endforeach;?>
                  <?php if($system[0]->module_language=='true'){?>
            	<?php  if(in_array('89',$role_resources_ids)) { ?>
                  <li class="divider"></li>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/languages');?>"> <i class="fa fa-cog text-aqua"></i><?php echo $this->lang->line('left_settings');?></a></li>
                  <?php } ?>
                  <?php } ?>
                </ul>
              </li>
            <?php } ?>   
           <li class="dropdown">
              <?php  if($user[0]->profile_picture!='' && $user[0]->profile_picture!='no file') {?>
            	<?php $cpimg = base_url().'uploads/profile/'.$user[0]->profile_picture;?>
            	<?php $cimg = '<img src="'.$cpimg.'" alt="" id="user_avatar" class="img-circle rounded-circle user_profile_avatar">';?>
            <?php } else {?>
            <?php  if($user[0]->gender=='Male') { ?>
            <?php 	$de_file = base_url().'uploads/profile/default_male.jpg';?>
            <?php } else { ?>
            <?php 	$de_file = base_url().'uploads/profile/default_female.jpg';?>
            <?php } ?>
            	<?php $cpimg = $de_file;?>
            	<?php $cimg = '<img src="'.$de_file.'" alt="" id="user_avatar" class="img-circle rounded-circle user_profile_avatar">';?>
            <?php  } ?>
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="true">
              <img src="<?php echo $cpimg;?>" class="user-image-top" alt="<?php echo $user[0]->first_name.' '.$user[0]->last_name;?>">
            </a>
            <ul class="dropdown-menu <?php echo $animated;?>">
              	<li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/profile');?>"> <i class="ion ion-person"></i><?php echo $this->lang->line('header_my_profile');?></a></li>
                  <li role="presentation">
                  <a data-toggle="modal" data-target=".policy" href="#"> <i class="fa fa-flag-o"></i><?php echo $this->lang->line('header_policies');?></a></li>
                  <?php if(in_array('60',$role_resources_ids)) { ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/settings');?>"> <i class="ion ion-settings"></i><?php echo $this->lang->line('left_settings');?></a></li>
                  <?php } ?>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/auth/lock');?>"> <i class="fa fa-lock"></i><?php echo $this->lang->line('xin_lock_user');?></a></li>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/profile?change_password=true');?>"> <i class="fa fa-key"></i><?php echo $this->lang->line('header_change_password');?></a></li>
                  <li class="divider"></li>
                  <li role="presentation">
                  <a role="menuitem" tabindex="-1" href="<?php echo site_url('admin/logout');?>"> <i class="fa fa-power-off text-red"></i><?php echo $this->lang->line('header_sign_out');?></a></li>
                </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <!-- <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gear fa-spin"></i></a>
          </li> -->
        </ul>
      </div>
    </nav>
  </header>
