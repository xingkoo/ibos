// Add and Edit
$(function(){
	//表单改动离开页面提示
	Ibos.checkFormChange();

	//格式化当天时间
	var today = Ibos.date.format(new Date(),"yyyy-mm-dd");

	// 编辑器初始化
	var ue = UE.getEditor('editor', {
		initialFrameWidth: 700,
		autoHeightEnabled:true,
		toolbars: UEDITOR_CONFIG.mode.simple
	});

	// 上传事件初始化
	var attachUpload = Ibos.upload.attach({
		post_params: { module:'report' },
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});

	// 汇报对象
	var userData = Ibos.data.get("user");
	$("#rp_to").userSelect({
		data: userData,
		box: $("#rp_to_box"),
		type: "user"
	});

	// 时间选择
	$('.remind-time-btn', "#rp_plan").each(function(){
		var $elem = $(this);
		$elem.datepicker();
		$elem.datepicker('setStartDate', today).on("changeDate", function(evt) {
			var date = Ibos.date.format(evt.date, "yyyy-mm-dd");
			var $elemSiblings = $elem.siblings(".rp-remind-bar");
			$elem.css("display","none");
			$elemSiblings.css("display","inline-block");
			$elemSiblings.find('.remind-time').text(date);
			$elem.parents(".rp-plan-item").eq(0).find(".remind-value").val(date);
		});
	});

	$(".o-close-small", "#rp_plan").on("click",function(){
		var $elemParent = $(this).parent();
		$elemParent.css("display","none");
		$elemParent.siblings(".remind-time-btn").css("display","inline-block");
	});
	

	$("#date_summary_start").datepicker({ target: $("#date_summary_end") });
	$("#date_plan_start").datepicker({ target: $("#date_plan_end") });
	var calcDate = function(a, b, type, operator) {
		operator = operator || "add";
		var sm = new moment(a);
		switch(type) {
			// 周
			case "0":
				sm[operator](1, "weeks");
				break;
			// 月
			case "1":
				sm[operator](1, "months");
				break;
			// 季
			case "2":
				sm[operator](3, "months");
				break;
			// 半年
			case "3":
				sm[operator](6, "months");
				break;
			case "4":
				sm[operator](1, "years");
				break;
			default:
				sm[operator](b, "days");
		}
		return sm.toDate();
	}

	Ibos.evt.add({
		// 上一总结周期
		"prevSummaryDate": function(param){
			// 获取对应的datetimepicker实例
			var startDatePicker = $("#date_summary_start").data("datetimepicker"),
				endDatePicker = $("#date_summary_end").data("datetimepicker"),
				// 拿到当前时间
				startDate = startDatePicker.getDate(),
				endDate = endDatePicker.getDate(),
				// 计算结果时间
				resStartDate = calcDate(startDate, +param.intervals, param.type, "subtract"),
				resEndDate = calcDate(endDate, +param.intervals, param.type, "subtract")

			// 赋值并更改可选时间范围
			startDatePicker.setEndDate(resEndDate)
			startDatePicker.setDate(resStartDate)
			endDatePicker.setStartDate(resStartDate)
			endDatePicker.setDate(resEndDate)
		},
		// 下一总结周期
		"nextSummaryDate": function(param){
			var startDatePicker = $("#date_summary_start").data("datetimepicker"),
				endDatePicker = $("#date_summary_end").data("datetimepicker"),
				startDate = startDatePicker.getDate(),
				endDate = endDatePicker.getDate(),
				resStartDate = calcDate(startDate, +param.intervals, param.type, "add"),
				resEndDate = calcDate(endDate, +param.intervals, param.type, "add");

			endDatePicker.setStartDate(resStartDate)
			endDatePicker.setDate(resEndDate)
			startDatePicker.setEndDate(resEndDate)
			startDatePicker.setDate(resStartDate)
		},
		// 上一计划周期
		"prevPlanDate": function(param){
			var startDatePicker = $("#date_plan_start").data("datetimepicker"),
				endDatePicker = $("#date_plan_end").data("datetimepicker"),
				startDate = startDatePicker.getDate(),
				endDate = endDatePicker.getDate(),
				resStartDate = calcDate(startDate, +param.intervals, param.type, "subtract"),
				resEndDate = calcDate(endDate, +param.intervals, param.type, "subtract");

			startDatePicker.setEndDate(resEndDate)
			startDatePicker.setDate(resStartDate)
			endDatePicker.setStartDate(resStartDate)
			endDatePicker.setDate(resEndDate)
		},
		// 下一计划周期
		"nextPlanDate": function(param){
			var startDatePicker = $("#date_plan_start").data("datetimepicker"),
				endDatePicker = $("#date_plan_end").data("datetimepicker"),
				startDate = startDatePicker.getDate(),
				endDate = endDatePicker.getDate(),
				resStartDate = calcDate(startDate, +param.intervals, param.type, "add"),
				resEndDate = calcDate(endDate, +param.intervals, param.type, "add");

			endDatePicker.setStartDate(resStartDate)
			endDatePicker.setDate(resEndDate)
			startDatePicker.setEndDate(resEndDate)
			startDatePicker.setDate(resStartDate)
		}
	});
	
	// 防重复提交
	$("#report_form").on("submit", function(){
		if($.data(this, "submit")) {
			return false;
		}
		$.data(this, "submit", true);
	})
});