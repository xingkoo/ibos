<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!--sidebar-->
	<?php echo $this->widget("IWWfListSidebar", array(), true);?>
	<!--右栏 开始-->
	<div class="mcr">
		<div class="page-list clearfix">
			<!--右栏头部功能栏 开始-->
			<div class="page-list-header">
				<div class="pull-left">
					<select class="form-control pull-left select-plan" id="select_prepare_project">
						<option value=""><?php echo $lang["Select query preparatory scheme"];?></option>
						<?php foreach ($tpls as $opt ) : ?>
							<option value="<?php echo $opt["seqid"];?>" <?php if ($id == $opt["seqid"]) echo "selected";?> ><?php echo $opt["tplname"];?></option>
						<?php endforeach; ?>
					</select>
					<button type="button" class="btn pull-left btn-save" id="save_prepare_project">
						<i class="o-save"></i>
					</button>
				</div>
			</div>
			<!--右栏头部功能栏 结束-->
			<!--高级查询条件选择区域 开始-->
			<div class="fill-nn">
				<form method="post" class="form-horizontal form-narrow" id="query_form">
					<div class="query-condition-from">
						<div class="mb ovh posr">
							<span class="condition-title"><?php echo $lang["Flow base info"];?></span>
							<span class="qf-divider"></span>
						</div>
						<div class="control-group"> 
							<label class="control-label"><?php echo $lang["Flow name"];?></label>
							<div class="controls">
								<span class="fill-mm fsl dib"><?php echo $flow["name"];?></span>
							</div>
						</div>
						<div class="control-group"> 
							<label class="control-label"><?php echo $lang["Query scope"];?></label>
							<div class="controls">
								<select name="flow_query_type" class="form-control qf-short-select" id="query_range">
									<option value="all" <?php if ($edit && ($tpl["flow"]["flowquerytype"] == "all")) echo "selected";?> ><?php echo $lang["Query scope all"];?></option>
									<option value="1" <?php if ($edit && ($tpl["flow"]["flowquerytype"] == "1")) echo "selected";?> ><?php echo $lang["Query scope 1"];?></option>
									<option value="2" <?php if ($edit && ($tpl["flow"]["flowquerytype"] == "2")) echo "selected";?> ><?php echo $lang["Query scope 2"];?></option>
									<option value="3" <?php if ($edit && ($tpl["flow"]["flowquerytype"] == "3")) echo "selected";?> ><?php echo $lang["Query scope 3"];?></option>
								</select>
							</div>
						</div>
						<div class="control-group"> 
							<label class="control-label"><?php echo $lang["Flow initiator"];?></label>
							<div class="controls">
								<input type="text" name="begin_user" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["beginuser"];?>"<?php endif; ?> id="flow_originator" placeholder="<?php echo $lang["Specify the originator"];?>" />
							</div>
						</div>
						<div class="control-group"> 
							<label class="control-label"><?php echo $lang["Flow subject/num"];?></label>
							<div class="controls">
								<input type="text" id="query_name" name="run_name" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["runname"];?>"<?php endif; ?> />
							</div>
						</div>
						<div class="control-group"> 
							<label class="control-label"><?php echo $lang["Flow status"];?></label>
							<div class="controls">
								<select name="flow_status" class="form-control qf-short-select" id="flow_state">
									<option value="all" <?php if ($edit && ($tpl["flow"]["flowstatus"] == "all")) echo "selected";?> ><?php echo $lang["All"];?></option>
									<option value="1" <?php if ($edit && ($tpl["flow"]["flowstatus"] == "1")) echo "selected";?> ><?php echo $lang["Being performed"];?></option>
									<option value="0" <?php if ($edit && ($tpl["flow"]["flowstatus"] == "0")) echo "selected";?> ><?php echo $lang["Has ended"];?></option>
								</select>
							</div>
						</div>
						<div class="control-group clearfix"> 
							<label class="control-label"><?php echo $lang["Flow begin date scope"];?></label>
							<div class="controls">
								<select class="form-control pull-left qf-short-select" id="start_time_range">
									<option selected><?php echo $lang["Quick choose"];?></option>
									<option value="1"><?php echo $lang["Today"];?></option>
									<option value="2"><?php echo $lang["Yesterday"];?></option>
									<option value="3"><?php echo $lang["This week"];?></option>
									<option value="4"><?php echo $lang["Last week"];?></option>
									<option value="5"><?php echo $lang["This month"];?></option>
									<option value="6"><?php echo $lang["Last month"];?></option>
								</select>
								<div class="span4 pull-left qf-time-grorp">
									<div class="datepicker input-group" id="s_date_start">
										<span class="input-group-addon">从</span>
										<a href="javascript:;" class="datepicker-btn"></a>
										<input type="text" name="time1" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["time1"];?>"<?php endif; ?> class="datepicker-input"/>
									</div>
								</div>
								<div class="span4 pull-left qf-time-grorp">
									<div class="datepicker input-group" id="s_date_end">
										<span class="input-group-addon">至</span>
										<a href="javascript:;" class="datepicker-btn"></a>
										<input type="text" name="time2" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["time2"];?>"<?php endif; ?> class="datepicker-input"/>
									</div>
								</div>
								<button type="button" class="btn pull-left btn-query-del" id="strat_time_clear">
									<i class="glyphicon-trash"></i>
								</button>
							</div>
						</div>
						<div id="end_time_box" class="control-group clearfix" <?php if ($edit && ($tpl["flow"]["flowstatus"] !== "0")) : ?> style="display: none;"<?php elseif (!$edit) : ?> style="display: none;"<?php endif; ?> > 
							<label class="control-label"><?php echo $lang["Flow ended date scope"];?></label>
							<div class="controls">
								<select class="form-control pull-left qf-short-select" id="end_time_range">
									<option selected><?php echo $lang["Quick choose"];?></option>
									<option value="1"><?php echo $lang["Today"];?></option>
									<option value="2"><?php echo $lang["Yesterday"];?></option>
									<option value="3"><?php echo $lang["This week"];?></option>
									<option value="4"><?php echo $lang["Last week"];?></option>
									<option value="5"><?php echo $lang["This month"];?></option>
									<option value="6"><?php echo $lang["Last month"];?></option>
								</select>
								<div class="span4 pull-left qf-time-grorp">
									<div class="datepicker input-group" id="e_date_start">
										<span class="input-group-addon">从</span>
										<a href="javascript:;" class="datepicker-btn"></a>
										<input type="text" name="time3" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["time3"];?>"<?php endif; ?> class="datepicker-input" />
									</div>
								</div>
								<div class="span4 pull-left qf-time-grorp">
									<div class="datepicker input-group" id="e_date_end">
										<span class="input-group-addon">至</span>
										<a href="javascript:;" class="datepicker-btn"></a>
										<input type="text" name="time4" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["time4"];?>"<?php endif; ?> class="datepicker-input" />
									</div>
								</div>
								<button type="button" class="btn pull-right btn-query-del" id="end_time_clear">
									<i class="glyphicon-trash"></i>
								</button>
							</div>
						</div>
						<div class="control-group"> 
							<label class="control-label"><?php echo $lang["Global attach name"];?></label>
							<div class="controls">
								<input type="text" name="attach_name" <?php if ($edit) : ?>value="<?php echo $tpl["flow"]["attachname"];?>"<?php endif; ?> id="attachment_name">
							</div>
						</div>
					</div>
					<div id="form_condition">
						<div class="mb ovh posr">
							<span class="qf-divider"></span>
							<span class="condition-title"><?php echo $lang["Form data condition"];?></span>
							<a href="javascript:;" class="pull-right condition-showmore" id="condition_show_more"><span><?php echo $lang["Open message"];?></span><i class="caret"></i></a>
						</div>
						<div class="condition-content" id="step_condition">
							<div class="control-group"> 
								<label class="control-label"><?php echo $lang["Form data condition"];?></label>
								<div class="controls">
									<div class="condition-block">
										<div class="condition-select">
											<select class="form-control qf-long-select" id="condition_field">
												<option value=""><?php echo $lang["Please select"];?></option>
												<?php foreach ($title as $index => $value ) : ?>
													<option value="<?php echo $value["key"];?>"><?php echo $value["title"];?></option>
												<?php endforeach; ?>
											</select>
											<select class="form-control qf-short-select" id="condition_operator">
												<option value="="><?php echo $lang["Equal"];?></option>
												<option value="<>"><?php echo $lang["Not equal to"];?></option>
												<option value=">"><?php echo $lang["Greater than"];?></option>
												<option value="<"><?php echo $lang["Less than"];?></option>
												<option value=">="><?php echo $lang["Greater than or equal to"];?></option>
												<option value="<="><?php echo $lang["Less than or equal to"];?></option>
												<option value="include"><?php echo $lang["Contains"];?></option>
												<option value="exclude"><?php echo $lang["Not contain"];?></option>
											</select>
											<input type="text" class="condition-text" placeholder="" id="condition_value" />
										</div>
										<div class="condition-btnarea">
											<div class="btn-group" id="condition_logic" data-toggle="buttons-radio">
												<button class="btn xcn active" type="button" data-value="AND"><?php echo $lang["With"];?></button>
												<button class="btn xcn" type="button" data-value="OR"><?php echo $lang["Or"];?></button>
											</div>
											<input type="hidden" id="condition_logic_input">
											<div class="btn-group">
												<button type="button" class="btn xcn" data-condition="addLeftParenthesis">（</button>
												<button type="button" class="btn xcn" data-condition="addRightParenthesis">）</button>
											</div>
											<button type="button" class="btn pull-right btn-query-del" data-condition="removeCondition">
												<i class="glyphicon-trash"></i>
											</button>
											<button type="button" class="btn btn-primary pull-right" data-condition="addCondition"><?php echo $lang["Add condition"];?></button>
										</div>
										<div class="condition-area">
											<select class="xcm" size="9" id="condition_select" data-select='condition' multiple>
												<?php if (!empty($conArr)) : ?>
													<?php foreach ($conArr as $con ) : ?>
														<option><?php echo $con;?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
											<input type="hidden" name="condformula" id="condition_result" <?php if ($edit) : ?>value="<?php echo $tpl["condformula"];?>"<?php endif; ?> >
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="export-options">
						<div class="mb ovh posr">
							<span class="condition-title"><?php echo $lang["Statistics options"];?></span>
							<span class="qf-divider"></span>
							<a href="javascript:;" class="pull-right options-showmore" id="options_show_more"><span><?php echo $lang["Open message"];?></span><i class="caret"></i></a>
						</div>
						<div class="options-content" id="options_content">
							<div class="control-group"> 
								<label class="control-label"><?php echo $lang["Group field"];?></label>
								<div class="controls">
									<select name="group_field" class="form-control qf-long-select" id="group_by_name">
										<?php foreach (array_merge($deftitle, $title) as $index => $value ) : ?>
											<option value="<?php echo $value["key"];?>" <?php if ($edit && ($value["key"] == $tpl["group"]["field"])) echo "selected";?> ><?php echo $value["title"];?></option>
										<?php endforeach; ?>
									</select>
									<select name="group_sort" class="form-control qf-short-select" id="sort_type">
										<option value="asc" <?php if ($edit && ($tpl["group"]["order"] == "asc")) echo "selected";?> ><?php echo $lang["Ascending"];?></option>
										<option value="desc" <?php if ($edit && ($tpl["group"]["order"] == "desc")) echo "selected";?> ><?php echo $lang["Descending"];?></option>
									</select>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label"><?php echo $lang["Statistical field"];?></label>
								<div class="controls">
									<div class="field-left pull-left">
										<ul class="field-ul">
											<li>
												<span class="display-title"><?php echo $lang["According to the following fields as table column"];?></span>
											</li>
											<li>
												<select multiple class="form-control" id="show_select" size="13">
													<?php $titleArr = array_merge($deftitle, $title);?>
													<?php foreach ($titleArr as $index => $value ) : ?>
														<?php if (!$edit || ($edit && (in_array($value["key"], $tpl["viewfields"]) || empty($tpl["viewfields"])))) : ?>
															<option value="<?php echo $value["key"];?>"><?php echo $value["title"];?></option>
															<?php echo unset($titleArr[$index]);?>
														<?php endif; ?>
													<?php endforeach; ?>
												</select>
											</li>
											<li>
												<button type="button" class="btn xcn" id="show_select_all"><?php echo $lang["Select all"];?></button>
											</li>
										</ul>
									</div>
									<div class="field-middle pull-left">
										<div class="xcn mbs"><button type="button" class="btn" id="turn_hidden"><i class="o-turn-hidden"></i></button></div>
										<div class="xcn mbs"><button type="button" class="btn" id="turn_show"><i class="o-turn-show"></i></button></div>
									</div>
									<div class="field-rightt pull-left">
										<ul class="field-ul">
											<li>
												<span class="display-title"><?php echo $lang["Dont show the following fields"];?></span>
											</li>
											<li>
												<select multiple class="form-control" id="hidden_select" size="13">
													<?php foreach ($titleArr as $index => $value ) : ?>
														<option value="<?php echo $value["key"];?>"><?php echo $value["title"];?></option>
													<?php endforeach; ?>
												</select>
											</li>
											<li>
												<button type="button" class="btn xcn" id="hidden_select_all"><?php echo $lang["Select all"];?></button>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix">
						<div class="pull-left">
							<a href="<?php echo $this->createUrl("query/advanced");?>" class="btn btn-large xcn"><?php echo $lang["Return"];?></a>
						</div>
						<div class="pull-right">
							<button type="button" data-click="dosearch" class="btn btn-large btn-primary"><?php echo $lang["Search list"];?></button>
							<button type="button" data-click="exportexcel" class="btn btn-large"><?php echo $lang["Export excel list"];?></button>
							<button type="button" data-click="exporthtml" class="btn btn-large"><?php echo $lang["Export html list"];?></button>
						</div>
					</div>
					<input type="hidden" name="viewextfields" id="viewextfields" />
					<input type="hidden" name="sumfields" id="sumfields" />
					<input type="hidden" name="flowid" value="<?php echo $flow["flowid"];?>" />
					<input type="hidden" name="sid" value="<?php echo $edit ? $tpl["seqid"] : "";?>" />
					<input type="hidden" name="tplname" id="tplname" />
					<input type="hidden" name="op" id="op" />
					<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
				</form>
			</div>
			<!--高级查询条件选择区域 结束-->
		</div>
	</div>
