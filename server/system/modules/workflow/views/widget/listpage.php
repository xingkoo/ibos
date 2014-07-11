<div class="page-list-footer">
	<div class="page-num-select">
		<div class="btn-group dropup">
			<?php $pageSize = $pages->getPageSize();?>
			<a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo Ibos::app()->createUrl("workflow/list/index", array("op" => "list", "type" => $type, "sort" => $sort));?>">
				<i class="o-setup"></i><span><?php echo $lang["Each page"];?> <?php echo $pageSize;?></span><i class="caret"></i>
			</a>
			<ul class="dropdown-menu" id="page_num_menu" data-url="<?php echo Ibos::app()->createUrl("workflow/list/index", array("op" => $op, "type" => $type, "sort" => $sort));?>" >
				<li data-value="10" <?php if ($pageSize == 10) echo  'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 10</a></li>
				<li data-value="20" <?php if ($pageSize == 20) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 20</a></li>
				<li data-value="30" <?php if ($pageSize == 30) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 30</a></li>
				<li data-value="40" <?php if ($pageSize == 40) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 40</a></li>
				<li data-value="50" <?php if ($pageSize == 50) echo 'class="active"';?> ><a href="javascript:;"><?php echo $lang["Each page"];?> 50</a></li>
			</ul>
		</div>
	</div>
	<div class="pull-right">
		<?php $this->widget("IWPage", array("pages" => $pages));?>
	</div>
</div>