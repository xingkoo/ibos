<table class="table table-striped table-head-condensed table-head-inverse mbz auth-rule-table" style="width: 740px;" id="auth_rule_table">
    <thead>
		<tr>
			<th><?php echo $lang["Licensed to"];?></th>
			<th><?php echo $lang["Give permissions"];?></th>
			<th><?php echo $lang["Manager scope"];?></th>
			<th width="70"></th>
		</tr>
	</thead>
	<tbody>
		<?php if (isset($list)) : ?>
			<?php foreach ($list as $per ) : ?>
				<tr>
					<td>
						<ul class="text-list">
							<?php if (!empty($per["userName"])) : ?>
								<li class="">
									<strong><?php echo $lang["User"];?>&nbsp;</strong>
									<span><?php echo $per["userName"];?></span>
								</li>
							<?php endif; ?>
							
							<?php if (!empty($per["deptName"])) : ?>
								<li class="">
									<strong><?php echo $lang["Department"];?>&nbsp;</strong>
									<span><?php echo $per["deptName"];?></span>
								</li>
							<?php endif; ?>
							
							<?php if (!empty($per["posName"])) : ?>
								<li class="">
									<strong><?php echo $lang["Position"];?>&nbsp;</strong>
									<span><?php echo $per["posName"];?></span>
								</li>
							<?php endif; ?>
						</ul>
					</td>
					<td width="180"><?php echo $per["typeName"];?></td>
					<td width="180"><?php echo $per["scopeName"];?></td>
					<td width="70">
						<a href="javascript:;" class="cbtn o-edit" data-auth="edit" data-id="<?php echo $per["id"];?>" /></a>
						<a href="javascript:;" class="cbtn o-trash mlm" data-auth="del" data-id="<?php echo $per["id"];?>" /></a>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="4"><?php echo $lang["Empty permission tip"];?></td>
			</tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">
				<a href="javascript:;" data-auth="add"><i class="cbtn o-plus"></i><?php echo $lang["Add one item"];?></a>
			</td>
		</tr>
	</tfoot>
</table>
<script>
	(function() {
		$("#auth_rule_table").on("click", "a", function() {
			var type = $.attr(this, "data-auth"), id, $elem;
			if (!type) {
				return false;
			}
			id = $.attr(this, "data-id");
			$elem = $(this);
			switch (type) {
				case "add":
					Manager.showSetup();
					break;
				case "edit":
					Manager.showSetup({id: id});
					break;
				case "del":
					Manager.deletePermission({id: id,formhash:'<?php echo FORMHASH;?>'}, function(data) {
						if(data.isSuccess){
							Ui.tip(U.lang('DELETE_SUCCESS'), 'success');
							$elem.parent().parent().remove();
						} else {
							Ui.tip(U.lang('DELETE_FAILED'), 'danger');
						}
					});
					break;
			}
		});
	})();
</script>