</div>
<script src='<?php echo $assetUrl;?>/js/wfcommon.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/query_form.js?<?php echo VERHASH;?>'></script>
<script>
	/**
	 * 值->时间映射数组
	 * @type Array
	 */
	var dateMap = new Array();
	// 今日
	dateMap[1] = {begin: '<?php echo date("Y-m-d", strtotime("now"));?>', end: '<?php echo date("Y-m-d", strtotime("now"));?>'};
	// 昨日
	dateMap[2] = {begin: '<?php echo date("Y-m-d", strtotime("-1 day"));?>', end: '<?php echo date("Y-m-d", strtotime("now"));?>'};
	// 本周
	dateMap[3] = {begin: '<?php echo date("Y-m-d", strtotime("Sunday last week"));?>', end: '<?php echo date("Y-m-d", strtotime("saturday this week"));?>'};
	// 上周
	dateMap[4] = {begin: '<?php echo date("Y-m-d", strtotime("Sunday -2 week"));?>', end: '<?php echo date("Y-m-d", strtotime("saturday last week"));?>'};
	// 本月
	dateMap[5] = {begin: '<?php echo date("Y-m-d", strtotime("first day of this month"));?>', end: '<?php echo date("Y-m-d", strtotime("last day of this month"));?>'};
	// 上月
	dateMap[6] = {begin: '<?php echo date("Y-m-d", strtotime("first day of last month"));?>', end: '<?php echo date("Y-m-d", strtotime("last day of last month"));?>'};
	Ibos.app.setPageParam('dateMap', dateMap);
	$(function() {
		//日期范围选择初始化
		$("#s_date_start").datepicker({
			target: $("#s_date_end")
		});
		$("#e_date_start").datepicker({
			target: $("#e_date_end")
		});
		//初始化流程发起人
		$("#flow_originator").userSelect({
			data: Ibos.data.get('user'),
			maximumSelectionSize: "1"
		});
	});
</script>