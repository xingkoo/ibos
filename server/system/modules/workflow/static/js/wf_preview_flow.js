/**
 * 
 */

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
	var flowId = Ibos.app.g("flowId");

	// 初始化
	$container.height($(window).height() - 110);
	window.onresize = function() {
		$container.height($(window).height() - 110);
	};

	// 还原步骤数据
	$.get(Ibos.app.url('workflow/preview/getprcs', {
		flowid: flowId,
		runid: Ibos.app.g("runId")
	}), function(data) {
		processView.set(data);
	}, 'json');
	

	var openFlowChart = function(id) {
		var url = Ibos.app.url('workflow/preview/flow', {
			key: Ibos.app.g("flowKey"),
			prcsid: id
		});
		window.open(url, 'subflow', "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1,top=0, left=0, width=" + screen.availWidth + ", height=" + screen.availHeight);
	};
	/**
	 * 子流程双击
	 */
	$container.on("dblclick", ".wf-step-sub", function() {
		var processId = this.id && this.id.replace("step_", "");
		openFlowChart(processId);
	});

	Ibos.events.add({
		/**
		 *
		 * @returns {undefined}
		 */
		"printProcess": function() {
			window.print();
		},
		/**
		 * 关闭查看器
		 * @returns {undefined}
		 */
		"closeDesinger": function() {
			window.close();
		},
		/**
		 * 经办人重新办理
		 * @param {type} param
		 * @param {type} $elem
		 * @returns {undefined}
		 */
		'redo': function(param, $elem) {
			Ui.confirm(U.lang('WF.CONFIRM_REDO'), function() {
				var url = Ibos.app.url('workflow/preview/redo');
				$.post(url, {
					formhash: G.formHash,
					key: param.key,
					uid: param.uid
				}, function(data) {
					if (data.isSuccess) {
						Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
						$elem.remove();
					} else {
						Ui.tip(U.lang('WF.OPERATION_FAILED'), 'danger');
					}
				}, 'json');
			});
		}
	});
	$(document).ready(function() {
		if (U.getCookie('workflow_todo_remind_' + Ibos.app.g("runId")) == '1') {
			$('#todo_remind_btn').addClass('disabled').html(U.lang('WF.ALREADY_SEND_REMIND'));
		} else {
			Ibos.events.add({ 
				// 超时催办
				"overtimeRemind": function(param, $elem) {
					Ui.dialog({
						id: 'd_confirm_remind',
						title: U.lang('WF.SEND_TODO_REMIND'),
						padding: 0,
						content: document.getElementById('confirm_remind'),
						ok: function() {
							var val = $('#message').val(),
								toid = $('#toid').val();
							if (val == '') {
								$('#message').blink().focus();
							}
							$('#confirm_remind').waiting(U.lang('IN_SUBMIT'), "mini");
							var url = Ibos.app.url('workflow/preview/sendremind');
							$.post(url, {
								formhash: G.formHash,
								runid: Ibos.app.g("runId"),
								message: val,
								toid: toid
							}, function(data) {
								$('#confirm_remind').stopWaiting();
								if (data.isSuccess) {
									Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
									Ui.closeDialog();
									$elem.remove();
								} else {
									Ui.tip(U.lang('OPERATION_SUCCESS'), 'danger');
								}
							}, 'json');
							return false;
						},
						cancel: true
					});
				}
			});
		}
	});
});