<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<!--左栏导航栏 开始-->
	<!--右栏 开始-->
	<div class="mcr">
		<div class="page-list clearfix">
			<!--右栏头部功能栏 开始-->
			<div class="page-list-header">
				<div class="pull-left" id="recycle_function">
					<button type="button" data-click="restoreRun" class="btn btn-primary" id="restore">恢复</button>
					<button type="button" data-click="destroy" class="btn" id="shift_delete">彻底删除</button>
				</div>
				<!--<form action="#" method="post">
					<div class="search search-config pull-right span4">
						<input type="text" placeholder="输入查询条件" id="mn_search" name="keyword" nofocus />
						<a href="javascript:;">search</a>
						<input type="hidden" name="type" id="normal_search" />
					</div>
				</form>-->
			</div>
			<!--右栏头部功能栏 结束-->
			<!--右栏列表 开始-->
			<?php if (!empty($list)) : ?>
				<div class="page-list-mainer xcm">
					<table class="table table-hover table-striped table-recycle">
						<thead>
							<tr>
								<th width="16">
									<label class="checkbox">
										<input type="checkbox" data-name="id[]" />
									</label>
								</th>
								<th>流程类型</th>
								<th width="140">发起人</th>
								<th width="160">开始时间</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($list as $run ) : ?>
								<tr id="list_tr_<?php echo $run["runid"];?>">
									<td>
										<label class="checkbox">
											<input type="checkbox" name="id[]" value="<?php echo $run["runid"];?>"/>
										</label>
									</td>
									<td>
										<div class="com-list-name">
											<em class="text-nowrap"><a class="xcm" title="<?php echo $run["runName"];?>" target="_blank" href="<?php echo $this->createUrl("preview/flow", array("key" => $run["key"]));?>"><?php echo StringUtil::cutStr($run["runName"], 25);?></a></em>
											<span class="fss tcm posa">[<?php echo $run["runid"];?>]<?php echo $run["typeName"];?></span>
										</div>
									</td>
									<td>
										<a data-toggle="usercard" data-param="uid=<?php echo $run["user"]["uid"];?>" href="<?php echo $run["user"]["space_url"];?>" class="avatar-circle" title="<?php echo $run["user"]["realname"];?>">
											<img src="<?php echo $run["user"]["avatar_middle"];?>" />
										</a>
										<span class="fss"><?php echo $run["user"]["realname"];?></span>
									</td>
									<td>
										<span class="fss tcm"><?php echo $run["begintime"];?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<!--右栏列表 结束-->
				<!--右栏底部功能栏 开始-->
				<div class="page-list-footer">
					<div class="page-num-select">
						<div class="btn-group dropup">
							<?php $pageSize = $pages->getPageSize();?>
							<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("recycle/index");?>">
								<i class="o-setup"></i><span><?php echo $lang["Each page"];?> <?php echo $pageSize;?></span><i class="caret"></i>
							</a>
							<ul class="dropdown-menu" id="page_num_menu">
								<li <?php if ($pageSize == 10) echo 'class="active"';?> >
									<a href="<?php echo $this->createUrl("recycle/index", array("pagesize" => "10"));?>"><?php echo $lang["Each page"];?> 10</a>
								</li>
								<li <?php if ($pageSize == 20) echo 'class="active"';?> >
									<a href="<?php echo $this->createUrl("recycle/index", array("pagesize" => "20"));?>"><?php echo $lang["Each page"];?> 20</a>
								</li>
								<li <?php if ($pageSize == 30) echo 'class="active"';?> >
									<a href="<?php echo $this->createUrl("recycle/index", array("pagesize" => "30"));?>"><?php echo $lang["Each page"];?> 30</a>
								</li>
								<li <?php if ($pageSize == 40) echo 'class="active"';?> >
									<a href="<?php echo $this->createUrl("recycle/index", array("pagesize" => "40"));?>"><?php echo $lang["Each page"];?> 40</a>
								</li>
								<li <?php if ($pageSize == 50) echo 'class="active"';?> >
									<a href="<?php echo $this->createUrl("recycle/index", array("pagesize" => "10"));?>"><?php echo $lang["Each page"];?> 50</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="pull-right">
						<?php $this->widget("IWPage", array("pages" => $pages));?>
					</div>
				</div>
			<?php else : ?>
				<div class="no-data-tip"></div>
			<?php endif; ?>
		</div>
		<!--右栏 结束-->
	</div>
</div>
<!--<div id="mn_search_advance" style="width: 400px; display: none;">
	<form id="mn_search_advance_form" method="post" action="" class="form-horizontal form-compact">
		<div class="control-group">
			<label class="control-label">流程选择</label>
			<div class="controls">
				<select>
					<option value="">选择流程</option>
					<option value="">测试流程2</option>
					<option value="">测试流程</option>
					<option value="">外出申请</option>
					<option value="">采购申请</option>
					<option value="">工作交办</option>
					<option value="">名片印刷登记</option>
					<option value="">请假申请</option>
					<option value="">离职申请</option>
					<option value="">转正申请</option>
					<option value="">出差申请</option>
					<option value="">加班登记</option>
					<option value="">员工招聘申请</option>
					<option value="">员工入职申请</option>
					<option value="">报销申请流程</option>
					<option value="">请款(付款)申请</option>
					<option value="">借支申请流程</option>
					<option value="">常用备用金申请流程</option>
					<option value="">发票申领</option>
					<option value="">商务费用申请</option>
					<option value="">合同审批流程</option>
					<option value="">网页试做申请</option>
					<option value="">设计人员项目评价流程</option>
					<option value="">技术人员项目评价流程</option>
					<option value="">项目经理评价流程</option>
					<option value="">开发岗月度绩效考核</option>
					<option value="">UI岗位月度绩效考核</option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">发起人</label>
			<div class="controls">
				<input type="text" id="choose_range" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">流水号</label>
			<div class="controls">
				<input type="text" />
			</div>
		</div>
		<div class="control-group">
			<label for="" class="control-label">开始时间</label>
			<div class="controls">
				<div class="datepicker">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" id="date_start">
				</div>
			</div>
		</div>
		<div class="control-group">
			<label for="" class="control-label">截止时间</label>
			<div class="controls">
				<div class="datepicker">
					<a href="javascript:;" class="datepicker-btn"></a>
					<input type="text" class="datepicker-input" id="date_end">
				</div>
			</div>
		</div>
		<input type="hidden" name="type" value="advanced_search"> 
	</form>
</div>-->
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/wfrecycle.js?<?php echo VERHASH;?>'></script>