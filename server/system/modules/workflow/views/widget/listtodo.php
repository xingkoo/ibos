<?php if (!empty($list)) : ?>
	<div class="page-list-mainer">
		<table class="table table-hover table-striped table-to-do">
			<thead>
				<tr>
					<th width="16">
						<label class="checkbox">
							<input type="checkbox" data-name="id[]"/>
						</label>
					</th>
					<th width="175"><?php echo $lang["Name"];?></th>
					<th><?php echo $lang["Originator"];?></th>
					<th width="160"><?php echo $lang["Steps and flow chart"];?></th>
					<th width="150"><?php echo $lang["Start time"];?></th>
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
								<em class="text-nowrap"><a class="<?php if ($run["flag"] == "1") : ?>xwb xcbu<?php else : ?>xcm<?php endif; ?>" title="<?php echo $run["runName"];?>" target="_blank" href="<?php echo Ibos::app()->createUrl("workflow/preview/print", array("key" => $run["key"]));?>"><?php echo StringUtil::cutStr($run["runName"], 25);?></a></em>
								<span class="fss tcm posa">[<?php echo $run["runid"];?>]<?php echo $run["typeName"];?></span>
							</div>
						</td>
						<td>
							<a data-toggle="usercard" data-param="uid=<?php echo $run["user"]["uid"];?>" href="<?php echo $run["user"]["space_url"];?>" class="avatar-circle" title="<?php echo $run["user"]["realname"];?>">
								<img src="<?php echo $run["user"]["avatar_middle"];?>" />
							</a>
							<span class="fss"><?php echo $run["user"]["realname"];?></span>
						</td>
						<td>
							<?php if ($run["type"] == "1") : ?>
								<span class="label"><?php echo $run["flowprocess"];?></span>
								<span class="fss">
									<a href="javascript:void(0);" data-click="viewFlow" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}"><?php echo $run["stepname"];?>
									<?php if (isset($run["sign"])) : ?>
										(<span class="type-host"><?php echo $lang["Sign"];?></span>)
									<?php endif; ?>
									</a>
								</span>
							<?php else : ?>
								<?php echo $run["stepname"];?>
							<?php endif; ?>
						</td>
						<td class="posr">
							<span class="art-list-time"><?php echo ConvertUtil::formatDate($run["begintime"]);?></span>
							<div class="right-btnbar">
								<div class="btn-toolbar pull-right">
									<div class="btn-group">
										<button type="button" class="btn dropdown-toggle btn-small" data-toggle="dropdown">
											<?php echo $lang["More"];?><span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu">
											<?php if (isset($run["turn"])) : ?>
												<li><a href="javascript:;" class="todo-transfer" data-click="turn" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;,&quot;type&quot;:&quot;<?php echo $run["type"];?>&quot;}"><?php echo $lang["Transfer"];?></a></li>
											<?php endif; ?>

											<?php if (isset($run["entrust"])) : ?>
												<li><a href="javascript:;" class="todo-entrust" data-click="entrust" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}"><?php echo $lang["Entrust"];?></a></li>
											<?php endif; ?>

											<?php if (isset($run["delay"])) : ?>
												<li><a href="javascript:;" class="todo-delay" data-click="delay" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}"><?php echo $lang["Delay"];?></a></li>
											<?php endif; ?>

											<?php if (isset($run["end"])) : ?>
												<li><a href="javascript:void(0);" data-click="end" data-param="{&quot;key&quot;: &quot;<?php echo $run["key"];?>&quot;}" class="todo-end"><?php echo $lang["End"];?></a></li>
											<?php endif; ?>
											<li><a href="javascript:;" class="todo-export" data-click="export" data-param="<?php echo $run["runid"];?>"><?php echo $lang["Export"];?></a></li>
											<?php if (isset($run["del"])) : ?>
												<li><a href="javascript:;" class="todo-delete" data-click="del" data-param="<?php echo $run["runid"];?>"><?php echo $lang["Delete"];?></a></li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
								<?php if (isset($run["host"]) || isset($run["sign"])) : ?>
									<a href="<?php echo Ibos::app()->createUrl("workflow/form/index", array("key" => $run["key"]));?>" class="btn btn-primary btn-small pull-right btn-host">
										<?php 
											if (isset($run["host"])) {
												echo $lang["Host"];
											} else {
												echo $lang["Sign"];
											}
										?>
									</a>
								<?php endif; ?>
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