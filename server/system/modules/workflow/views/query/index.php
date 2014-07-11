<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<!--右栏 开始-->
	<div class="mcr">
		<!--右栏头部标题 开始-->
		<div class="bdbs fill-sn lhf clearfix">
			<span class="pull-left fsl xwb vam"><?php echo $lang["Work search"];?></span>
			<a href="<?php echo $this->createUrl("query/advanced ");?>" class="btn pull-right"><?php echo $lang["Into advanced search"];?></a>
		</div>
		<!--右栏头部标题 结束-->
		<!--右栏查询过滤功能区域 开始-->
		<div class="query-form" id="query_form">
			<form action="#" method="post" class="form-horizontal form-narrow">
				<div class="control-group"> 
					<label class="control-label"><?php echo $lang["Flow"];?></label>
					<div class="controls clearfix">
						<input type="text" class="span5" data-type="flowid" <?php if ($flowid) : ?>value="<?php echo $flowid;?>"<?php endif; ?> id="flow_choose" placeholder="<?php echo $lang["Select process"];?>" />
						<div class="btn-group ml" id="flow_type">
							<a href="javascript:void(0);" data-type="type" data-param="all" class="btn <?php if ($type == "all") echo 'active';?>"><?php echo $lang["All of it"];?></a>
							<a href="javascript:void(0);" data-type="type" data-param="perform" class="btn <?php if ($type == "perform") echo 'active';?>"><span class="xcgn"><?php echo $lang["Perform"];?></span></a>
							<a href="javascript:void(0);" data-type="type" data-param="end" class="btn <?php if ($type == "end") echo 'active';?>"><span class="tcm"><?php echo $lang["Has ended"];?></span></a>
						</div>
					</div>
				</div>
				<div class="control-group"> 
					<label class="control-label"><?php echo $lang["Scope"];?></label>
					<div class="controls clearfix">
						<ul class="range-select btn-range pull-left">
							<li <?php if ($scope == "none") echo 'class="active"';?> >
								<a href="javascript:void(0);" data-type="scope" data-param="none"><?php echo $lang["No limit"];?></a>
							</li>
							<li <?php if ($scope == "start") echo 'class="active"';?> >
								<a href="javascript:void(0);" data-type="scope" data-param="start"><?php echo $lang["I started"];?></a>
							</li>
							<li <?php if ($scope == "handle") echo 'class="active"';?> >
								<a href="javascript:void(0);" data-type="scope" data-param="handle"><?php echo $lang["I handleing"];?></a>
							</li>
							<li <?php if ($scope == "manage") echo 'class="active"';?> >
								<a href="javascript:void(0);" data-type="scope" data-param="manage"><?php echo $lang["I manage"];?></a>
							</li>
							<li <?php if ($scope == "focus") echo 'class="active"';?> >
								<a href="javascript:void(0);" data-type="scope" data-param="focus"><?php echo $lang["I focus"];?></a>
							</li>
						</ul>
						<div class="span5 pull-left choose-range"><input type="text" data-type="beginuser" <?php if (isset($beginuser)) : ?>value="<?php echo $beginuser;?>"<?php endif; ?> id="choose_range" placeholder="<?php echo $lang["Specify the originator"];?>" /></div>
					</div>
				</div>
				<div class="control-group"> 
					<label class="control-label"><?php echo $lang["Time"];?></label>
					<div class="controls clearfix">
						<select class="form-control pull-left qf-short-select" data-type="time" id="time_user_defined">
							<option value="none" <?php if ($time == "none") echo "selected";?> ><?php echo $lang["No limit"];?></option>
							<option value="today" <?php if ($time == "today") echo "selected";?> ><?php echo $lang["Today"];?></option>
							<option value="yesterday" <?php if ($time == "yesterday") echo "selected";?> ><?php echo $lang["Yesterday"];?></option>
							<option value="thisweek" <?php if ($time == "thisweek") echo "selected";?> ><?php echo $lang["This week"];?></option>
							<option value="lastweek" <?php if ($time == "lastweek") echo "selected";?> ><?php echo $lang["Last week"];?></option>
							<option value="thismonth" <?php if ($time == "thismonth") echo "selected";?> ><?php echo $lang["This month"];?></option>
							<option value="lastmonth" <?php if ($time == "lastmonth") echo "selected";?> ><?php echo $lang["Last month"];?></option>
							<option value="custom" <?php if ($time == "custom") echo "selected";?> ><?php echo $lang["Custom"];?></option>
						</select>
						<div class="span4 pull-left wq-time-grorp<?php if ($time == "custom") echo ' show';?>">
							<div class="datepicker input-group" id="start_time_picker">
								<span class="input-group-addon"><?php echo $lang["From"];?></span>
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" data-type="custom_time" class="datepicker-input" id="start_time">
							</div>
						</div>
						<div class="span4 pull-left wq-time-grorp <?php if ($time == "custom") echo "show";?>">
							<div class="datepicker input-group" id="end_time_picker">
								<span class="input-group-addon"><?php echo $lang["To"];?></span>
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" data-type="custom_time" class="datepicker-input" id="end_time">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!--右栏查询过滤功能区域 结束-->
		<div class="page-list clearfix">
			<!--右栏列表功能栏 开始-->
			<div class="chl page-list-header" id="query_function">
				<button type="button" class="btn" data-click="export"><?php echo $lang["Export"];?>ZIP</button>
				<?php if ($advanceOpt) : ?>
					<button type="button" class="btn" data-click="admindel"><?php echo $lang["Admin delete"];?></button>
					<button type="button" class="btn" data-click="forceend" ><?php echo $lang["Force end"];?></button>
				<?php endif; ?>
			</div>
			<!--右栏列表功能栏 结束-->
			<!--右栏列表 开始-->
			<div class="page-list-mainer xcm">
				<?php if (!empty($list)) : ?>
					<table class="table table-hover table-striped table-query" id="table-query">
						<thead>
							<tr>
								<th width="16">
									<label class="checkbox">
										<input type="checkbox" data-name="id[]" />
									</label>
								</th>
								<th><?php echo $lang["Name"];?></th>
								<th width="20"></th>
								<th width="100"><?php echo $lang["Originator"];?></th>
								<th width="102"><?php echo $lang["Current state"];?></th>
								<th width="150"><?php echo $lang["Start time"];?></th>
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
													$original_title .= '<li>' . $attach["filename"] .'</li>';
												}
												$original_title .= '</ul>';
											?>
											<div class="fill-mm q-attachment-area" data-placement="bottom" data-html="true" data-original-title="<?php echo CHtml::encode($original_title);?>">
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
										<?php if ($val["endtime"] == 0) : ?>
											<span class="fss xcgn"><?php echo $lang["Perform"];?></span>
										<?php else : ?>
											<span class="fss tcm"><?php echo $lang["Has ended"];?></span>
										<?php endif; ?>
									</td>
									<td class="posr">
										<span class="art-list-time tcm"><?php echo $val["begin"];?></span>
										<div class="right-btnbar">
											<!-- <a href="javascript:void(0);" data-click="viewFlow" data-param="{&quot;key&quot;: &quot;<?php echo $val["key"];?>&quot;}" class="btn btn-primary btn-small pull-left flow-map"><?php echo $lang["Flow chart"];?></a> -->
											<a href="<?php echo $this->createUrl("preview/flow", array("key" => $val["key"]));?>" target="_blank" class="btn btn-primary btn-small pull-left flow-map"><?php echo $lang["Flow chart"];?></a>
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
			<div class="page-list-footer">
				<div class="page-num-select">
					<div class="btn-group dropup">
						<?php $pageSize = $pages->getPageSize();?>
						<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("query/index");?>">
							<i class="o-setup"></i><span><?php echo $lang["Each page"];?> <?php echo $pageSize;?></span><i class="caret"></i>
						</a>
						<ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo $this->createUrl("query/index");?>" >
							<li data-value="10" <?php if ($pageSize == 10) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 10</a></li>
							<li data-value="20" <?php if ($pageSize == 20) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 20</a></li>
							<li data-value="30" <?php if ($pageSize == 30) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 30</a></li>
							<li data-value="40" <?php if ($pageSize == 40) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 40</a></li>
							<li data-value="50" <?php if ($pageSize == 50) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 50</a></li>
						</ul>
					</div>
				</div>
				<div class="pull-right">
					<?php $this->widget("IWPage", array("pages" => $pages));?>
				</div>
			</div>
		</div>
		<!--右栏 结束-->
	</div>
