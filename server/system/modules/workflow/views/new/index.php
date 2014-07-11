<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<div class="mcr">
		<!-- 常用流程 -->
		<?php if (!empty($commonlyFlows)) : ?>
			<div class="wf-row wf-common-row">
				<h5><?php echo $lang["Commonly used process"];?></h5>
				<div class="row">
					<?php foreach ($commonlyFlows as $cmflow ) : ?>
						<div class="span3 mb">
							<div class="wf-cell">
								<div class="fill-ss wf-cell-text"><?php echo $cmflow["name"];?></div>
								<div class="wf-cell-mask">
									<a href="javascript:void(0);" data-click="new" class="wf-new" data-id="<?php echo $cmflow["flowid"];?>" data-text="<?php echo $cmflow["name"];?>"><?php echo $lang["Create"];?></a>
									<a href="javascript:void(0);" data-click="preview" class="wf-preview" data-id="<?php echo $cmflow["flowid"];?>"><?php echo $lang["Review"];?></a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if (!empty($flows)) : ?>
			<?php $counter = 0; ?>
			<?php foreach ($flows as $catId => $flow ) : ?>
				<?php $counter++; ?>
				<div class="wf-row <?php if (($counter % 2) == 0) echo "bglg";?>">
					<h5><?php echo $sort[$catId]["name"];?></h5>
					<div class="row">
						<?php foreach ($flow as $ft ) : ?>
							<div class="span3 mb">
								<div class="wf-cell">
									<div class="fill-ss wf-cell-text"><?php echo $ft["name"];?></div>
									<div class="wf-cell-mask">
										<?php if ($ft["enabled"]) : ?>
											<a href="javascript:void(0);" data-click="new" class="wf-new" data-id="<?php echo $ft["flowid"];?>" data-text="<?php echo $ft["name"];?>"><?php echo $lang["Create"];?></a>
										<?php else : ?>
											<span data-toggle="tooltip" title="<?php echo $lang["Flow disabled tip"];?>" class="wf-new tcm"><?php echo $lang["Create"];?></span>
										<?php endif; ?>
										<a href="javascript:void(0);" data-click="preview" class="wf-preview" data-id="<?php echo $ft["flowid"];?>"><?php echo $lang["Review"];?></a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="no-data-tip"></div>
		<?php endif; ?>
	</div>
</div>
<script>
	(function() {
		$("[data-toggle='tooltip']").tooltip();
		Ibos.events.add({
			'new': function(param, $elem) {
				var flowId = $elem.data('id'),
						text = $elem.data('text'),
						url = Ibos.app.url('workflow/new/add', {flowid: flowId}),
				dialogOpt = {
					id: 'd_start_work',
					title: text,
					width: '400px',
					ok: function() {
						if (newFlowSubmitCheck()) {
							$('#new_form').waiting(U.lang('IN_SUBMIT'), "mini");
							$.post(url, $('#new_form').serializeArray(), function(data) {
								if (data.isSuccess) {
									window.location.href = data.jumpUrl;
								} else {
									$('#new_form').stopWaiting();
									Ui.tip(data.msg, 'danger');
								}
							}, 'json');
						}
						return false;
					},
					cancel: true
				};
				Ui.ajaxDialog(url, dialogOpt);
			},
			'preview': function(param, $elem) {
				var flowId = $elem.data('id');
				var url = Ibos.app.url('workflow/preview/newpreview',{flowid:flowId});
				window.open(url, 'preview', "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1,top=0, left=0, width=" + screen.availWidth + ", height=" + screen.availHeight);
			}
		});
	})();
</script>