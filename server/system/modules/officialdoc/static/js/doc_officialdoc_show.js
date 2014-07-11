/**
 * Officialdoc/officialdoc/show
 * @version $Id$
 */

$(function(){
	// window.onbeforeunload = function(){
	// 	signDoc();
	// 	return "您还没签收该公文, 确定离开该页面吗？"
	// }

	// function signDoc(){
	// 	Ui.confirm("您还没签收该公文，确定签收吗？", function(){
	// 		var id = Ibos.app.g("docId"),
	// 			$elem = $("#sign_btn");
	// 		signdoc(id, $elem);
	// 	});

	// }

	//替换百度编辑器换页标识符操作
	var content = $("#art_content").html();
	var replaceCont = content.replace(/_baidu_page_break_tag_/g, "</div><div class='officialdoc-content'>");
	$("#art_content").html(replaceCont);
	//设置页码数
	var $offContents = $("#art_content .officialdoc-content");
	$offContents.each(function(key, val){
		$("<span class='page-num'>" + (key + 1) +" / " + $offContents.length + "</span>").appendTo(this);
	});


	//初始化表情功能
	$('#comment_emotion').ibosEmotion({
		target: $('#commentBox')
	});


	// 加载签收人员数据
	var loadSign = function(id, $elem, callback) {
		if (!$elem.data("loaded")) {
			Official.op.getSign(id, function(res){
				$elem.html(res.signView).data("loaded", 1);
				callback && callback(res);
			});
		}
	};

	// 加载未签收数据
	var loadNoSign = function(id, $elem, callback) {
		if (!$elem.data("loaded")) {
			Official.op.getNoSign(id, function(res){
				$elem.html(res.unsignView).data("loaded", 1);
				callback && callback(res);
			})
		}
	};

	// 加载历史版本数据
	var loadVersion = function(id, $elem, callback) {
		if(!$elem.data("loaded")) {
			Official.op.getVersion(id, function(res){
				$elem.html($.template("tpl_version_table", {
					versions: res
				}))
				.data("loaded", 1);
				callback && callback(res);
			});
		}
	};

	var signdoc = function(id, $elem){
		Official.op.sign(id, function(res){
			if(res.isSuccess) {
				var btnHtml = "<button type='button' disabled='disabled' class='btn btn-large'> <i class='o-art-handel-sign'></i><span class='dib fsl'>您已签收</span></button><span class='dib mls'>签收时间为: 2014年9月16日 12:00</span>"
				$elem.parent().html(btnHtml);
				Ui.tip(U.lang("OPERATION_SUCCESS"), "success");
				window.location.href=document.referrer;
			} else {
				Ui.tip(data.msg, "warning");
			}
		});
	}

	//加载更多签收人数据
	$("#issign").delegate("#load_more_sign" ,"click", function(){
		$("#art_sing_table").css({"height":"auto"});
		$("#load_more_sign").css({"display":"none"});
	});

	//加载更多未签收人数据
	$("#isnosign").delegate("#load_more_no_sign", "click", function(){
		$("#art_no_sing_table").css({"height":"auto"});
		$("#load_more_no_sign").css({"display":"none"});
	});

	// 切换到签收情况
	$("#sign_tab").on("shown", function(){
		loadSign(Ibos.app.g("docId"), $($.attr(this, "href")), function(res){
			var contentHeight = $("#art_sing_table").height();
			var moreHtml = "<div class='fill-hn xac doc-reader-more'><a href='javascript:;' class='link-more' id='load_more_sign'><i class='cbtn o-more'></i><span class='ilsep'>查看更多签收人员</span></a></div>";
			if(contentHeight > 300){
				$("#art_sing_table").css({"height":"300"});
				$("#issign").append(moreHtml);	
			}
			$("#issign .o-art-pc-phone").tooltip();
		});
	});

	//切换到未签收情况
	$("#no_sign_tab").on("shown", function(){
		var docid = Ibos.app.g("docId");
		loadNoSign(docid, $($.attr(this, "href")), function(res){
			var contentHeight = $("#art_no_sing_table").height();
			var moreHtml = "<div class='fill-hn xac doc-reader-more'><a href='javascript:;' class='link-more' id='load_more_no_sign'><i class='cbtn o-more'></i><span class='ilsep'>查看更多未签收人员</span></a></div>";
			if(contentHeight > 300){
				$("#art_no_sing_table").css({"height":"300"});
				$("#isnosign").append(moreHtml);
			}
			//将未签收人员的id发送给后台
			$("#at_once_remind").on("click", function(){
				var $imgs =  $("#art_no_sing_table img"),
					uids = Official.getImgsID($imgs),
					url = Ibos.app.url("officialdoc/officialdoc/index", {op: "remind"}),
					docTitle = Ibos.app.g("docTitle");
				$.post(url, {docid: docid, uids: uids, docTitle: docTitle}, function(res) {
					if(res.isSuccess){
						$("#at_once_remind").html("已提醒").css({"color":"#82939e"});
						Ui.tip(U.lang("OPERATION_SUCCESS"), "success");
					}
				});
			});
		});
	});

	// 切换到历史版本
	$("#version_tab").on("shown", function(){
		loadVersion(Ibos.app.g("docId"), $($.attr(this, "href")), function(res){
			var liLength = $(".version-list").children("li").length,
				emptyInfo = "<div class='empty-info'></div>";
			if(!liLength){
				$("#version").append(emptyInfo);
			}
		});
	});

	// 禁用评论时，默认显示阅读人员
	if(!Ibos.app.g("commentEnable") || !Ibos.app.g("commentStatus")) {
		$("#sign_tab").tab("show");
	}

	//点击关闭当前公文时，提示签收公文
	$("#art_close").on("click.artClose", function(){
		Ui.confirm(L.DOC.HAS_NOT_SIGN_DOC, function(){
			var id = Ibos.app.g("docId"),
				$elem = $("#sign_btn");
			signdoc(id, $elem);
		},function(){
			window.location.href=document.referrer;
		});
	});

	//点击审核通过
	$("#approval_btn").on("click", function(){
        Ui.confirm(L.DOC.CAN_NOT_REVOKE_OPERATE, function(){
            var docids = Ibos.app.g("docId");
            $.post(Ibos.app.url("officialdoc/officialdoc/edit", {op: 'verify'}),{ docids: docids },function(res){
                if(res.isSuccess){
                    Ui.tip(L.DOC.APPROVAL_SUCCESS);
                    window.location.href=document.referrer;
                } else {
					Ui.tip(res.info, 'warning');
				}
            });
        });
    });

	//点击回退，填写回退理由
    $("#doc_rollback").on("click", function(){
    	Ui.dialog({
    		id: "doc_rollback",
    		title: L.DOC.DOC_ROLLBACK,
    		content: document.getElementById("rollback_reason"),
    		cancel: true,
    		ok: function(){
    			var docids = Ibos.app.g('docId');
				var	reason = $("#rollback_textarea").val();
				
				$.post(Ibos.app.url("officialdoc/officialdoc/edit", {op: 'back'}),{ docids: docids, reason: reason },function(res){
					if(res.isSuccess){
						Ui.tip(L.OPERATION_SUCCESS);
						window.location.href=document.referrer;
					}else{
						Ui.tip(L.DOC.REASON_IS_EMPTY, "danger");
					}
				}, 'json');
    		}
    	});
    });

	Ibos.evt.add({
		// 签收公文
		"signDoc": function(param, elem) {
			Official.op.sign(Ibos.app.g("docId"), function(res){
				if(res.isSuccess) {
					var btnHtml = "<button type='button' disabled='disabled' class='btn btn-large'> <i class='o-art-handel-sign'></i><span class='dib fsl'>您已签收</span></button><span class='dib mls'>签收时间为: " + res.signtime + "</span>",
						$artClose = $("#art_close");
					$(elem).parent().html(btnHtml);
					$artClose.off("click.artClose");
					$artClose.on("click", function(){
						window.location.href=document.referrer;
					});
					Ui.tip(U.lang("OPERATION_SUCCESS"), "success");
				} else {
					Ui.tip(res.msg, "warning");
				}
			});
		},

		// 下次签收
		"signNextTime": function(param, elem){
			var $btn = $(elem).parent().find('.btn');
			$btn.removeClass('btn-danger').removeAttr('data-action').attr({disabled:"disabled"});
			$btn.children('.fsl').html(L.DOC.APPROVAL_NEXT_TIME);
			$btn.children('i').removeClass('o-art-immediately-sign').addClass('o-art-next-sign');
			$(elem).css({"display":"none"});
		},

		// 转发到邮件
		"forwardDocByMail": function(){
			window.location = Ibos.app.url("email/content/add", {
				"op": "forwardDoc",
				"relatedid": Ibos.app.g("docId")
			});
		},
	});
});