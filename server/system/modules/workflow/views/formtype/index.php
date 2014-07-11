<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->widget("IWWfListSidebar", array("category" => $category, "catId" => $catId), true);?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<a href="javascript:;" data-click="addForm" class="btn btn-primary"><?php echo $lang["New"];?></a>
					<a href="javascript:;" data-click="importForm" data-param='{"catid":"<?php echo $this->catid;?>"}' class="btn"><?php echo $lang["Form import new"];?></a>
					<a href="javascript:;" data-click="exportForm" class="btn"><?php echo $lang["Export"];?></a>
					<a href="javascript:;" data-click="delForm" class="btn"><?php echo $lang["Delete"];?></a>
				</div>
				<form action="<?php echo $this->createUrl("formtype/index");?>" method="get">
					<div class="search search-config pull-right span4">
						<input type="hidden" name="r" value="workflow/formtype/index" />
						<input type="text" name="keyword" placeholder="<?php echo $lang["Form search tip"];?>" id="mn_search" nofocus>
						<input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
						<a href="javascript:;"></a>
					</div>
				</form>
			</div>
			<div class="page-list-mainer">
				<table class="table table-hover wf-form-table">
					<thead>
						<tr>
							<th width="20">
								<label class="checkbox">
									<input type="checkbox" data-name="form">
								</label>
							</th>
							<th><?php echo $lang["Form name"];?></th>
							<th><?php echo $lang["Subordinate process"];?></th>
							<th width="270"><?php echo $lang["Subordinate departments"];?></th>
						</tr>
					</thead>
					<tbody id="wf_form_list"></tbody>
				</table>
			</div>
			<?php if ($limit < $count) : ?>
				<div id="page" class="page-list-footer" style="display: none;">
					<div class="page-num-select">
						<div class="btn-group dropup">
							<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>">
								<i class="o-setup"></i> <?php echo $lang["Each page"];?> <?php echo $pageSize;?> <i class="caret"></i>
							</a>
							<ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo $this->createUrl("formtype/index", array("catid" => $this->catid));?>" >
								<li data-value="10" <?php if ($pageSize == 10) echo 'class="active"';?>><a href="javascript:;"><?php echo $lang["Each page"];?> 10</a></li>
								<li data-value="20" <?php if ($pageSize == 20) echo 'class="active"';?>><a href="javascript:;"><?php echo $lang["Each page"];?> 20</a></li>
								<li data-value="30" <?php if ($pageSize == 30) echo 'class="active"';?>><a href="javascript:;"><?php echo $lang["Each page"];?> 30</a></li>
								<li data-value="40" <?php if ($pageSize == 40) echo 'class="active"';?>><a href="javascript:;"><?php echo $lang["Each page"];?> 40</a></li>
								<li data-value="50" <?php if ($pageSize == 50) echo 'class="active"';?>><a href="javascript:;"><?php echo $lang["Each page"];?> 50</a></li>
							</ul>
						</div>
					</div>
					<div class="pull-right"><?php $this->widget("IWPage", array("pages" => $pages));?></div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div id="dialog_edition" style="display: none;">
		<form action="#" method="post" style="width: 460px;">
			<select class="span5" id="edition_select"></select>
			<button type="button" class="btn" data-edition="preview"><?php echo $lang["Review"];?></button>	
			<button type="button" class="btn" data-edition="restore"><?php echo $lang["Restore version"];?></button>	
			<button type="button" class="btn" data-edition="del"><?php echo $lang["Delete"];?></button>	
		</form>
	</div>
	<div id="dialog_form_setting" style="width: 400px; display:none;">
		<form action="" class="form-horizontal form-compact" id="form_setting_form">
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Form name"];?></label>
				<div class="controls">
					<input type="text" name="formname" id="form_setting_name">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Form category"];?></label>
				<div class="controls">
					<select name="catid" id="form_setting_catelog">
						<?php foreach ($this->category as $cat ) : ?>
							<option <?php if ($cat["catid"] == $this->catid) echo "selected";?> value="<?php echo $cat["catid"];?>"><?php echo $cat["name"];?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Subordinate departments"];?></label>
				<div class="controls">
					<input type="text" name="deptid" id="form_setting_department" />
					<p class="help-block"><?php echo $lang["Belongs dept desc"];?></p>
				</div>
			</div>
			<input type="hidden" name="formid" id="form_setting_id">
			<input type="hidden" name="formhash" value='<?php echo FORMHASH;?>'>
		</form>
	</div>
</div>

<script type="text/template" id="tpl_form_item">
	<tr data-id="<%=id%>">
	<td>
	<label class="checkbox">
	<input type="checkbox" name="form" value="<%=id%>">
	</label>
	</td>
	<td>
	<a href="javascript:;" data-form="edit" data-param='{"formid": "<%=id%>"}' class="xcm dye"><%=name%></a>
	</td>
	<td>
	<span class="fss"><%=flow%></span>
	</td>
	<td>
	<span class="wf-form-dept"><%=department%></span>
	<div class="wf-form-op">
	<button type="button" class="btn btn-mini" data-form="edit" data-param='{"formid":"<%=id%>"}'><?php echo $lang["Edit attribute"];?></button>
	<button type="button" class="btn btn-mini" data-form="design" data-param='{"formid": "<%=id%>"}'><?php echo $lang["Design form"];?></button>
	<button type="button" class="btn btn-mini" data-form="import" data-param='{"formid": <%=id%>,"catid":"<?php echo $this->catid;?>"}'><?php echo $lang["Import"];?></button>
	<button type="button" class="btn btn-mini" data-form="edition" data-param='{"id": "<%=id%>"}'><?php echo $lang["History version"];?></button>
	</div>
	</td>
	</tr>
</script>
<script type="text/template" id="tpl_version">
	<form action="#" method="post" style="width: 460px;">
	<select class="span5">
	<% for(var i = 0; i < options.length; i++){ %>
	<option value="<%=options[i].value%>"><%=options[i].text%></option>
	<% } %>
	</select>
	<button type="button" class="btn" data-edition="preview"><?php echo $lang["Review"];?></button>	
	<button type="button" class="btn" data-edition="restore"><?php echo $lang["Restore version"];?></button>	
	<button type="button" class="btn" data-edition="del"><?php echo $lang["Delete"];?></button>	
	</form>
</script>
<script src='<?php echo $assetUrl;?>/js/wfformsetup.js?<?php echo VERHASH;?>'></script>
<script>
(function() {
	//搜索
	Ibos.search.init();
	Ibos.search.disableAdvance();

	$(document).ready(function() {
		// 保存成功提示
		if (U.getCookie('form_save_success') == 1) {
			Ui.tip(U.lang('SAVE_SUCEESS'), 'success');
			U.setCookie('form_save_success', '', -1);
		}
		// 保存并设计，弹出设计窗口
		if (U.getCookie('form_save_success') == 2) {
			Wfs.formItem.design({formid: U.getCookie('formid')});
			U.setCookie('form_save_success', '', -1);
			U.setCookie('formid', '', -1);
		}
		// 列表加载数据
		$('.page-list-mainer').waiting(U.lang('READ_INFO'), "mini");
		$.get('<?php echo $this->createUrl("formtype/index", array("inajax" => 1, "limit" => $limit, "offset" => $offset, "keyword" => $keyword, "catid" => $this->catid));?>', function(data) {
			$('.page-list-mainer').stopWaiting();
			if (data.count > 0) {
				$('#page').show();
				Wfs.formList.addItem(data.list);
			} else {
				$('#page').hide();
				$('.page-list-mainer').html('<div class="no-data-tip"></div>');
			}
		}, 'json');
	});
})();
</script>