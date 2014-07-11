/**
 * Officialdoc/officialdoc/edit
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
	var ue = UE.getEditor('editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		toolbars: UEDITOR_CONFIG.mode.simple
	});


	//默认模板设置
	$("#rc_type").on("change", function() {
		Official.selectTemplate(ue, this.value);
	});

	//修改内容后，点击提交时，选择修改理由
	$("#officialdoc_form").submit(function() {
		var status = $(this).attr("data-status");
		if(status == undefined){
			status = false;
		}
		if(!status){
			Ui.dialog({
				id: "alter_reason",
				title: false,
				content: document.getElementById("alter_reason"),
				cancel: true,
				ok: function(){
					$("#officialdoc_form").attr("data-status","true");
					$("input[name='reason']").val($("#reason").val());
					$("#officialdoc_form").submit();
				}
			});
		}
		return status;
	});

	Ibos.evt.add({
		// 预览
		// @Todo: 预览功能需要加强, 现在的太山寨
		"preview": function() {
			var content = ue.getContent();
			Official.openPostWindow(content)
		}
	})

	$("#articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
				catid = this.value,
				url = Ibos.app.url("officialdoc/officialdoc/add", {op: "checkIsAllowPublish"});
		$.get(url, {catid: catid, uid: uid}, function(res) {
			$("#article_status label").eq(1).toggle(res.isSuccess).end().eq(+res.isSuccess).trigger("click");
		}, 'json');
	});
	
	//上传
	Ibos.upload.attach({
		post_params: {module: 'officialdoc'},
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});

})
