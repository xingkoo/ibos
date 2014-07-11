(function(){
	var jsPlumbDefaults = {
		Endpoint: "Blank",
		// Endpoint:[ "Dot", { radius:5 } ],
		// EndpointStyle:{ fillStyle:"transparent" },
		// EndpointHoverStyle:{ fillStyle:"#ffa500" },
		
		Connector: "Flowchart",

		ConnectionOverlays : [
			[ "Arrow", { 
				location: 1,
				id: "arrow",
	            length: 14,
	            foldback: 0.6
			} ]
		],

		ConnectionsDetachable: false,
		ReattachConnections: false,

		// LogEnabled: true, // 调试模式
		// 锚点自动调整位置
		Anchor: "Continuous",
		// Anchor: [[0, 0.5, 1, 0], [1, 0.5, 1, 0]],
		// 连接器样式
		PaintStyle: { 
			strokeStyle:"#EE8C0C",
			lineWidth: 3,
			dashstyle: "0",
			outlineWidth: 4,
			outlineColor: "transparent"
		},

		HoverPaintStyle: {
			strokeStyle:"#B2C0D1",
			lineWidth:3,
			dashstyle:"4 1"
		}
	};


	// 判断连接器是否指向了自己
	var isConnectToSelf = function(param) {
			return param.sourceId === param.targetId
		},
		isFromStartToEnd = function(param){
			return param.sourceId === "step_-1" && param.targetId === "step_0"
		},
		// 判断连接步骤是否重复
		stepIsRepeat = function(param){
			// 获取同一scope下的链接器，判断当前链接是否有重复
			var cnts = jsPlumb.getConnections(param.scope);
			if(cnts.length > 0) {
				for(var i = 0, len = cnts.length; i < len; i++) {
					// 如果有链接器 sourceId 及 targetId 都相等，那大概也许可能就相等了。
					if(param.sourceId === cnts[i].sourceId && param.targetId === cnts[i].targetId) {
						return true;
					}
				}
			}
			return false;
		};

	// 连接前的事件，
	var beforeDropHandler = function(param) {
		if(isConnectToSelf(param)){
			return false;
		}
		if(stepIsRepeat(param)){
			Ui.tip(U.lang('WF.REPEAT_STEP'), "warning");
			return false;
		}
		if(isFromStartToEnd(param)) {
			Ui.tip(U.lang('WF.NOT_FROM_START_TO_END'), 'warning');
			return false;
		}
		return true;
	};

	// 连接时, 判断步骤是否重复
	jsPlumb.bind("beforeDrop", beforeDropHandler);

	jsPlumb.importDefaults(jsPlumbDefaults);
	
})();


var processDesign = {
	instance: new wfDesigner($("#wf_designer_canvas"), jsPlumb),
	parseData: function(data){
		var ret = {
				steps: [],
				connects: []
			};
		var pushConnect = function(sourceId, targetIds) {
			var tids,d;
			if(typeof sourceId === "undefined" || typeof targetIds !== "string" || targetIds === "") {
				return false;
			}
			tids = targetIds.split(",");
			for(var i = 0; i < tids.length; i++) {
				ret.connects.push(sourceId + "," + tids[i]);
			}			
		};

		if(data && data.length) {
			for(var i = 0; i < data.length; i++) {
				// 只接收需要的属性
				d = {
					id: data[i].id,
					index: data[i].processid,
					top: data[i].top,
					left: data[i].left,
					name: data[i].name,
					to: data[i].to
				};
				// 特殊步骤处理, (开始结束)
				if(d.index === -1){
					d.cls = "wf-step wf-step-start";
				} else if(d.index === 0){
					d.cls = "wf-step wf-step-end";
				}
				ret.steps.push(d);
				if(d.to) {
					pushConnect(d.index, d.to);
				}
			}
		}

		return ret;
	},
	set: function(data){
		var that = this,
			_data;
			addStartStep = function(){
				that.instance.addStep({
					id: -1,
					name: L.START,
					cls: "wf-step wf-step-start",
					index: -1
				});
				
			},
			addEndStep = function(){
				var $step = that.instance.addStep({
					id: 0,
					name: L.END,
					cls: "wf-step wf-step-end",
					index: 0
				});
				var $container = $("#wf_designer_canvas");
				var position = {
					left: $container.outerWidth() - $step.outerWidth() - 80,
					top: $container.outerHeight() - $step.outerHeight() - 80
				};

				$step.css(position);
				that.instance.updateData('0', position);
			};


		if(data && data.length) {
			_data = this.parseData(data);
			that.instance.addSteps(_data.steps);
			// 在步骤都加载完成后才连线
			that.instance.addConnects(_data.connects);
		} else {
			// 增加开始和结束步骤
			addEndStep();
			addStartStep();
		}
		jsPlumb.setTargetEnabled("step_-1", false);
		jsPlumb.setSourceEnabled("step_0", false);
	},
	add: function(data){
		return this.instance.addSteps(data, null, true);
	},

	getData: function(){
		var steps = [],
			connects = [],
			stepData = this.instance.getSteps(),
			connectData = this.instance.getConnects();

		// 解析步骤数据
		for(var i in stepData){
			if(stepData.hasOwnProperty(i)){
				stepData[i].processid = stepData[i].index;
				steps.push({
					id: stepData[i].id,
					processid: stepData[i].processid,
					top: stepData[i].top,
					left: stepData[i].left,
					name: stepData[i].name
				});
			}
		}
		// 解析链接数据
		for(var i in connectData){
			if(connectData.hasOwnProperty(i)){
				connects.push({ prcs: connectData[i]});
			}
		}
		return {
			steps: steps,
			connects: connects
		};
	}
};


