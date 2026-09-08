<?php $result = get_sub_departments($department_id);?>

<div class="form-group" id="subdepartment_ajax">
  <label for="designation"><?php echo $this->lang->line('xin_hr_sub_department');?></label>
  <select class="select2" data-plugin="select_hrm" data-placeholder="<?php echo $this->lang->line('xin_select_department');?>" name="subdepartment_id" id="aj_subdepartment" >
    <option value=""></option>
    <?php foreach($result as $deparment) {?>
    <option value="<?php echo $deparment->sub_department_id?>"><?php echo $deparment->department_name?></option>
    <?php } ?>
  </select>
</div>