</div>
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/wfquery.js?<?php echo VERHASH;?>'></script>
<script type="text/javascript">
	Ibos.app.setPageParam("flowlist", $.parseJSON('<?php echo addslashes(json_encode($flowlist));?>'));
	var searchParam = {
		flowid: '<?php echo $flowid;?>',
		scope: '<?php echo $scope;?>',
		type: '<?php echo $type;?>',
		time: '<?php echo $time;?>',
		beginuser: '<?php echo $beginuser;?>'
	};
	Ibos.app.setPageParam('searchParam', searchParam);
	$(function() {
		// 流程选择
		$("#flow_choose").ibosSelect({
			data: Ibos.app.g("flowlist"),
			multiple: false
		});
		// 指定发起人
		$('#choose_range').userSelect({
			data: Ibos.data.get("user"),
			type: "user",
			maximumSelectionSize: "1"
		});
		// 点击时间选择类型中的"自定义"项后,时间选择出现
		$("#time_user_defined").change(function() {
			$(".wq-time-grorp").toggleClass("show", this.value == "custom");
		});

		//初始化时间范围选择
		$("#start_time_picker").datepicker({target: $("#end_time_picker")});
		$("#start_time_picker, #end_time_picker").on("hide", function(){
			var startTime = $("#start_time").val(),
				endTime = $("#end_time").val();
			if(startTime && endTime) {
				setFilter($("#time_user_defined"))
			}
		})

		// 流程类型中,"所有""已结束""进行中"三种类型点击选择时样式切换
		$("#flow_type .btn").on("click", function() {
			$(this).addClass("active").siblings().removeClass("active");
		});

		//初始化选择范围选择
		$("#choose_range").userSelect({data: []});
		//初始化附件提示栏
		$(".q-attachment-area").tooltip({trigger: "hover"});

		function setFilter($elem) {
			var params = Ibos.app.g('searchParam'), type = $elem.data('type'), param = $elem.data('param'),
					$start = $('#start_time'), $end = $('#end_time');
			if (type == 'scope') {
				params.scope = param;
			} else if (type == 'type') {
				params.type = param;
			} else if (type == 'time') {
				var val = $elem.val();
				if (val == 'custom') {
					var startTime = $start.val(),
						endTime = $end.val();
					if (!startTime || !endTime) {
						return;
					}
					params.start = startTime;
					params.end = endTime;
				}
				params.time = val;
			} else if (type == 'beginuser') {
				var val = $elem.val();
				params.scope = 'custom';
				params.beginuser = val;
			} else if (type == 'flowid') {
				params.flowid = $elem.val();
			} else if (type == 'custom_time') {
				if ($start.val() !== '' && $end.val() !== '') {
					params.start = $start.val();
					params.end = $end.val();
				} else {
					return;
				}
			}
			var url = Ibos.app.url('workflow/query/index', params);
			window.location.href = url;
		}

		$('#query_form').on('click', 'a[data-type]', function() {
			setFilter($(this));
		}).on('change', '[data-type]', function() {
			setFilter($(this));
		});

	});
</script>
