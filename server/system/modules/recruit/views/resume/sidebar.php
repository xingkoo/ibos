<!-- load css -->

<div class="aside">
	<div class="sbbf">
		<ul class="nav nav-strip nav-stacked">
			<li <?php if (Ibos::app()->controller->id == "resume") echo ' class="active" ';>
				<a href="<?php echo $this->createUrl("resume/index");?>">
					<i class="o-rct-talents"></i>
					<?php echo Ibos::lang("Talent management");?>
				</a>
			</li>
			<li <?php if (Ibos::app()->controller->id == "contact") echo ' class="active" ';?> >
				<a href="<?php echo $this->createUrl("contact/index");?>">
					<i class="o-rct-interview"></i>
					<?php echo Ibos::lang("Contact record");?>
				</a>
			</li>
			<li <?php if (Ibos::app()->controller->id == "interview") echo ' class="active" ';?> >
				<a href="<?php echo $this->createUrl("interview/index");?>">
					<i class="o-rct-backdrop"></i>
					<?php echo Ibos::lang("Interview management");?>
				</a>
			</li>
			<li <?php if (Ibos::app()->controller->id == "bgchecks") echo ' class="active" ';?> >
				<a href="<?php echo $this->createUrl("bgchecks/index");?>">
					<i class="o-rct-contact"></i>
					<?php echo Ibos::lang("Background investigation");?>
				</a>
			</li>
			<?php if (ModuleUtil::getIsEnabled("statistics") && isset($statModule["recruit"])) : ?>
				<?php echo $this->widget(StatCommonUtil::getWidgetName("recruit", StatConst::SIDEBAR_WIDGET), array(), true);?>
			<?php endif; ?>
		</ul>
	</div>
</div>