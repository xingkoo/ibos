<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET;?>">
		<title><?php echo $lang["New preview"];?></title>
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/base.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/common.css?<?php echo VERHASH;?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL;?>/css/iefix.css?<?php echo VERHASH;?>">
		<![endif]-->
		<!-- private css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/zTree/css/ibos/ibos.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/Select2/select2.css?<?php echo VERHASH;?>" />
		<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/preview.css?<?php echo VERHASH;?>" />
		<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/wf_form.css?<?php echo VERHASH;?>">
	</head>
	<body>
		<div class="main">
            <!--表单 开始-->
            <div class="main-content">
                <div class="mc-top clearfix">
                    <div class="pull-left">
                        <span class="mc-title"><?php echo $lang["Form"];?></span>
                    </div>
                </div>
                <div class="form-area">
                    <div class="clearfix">
                        <span class="pull-left corner-1"></span>
                        <span class="pull-right corner-2"></span>
                    </div>
                    <div class="form-content grid-container" id="container">
						<?php echo $printmodel;?>
                    </div>
                    <span class="pull-left corner-3"></span>
                    <span class="pull-right corner-4"></span>
                </div>
            </div>
            <!--表单 结束-->
			<!--流程图 开始-->
			<div class="main-content">
                <div class="mc-top clearfix">
                    <div class="pull-left">
                        <span class="mc-title"><?php echo $lang["Steps and flow chart"];?></span>
                    </div>
                </div>
                <div class="wf-designer-body">
					<div class="wf-designer-mainer" style="margin-right: 0;">
						<div class="alert" style="margin-bottom: 0;" id="wf_alert">
							<i class="bulb"></i>
							<strong class="ilsep"><?php echo $lang["Color identification"];?></strong>
							<span class="ilsep">
								<i class="lump-processing"></i>
								<?php echo $lang["Perform"];?>
							</span>
							<span class="ilsep">
								<i class="lump-finish"></i>
								<?php echo $lang["Form finish"];?>
							</span>
							<span class="ilsep">
								<i class="lump-sub"></i>
								<?php echo $lang["Subflow"];?>
							</span>
							<span class="ilsep">
								<i class="lump-inactive"></i>
								<?php echo $lang["Not receive"];?>
							</span>
							<span class="ilsep">
								<i class="lump-suspend"></i>
								<?php echo $lang["In delay"];?>
							</span>
						</div>
						<div class="wf-designer-canvas wf-designer-view scroll" id="wf_designer_canvas">
						</div>
					</div>
				</div>
            </div>
			<!--流程图 结束-->
        </div>
		<script>
			var G = {
				SITE_URL: '<?php echo Ibos::app()->setting->get("siteurl");?>',
				STATIC_URL: '<?php echo STATICURL;?>',
				cookiePre: '<?php echo Ibos::app()->setting->get("config/cookie/cookiepre");?>',
				cookiePath: '<?php echo Ibos::app()->setting->get("config/cookie/cookiepath");?>',
				cookieDomain: '<?php echo Ibos::app()->setting->get("config/cookie/cookiedomain");?>',
				formHash: '<?php echo FORMHASH;?>'
			};
		</script>
		<script src="<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>"></script>
		<script src='<?php echo STATICURL;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/qrcode/jquery.qrcode.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/zTree/jquery.ztree.all-3.5.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/Select2/select2.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo FileUtil::fileName("data/org.js");?>?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/app/ibos.userSelect.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/wfcomponents.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/jsPlumb/jquery.jsPlumb-1.5.3.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/wfdesigner.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/processView.js?<?php echo VERHASH;?>'></script>
		<script>
			$(function() {
				var wfc = new wfComponents($('#container'));
				wfc.initItem();
				var flowId = <?php echo $flowID;?>

				$(function() {
					var url = Ibos.app.url('workflow/preview/getprcs', {flowid: flowId, runid: 0});
					$.get(url, function(data) {
						processView.set(data);
					}, 'json');
				});
			});
		</script>
	</body>
</html>