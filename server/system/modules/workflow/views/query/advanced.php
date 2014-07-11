<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<div class="mcr">
		<div class="page-list clearfix">
			<div class="page-list-header">
				<div class="pull-left">
					<span class="common-title"><?php echo $lang["Color identification"];?></span>
					<span class="type-dealing"><?php echo $lang["In handle"];?></span>
					<span class="type-finish"><?php echo $lang["Have been transferred"];?></span>
				</div>
				<a href="<?php echo $this->createUrl("query/index");?>" class="btn pull-right"><?php echo $lang["Return to query"];?></a>
			</div>
		</div>
		<?php if (!empty($flows)) : ?>
			<div id="flow_choose">
				<ul>
					<?php $counter = 0; ?>
					<?php foreach ($flows as $catId => $flow ) : ?>
						<?php $counter++; ?>
						<li class="fill-ss <?php if (($counter % 2) == 0) : ?>bglg<?php endif; ?>">
							<span class="common-title mbs"><?php echo $sort[$catId]["name"];?></span>
							<ul class="list-inline type-collection">
								<?php foreach ($flow as $ft ) : ?>
									<li>
										<a href="<?php echo $this->createUrl("query/search", array("flowid" => $ft["flowid"]));?>" class="curp dib">
											<div class="all-type">
												<?php echo $ft["name"];?>
											</div>
											<div class="type-numbers">
												<span class="dealing-nub"><?php echo $ft["handle"];?></span>
												<span class="finish-nub"><?php echo $ft["done"];?></span>
											</div>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<div class="no-data-tip"></div>
		<?php endif; ?>
	</div>
</div>