Ibos.events.add({
	/**
	 * 延期操作
	 * @param {object} elem
	 * @param {mixed} param
	 * @returns {undefined}
	 */
	'delay': function(param, $elem) {
		Ui.dialog({
			title: U.lang('WF.HOW_LONG_IS_THE_DELAY'),
			id: 'd_dialog_delay',
			content: document.getElementById('dialog_delay'),
			ok: function() {
				// 检查日期是否正确 
				var custom = $('#custom_time').val(), time;
				if (custom !== "") {
					var activeTime = new Date(Date.parse(custom.replace(/-/g, "/")));
					var nowTime = new Date();
					if (activeTime < nowTime) {
						Ui.tip(U.lang('WF.DELAY_CUSTOM_TIME_ERROR'), 'danger');
						return false;
					}
					time = custom;
				} else {
					time = $('#dialog_delay').find('input[type="radio"]:checked').val();
				}
				var url = Ibos.app.url('workflow/handle/adddelay');
				$.post(url, {key: param.key, time: time}, function(data) {
					if (data.isSuccess) {
						Ui.tip(U.lang('WF.DELAY_OPT_SUCCESS'), 'success');
						$elem.parents('tr').remove();
						Ui.getDialog('d_dialog_delay').close();
					} else {
						Ui.tip(data.msg ? data.msg : U.lang('OPERATION_FAILED'), 'danger');
					}
				}, 'json');
				return false;
			},
			cancel: true
		});
	},
	/**
	 * 恢复延期
	 * @param {object} $elem
	 * @param {mixed} param
	 * @returns {undefined}
	 */
	'restore': function(param, $elem) {
		Ui.confirm(U.lang('WF.CONFIRM_RESTORE'), function() {
			var url = Ibos.app.url('workflow/handle/restoredelay', {key: param.key});
			$.get(url, function(data) {
				if (data.isSuccess) {
					$elem.parents('tr').remove();
					Ui.tip(U.lang('WF.RESTORE_SUCCESS'), 'success');
				} else {
					Ui.tip(data.msg ? data.msg : U.lang('OPERATION_FAILED'), 'danger');
				}
			}, 'json');
		});
	},
	/**
	 * 回收
	 * @param {type} $elem
	 * @param {type} param
	 * @returns {undefined}
	 */
	'takeback': function(param, $elem) {
		Ui.confirm(U.lang('WF.CONFIRM_CALLBACK'), function() {
			var url = Ibos.app.url('workflow/handle/takeback', {key: param.key});
			$.get(url, function(res) {
				if (res.status == 1) {
					Ui.alert(U.lang('ALREADY_RECEIVED'));
				} else if (res.status == 2) {
					Ui.alert(U.lang('NO_ACCESS_TAKEBACK'));
				} else {
					Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
					$elem.parents('tr').remove();
				}
			}, 'json');
		});
	}
});
