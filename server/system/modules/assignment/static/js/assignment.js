/**
 * Assignment
 * 指派任务通用 JS
 * @version $Id$
 */

var Assignment = {
	// 任务最大字符数
	ASSIGN_MAXCHAR: 25,
	ASSIGN_DESC_MAXCHAR: 140,


	op: {
		// 发布任务
		addTask: function(data, callback){
			if(data && data.length) {
				$.post(Ibos.app.url('assignment/default/add', {'addsubmit': 1}), data, callback, "json");
			}
		},

		// 更新任务信息
		updateTask: function(id, data, callback){
			if(id && data && data.length){
				$.post(Ibos.app.url('assignment/default/edit', {'updatesubmit': 1, 'id': id}), data, callback, "json");
			}
		},

		// 获取任务信息
		getTask: function(id, callback){
			if(id){
				$.post(Ibos.app.url('assignment/default/edit', {'id': id}), callback, "json");
			}
		},

		// 移除任务
		removeTask: function(id, callback){
			if(id){
				$.post(Ibos.app.url("assignment/default/del"), { id: id }, callback, "json")
			}
		},

		// 催办任务
		urgeTask: function(id, callback){
			if(id){
				$.post(Ibos.app.url("assignment/unfinished/ajaxentrance"), { op: "push", id: id }, callback, "json")
			}
		},

		// 完成任务
		finishTask: function(id, callback){
			if(id){
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'toFinished'}), { id: id }, callback, "json");
			}
		},

		// 评价任务，添加图章
		addStamp: function(id, stampId, callback){
			if(id && stampId) {
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'stamp'}), { id: id, stamp: stampId }, callback, "json");
			}
		},

		// 重启任务
		restartTask: function(id, callback){
			if(id){
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'restart'}), { id: id }, callback, "json");
			}
		},

		// 申请延期任务
		applyDelayTask: function(id, param, callback){
			param = param || {}
			if(id){
				param.id = id;
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'applyDelay'}), param, callback, "json");
			}
		},

		// 发布人延期任务
		delayTask: function(id, param, callback){
			param = param || {}
			if(id){
				param.id = id;
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'delay'}), param, callback, "json");
			}
		},

		// 处理任务延期申请
		dealDelayApply: function(id, agree, callback){
			if(id){
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'runApplyDelayResult'}), {
					id: id,
					agree: +agree
				}, callback, "json");
			}
		},
		applyCancelTask: function(id, param, callback){
			param = param || {};
			// param.cancelReason;
			if(id){
				param.id = id;
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'applyCancel'}), param, callback, "json");
			}
		},
		cancelTask: function(id, callback){
			if(id){
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'cancel'}), {id: id}, callback, "json");
			}
		},
		dealCancelApply: function(id, agree, callback){
			if(id){
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'runApplyCancelResult'}), {
					id: id,
					agree: +agree
				}, callback, "json");
			}
		},
		addRemind: function(id, param, callback){
			param = param || {};
			// param.remindTime
			// param.remindContent
			if(id){
				param.id = id;
				$.post(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'remind', 'remindsubmit': 1}), param, callback, "json");
			}
		}
	},

	// 验证任务信息的正确性
	validateTaskForm: function(form){
		if(!form || !form.elements){
			return false;
		}

		var subject = $.trim(form.subject.value);
		// 任务内容
		if(!subject){
			Ui.tip("@ASM.PLEASE_INPUT_SUBJECT", "warning");
			return false;
		}
		if(U.getCharLength(subject) > this.ASSIGN_MAXCHAR) {
			Ui.tip("@ASM.SUBJECT_OVERCOUNT", "warning");
			return false;
		}

		// 负责人
		if(!form.chargeuid.value){
			Ui.tip("@ASM.PLEASE_SELECT_CHARGE", "warning");
			return false;
		}

		if(!form.endtime.value){
			Ui.tip("@ASM.PLEASE_SELECT_ENDTIME", "warning");
			return false;
		}

		if(U.getCharLength(form.description.value) > this.ASSIGN_DESC_MAXCHAR) {
			Ui.tip("@ASM.DESCRIPTION_OVERCOUNT", "warning");
			return false;
		}

		return true;
	},

	inLoading: function(inLoad){
		if(inLoad){
			$('[data-node-type="taskView"]').waiting(null, "normal");
		} else {
			$('[data-node-type="taskView"]').waiting(false);
		}
	},

	addTask: function(form, callback){
		// 添加任务时的视图变化
		var addTaskView = function(data){
			if(data){
				var tableTpl = '<table class="table table-hover am-op-table" data-node-type="taskTable"></table>';

				// 如果是安排给自己，则另外需要添加一行负责任务
				if(data.charge.uid == Ibos.app.g("uid")){
					var $chargeTable = $("#am_my_charge [data-node-type='taskTable']");
					// 目前还没有任何一行时，新建表格并去除空值
					if(!$chargeTable.length){
						$chargeTable = $(tableTpl).replaceAll('#am_my_charge .am-charge-empty');
					}
					data.chargeself = true;
					$chargeTable.prepend($.template("tpl_task", data));
				// 否则添加一行指派任务
				} else {
					var $designeeTable = $("#am_my_designee [data-node-type='taskTable']");
					if(!$designeeTable.length){
						$designeeTable = $(tableTpl).replaceAll('#am_my_designee .am-designee-empty');
					}
					$designeeTable.prepend($.template("tpl_task", data));
				}
			}
		}

		// 重置新增任务表单
		var resetAddForm = function(form){
			var $form = $(form);
			// 取回焦点
			$($form[0].subject).val("").focus();
			$form[0].description.value = "";
			
			// 移除所有附件
			$('[data-node-type="attachRemoveBtn"]', $form).trigger("click");
		}

		if(this.validateTaskForm(form)){
			var $form =$(form);
			$form.waiting(null, "small", true);
			Assignment.op.addTask($(form).serializeArray(), function(res){
				$form.waiting(false);
				if(res.isSuccess) {
					addTaskView(res.data);
					resetAddForm(form);
					Ui.tip("@ASM.ADD_TASK_SUCCESS");				
				} else {
					Ui.tip(res.msg, "danger");
				}
			})
		}
	},

	updateTask: function(id, form, callback){
		if(this.validateTaskForm(form)){
			Assignment.op.updateTask(id, $(form).serializeArray(), callback)
		}
	},

	// 从视图上移除对应任务
	removeTask: function(ids){
		var idArr;
		if(ids){
			idArr = (ids + "").split(",");
		}
		if(idArr.length){
			$.each(idArr, function(i, id){
				$("[data-node-type='taskTable'] tr[data-id='" + id + "']").fadeOut(function(){
					$(this).remove()
				});
			});
		}
	},

	openTaskEditDialog: function(id){
		var _this = this;
		Ui.closeDialog("d_am_edit");
		Ui.ajaxDialog(Ibos.app.url("assignment/default/edit", { id: id }), {
			id: "d_am_edit",
			title: U.lang("ASM.EDIT_TASK"),
			width: 780,
			padding: "20px"
		})
	},

	showRemindDialog: function(id){
		Ui.ajaxDialog(Ibos.app.url('assignment/unfinished/ajaxentrance', {'op': 'remind', 'id': id}), {
			id: "d_task_remind",
			title: U.lang("ASM.SETUP_REMIND"),
			ok: function(){
				var dialog = this,
					$form = this.DOM.content.find("form"),
					remindDate = $form[0].reminddate.value,
					remindTime = $form[0].remindtime.value,
					param = {};

				param.remindTime = remindDate ? remindDate + " " + remindTime : "";
				param.remindContent = $form[0].remindcontent.value;
				Assignment.op.addRemind(id, param, function(res){
					if(res.isSuccess) {
						dialog.close();
						Ui.tip(res.msg);
					} else {
						Ui.tip(res.msg, "danger");
					}
				});
				return false;
			},
			cancel: true
		})
	}
}

