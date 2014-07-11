(function() {
	// 表单验证
	$.formValidator.initConfig({formID: "type_form"});
	$("#flow_name").formValidator().regexValidator({
		dataType: "enum",
		regExp: "notempty",
		onError: U.lang("WF.FLOW_NAME_CANNOT_BE_EMPTY")
	});
	$('#form_name').formValidator().functionValidator({fun: checkFormName});
	function checkFormName(val) {
		if ($('#form_select').val() === '' && val === '') {
			return U.lang("WF.ENTER_FORM_NAME");
		}
		return true;
	}

	$('#flow_catid').formValidator({
		onShow: U.lang("WF.SELECT_FLOW_TYPE"),
		onFocus: U.lang("WF.SELECT_FLOW_TYPE")
	}).inputValidator({
            min: 0, 
            onError: U.lang("WF.SELECT_FLOW_TYPE")
        });

	// 表单验证end

	// 表单选择器
	var formData = Ibos.app.g("formData") || [];
	$("#form_select").ibosSelect({
		data: formData,
		width: '100%',
		multiple: false
	});

	$('#type_setting_department').userSelect({
		data: Ibos.data.get("department"),
		type: "department"
	});

	$("#ref_ctrl").click(function() {
		$("#ref_detail").slideToggle(200);
	});
	$("#ref_eps").click(function() {
		$("#ref_eps_ins").slideToggle(200);
	});
	var $formSelect = $("#form_select"), $formName = $("#form_name"), $formPreviewBtn = $("#form_preview_btn"), formId = $formSelect.val();
	$formSelect.on("change", function() {
		var toNew = this.value === "" ? true : false;
		// 新建表单
		$formName.toggle(toNew);
		$formPreviewBtn.toggle(!toNew);
		formId = this.value;
	});

	// 使用状态说明
	$('#using_state').popover({html: true, title: U.lang("WF.USING_STATE_TITLE"), content: U.lang("WF.USING_STATE_DESC"), trigger: 'hover'});
	// 委托类型说明
	$('#delegate_type').popover({html: true, title: U.lang("WF.DELEGATE_TYPE_TITLE"), content: U.lang('WF.DELEGATE_TYPE_DESC'), trigger: 'hover'});

	// 预览表单
	$formPreviewBtn.on("click", function() {
		if (formId) {
			Ui.openFullWindow(Ibos.app.url('workflow/formtype/preview', {formid: formId}), "preview");
		}
	});

	// 手工修改文号设置的交互
	$('#auto_edit').on('change', function() {
		if (this.value === '2' || this.value === '3' || this.value === '4') {
			$('#is_force').show();
		} else {
			$('#is_force').hide();
			$('#force_pre_set').label('uncheck');
		}
	});
	// 流程类型交互：显示预设步骤
	$('#flow_type').find('input[type=radio]').on('change', function() {
		$('#free_set').toggle(this.value == 2);
	});
	Ibos.checkFormChange($(document.body), U.lang("WF.TYPE_FORM_CHANGE_TIP"));
})();