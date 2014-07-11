<?php if (!empty($list)) : ?>
	<div class="page-list-mainer">
		<table class="table table-hover table-striped table-to-do" id="article_table">
			<thead>
				<tr>
					<th width="16">
						<label class="checkbox">
							<input type="checkbox" data-name="id[]"/>
						</label>
					</th>
					<th width="345"><?php echo $lang["Name"];?></th>
					<th width="120"><?php echo $lang["Originator"];?></th>
					<th width="140"><?php echo $lang["Used time"];?></th>
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
								<em class="text-nowrap"><a class="xcm" title="<?php echo $run["runName"];?>" href="<?php echo Ibos::app()->createUrl("workflow/preview/print", array("key" => $run["key"]));?>" target="_blank"><?php echo StringUtil::cutStr($run["runName"], 50);?></a></em>
								<span class="fss tcm posa">[<?php echo $run["runid"];?>]<?php echo $run["typeName"];?></span>
							</div>
						</td>
						<td>
							<span class="fss"><?php echo $run["user"]["realname"];?></span>
						</td>
						<td class="posr">
							<span class="art-list-time"><?php echo $run["usedtime"];?></span>
							<div class="right-btnbar">
								<a href="javascript:void(0);" data-click="export" data-param="<?php echo $run["runid"];?>" class="btn btn-small pull-right btn-host"><?php echo $lang["Export"];?></a>
								<?php if (isset($run["del"])) : ?>
								<a href="javascript:void(0);" data-click="del" data-param="<?php echo $run["runid"];?>" class="btn btn-small pull-right btn-host"><?php echo $lang["Delete"];?></a>";
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
		$this->render("wfwidget.listpage", array("pages" => $pages, "lang" => $lang, "op" => $op, "type" => $type, "sort" => $sort));
	?>
<?php else : ?>
	<div class="no-data-tip"></div>
<?php endif; ?>