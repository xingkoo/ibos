<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class='ct ctform'>
			<form id="user_form" action="<?php echo $this->createUrl( 'user/edit' ); ?>" method="post" class="form-horizontal">
				<fieldset>
					<legend><?php echo $lang['Edit user']; ?></legend>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['User name']; ?></label>
						<div class="controls span6">
							<div class="controls-content"><?php echo $user['username']; ?></div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Password']; ?></label>
						<div class="controls span6">
							<input type="text" name="password" id="password" placeholder="<?php echo $lang['Empty then does not change']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Real name']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="realname" id="realname" value="<?php echo $user['realname']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Mobile']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="mobile" id="mobile" value="<?php echo $user['mobile']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Email']; ?><span class="xcr">*</span></label>
						<div class="controls span6">
							<input type="text" name="email" id="email" value="<?php echo $user['email']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Gender']; ?></label>
						<div class="controls span6">
							<label class="radio radio-inline">
								<input type="radio" name="gender" value="1" <?php if ( $user['gender'] == '1' ): ?>checked<?php endif; ?>><?php echo $lang['Male']; ?>
							</label>
							&nbsp;&nbsp;
							<label class="radio radio-inline">
								<input type="radio" name="gender" value="0" <?php if ( $user['gender'] == '0' ): ?>checked<?php endif; ?>><?php echo $lang['Female']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Jobnumber']; ?></label>
						<div class="controls span4">
							<input type="text" name="jobnumber" value="<?php echo $user['jobnumber']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Direct leader']; ?></label>
						<div class="controls span8">
							<input type="text" name="upuid" value="<?php echo StringUtil::wrapId( $user['upuid'] ); ?>" data-toggle="userSelect" id="user_supervisor">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo $lang['Department']; ?><br>
							<a class="display_auxiliary" data-target="#auxiliary_dept_wrap" href="javascript:void(0);">(<?php echo $lang['Specify an ancillary department']; ?>)</a>
						</label>
						<div class="controls span8">
							<input type="text" name="deptid" value="<?php echo StringUtil::wrapId( $user['deptid'], 'd' ); ?>" id="user_department" />
						</div>
					</div>
					<div id="auxiliary_dept_wrap" class="control-group" style="display: none;">
						<label class="control-label">
							<?php echo $lang['Ancillary department']; ?>
						</label>
						<div class="controls span8">
							<input type="text" name="auxiliarydept" value="<?php echo StringUtil::wrapId($user["auxiliarydept"], 'd' ); ?>" id="auxiliary_department">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo $lang['Position']; ?><br>
							<a class="display_auxiliary" data-target="#auxiliary_pos_wrap" href="javascript:void(0);">(指定辅助岗位)</a>
						</label>
						<div class="controls span8">
							<input type="text" name="positionid" value="<?php echo StringUtil::wrapId( $user['positionid'], 'p' ); ?>" id="user_position">
						</div>
					</div>
					<div id="auxiliary_pos_wrap" class="control-group" style="display: none;">
						<label class="control-label"><?php echo $lang['Ancillary position']; ?></label>
						<div class="controls span8">
							<input type="text" name="auxiliarypos" value="<?php echo StringUtil::wrapId( $user['auxiliarypos'], 'p' ); ?>" id="auxiliary_position">
						</div>
					</div>
					<?php if ( $user['uid'] !== '1' ): ?>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Account status']; ?></label>
							<div class="controls span8">
								<label class="radio radio-inline">
									<input type="radio" value="0" <?php if ( $user['status'] == 0 ): ?>checked<?php endif; ?> name="status">
									<?php echo $lang['Enabled']; ?>
								</label>
								<label class="radio radio-inline">
									<input type="radio" value="1" <?php if ( $user['status'] == 1 ): ?>checked<?php endif; ?> name="status">
									<?php echo $lang['Lock']; ?>
								</label>
								<label class="radio radio-inline">
									<input type="radio" value="2" <?php if ( $user['status'] == 2 ): ?>checked<?php endif; ?> name="status">
									<?php echo $lang['Disabled']; ?>
								</label>
							</div>
						</div>
					<?php endif; ?>
					<div id="submit_bar" class="clearfix">
						<button type="button" onclick='history.go(-1);' class="btn btn-large btn-submit pull-left"><?php echo $lang['Return']; ?></button>
						<div class='pull-right'>
							<button type="submit" name='userSubmit' class="btn btn-large btn-submit btn-primary"><?php echo $lang['Save']; ?></button>
						</div>
					</div>
					<input type="hidden" name="uid" value="<?php echo $user['uid']; ?>" />
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
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_user_edit.js?<?php echo VERHASH; ?>'></script>
