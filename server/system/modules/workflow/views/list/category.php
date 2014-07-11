<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<div class="mcr">
		<div class="mc-header">
			<ul class="mnv clearfix">
				<li <?php if ($type == "todo") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => "todo", "sort" => $sort));?>">
						<i class="o-nav-works"></i>
						<?php echo $lang["Todo work"];?>
					</a>
				</li>
				<li <?php if ($type == "trans") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => "trans", "sort" => $sort));?>">
						<i class="o-nav-finish"></i>
						<?php echo $lang["Have been transferred"];?>
					</a>
				</li>
				<li <?php if ($type == "done") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => "done", "sort" => $sort));?>">
						<i class="o-nav-over"></i>
						<?php echo $lang["Has been completed"];?>
					</a>
				</li>
				<li <?php if ($type == "delay") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => "delay", "sort" => $sort));?>">
						<i class="o-nav-stop"></i>
						<?php echo $lang["Has been postponed"];?>
					</a>
				</li>
			</ul>
		</div>
		<div class="page-list clearfix">
			<div class="page-list-header">
				<div class="btn-toolbar pull-right span7">
					<div class="btn-group">
						<button type="button" class="btn btn-default dropdown-toggle toggle-all-btn" data-toggle="dropdown">
							<?php echo $sortText;?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
                            <li <?php if ($sort == "all") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => $type, "sort" => "all"));?>"><?php echo $lang["All of it"];?></a></li>
                            <li <?php if ($sort == "host") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => $type, "sort" => "host"));?>"><?php echo $lang["Host"];?></a></li>
                            <li <?php if ($sort == "sign") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => $type, "sort" => "sign"));?>"><?php echo $lang["Sign"];?></a></li>
                            <li <?php if ($sort == "rollback") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => $type, "sort" => "rollback"));?>"><?php echo $lang["Rollback"];?></a></li>
						</ul>
					</div>
					<div class="btn-group">
						<a title="<?php echo $lang["List view"];?>" class="btn btn-display-list <?php if ($op == "list") echo "active";?>" href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => $type, "sort" => $sort));?>"><i class="o-display-list"></i></a>
						<a title="<?php echo $lang["Category view"];?>" class="btn btn-display-classity <?php if ($op == "category") echo "active";?>" href="<?php echo $this->createUrl("list/index", array("op" => "category"));?>"><i class="o-display-category"></i></a>
					</div>
					<form action="#" method="post" id="search_form">
						<div class="search pull-right span7">
							<input type="text" placeholder="<?php echo $lang["Search tip"];?>" name="keyword" id="mn_search" nofocus />
							<a href="javascript:;">search</a>
							<input type="hidden" name="type" id="normal_search" />
						</div>
					</form>
				</div>
			</div>
			<?php $this->widget("IWWfCategoryView", array("type" => $type, "sort" => $sort, "op" => "category", "flowid" => $flowId, "uid" => Ibos::app()->user->uid, "keyword" => $keyword, "flag" => $flag), true);?>
		</div>
	</div>
</div>
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script>
	//搜索
	$("#mn_search").search();
</script>