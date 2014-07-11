<div class="fill" style="width: 650px;" id="auth_rule_setting">
	<form class="form-horizontal form-narrow" id="manager_form">
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Licensed to"];?></label>
			<div class="controls">
				<input type="text" name="users" id="auth_for">
				<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
				<input type="hidden" name="flowid" value="<?php echo $flowId;?>" />
				<div></div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Control type"];?></label>
			<div class="controls" id="manager_options">
				<label class="radio radio-inline">
					<input type="radio" name="type" checked value="0" /><?php echo $lang["Flow purv all"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv manager desc"];?>">
					<input type="radio" name="type" value="1" /><?php echo $lang["Flow purv manager"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv monitoring desc"];?>">
					<input type="radio" name="type" value="2"><?php echo $lang["Flow purv monitoring"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv search desc"];?>">
					<input type="radio" name="type" value="3" /><?php echo $lang["Flow purv search"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv edit desc"];?>">
					<input type="radio" name="type" value="4" /><?php echo $lang["Flow purv edit"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv review desc"];?>">
					<input type="radio" name="type" value="5" /><?php echo $lang["Flow purv review"];?>
				</label>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Control scope"];?></label>
			<div class="controls">
				<select name="scope" id="manage_scope" class="span6">
					<option value="selforg"><?php echo $lang["Flow scope selforg"];?></option>
					<option value="alldept" selected><?php echo $lang["Flow scope alldept"];?></option>
					<option value="selfdept"><?php echo $lang["Flow scope selfdept"];?></option>
					<option value="selfdeptall"><?php echo $lang["Flow scope selfdeptall"];?></option>
					<option value="custom"><?php echo $lang["Flow scope custom"];?></option>
				</select>
			</div>
		</div>
		<div class="control-group" id="custom_department" style="display:none;">
			<div class="controls">
				<input type="text" name="scopedept" id="custom_department_select">
			</div>
		</div>
	</form>
</div>
<script>
	(function() {
		var $wrap = $("#auth_rule_setting");
		$wrap.find(".radio input").label();
		$("#auth_for").userSelect({
			data: Ibos.data.get(),
			type: "all"
		});

		$("#custom_department_select").userSelect({
			data: Ibos.data.get('department'),
			type: "department"
		});


		$("#manager_options").find('label').tooltip({container: $wrap});
		var $customDepartment = $("#custom_department");
		$("#manage_scope").on("change", function() {
			$customDepartment.toggle(this.value === "custom");
		});
	})();
</script>
<style>.tooltip{ z-index: 10000}</style>
