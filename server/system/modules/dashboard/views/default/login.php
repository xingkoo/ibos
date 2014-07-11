<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET; ?>">
		<title><?php echo $lang['Admin login']; ?></title>
		<link rel="shortcut icon" href="<?php echo STATICURL; ?>/image/favicon.ico?<?php echo VERHASH; ?>">
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>">
		<![endif]-->
		<!-- private css -->
		<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/login.css?<?php echo VERHASH; ?>">
	</head>
	<body>
		<div class="full">
			<div class="bg">
				<img id="bg" src="<?php echo $assetUrl; ?>/image/bg_body.jpg" alt="" style="display:none;">
			</div>
			<div class="mainer">
				<div class="login-wrap">
					<h1 class="logo">
						<img src="<?php echo $assetUrl; ?>/image/logo.png" width="300" height="80" alt="IBOS">
					</h1>
					<div class="login shadow radius well well-white">
						<form id="loginForm" method="post" action="<?php echo Yii::app()->urlManager->createUrl( 'dashboard/default/login' ); ?>">
							<div class="login-group">
								<label><?php echo $lang['Account']; ?></label>
								<div class="input-group">
									<span class="input-group-addon addon-icon input-large">
										<i class="glyphicon-user"></i>
									</span>
									<input type="text" <?php if ( !empty( $userName ) ): ?> value="<?php echo $userName; ?>" readonly<?php endif; ?> name="username" id="login_user" class="input-large">
								</div>
							</div>
							<div class="login-group">
								<label><?php echo $lang['Password']; ?></label>
								<div class="input-group">
									<span class="input-group-addon addon-icon input-large">
										<i class="glyphicon-lock"></i>
									</span>
									<input type="password" class="input-large" id="login_pass" name="password" >
								</div>
							</div>
							<div class="login-group login-btn">
								<button id="submit-btn" type="submit" data-loading-text="<?php echo $lang['Logging']; ?>..." 
										autocomplete="off" name="loginsubmit" class="btn btn-primary btn-large btn-block">
											<?php echo $lang['Login']; ?>
								</button>
							</div>
							<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
						</form>
					</div>
				</div>
			</div>
			<div class="footer">
				Powered by <strong>IBOS <?php echo VERSION; ?> <?php echo VERSION_DATE; ?></strong>
			</div>
		</div>
		<!-- load js -->
		<script src="<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo STATICURL; ?>/js/build/base.min.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo STATICURL; ?>/js/build/common.min.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo $assetUrl; ?>/js/login.js?<?php echo VERHASH; ?>"></script>
		<script>
			(function() {
				setTimeout(function() {
					if ($('#login_user').val() == '') {
						$("#login_user").focus();
					} else {
						$('#login_pass').focus();
					}
				}, 0);
				// 此处不能使用 "==="， Ie8 下最顶层window !== window.top
				if (window != window.top) {
					var requertUrl = window.location.href;
					var loginUrl = $("#loginForm").attr('action');
					if (requertUrl.indexOf(loginUrl) > -1) {
						// 如果子框架访问的是后台登陆页，顶层框架页面跳转到后台登陆页
						window.top.location.href = requertUrl;
					}
				}
			})()
		</script>
	</body>
</html>