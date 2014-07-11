var FormDesigner = {
	// @Todo 提取公用Select类
	// 历史版本下拉选框
	formEditionSelect: {
		$select: $("#edition_select"),
		optionTpl: '<option value="<%=value%>""><%=text%></option>',
		getOptionsTpl: function(data) {
			var tpl = "", len = data && data.length;
			if (len) {
				for (var i = 0; i < len; i++) {
					tpl += $.template(this.optionTpl, data[i]);
				}
			}
			return tpl;
		},
		updateOptions: function(data) {
			this.$select.html(this.getOptionsTpl(data));
		},
		removeOption: function(value) {
			this.$select.find("option").each(function() {
				if (this.value === "" + value) {
					$(this).remove();
					return false;
				}
			});
		},
		getValue: function() {
			return this.$select.val();
		}
	},
	// 历史版本
	formEdition: {
		$container: $("#dialog_edition"),
		$select: $("#edition_select"),
		_getEdition: function() {
			return this.$select.val();
		},
		_checkEdition: function() {
			var edition = this._getEdition();
			if (!edition || edition === "0") {
				this.$select.blink();
				return false;
			}
			return true;
		},
		init: function() {
			var me = this;
			if (!this._isInit) {
				this.$container.on("click", "[data-edition]", function() {
					var op = $.attr(this, "data-edition"), value = FormDesigner.formEditionSelect.getValue();
					me[op].call(me, value);
				});
				this.isInit = true;
			}
		},
		/**
		 * 历史版本预览
		 * @param {type} id
		 * @returns {undefined}
		 */
		preview: function(id) {
			if (this._checkEdition()) {
				window.open(Ibos.app.url('workflow/formtype/preview', {verid: id}),
				"menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1,top=0, left=0, width=" + screen.availWidth + ", height=" + screen.availHeight);
			}
		},
		/*
		 * 恢复至应用版本
		 * @param {integer} id
		 * @returns {undefined}*
		 */
		restore: function(id) {
			if (this._checkEdition()) {
				Ui.confirm(U.lang("WF.EDITION_RESTORE_CONFIRM"), function() {
					$.get(Ibos.app.url('workflow/formversion/restore'), {verid: id}, function(res) {
						if (res.isSuccess) {
							window.location.reload();
						} else {
							Ui.alert(res.msg);
						}
					});
				});
			}
		},
		/**
		 * 删除历史版本
		 * @param {type} id
		 * @returns {undefined}
		 */
		remove: function(id) {
			if (this._checkEdition()) {
				Ui.confirm(U.lang("WF.EDITION_DELETE_CONFIRM"), function() {
					$.get(Ibos.app.url('workflow/formversion/del'), {verid: id}, function(res) {
						if (res.isSuccess) {
							Ui.tip(U.lang('DELETE_SUCCESS'), 'success');
							FormDesigner.formEditionSelect.removeOption(id);
						} else {
							Ui.tip(U.lang('DELETE_FAILED'), 'danger');
						}
					});
				});
			}
		}
	}

}
$(function(){
	// 右侧栏定位
	$('#fd_sidebar').affix({ offset: { top: 60 } });
})