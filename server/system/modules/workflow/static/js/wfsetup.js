/**
 * wfsetup.js
 * 工作流模块通用JS
 * IBOS
 * @author		inaki
 * @version		$Id$
 */


// 菜单滑出到界面的功能
// menu: 菜单
// container: 宿主
// dir: 方向
// speed: 速度
// distance 距离，默认为菜单自身大小
// 
// Common

function slideWindow($menu, $container, dir, options) {
	// init
	options = $.extend({
		justify: true,
		speed: 200
	}, options);
	if (!/top|bottom|left|right/.test(dir)) {
		dir = "right";
	}

	// 使$menu 相对于 $container 绝对定位
	if (!$container || !$container.length) {
		$container = $menu.parent();
	} else if ($menu.offsetParent() !== $container) {
		$container.append($menu);
	}
	// 让$container成为定位父节点
	if (!/fixed|absolute|relative/.test($container.css("position"))) {
		$container.css("position", "relative");
	}
	$menu.css("position", "absolute");

	// 自适应容器的高度或宽度
	var _resetScale = function() {
		if (dir === "top" || dir === "bottom") {
			$menu.css("left", 0).outerWidth($container.outerWidth());
		} else {
			$menu.css("top", 0).outerHeight($container.outerHeight());
		}
	};

	var _style = {};
	var menuHeight = $menu.outerHeight(),
			menuWidth = $menu.outerWidth();
	// 初始定位
	switch (dir) {
		case "top":
			_style.top = -menuHeight;
			break;
		case "bottom":
			_style.bottom = -menuHeight;
			break;
		case "left":
			_style.left = -menuWidth;
			break;
		default:
			_style.right = -menuWidth;
			break;
	}

	$menu.css(_style);
	if (options.justify) {
		_resetScale();
	}

	var _isIn = false;
	var _slideIn = function(callback) {
		var _d = {};
		// if(!_isIn){
		$menu.trigger("beforeslidein");
		if (options.justify) {
			_resetScale();
		}
		_d[dir] = options.distance || 0;
		$menu.show().animate(_d, options.speed, function() {
			_isIn = true;
			$menu.trigger("slidein");
			callback && callback();
		});
		// }
	};
	var _slideOut = function(callback) {
		var _d = {};
		// if(_isIn){
		$menu.trigger("beforeslideout");
		if (dir === "top" || dir === "bottom") {
			// 由于在显示时，可能被改变高度，所以要重新计算
			_d[dir] = -$menu.outerHeight();
		} else {
			_d[dir] = -$menu.outerWidth();
		}
		$menu.animate(_d, options.speed, function() {
			_isIn = false;
			$menu.trigger("slideout");
			callback && callback();
		});
		// }
	};
	return {
		// isIn: _isIn,
		slideIn: _slideIn,
		slideOut: _slideOut
	};
}

/**
 * [MuitiSelect description]
 * @param {HTMLSelectElement} select 要初始化select节点
 */

var CusSelect = function(select) {
	var that = this;
	this.$select = $(select);
	this.$select.on("dblclick", "option", function() {
		that.removeOne($(this));
	});
};

