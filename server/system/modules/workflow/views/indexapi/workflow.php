<link rel="stylesheet" href="<?php echo $assetUrl . "/css/index_workflow.css";?>">
<!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
<script></script>
<?php if (!empty($todo)) : ?>
	<table class="table table-striped">
		<tbody>
			<?php foreach ($todo as $run ) : ?>
				<tr>
					<td>
						<div class="wk-text-nowrap">
							<a class="<?php if ($run["flag"] == "1") : ?>xwb xcbu<?php else : ?>xcm<?php endif; ?>" href="<?php echo Ibos::app()->createUrl("workflow/form/index", array("key" => $run["key"]));?>" target="_blank"><?php echo $run["runName"];?></a> 
						</div>
					</td>
					<td width="120">
						<span class="label"><?php echo $run["flowprocess"];?></span>
						<span class="fss">
							<a data-click="viewFlow" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}" href="javascript:void(0);"><?php echo $run["stepname"];?></a>
						</span>
					</td>
					<td width="20">
						<?php if ($run["focus"]) : ?>
						<span class="o-yw-attention"></span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class="mbox-base">
		<div class="fill-hn xac">
			<a href="<?php echo Ibos::app()->createUrl("workflow/list/index", array("op" => "list", "type" => "todo", "sort" => "all"));?>" class="link-more">
				<i class="cbtn o-more"></i>
				<span class="ilsep">查看更多待办工作</span>
			</a>
		</div>
	</div>
	<script>
		var openFullWindow = function(url, name, paramStr) {
		paramStr = (paramStr ? (paramStr + ",") : "") + "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1,top=0, left=0, width=" + screen.availWidth + ", height=" + screen.availHeight;
		window.open(url, name, paramStr);
		};
		Ibos.events.add({
		'viewFlow': function(param, \$elem) {
		openFullWindow(Ibos.app.url('workflow/preview/flow', param), "viewFlow");
		}});
	</script>
<?php else : ?>
	<div class="in-wf-empty"></div>
<?php endif; ?>