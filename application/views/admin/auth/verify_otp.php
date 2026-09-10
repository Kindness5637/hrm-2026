<?php $system = $this->Xin_model->read_setting_info(1);?>
<?php $company = $this->Xin_model->read_company_setting_info(1);?>
<?php $favicon = base_url().'uploads/logo/favicon/fav.png'?>
<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Verify OTP - <?php echo $company[0]->company_name;?></title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="icon" type="image/x-icon" href="<?php echo $favicon;?>">
<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/theme_assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/theme_assets/bower_components/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/theme_assets/dist/css/AdminLTE.min.css">
<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/vendor/toastr/toastr.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<style type="text/css">
.login-page {
  background-image: url('<?php echo base_url();?>pexels-architecture-1868667.jpg') !important;
  background-position: center center !important;
  background-size: cover !important;
  background-repeat: no-repeat !important;
}
.login-page::before {
  content: '';
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.7);
  pointer-events: none;
  z-index: 0;
}
.login-box {
  position: relative;
  z-index: 1;
}
.otp-input {
  text-align: center;
  font-size: 28px;
  font-weight: bold;
  letter-spacing: 12px;
  height: 56px;
  width: 100%;
}
</style>
</head>
<body class="hold-transition login-page">
<img id="hrload-img" src="<?php echo base_url()?>skin/img/loading.gif" style="display:none; z-index:87896969; float:right; margin-right:25px; margin-top:0;">

<div class="login-box animated fadeInDownBig" style="background: rgba(255, 255, 255, 0.7); border-radius: 12px; box-shadow: 0 8px 40px rgba(0,0,0,0.18); padding: 40px 50px; max-width: 520px; width: 100%; flex-shrink: 0;">
  <div class="login-box-body">
    <div class="login-logo">
      <?php if(!empty($company[0]->sign_in_logo_data)):?>
      <img src="data:image/png;base64,<?php echo $company[0]->sign_in_logo_data;?>" alt="logo" style="max-width: 280px; height: auto; margin-bottom: 10px;">
      <?php else:?>
      <img src="<?php echo base_url();?>uploads/logo/signin/<?php echo $company[0]->sign_in_logo;?>" alt="logo" style="max-width: 280px; height: auto; margin-bottom: 10px;">
      <?php endif;?>
    </div>
    <p class="login-box-msg">Enter the 6-digit OTP sent to your email</p>

    <?php echo form_open('admin/auth/verify_otp_check', 'id="otp-form" autocomplete="off"');?>
    <input type="hidden" name="email" value="<?php echo $email;?>">
    <div class="form-group">
      <input type="text" name="otp" class="form-control otp-input" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="off" inputmode="numeric">
    </div>
    <div class="row" style="margin-top: 20px;">
      <div class="col-xs-6">
        <a href="<?php echo site_url('');?>" class="btn btn-default btn-block btn-flat" style="height: 48px;"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
      <div class="col-xs-6">
        <button type="submit" class="btn btn-primary btn-block btn-flat save" style="height: 48px;"><i class="fa fa-check"></i> Verify OTP</button>
      </div>
    </div>
    <?php echo form_close();?>
    <hr>
    <div class="lockscreen-footer text-center">
      &copy; <?php echo date('Y');?> <?php echo $system[0]->footer_text;?>
    </div>
  </div>
</div>

<script src="<?php echo base_url();?>skin/hrsale_assets/theme_assets/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo base_url();?>skin/hrsale_assets/theme_assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>skin/hrsale_assets/vendor/toastr/toastr.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  // Auto-focus and auto-submit when 6 digits entered
  $('input[name="otp"]').on('input', function(){
    this.value = this.value.replace(/[^0-9]/g, '');
    if(this.value.length === 6){
      $('#otp-form').submit();
    }
  }).focus();

  $('#otp-form').submit(function(e){
    e.preventDefault();
    var otp = $('input[name="otp"]').val();
    if(otp.length !== 6){
      toastr.error('Please enter a 6-digit OTP');
      return;
    }
    $('.save').prop('disabled', true);
    $('#hrload-img').show();
    $.ajax({
      type: "POST",
      url: $(this).attr('action'),
      data: $(this).serialize(),
      dataType: 'json',
      success: function(JSON){
        $('#hrload-img').hide();
        $('.save').prop('disabled', false);
        if(JSON.error != ''){
          toastr.error(JSON.error);
          $('input[name="otp"]').val('').focus();
        } else {
          toastr.success(JSON.result);
          setTimeout(function(){
            window.location = '<?php echo site_url('admin/auth/reset_password?email='.urlencode($email));?>';
          }, 1500);
        }
      },
      error: function(){
        $('#hrload-img').hide();
        $('.save').prop('disabled', false);
        toastr.error('An error occurred. Please try again.');
      }
    });
  });
});
</script>
</body>
</html>
