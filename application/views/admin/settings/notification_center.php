<?php
/**
 * Notification Center — 3-tab settings page
 * Tab 1: Deliverability (SMTP + CC + test send + outbox log)
 * Tab 2: Recipients (who's who directory + overrides)
 * Tab 3: Routing rules (module × event → role checkboxes)
 */
?>
<?php $get_animate = $this->Xin_model->get_content_animate(); ?>

<section id="basic-listgroup">
  <div class="row match-heights <?php echo $get_animate; ?>">

    <!-- ===== LEFT NAV ===== -->
    <div class="col-lg-3 col-md-3" style="position:relative;">
      <div class="card" id="nc_sidebar">
        <div class="card-blocks">
          <div class="list-group">
            <a class="list-group-item list-group-item-action nav-tabs-link hrsale-tab-item active"
               href="#nc_deliverability" data-setting="nc1" data-profile-block="nc_deliverability"
               data-toggle="tab" aria-expanded="true" id="setting_nc1">
              <i class="fa fa-envelope"></i> Deliverability
            </a>
            <a class="list-group-item list-group-item-action nav-tabs-link hrsale-tab-item"
               href="#nc_recipients" data-setting="nc2" data-profile-block="nc_recipients"
               data-toggle="tab" aria-expanded="true" id="setting_nc2">
              <i class="fa fa-users"></i> Recipients
            </a>
            <a class="list-group-item list-group-item-action nav-tabs-link hrsale-tab-item"
               href="#nc_routing" data-setting="nc3" data-profile-block="nc_routing"
               data-toggle="tab" aria-expanded="true" id="setting_nc3">
              <i class="fa fa-random"></i> Routing Rules
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== TAB 1 — DELIVERABILITY ===== -->
    <div class="col-md-9 current-tab <?php echo $get_animate; ?>" id="nc_deliverability">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-envelope"></i> SMTP Configuration &amp; Deliverability</h3>
        </div>
        <div class="box-body">
          <div class="box-block">
            <?php $attributes = array('name' => 'nc_smtp_form', 'id' => 'nc_smtp_form', 'autocomplete' => 'off'); ?>
            <?php $hidden = array('type' => 'notification_smtp'); ?>
            <?php echo form_open('admin/settings/notification_smtp_save', $attributes, $hidden); ?>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Mail Type</label>
                  <select class="form-control" name="email_type" id="nc_email_type" data-plugin="select_hrm">
                    <option value="codeigniter" <?php if ($email_type == 'codeigniter') echo 'selected'; ?>>CodeIgniter Mail()</option>
                    <option value="phpmail" <?php if ($email_type == 'phpmail') echo 'selected'; ?>>PHP Mail()</option>
                    <option value="smtp" <?php if ($email_type == 'smtp') echo 'selected'; ?>>SMTP</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Global CC Emails</label>
                  <input class="form-control" name="notification_cc_emails" type="text"
                         value="<?php echo htmlspecialchars($cc_emails); ?>"
                         placeholder="observer1@co.ke, observer2@co.ke">
                  <p class="help-block">Comma-separated. Appended to every notification email.</p>
                </div>
              </div>
            </div>

            <div id="nc_smtp_fields" <?php if ($email_type != 'smtp') echo 'style="display:none;"'; ?>>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>SMTP Host</label>
                    <input class="form-control" name="smtp_host" type="text" value="<?php echo htmlspecialchars($smtp_host); ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>SMTP Username</label>
                    <input class="form-control" name="smtp_username" type="text" value="<?php echo htmlspecialchars($smtp_username); ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>SMTP Password</label>
                    <input class="form-control" name="smtp_password" type="password" value="<?php echo htmlspecialchars($smtp_password); ?>">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>SMTP Port</label>
                    <input class="form-control" name="smtp_port" type="text" value="<?php echo htmlspecialchars($smtp_port); ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>SMTP Security</label>
                    <select class="form-control" name="smtp_secure" data-plugin="select_hrm">
                      <option value="tls" <?php if ($smtp_secure == 'tls') echo 'selected'; ?>>TLS</option>
                      <option value="ssl" <?php if ($smtp_secure == 'ssl') echo 'selected'; ?>>SSL</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="form-actions box-footer">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-check-square-o"></i> Save Configuration
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <?php echo form_close(); ?>
          </div>

          <!-- Test Send -->
          <div class="box-block" style="border-top:1px solid #eee; padding-top:15px;">
            <h4><i class="fa fa-paper-plane"></i> Send Test Email</h4>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Recipient Email</label>
                  <div class="input-group">
                    <input class="form-control" id="nc_test_email" type="email" placeholder="test@stalis.co.ke">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-warning" id="nc_test_send_btn">
                        <i class="fa fa-paper-plane"></i> Send Test
                      </button>
                    </span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div id="nc_test_result" style="margin-top:8px;"></div>
              </div>
            </div>
          </div>

          <!-- Outbox Log -->
          <div class="box-block" style="border-top:1px solid #eee; padding-top:15px;">
            <h4><i class="fa fa-list"></i> Recent Outbox (last 20)</h4>
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="nc_outbox_table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>To</th>
                    <th>CC</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Sent At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($outbox)): ?>
                    <?php foreach ($outbox as $row): ?>
                      <tr>
                        <td><?php echo $row->outbox_id; ?></td>
                        <td><?php echo htmlspecialchars($row->sent_to); ?></td>
                        <td><?php echo htmlspecialchars($row->cc); ?></td>
                        <td><?php echo htmlspecialchars($row->subject); ?></td>
                        <td>
                          <?php if ($row->status == 'sent'): ?>
                            <span class="label label-success">Sent</span>
                          <?php else: ?>
                            <span class="label label-danger">Failed</span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo $row->created_at; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No emails sent yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ===== TAB 2 — RECIPIENTS (WHO'S WHO) ===== -->
    <div class="col-md-9 current-tab <?php echo $get_animate; ?>" id="nc_recipients" style="display:none;">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-users"></i> Notification Recipients — Directory</h3>
        </div>
        <div class="box-body">
          <div class="box-block">
            <p class="text-muted">
              Override email addresses for notification roles. Leave blank to use the employee's default email from the directory.
            </p>

            <?php $attributes = array('name' => 'nc_recipients_form', 'id' => 'nc_recipients_form', 'autocomplete' => 'off'); ?>
            <?php $hidden = array('type' => 'notification_recipients'); ?>
            <?php echo form_open('admin/settings/notification_recipients_save', $attributes, $hidden); ?>

            <div class="table-responsive">
              <table class="table table-bordered" id="nc_recipients_table">
                <thead>
                  <tr>
                    <th>Role</th>
                    <th>Scope</th>
                    <th>Default (from directory)</th>
                    <th>Override Email</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Global roles from overrides table -->
                  <?php
                  $role_labels = array(
                    'ceo'           => 'CEO / Director',
                    'hr_manager'    => 'HR Manager',
                    'company_admin' => 'Company Admin',
                  );
                  $default_emails = array(
                    'ceo'           => 'henry.njoroge@stalis.co.ke',
                    'hr_manager'    => 'lilian.maina@stalis.co.ke',
                    'company_admin' => 'softwareadmin@stalis.co.ke',
                  );
                  foreach ($role_labels as $rtype => $rlabel):
                    $override_email = '';
                    $scope_id = 0;
                    foreach ($overrides as $ov) {
                      if ($ov->role_type == $rtype && $ov->scope_id == 0) {
                        $override_email = $ov->override_email;
                        $scope_id = $ov->scope_id;
                        break;
                      }
                    }
                  ?>
                  <tr>
                    <td>
                      <strong><?php echo $rlabel; ?></strong>
                      <input type="hidden" name="role_type[]" value="<?php echo $rtype; ?>">
                      <input type="hidden" name="scope_id[]" value="0">
                    </td>
                    <td>All companies</td>
                    <td class="text-muted">
                      <i class="fa fa-lock"></i>
                      <?php echo htmlspecialchars($default_emails[$rtype]); ?>
                    </td>
                    <td>
                      <input class="form-control nc-override-input" name="override_email[]" type="email"
                             value="<?php echo htmlspecialchars($override_email); ?>"
                             placeholder="Leave blank for default">
                    </td>
                    <td>
                      <?php if (!empty($override_email)): ?>
                        <span class="label label-info">Overridden</span>
                      <?php else: ?>
                        <span class="label label-default">Default</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>

                  <!-- Department heads -->
                  <?php foreach ($dept_heads as $dh): ?>
                    <?php
                    $override_email = '';
                    foreach ($overrides as $ov) {
                      if ($ov->role_type == 'department_head' && $ov->scope_id == $dh->department_id) {
                        $override_email = $ov->override_email;
                        break;
                      }
                    }
                    $default_email = !empty($dh->email) ? $dh->email : '(no email on file)';
                    ?>
                    <tr>
                      <td>
                        <strong>Dept Head</strong>
                        <input type="hidden" name="role_type[]" value="department_head">
                        <input type="hidden" name="scope_id[]" value="<?php echo $dh->department_id; ?>">
                      </td>
                      <td><?php echo htmlspecialchars($dh->department_name); ?></td>
                      <td class="text-muted">
                        <i class="fa fa-lock"></i>
                        <?php echo htmlspecialchars($default_email); ?>
                        <?php if (empty($dh->email)): ?>
                          <span class="label label-warning" title="Dangling head — employee may be deleted">⚠ Dangling</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <input class="form-control nc-override-input" name="override_email[]" type="email"
                               value="<?php echo htmlspecialchars($override_email); ?>"
                               placeholder="Leave blank for default">
                      </td>
                      <td>
                        <?php if (!empty($override_email)): ?>
                          <span class="label label-info">Overridden</span>
                        <?php else: ?>
                          <span class="label label-default">Default</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="form-actions box-footer">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-check-square-o"></i> Save Recipients
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== TAB 3 — ROUTING RULES ===== -->
    <div class="col-md-9 current-tab <?php echo $get_animate; ?>" id="nc_routing" style="display:none;">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-random"></i> Notification Routing Rules</h3>
        </div>
        <div class="box-body">
          <div class="box-block">
            <p class="text-muted">
              Configure which roles receive notifications for each module + event combination.
              Check the roles that should be notified. Toggle the switch to enable/disable.
            </p>

            <?php $attributes = array('name' => 'nc_routing_form', 'id' => 'nc_routing_form', 'autocomplete' => 'off'); ?>
            <?php $hidden = array('type' => 'notification_routing'); ?>
            <?php echo form_open('admin/settings/notification_routing_save', $attributes, $hidden); ?>

            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="nc_routing_table">
                <thead>
                  <tr>
                    <th>Module</th>
                    <th>Event</th>
                    <th>Employee</th>
                    <th>Dept Head</th>
                    <th>HR</th>
                    <th>CEO</th>
                    <th>Admin</th>
                    <th>Enabled</th>
                    <th>Preview</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $all_roles = array('employee', 'department_head', 'hr_manager', 'ceo', 'company_admin');
                  $module_labels = array(
                    'leave'         => '<i class="fa fa-calendar"></i> Leave',
                    'ticket'        => '<i class="fa fa-ticket"></i> Ticket',
                    'project'       => '<i class="fa fa-folder-open"></i> Project',
                    'announcement'  => '<i class="fa fa-bullhorn"></i> Announcement',
                    'award'         => '<i class="fa fa-trophy"></i> Award',
                    'task'          => '<i class="fa fa-tasks"></i> Task',
                    'payslip'       => '<i class="fa fa-money"></i> Payslip',
                  );
                  $event_labels = array(
                    'submitted'       => 'Submitted',
                    'first_approved'  => 'First Approved',
                    'second_approved' => 'Second Approved',
                    'rejected'        => 'Rejected',
                    'reconciled'      => 'Reconciled',
                    'created'         => 'Created',
                    'assigned'        => 'Assigned',
                    'replied'         => 'Replied',
                    'closed'          => 'Closed',
                    'published'       => 'Published',
                    'given'           => 'Given',
                    'generated'       => 'Generated',
                  );
                  $cur_module = '';
                  foreach ($rules as $rule):
                    $active_roles = array_map('trim', explode(',', $rule->notify_roles));
                  ?>
                  <tr>
                    <td>
                      <input type="hidden" name="module[]" value="<?php echo htmlspecialchars($rule->module); ?>">
                      <?php
                      echo isset($module_labels[$rule->module]) ? $module_labels[$rule->module] : htmlspecialchars($rule->module);
                      ?>
                    </td>
                    <td>
                      <input type="hidden" name="event[]" value="<?php echo htmlspecialchars($rule->event); ?>">
                      <?php
                      echo isset($event_labels[$rule->event]) ? $event_labels[$rule->event] : htmlspecialchars($rule->event);
                      ?>
                    </td>
                    <input type="hidden" name="notify_roles[]" class="nc-roles-hidden"
                           value="<?php echo htmlspecialchars($rule->notify_roles); ?>"
                           data-rule="<?php echo htmlspecialchars($rule->module . '.' . $rule->event); ?>">
                    <?php foreach ($all_roles as $r): ?>
                    <td class="text-center">
                      <input type="checkbox" class="nc-role-check"
                             data-rule="<?php echo htmlspecialchars($rule->module . '.' . $rule->event); ?>"
                             data-role="<?php echo $r; ?>"
                             <?php if (in_array($r, $active_roles)) echo 'checked'; ?>>
                    </td>
                    <?php endforeach; ?>
                    <td class="text-center">
                      <input type="hidden" name="enabled[]" value="0">
                      <input type="checkbox" class="nc-enabled-check" name="enabled[]"
                             value="1" <?php if ($rule->enabled) echo 'checked'; ?>>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-xs btn-info nc-preview-btn"
                              data-module="<?php echo htmlspecialchars($rule->module); ?>"
                              data-event="<?php echo htmlspecialchars($rule->event); ?>">
                        <i class="fa fa-eye"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="form-actions box-footer">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-check-square-o"></i> Save Routing Rules
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
(function(){
  var sidebar = document.getElementById('nc_sidebar');
  if (!sidebar) return;
  var col = sidebar.parentElement;
  var topOffset = 20;
  function onScroll() {
    var rect = col.getBoundingClientRect();
    if (rect.top <= topOffset) {
      sidebar.style.position = 'fixed';
      sidebar.style.top = topOffset + 'px';
      sidebar.style.width = col.offsetWidth + 'px';
      sidebar.style.zIndex = '10';
    } else {
      sidebar.style.position = '';
      sidebar.style.top = '';
      sidebar.style.width = '';
      sidebar.style.zIndex = '';
    }
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  window.addEventListener('resize', function(){
    if (sidebar.style.position === 'fixed') {
      sidebar.style.width = col.offsetWidth + 'px';
    }
  });
})();
</script>

<!-- Preview Recipients Modal -->
<style>
#nc_preview_modal .modal-dialog {
  display: flex;
  align-items: center;
  min-height: calc(100vh - 100px);
  margin-top: 50px;
}
#nc_preview_modal .modal-content {
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
</style>
<div class="modal fade" id="nc_preview_modal" tabindex="-1" role="dialog" aria-labelledby="nc_preview_modal_label">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius:8px;overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:15px 20px;">
        <h5 class="modal-title" id="nc_preview_modal_label" style="color:#fff;font-weight:600;"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.8;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding:20px;"></div>
      <div class="modal-footer" style="border-top:1px solid #f0f0f0;padding:12px 20px;">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
