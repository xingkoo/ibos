(function() {
	// 日期选择
	var $beginStartDate = $("#flow_begin_startdate"),
			$beginEndDate = $("#flow_begin_enddate"),
			$finishStartDate = $("#flow_finish_startdate"),
			$finishEndDate = $("#flow_finish_enddate");

	$beginStartDate.datepicker({target: $beginEndDate});
	$finishStartDate.datepicker({target: $finishEndDate});

	var tab = new P.commonTab($("#search_tpl_nav"));
	var cs = crossSelect($("#display_field"), $("#hidden_field"));
	$("#cs_to_right").click(function() {
		cs.toRight();
	});
	$("#cs_to_left").click(function() {
		cs.toLeft();
	});
	$("#cs_select_right_all").click(function() {
		cs.selectRightAll();
	});
	$("#cs_select_left_all").click(function() {
		cs.selectLeftAll();
	});

	// 流程发起人
	$("#flow_initiator").userSelect({
		data: Ibos.data.get('user'),
		type: "user",
		maximumSelectionSize: "1",
		box: $("<div></div>").appendTo(document.body)
	});
	// 表单数据条件选择器
	var $conditionLogic = $("#condition_logic"), $conditionLogicInput = $("#condition_logic_input");
	$conditionLogicInput.val($conditionLogic.find('button.active').attr('data-value'));
	var logic = $conditionLogicInput.val();
	$conditionLogic.on("click", "button", function() {
		var value = $.attr(this, "data-value");
		$conditionLogicInput.val(value);
		logic = value;
	});
	var fd = new FormCondition(document.getElementById("condition_select"));
	$("#form_condition").on("click", "[data-condition]", function() {
		var type = $.attr(this, "data-condition");
		if (type === "addCondition") {
			var field = $("#condition_field").find('option:selected').text(), operator = $("#condition_operator").val(), value = $("#condition_value").val();
			fd.addCondition(field, operator, value, logic);
		} else {
			fd[type](logic);
		}
		$("#condition_result").val(fd.getConditions());
	});
	// ---------------

	/**
	 * 日期快捷选择
	 * @param {integer} val 下拉菜单的值
	 * @param {jQuery object} dateBegin 开始时间字段
	 * @param {jQuery object} dateEnd 结束时间字段
	 * @returns {void}
	 */
	function dateChange(val, dateBegin, dateEnd) {
		var dateMap = Ibos.app.g('dateMap');
		var date = dateMap[val];
		dateBegin.val(date.begin);
		dateEnd.val(date.end);
	}
	// 下拉菜单事件
	$('#search_tpl_dialog').find('select').on('change', function() {
		var type = $(this).data('type');
		if (type === 'date_change_begin') {
			var selectorBegin = $('#start_begin'), selectorEnd = $('#start_end');
			dateChange(this.value, selectorBegin, selectorEnd);
		} else if (type === 'date_change_end') {
			var selectorBegin = $('#end_begin'), selectorEnd = $('#end_end');
			dateChange(this.value, selectorBegin, selectorEnd);
		} else if (type === 'flow_status') {
			if (this.value == 0) {
				$('#flow_end_date_scope').show();
			} else {
				$('#flow_end_date_scope').hide();
			}
		}
	});
})();