// 下拉菜单日期选择器
// @Todo: 视使用情况考虑优化后作为组件使用？
// @Todo: 语言包整理
var DropdownDatepicker = function(selector){
	var $elem = $(selector);
	var createItem = function(dateOffset){
		var $item;
		var today = (new Date).getDay();
		var dayLang = ["周日", "周一", "周二", "周三", "周四", "周五", "周六"];
		var tpl = '<li class="<%= active ? \'active\' : \'\' %>">' + 
			'<a href="javascript:;" data-node-type="dateOffset" data-offset="<%= offset %>"><%=dayName%></a>' +
		'</li>'

		// 今天
		if(dateOffset == 0){
			$item = $.tmpl(tpl, { active: true, offset: 0, dayName: "今天" });
		// 下周
		} else if(dateOffset + today > 6) {
			$item = $.tmpl(tpl, { active: false, offset: dateOffset, dayName: "下" +  dayLang[dateOffset + today - 7] });
		// 本周
		} else {
			$item = $.tmpl(tpl, { active: false, offset: dateOffset, dayName: dayLang[dateOffset + today] });
		}
		return $item;
	}

	var createMenu = function(){
		var $menu = $('<ul class="dropdown-menu dropdown-datepicker-menu"></ul>');
		for(var i = 0; i < 7; i++) {
			$menu.append(createItem(i));
		}
		$menu.append(
			'<li class="divider" style="margin: 0;"></li>',
			'<li><a href="javascript:;" data-node-type="dateOther">其他日期</a></li>',
			'<li class="divider" style="margin: 0;"></li>',
			'<li><a href="javascript:;" data-node-type="dateEmpty">不再</a></li>'
		)

		// 初始化其他日期
		var $pickerCtrl = $menu.find('[data-node-type="dateOther"]').datepicker()
		// 显示日期选择器时，重新定位
		.on("show", function(){
			var widget = $(this).data("datetimepicker").widget;
			widget.position({
				of: this,
				at: "right top",
				my: "left+5 top"
			})
		})
		// 关闭日期选择器同时关闭下拉菜单
		.on("hide", function(){
			$(document).trigger("click.dropdown.data-api");
		})
		.on("changeDate", function(evt){
			notifyChange({ date: evt.localDate })
		});

		$menu.bindEvents({
			// 快捷日期选择
			"click [data-node-type='dateOffset']": function(){
				var offset = +$.attr(this, "data-offset"),
					date = new Date();
				date.setDate(date.getDate() + offset);
				notifyChange({ date: date });
				select($(this).parent());
			},
			"click [data-node-type='dateEmpty']": function(){
				notifyChange({ date: null });
				select($(this).parent());
			}
		});

		return $menu;
	}

	var select = function($elem){
		$elem.addClass("active").siblings().removeClass("active");
	}

	var notifyChange = function(data){
		$elem.trigger("changeDate", data)
	}
	
	if($elem && $elem.length){
		$elem.attr("data-toggle", "dropdown").after(createMenu());
	}
}

