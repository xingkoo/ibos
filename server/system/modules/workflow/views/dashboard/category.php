<form action="<?php echo $this->createUrl("dashboard/category");?>" class="form-horizontal" method="post">
	<div class="ct">
		<div class="clearfix">
			<h1 class="mt"><?php echo $lang["Workflow"];?></h1>
			<ul class="mn">
				<li>
					<a href="<?php echo $this->createUrl("dashboard/param");?>"><?php echo $lang["Param setup"];?></a>
				</li>
				<li>
					<span><?php echo $lang["Category setup"];?></span>
				</li>
			</ul>
		</div>
		<div>
			<div class="ctb">
				<h2 class="st"><?php echo $lang["Category setup"];?></h2>
				<div>
					<div class="page-list">
						<div class="page-list-mainer">
							<table class="table table-striped" id="category_setup_table">
								<thead>
									<tr>
										<th width="60"><?php echo $lang["Serial number"];?></th>
										<th width="130"><?php echo $lang["Category name"];?></th>
										<th><?php echo $lang["Management department"];?><span class="help-inline">(<?php echo $lang["Management department tip"];?>)</span></th>
										<th width="100"><?php echo $lang["Flow nums"];?></th>
										<th width="100"><?php echo $lang["Form nums"];?></th>
										<th width="30"><?php echo $lang["Operation"];?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $list as $cat ): ?>
										<tr data-row="<?php echo $cat["catid"];?>">
											<td><input type="text" name="sort[<?php echo $cat["catid"];?>]" value="<?php echo $cat["sort"];?>"></td>
											<td><input type="text" name="name[<?php echo $cat["catid"];?>]" value="<?php echo $cat["name"];?>"></td>
											<td>
												<div class="wfccontrols">
													<input type="text" name="deptid[<?php echo $cat["catid"];?>]" value="<?php echo $cat["deptid"];?>" data-id="<?php echo $cat["catid"];?>" id="cat_dept_<?php echo $cat["catid"];?>" />
													<div id="cat_dept_<?php echo $cat["catid"];?>_box"></div>
												</div>
											</td>
											<td><?php echo $cat["flownums"];?></td>
											<td><?php echo $cat["formnums"];?></td>
											<td>
												<a href="javascript:;" class="cbtn o-trash" data-act="remove" data-id='<?php echo $cat["catid"];?>'></a>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="6">
											<a href="javascript:void(0);" class="operate-group" id="add_category">
												<i class="cbtn o-plus"></i>
												<?php echo $lang["Add category"];?>
											</a>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				<div style="margin-top: 30px;">
					<button type="submit" class="btn btn-primary btn-large btn-submit"> <?php echo $lang["Submit"];?> </button>
				</div>
				<input type="hidden" name="delid" id="delid" />
				<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
			</div>
		</div>
	</div>
</form>

<script type="text/template" id="new_cat_tpl">
	<tr data-row="<%=id%>">
	<td><input type="text" name="newsort[<%=id%>]" value=""></td>
	<td><input type="text" name="newname[<%=id%>]" value=""></td>
	<td>
	<div class="wfccontrols">
	<input type="text" name="newdeptid[<%=id%>]" id="cat_dept_<%=id%>" />
	<div id="cat_dept_<%=id%>_box"></div>
	</div>
	</td>
	<td></td>
	<td></td>
	<td>
	<a href="javascript:;" class="cbtn o-trash" data-act="remove"></a>
	</td>
	</tr>
</script>
<script>
	var deptData = Ibos.data.get("department");
	(function() {
		$('#add_category').on('click', function() {
			var date = new Date();
			var id = date.getTime();
			var temp = $.template('new_cat_tpl', {id: id});
			$('#category_setup_table tbody').append(temp);
			$('#cat_dept_' + id).userSelect({
				box: $('#cat_dept_' + id + '_box'),
				type: 'department',
				data: deptData
			});
		});
		$("[data-act='remove']").live('click', function() {
			var id = $(this).data('id');
			if (id) {
				$('#delid').val($('#delid').val() + id + ',');
			}
			$(this).parent().parent().remove();
		});
	})();
	$(document).ready(function() {
		$('.wfccontrols input[type=text]').each(function() {
			var dataId = $(this).data('id'), id = 'cat_dept_' + dataId, boxId = 'cat_dept_' + dataId + '_box';
			$('#' + id).userSelect({
				box: $('#' + boxId),
				type: 'department',
				data: deptData
			});
		});
	});
</script>";
