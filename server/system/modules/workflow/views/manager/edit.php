<div class="fill" style="width: 650px;" id="auth_rule_setting">
	<form class="form-horizontal form-narrow" id="manager_form">
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Licensed to"];?></label>
			<div class="controls">
				<input type="text" name="users" value="<?php echo $users;?>" id="auth_for">
				<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
				<input type="hidden" name="id" value="<?php echo $per["id"];?>" />
				<div></div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Control type"];?></label>
			<div class="controls" id="manager_options">
				<label class="radio radio-inline">
					<input type="radio" name="type" <?php if ($per["type"] == "0") echo "checked";?> value="0" /><?php echo $lang["Flow purv all"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv manager desc"];?>">
					<input type="radio" name="type" <?php if ($per["type"] == "1") echo "checked";?> value="1" /><?php echo $lang["Flow purv manager"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv monitoring desc"];?>">
					<input type="radio" name="type" <?php if ($per["type"] == "2") echo "checked";?> value="2"><?php echo $lang["Flow purv monitoring"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv search desc"];?>">
					<input type="radio" name="type" <?php if ($per["type"] == "3") echo "checked";?> value="3" /><?php echo $lang["Flow purv search"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv edit desc"];?>">
					<input type="radio" name="type" <?php if ($per["type"] == "4") echo "checked";?> value="4" /><?php echo $lang["Flow purv edit"];?>
				</label>
				<label class="radio radio-inline" title="<?php echo $lang["Flow purv review desc"];?>">
					<input type="radio" name="type" <?php if ($per["type"] == "5") echo "checked";?> value="5" /><?php echo $lang["Flow purv review"];?>
				</label>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Control scope"];?></label>
			<div class="controls">
				<select name="scope" id="manage_scope" class="span6">
					<option value="selforg" <?php if ($per["scope"] == "selforg") echo "selected";?> ><?php echo $lang["Flow scope selforg"];?></option>
					<option value="alldept" <?php if ($per["scope"] == "alldept") echo "selected";?> ><?php echo $lang["Flow scope alldept"];?></option>
					<option value="selfdept" <?php if ($per["scope"] == "selfdept") echo "selected";?> ><?php echo $lang["Flow scope selfdept"];?></option>
					<option value="selfdeptall" <?php if ($per["scope"] == "selfdeptall") echo "selected";?> ><?php echo $lang["Flow scope selfdeptall"];?></option>
					<option value="custom" <?php if ($custom) echo "selected";?> ><?php echo $lang["Flow scope custom"];?></option>
				</select>
			</div>
		</div>
		<div class="control-group" id="custom_department" <?php if (!$custom) echo ' style="display:none;"';?> >
			<div class="controls">
				<input type="text" name="scopedept" <?php if ($custom) : ?> value="<?php echo StringUtil::wrapId($per["scope"], "d");?>"<?php endif; ?> id="custom_department_select">
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