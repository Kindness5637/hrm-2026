$(document).ready(function(){

  // Init select2
  $('[data-plugin="select_hrm"]').select2({ width: '100%' });

  /* ===========================================================
   * Tab switching (matches the existing settings.js pattern)
   * =========================================================== */
  $(".nav-tabs-link").click(function(){
    var profile_id = $(this).data('setting');
    var profile_block = $(this).data('profile-block');
    $('.list-group-item').removeClass('active');
    $('.current-tab').hide();
    $('#setting_'+profile_id).addClass('active');
    $('#'+profile_block).show();
  });

  /* ===========================================================
   * TAB 1 — SMTP fields toggle
   * =========================================================== */
  $('#nc_email_type').change(function(){
    if ($(this).val() == 'smtp') {
      $('#nc_smtp_fields').show();
    } else {
      $('#nc_smtp_fields').hide();
    }
  });

  /* ===========================================================
   * TAB 1 — Save SMTP config
   * =========================================================== */
  $('#nc_smtp_form').submit(function(e){
    e.preventDefault();
    var obj = $(this);
    $('.save').prop('disabled', true);
    $.ajax({
      type: 'POST',
      url: e.target.action,
      data: obj.serialize() + '&is_ajax=1&type=notification_smtp&form=nc_smtp_form',
      cache: false,
      success: function(JSON){
        if (JSON.error != '') {
          toastr.error(JSON.error);
        } else {
          toastr.success(JSON.result);
        }
        $('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
        $('.save').prop('disabled', false);
      }
    });
  });

  /* ===========================================================
   * TAB 1 — Test send
   * =========================================================== */
  $('#nc_test_send_btn').click(function(){
    var email = $('#nc_test_email').val();
    if (!email) {
      toastr.error('Enter an email address first.');
      return;
    }
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    $('#nc_test_result').html('');
    $.ajax({
      type: 'POST',
      url: site_url + 'settings/notification_test_send/',
      data: { test_email: email, csrf_hrsale: $('input[name="csrf_hrsale"]').val() },
      cache: false,
      success: function(JSON){
        $('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Test');
        if (JSON.error != '') {
          toastr.error(JSON.error);
          $('#nc_test_result').html('<div class="alert alert-danger">' + JSON.error + '</div>');
        } else {
          toastr.success(JSON.result);
          $('#nc_test_result').html('<div class="alert alert-success">' + JSON.result + '</div>');
        }
      }
    });
  });

  /* ===========================================================
   * TAB 2 — Save recipients
   * =========================================================== */
  $('#nc_recipients_form').submit(function(e){
    e.preventDefault();
    var obj = $(this);
    $('.save').prop('disabled', true);
    $.ajax({
      type: 'POST',
      url: e.target.action,
      data: obj.serialize() + '&is_ajax=1&type=notification_recipients&form=nc_recipients_form',
      cache: false,
      success: function(JSON){
        if (JSON.error != '') {
          toastr.error(JSON.error);
        } else {
          toastr.success(JSON.result);
        }
        $('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
        $('.save').prop('disabled', false);
      }
    });
  });

  /* ===========================================================
   * TAB 3 — Role checkbox aggregation
   * Each row has individual role checkboxes; we aggregate them
   * into a comma-separated value before form submit.
   * =========================================================== */
  // When a role checkbox is toggled, update the hidden input for that row
  $('.nc-role-check').change(function(){
    var rule = $(this).data('rule');
    var roles = [];
    $('.nc-role-check[data-rule="'+rule+'"]').each(function(){
      if ($(this).is(':checked')) {
        roles.push($(this).data('role'));
      }
    });
    $('.nc-roles-hidden[data-rule="'+rule+'"]').val(roles.join(','));
  });

  // Before form submit, ensure all hidden inputs are up to date
  $('#nc_routing_form').submit(function(e){
    $('.nc-role-check').each(function(){
      $(this).trigger('change');
    });

    e.preventDefault();
    var obj = $(this);
    $('.save').prop('disabled', true);
    $.ajax({
      type: 'POST',
      url: e.target.action,
      data: obj.serialize() + '&is_ajax=1&type=notification_routing&form=nc_routing_form',
      cache: false,
      success: function(JSON){
        if (JSON.error != '') {
          toastr.error(JSON.error);
        } else {
          toastr.success(JSON.result);
        }
        $('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
        $('.save').prop('disabled', false);
      }
    });
  });

  /* ===========================================================
   * TAB 3 — Preview recipients
   * =========================================================== */
  $('.nc-preview-btn').click(function(){
    var module = $(this).data('module');
    var event = $(this).data('event');
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.ajax({
      type: 'POST',
      url: site_url + 'settings/notification_preview/',
      data: {
        module: module,
        event: event,
        csrf_hrsale: $('input[name="csrf_hrsale"]').val()
      },
      cache: false,
      success: function(JSON){
        $('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
        btn.prop('disabled', false).html('<i class="fa fa-eye"></i>');
        if (JSON.error != '') {
          toastr.error(JSON.error);
        } else {
          var emails = JSON.data.length > 0 ? JSON.data.join(', ') : '(no recipients configured)';
          toastr.info(JSON.result);
          // Show in a temporary alert
          var msg = '<strong>' + module + ' → ' + event + '</strong><br>' + emails;
          alert(msg);
        }
      }
    });
  });

});
