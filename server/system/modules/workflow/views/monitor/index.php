<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<!--右栏 开始-->
	<div class="mcr">
		<!--右栏头部标题 开始-->
		<div class="fill-nn bdbs">
			<span class="fsl xwb"><?php echo $lang["Work monitor search"];?></span>
		</div>
		<!--右栏头部标题 结束-->
		<!--右栏查询过滤 开始-->
		<div class="query-form">
			<form action="#" method="post" class="form-horizontal form-narrow">
				<div class="control-group"> 
					<label class="control-label"><?php echo $lang["Flow"];?></label>
					<div class="controls clearfix">
						<input type="text" class="span4" name="flowid" id="flow_choose" placeholder="<?php echo $lang["Select process"];?>" />
						<select name="usertype" class="form-control span2">
							<option value="opuser"><?php echo $lang["Host user"];?></option>
							<option value="beginuser"><?php echo $lang["Originator"];?></option>
						</select>
						<div class="dib span5 m-choose-person"><input type="text" name="toid" id="choose_person" placeholder="<?php echo $lang["Please select a staff"];?>"/></div>
					</div>
				</div>
				<div class="control-group"> 
					<label class="control-label"><?php echo $lang["Name"];?></label>
					<div class="controls clearfix">
						<input type="text" placeholder="<?php echo $lang["Enter run name"];?>" name="runname" class="span4" onfocus />
						<input type="text" placeholder="<?php echo $lang["Serial number"];?>" name="runid" class="span2" nofocus/>
						<button type="submit" class="btn btn-primary"><?php echo $lang["Search"];?></button>
					</div>
				</div>
				<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
			</form>
		</div>
		<!--右栏查询过滤 结束-->
		<div class="page-list clearfix">
			<!--右栏列表功能栏 开始-->
			<div class="chl page-list-header" id="monitor_function">
				<button type="button" data-click="batchDel" class="btn monitor-tr-delete"><?php echo $lang["Delete"];?></button>
			</div>
			<div class="page-list-mainer xcm">
				<?php if (!empty($list)) : ?>
					<table class="table table-hover table-striped table-monitor" id="table_monitor">
						<thead>
							<tr>
								<th width="16">
									<label class="checkbox">
										<input type="checkbox" data-name="id[]" />
									</label>
								</th>
								<th><?php echo $lang["Name"];?></th>
								<th width="20"></th>
								<th width="110"><?php echo $lang["Current host"];?></th>
								<th width="104"><?php echo $lang["Cur step"];?></th>
								<th width="170"><?php echo $lang["Used time"];?></th>
								<th width="20"></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($list as $val ) : ?>
								<tr id="list_tr_<?php echo $val["runid"];?>">
									<td>
										<label class="checkbox">
											<input type="checkbox" name="id[]" value="<?php echo $val["runid"];?>" />
										</label>
									</td>
									<td>
										<div class="com-list-name">
											<em class="text-nowrap"><a target="_blank" href="<?php echo $this->createUrl("preview/print", array("key" => $val["key"]));?>" class="xcm"><?php echo $val["runName"];?></a></em>
											<span class="fss tcm posa">[<?php echo $val["runid"];?>]<?php echo $val["typeName"];?></span>
										</div>
									</td>
									<td>
										<?php if (!empty($val["attachmentid"])) : ?>
											<?php
												$original_title = '<ul>';
												foreach ($val["attachdata"] as $attach ) {
													$original_title = '<li>'. $attach["filename"] . '</li>';
												}
											?>
											<div class="fill-mm q-attachment-area" data-placement="bottom" data-html="true" data-original-title="<?php echo CHtml::encode($original_title);?>" ?>
												<a href="javascript:void(0);" class="o-attachment"></a>
											</div>
										<?php endif; ?>
									</td>
									<td>
										<a data-toggle="usercard" data-param="uid=<?php echo $val["user"]["uid"];?>" href="<?php echo $val["user"]["space_url"];?>" class="avatar-circle" title="<?php echo $val["user"]["realname"];?>">
											<img src="<?php echo $val["user"]["avatar_middle"];?>" />
										</a>
										<span class="fss"><?php echo $val["user"]["realname"];?></span>
									</td>
									<td>
										<?php if ($val["type"] == "1") : ?>
											<span class="label"><?php echo $val["flowprocess"];?></span>
											<span class="fss dib step-text-nowrap">
												<a href="javascript:void(0);" data-click="viewFlow" data-param="{&quot;key&quot;: &quot;<?php echo $val["key"];?>&quot;}"><?php echo $val["stepname"];?></a>
											</span>
										<?php else : ?>
											<?php echo $val["stepname"];?>
										<?php endif; ?>
									</td>
									<td class="posr">
										<?php if ($val["processtime"] == 0) : ?>
											<span class="fss xcr"><?php echo $lang["Not receive"];?></span>
										<?php else : ?>
											<span class="fss xcgn"><?php echo $val["timestr"];?></span>
										<?php endif; ?>
										<div class="right-btnbar">
											<button type="button" data-click="turn" data-param="{&quot;key&quot;: &quot;<?php echo $val["key"];?>&quot;,&quot;type&quot;:&quot;<?php echo $val["type"];?>&quot;}" class="btn btn-mini"><?php echo $lang["Transfer"];?></button>
											<?php if ($val["freeother"] != 0) : ?>
												<button type="button" data-click="entrust" data-param="{&quot;key&quot;: &quot;<?php echo $val["key"];?>&quot;,&quot;manager&quot;:1}" class="btn btn-mini"><?php echo $lang["Entrust"];?></button>
											<?php endif; ?>
											<button type="button" data-click="end" data-param="{&quot;key&quot;: &quot;<?php echo $val["key"];?>&quot;}" class="btn btn-mini stop-flow"><?php echo $lang["End"];?></button>
										</div>
									</td>
									<td>
										<i class="<?php if ($val["focus"]) : ?>o-ck-attention<?php else : ?>o-tr-attention<?php endif; ?> pull-right" data-param="<?php echo $val["runid"];?>" data-click="focus"></i>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<!--右栏列表 结束-->
			<!--右栏底部功能栏 开始-->
			<?php if ($pages->getPageSize() < $pages->getItemCount()) : ?>
				<div class="page-list-footer">
					<div class="page-num-select">
						<div class="btn-group dropup">
							<?php $pageSize = $pages->getPageSize(); ?>
							<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("monitor/index");?>">
								<i class="o-setup"></i><span><?php echo $lang["Each page"];?> <?php echo $pageSize;?></span><i class="caret"></i>
							</a>
							<ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo $this->createUrl("monitor/index");?>" >
								<li data-value="10" <?php if ($pageSize == 10) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 10</a></li>
								<li data-value="20" <?php if ($pageSize == 20) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 20</a></li>
								<li data-value="30" <?php if ($pageSize == 30) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 30</a></li>
								<li data-value="40" <?php if ($pageSize == 40) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 40</a></li>
								<li data-value="50" <?php if ($pageSize == 50) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 50</a></li>
							</ul>
						</div>
					</div>
					<div class="pull-right">
						<?php $this->widget("IWPage", array("pages" => $pages)); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<!--右栏 结束-->
	</div>
</div>
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script>
	Ibos.app.setPageParam("flowlist", $.parseJSON('<?php echo addslashes(json_encode($flowlist));?>'));
	$(function() {
		// 流程选择
		$("#flow_choose").ibosSelect({
			data: Ibos.app.g("flowlist"),
			multiple: false
		});
		// 指定发起人
		$('#choose_person').userSelect({
			data: Ibos.data.get("user"),
			type: "user",
			maximumSelectionSize: "1"
		});

		//初始化气泡提示
		$(".q-attachment-area").tooltip();
	});
</script>
