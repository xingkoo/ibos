<div id="search_tpl_dialog" style="width: 760px;">
	<form action="javascript:;" class="form-horizontal form-narrow" id="advanced_search_form">
		<div>
			<ul class="nav nav-skid" id="search_tpl_nav">
				<li class="active">
					<a href="javascript:;" data-target="#primary_param"><?php echo $lang["Flow base info"];?></a>
				</li>
				<li>
					<a href="javascript:;" data-target="#form_condition"><?php echo $lang["Form data condition"];?></a>
				</li>
				<li>
					<a href="javascript:;" data-target="#statistics_options"><?php echo $lang["Statistics options"];?></a>
				</li>
			</ul>
		</div>
		<div class="fill">
			<!--  工作流基本属性  -->
			<div id="primary_param">
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Flow name"];?></label>
					<div class="controls controls-content fsl"><?php echo $flow["name"];?></div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Template name"];?></label>
					<div class="controls">
						<input type="text" value="" name="tplname" class="span4">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Query scope"];?></label>
					<div class="controls">
						<select name="flow_query_type" class="span4">
							<option value="all" selected><?php echo $lang["Query scope all"];?>option>
							<option value="1"><?php echo $lang["Query scope 1"];?></option>
							<option value="2"><?php echo $lang["Query scope 2"];?></option>
							<option value="3"><?php echo $lang["Query scope 3"];?></option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Flow initiator"];?></label>
					<div class="controls">
						<input type="text" value="" name="begin_user" id="flow_initiator">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Flow subject/num"];?></label>
					<div class="controls">
						<input type="text" name="run_name" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Flow status"];?></label>
					<div class="controls">
						<select name="flow_status" data-type="flow_status" class="span4">
							<option value="all" selected><?php echo $lang["All"];?></option>
							<option value="1"><?php echo $lang["Being performed"];?></option>
							<option value="0"><?php echo $lang["Has ended"];?></option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Flow begin date scope"];?></label>
					<div class="controls">
						<select data-type="date_change_begin" style="width: 130px;">
							<option selected><?php echo $lang["Quick choose"];?></option>
							<option value="1"><?php echo $lang["Today"];?></option>
							<option value="2"><?php echo $lang["Yesterday"];?></option>
							<option value="3"><?php echo $lang["This week"];?></option>
							<option value="4"><?php echo $lang["Last week"];?></option>
							<option value="5"><?php echo $lang["This month"];?></option>
							<option value="6"><?php echo $lang["Last month"];?></option>
						</select>
						<div class="dib" style="width: 220px;">
							<div class="datepicker" id="flow_begin_startdate">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" id="start_begin" name="time1" class="datepicker-input">
							</div>
						</div>
						<div class="dib" style="width: 220px;">
							<div class="datepicker" id="flow_begin_enddate">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" id="start_end" name="time2" class="datepicker-input">
							</div>
						</div>
					</div>
				</div>
				<div class="control-group" id="flow_end_date_scope" style="display: none;">
					<label class="control-label"><?php echo $lang["Flow ended date scope"];?></label>
					<div class="controls">
						<select data-type="date_change_end" style="width: 130px;">
							<option selected><?php echo $lang["Quick choose"];?></option>
							<option value="1"><?php echo $lang["Today"];?></option>
							<option value="2"><?php echo $lang["Yesterday"];?></option>
							<option value="3"><?php echo $lang["This week"];?></option>
							<option value="4"><?php echo $lang["Last week"];?></option>
							<option value="5"><?php echo $lang["This month"];?></option>
							<option value="6"><?php echo $lang["Last month"];?></option>
						</select>
						<div class="dib" style="width: 220px;">
							<div class="datepicker" id="flow_finish_startdate">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" id="end_begin" name="time3" class="datepicker-input">
							</div>
						</div>
						<div class="dib" style="width: 220px;">
							<div class="datepicker" id="flow_finish_enddate">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" id="end_end" name="time4" class="datepicker-input">
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Global attach name"];?></label>
					<div class="controls">
						<input type="text" name="attach_name" />
					</div>
				</div>
			</div>
			<!-- 表单数据条件 -->
			<div id="form_condition" style="display: none;">
				<div class="row mb">
					<div class="span3">
						<select id="condition_field">
							<option value=""><?php echo $lang["Please select"];?></option>
							<?php foreach ($title as $index => $value ) : ?>
								<option value="<?php echo $value["key"];?>"><?php echo $value["title"];?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="span2">
						<select name="" id="condition_operator">
							<option value="="><?php echo $lang["Equal"];?></option>
							<option value="<>"><?php echo $lang["Not equal to"];?></option>
							<option value=">"><?php echo $lang["Greater than"];?></option>
							<option value="<"><?php echo $lang["Less than"];?></option>
							<option value=">="><?php echo $lang["Greater than or equal to"];?></option>
							<option value="<="><?php echo $lang["Less than or equal to"];?></option>
							<option value="include"><?php echo $lang["Contains"];?></option>
							<option value="exclude"><?php echo $lang["Not contain"];?></option>
						</select>
					</div>
					<div class="span7">
						<input type="text" id="condition_value">
					</div>
				</div>
				<div class="mb">
					<div class="pull-right">
						<button class="btn btn-primary" data-condition="addCondition"><?php echo $lang["Add condition"];?></button>
						<button class="btn btn-fix" data-condition="removeCondition">
							<i class="glyphicon-trash"></i>
						</button>
					</div>
					<div class="btn-group" id="condition_logic" data-toggle="buttons-radio">
						<button class="btn btn-fix active" data-value="AND"><?php echo $lang["With"];?></button>
						<button class="btn btn-fix" data-value="OR"><?php echo $lang["Or"];?></button>
					</div>
					<input type="hidden" id="condition_logic_input" value="AND">
					<div class="btn-group">
						<button class="btn btn-fix" data-condition="addLeftParenthesis">(</button>
						<button class="btn btn-fix" data-condition="addRightParenthesis">)</button>
					</div>
				</div>
				<div>
					<select name="" id="condition_select" multiple></select>
					<input type="hidden" name="condformula" id="condition_result">
				</div>
			</div>
			<!-- 统计报表选项 -->
			<div id="statistics_options" style="display: none;">
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Group field"];?></label>
					<div class="controls">
						<select name="group_field" class="span3">
							<?php foreach (array_merge($deftitle, $title) as $index => $value ) : ?>
								<option value="<?php echo $value["key"];?>"><?php echo $value["title"];?></option>
							<?php endforeach; ?>
						</select>
						<select name="group_sort" id="" class="span2">
							<option value="asc"><?php echo $lang["Ascending"];?></option>
							<option value="desc"><?php echo $lang["Descending"];?></option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Statistical field"];?></label>
					<div class="controls controls-content">
						<div class="crs-slt-left">
							<div class="xwb mbs"><?php echo $lang["According to the following fields as table column"];?></div>
							<div class="mbs">
								<select name="" id="display_field" multiple>
									<?php foreach (array_merge($deftitle, $title) as $index => $value ) : ?>
										<option value="<?php echo $value["key"];?>" ><?php echo $value["title"];?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<button class="btn" id="cs_select_left_all"><?php echo $lang["Select all"];?></button>
						</div>
						<div class="crs-slt-center">
							<button class="btn btn-fix mb" id="cs_to_left"> 
								<i class="glyphicon-chevron-left"></i>
							</button>
							<button class="btn btn-fix" id="cs_to_right"> 
								<i class="glyphicon-chevron-right"></i>
							</button>
						</div>
						<div class="crs-slt-right">
							<div class="xwb mbs"><?php echo $lang["Dont show the following fields"];?></div>
							<div class="mbs">
								<select name="" id="hidden_field" multiple></select>
							</div>
							<button class="btn" id="cs_select_right_all"><?php echo $lang["Select all"];?></button>
						</div>
					</div>
				</div>
				<input type="hidden" name="viewextfields" id="viewextfields" />
				<input type="hidden" name="sumfields" id="sumfields" />
				<input type="hidden" name="flowid" value="<?php echo $this->flowid;?>" />
				<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
			</div>
		</div>
	</form>
</div>
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
</script>
<script src='<?php echo $this->getAssetUrl();?>/js/wfquerytpl.js?<?php echo VERHASH;?>'></script>