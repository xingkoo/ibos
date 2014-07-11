<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<div class="mcr">
		<div class="mc-header">
			<ul class="mnv clearfix">
				<li <?php if ($type == "todo") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => "todo", "sort" => $sort));?>">
						<i class="o-nav-works"></i>
						<?php echo $lang["Todo work"];?>
					</a>
				</li>
				<li <?php if ($type == "trans") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => "trans", "sort" => $sort));?>">
						<i class="o-nav-finish"></i>
						<?php echo $lang["Have been transferred"];?>
					</a>
				</li>
				<li <?php if ($type == "done") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => "done", "sort" => $sort));?>">
						<i class="o-nav-over"></i>
						<?php echo $lang["Has been completed"];?>
					</a>
				</li>
				<li <?php if ($type == "delay") echo 'class="active"';?> >
					<a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => "delay", "sort" => $sort));?>" >
						<i class="o-nav-stop"></i>
						<?php echo $lang["Has been postponed"];?>
					</a>
				</li>
			</ul>
		</div>
		<div class="page-list clearfix">
			<div class="page-list-header">
				<button type="button" data-click="export" class="btn export-btn pull-left"><?php echo $lang["Export"];?></button>
				<div class="btn-toolbar pull-right span7 posr">
					<div class="btn-group">
						<button type="button" class="btn dropdown-toggle toggle-all-btn" data-toggle="dropdown">
							<?php echo $sortText;?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
                            <li <?php if ($sort == "all") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => $type, "sort" => "all"));?>"><?php echo $lang["All of it"];?></a></li>
                            <li <?php if ($sort == "host") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => $type, "sort" => "host"));?>"><?php echo $lang["Host work"];?></a></li>
                            <li <?php if ($sort == "sign") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => $type, "sort" => "sign"));?>"><?php echo $lang["Sign"];?></a></li>
                            <li <?php if ($sort == "rollback") echo 'class="active"';?> ><a href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => $type, "sort" => "rollback"));?>"><?php echo $lang["Rollback"];?></a></li>
						</ul>
					</div>
					<div class="btn-group">
						<a title="<?php echo $lang["List view"];?>" class="btn btn-display-list <?php if ($op == "list") echo "active";?>" href="<?php echo $this->createUrl("list/index", array("op" => "list", "type" => $type, "sort" => $sort));?>"><i class="o-display-list"></i></a>
						<a title="<?php echo $lang["Category view"];?>" class="btn btn-display-classity <?php if ($op == "category") echo "active";?>" href="<?php echo $this->createUrl("list/index", array("op" => "category", "type" => $type, "sort" => $sort));?>"><i class="o-display-category"></i></a>
					</div>
					<form action="#" method="post" id="search_form">
						<div class="search span7 posa" style="top:0px; left:185px;">
							<input type="text" placeholder="<?php echo $lang["Search tip"];?>" name="keyword" id="mn_search" nofocus />
							<a href="javascript:;">search</a>
							<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
							<input type="hidden" name="op" value="<?php echo $op;?>" />
							<input type="hidden" name="type" value="<?php echo $type;?>" />
							<input type="hidden" name="sort" value="<?php echo $sort;?>" />
						</div>
					</form>
				</div>
			</div>
			<?php $this->widget("IWWfListView", array("type" => $type, "sort" => $sort, "op" => "list", "flowid" => $flowId, "uid" => Ibos::app()->user->uid, "keyword" => $keyword, "pageSize" => $this->getListPageSize(), "flag" => $flag), true);?>
		</div><!-- end pagelist-->
	</div><!-- end mcr-->
</div><!-- end mc-->
<div id="dialog_delay">
	<ul>
		<li>
			<label class="radio">
				<span class="icon"></span>
				<span class="icon-to-fade"></span>
				<input type="radio" name="delay-time" checked value="1" />明天
			</label>
		</li>
		<li>
			<label class="radio">
				<span class="icon"></span>
				<span class="icon-to-fade"></span>
				<input type="radio" name="delay-time" value="2" />后天
			</label>
		</li>
		<li>
			<label class="radio">
				<span class="icon"></span>
				<span class="icon-to-fade"></span>
				<input type="radio" name="delay-time" value="3" />下周(<span id="next_week_time"></span>)
			</label>
		</li>
		<li>
			<label class="radio">
				<span class="icon"></span>
				<span class="icon-to-fade"></span>
				<input type="radio" name="delay-time" value="4" />自定义
			</label>
		</li>
		<li>
			<div class="datepicker delay-time">
				<input type="text" id="custom_time" readonly="" class="datepicker-input custom-time">	
				<a href="javascript:;" class="datepicker-btn"></a>
			</div>
		</li>
	</ul>
