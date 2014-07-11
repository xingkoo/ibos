// JavaScript Document
// 表单数据条件
var FormCondition = (function() {
	var isNotNullString = function(str) {
		return $.trim(str) !== "";
	};

	function FormCondition(node) {
		// enforces new
		if (!(this instanceof FormCondition)) {
			return new FormCondition(node);
		}
		this.node = node;
	}

	FormCondition.prototype = {
		constructor: FormCondition,
		_valid: function(text) {
			//检查公式
			if (text.indexOf("(") >= 0) {
				var nNum1 = text.split("(").length;
				var nNum2 = text.split(")").length;
				if (nNum1 !== nNum2) {
					return false;
				}
			}
			return true;
		},
		_getCurrentIndex: function() {
			var current, opts = this.node.options;
			for (var i = 0; i < opts.length; i++) {
				if (opts[i].selected) {
					current = this.node.selectedIndex;
					break;
				}
			}
			if (typeof current === "undefined") {
				current = opts.length - 1;
			}
			return current;
		},
		addLeftParenthesis: function(logic) {
			var opts = this.node.options,
					len = opts.length,
					currentIndex,
					ptOpt,
					text,value;

			if (len > 0) {
				//检查是否有条件
				currentIndex = this._getCurrentIndex();
			} else {
				//有条件才能添加左括号表达式
				Ui.tip(L.WF.CONDITION_NEED, "warning");
				return;
			}

			text = opts[currentIndex].text;
			value = opts[currentIndex].value;

			//无法编辑已经"发生关系"的条件 O(∩_∩)O~
			if (($.trim(text).substr(-3, 3) === 'AND') || ($.trim(text).substr(-2, 2) === 'OR')) {
				Ui.tip(L.WF.CONDITION_CANNOT_EDIT, "warning");
				return;
			}
			if (text.indexOf('(') >= 0) { //检查括号匹配
				if (!this._valid(text)) {
					Ui.tip(L.WF.CONDITION_FORMAT_ERROR, "warning");
					return;
				} else {
					text += " " + logic;
					value += " " + logic;
				}
			} else {
				text = text + " " + logic;
				value = value + " " + logic;
			}

			this.node.options[currentIndex].text = text;
			this.node.options[currentIndex].value = value;

			ptOpt = document.createElement('option');
			ptOpt.text = "( ";
			ptOpt.value = "( ";
			this.node.appendChild(ptOpt);
		},
		/**
		 * 增加右括号表达式
		 */
		addRightParenthesis: function(logic) {
			var opts = this.node.options,
					len = opts.length,
					currentIndex,
					text,value;

			if (len > 0) {
				currentIndex = this._getCurrentIndex();
			} else {
				Ui.tip(L.WF.CONDITION_NEED, "warning");
				return;
			}

			text = this.node.options[currentIndex].text;
			value = this.node.options[currentIndex].value;
			if (($.trim(text).substr(-3, 3) === 'AND') || ($.trim(text).substr(-2, 2) === 'OR')) {
				Ui.tip(L.WF.CONDITION_CANNOT_EDIT, "warning");
				return;
			}
			if (($.trim(text).substr(-1, 1) === '(')) {
				Ui.tip(L.WF.CONDITION_NEED, "warning");
				return;
			}

			if (!this._valid(text)) {
				text = text + ")";
				value = value + ")";
			}
			opts[currentIndex].text = text;
			opts[currentIndex].value = value;
		},
		addCondition: function(field, operator, value, logic, textValue) {
			var toAdd = true,
					len = this.node.length,
					inBracket = false,
					text,
					newText,
					newValue,
					optionNode;
			logic = logic || "AND";
			if (isNotNullString(field) && isNotNullString(operator) && isNotNullString(value)) {
				if (len > 0) {
					text = this.node.options[len - 1].text;

					if (!this._valid(text)) {
						toAdd = false;
					}
				}
				if (value.indexOf("'") >= 0) {
					Ui.tip(L.WF.CONDITION_INVAILD, "warning");
					return;
				}

				newText = "'" + field + "' " + operator + " '" + value + "'";
				newValue = textValue + ' ' + operator + " '" + value + "'";

				for (var i = 0; i < len; i++) {
					if (this.node.options[i].text.indexOf(newText) >= 0) {
						Ui.tip(L.WF.CONDITION_REPEAT, "warning");
						return;
					}
				}

				if (toAdd) {
					optionNode = document.createElement('option');
					optionNode.text = newText;
					optionNode.value = newValue;
					this.node.appendChild(optionNode);

					if (len > 0) {
						this.node.options[len - 1].text += "  " + logic;
						this.node.options[len - 1].value += "  " + logic;
					}
				} else {
					inBracket = $.trim(this.node.options[len - 1].text).substr(-1, 1) === "(";
					this.node.options[len - 1].text += (inBracket ? newText : " " + logic + " " + newText);
					this.node.options[len - 1].value += (inBracket ? newValue : " " + logic + " " + newValue);
				}
			} else {
				Ui.tip(L.WF.CONDITION_INCOMPLETE, "warning");
				return;
			}
		},
		removeCondition: function() {
			var node = this.node;
			for (var i = 0; i < node.options.length; i++) {
				if (node.options[i].selected) {
					if (typeof node.options[i + 1] === "undefined" && typeof node.options[i - 1] !== "undefined") {
						node.options[i - 1].text = node.options[i - 1].text.replace(/(AND|OR)$/, '');
					}
					node.removeChild(node.options[i]);
					i--;
				}
			}
		},
		clearCondition: function() {
			this.node.innerHTML = "";
		},
		getConditions: function() {
			var node = this.node, result = "";
			for (var i = 0; i < node.options.length; i++) {
				result += node.options[i].value + "\n";
			}
			return result;
		}
	};
	return FormCondition;
}());

