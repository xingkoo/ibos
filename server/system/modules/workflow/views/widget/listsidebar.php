<div class="aside">
	<div class="sbbf sbbf sbbl">
		<div class="sbbf-top">
			<a href="<?php echo Ibos::app()->createUrl("workflow/new/index");?>" class="btn btn-warning btn-work-flow">
				<i class="o-list-write"></i><?php echo $lang["Start work"];?>
			</a> 
		</div>
		<div class="sbb sbbf">
			<ul class="nav nav-stacked nav-strip">
				<li <?php if ($control == "list") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/list/index");?>">
						<i class="o-list-work"></i>
						<?php echo $lang["My work"];?>
						<span class="badge pull-right" data-count="new" style="display: none;"></span>
					</a>
				</li>
				<li <?php if ($control == "focus") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/focus/index");?>">
						<i class="o-list-attention"></i>
						<?php echo $lang["My focus"];?>
						<span class="badge pull-right" data-count="focus" style="display: none;"></span>
					</a>
				</li>
				<li <?php if ($control == "entrust") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/entrust/index");?>">
						<i class="o-list-entrust"></i>
						<?php echo $lang["Work entrust"];?>
					</a>
				</li>
				<li <?php if ($control == "recycle") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/recycle/index");?>">
						<i class="o-list-destroy"></i>
						<?php echo $lang["Work recycle"];?>
						<span class="badge pull-right" data-count="recycle" style="display: none;"></span>
					</a>
				</li>
				<li <?php if ($control == "query") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/query/index");?>">
						<i class="o-list-search"></i>
						<?php echo $lang["Work query"];?>
					</a>
				</li>
				<li <?php if ($control == "monitor") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/monitor/index");?>">
						<i class="o-list-monitoring"></i>
						<?php echo $lang["Work monitor"];?>
					</a>
				</li>
			</ul>
		</div>
		<div>
			<ul class="nav nav-stacked nav-strip">
	            <li <?php if ($control == "type") echo ' class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/type/index");?>">
						<i class="o-wf-manage"></i>
						<?php echo $lang["Workflow manager"];?>
					</a>
					<?php if ($control == "type") : ?>
						<ul class="sbb-list">
							<?php foreach ($category as $cat ) : ?>
								<li <?php if ($catId == $cat["catid"]) echo 'class="active"';?> ><a href="<?php echo Ibos::app()->createUrl("workflow/type/index", array("catid" => $cat["catid"]));?>"><?php echo $cat["name"];?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
				<li <?php if ($control == "formtype") echo 'class="active"';?> >
					<a href="<?php echo Ibos::app()->createUrl("workflow/formtype/index");?>">
						<i class="o-wf-form"></i>
						<?php echo $lang["Form library manager"];?>
					</a>
					<?php if ($control == "formtype") : ?>
						<ul class="sbb-list">
							<?php foreach ($category as $cat ) : ?>
								<li <?php if ($catId == $cat["catid"]) echo 'class="active"';?> ><a href="<?php echo Ibos::app()->createUrl("workflow/formtype/index", array("catid" => $cat["catid"]));?>"><?php echo $cat["name"];?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			</ul>
		</div>
	</div>
</div>