<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--左栏导航栏 开始-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<!--左栏导航栏 结束-->
	<!--右栏 开始-->
	<div class="mcr">
		<!--右栏头部标题 开始-->
		<div class="fill-nn bdbs">
			<span class="fsl xwb">查询结果</span>
		</div>
		<!--右栏头部标题 结束-->
		<div class="page-list clearfix">
			<!--右栏列表功能栏 开始-->
			<div class="page-list-header chl">
				<a href="<?php echo $this->createUrl("query/search", array("flowid" => $flowid));?>" class="btn" ><?php echo $lang["Return"];?></a>
				<?php if ($advanceOpt) : ?>
					<button type="button" class="btn" data-click="export"><?php echo $lang["Export"];?>ZIP</button>
					<button type="button" class="btn" data-click="admindel"><?php echo $lang["Admin delete"];?></button>
					<button type="button" class="btn" data-click="forceend" ><?php echo $lang["Force end"];?></button>
				<?php endif; ?>
			</div>
			<!--右栏列表功能栏 结束-->
			<!--右栏列表 开始-->
			<div class="page-list-mainer xcm">
				<?php if (!empty($list)) : ?>
					<table class="table table-hover table-striped table-query">
						<thead>
						<th width="16">
							<label class="checkbox">
								<input type="checkbox" data-name="id[]" />
							</label>
						</th>
						<th><?php echo $lang["Name"];?></th>
						<th width="20"></th>
						<th width="100"><?php echo $lang["Originator"];?></th>
						<th width="102"><?php echo $lang["Current state"];?></th>
						<th width="150"><?php echo $lang["Start time"];?></th>
						<th width="20"></th>
						</thead>
						<tbody>
							<?php foreach ($list as $val ) : ?>
								<tr id="list_tr_<?php echo $val["runid"];?>">
									<td>
										<label class="checkbox">
											<input type="checkbox" name="id[]" value="<?php echo $val["runid"];?>" />
										</label>
									</td>
									<td>
										<div class="com-list-name">
											<em class="text-nowrap"><a target="_blank" href="<?php echo $this->createUrl("preview/print", array("key" => $val["key"]));?>" class="xcm"><?php echo $val["runName"];?></a></em>
											<span class="fss tcm posa">[<?php echo $val["runid"];?>]<?php echo $val["typeName"];?></span>
										</div>
									</td>
									<td>
										<?php if (!empty($val["attachmentid"])) : ?>
											<?php
												$original_title = '<ul>';
												foreach ($val["attachdata"] as $attach ) {
													$original_title .= '<li>' . $attach["filename"] .'</li>';
												}
												$original_title .= '</ul>';
											?>
											<div class="fill-mm q-attachment-area" data-placement="bottom" data-html="true" data-original-title="<?php echo CHtml::encode($original_title);?>">
												<a href="javascript:void(0);" class="o-attachment"></a>
											</div>
										<?php endif; ?>
									</td>
									<td>
										<a data-toggle="usercard" data-param="uid=<?php echo $val["user"]["uid"];?>" href="<?php echo $val["user"]["space_url"];?>" class="avatar-circle" title="<?php echo $val["user"]["realname"];?>">
											<img src="<?php echo $val["user"]["avatar_middle"];?>" />
										</a>
										<span class="fss"><?php echo $val["user"]["realname"];?></span>
									</td>
									<td>
										<?php if ($val["endtime"] == 0) : ?>
											<span class="fss xcgn"><?php echo $lang["Perform"];?></span>
										<?php else : ?>
											<span class="fss tcm"><?php echo $lang["Has ended"];?></span>
										<?php endif; ?>
									</td>
									<td class="posr">
										<span class="art-list-time tcm"><?php echo $val["begin"];?></span>
										<div class="right-btnbar">
											<div class="btn-toolbar pull-right">
												<div class="btn-group">
													<button type="button" class="btn dropdown-toggle btn-small" data-toggle="dropdown">
														<?php echo $lang["More"];?><span class="caret"></span>
													</button>
													<ul class="dropdown-menu" role="menu">
														<?php if (($val["flag"] == "1") || ($val["flag"] == "2")) : ?>
															<li><a href="<?php echo $this->createUrl("form/index", array("key" => $val["key"]));?>" target="_blank"><?php echo $lang["Handle"];?></a></li>
														<?php endif; ?>
														
														<?php if ($val["isend"]) : ?>
															<?php if (Ibos::app()->user->isadministrator == 1) : ?>
																<li><a href="javascript:void(0);" data-click="restorerun" data-param="{&quot;runid&quot;: &quot;<?php echo $val["runid"];?>&quot;}"><?php echo $lang["Restore run"];?></a></li>
															<?php endif; ?>
															
															<?php if ($val["editper"]) : ?>
																<li><a href="javascript:void(0);" data-click="edit" data-param="{&quot;runid&quot;: &quot;<?php echo $val["runid"];?>&quot;&quot;flowid&quot;: &quot;<?php echo $val["flowid"];?>&quot;}">编辑</a></li>
															<?php endif; ?>
														<?php endif; ?>
													</ul>
												</div>
											</div>
											<a href="javascript:void(0);" style="margin-right:5px;" data-click="viewFlow" data-param="{&quot;key&quot;: &quot;<?php echo $val["key"];?>&quot;}" class="btn btn-primary btn-small pull-left flow-map"><?php echo $lang["Flow chart"];?></a>
										</div>
									</td>
									<td>
										<i class="<?php if ($val["focus"]) : ?>o-ck-attention<?php else : ?>o-tr-attention<?php endif; ?> pull-right" data-param="<?php echo $val["runid"];?>" data-click="focus"></i>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<!--右栏列表 结束-->
			<!--右栏底部功能栏 开始-->
			<div class="page-list-footer">
				<div class="page-num-select">
					<div class="btn-group dropup">
						<?php $pageSize = $pages->getPageSize();?>
						<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("query/index");?>">
							<i class="o-setup"></i><span><?php echo $lang["Each page"];?> <?php echo $pageSize;?></span><i class="caret"></i>
						</a>
						<ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo $this->createUrl("query/index");?>" >
							<li data-value="10" <?php if ($pageSize == 10) echo 'class="active"'; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 10</a></li>
							<li data-value="20" <?php if ($pageSize == 20) echo 'class="active"'; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 20</a></li>
							<li data-value="30" <?php if ($pageSize == 30) echo 'class="active"'; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 30</a></li>
							<li data-value="40" <?php if ($pageSize == 40) echo 'class="active"'; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 40</a></li>
							<li data-value="50" <?php if ($pageSize == 50) echo 'class="active"'; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 50</a></li>
						</ul>
					</div>
				</div>
				<div class="pull-right">
					<?php $this->widget("IWPage", array("pages" => $pages));?>
				</div>
			</div>
		</div>
		<!--右栏 结束-->
	</div>
</div>
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/wfquery.js?<?php echo VERHASH;?>'></script>