CusSelect.prototype = {
	constructor: CusSelect,
	/**
	 * 添加一项
	 * @method addOne
	 * @param {jQuery|Object} node option节点或用于生成option节点的数据: 如{ value: '', text: '' }
	 * @return {jQuery}       添加的option节点
	 */
	addOne: function(node) {
		var $node;
		if (node.value && node.text) {
			$node = $('<option value="' + node.value + '">' + node.text + '</option>');
		} else {
			$node = node;
		}

		if ($node && $node.length) {
			$node && this.$select.append($node);
			this.$select.trigger('selectadd', $node);
			return $node;
		}
	},
	/**
	 * 移除一项
	 * @param  {jQuery|String} value option节点或某个option节点的value值
	 * @return {jQuery}       移除的option节点
	 */
	removeOne: function(node) {
		// 当传入节点
		var $node;
		if (typeof node === "string") {
			$node = this.get(node);
		} else {
			$node = node;
		}
		if ($node && $node.length) {
			$node.remove();
			this.$select.trigger('selectremove', $node);
			return $node;
		}
	},
	get: function(value) {
		var opts = this.$select.children(),
				result = null;
		if (value) {
			opts.each(function() {
				if (this.value === value) {
					result = $(this);
					return false;
				}
			});
			return result;
		} else {
			return opts;
		}
	},
	getSelected: function() {
		var $opts = this.$select.children();
		return $opts.filter("[selected]");
	},
	selectOne: function(value) {
		var $opt = this.get(value);
		this.$select.children().each(function(index, node) {
			$(node).prop("selected", false);
		});
		$opt.prop("selected", true);
	},
	selectAll: function() {
		var opts = this.$select.children();
		opts.each(function() {
			$(this).prop("selected", true);
		});
		return opts;
	},
	unselectAll: function() {
		var opts = this.$select.children();
		opts.each(function() {
			$(this).prop("selected", false);
		});
		return opts;
	},
	getSelectedValue: function() {
		return $.map(this.getSelected(), function(node) {
			return node.value;
		}).join(",");
	},
	add: function(nodes) {
		var that = this,
				$results = $(),
				res;

		$.each(nodes, function(index, node) {
			if (node.nodeType && node.nodeType === 1) {
				res = that.addOne($(node));
			} else {
				res = that.addOne(node);
			}
			if (res && res.length) {
				$results = $results.add(res);
			}
		});

		return $results;
	},
	remove: function(nodes) {
		var that = this,
				$results = $(),
				res;

		$.each(nodes, function(index, node) {
			if (node.nodeType && node.nodeType === 1) {
				res = that.removeOne($(node));
			} else {
				res = that.removeOne(node);
			}
			if (res && res.length) {
				$results = $results.add(res);
			}
		});

		return $results;
	},
	removeSelected: function() {
		var $selected = this.getSelected();
		this.remove($selected);
	}
};

var crossSelect = function($selectLeft, $selectRight) {
	var insLeft = new CusSelect($selectLeft),
			insRight = new CusSelect($selectRight);

	$selectLeft.on("selectremove", function(evt, $node) {
		insRight.addOne($node);
	});
	$selectRight.on("selectremove", function(evt, $node) {
		insLeft.addOne($node);
	});


	return {
		toRight: function() {
			insLeft.removeSelected();
		},
		toLeft: function() {
			insRight.removeSelected();
		},
		selectLeftAll: function() {
			insLeft.selectAll();
		},
		selectRightAll: function() {
			insRight.selectAll();
		}
	};
};

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
					text;

			if (len > 0) {
				//检查是否有条件
				currentIndex = this._getCurrentIndex();
			} else {
				//有条件才能添加左括号表达式
				Ui.tip(L.WF.CONDITION_NEED, "warning");
				return;
			}

			text = opts[currentIndex].text;

			//无法编辑已经“发生关系”的条件 O(∩_∩)O~
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
				}
			} else {
				text = text + " " + logic;
			}

			this.node.options[currentIndex].text = text;

			ptOpt = document.createElement('option');
			ptOpt.text = "( ";
			this.node.appendChild(ptOpt);
		},
		/**
		 * 增加右括号表达式
		 */
		addRightParenthesis: function(logic) {
			var opts = this.node.options,
					len = opts.length,
					currentIndex,
					text;

			if (len > 0) {
				currentIndex = this._getCurrentIndex();
			} else {
				Ui.tip(L.WF.CONDITION_NEED, "warning");
				return;
			}

			text = this.node.options[currentIndex].text;
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
			}
			opts[currentIndex].text = text;
		},
		addCondition: function(field, operator, value, logic) {
			var toAdd = true,
					len = this.node.length,
					inBracket = false,
					text,
					newText,
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

				for (var i = 0; i < len; i++) {
					if (this.node.options[i].text.indexOf(newText) >= 0) {
						Ui.tip(L.WF.CONDITION_REPEAT, "warning");
						return;
					}
				}

				if (toAdd) {
					optionNode = document.createElement('option');
					optionNode.text = newText;
					this.node.appendChild(optionNode);

					if (len > 0) {
						this.node.options[len - 1].text += "  " + logic;
					}
				} else {
					inBracket = $.trim(this.node.options[len - 1].text).substr(-1, 1) === "(";
					this.node.options[len - 1].text += (inBracket ? newText : " " + logic + " " + newText);
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
				result += node.options[i].text + "\n";
			}
			return result;
		}
	};
	return FormCondition;
}());
// 表单数据条件
var formCondition = new FormCondition(document.getElementById("condition_select"));
/**
 * 查询模板
 * @type {object}
 */
