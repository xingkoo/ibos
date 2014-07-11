$(function(){
	/* 表单验证 */
	$.formValidator.initConfig({formID: "email_form", onError: Ibosapp.formValidate.pageError});
	$("#toids").formValidator({ 
		relativeID: "toids_row",
		onFocus: U.lang("EM.SELECT_RECEIVER")
	})
	.functionValidator({
		fun: function(txt, elem){
			// 内部收件人及外部收件人只要有一者即可允许发送
			if($.trim(elem.value) === "" && $.trim(document.getElementById("to_web_email").value) === "") {
				return false;
			}
			return true;
		},
	 	onError: U.lang("EM.SELECT_RECEIVER")
	})
	.on("change", function(){ $(this).trigger("blur") })

	$("#mal_title").formValidator()
	.regexValidator({
		dataType: "enum",
		regExp: "notempty",
		onError: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY")
	});


	/* 用户选择框初始化*/
	$("#toids, #copytoids, #secrettoids").userSelect({
		type: "user",
		data: Ibos.data.get('user')
	});


	/* 附件上传 */
	var attachUpload = Ibos.upload.attach({
		post_params: {module: 'email'},
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	})


	/* 邮件标题颜色 */
	var MAL_LEVEL_COLOR = {
		NORMAL: '',
		URGENCY: 'xcr',
		IMPORTANT: 'xcgn'
	},
	MAL_LEVEL_MAP = {
		'0': MAL_LEVEL_COLOR.NORMAL,
		'1': MAL_LEVEL_COLOR.IMPORTANT,
		'2': MAL_LEVEL_COLOR.URGENCY
	},
	$levelCtrl = $("#mal_level"), $levelMenu = $levelCtrl.next();

	$levelCtrl.on("select", function(evt, data) {
		$("#mal_title").attr('class', MAL_LEVEL_MAP[evt.selected]);
		$("#mal_level_val").val(evt.selected);
	});

	new P.PseudoSelect($levelCtrl, $levelMenu, {
		template: '<span><%=text%></span> <i class="caret"></i>'
	});


	/* 离开提示 */
	Ibos.checkFormChange($(document.body), U.lang("EM.MAIL_UNSAVE_WARNING"), "editor");


	// 验证是否有外部收件箱
	$("#email_form").on("submit", function() {
		if($.data(this, "submiting")) {
			return false;
		}

		if ($("#to_web_email").val() != "" && $("#webid").val() == "") {
			Ui.tip("@EM.EMPTY_FROMWEBID_TIP", 'warning');
			return false;
		}

		if($.formValidator.pageIsValid()) {
			$.data(this, "submiting", true);				
		}
	});


});