//工作查询
var QueryForm = {
	/**
	 * 全选
	 * @method selectAll
	 * @param  {String} elem 被选择的select元素
	 */
	selectOptions: function(elem) {
		$(elem).attr("selected", "selected");
	},
	/**
	 * 移动添加
	 * @method selectAll
	 * @param  {String} startElem 被选元素
	 * @param  {String} targetElem 添加到的目标元素
	 */
	appendTo: function(startElem, targetElem) {
		var moveElems = $(startElem).remove();
		$(targetElem).append(moveElems);
	},
	/**
	 * 模块的显示与隐藏切换
	 * @method selectAll
	 * @param  {String} elem 点击切换的元素
	 * @param  {String} targetElem 被操作的对象模块
	 */
	displayToggle: function(elem, targetElem) {
		$(elem).toggle(function() {
			$(targetElem).slideDown("fast");
			$(this).children("span").text("收起信息");
			$(this).addClass("active");
		}, function() {
			$(targetElem).slideUp("fast");
			$(this).children("span").text("展开信息");
			$(this).removeClass("active");
		});
	},
	/**
	 * 日期快捷选择
	 * @param {integer} val 下拉菜单的值
	 * @param {jQuery object} dateBegin 开始时间字段
	 * @param {jQuery object} dateEnd 结束时间字段
	 * @returns {void}
	 */
	dateChange: function(val, $dateBegin, $dateEnd) {
		var dateMap = Ibos.app.g('dateMap');
		var date = dateMap[val];
		$dateBegin.datepicker("setDate", new Date(date.begin));
		$dateEnd.datepicker("setDate", new Date(date.end));
	}
}

