<div id="app_dialog" class="app-dialog">
	<!-- Tab -->
	<div class="app-dialog-sidebar">
		<div class="app-dialog-title">
			<i class="o-app-pool"></i>
			<h3><?php echo $lang["Application Market"];?></h3>
		</div>
		<ul id="app_dialog_tab" class="nav nav-strip nav-stacked">
			<li class="active"><a href="#all" data-id="0" data-toggle="tab"><?php echo $lang["All"];?> (<?php echo $appCount;?>)</a></li> 
			<?php foreach ($category as $value ) : ?>
				<li><a href="#<?php echo $value["name"];?>" data-id="<?php echo $value["catid"];?>" data-toggle="tab"><?php echo $value["description"];?> (<?php echo $value["count"];?>)</a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="tab-content app-dialog-content">
		<div class="tab-pane active">
			<div class="fill-sn bdbs clearfix">
				<div class="btn-group pull-left">
					<button type="button" class="btn" data-node-type="appPrevPageBtn" disabled>
						<i class="glyphicon-chevron-left"></i>
					</button>
					<button type="button" class="btn" data-node-type="appNextPageBtn">
						<i class="glyphicon-chevron-right"></i>
					</button>
				</div>
				
			<!--<div class="search pull-right">
					<input type="text" placeholder="输入需要的应用名称" id="app_search" name="search" nofocus>
					<a href="javascript:;"></a>
				</div>-->
				
			</div>
			<div class="app-shortcut">
				<ul class="app-shortcut-list clearfix" id="app_pool">
					<!-- <li>
						<div class="app-shortcut-icon" title="条形码批量生成器">
							<a href="javascript:;">
								<img src="http://apps3.bdimg.com/store/static/kvt/ae7ecf1cb0d3194185da840a8edcec79.jpg" alt="条形码批量生成器">
							</a>
						</div>
						<div class="app-shortcut-name">
							<a href="javascript:;">条形码批量生成器</a>
						</div>
					</li> -->
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- load script -->
<script src="<?php echo $assetUrl;?>/js/applist.js?<?php echo VERHASH;?>"></script>
<!-- load script end -->