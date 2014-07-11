<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET;?>">
		<title><?php echo $lang["Work flow process designer"];?></title>
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/base.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/common.css?<?php echo VERHASH;?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL;?>/css/iefix.css?<?php echo VERHASH;?>">
		<![endif]-->
		<!-- private css -->
		<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href='<?php echo STATICURL;?>/js/lib/Select2/select2.css?<?php echo VERHASH;?>' />
		<link rel="stylesheet" href='<?php echo STATICURL;?>/js/lib/zTree/css/ibos/ibos.css?<?php echo VERHASH;?>' />
	</head>
	<body class="wf-designer ibbody">
		<div class="wf-designer-header">
			<h1><?php echo $lang["Flow process designer"];?> - <?php echo $flowName;?></h1>
			<a href="javascript:;" title="<?php echo $lang["Printing process flow diagram"];?>" class="cbtn o-print-bk" data-click="printProcess"></a>
			<a href="javascript:;" title="<?php echo $lang["Close designer"];?>" class="cbtn o-close-bk" data-click="closeDesinger"></a>
		</div>
		<div class="wf-designer-body">
			<div class="wf-designer-mainer">
				<div class="btn-toolbar fill-sn">
					<button type="button" class="btn btn-primary pull-left" data-click="addStep"><?php echo $lang["Add process"];?></button>
					<button type="button" class="btn pull-left" data-click="removeSelectedStep"><?php echo $lang["Del process"];?></button>
					<div class="btn-group">				
						<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><?php echo $lang["More"];?></button>
						<ul class="dropdown-menu">
							<li><a href="javascript:;" data-click="clearAllConnect"><?php echo $lang["Remove all links"];?></a></li>
							<li><a href="javascript:;" data-click="reloadFlow"><?php echo $lang["Reload"];?></a></li>
						</ul>
					</div>
				</div>
				<div class="alert" id="wf_alert">
					<i class="bulb"></i>
					<?php echo $lang["Flow design tip"];?>
				</div>
				<div class="wf-designer-canvas" id="wf_designer_canvas"></div>
			</div>
			<div class="wf-designer-sidebar">
				<div id="wf_step_info" class="wf-step-infobar"><img src="<?php echo $assetUrl;?>/image/flowdesignerdesc.png?<?php echo VERHASH;?>" /></div>
				<div class="fill">
					<button type="button" id="submit_btn" data-loading-text="<?php echo $lang["In submit"];?>..." data-toggle="button" autocomplete="off" class="btn btn-block btn-large btn-primary" data-click="saveFlow"><?php echo $lang["Flow design save"];?></button>
				</div>
			</div>
		</div>
		<div id="step_context_menu" class="hide">
			<ul class="dropdown-menu show">
				<li><a href="javascript:;" id="step_context_basic"><?php echo $lang["Base info"];?></a></li>
				<li><a href="javascript:;" id="step_context_field"><?php echo $lang["Form fields"];?></a></li>
				<li><a href="javascript:;" id="step_context_handler"><?php echo $lang["Handle role"];?></a></li>
				<li><a href="javascript:;" id="step_context_condition"><?php echo $lang["Transfer conditions"];?></a></li>
				<li class="divider"></li>
				<li><a href="javascript:;" id="step_context_del"><?php echo $lang["Del process"];?></a></li>
			</ul>
		</div>
		<div id="common_context_menu" class="hide">
			<ul class="dropdown-menu show">
				<li><a href="javascript:;" id="common_context_addstep"><?php echo $lang["Add process"];?></a></li>
				<li><a href="javascript:;" id="common_context_reload"><?php echo $lang["Reload"];?></a></li>
				<li><a href="javascript:;" id="common_context_save"><?php echo $lang["Save flow"];?></a></li>
			</ul>
		</div>
		<script>
			var PAGE_PARAM = {};
			var G = {
				SITE_URL: '<?php echo Ibos::app()->setting->get("siteurl");?>',
				STATIC_URL: '<?php echo STATICURL;?>',
				formHash: '<?php echo FORMHASH;?>'
			};
		</script>
		<script type="text/template" id="tpl_flow_add">
			<form action="post">
				<table id="flow_add_table" class="table table-striped table-head-condensed table-head-inverse mbz" style="width: 350px;">
					<thead>
						<tr>
							<th width="40"><?php echo $lang["Serial number"];?></th>
							<th width="200"><?php echo $lang["Process name"];?></th>
							<th width="60"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="3">
								<span class="xcr ilsep">*</span><?php echo $lang["The serial number cannot be repeated"];?>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<a href="javascript:;" data-type="addRow"><i class="o-plus cbtn"></i><?php echo $lang["Add one item"];?></a>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3">
								<label class="checkbox mbz">
									<input type="checkbox" id="link_by_index"><?php echo $lang["Link by index"];?>
								</label>
							</td>
						</tr>
					</tfoot>
				</table>
			</form>
		</script>
		<script type="text/template" id="tpl_flow_add_row">
			<tr>
				<td><input type="text" name="index" value="<%=index%>"></td>
				<td><input type="text" name="name"></td>
				<td><a href="javascript:;" data-type="removeRow" class="o-trash cbtn"></a></td>
			</tr>
		</script>
		<script src="<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>"></script>
		<script src='<?php echo STATICURL;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/jsPlumb/jquery.jsPlumb-1.5.3.js?<?php echo VERHASH;?>'></script>

		<script src='<?php echo STATICURL;?>/js/lib/Select2/select2.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/zTree/jquery.ztree.all-3.5.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo FileUtil::fileName("data/org.js");?>?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/app/ibos.userSelect.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/jquery.contextmenu.r2.packed.js?<?php echo VERHASH;?>'></script>
		<script>Ibos.app.setPageParam('flowId', <?php echo $flowId;?>)</script>
		<script src='<?php echo $assetUrl;?>/js/wfsetup.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/wfdesigner.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/processDesign.js?<?php echo VERHASH;?>'></script>
	</body>
</html>