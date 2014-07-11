<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET;?>">
		<title><?php echo $lang["Form designer"];?></title>
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/base.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/common.css?<?php echo VERHASH;?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL;?>/css/iefix.css?<?php echo VERHASH;?>">
		<![endif]-->
		<!-- private css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/formdesigner.css?<?php echo VERHASH;?>">
		<script> document.createElement("ic")</script>
	</head>
	<body class="wf-designer ibbody">
		<div class="wf-designer-header">
			<h1><?php echo $lang["You are editing"];?>：<?php echo $form["formname"];?></h1>
		</div>
		<form action="<?php echo $this->createUrl("formtype/design");?>" method="post" id="save_form">
			<div class="wf-designer-body">
				<div class="form-designer-mainer clearfix">
					<!-- <div class="form-designer-tip"><?php echo $lang["Form designer tip"];?></div> -->
					<div>
						<!-- 编辑器 -->
						<div id="form_editor" class="form-editor">
							<script id="editor" name="content" type="text/plain"><?php echo $form["printmodel"];?></script>
						</div>
					</div>
				</div>
				<div class="form-designer-sidebar" id="fd_sidebar">
					<a href="<?php echo $this->createUrl("formtype/design", array("mode" => "simple", "formid" => $form["formid"]));?>" class="btn btn-small pull-right btn-advanced-mode"><?php echo $lang["Simple mode"];?></a>
					<ul class="nav nav-skid">
						<li class="active"><a href="#common_controls"  data-toggle="tab"><?php echo $lang["Common"];?></a></li>
						<li><a href="#advanced_controls"  data-toggle="tab"><?php echo $lang["Extension"];?></a></li>
					</ul>
					<!-- 表单控件 -->
					<div class="fill">
						<div class="tab-content mb">
							<!-- 常用控件 -->
							<div id="common_controls" class="tab-pane active">
								<div class="grid-box-alternative">
									<!-- 标签文本 -->
									<div class="grid-box">
										<ic data-type="label">
											<div class="grid-preview">
												<label><i class="o-ctrl-label"></i> <?php echo $lang["Label text"];?></label>
												<div class="ctrl-preview ctrl-label-preview"></div>
											</div>
											<div class="grid-view">
												<div class="control-group">
													<div class="label-control"><?php echo $lang["Label text"];?></div>
												</div>
											</div>
										</ic>
									</div>
									<!-- 单行输入框 -->
									<div class="grid-box">
										<ic data-type="text">
											<div class="grid-preview">
												<label><i class="o-ctrl-text"></i> <?php echo $lang["Single input box"];?></label>
												<div class="ctrl-preview ctrl-text-preview"></div>
											</div>
											<div class="grid-view">
												<input type="text">
											</div>
										</ic>
									</div>
									<!-- 多行文本框 -->
									<div class="grid-box">
										<ic data-type="textarea">
											<div class="grid-preview">
												<label><i class="o-ctrl-textarea"></i> <?php echo $lang["Multiline input box"];?></label>
												<div class="ctrl-preview ctrl-textarea-preview"></div>
											</div>
											<div class="grid-view">
												<textarea name="" id="" cols="20" rows="5"></textarea>
											</div>
										</ic>
									</div>
									<!-- 下拉菜单 -->
									<div class="grid-box">
										<ic data-type="select">
											<div class="grid-preview">
												<label><i class="o-ctrl-select"></i> <?php echo $lang["Dropdown menu"];?></label>
												<div class="ctrl-preview ctrl-select-preview"></div>
											</div>
											<div class="grid-view">
												<select id=""></select>
											</div>
										</ic>
									</div>
									<!-- 单选框 -->
									<div class="grid-box">
										<ic data-type="radio">
											<div class="grid-preview">
												<label><i class="o-ctrl-radio"></i> <?php echo $lang["Radio buttons"];?></label>
												<div class="ctrl-preview ctrl-radio-preview"></div>
											</div>
											<div class="grid-view">
												<label class="radio radio-inline"><input type="radio" name="">1</label>
												<label class="radio radio-inline"><input type="radio" name="">2</label>
												<label class="radio radio-inline"><input type="radio" name="">3</label>
											</div>
										</ic>
									</div>
									<!-- 复选框 -->
									<div class="grid-box">
										<ic data-type="checkbox">
											<div class="grid-preview">
												<label><i class="o-ctrl-checkbox"></i> <?php echo $lang["Check box"];?></label>
												<div class="ctrl-preview ctrl-checkbox-preview"></div>
											</div>
											<div class="grid-view">
												<label class="checkbox checkbox-inline"><input type="checkbox" name="">1</label>
												<label class="checkbox checkbox-inline"><input type="checkbox" name="">2</label>
												<label class="checkbox checkbox-inline"><input type="checkbox" name="">3</label>
											</div>
										</ic>
									</div>
									<!-- 部门人员控件 -->
									<div class="grid-box">
										<ic data-type="user">
											<div class="grid-preview">
												<label><i class="o-ctrl-user"></i> <?php echo $lang["User control"];?></label>
												<div class="ctrl-preview ctrl-user-preview"></div>
											</div>
											<div class="grid-view">
												<input type="text" value="<?php echo $lang["User control"];?>">
											</div>
										</ic>
									</div>
									<!-- 日历控件 -->
									<div class="grid-box">
										<ic data-type="date">
											<div class="grid-preview">
												<label><i class="o-ctrl-date"></i> <?php echo $lang["Calendar control"];?></label>
												<div class="ctrl-preview ctrl-date-preview"></div>
											</div>
											<div class="grid-view">
												<input type="text" value="<?php echo $lang["Calendar control"];?>">
											</div>
										</ic>
									</div>
									<!-- 宏控件 -->
									<div class="grid-box">
										<ic data-type="auto">
											<div class="grid-preview">
												<label><i class="o-ctrl-auto"></i> <?php echo $lang["Macro control"];?></label>
												<div class="ctrl-preview ctrl-auto-preview"></div>
											</div>
											<div class="grid-view">
												<div class="smart-control"><?php echo $lang["Macro control"];?></div>
											</div>
										</ic>
									</div>
									<!-- 计算控件 -->
									<div class="grid-box">
										<ic data-type="calc">
											<div class="grid-preview">
												<label><i class="o-ctrl-calc"></i> <?php echo $lang["Calculate control"];?></label>
												<div class="ctrl-preview ctrl-calc-preview"></div>
											</div>
											<div class="grid-view">
												<div class="smart-control"><?php echo $lang["Calculate control"];?></div>
											</div>
										</ic>
									</div>
									<!-- 列表控件 -->
									<div class="grid-box">
										<ic data-type="listview">
											<div class="grid-preview">
												<label><i class="o-ctrl-listview"></i> <?php echo $lang["List control"];?></label>
												<div class="ctrl-preview ctrl-listview-preview"></div>
											</div>
											<div class="grid-view">
												<table class="table table-bordered">
													<thead>
														<tr>
															<th>Title1</th>
															<th>Title2</th>
															<th>Title2</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>List1</td>
															<td>List2</td>
															<td>List3</td>
														</tr>
														<tr>
															<td>List4</td>
															<td>List5</td>
															<td>List6</td>
														</tr>
													</tbody>
												</table>
											</div>
										</ic>
									</div>
								</div>
							</div>
							<!-- 高级控件 -->
							<div id="advanced_controls" class="tab-pane">
								<div class="grid-box-alternative">
									<!-- 签章控件 -->
									<div class="grid-box">
										<ic data-type="sign">
											<div class="grid-preview">
												<label><i class="o-ctrl-sign"></i> <?php echo $lang["Signature control"];?></label>
												<div class="ctrl-preview ctrl-sign-preview"></div>
											</div>
											<div class="grid-view">
												<?php echo $lang["Signature control"];?>
											</div>
										</ic>
									</div>
									<!-- 进度条控件 -->
									<div class="grid-box">
										<ic data-type="progressbar">
											<div class="grid-preview">
												<label><i class="o-ctrl-progress"></i> <?php echo $lang["Progressbar control"];?></label>
												<div class="ctrl-preview ctrl-progress-preview"></div>
											</div>
											<div class="grid-view">
												<?php echo $lang["Progressbar control"];?>
											</div>
										</ic>
									</div>
									<!-- 图片上传控件 -->
									<div class="grid-box">
										<ic data-type="imgupload">
											<div class="grid-preview">
												<label><i class="o-ctrl-image"></i> <?php echo $lang["Image upload control"];?></label>
												<div class="ctrl-preview ctrl-image-preview"></div>
											</div>
											<div class="grid-view">
												<?php echo $lang["Image upload control"];?>
											</div>
										</ic>
									</div>
									<!-- 二维码控件 -->
									<div class="grid-box">
										<ic data-type="qrcode">
											<div class="grid-preview">
												<label><i class="o-ctrl-qrcode"></i> <?php echo $lang["Qr code control"];?></label>
												<div class="ctrl-preview ctrl-qrcode-preview"></div>
											</div>
											<div class="grid-view">
												<?php echo $lang["Qr code control"];?>
											</div>
										</ic>
									</div>
								</div>
							</div>
						</div>
						<div class="form-designer-opbar">
							<button type="button" class="btn btn-primary" data-action="saveForm"><?php echo $lang["Form save"];?></button>
							<button type="button" class="btn" data-action="previewForm"><?php echo $lang["Review"];?></button>
							<button type="button" class="btn" data-action="saveVersion"><?php echo $lang["Archive"];?></button>
							<button type="button" class="btn" data-action="viewVersion"><?php echo $lang["History version"];?></button>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="op" id="form_design_op" value="" />
			<input type="hidden" name="formid" value="<?php echo $formid;?>" />
			<input type="hidden" name="mode" value="<?php echo $mode;?>" />
			<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
		</form>
		<div id="dialog_edition" style="width: 400px; display: none;">
			<form class="form-horizontal form-compact" id="edition_form">
				<div class="control-group">
					<label class="control-label"><?php echo $lang["History version"];?></label>
					<div class="controls">
						<select id="edition_select"></select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls" >
						<a href="javascript:;" class="btn btn-small" data-edition="preview"><?php echo $lang["Review"];?></a>
						<a href="javascript:;" class="btn btn-small" data-edition="restore"><?php echo $lang["Restore"];?></a>
						<a href="javascript:;" class="btn btn-small" data-edition="remove"><?php echo $lang["Delete"];?></a>
						<!-- <button type="button"></button> -->
					</div>
				</div>
			</form>
		</div>
		<script>
			var G = {
				STATIC_URL: "<?php echo STATICURL;?>",
				ASSET_URL: "<?php echo $assetUrl;?>",
				SITE_URL: '<?php echo Ibos::app()->setting->get("siteurl");?>',
				formid: <?php echo $form["formid"];?>
			};
		</script>
		<script src='<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>
		<script src="<?php echo STATICURL;?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH;?>"></script>
		<script src="<?php echo STATICURL;?>/js/lib/ueditor/editor_all.js?<?php echo VERHASH;?>"></script>
		<script src="<?php echo STATICURL;?>/js/lib/ueditor/editor_formcontrols.js?<?php echo VERHASH;?>"></script>

		<script src='<?php echo $assetUrl;?>/js/formdesigner.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/adformdesigner.js?<?php echo VERHASH;?>'></script>
		<script>
			$(document).ready(function() {
				// 保存成功提示
				if (U.getCookie('form_op_save') == 1) {
					Ui.tip(U.lang('SAVE_SUCEESS'));
					U.setCookie('form_op_save', 0, -1);
				}
				if (U.getCookie('form_op_version') == 1) {
					Ui.tip(U.lang('WF.SAVE_VERSION_SUCEESS'));
					U.setCookie('form_op_version', 0, -1);
				}

			});
		</script>
	</body>
</html>