<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<!-- Loop start -->
<div class="mc clearfix">
    <!-- Sidebar start -->
	<?php echo $this->getSidebar(); ?>
	<!-- Sidebar end -->
    <!-- Loop right start -->
    <div class="mcr">
        <div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<button class="btn btn-primary pull-left" data-click="addLoop" ><?php echo $lang['New']; ?></button>
					<button class="btn pull-left" data-click="deleteLoops" ><?php echo $lang['Delete']; ?></button>
				</div>
			</div>
			<div class="page-list-mainer">
				<table class="table table-hover">
					<thead>
						<tr>
							<th width="20">
								<label class="checkbox">
									<input type="checkbox" data-name="loop[]">
								</label>
							</th>
							<th><?php echo $lang['Subject']; ?></th>
							<th width="160"><?php echo $lang['Last modified date']; ?></th>
							<th width="240"><?php echo $lang['Cycle']; ?></th>
							<th width="70"><?php echo $lang['Operation']; ?></th>
						</tr>
					</thead>
					<tbody id="loop_tbody"></tbody>
				</table>
				<div class="no-data-tip" style="display:none" id="no_data_tip"></div>
			</div>
			<div class="page-list-footer">
				<div class="pull-right">
					<?php $this->widget( 'IWPage', array( 'pages' => $pages ) ); ?>
				</div>
			</div>
		</div>
    </div>
	<!-- Loop right end -->
</div>
<!-- Loop end -->

<!-- 新增周期性事务窗口 -->

<div id="loop_dialog" style="width: 520px; display: none;">
	<form method="post" name="" id="add_calendar_form">
		<div class="mb">
			<textarea id="loop_subject" style="height:100px;" placeholder="<?php echo $lang['No subject']; ?>"></textarea>
		</div>
		<div>
			<div class="row mb">
				<div class="span5">
					<div class="input-group datepicker" style="" id="loop_start_time_datepicker" style="width: 180px;">
						<span class="input-group-addon"><?php echo $lang['From']; ?></span>
						<a href="javasrcipt:;" class="datepicker-btn"></a>
						<input type="text" id="loop_start_time" class="datepicker-input pull-left">
					</div>
				</div>
				<div class="span5">
					<div class="input-group datepicker" id="loop_end_time_datepicker">
						<span class="input-group-addon"><?php echo $lang['To']; ?></span>
						<a href="javasrcipt:;" class="datepicker-btn"></a>
						<input type="text" id="loop_end_time" class="datepicker-input pull-left">
					</div>
				</div>
				<div class="span2">
					<div class="color-picker-btn pull-right" id="color_picker_btn"></div>
					<input type="hidden" id="loop_theme" value="0">
				</div>
			</div>
		</div>
		<div class="form-horizontal form-compact">
			<div class="control-group">
				<label for="loop_type" class="control-label"><?php echo $lang['Repeat']; ?></label>
				<div class="controls">
					<select name="" id="loop_type" class="span6">
						<option value="week"><?php echo $lang['Weekly']; ?></option>
						<option value="month"><?php echo $lang['Per month']; ?></option>
						<option value="year"><?php echo $lang['Per year']; ?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Repetition time']; ?></label>
				<div class="controls">
					<!-- 周循环 -->
					<div id="repeat_per_week">
						<label class="checkbox checkbox-inline">一<input type="checkbox" name="weekbox[]" value="1"></label>
						<label class="checkbox checkbox-inline">二<input type="checkbox" name="weekbox[]" value="2"></label>
						<label class="checkbox checkbox-inline">三<input type="checkbox" name="weekbox[]" value="3"></label>
						<label class="checkbox checkbox-inline">四<input type="checkbox" name="weekbox[]" value="4"></label>
						<label class="checkbox checkbox-inline">五<input type="checkbox" name="weekbox[]" value="5"></label>
						<label class="checkbox checkbox-inline">六<input type="checkbox" name="weekbox[]" value="6"></label>
						<label class="checkbox checkbox-inline">日<input type="checkbox" name="weekbox[]" value="7"></label>
					</div>
					<!-- 月循环 -->
					<div id="repeat_per_month" style="display:none;">
						<select id="loop_month_day" class="span6">
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
							<option value="16">16</option>
							<option value="17">17</option>
							<option value="18">18</option>
							<option value="19">19</option>
							<option value="20">20</option>
							<option value="21">21</option>
							<option value="22">22</option>
							<option value="23">23</option>
							<option value="24">24</option>
							<option value="25">25</option>
							<option value="26">26</option>
							<option value="27">27</option>
							<option value="28">28</option>
							<option value="29">29</option>
							<option value="30">30</option>
							<option value="31">31</option>
						</select>
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
					<!-- 年循环 -->
					<div id="repeat_per_year" style="display:none;">
						<div class="datepicker span6" id="loop_year_day_picker">
							<input type="text" value="" id="loop_year_day" class="datepicker-input">
							<a href="javascript:;" class="datepicker-btn"></a>
						</div>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Start date']; ?></label>
				<div class="controls">
					<div class="datepicker span6" id="loop_start_day_datepicker">
						<input type="text" value="<?php echo date( 'Y-m-d', time() ); ?>" id="loop_start_day" class="datepicker-input">
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['End date']; ?></label>
				<div class="controls">
					<div class="datepicker span6" id="loop_end_day_datepicker">
						<input type="text" value="" id="loop_end_day" class="datepicker-input">
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
					<?php echo $lang['Empty does not end']; ?>
				</div>
			</div>
		</div>
	</form>
