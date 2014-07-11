$(function() {
	Ibos.events.add({
		/**
		 * 添加规则
		 * @param {Object} param
		 * @param {Object} $elem
		 * @returns {undefined}
		 */
		'addRule': function(param, $elem) {
			var url = Ibos.app.url('workflow/entrust/add');
			Ui.ajaxDialog(url, {
				title: U.lang('WF.ADD_ENTRUST_RULE'),
				id: 'd_rule',
				width: '380px',
				ok: function() {
					if (postCheck()) {
						$('#entrust_form').submit();
					}
					return false;
				},
				cancel: true
			});
		},
		/**
		 * 开启
		 * @param {type} param
		 * @param {type} $elem
		 * @returns {undefined}
		 */
		'setEnabled': function(param, $elem) {
			var ids = wfList.getCheckedId();
			wfList.batchOpt(Ibos.app.url('workflow/entrust/status'), {flag: '1'}, function() {
				var arr = ids.split(",");
				for (var i = 0; i < arr.length; i++) {
					var $tr = $("#list_tr_" + arr[i]);
					$tr.find('[data-change]').prop('checked', true).trigger('change');
				}
			}, null);
		},
		/**
		 * 关闭
		 * @param {type} param
		 * @param {type} $elem
		 * @returns {undefined}
		 */
		'setDisabled': function(param, $elem) {
			wfList.batchOpt(Ibos.app.url('workflow/entrust/status'), {flag: '0'}, function(res, ids) {
				var arr = ids.split(",");
				for (var i = 0; i < arr.length; i++) {
					var $tr = $("#list_tr_" + arr[i]);
					$tr.find('[data-change]').prop('checked', false).trigger('change');
				}
			}, null);
		},
		/**
		 * 删除规则
		 * @param {type} param
		 * @param {type} $elem
		 * @returns {undefined}
		 */
		'delRule': function(param, $elem) {
			wfList.batchOpt(Ibos.app.url('workflow/entrust/del'), null, function(res, ids) {
				var arr = ids.split(",");
				for (var i = 0; i < arr.length; i++) {
					$("#list_tr_" + arr[i]).remove();
				}
			}, U.lang('WF.CONFIRM_DEL_RULE'));
		}
	});

	// 列表条数设置
	var $pageNumCtrl = $("#page_num_ctrl"), $pageNumMenu = $("#page_num_menu"), pageNumSelect = new P.PseudoSelect($pageNumCtrl, $pageNumMenu, {
		template: '<i class="o-setup"></i> <span><%=text%></span> <i class="caret"></i>'
	});
	$pageNumCtrl.on("select", function(evt) {
		var url = $pageNumCtrl.attr("data-url") + "&pagesize=" + evt.selected;
		window.location.href = url;
	});
	$('[data-change]').on('change', function() {
		var id = $(this).data('id');
		wfList.deal(Ibos.app.url('workflow/entrust/status'), {flag: +$(this).prop('checked')}, id, function(res, id) {
			var $tr = $('#list_tr_' + id);
			if ($tr.find('[data-change]').prop('checked') == true) {
				$tr.removeClass('state-close');
			} else {
				$tr.addClass("state-close");
			}
		});
	});
});