$(document).ready(function() {
var xin_table = $('#xin_table').dataTable({
	"bDestroy": true,
	"ajax": {
		url : base_url+"/employees_list/",
		type : 'GET'
	},
	"fnDrawCallback": function(settings){
	$('[data-toggle="tooltip"]').tooltip();          
	}
});

$('[data-plugin="select_hrm"]').select2($(this).attr('data-options'));
$('[data-plugin="select_hrm"]').select2({ width:'100%' }); 

// Date
$('.date_of_birth').datepicker({
  changeMonth: true,
  changeYear: true,
  dateFormat:'yy-mm-dd',
  yearRange: '1940:' + new Date().getFullYear()
});
// Date
$('.date_of_joining').datepicker({
  changeMonth: true,
  changeYear: true,
  dateFormat:'yy-mm-dd',
  yearRange: '1940:' + ':' + new Date().getFullYear()
});

/* Delete data */
$("#delete_record").submit(function(e){
	/*Form Submit*/
	e.preventDefault();
	var obj = $(this), action = obj.attr('name');
	$.ajax({
		type: "POST",
		url: e.target.action,
		data: obj.serialize()+"&is_ajax=2&form="+action,
		cache: false,
		success: function (JSON) {
			if (JSON.error != '') {
				toastr.error(JSON.error);
				$('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
			} else {
				$('.delete-modal').modal('toggle');
				xin_table.api().ajax.reload(function(){ 
					toastr.success(JSON.result);
					$('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
				}, true);							
			}
		}
	});
});

// edit
$('.edit-modal-data').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget);
	var warning_id = button.data('warning_id');
	var modal = $(this);
$.ajax({
	url : base_url+"/read/",
	type: "GET",
	data: 'jd=1&is_ajax=1&mode=modal&data=warning&warning_id='+warning_id,
	success: function (response) {
		if(response) {
			$("#ajax_modal").html(response);
		}
	}
	});
});
$("#ihr_report").submit(function(e){
	/*Form Submit*/
		e.preventDefault();
		 var xin_table2 = $('#xin_table').dataTable({
			"bDestroy": true,
			"ajax": {
				url : site_url+"employees/employees_list/?ihr=true&company_id="+$('#filter_company').val()+"&location_id="+$('#filter_location').val()+"&department_id="+$('#filter_department').val()+"&designation_id="+$('#filter_designation').val(),
				type : 'GET'
			},
			"fnDrawCallback": function(settings){
				$('[data-toggle="tooltip"]').tooltip();          
			}
		});
		xin_table2.api().ajax.reload(function(){
			toastr.success(request_submitted);
		}, true);
});

// Helper: reinit select2 on a container and auto-select if only 1 option
function reinitSelect2(container) {
	var sel = container.find('select[data-plugin="select_hrm"]');
	if (sel.length) {
		sel.select2({ width:'100%' });
		// Auto-select if only 1 real option
		var opts = sel.find('option[value!=""]');
		if (opts.length === 1) {
			sel.val(opts.val()).trigger('change');
		}
	}
}

// === CHAIN: Company → Location → Department → Designation ===

// Company change → load locations
jQuery("#aj_company").change(function(){
	var cid = jQuery(this).val();
	if (!cid) return;
	jQuery.get(escapeHtmlSecure(base_url+"/get_company_elocations/"+cid), function(data, status){
		jQuery('#location_ajax').html(data);
		reinitSelect2(jQuery('#location_ajax'));
	});
});

// Location change → load departments (event delegation for dynamically loaded content)
jQuery(document).on('change', '#aj_location_id', function(){
	var lid = jQuery(this).val();
	if (!lid) return;
	jQuery.get(base_url+"/get_location_departments/"+lid, function(data, status){
		jQuery('#department_ajax').html(data);
		reinitSelect2(jQuery('#department_ajax'));
	});
});

// Department change → load designations (event delegation)
jQuery(document).on('change', '#aj_subdepartments', function(){
	var did = jQuery(this).val();
	if (!did) return;
	jQuery.get(base_url+"/is_designation/"+did, function(data, status){
		jQuery('#designation_ajax').html(data);
		reinitSelect2(jQuery('#designation_ajax'));
	});
});

// Sub-department change → load designations (event delegation)
jQuery(document).on('change', '#aj_subdepartment', function(){
	var did = jQuery(this).val();
	if (!did) return;
	jQuery.get(base_url+"/designation/"+did, function(data, status){
		jQuery('#designation_ajax').html(data);
		reinitSelect2(jQuery('#designation_ajax'));
	});
});

// Filter company change
jQuery("#filter_company").change(function(){
	if(jQuery(this).val() == 0){
		jQuery('#filter_location').prop('selectedIndex', 0);	
		jQuery('#filter_department').prop('selectedIndex', 0);
		jQuery('#filter_designation').prop('selectedIndex', 0);
	}	
	jQuery.get(escapeHtmlSecure(site_url+"employees/filter_company_flocations/"+jQuery(this).val()), function(data, status){
		jQuery('#location_ajaxflt').html(data);
	});
});

/* Add data */
$("#xin-form").submit(function(e){
	var fd = new FormData(this);
	var obj = $(this), action = obj.attr('name');
	fd.append("is_ajax", 1);
	fd.append("add_type", 'employee');
	fd.append("form", action);
	e.preventDefault();
	$('.icon-spinner3').show();
	$('.save').prop('disabled', true);
	$.ajax({
		url: e.target.action,
		type: "POST",
		data:  fd,
		contentType: false,
		cache: false,
		processData:false,
		success: function(JSON)
		{
			if (JSON.error != '') {
				toastr.error(JSON.error);
				$('.icon-spinner3').hide();
				$('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
				$('.save').prop('disabled', false);
			} else {
				$('.icon-spinner3').hide();
				xin_table.api().ajax.reload(function(){ 
					toastr.success(JSON.result);
					$('input[name="csrf_hrsale"]').val(JSON.csrf_hash);
				}, true);
				$('.add-form').removeClass('in');
				$('.select2-selection__rendered').html('--Select--');
				$('#xin-form')[0].reset();
				$('.save').prop('disabled', false);
			}
		},
		error: function() 
		{
			toastr.error('An error occurred.');
			$('.icon-spinner3').hide();
			$('.save').prop('disabled', false);
		} 	        
   });
 });
$( document ).on( "click", ".delete", function() {
$('input[name=_token]').val($(this).data('record-id'));
$('#delete_record').attr('action',base_url+'/delete/'+$(this).data('record-id'));
});
});
