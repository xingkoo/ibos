<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET;?>">
		<title><?php echo $run["name"];?></title>
		<style type="text/css">
			/* CSS Document */
			@charset "utf-8";
			body{ 
				font: 14px/20px Arial, 'Microsoft Yahei', 'Simsun', sans-serif;
				color: #58585C;
			}
			.main{ width: 840px; margin: 0 auto; }
			.main-content{ margin: 60px 0; }

			.form-content .view{padding:0;word-wrap:break-word;cursor:text;height:100%;}
			.form-content p{margin:5px 0;}
			.form-content /* Table */
			.form-content table.noBorderTable td,
			.form-content table.noBorderTable th,
			.form-content table.noBorderTable caption{border:1px dashed #999 !important}
			.form-content table{margin-bottom:10px;border-collapse:collapse;display:table;}
			.form-content td,th{ background:white; padding: 5px 10px;border: 1px solid #999;}
			.form-content caption{
				border: 1px solid #999;
				border-bottom: 0;
				padding: 10px;
				text-align: left;
				font-size: 16px;
				font-weight: 700;
			}
			.form-content th{border-top:2px solid #BBB;background:#F7F7F7;}
			.form-content td p{margin:0;padding:0;}
			/* List */
			/* 用户自定义样式 */
			<?php echo $css;?>
		</style>
	</head>
	<body>
		<div class="main">
            <!--表单 开始-->
            <div class="main-content">
                <div class="form-area">
                    <div class="form-content" id="container">
						<?php echo $model;?>
                    </div>
                </div>
            </div>
            <!--表单 结束-->
			<!--会签 开始-->
			<?php if (!empty($feedback)) : ?>
				<div class="main-content">
					<div class="form-content">
						<table width="100%">
							<caption><?php echo $lang["Sign"];?></caption>
							<tbody>
								<?php foreach ($feedback as $fb ) : ?>
								<tr>
									<td width="20%">
										<strong>第 <?php echo $fb["flowprocess"];?> 步</strong>
										<div><?php echo $fb["name"];?></div>
									</td>
									<td>
										<div>
											<strong><?php echo $fb["user"]["realname"];?>: </strong>
											<span><?php echo $fb["content"];?></span>
											<small>(<?php echo $fb["edittime"];?>)</small>
										</div>
										<?php if (isset($fb["reply"])) : ?>
											<ul>												
												<?php foreach ($fb["reply"] as $rp ) : ?>
												<li class="cmt-item">
													<div class="cmt-body fss">
														<p class="xcm">
															<!-- <a href="<?php echo $rp["user"]["space_url"];?>" class="anchor"><?php echo $rp["user"]["realname"];?>：</a> -->
															<strong><?php echo $rp["user"]["realname"];?>: </strong>
															<?php echo $rp["content"];?>
															<small>(<?php echo ConvertUtil::formatDate($rp["edittime"], "u");?>)</small>
														</p>
													</div>
												</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endif; ?>
			<!--会签 结束-->
			<!--步骤 开始-->
			<?php if (!empty($viewflow)) : ?>
				<div class="main-content">
					<div class="form-content">
						<table width="100%">
							<caption><?php echo $lang["Steps and flow chart"];?></caption>
							<tbody>
							<?php foreach ($viewflow as $key => $val ) : ?>
								<tr>
									<td>
										<strong>第 <?php echo $key;?> 步</strong>
										<div>
											<?php
												if (!isset($val["name"])) {
													echo Ibos::lang("Step", "", array("{step}" => $key));
												} else {
													echo "[". $val["name"] ."]";
												}
											?>
 
											<?php echo $lang["From"];?> [<?php echo $val["opuser"];?>] <?php echo $lang["Host work"];?>											
											<?php if ($val["flag"] == "2") : ?>
												<strong class="fss lhl xco ">(<?php echo $lang["Perform"];?></strong>)
											<?php endif; ?>
										</div>
									</td>
									<td>
										<?php if (($val["flag"] == "3") || ($val["flag"] == "4")) : ?>
											<p class="fss lhl"><?php echo $lang["Used time"];?>：<?php echo $val["timestr"];?></p>
											<p class="fss lhl"><?php echo $lang["Begin"];?>：<?php echo $val["processtime"];?></p>
											<?php if (!empty($val["delivertime"])) : ?>
												<p class="fss lhl"><?php echo $lang["End"];?>：<?php echo $val["delivertime"];?></p>
											<?php endif; ?>
										<?php elseif ($val["flag"] == "2") : ?>
											<p class="fss lhl"><?php echo $lang["Continuous"];?>：<?php echo $val["timestr"];?>

											<?php if ($val["timeoutflag"]) : ?>
												<?php echo $lang["Timelimit"] . $val["timeout"] . $lang["Hour"] . "," .	$lang["Timeout"] . ":" . $val["timeused"];?>
											<?php endif; ?>
											</p>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endif; ?>
        </div>
		<script>
			// 用户自定义脚本
<?php echo $script;?>
		</script>
	</body>
</html>