var searchTemplate = {
	_wid: 0,
	addUrl: Ibos.app.url('workflow/querytpl/add'),
	editUrl: Ibos.app.url('workflow/querytpl/edit'),
	delUrl: Ibos.app.url('workflow/querytpl/del'),
	listUrl: Ibos.app.url('workflow/querytpl/index'),
	showList: function(param, ok, cancel) {
		this._wid = guide.getId();
		guide.dialog(this.listUrl, param, {
			title: U.lang('WF.FLOW_QUERY_TPL'),
			ok: false,
			cancel: true
		});
	},
	showSetting: function(param, ok, cancel) {
		this._wid = guide.getId();
		var title = '', that = this, url;
		if (param) {
			url = this.editUrl;
			title = U.lang('WF.EDIT_QUERY_TPL');
		} else {
			url = this.addUrl;
			title = U.lang('WF.NEW_QUERY_TPL');
		}
		guide.dialog(url, param, {
			title: title,
			ok: function() {
				var $form = $('#advanced_search_form'),
					$conditionResult = $('#condition_result'),
					$conditionSelect = $('#condition_select'),
					viewFields = '', sumFields = '';
				// step1:处理可见显示字段
				$('#viewextfields').val('');
				$('#sumfields').val('');
				$.each($('#display_field').find('option'), function(i, n) {
					var val = n.value;
					viewFields += val + ',';
					if (n.selected) {
						sumFields += val + ',';
					}
				});
				$('#viewextfields').val(viewFields);
				$('#sumfields').val(sumFields);
				// step2:处理表单字段条件
				if ($conditionSelect.length > 0) {
					var checkExp = true;
					$.each($conditionSelect.find('option'), function(i, n) {
						if (!formCondition._valid(n.text)) {
							checkExp = false;
							return false;
						}
					});
					if (!checkExp) {
						Ui.tip(U.lang('WF.CONDITION_FORMAT_ERROR'), 'danger');
						if (tab) {
							tab.tab('#form_condition');
						}
						return false;
					}
				} else {
					$conditionResult.val('');
				}
				$form.waiting(U.lang('IN_SUBMIT'), "mini", true);
				$.post(url, $form.serializeArray(), function(data) {
					$form.stopWaiting();
					if (data.isSuccess) {
						Ui.tip(U.lang('SAVE_SUCEESS'), 'success');
						that.showList();
					} else {
						Ui.tip(U.lang('SAVE_FAILED'), 'danger');
					}
				}, 'json');
				return false;
			},
			cancel: cancel
		});
	},
	deleleTemplate: function(param, callback) {
		$.extend(param, {flowid: this._wid});
		$.post(this.delUrl, param, callback, 'json');
	}
};

var Manager = {
	_wid: 0,
	AddUrl: Ibos.app.url('workflow/manager/add'),
	EditUrl: Ibos.app.url('workflow/manager/edit'),
	listUrl: Ibos.app.url('workflow/manager/index'),
	deleteUrl: Ibos.app.url('workflow/manager/del'),
	showList: function(param) {
		this._wid = guide.getId();
		guide.dialog(this.listUrl, param, {
			title: U.lang('WF.MANAGER_LIST'),
			ok: false,
			cancel: true
		});
	},
	showSetup: function(param) {
		this._wid = guide.getId();
		var title = '', that = this, url;
		if (param) {
			url = this.EditUrl;
			title = U.lang('WF.EDIT_MANAGE_PRIV');
		} else {
			url = this.AddUrl;
			title = U.lang('WF.NEW_MANAGE_PRIV');
		}
		guide.dialog(url, param, {
			title: title,
			ok: function() {
				if ($.trim($('#auth_for').val()) === '') {
					Ui.tip(U.lang('WF.INVALID_AUTH_OBJECT'), 'danger');
					return false;
				}
				if ($('#manage_scope').val() === 'custom' && $('#custom_department_select').val() === '') {
					Ui.tip(U.lang('WF.INVALID_CUSTOM_DEPT'), 'danger');
					return false;
				}
				$('#auth_rule_setting').waiting(U.lang('IN_SUBMIT'), "mini", true);
				$.post(url, $('#manager_form').serializeArray(), function(data) {
					if (data.isSuccess) {
						$('#auth_rule_setting').stopWaiting();
						Ui.tip(U.lang('SAVE_SUCEESS'), 'success');
						that.showList();
					} else {
						Ui.tip(U.lang('SAVE_FAILED'), 'danger');
					}
				}, 'json');
				return false;
			},
			cancel: true
		});
	},
	deletePermission: function(param, callback) {
		$.extend(param, {flowid: this._wid});
		$.post(this.deleteUrl, param, callback, 'json');
	}
};

