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
					<th><?php echo $lang["Name"];?></th>
					<th width="100"><?php echo $lang["Originator"];?></th>
					<th width="130"><?php echo $lang["Steps and flow chart"];?></th>
					<th width="140"></th>
					<th width="20"></th>
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
								<em class="text-nowrap"><a class="xcm" title="<?php echo $run["runName"];?>" target="_blank" href="<?php echo Ibos::app()->createUrl("workflow/preview/print", array("key" => $run["key"]));?>"><?php echo StringUtil::cutStr($run["runName"], 50);?></a></em>
								<span class="fss tcm posa">[<?php echo $run["runid"];?>]<?php echo $run["typeName"];?></span>
							</div>
						</td>
						<td>
							<span class="fss"><?php echo $run["user"]["realname"];?></span>
						</td>
						<td class="posr">
							<?php if ($run["type"] == "1") : ?>
								<span class="label over-step-label"><?php echo $run["flowprocess"];?></span>
								<span class="fss step-and-flow">
									<a href="javascript:void(0);" data-click="viewFlow" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}"><?php echo $run["stepname"];?></a>
								</span>
							<?php else : ?>
								<?php echo $run["stepname"];?>
							<?php endif; ?>
						</td>
						<td>
							<div>
								<div class="btn-toolbar right-btnbar pull-right">
									<?php if (isset($run["rollback"])) : ?>
										<a href="javascript:void(0);" data-click="takeback" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}" class="btn btn-small pull-left btn-host"><?php echo $lang["Take back"];?></a>
									<?php endif; ?>
									<div class="btn-group">
										<button type="button" class="btn dropdown-toggle btn-small" data-toggle="dropdown">
											<?php echo $lang["More"];?><span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu">
											<li><a href="javascript:;" class="todo-export" data-click="export" data-param="<?php echo $run["runid"];?>"><?php echo $lang["Export"];?></a></li>
										</ul>
									</div>
								</div>
							</div>
						</td>
						<td>
							<i class="<?php if ($run["focus"]) : ?>o-ck-attention<?php else : ?>o-tr-attention<?php endif; ?> pull-right" data-param="<?php echo $run["runid"];?>" data-click="focus"></i>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php $this->render("wfwidget.listpage", array("pages" => $pages, "lang" => $lang, "op" => $op, "type" => $type, "sort" => $sort));?>
<?php else : ?>
	<div class="no-data-tip"></div>
<?php endif; ?>