$(function() {
	// 统计字段的显示字段全选
	$("#show_select_all").on("click", function() {
		QueryForm.selectOptions("#show_select option");
	});

	// 统计字段的隐藏字段全选
	$("#hidden_select_all").on("click", function() {
		QueryForm.selectOptions("#hidden_select option");
	});

	// 分组字段更改，把隐藏对应的分组字段改为显示
	$("#group_by_name").on("change", function() {
		var groupField = $("#group_by_name").val();
		QueryForm.appendTo("#hidden_select option[value='" + groupField + "']", "#show_select");
	});

	//统计字段,将显示字段移动到隐藏字段
	$("#turn_hidden").on("click", function() {
		var groupField = $("#group_by_name").val(),
				selectedItem = $("#show_select option:selected");
		if (selectedItem.length == 1 && $("#show_select option[value='" + groupField + "']").is(":selected")) {
			Ui.tip(U.lang("WF.GROUPING_FIELD_CAN_NOT_HIDE"), 'warning');
		}
		$("#show_select option[value='" + groupField + "']").attr('selected', false);

		QueryForm.appendTo("#show_select option:selected", "#hidden_select");
	});

	// 统计字段,将隐s藏字段移动到显示字段
	$("#turn_show").on("click", function() {
		QueryForm.appendTo("#hidden_select option:selected", "#show_select");
	});

	// 表单数据条件模块的显示与隐藏的切换
	QueryForm.displayToggle("#condition_show_more", "#step_condition");

	// 导出统计表模块的显示与隐藏的切换	
	QueryForm.displayToggle("#options_show_more", "#options_content");

	/**
	 * 头部保存模板功能
	 */
	$("#save_prepare_project").on("click", function() {
		Ui.prompt(U.lang('WF.PLEASE_ENTER_THE_PACKAGE_NAME'), function(val) {
			if ($.trim(val) == '') {
				return false;
			}
			$('#tplname').val(val);
			$('#query_form').attr('action', Ibos.app.url('workflow/query/add')).submit();
		}, U.lang('WF.NEW_PLAN_EXAMPLE'));
	});
	/**
	 * 初始化流程开始时间范围显示
	 */
	$("#start_time_range").change(function() {
		var val = $(this).val();
		QueryForm.dateChange(val, $('#s_date_start'), $("#s_date_end"));
	});
	/**
	 * 初始化流程结束时间范围显示
	 */
	$("#end_time_range").change(function() {
		var val = $(this).val();
		QueryForm.dateChange(val, $("#e_date_start"), $("#e_date_end"));
	});
	/**
	 * 查询模板选择后跳转
	 */
	$('#select_prepare_project').change(function() {
		if (this.value !== '') {
			window.location.href = Ibos.app.url('workflow/query/search', {id: this.value});
		}
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
			var field = $("#condition_field").find('option:selected').text(), operator = $("#condition_operator").val(), value = $("#condition_value").val(), textValue = $('#condition_field').val();
			fd.addCondition(field, operator, value, logic, textValue);
		} else {
			fd[type](logic);
		}
		$("#condition_result").val(fd.getConditions());
	});

	//流程日期范围选择后清空
	$("#strat_time_clear").on("click", function() {
		$("#s_date_start").data("datetimepicker").setValue(null);
		$("#s_date_end").data("datetimepicker").setValue(null);
	});
	$("#end_time_clear").on("click", function() {
		$("#e_date_start").data("datetimepicker").setValue(null);
		$("#e_date_end").data("datetimepicker").setValue(null);
	});
	/**
	 * 流程状态切换结束日期范围可见性，流程状态为已结束才可见
	 */
	$('#flow_state').on('change', function() {
		$('#end_time_box').toggle(this.value == 0);
	});
	/**
	 * 表单提交前处理
	 */
	$('#query_form').on('submit', function() {
		var $conditionResult = $('#condition_result'),
				$conditionSelect = $('#condition_select'),
				viewFields = '', sumFields = '';
		// step1:处理可见显示字段
		$('#viewextfields').val('');
		$('#sumfields').val('');
		$.each($('#show_select').find('option'), function(i, n) {
			var val = n.value;
			viewFields += val + ',';
			if (n.selected) {
				sumFields += val + ',';
			}
		});
		$('#viewextfields').val(viewFields);
		$('#sumfields').val(sumFields);
		// step2:处理表单字段条件
		var fd = new FormCondition(document.getElementById("condition_select"));
		if ($conditionSelect.length > 0) {
			var checkExp = true;
			$.each($conditionSelect.find('option'), function(i, n) {
				if (!fd._valid(n.text)) {
					checkExp = false;
					return false;
				}
			});
			if (!checkExp) {
				Ui.tip(U.lang('WF.CONDITION_FORMAT_ERROR'), 'danger');
				return false;
			}
		} else {
			$conditionResult.val('');
		}
		return true;
	});

	Ibos.events.add({
		/**
		 * 
		 * @param {type} $elem
		 * @param {type} param
		 * @returns {undefined}
		 */
		'dosearch': function($elem, param) {
			$('#query_form').attr('action', Ibos.app.url('workflow/query/searchResult')).submit();
		},
		/**
		 * 
		 * @param {type} $elem
		 * @param {type} param
		 * @returns {undefined}
		 */
		'exportexcel': function($elem, param) {
			$('#op').val('excel');
			$('#query_form').attr('action', Ibos.app.url('workflow/query/export')).submit();
		},
		/**
		 * 
		 * @param {type} $elem
		 * @param {type} param
		 * @returns {undefined}
		 */
		'exporthtml': function($elem, param) {
			$('#op').val('html');
			$('#query_form').attr('action', Ibos.app.url('workflow/query/export')).submit();
		}
	});

});