
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
	</div>
	<div>
		<form action="" class="form-horizontal">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
				<div class="xac mtg">
					<div class="dib">
						<i class="o-success-image"></i>
					</div>
					<div class="success-tip-info">
						<div class="info-content">
							<?php echo $data['msg']; ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	(function() {
		$('#back_home').on('click', function() {
			window.parent.location.href = "<?php echo $this->createUrl( 'default/index' ); ?>";
		});
	})();
</script>