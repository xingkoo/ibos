/**
 * Organization/user/edit
 */
$(function() {
	$.formValidator.initConfig({ formID:"user_form", errorFocus:true });
	// 真实姓名
	$("#realname").formValidator()
	.regexValidator({
		regExp:"notempty",
		dataType:"enum",
		onError: U.lang("RULE.REALNAME_CANNOT_BE_EMPTY")
	});

	// 密码
	var pwdSettings = Ibos.app.g("password"),
		pwdErrorTip = U.lang("V.PASSWORD_LENGTH_RULE", { 
			min: pwdSettings.minLength, 
			max: pwdSettings.maxLength
		})
	$("#password")
	.formValidator({ 
		onFocus: pwdErrorTip, 
		empty: true
	})
	.inputValidator({
		min: pwdSettings.minLength,
		max: pwdSettings.maxLength,
		onError: pwdErrorTip
	})
	.regexValidator({
		regExp: pwdSettings.regex,
		dataType:"string",
		onError: U.lang("RULE.CONTAIN_NUM_AND_LETTER")
	});

	$("#mobile").formValidator()
	.regexValidator({
		regExp:"mobile",
		dataType:"enum",
		onError: U.lang("RULE.MOBILE_INVALID_FORMAT")
	});

	$("#email").formValidator()
	.regexValidator({
		regExp:"email",
		dataType:"enum",
		onError: U.lang("RULE.EMAIL_INVALID_FORMAT")
	});

	// 用户 部门 岗位选择 		
	var userData = Ibos.data.get("user"),
		depData = Ibos.data.get("department"),
		posData = Ibos.data.get("position");

	$("#user_supervisor").userSelect({
		type: "user",
		maximumSelectionSize: "1",
		data: userData
	});
	$("#user_department").userSelect({
		type: "department",
		maximumSelectionSize: "1",
		data: depData
	});
	$('#auxiliary_department').userSelect({
		type: "department",
		data: depData
	});
	$("#user_position").userSelect({
		type: "position",
		maximumSelectionSize: "1",
		data: posData
	});
	$('#auxiliary_position').userSelect({
		type: "position",
		data: posData
	});
	$('.display_auxiliary').on('click', function() {
		var target = $(this).data('target');
		$(target).show();
		$(this).hide();
	});

	var settings = {
		data: {
			simpleData: {
				enable: true
			}
		},
		view: {
			showLine: false,
			selectedMulti: false
		}
	};
	var $tree = $("#utree");
	$tree.waiting(null, "normal");
	$.get(Ibos.app.url("organization/user/index", {
		"op": "tree"
	}), function(data) {
		$.fn.zTree.init($tree, settings, data);
		$tree.waiting(false);
	}, 'json');

	// Debug::表单控件提交检查
	$('#user_form').on('submit', function() {
		if ($.trim(this.realname.value) === '') {
			$(this.realname).blink().focus();
			return false;
		}
		if ($.trim(this.mobile.value) === '') {
			$(this.mobile).blink().focus();
			return false;
		}
		if ($.trim(this.email.value) === '') {
			$(this.email).blink().focus();
			return false;
		}
		return true;
	});
});