</div>

<!-- 插入联系信息模板 -->
<script type="text/ibos-template" id="loop_template">
	<tr id="loop_row_<%=calendarid%>">
		<td>
			<label class="checkbox">
				<input type="checkbox" name="loop[]" value="<%=calendarid%>">
			</label>
		</td>
		<td>
			<span class="cal-theme-square" style="background-color: <%=bgcolor%>"></span>
			<a href="javascript:"><%=subject%></a>
		</td>
		<td>
			<%=uptime%>
		</td>
		<td>
			<%=cycle%>
		</td>
		<td>
			<a href="javascript:" data-click="editLoop" data-id="<%=calendarid%>" title="<?php echo $lang['Edit']; ?>" class="cbtn o-edit"></a>
			<a href="javascript:" data-click="deleteOneLoop" data-id="<%=calendarid%>"  title="<?php echo $lang['Delete']; ?>" class="cbtn o-trash"></a>
		</td>
	</tr>
</script>

<script src='<?php echo STATICURL ?>/js/lib/moment.min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<!-- <script src='<?php echo $assetUrl; ?>/js/cal_loop_index.js?<?php echo VERHASH; ?>'></script> -->
<!-- <script src='<?php echo $assetUrl; ?>/js/cal_loop_index.js?<?php echo VERHASH; ?>'></script> -->
<script>
	$(function(){
		var loopData = $.parseJSON('<?php echo json_encode($loopList) ?>');
		if(!loopData.length){
			$("#no_data_tip").show();
		}
		var $loopBody = $("#loop_tbody");
		var loopTable = new Ibos.List($loopBody, "loop_template", {idField: "calendarid"});

		$loopBody.on("list.add list.update", function(evt, data){
			data.item.find(".checkbox input[type='checkbox']").label();
		});
	
		var defaultColors = ["#3497DB", "#A6C82F", "#F4C73B", "#EE8C0C", "#E76F6F", "#AD85CC", "#98B2D1", "#82939E"],
			getColor = function(value){
				value = parseInt(value, 10);
				if(defaultColors[value]) {
					return defaultColors[value];
				} else {
					return defaultColors[0];
				}
			},
			toTplData = function(data){
				return {
					calendarid: data.calendarid,
					bgcolor: getColor(data.category),
					subject: data.subject,
					uptime: data.uptime,
					cycle: data.cycle
				}
			}

		for(var i = 0; i < loopData.length; i++) {
			loopTable.addItem(toTplData(loopData[i]));
		}


		var loop = {
			//改变循环类型(周/月/年)
			_setType: function(type){
				$("#repeat_per_" + type).show().siblings().hide();
				if(type === "year") {
					$("#loop_year_day_picker").datepicker({
						format: "MM-dd"
					})
				}
			},

			// 获取新建编辑对话框中的数据 
			_getDialogData: function(){
				return {
					subject: $("#loop_subject").val(),
					starttime: $("#loop_start_time").val(),
					// 复数？？
					endtimes: $("#loop_end_time").val(),
					category: $('#loop_theme').val(),

					// setday: $('#c_setday').val(),
					reply: true,
					recurringbegin: $('#loop_start_day').val(),
					recurringend: $('#loop_end_day').val(),
					recurringtype: $('#loop_type').val(),
					weekbox: U.getCheckedValue("weekbox[]"),
					month: $('#loop_month_day').val(),
					year: $('#loop_year_day').val()
				}
			},
			// 设置新建编辑对话框中的数据 
			_setDialogData: function(data){
				var that = this,
					$form = $("#add_calendar_form"),
					vals,
					date = new Date,
					startTime = data.starttime ? 
						moment(data.starttime, "HH:mm").toDate() : 
						moment().toDate(),

					endTime = data.endtime ? 
						moment(data.endtime, "HH:mm").toDate() : 
						moment().add(30, "m").toDate(),

					startDate = data.recurringbegin ? 
						moment(data.recurringbegin).toDate() : 
						moment().toDate(),

					endDate = data.recurringend ? 
						moment(data.recurringend).toDate() : 
						null;

				!U.isUnd(data.subject) && $("#loop_subject").val(data.subject);

				// 初始化开始时间及结束时间， 默认为当前时间至30分钟后
				$("#loop_start_time_datepicker").datetimepicker("setLocalDate", startTime);
				$("#loop_end_time_datepicker").datetimepicker("setLocalDate", endTime );

				!U.isUnd(data.category) && $('#loop_theme').val(data.category).trigger("change");

				$("#loop_start_day_datepicker").datetimepicker("setLocalDate", startDate);
				if(endDate) {
					$('#loop_end_day_datepicker').datetimepicker("setLocalDate", endDate);
				}


				!U.isUnd(data.recurringtype) && $("#loop_type").val(data.recurringtype);
				// 还原循环类型
				$('#loop_type').off("change").on("change", function() {
					that._setType(this.value);
				}).trigger("change");

				// 还原复选框选中状态
				if(data.recurringtype === "week"){
					vals = data.recurringtime.split(",")
					$form.find("[name='weekbox[]']").each(function(){
						$(this).prop("checked", $.inArray(this.value, vals) !== -1).label("refresh");
					})			
				} else if(data.recurringtype === "month") {
					data.recurringtime && $('#loop_month_day').val(data.recurringtime);
				} else if(data.recurringtype === "year") {
					data.recurringtime && $("#loop_year_day").val(data.recurringtime);
				}
			},
			// 检验提交的数据
			// @Todo;
			_validateData: function(data){
				return true;
			},
			_showDialog: function(options){ // title, ok, init, cancel
				var that = this;
				options = options || {};
				Ui.dialog({
					id: 'd_loop_dialog',
					title: options.title,
					content: Dom.byId('loop_dialog'),
					ok: function(){
						var loopData = that._getDialogData();
						if(that._validateData(loopData)) {
							options.ok && options.ok(loopData);
						}
					},
					init: function(){
						// 开始时间 结束时间组 这里由于做了容错，所以不对时间范围做限制
						$("#loop_start_time_datepicker, #loop_end_time_datepicker").datepicker({
							pickTime: true,
							pickDate: false,
							pickSeconds: false,
							format: "hh:ii"
						});
						// 开始日期 结束日期组
						$("#loop_start_day_datepicker").datepicker({
							target: "loop_end_day_datepicker"
						});

						// 颜色选择器
						var $pickerBtn = $("#color_picker_btn"),
							$pickerInput = $("#loop_theme"),
							theme = $pickerInput.val();

						var setColor = function(val){
							
							color = getColor(val);
							$pickerBtn.css("background-color", color);
						};

						$pickerInput.off("change").on("change", function(){
							setColor(this.value);
						}).trigger("change");

						$pickerBtn.colorPicker({ 
							data: defaultColors,
							onPick: function(hex){
								$pickerInput.val(hex ? $.inArray(hex, defaultColors) : -1).trigger("change");
							}
						});

						options.init && options.init();
					},
					cancel: options.cancel || true
				});
			},
			//新增周期性事务
			add: function(){
				var that = this;
				this._showDialog({
					title: U.lang("CAL.PREIODIC_AFFAIRS"),
					init: function(){
						that._setDialogData({
							subject: "",
							starttime: "",
							endtime: "",
							category: "-1",
							recurringend: "",
							recurringtype: "week",
							recurringtime: ""
						});
					},
					ok: function(data){
						$.post(Ibos.app.url('calendar/loop/add'), data, function(res){
							if(res.isSuccess) {
								loopTable.addItem(toTplData(res), true);
								Ui.tip(U.lang('OPERATION_SUCCESS'));
							} else {
								Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
							}
						}, "json");
						$("#no_data_tip").hide();
					}
				});
			},
			//编辑周期性事务
			edit: function(id) {
				var that = this,
					dataEditUrl = Ibos.app.url('calendar/loop/edit', { op: 'geteditdata'}),
					saveUrl = Ibos.app.url('calendar/loop/edit');

				Ui.dialog({
					title: U.lang("CAL.PREIODIC_AFFAIRS"),
					init: function(){
						var api = this;
						$.get(dataEditUrl, { editCalendarid: id }, function(res){
							api.close();
							that._showDialog({
								title: U.lang("CAL.PREIODIC_AFFAIRS"),
								init: function(){
									that._setDialogData(res);
								},
								ok: function(data){
									$.post(saveUrl, $.extend({ editCalendarid: id }, data), function(res){
										if(res.isSuccess){
											res.calendarid = id
											loopTable.updateItem(toTplData(res));
											Ui.tip(U.lang('OPERATION_SUCCESS'));
										} else {
											Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
										}
									});
								}
							});
						});
					}
				});
			},
			//删除周期性事务
			remove: function(id) {
				var url = Ibos.app.url('calendar/loop/del');
				if(id){
					Ui.confirm(U.lang("CAL.CONFIRM_TO_DELETE_THIS_SERIES"), function(){
						$.post(url, { delCalendarid: id }, function(res){
							var ids;
							if(res.isSuccess) {
								ids = id.split(",");
								$.each(ids, function(index, oneId) {
									loopTable.removeItem(oneId);
								});
								Ui.tip(U.lang('OPERATION_SUCCESS'));
							} else {
								Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
							}
						}, "json")
					})
				}
			}
		}


		Ibos.events.add({
			addLoop: function(){ loop.add() },
			editLoop: function(param, $elem){
				var id = $elem.attr('data-id');
				loop.edit(id);
			},
			deleteOneLoop: function(param, $elem){
				loop.remove($elem.attr('data-id'))
			},
			deleteLoops: function(){
				var ids = U.getCheckedValue("loop[]");
				if(!ids) {
					Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
					return false;
				}
				loop.remove(ids)
			}
		});
	})


</script>		