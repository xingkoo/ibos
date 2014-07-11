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
		<script> window.html5 = { elements: "ic" }</script>
		<script src="<?php echo $assetUrl;?>/js/html5shiv.js?<?php echo VERHASH;?>"></script>
	<![endif]-->
	<!-- private css -->
	<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>">
	<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
	<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/formdesigner.css?<?php echo VERHASH;?>">
	<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/wf_form.css?<?php echo VERHASH;?>">
</head>
<body class="wf-designer ibbody">
	<div class="wf-designer-recyclebin" id="wf_designer_recyclebin">回收站</div>
	<div class="wf-designer-header">
		<h1><?php echo $lang["You are editing"];?>：<?php echo $form["formname"];?></h1>
	</div>
	<form action="<?php echo $this->createUrl("formtype/design");?>" method="post" id="save_form">
		<div class="wf-designer-body form-simple-designer">
			<div class="form-designer-mainer">
				<div class="form-designer-tip"><?php echo $lang["Form designer tip"];?></div>
				<div class="fill">
					<!-- 表单设计器容器 -->
					<div>
						<div class="grid-container  form-horizontal" id="form_design"><?php echo $form["printmodel"];?></div>
					</div>
				</div>
			</div>
			<div class="form-designer-sidebar" id="fd_sidebar">
				<a href="<?php echo $this->createUrl("formtype/design", array("mode" => "advanced", "formid" => $form["formid"]));?>" class="btn btn-small pull-right btn-advanced-mode">高级模式</a>
				<ul class="nav nav-skid">
					<li class="active"><a href="#common_controls"  data-toggle="tab"><?php echo $lang["Common"];?></a></li>
					<li><a href="#advanced_controls"  data-toggle="tab"><?php echo $lang["Extension"];?></a></li>
				</ul>
				<!-- 布局设置 -->
				<div class="fill bdbs">
					<div class="grid-row-alternative clearfix">
						<!-- 单栏 -->
						<div class="grid-row">
							<div class="grid-preview"><i class="o-column-one"></i>单栏</div>
							<div class="grid-view">
								<div class="row">
									<div class="span12 grid-column"></div>
								</div>
							</div>
						</div>
						<!-- 左侧 -->
						<div class="grid-row">
							<div class="grid-preview"><i class="o-column-left"></i>左侧</div>
							<div class="grid-view">
								<div class="row">
									<div class="span8 grid-column"></div>
									<div class="span4 grid-column"></div>
								</div>
							</div>
						</div>
						<!-- 右侧 -->
						<div class="grid-row">
							<div class="grid-preview"><i class="o-column-right"></i>右侧</div>
							<div class="grid-view">
								<div class="row">
									<div class="span4 grid-column"></div>
									<div class="span8 grid-column"></div>
								</div>
							</div>
						</div>
						<!-- 两栏 -->
						<div class="grid-row">
							<div class="grid-preview"><i class="o-column-two"></i>两栏</div>
							<div class="grid-view">
								<div class="row">
									<div class="span6 grid-column"></div>
									<div class="span6 grid-column"></div>
								</div>
							</div>
						</div>
						<!-- 三栏 -->
						<div class="grid-row">
							<div class="grid-preview"><i class="o-column-three"></i>三栏</div>
							<div class="grid-view">
								<div class="row">
									<div class="span4 grid-column"></div>
									<div class="span4 grid-column"></div>
									<div class="span4 grid-column"></div>
								</div>
							</div>
						</div>
						<!-- 分界线 -->
						<div class="grid-row">
							<div class="grid-preview"><i class="o-column-hr"></i>分界线</div>
							<div class="grid-view">
								<div class="row">
									<div class="span12 grid-column grid-nodrop">
										<hr>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- 表单控件 -->
				<div class="fill">
					<div class="tab-content mb">
						<!-- 常用控件 -->
						<div id="common_controls" class="tab-pane active">
							<div class="grid-box-alternative">
								<!-- 标签文本 -->
								<div class="grid-box">
									<ic data-type="label" data-text="<?php echo $lang["Label text"];?>" data-width="200" data-font-size="14" data-align="left"
									 data-bold="0" data-italic="0" data-underline="0" data-color="#82939E" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-label"></i> <?php echo $lang["Label text"];?></label>
											<div class="ctrl-preview ctrl-label-preview"></div>
										</div>
										<div class="grid-view">
											<span style="display: inline-block; width: 200px; font-size: 14px; color: #82939E;"><?php echo $lang["Label text"];?></span>
										</div>
									</ic>
								</div>
								<!-- 单行输入框 -->
								<div class="grid-box">
									<ic data-type="text" data-title="<?php echo $lang["Single input box"];?>" data-width="200" data-value=" " data-hide="0" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-text"></i> <?php echo $lang["Single input box"];?></label>
											<div class="ctrl-preview ctrl-text-preview"></div>
										</div>
										<div class="grid-view">
											<input type="text" style="width: 200px;">
										</div>
									</ic>
								</div>
								<!-- 多行输入框 -->
								<div class="grid-box">
									<ic data-type="textarea" data-title="<?php echo $lang["Multiline input box"];?>" data-width="200" data-rows="5"
									data-value=" " data-rich="0" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-textarea"></i> <?php echo $lang["Multiline input box"];?></label>
											<div class="ctrl-preview ctrl-textarea-preview"></div>
										</div>
										<div class="grid-view">
											<textarea rows="5" style="width: 200px;"></textarea>
										</div>
									</ic>
								</div>
								<!-- 下拉菜单 -->
								<div class="grid-box">
									<ic data-type="select" data-title="<?php echo $lang["Dropdown menu"];?>" data-width="200" 
									data-check=" " data-field=" " data-fields=" " data-size="1" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-select"></i> <?php echo $lang["Dropdown menu"];?></label>
											<div class="ctrl-preview ctrl-select-preview"></div>
										</div>
										<div class="grid-view">
											<select style="width: 200px;"></select>
										</div>
									</ic>
								</div>
								<!-- 单选框 -->
								<div class="grid-box">
									<ic data-type="radio" data-title="<?php echo $lang["Radio buttons"];?>" data-radio-field="1`2`3" data-radio-check="1" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-radio"></i> <?php echo $lang["Radio buttons"];?></label>
											<div class="ctrl-preview ctrl-radio-preview"></div>
										</div>
										<div class="grid-view">
											<label><input type="radio" value="1" name="radio" checked>1</label>
											<label><input type="radio" value="2" name="radio">2</label>
											<label><input type="radio" value="3" name="radio">3</label>
										</div>
									</ic>
								</div>
								<!-- 复选框 -->
								<div class="grid-box">
									<ic data-type="checkbox" data-title="<?php echo $lang["Check box"];?>" data-checkbox-checked="0" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-checkbox"></i> <?php echo $lang["Check box"];?></label>
											<div class="ctrl-preview ctrl-checkbox-preview"></div>
										</div>
										<div class="grid-view">
											<label title="<?php echo $lang["Check box"];?>"><input type="checkbox"></label>
										</div>
									</ic>
								</div>
								<!-- 部门人员控件 -->
								<div class="grid-box">
									<ic data-type="user" data-title="<?php echo $lang["User control"];?>" data-select-type="user" data-width="200" data-single="0" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-user"></i> <?php echo $lang["User control"];?></label>
											<div class="ctrl-preview ctrl-user-preview"></div>
										</div>
										<div class="grid-view">
											<input type="text" value="<?php echo $lang["User control"];?>" style="width: 200px;">
										</div>
									</ic>
								</div>
								<!-- 日历控件 -->
								<div class="grid-box">
									<ic data-type="date" data-title="<?php echo $lang["Calendar control"];?>" data-width="200" data-date-format="yyyy-mm-dd hh:ii:ss" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-date"></i> <?php echo $lang["Calendar control"];?></label>
											<div class="ctrl-preview ctrl-date-preview"></div>
										</div>
										<div class="grid-view">
											<input type="text" value="<?php echo $lang["Calendar control"];?>" style="width: 200px;">
										</div>
									</ic>
								</div>
								<!-- 宏控件 -->
								<div class="grid-box">
									<ic data-type="auto" data-title="<?php echo $lang["Macro control"];?>" data-field="sys_date" data-src=" " data-width="200" data-hide="0" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-auto"></i> <?php echo $lang["Macro control"];?></label>
											<div class="ctrl-preview ctrl-auto-preview"></div>
										</div>
										<div class="grid-view">
											<span class="fake-auto" style="width:200px;"><?php echo $lang["Macro control"];?></span>
										</div>
									</ic>
								</div>
								<!-- 计算控件 -->
								<div class="grid-box">
									<ic data-type="calc"  data-title="<?php echo $lang["Calculate control"];?>" data-prec="4" 
									data-width="200" data-value=" "  contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-calc"></i> <?php echo $lang["Calculate control"];?></label>
											<div class="ctrl-preview ctrl-calc-preview"></div>
										</div>
										<div class="grid-view">
											<span class="fake-calc" style="width: 200px;"><?php echo $lang["Calculate control"];?></span>
										</div>
									</ic>
								</div>
								<!-- 列表控件 -->
								<div class="grid-box">
									<ic data-type="listview" data-title="<?php echo $lang["List control"];?>" data-lv-title=" "
									 data-lv-coltype=" " data-lv-colvalue=" " data-lv-sum=" " contenteditable="false">
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
														<td></td>
														<td></td>
														<td></td>
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
									<ic data-type="sign" data-title="<?php echo $lang["Signature control"];?>" data-sign-type="1,1" data-sign-color=" " data-sign-field=" " contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-sign"></i> <?php echo $lang["Signature control"];?></label>
											<div class="ctrl-preview ctrl-sign-preview"></div>
										</div>
										<div class="grid-view">
											<button type="button" class="btn" style="margin-right:5px;">签章</button>
											<button type="button" class="btn">手写</button>
										</div>
									</ic>
								</div>
								<!-- 进度条控件 -->
								<div class="grid-box">
									<ic data-type="progressbar" data-title="<?php echo $lang["Progressbar control"];?>" data-width="200" data-step="5" data-progress-style="primary" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-progress"></i> <?php echo $lang["Progressbar control"];?></label>
											<div class="ctrl-preview ctrl-progress-preview"></div>
										</div>
										<div class="grid-view">
											<span class="progress progress-striped active" style="display:inline-block; width: 200px;" title="<?php echo $lang["Progressbar control"];?>"><span class="progress-bar progress-bar-primary" style="display:inline-block;width:5%"><input type="hidden" /></span></span>
										</div>
									</ic>
								</div>
								<!-- 图片上传控件 -->
								<div class="grid-box">
									<ic data-type="imgupload" data-title="<?php echo $lang["Image upload control"];?>" data-width="200" data-height="200" contenteditable="false">
										<div class="grid-preview">
											<label><i class="o-ctrl-image"></i> <?php echo $lang["Image upload control"];?></label>
											<div class="ctrl-preview ctrl-image-preview"></div>
										</div>
										<div class="grid-view">
											<span class="fake-imgupload" title="<?php echo $lang["Image upload control"];?>" style="width:200px;height:200px"><input type="hidden"></span>
										</div>
									</ic>
								</div>
								<!-- 二维码控件 -->
								<div class="grid-box">
									<ic data-type="qrcode" data-title="二维码控件" data-qrcode-type="text" data-value=" " data-qrcode-size="1"  contenteditable="false">
											<div class="grid-preview">
												<label><i class="o-ctrl-qrcode"></i> <?php echo $lang["Qr code control"];?></label>
												<div class="ctrl-preview ctrl-qrcode-preview"></div>
											</div>
											<div class="grid-view">
												<span class="fake-qrcode"><input type="hidden"></span>
												<!-- <?php echo $lang["Qr code control"];?> -->
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
				formid: <?php echo $form["formid"];?>,
				dataId: <?php echo $form["itemmax"];?>
			};
		</script>
		<script src='<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/jquery.mousewheel.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/lib/qrcode/jquery.qrcode.min.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo STATICURL;?>/js/src/minieditor.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/formdesigner.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/spformdesigner.js?<?php echo VERHASH;?>'></script>
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