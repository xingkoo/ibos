<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<!--右栏 开始-->
	<div class="mcr">
		<!--右栏头部导航栏 开始-->
		<div class="mc-header">
			<ul class="mnv clearfix mnv-entrust">
				<li>
					<a href="<?php echo $this->createUrl("entrust/index", array("op" => "rule"));?>">
						<i class="o-nav-rule"></i>
						<?php echo $lang["Entrust rule"];?>
					</a>
				</li>
				<li>
					<a href="<?php echo $this->createUrl("entrust/index", array("op" => "record"));?>">
						<i class="o-nav-record"></i>
						<?php echo $lang["Entrust record"];?>
					</a>
				</li>
				<li class="active">
					<a href="<?php echo $this->createUrl("entrust/index", array("op" => "berule"));?>">
						<i class="o-nav-berule"></i>
						<?php echo $lang["Be entrusted rules"];?>
					</a>
				</li>
				<li>
					<a href="<?php echo $this->createUrl("entrust/index", array("op" => "berecord"));?>">
						<i class="o-nav-berecord"></i>
						<?php echo $lang["Be entrusted record"];?>
					</a>
				</li>
			</ul>
		</div>
		<!--右栏头部导航栏 结束-->
		<div class="page-list clearfix">
			<!--右栏列表 开始-->
			<div class="page-list-mainer xcm">
				<?php if (!empty($list)) : ?>
					<table class="table table-hover table-striped table-record">
						<thead>
							<tr>
								<th width="50"><?php echo $lang["Status"];?></th>
								<th width="300"><?php echo $lang["Flow type"];?></th>
								<th><?php echo $lang["Entrust originator"];?></th>
								<th width="180"><?php echo $lang["Period of validity"];?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($list as $key => $val ) :?>
								<tr class="<?php if ($key == 0) : ?>active<?php endif; ?>">
									<td>
										<i class="o-usable"></i>
									</td>
									<td>
										<?php echo $val["typeName"];?>
									</td>
									<td>
										<a data-toggle="usercard" data-param="uid=<?php echo $val["user"]["uid"];?>" href="<?php echo $val["user"]["space_url"];?>" class="avatar-circle" title="<?php echo $val["user"]["realname"];?>">
											<img src="<?php echo $val["user"]["avatar_middle"];?>" />
										</a>
										<span class="fss"><?php echo $val["user"]["realname"];?></span>
									</td>
									<td>
										<span class="fss"><?php echo $val["datedesc"];?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<div class="page-list-footer">
				<div class="page-num-select">
					<div class="btn-group dropup">
						<?php $pageSize = $pages->getPageSize();?>
						<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("entrust/index", array("op" => $op));?>">
							<i class="o-setup"></i><span><?php echo $lang["Each page"];?> <?php echo $pageSize;?></span><i class="caret"></i>
						</a>
						<ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo $this->createUrl("entrust/index", array("op" => $op));?>" >
							<li data-value="10" <?php if ($pageSize == 10) : ?> class="active" <?php endif; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 10</a></li>
							<li data-value="20" <?php if ($pageSize == 20) : ?> class="active" <?php endif; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 20</a></li>
							<li data-value="30" <?php if ($pageSize == 30) : ?> class="active" <?php endif; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 30</a></li>
							<li data-value="40" <?php if ($pageSize == 40) : ?> class="active" <?php endif; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 40</a></li>
							<li data-value="50" <?php if ($pageSize == 50) : ?> class="active" <?php endif; ?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 50</a></li>
						</ul>
					</div>
				</div>
				<div class="pull-right">
					<?php $this->widget("IWPage", array("pages" => $pages));?>
				</div>
			</div>
		</div>
	</div>
</div>
