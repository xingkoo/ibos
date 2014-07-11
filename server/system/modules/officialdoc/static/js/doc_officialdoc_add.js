/**
 * Officialdoc/officialdoc/add
 * @version $Id$
 */

$(function() {
	// 表单验证
	$.formValidator.initConfig({formID: "officialdoc_form"});
	$("#subject").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY")
	});


	//选人框
	$("#publishScope, #ccScope").userSelect({
		data: Ibos.data.get()
	});


	// 初始化编辑器
	// 操作栏扩展分页按钮
	UEDITOR_CONFIG.mode.simple[0].push('pagebreak')
	var ue = UE.getEditor('doc_add_editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		toolbars: UEDITOR_CONFIG.mode.simple
	});
	ue.ready(function() {
		Ibosapp.editor.initLocal(ue);
		$("#officialdoc_form").submit(function() {
			$.formValidator.pageIsValid() && Ibosapp.editor.clearLocal(ue);
		})
	});


	//默认模板设置
	$("#rc_type").on("change", function() {
		Official.selectTemplate(ue, this.value);
	});


	Ibos.evt.add({
		// 预览
		// @Todo: 预览功能需要加强, 现在的太山寨
		"preview": function() {
			var content = ue.getContent();
			Official.openPostWindow(content)
		}
	})


	// 新手引导
	setTimeout(function() {
		Ibos.app.guide("doc_add", function() {
			Ibos.intro([
				{
					element: "#purview_intro",
					intro: U.lang("DOC.INTRO.DOC_ADD.PURVIEW")
				}, {
					element: "#rc_type",
					intro: U.lang("DOC.INTRO.DOC_ADD.SELECT_TPL")
				}
			], function() {
				Ibos.app.finishGuide('doc_add');
			});
		})
	}, 1000)

	$("#articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
				catid = this.value,
				url = Ibos.app.url("officialdoc/officialdoc/add", {op: "checkIsAllowPublish"});
		$.get(url, {catid: catid, uid: uid}, function(res) {
			$("#article_status label").eq(1).toggle(res.isSuccess)
		}, 'json');
	});

	$("#officialdoc_form").submit(function() {
		if($.data(this, "submiting")) {
			return false;
		}
		if($.formValidator.pageIsValid()) {
			$.data(this, "submiting", true);
		}
	})
	//上传
	Ibos.upload.attach({
		post_params: {module: 'officialdoc'},
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});
})