$(function() {
	/**
	 * 设计器wrapper
	 * @param {object} $container  
	 */
	var $container = $("#wf_designer_canvas");
	/**
	 * 当前流程ID
	 * @param {integer} flowId 
	 */
	var flowId = Ibos.app.g('flowId');
	

	/**
	 * 数据通信对象
	 * @param {object} wfInter
	 */
	var wfInter = {
		/**
		 * 初始化流程链接
		 * @param {function} callback
		 * @returns {undefined}
		 */
		init: function(callback) {
			 var url = Ibos.app.url("workflow/process/getprocess"),param = {flowid: flowId};
			 $container.waiting(U.lang('WF.LOADING_PROCESS'));
			 $.post(url, param, function(res){
			 	if(res.isSuccess) {
					// 生成节点与链接
			 		processDesign.set(res.data);
					// 初始化步骤右键菜单
					$(normalStepSelector).contextMenu("step_context_menu", stepContextMenuSettings);
					// 初始化通用右键菜单
					$container.contextMenu("common_context_menu", commonContextMenuSettings);
					// 阻止非空白处的右键事件，不出现菜单
					$container.on("contextmenu", "*", function(evt) {
						evt.stopPropagation();
					});
					// 绑定事件点击控制事件
					Ibos.events.add(wfControll);
			 	}
				$container.stopWaiting();
			 	callback && callback(res);
			 },'json');
		},
		/**
		 * 增加步骤提交
		 * @param {object} data 整理后的表单数据
		 * @param {function} callback 执行回调函数
		 * @returns {undefined}
		 */
		addStep: function(data, callback) {
			 var url = Ibos.app.url("workflow/process/add");
			 $.post(url, $.extend({ flowid: flowId, formhash: Ibos.app.g('formHash') }, data), callback, 'json');
		},
		/**
		 * 读取编辑页面
		 * @param {integer} processId 步骤ID
		 * @param {string} op 编辑类型
		 * @param {function} callback 执行回调函数
		 * @returns {undefined}
		 */
		getStepEdit: function(processId, op, callback) {
			 var url = Ibos.app.url("workflow/process/edit");
			 	param = {
			 		flowid: flowId,
			 		processid: processId,
			 		op: op
			 	};
			 $.get(url, param, callback);
		},
		/**
		 * 提交编辑页面
		 * @param {integer} processId 步骤ID
		 * @param {object} formData 表单数据
		 * @param {function} callback 执行回调函数
		 * @returns {undefined}
		 */
		saveStepEdit: function(processId, formData, callback) {
			//步骤节点检查
			if( $('#node_type').val() === '0' ){
				if( $('#step_name').val() === "") {
					Ui.tip(U.lang('WF.EMPTY_STEP_NAME'),'warning');
					$("#flow_edit_tab a[href='#step_basic_info']").tab("show");
					$('#step_name').blink();
					return false;
				}
			} else { //子流程节点检查
				if( $('#subflow_type').val() === "" ) {
					Ui.tip(U.lang('WF.EMPTY_CHILD_FLOW'),'warning');
					$("#flow_edit_tab a[href='#step_basic_info']").tab("show");
					$('#subflow_type').blink();
					return false;
				}
			}
			var con = $("[data-select='condition']");
			if(con.length > 0) {
				var checkCon = true;
				$.each (con,function(i,o){
					var id = $(o).attr('data-id'),$conditionResult = $('#condition_result_'+id),$conditionSelect = $('#condition_select_'+id);
					var formCondition = new FormCondition(document.getElementById("condition_select_"+id));
					if ($conditionSelect.children().length > 0) {
						var checkExp = true;
						$.each($conditionSelect.find('option'), function(i, n) {
							if (!formCondition._valid(n.text)) {
								checkExp = checkCon = false;
								return false;
							}
						});
						if (!checkExp) {
							$conditionSelect.focus().blink();
							return false;
						}
					} else {
						$conditionResult.val('');
					}
				});
				if(!checkCon){
					Ui.tip(U.lang('WF.CONDITION_FORMAT_ERROR'), 'warning');
					$("#flow_edit_tab a[href='#step_condition']").tab("show");
					return false;
				}
			}
			$("#step_edit_form").waiting(U.lang('IN_SUBMIT'), "mini", true);
			var param = {
				flowid : flowId,
				oldprcsid:processId,
				formhash:G.formHash
			};
			var url = Ibos.app.url("workflow/process/edit");
			$.post(url+'&'+$.param(param), formData, callback,'json');
		},
		/**
		 * 删除步骤提交
		 * @param {integer} processId 步骤ID
		 * @param {function} callback 执行回调函数
		 * @returns {undefined}
		 */
		removeStep: function(processId, callback) {
			 var url = Ibos.app.url("workflow/process/del"),
			 	param = {
					formhash: Ibos.app.g('formHash'),
					flowid:flowId,
			 		processid: processId
			 	};
			 $.post(url, param, callback,'json');
		},
		/**
		 * 保存链接与视图
		 * @param {object} data
		 * @param {function} callback
		 * @returns {undefined}
		 */
		save: function(data, callback) {
			 var url = Ibos.app.url("workflow/process/saveview"),
			 	param = {
			 		flowid: flowId,
			 		data: data,
					formhash: Ibos.app.g('formHash')
			 	};
			 $.post(url, param, callback);
		}
	};
	/**
	 * 设计器容器内普通步骤选择器
	 * @param {String} normalStepSelector 
	 */
	var normalStepSelector = ".wf-step:not(.wf-step-start,.wf-step-end)";
	// 执行初始化流程步骤操作
	wfInter.init();
	$container.height($(window).height() - 170);
	window.onresize = function() {
		$container.height($(window).height() - 170);
	};
	/**
	 * 右侧信息条
	 * @param function infoBar
	 */
	var infoBar = (function() {
		var $container = $("#wf_step_info");
		// 右侧信息栏编辑事件
		$container.on("click", "[data-type='edit']", function() {
			var op = $.attr(this, "data-operate"),processId = $.attr(this, "data-id");
			wfControll.edit(processId, op);
		});
		return {
			$container: $container,
			setContent: function(content) {
				$container.html(content);
			},
			/**
			 * 加载步骤信息
			 * @param {integer} processId 步骤ID
			 * @param {function} callback 执行回调函数
			 * @returns {undefined}
			 */
			loadContent: function(param){
				var that = this;
				$container.waiting(U.lang('READ_INFO'));
				$.post(Ibos.app.url('workflow/process/getprocessinfo'), param, function(res){
					if (res.isSuccess) {
						$container.stopWaiting();
						that.setContent(res.data);
					}
				}, 'json')
			},
			reset: function() {
				$container.html("<div class='wf-step-guide'></div>");
			}
		};
	})();
	/**
	 * 控制器
	 * @param object wfControll
	 */	
	var wfControll = {
		/**
		 * 编辑步骤
		 * @param {integer} processId 步骤ID
		 * @param {string} op 编辑类型
		 * @returns {undefined}
		 */
		editStep: function(processId, op) {
			var isLoad = false,ins = processDesign.instance,dialog;
			processId = processId.replace("step_", "");
			if (processId && ins.hasIndex(processId)) {
				var title = ins.getData(processId).name;
				Ui.closeDialog("d_step_edit");
				dialog = Ui.dialog({
					id: "d_step_edit",
					padding: 0,
					title: title,
					okVal: U.lang('SAVE'),
					width:'800px',
					ok: function() {
						var newProcessId = parseInt($("#step_index").val(), 10),
							name = $.trim($("#step_name").val());
						// processId及name都不为空时，处理数据
						if (!isNaN(newProcessId) && name !== "") {
							// 不允许processId重复
							if (newProcessId != processId && processDesign.instance.hasIndex(newProcessId)) {
								Ui.tip(U.lang('WF.STEP_EXISTS'), "warning");
								return false;
							}
						}
						if (isLoad) {
							var data = $("#step_edit_form").serializeArray();
							wfInter.saveStepEdit(processId, data, function(res) {
								if (res.isSuccess) {
									$("#step_edit_form").stopWaiting();
//									ins.updateData(processId, res.data);
//									ins.repaint(); // ins.updateStep()
									dialog.close();
									window.location.reload();
								} else {
									$("#step_edit_form").stopWaiting();
									dialog.close();
									Ui.tip(res.msg, "warning");
								}
							});
							return false;
						}
					},
					cancel: true
				});
				wfInter.getStepEdit(processId, op, function(res) {
					dialog.content(res);
					isLoad = true;
				});
			}
		},
		// 增加步骤， 打开新增对话框
		addStep: function() {
			// 新增行
			var addRow = function(data) {
				var $lastRow = $("#flow_add_table").find("tbody tr:eq(-1)");
				return $.tmpl("tpl_flow_add_row", data).insertBefore($lastRow);
			};
			Ui.dialog({
				id: "d_step_add",
				title:U.lang('WF.ADD_STEP'),
				content: $.template("tpl_flow_add"),
				padding: 0,
				init: function() {
					var index = processDesign.instance.getMaxIndex();
					// 初始化三条记录
					addRow({index: ++index});
					addRow({index: ++index});
					addRow({index: ++index});

					// 行移除
					$("#flow_add_table").on("click", "[data-type='removeRow']", function() {
						$(this).parent().parent().remove();
					// 行增加
					}).on("click", "[data-type='addRow']", function() {
						addRow({index: ++index});

					}).on("keypress", "[name='index']", function(evt) {
						// 禁止输入数字以外的字符且首字符不能为0
						if ((evt.which === 48 && this.value == "") || evt.which > 57 || evt.which < 48) {
							return false;
						}

					}).find(".checkbox input").label();
				},
				ok: function() {
					var steps = [],connects = [],needConnect = $("#link_by_index").prop("checked");
					// 循环每行获取对应的processId及name值
					$("#flow_add_table tbody tr").each(function() {
						var $row = $(this);
						var processId = parseInt($row.find("input[name='index']").val(), 10),
								name = $.trim($row.find("input[name='name']").val());
						// processId及name都不为空时，处理数据
						if (!isNaN(processId) && name !== "") {
							// 不允许processId重复
							if (processDesign.instance.hasIndex(processId)) {
								Ui.tip(U.lang('WF.STEP_EXISTS'), "warning");
								return false;
							}
							// 也不允许新增中有processId重复
							for (var i = 0; i < steps.length; i++) {
								if (processId === steps[i].processId) {
									Ui.tip(U.lang('WF.STEP_EXISTS'), "warning");
									return false;
								}
							}
							// 满足所有条件的加入数组
							steps.push({processId: processId, name: name});
						}
					});
					if (steps.length) {
						$("#flow_add_table").waiting(U.lang('IN_SUBMIT'), "mini", true);
						wfInter.addStep({steps:steps}, function(res) {
							var $steps;
							if (res.isSuccess) {
								// 增加至视图
								if (res.data && res.data.length) {
									for (var i = 0; i < res.data.length; i++) {
										steps[i].id = res.data[i];
										steps[i].index = steps[i].processId;
										$steps = processDesign.add(steps[i]);
										// 初始化右键菜单
										$steps.contextMenu("step_context_menu", stepContextMenuSettings);
									}
								}
								// 初始化右键菜单 
								// 默认需要连线时
								if (needConnect) {
									// 按序号排序时，排序数组
									steps = steps.sort(function(a, b) {
										return a.index > b.index;
									});
									for (var i = 0; i < steps.length; i++) {
										if (i !== steps.length - 1) {
											processDesign.instance.addConnects(steps[i].index + "," + steps[i + 1].index);
										}
									}
								}
								$("#flow_add_table").stopWaiting();
							}
						});
						return true;
					}
					return false;
				}
			});
		},
		// 移除步骤
		removeStep: function(id) {
			var name;
			if (id && processDesign.instance.hasIndex(id)) {
				name = processDesign.instance.getData(id).name;
				Ui.confirm(U.lang('WF.DELETE_STEP_CONFIRM', { name: name }), function() {
					wfInter.removeStep(id, function(res) {
						if (res.isSuccess) {
							Ui.tip(U.lang('DELETE_SUCCESS'), "success");
							processDesign.instance.removeStep(id);
						}
					});
				});
			} else {
				Ui.tip(U.lang('WF.PLEASE_SELECT_A_STEP'), "warning");
			}
		},
		/**
		 * 删除选中步骤
		 * @returns {undefined}
		 */
		removeSelectedStep: function() {
			var selectedId = processDesign.instance.getSelect();
			wfControll.removeStep(selectedId);
		},
		/**
		 * 保存视图
		 * @returns {undefined}
		 */
		saveFlow: function() {
			var data = processDesign.getData();
			$('#submit_btn').button('loading');
			wfInter.save(data, function(res) {
				if (res.isSuccess) {
					Ui.tip(U.lang("SAVE_SUCEESS"),'success');
					$('#submit_btn').button('reset');
				}
			});
		},
		/**
		 * 清除所有链接
		 * @returns {undefined}
		 */
		clearAllConnect: function() {
			Ui.confirm(U.lang('WF.CLEAR_CONNECTION_CONFIRM'), function() {
				processDesign.instance.clearConnect();
			});
		},
		/**
		 * 重新加载视图
		 * @returns {undefined}
		 */
		reloadFlow: function() {
			processDesign.instance.clear();
			wfInter.init();
		},
		/**
		 * 打印
		 * @returns {undefined}
		 */
		printProcess: function() {
			window.print();
		},
		/**
		 * 关闭设计器
		 */
		closeDesinger: function() {
			window.close();
		}
	};

	/**
	 * 步骤右键菜单配置
	 */
	var stepContextMenuSettings = {
			bindings: {
				// 编辑-基本信息
				"step_context_basic": function(elem, b) {
					wfControll.editStep(elem.id, "base");
				},
				// 编辑-表单字段
				"step_context_field": function(elem, b) {
					wfControll.editStep(elem.id, "field");
				},
				// 编辑-经办角色
				"step_context_handler": function(elem, b) {
					wfControll.editStep(elem.id, "handle");
				},
				// 编辑-转出条件
				"step_context_condition": function(elem, b) {
					wfControll.editStep(elem.id, "condition");
				},
				// 删除步骤
				"step_context_del": function(elem, b) {
					var id = elem.id.replace("step_", "");
					wfControll.removeStep(id);
				}
				// @Todo 新增下一步骤
			}
		},
		/**
		 * 通用右键菜单配置对象
		 */
		commonContextMenuSettings = {
			bindings: {
				// 新增步骤
				// @Todo: 是否新增单一步骤
				"common_context_addstep": wfControll.addStep,
				// 刷新视图
				"common_context_reload": wfControll.reloadFlow,
				// 保存流程
				"common_context_save": wfControll.saveFlow
			}
		};

	// 编辑步骤细节处理
	// 双击编辑步骤
	$container.on("dblclick", normalStepSelector, function() {
		wfControll.editStep(this.id, "base");
	});
	// 读取步骤信息并刷新右栏
	infoBar.reset();
	// 选中步骤并读取步骤信息
	var loadLock;
	$container.on("click", normalStepSelector + ",.wf-step-active", function() {
		var id = parseInt(this.id.replace("step_", ""), 10);
		processDesign.instance.select(id);
		if(!loadLock) {
			infoBar.loadContent({ flowid: flowId, processid: id });
			loadLock = true;
			setTimeout(function(){
				loadLock = false;
			}, 500)
		}
	});
});