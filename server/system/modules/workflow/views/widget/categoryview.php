<div class="classity-content">
	<?php if (!empty($list)) : ?>
		<ul class="content-ul">
			<?php $counter = 0; ?>
			<?php foreach ($list as $catId => $flows ) : ?>
				<?php $counter++; ?>
				<li <?php if (($counter % 2) == 0) echo 'class="bglg"';?> >
					<span class="classity-title"><?php echo $catSort[$catId]["name"];?></span>
					<?php if (!empty($flows)) : ?>
						<ul class="content-box clearfix">
							<?php foreach ($flows as $flow ) : ?>
								<li>
									<a class="wf-info-box" href="<?php echo Ibos::app()->createUrl("workflow/list/index", array("op" => "list", "type" => $type, "sort" => $sort, "flowid" => $flow["flowid"]));?>">
										<div class="classity-name">
											<?php echo $flow["name"];?>
											<?php if (isset($flow["unreceive"]) && !empty($flow["unreceive"])) : ?>
												<span class="badge prompt-info"><?php echo $flow["unreceive"];?></span>
											<?php endif; ?>
										</div>
										<div class="classity-number"><?php echo $flow["count"];?></div>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<div class="no-data-tip"></div>
	<?php endif; ?>
</div>