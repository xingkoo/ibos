<div class="wf-step-info">
	<div class="wf-step-info-title">
		<h4><?php echo $lang["Base info"];?></h4>
		<a href="javascript:;" data-event></a>
	</div>
	<ul>
		<li>
			<strong><?php echo $lang["Current step name"];?></strong>
			<p><?php echo $name;?></p>
		</li>
		<?php if (isset($pre)) : ?>
			<li>
				<strong><?php echo $lang["Pre step name"];?></strong>
				<p><?php echo implode(",", $pre);?></p>
			</li>
		<?php endif; ?>

		<?php if (isset($next)) : ?>
			<li>
				<strong><?php echo $lang["Next step name"];?></strong>
				<p><?php echo implode(",", $next);?></p>
			</li>
		<?php endif; ?>
	</ul>
</div>
<div class="wf-step-info">
	<h4><?php echo $lang["Form fields"];?></h4>
	<ul>
		<li>
			<strong><?php echo $lang["Can write field"];?></strong>
			<p title="<?php echo $processitem;?>"><?php echo $itemcount;?></p>
		</li>
		<li>
			<strong><?php echo $lang["Confidential fields"];?></strong>
			<p title="<?php echo $hiddenitem;?>"><?php echo $hiddencount;?></p>
		</li>
	</ul>
</div>
<div class="wf-step-info">
	<h4><?php echo $lang["Handle role"];?></h4>
	<ul>
		<?php if (!empty($user)) : ?>
			<li>
				<strong><?php echo $lang["User"];?></strong>
				<p><?php echo $user;?></p>
			</li>
		<?php endif; ?>

		<?php if (!empty($dept)) : ?>
			<li>
				<strong><?php echo $lang["Department"];?></strong>
				<p><?php echo $dept;?></p>
			</li>
		<?php endif; ?>

		<?php if (!empty($position)) : ?>
			<li>
				<strong><?php echo $lang["Position"];?></strong>
				<p><?php echo $position;?></p>
			</li>
		<?php endif; ?>
	</ul>
</div>


<?php if (isset($prcsout)) : ?>
	<div class="wf-step-info">
		<h4><?php echo $lang["Transfer conditions"];?></h4>
		<ul>
			<?php foreach ($prcsout as $key => $value ) : ?>
				<li>
					<strong><?php echo $value["name"];?></strong>
					<?php foreach (explode("\\n", $value["con"]) as $con ) : ?>
						<p><?php echo $con;?></p>
					<?php endforeach; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>