$(function(){
	Ibos.evt.add({
		// 回到我负责的任务顶部
		"toCharge": function(){
			Ui.scrollYTo("am_my_charge", -75);
		},
		// 回到我指派的任务顶部
		"toDesignee": function(){
			Ui.scrollYTo("am_my_designee", -75);
		},
		// 回到我参与的任务顶部
		"toParticipant": function(){
			Ui.scrollYTo("am_my_participant", -75);
		},
		// 回到页面顶部
		"totop": function(){
			Ui.scrollToTop()
		},

		// 打开提醒设置对话框
		"openRemindDialog": function(param){
			Assignment.showRemindDialog(param.id);
		},

		// 打开编辑任务对话框
		"openTaskEditDialog": function(param){ 
			Assignment.openTaskEditDialog(param.id)
		},

		// 移除任务
		"removeTask": function(param){
			Ui.confirm(U.lang("ASM.REMOVE_TASK_CONFIRM"), function(){
				Assignment.inLoading(true);
				Assignment.op.removeTask(param.id, function(res){
					Assignment.inLoading(false);
					if(res.isSuccess){
						Assignment.removeTask(param.id);
						Ui.tip(res.msg);
					} else {
						Ui.tip(res.msg, "danger");
					}
				});
			});
		},

		// 更新任务数据
		"updateTask": function(param){
			var dialog = Ui.getDialog("d_am_edit"),
				form = dialog.DOM.content.find("form")[0];

			Assignment.updateTask(param.id, form, function(res){
				if(res.isSuccess){
					Ui.tip(res.msg);
					dialog.close();
					window.location.reload();
				} else {
					Ui.tip(res.msg, "danger");
				}
			})
		}
	});
	
	// 更改任务状态
	$(document).on("click", ".am-checkbox:not(.disabled)", function(){
		var id = $.attr(this, "data-id"),
			$elem = $(this),
			_callback = function(res){
				Assignment.inLoading(false);
				if(res.isSuccess){
					Assignment.removeTask(id);
					Ui.tip(res.msg)
				} else {
					Ui.tip(res.msg, "danger");
				}
			}

		$elem.addClass("checked");
		Assignment.inLoading(true);		
		// 取消完成（跟重启任务是同一操作？）
		if($elem.hasClass("am-checkbox-ret")) {
			Assignment.op.restartTask(id, _callback)
		// 完成任务
		} else {
			Assignment.op.finishTask(id, _callback);		
		}
	})
})
