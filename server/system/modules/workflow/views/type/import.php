<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET;?>">
		<title><?php echo $lang["Workflow"];?></title>
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL;?>/css/base.css?<?php echo VERHASH;?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL;?>/css/iefix.css?<?php echo VERHASH;?>">
		<![endif]-->
	</head>
	<body>
		<div style="padding: 20px; width: 400px;">
			<form class="form-horizontal" method="post" enctype="multipart/form-data" action="<?php echo $this->createUrl("type/import");?>" id="import_form">
				<div class="control-group">
					<label class="control-label">&nbsp;</label>
					<div class="controls">
						<input type="file" name="import" style="line-height: 20px">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">&nbsp;</label>
					<div class="controls">
						<label>
							<input type="checkbox" name="useron" value="1" /><?php echo $lang["Import user info"];?>
							<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
							<input type="hidden" name="typeSubmit" value="1" />
							<input type="hidden" name="flowid" value="<?php echo $flowId;?>" />
							<span class="label label-danger">(<?php echo $lang["Import tips"];?>)</span>
						</label>
					</div>
				</div>
			</form>
		</div>
	</body>
</html>