<li <?php if ($inPersonal) echo 'class="active"';?>>
	<a href="<?php echo Ibos::app()->createUrl("report/stats/personal");?>">
		<i class="os-personal-statistic"></i>
		<?php echo Ibos::lang("Personal statistics");?>
	</a>

	<?php if ($inPersonal) : ?>
		<?php echo $this->getController()->widget("IWReportType", array("type" => "personal"), true);?>
	<?php endif; ?>
</li>


<?php if ($hasSub) : ?>
<li <?php if ($inReview) echo 'class="active"';?> >
	<a href="<?php echo Ibos::app()->createUrl("report/stats/review");?>">
		<i class="os-statistics"></i>
		<?php echo Ibos::lang("Review statistics");?>
	</a>
	
	<?php if ($inReview) : ?>
		<?php echo $this->getController()->widget("IWReportType", array("type" => "review"), true);
		echo $this->getController()->widget("IWReportSublist", array("stats" => true), true);?>
	<?php endif; ?>
</li>
<?php endif; ?>