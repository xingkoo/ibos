<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class='ct ctform'>
			<form id="user_form" action="<?php echo $this->createUrl( 'user/add' ); ?>" method="post" class="form-horizontal">
				<fieldset>
					<legend><?php echo $lang['Add user']; ?></legend>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['User name']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="username" id="username"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Password']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="password" id="password"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Real name']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="realname" id="realname"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Mobile']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="mobile" id="mobile"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Email']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="email" id="email"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Gender']; ?></label>
						<div class="controls span6">
							<label class="radio radio-inline">
								<input type="radio" name="gender" value="1" checked="checked"><?php echo $lang['Male']; ?>
							</label>
							&nbsp;&nbsp;
							<label class="radio radio-inline">
								<input type="radio" name="gender" value="0"><?php echo $lang['Female']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Jobnumber']; ?></label>
						<div class="controls span4">
							<input type="text" name="jobnumber" id="jobnumber"/>
						</div>
					</div>
					<div class="control-group" id="supervisor_intro">
						<label class="control-label"><?php echo $lang['Direct leader']; ?></label>
						<div class="controls span8">
							<input type="text" name="upuid" data-toggle="userSelect" id="user_supervisor" value="<?php echo $manager ?>">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo $lang['Department']; ?><br>
							<a class="display_auxiliary" id="auxiliary_dept_intro" data-target="#auxiliary_dept_wrap" href="javascript:void(0);">(<?php echo $lang['Specify an ancillary department']; ?>)</a>
						</label>
						<div class="controls span8">
							<input type="text" name="deptid" id="user_department" value="<?php echo $deptid ?>" />
						</div>
					</div>
					<div id="auxiliary_dept_wrap" class="control-group" style="display: none;">
						<label class="control-label">
							<?php echo $lang['Ancillary department']; ?>
						</label>
						<div class="controls span8">
							<input type="text" name="auxiliarydept" id="auxiliary_department">
						</div>
					</div>
					<div class="control-group" id="position_intro">
						<label class="control-label">
							<?php echo $lang['Position']; ?><br>
							<a class="display_auxiliary" data-target="#auxiliary_pos_wrap" href="javascript:void(0);">(指定辅助岗位)</a>
						</label>
						<div class="controls span8">
							<input type="text" name="positionid" id="user_position">
						</div>
					</div>
					<div id="auxiliary_pos_wrap" class="control-group" style="display: none;">
						<label class="control-label"><?php echo $lang['Ancillary position']; ?></label>
						<div class="controls span8">
							<input type="text" name="auxiliarypos" id="auxiliary_position">
						</div>
					</div>
					<div class="control-group" id="account_status_intro">
						<label class="control-label"><?php echo $lang['Account status']; ?></label>
						<div class="controls span8">
							<label class="radio radio-inline">
								<input type="radio" value="0" checked name="status">
								<?php echo $lang['Enabled']; ?>
							</label>
							<label class="radio radio-inline">
								<input type="radio" value="1" name="status">
								<?php echo $lang['Lock']; ?>
							</label>
							<label class="radio radio-inline">
								<input type="radio" value="2" name="status">
								<?php echo $lang['Disabled']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Send sms remind']; ?></label>
						<div class="controls">
							<input type="checkbox" name="sendremind" value="1" data-toggle="switch">
						</div>
					</div>
					<div id="submit_bar" class="clearfix">
						<button type="button" onclick='history.go(-1);' class="btn btn-large btn-submit pull-left"><?php echo $lang['Return']; ?></button>
						<div class='pull-right'>
							<button type="submit" name='userSubmit' class="btn btn-large btn-submit btn-primary"><?php echo $lang['Submit']; ?></button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<script>
	Ibos.app.setPageParam({
		'minPasswordLength': "<?php echo $passwordLength ?>",
		'maxPasswordLength': 32,
		'passwordRegex': "<?php echo $preg ?>"
	});
</script>
<script src='<?php echo STATICURL; ?>/js/lib/introjs/intro.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_user_add.js?<?php echo VERHASH; ?>'></script>
