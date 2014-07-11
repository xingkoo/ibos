<table class="table table-striped table-head-condensed table-head-inverse mbz" style="width: 360px;" id="search_template_list">
    <thead>
		<tr>
			<th colspan="2"><?php echo $lang["Template name"];?></th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($list)) : ?>
			<?php foreach ($list as $tpl ) : ?>
				<tr>
					<td><?php echo $tpl["tplname"];?></td>
					<td width="70">
						<a href="javascript:;" class="cbtn o-edit" data-search-template="edit" data-id="<?php echo $tpl["seqid"];?>"></a>
						<a href="javascript:;" class="cbtn o-trash mlm" data-search-template="del" data-id="<?php echo $tpl["seqid"];?>"></a>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="2"><?php echo $lang["Empty query tpl tip"];?></td>
			</tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2">
				<a href="javascript:;" data-search-template="add">
					<i class="cbtn o-plus"></i>
					<?php echo $lang["Add one item"];?>
				</a>
			</td>
		</tr>
	</tfoot>
</table>
<script>
	(function() {
		$("#search_template_list").on("click", "a", function() {
			var type = $.attr(this, "data-search-template"), sid, $elem;
			if (!type) {
				return false;
			}
			sid = $.attr(this, "data-id");
			$elem = $(this);
			switch (type) {
				case "add":
					searchTemplate.showSetting();
					break;
				case "edit":
					searchTemplate.showSetting({sid: sid});
					break;
				case "del":
					searchTemplate.deleleTemplate({sid: sid, formhash: '<?php echo FORMHASH;?>'}, function(data) {
						if (data.isSuccess) {
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