var timer = {
	_wid: 0,
	listUrl: Ibos.app.url('workflow/timer/index'),
	saveUrl: Ibos.app.url('workflow/timer/save'),
	showList: function(param, ok, cancel) {
		var that = this;
		guide.dialog(this.listUrl, param, {
			title: U.lang('WF.SET_THE_TIMING_TASK'),
			ok: function() {
				$('#crontab_form').waiting(U.lang('IN_SUBMIT'), "mini", true);
				$.post(that.saveUrl, $('#crontab_form').serializeArray(), function(data) {
					if (data.isSuccess) {
						$('#crontab_form').stopWaiting();
						Ui.tip(U.lang('SAVE_SUCEESS'), 'success');
						that.showList();
					} else {
						Ui.tip(U.lang('SAVE_FAILED'), 'danger');
					}
				}, 'json');
				return false;
			},
			cancel: cancel
		});
	}
};

var free = {
	_wid: 0,
	url: Ibos.app.url('workflow/type/freenew'),
	show: function(param) {
		var that = this;
		this._wid = guide.getId();
		guide.dialog(this.url, param, {
			id: 'free_new_user',
			title: U.lang('WF.SPECIFY_FREE_FLOW_NEW_USER'),
			ok: function() {
				var val = $('#new_user').val();
				if ($.trim(val) !== '') {
					$.post(that.url, {flowid: that._wid, formhash: G.formHash, newuser: val}, function(data) {
						if (data.isSuccess) {
							Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
							guide.hideDialog();
						}
					}, 'json');
				}
			}
		});
	}
};

(function() {
	var openFullWindow = function(url, name, paramStr) {
		paramStr = (paramStr ? (paramStr + ",") : "") + "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1,top=0, left=0, width=" + screen.availWidth + ", height=" + screen.availHeight;
		window.open(url, name, paramStr);
	}
	var handler = {
		editFlow: function(flowid) {
			window.location.href = Ibos.app.url('workflow/type/edit', {flowid: flowid});
		},
		importFlow: function(flowid) {
			Ui.openFrame(Ibos.app.url('workflow/type/import', {flowid: flowid}), {
				id: 'import_frame',
				title: U.lang('WF.SPECIFY_THE_IMPORT_FILE'),
				padding: 20,
				ok: function() {
					var ifm = this.iframe;
					ifm.contentDocument.forms[0].submit();
					return false;
				},
				okVal: U.lang("IMPORT"),
				cancel: true
			});
		},
		exportFlow: function(flowid) {
			window.location.href = Ibos.app.url('workflow/type/export', {flowid: flowid});
		},
		designForm: function() {
			openFullWindow(Ibos.app.url('workflow/formtype/design', {formid: guide.getFormId()}), "designForm");
		},
		previewForm: function() {
			openFullWindow(Ibos.app.url('workflow/formtype/preview', {formid: guide.getFormId()}), "previewForm");
		},
		designFlow: function(flowid) {
			openFullWindow(Ibos.app.url('workflow/process/index', {flowid: flowid}), "designFlow");
		},
		verifyFlow: function(flowid) {
			var opt = {
				title: U.lang('WF.EXAM_FLOW'),
				id: 'd_verify',
				padding: 0,
				cancel: true
			};
			var url = Ibos.app.url('workflow/type/verify', {flowid: flowid});
			Ui.ajaxDialog(url, opt);
		},
		freeNew: function() {
			free.show();
		},
		addSearchTemplate: function() {
			searchTemplate.showSetting();
		},
		showSearchTemplateList: function() {
			searchTemplate.showList();
		},
		addPermission: function() {
			Manager.showSetup();
		},
		showManagerPermissionList: function() {
			Manager.showList();
		},
		showTimerList: function() {
			timer.showList();
		}
	};

	$("#wf_guide").on("click", "[data-guide]", function() {
		var type = $.attr(this, "data-guide"),
				param = $.attr(this, "data-param"),
				flowid = guide.getId(),
				params = param ? $.parseJSON(param) : flowid;
		handler[type] && handler[type].call(handler, params, $(this));
	});
})();