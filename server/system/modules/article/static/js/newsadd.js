$(function(){
	$.formValidator.initConfig({ formID:"article_form", errorFocus: true});
	$("#subject").formValidator({ onFocus: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY") })
	.regexValidator({
		regExp:"notempty",
		dataType:"enum",
		onError: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY")
	});

	// tab 事件
	$("#content_type [data-toggle=tab]").on("show", function(evt){
		$("#content_type_value").val($.attr(evt.target, "data-value"));
	})
	


	//上传
	var uploadSettings = {
		upload_url: Ibos.app.url('main/attach/upload', { uid: Ibos.app.g('uid'), 'hash': Ibos.app.g('uploadHash') }),
		file_post_name: "Filedata",
		file_size_limit: Ibos.app.g('uploadMaxSize'),
		file_types: Ibos.app.g('uploadTypes'),
		file_types_description: Ibos.app.g('uploadTypesDesc'),
		post_params: {module: 'article'},
	}

	var attachUpload = Ibos.upload.attach($.extend({}, uploadSettings, {
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	}));

	// 图片上传配置
	var picUpload = Ibos.upload.attach($.extend({}, uploadSettings, {
		file_types: "*.gif;*.jpg;*.jpeg;*.png;",
		button_placeholder_id: "pic_upload",
		custom_settings: {
			containerId: "pic_list",
			inputId: "picids",
			success: function(file, data, item){
				Article.pic.initPicItem(item, data);
			}
		}
	}));

	var $picRemove = $("#pic_remove"),
		$picMoveUp = $("#pic_moveup"),
		$picMoveDown = $("#pic_movedown"),
		picSelected = [];

	Ui.setLinkage("pic", null, function($checked){
		var count = $checked.length,
			enableRemove = count >= 1,
			enableMove = count === 1;

		// 根据选中条目数决定，删除按钮和移动按钮的显隐
		$picRemove.toggle(enableRemove);
		$picMoveUp.toggle(enableMove);
		$picMoveDown.toggle(enableMove);

		picSelected = $checked.map(function(){
			return this.value;
		}).get();
	});
	// 删除选中图片项
	$picRemove.on("click", function(){
		Article.pic.removeSelect(picSelected);
		$picRemove.hide();
	});
	// 上移选中图片项
	$picMoveUp.on("click", function(){
		Article.pic.moveUp(picSelected[0]);
	});
	// 下移选中图片项
	$picMoveDown.on("click", function(){
		Article.pic.moveDown(picSelected[0]);
	});



    $("#publishScope").userSelect({
        data: Ibos.data.get()
    });

    $('#voteStatus').on('change',function(){
        $('#vote').toggle($.prop(this, 'checked'));
    });
    
    //状态值改变
//    $('#article_status').on('click',function(){
//        $('#status').val($(this).find('.active input').val());
//    });

	// @Todo:
	// 预览功能太弱，需要改进
	// 预览多个页面用到，需要提取到公共函数
	var openPostWindow = function (url, data){
        var tempForm = document.createElement("form");  
        tempForm.id="tempForm1";  
        tempForm.method="post";  
        tempForm.action=url; 
        tempForm.target = "_blank"; 

    	var input;
    	for(var i in data) {
    		input = document.createElement("input");
    		input.type = "hidden";
    		input.name = i;
    		input.value = data[i];
    		tempForm.appendChild(input);
    	}

        //监听事件的方法        打开页面window.open(name);
        // tempForm.addEventListener("onsubmit",function(){  window.open(url, "_blank"); });
        tempForm.addEventListener("onsubmit", function() {
			window.open(url);
		});
        document.body.appendChild(tempForm);  


        tempForm.submit();
        document.body.removeChild(tempForm);
    }

    // 预览
	$('#prewiew_submit').on('click',function(){
	    var type = parseInt($('#content_type_value').val(), 10),
	    	TYPE_ARTICLE = 0, TYPE_PIC = 1, TYPE_URL = 2;

	    // 文章
	    if( type === TYPE_ARTICLE ){
	        var url = Ibos.app.url("article/default/index", {"op": "preview"}),
	        	setting = {
		            subject: $('#subject').val(),
		            content: UE.getEditor('article_add_editor').getContent()
		        };
	        openPostWindow(url, setting);
        //图片
	    // 超链接
	    }else if( type === TYPE_URL ){
	        var url = $('#article_link_url').val(),
	        	results = U.reg.url.exec(url);

	        if(results){
	        	// 没有协议前缀，自动补全
	        	window.open(results[1] ? url : "http://" + url);
	        } else {
	        	Ui.tip(U.lang("RULE.URL_INVALID_FORMAT"), "warning")
	        }
	    }
	});

	// 新手引导
	setTimeout(function(){
		Ibos.app.guide("art_def_add", function(){
			Ibos.intro([
				{
					element: "#article_status",
					intro: U.lang("INTRO.NEWS_DEF_ADD.STATUS"),
					position: "top"
				}
			], function(){
				Ibos.app.finishGuide('art_def_add');
			});
		})
	}, 1000)
	
	$("#add_articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
				catid = this.value,
				url = Ibos.app.url("article/default/add", {op: "checkIsAllowPublish"});
		$.get(url, {catid: catid, uid: uid}, function(res) {
			$("#article_status label").eq(1).toggle(res.isSuccess)
		}, 'json');
	});

	// 验证表单
	var valiForm = function(){
		if($.formValidator.pageIsValid()){
			var ue = UE.getEditor("article_add_editor");
			var content = ue.getContentTxt()
			var type = $("#content_type_value").val();
			if( type == 0 && $.trim(content) === ""){
				Ui.tip("@ART.CONTENT_CANNOT_BE_EMPTY", "warning");
				ue.focus();
				return false;
			} else if(type == 1 && $("#picids").val() == "") {
				Ui.tip("@ART.PIC_CONTENT_CANNOT_BE_EMPTY", "warning")
				return false;
			} else if(type == 2 && $.trim($("#article_link_url").val()) == "" ){
				Ui.tip("@ART.LINK_CANNOT_BE_EMPTY", "warning")
				return false
			} else {
				return true;
			}
		}
	}

	// 编辑器
    var ue = UE.getEditor('article_add_editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		autoHeightEnabled:true,
		toolbars: UEDITOR_CONFIG.mode.simple
	});

    if(Ibos.app.g("editorCache")){
	    ue.ready(function(){
	    	Ibosapp.editor.initLocal(ue);
	    });
    }
	$("#article_form").submit(function(){
		if($.data(this, "submiting")) {
			return false;
		}

		if(!valiForm()){
			return false;
		}
    	if(Ibos.app.g("editorCache")){
    		Ibosapp.editor.clearLocal(ue);
    	}

    	$.data(this, "submiting", true);
	})

});