</div>
<style type="text/css">
	.bg-nav-list li{
		width: 100px;
		height: 60px;
		background-color: #f9fbff;
		border-right: 1px #ebeff6 solid;
		border-bottom: 1px #ebeff6 solid;
		line-height: 60px;
		text-align: center;
		vertical-align: middle;
	}
	.bg-choose{
		padding-top:20px;
		margin-left: 106px;
		width: 474px;
		height: 100%;
		vertical-align: top; 
	}
	.model-img{
		display: inline-block;
		padding:3px;
		width: 132px;
		height: 72px;
		border: 1px #f0f0f1 solid;
		background-color: #3497db;
	}
	.model-img img{
		width: 132px;
		height: 72px;
	}
	.choose-list li{
		padding: 0;
		margin-left: 15px;
		margin-bottom: 15px;
	}
	.bg-table .nav-list{
		width: 100px;
		height: 60px;
		background-color: #f9fbff;
		border-right: 1px #ebeff6 solid;
		border-bottom: 1px #ebeff6 solid;
		line-height: 60px;
		text-align: center;
		vertical-align: middle;
	}
	.bg-nav-list li{
		cursor: pointer;
	}
	.bg-nav-list li.active{
		background-color: #fff;
		color: #3497db;
		font-weight: 700;
	}
	.skin-bg{
		position: relative;
		overflow: inherit;
		height: 100%;
		background-color: #fff;
	}
	.left-nav{
		position: absolute;
		height: 100%;
		background: #f9fbff;
		overflow: hidden;
	}

</style>
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/wfmywork.js?<?php echo VERHASH;?>'></script>
<script>
	(function() {
		//初始化延期弹框中延期时间
		$(".delay-time").datepicker({startDate: '<?php echo date("Y-m-d H:i:s", strtotime("+1 day"));?>'});
		// 列表条数设置
		var $pageNumCtrl = $("#page_num_ctrl"), $pageNumMenu = $("#page_num_menu"), pageNumSelect = new P.PseudoSelect($pageNumCtrl, $pageNumMenu, {
			template: '<i class="o-setup"></i> <span><%=text%></span> <i class="caret"></i>'
		});
		$pageNumCtrl.on("select", function(evt) {
			var url = $pageNumCtrl.attr("data-url") + "&pagesize=" + evt.selected;
			window.location.href = url;
		});

		// 办理完毕流程提示
		if (U.getCookie('flow_complete_flag') == 1) {
			Ui.tip(U.lang('WF.COMPLETE_SUCEESS'), 'success');
			U.setCookie('flow_complete_flag', '');
		}
		// 转交成功提示
		if (U.getCookie('flow_turn_flag') == 1) {
			Ui.tip(U.lang('WF.TRUN_SUCEESS'), 'success');
			U.setCookie('flow_turn_flag', '');
		}
		//延期弹窗中下一周时间初始化
		var now = +new Date();
		var ONE_WEEK_TIME = 7 * 24 * 60 * 60 * 1000;
		var time = Ibos.date.format(new Date(now + ONE_WEEK_TIME));
		$("#next_week_time").text(time);
		//点击时间选择类型中的"自定义"项后,时间选择出现
		$(":radio[name='delay-time']").change(function() {
			var val = $(this).val();
			if (val == "4") {
				$(".delay-time").addClass("show");
			} else {
				$('#custom_time').val('');
				$(".delay-time").removeClass("show");
			}
		});
		$(".bg-nav-list li").on('click', function() {
			var index = $(this).index();
			$(this).addClass("active").siblings().removeClass('active');
			$(".bg-choose div").eq(index).show().siblings().hide();
		});
	})();

</script>
