<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET;?>">
		<title><?php echo $lang["Workflow"];?></title>
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/base.css?<?php echo VERHASH;?>">
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/common.css?<?php echo VERHASH;?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL;?>/css/iefix.css?<?php echo VERHASH;?>">
		<![endif]-->
		<style>
			.import-form-wrap{ width: 480px; }
			.import-form{ padding: 40px 40px 0; }
		</style>
	</head>
	<body>
		<div class="import-form-wrap">
			<?php if (!empty($id)) : ?>
				<div class="bulb-tip">
					<?php echo $lang["Form import tip"];?>
				</div>
			<?php endif; ?>
			<form enctype="multipart/form-data" method="post" action="<?php echo $this->createUrl("formtype/import");?>" id="import_form" class="import-form">
				<div>
					<input type="file" name="import" style="line-height: 20px">
				</div>
				<div class="mb tcm"><?php echo $lang["Form import desc"];?></div>
				<div>
					<?php echo $lang["After import"];?>：
					<label class="radio radio-inline"><input type="radio" name="nextopt" value="edit" checked><?php echo $lang["Edit attribute"];?></label>&nbsp;
					<label class="radio radio-inline"><input type="radio" name="nextopt" value="design"><?php echo $lang["Design form"];?></label>&nbsp;
					<label class="radio radio-inline"><input type="radio" name="nextopt" value="close"><?php echo $lang["Closed"];?></label>&nbsp;
				</div>
				<label><input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" /></label>
				<label><input type="hidden" name="catid" value="<?php echo $catid;?>" /></label>
				<label><input type="hidden" name="formid" value="<?php echo $id;?>" /></label>
			</form>
		</div>
		<script src="<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>"></script>
		<script src="<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>"></script>
	</body>
</html>