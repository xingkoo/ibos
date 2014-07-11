Ibos.events.add({
	/**
	 * 恢复工作
	 * @param {object} $elem
	 * @param {mixed} param
	 * @returns {undefined}
	 */
	'restoreRun': function(param, $elem) {
		wfList.batchOpt(Ibos.app.url('workflow/recycle/restore'), null, function(res, ids) {
			wfList.removeRows(ids);
		}, U.lang('WF.CONFIRM_RESTORE_RUN'));
	},
	/**
	 * 销毁工作
	 * @param {object} $elem
	 * @param {mixed} param
	 * @returns {undefined}
	 */
	'destroy': function(param, $elem) {
		wfList.batchOpt(Ibos.app.url('workflow/recycle/destroy'), null, function(res, ids) {
			wfList.removeRows(ids);
		}, U.lang('WF.CONFIRM_DESTROY_RUN'));
	}
});