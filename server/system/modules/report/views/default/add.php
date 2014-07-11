<!-- private css -->
	<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/report.css?<?php echo VERHASH;?>">
	<link rel="stylesheet" href="<?php echo STATICURL;?>/css/emotion.css?<?php echo VERHASH;?>">

	<!-- Mainer -->
	<div class="wrap">
		<div class="mc clearfix">
			<!-- Sidebar -->
			<?php echo $this->getSidebar();?>
			<!-- Mainer right -->
			<form action="<?php echo $this->createUrl("default/add", array("op" => "save"));?>" id="report_form" method="post">
				<div class="mcr">
					<div class="page-list">
						<div class="ctform">
							<div class="mb">
								<input type="text" name="subject" id="" value="<?php echo $subject;?>">
							</div>
							<div>
								<label><?php echo $lang["Reporting to"];?></label>
								<input type="text" name="toid" id="rp_to" value="<?php if ($upUid) echo $upUid;?>">
								<div id="rp_to_box"></div>
							</div>
						</div>
						<div class="page-list-mainer">
							<table class="rp-detail-table">
								<!-- 工作小结 -->
								<tbody id="rp_complete">
									<tr>
										<td colspan="3">
											<div class="fill-ss">
												<div class="pull-right">
													<div class="calendar-group pull-left">
														<div class="datepicker form_datetime" id="date_summary_start">
															<a href="javascript:;" class="datepicker-btn" ></a>
															<input type="text" class="datepicker-input" id="summary_start_time" name="begindate" value="<?php echo $summaryAndPlanDate["summaryBegin"];?>">
														</div>
														<span class="sep"><?php echo $lang["To"];?></span>
														<div class="datepicker form_datetime" id="date_summary_end">
															<a href="javascript:;" class="datepicker-btn"></a>
															<input type="text" class="datepicker-input" id="summary_end_time" name="enddate" value="<?php echo $summaryAndPlanDate["summaryEnd"];?>">
														</div>
													</div>
													<div class="btn-group ml">
														<button type="button" class="btn" id="date_summary_prev" data-action="prevSummaryDate" data-param='{"type": "<?php echo $intervaltype;?>", "intervals": "<?php echo $intervals;?>" }'>
															<i class="glyphicon-chevron-left"></i>
														</button>
														<button type="button" class="btn" id="date_summary_next" data-action="nextSummaryDate" data-param='{"type": "<?php echo $intervaltype;?>", "intervals": "<?php echo $intervals;?>" }'>
															<i class="glyphicon-chevron-right"></i>
														</button>
													</div>
												</div>
												<h4><?php echo $lang["Work summary"];?></h4>
											</div>
										</td>
									</tr>
									<!-- 原计划 -->
									<?php if (!empty($orgPlanList)) : ?>
										<?php foreach ($orgPlanList as $k => $orgPlan ) : ?>
											<tr>
												<?php if ($k == 0) : ?>
													<th rowspan="<?php echo count($orgPlanList);?>" width="68" class="sep"><?php echo $lang["Original plan"];?></th>
												<?php endif; ?>
												<td width="3" class="sep"></td>
												<td>
													<div class="fill-sn">
														<div class="bamboo-pgb pull-right">
															<span class="pull-left xcn fss" id="processbar_info_<?php echo $orgPlan["recordid"];?>">100%</span>
															<span  data-toggle="bamboo-pgb" data-id="<?php echo $orgPlan["recordid"];?>"></span>
															<input type="hidden" id="processinput_<?php echo $orgPlan["recordid"];?>" name="orgPlan[<?php echo $orgPlan["recordid"];?>][process]" value="10">
														</div>
														<span class="rp-detail-num" data-toggle="badge"><?php echo $k + 1;?>.</span> <?php echo $orgPlan["content"];?>
														<div class="rp-exec-status">
															<input type="text" name="orgPlan[<?php echo $orgPlan["recordid"];?>][exedetail]" class="input-small span7" placeholder="<?php echo $lang["Implementation for click typing"];?>">
														</div>
													</div>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<!-- 计划外 -->
									<tr>
										<th rowspan="3" width="68" class="sep" id="rp_report_rowspan"><?php echo $lang["Outside plan"];?></th>
									</tr>
									<tr>
										<td class="sep" width="3"></td>
										<td>
											<div class="fill-sn">
												<div class="bamboo-pgb pull-right">
													<a href="javascript:;" class="o-trash cbtn pull-right ml" title="<?php echo $lang["Delete"];?>"></a>
													<span class="pull-left xcn fss" id="processbar_info_100">100%</span>
													<span data-toggle="bamboo-pgb" data-id="100"></span>
													<input type="hidden" id="processinput_100" name="outSidePlan[100][process]" value="10">
												</div>
												<span class="rp-detail-num" data-toggle="badge"><?php echo count($orgPlanList) + 1;?>".</span>
												<input type="text" name="outSidePlan[100][content]" class="rp-input span7" placeholder="<?php echo $lang["Click written summary"];?>">
											</div>
										</td>
									</tr>
									<tr>
										<td class="sep" width="3"></td>
										<td>
											<div class="fill-sn">
												<a href="javascript:;" class="add-one" id="rp_report_add">
													<i class="cbtn o-plus"></i>
													<?php echo $lang["Add one item"];?>
												</a>
											</div>
										</td>
									</tr>
								</tbody>
								<tbody>
									<!-- 工作总结 -->
									<tr>
										<th class="sep" width="68"><?php echo $lang["Work"];?><br /><?php echo $lang["Summary"];?></th>
										<td class="sep" width="3"></td>
										<td>
											<div style="min-height: 375px">
												<script type="text/plain" name="content" id="editor"></script>
											</div>
										</td>
									</tr>
									<!-- 附件 -->
									<tr>
										<th class="sep" width="68"><?php echo $lang["Attachement"];?></th>
										<td class="sep" width="3"></td>
										<td>
											<div class="att">
											<div class="attb">
												<span id="upload_btn"></span>
												<!-- 文件柜 -->
												<!--<button type="button" class="btn btn-icon vat"><i class="o-folder-close"></i></button>-->
											</div>
											<div>
												<div class="attl" id="file_target"></div>
											</div>
										</div>
										</td>
										<input type="hidden" name="attachmentid" id="attachmentid" />
									</tr>
								</tbody>
								<!-- 计划 -->
								<tbody id="rp_plan">
									<tr>
										<td colspan="3">
											<div class="fill-ss">
												<div class="pull-right">
													<div class="calendar-group pull-left">
														<div class="datepicker form_datetime" id="date_plan_start">
															<a href="javascript:;" class="datepicker-btn" ></a>
															<input type="text" class="datepicker-input" id="plan_start_time" name="planBegindate" value="<?php echo $summaryAndPlanDate["planBegin"];?>">
														</div>
														<span class="sep"><?php echo $lang["To"];?></span>
														<div class="datepicker form_datetime" id="date_plan_end">
															<a href="javascript:;" class="datepicker-btn" ></a>
															<input type="text" class="datepicker-input" id="plan_end_time" name="planEnddate" value="<?php echo $summaryAndPlanDate["planEnd"];?>">
														</div>
													</div>
													<div class="btn-group ml">
														<button type="button" class="btn" id="date_plan_prev" data-action="prevPlanDate" data-param='{"type": "<?php echo $intervaltype;?>", "intervals": "<?php echo $intervals;?>" }'>
															<i class="glyphicon-chevron-left"></i>
														</button>
														<button type="button" class="btn" id="date_plan_next" data-action="nextPlanDate" data-param='{"type": "<?php echo $intervaltype;?>", "intervals": "<?php echo $intervals;?>" }'>
															<i class="glyphicon-chevron-right"></i>
														</button>
													</div>
												</div>
												<h4><?php echo $lang["Work plan"];?></h4>
											</div>
										</td>
									</tr>
									<tr>
										<th width="68" rowspan="4" class="sep" id="rp_plan_rowspan"><?php echo $lang["Work"];?><br /><?php echo $lang["Plan"];?></th>
									</tr>
									<tr>
										<td class="sep" width="3"></td>
										<td>
											<div class="rp-plan-item fill">
												<div class="vernier rp-vernier-item"></div>
												<input type="hidden" name="nextPlan[100][reminddate]" class="remind-value" value="">
												<div class="rp-vernier-size"></div>
												<div class="posr">
													<div class="pull-right">
														<?php if ($isInstallCalendar) : ?>
															<div class="rp-remind-bar">
																<i class="o-clock"></i>
																<span class="remind-time"></span> 
																<a href="javascript:;" class="o-close-small"></a>
															</div>
															<a href="javascript:;" class="co-clock remind-time-btn" title="<?php echo $lang["Set remind"];?>"></a>
														<?php endif; ?>
														<a href="javascript:;" class="cbtn o-trash mlm" title="<?php echo $lang["Delete"];?>"></a>
													</div>
													<span class="rp-detail-num" data-toggle="badge">1.</span>
													<input type="text" name="nextPlan[100][content]" class="rp-input span7" placeholder="<?php echo $lang["Click written plan"];?>" value="">
												</div>
											</div>
										</td>
									</tr>
									<tr>
										<td class="sep" width="3"></td>
										<td>
											<div class="rp-plan-item fill">
												<div class="vernier rp-vernier-item"></div>
												<input type="hidden" name="nextPlan[101][reminddate]" class="remind-value" value="">	
												<div class="rp-vernier-size"></div>
												<div class="posr">
													<div class="pull-right">
														<?php if ($isInstallCalendar) : ?>
															<div class="rp-remind-bar">
																<i class="o-clock"></i>
																<span class="remind-time"></span> 
																<a href="javascript:;" class="o-close-small"></a>
															</div>
															<a href="javascript:;" class="co-clock remind-time-btn" title="<?php echo $lang["Set remind"];?>"></a>
														<?php endif; ?>
														<a href="javascript:;" class="cbtn o-trash mlm" title="<?php echo $lang["Delete"];?>"></a>
													</div>
													<span class="rp-detail-num" data-toggle="badge">2.</span>
													<input type="text" name="nextPlan[101][content]" class="rp-input span7" placeholder="<?php echo $lang["Click written plan"];?>" >
												</div>
											</div>
										</td>
									</tr>
									<tr>
										<td class="sep"></td>
										<td>
											<div class="fill-sn">
												<a href="javascript:;" class="add-one" id="rp_plan_add">
													<i class="cbtn o-plus"></i>
													<?php echo $lang["Add one item"];?>
												</a>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="fill-sn">
								<button type="button" class="btn btn-large btn-submit" onclick="history.back();"><?php echo $lang["Return"];?></button>
								<button type="submit" class="btn btn-large btn-submit btn-primary pull-right"><?php echo $lang["Save"];?></button>
							</div>
						</div>
					</div>
					<!-- Mainer content -->
				</div>
				<input type="hidden" name="typeid" value="<?php echo $typeid;?>" />
				<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
			</form>
		</div>
	</div>

	<!-- 新建工作小结模板 -->
	<script type="text/ibos-template" id="rp_complete_tpl">
		<tr>
			<td class="sep" width="3"></td>
			<td>
				<div class="fill">
					<div class="bamboo-pgb pull-right">
						<a href="javascript:;" class="o-trash cbtn pull-right ml" title="<?php echo $lang["Delete"];?>"></a>
						<span class="pull-left fss xcn" id="processbar_info_<%=id%>">100%</span>
						<span data-toggle="bamboo-pgb" data-id="<%=id%>"></span>
						<input type="hidden" id="processinput_<%=id%>" name="outSidePlan[<%=id%>][process]" value="10" />
					</div>
					<span class="rp-detail-num" data-toggle="badge"><%=id%>.</span>
					<input type="text" name="outSidePlan[<%=id%>][content]" class="rp-input span7" placeholder="<?php echo $lang["Click written summary"];?>">
				</div>
			</td>
		</tr>
	</script>
	<!-- 新建工作计划模板 -->
	<script type="text/ibos-template" id="rp_plan_tpl">
		<tr>
			<td class="sep" width="3"></td>
			<td>
				<div class="rp-plan-item fill">
					<div class="vernier rp-vernier-item"></div>
					<input type="hidden" name="nextPlan[<%=id%>][reminddate]" class="remind-value" value="">
					<div class ="rp-vernier-size"></div>
					<div class="posr">
						<div class="pull-right">
							<?php if ($isInstallCalendar) : ?>
								<div class="rp-remind-bar">
									<i class="o-clock"></i>
									<span class="remind-time"></span> 
									<a href="javascript:;" class="o-close-small"></a>
								</div>
								<a href="javascript:;" class="co-clock remind-time-btn" title="<?php echo $lang["Set remind"];?>"></a>
							<?php endif; ?>
							<a href="javascript:;" class="cbtn o-trash mlm" title="<?php echo $lang["Delete"];?>"></a>
						</div>
						<span class="rp-detail-num" data-toggle="badge"><%=id%></span>
						<input type="text" name="nextPlan[<%=id%>][content]" class="rp-input span7" placeholder="<?php echo $lang["Click written plan"];?>" >
					</div>
				</div>
			</td>
		</tr>
	</script>

	<script src='<?php echo STATICURL;?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/moment.min.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo $assetUrl;?>/js/report.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo $assetUrl;?>/js/reportcm.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo $assetUrl;?>/js/reportadd.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo $assetUrl;?>/js/reportType.js?<?php echo VERHASH;?>'></script>