/**
 * Article/default/index&op=show
 * @version $Id$
 */

$(function(){
	//初始化表情功能
	$('#comment_emotion').ibosEmotion({
		target: $('#commentBox')
	});

	// 如果图片类型的新闻，需要加载 Gallery 组件
	if(Ibos.app.g("articleType") === 1) {
		var cssUrl = Ibos.app.getStaticUrl("/js/lib/ADGallery/jquery.ad-gallery.css?" + Ibos.app.g("VERHASH")),
			jsUrl = Ibos.app.getStaticUrl("/js/lib/ADGallery/jquery.ad-gallery.js?" + Ibos.app.g("VERHASH"));

		U.loadFile(document, {
			id: "ad_gallery",
			tag: "link",
			rel: "stylesheet",
			href: cssUrl
		}, function(){
			$.getScript(jsUrl, function(){
				$('#gallery').adGallery({ loader_image: Ibos.app.getStaticUrl("/image/loading_mini.gif") });
			});
		});

	}

	// 加载阅读情况数据
	var loadReader = function(id, $elem, callback) {
		// 避免重复加载
		if (!$elem.attr('data-loaded')) {
			Article.op.getArticleReaders(id, function(res){
				if(res) {
					$elem.html($.template("tpl_reader_table", {
						readerData: res
					}))
					.attr('data-loaded', '1');
					callback && callback(res);
				}
			});
		}
	};

	// 展开所有阅读人员
	$(document).on('click', '.reader-all', function() {
		$(this).hide().parent().prev().html($.attr(this, "data-fullList"));
	});

	// 加载查阅人员情况
	$("#isread_tab").on("shown", function(){
		var articleid = $('#articleid').val(),
			$target = $($.attr(this, "href"));
		loadReader(articleid, $target, function(res){
			var readerTabHeight = $("#art_reader_table").height();
				moreHtml = "<div class='art-reader-more fill-hn xac'><a href='javascript:;' class='link-more' id='load_more_reader'><i class='cbtn o-more'></i><span class='ilsep'>查看更多查阅人员</span></a></div>";
			if(readerTabHeight > 300){
				$("#art_reader_table").css({"height":"300"});
				$("#isread").append(moreHtml);	
			}
		});
	});

	$("#isread").delegate("#load_more_reader" ,"click", function(){
		$("#art_reader_table").css({"height":"auto"});
		$("#load_more_reader").parent().css({"display":"none"});
	});

	// 禁用评论或新闻不允许评论时，直接显示查阅人员
	if(!Ibos.app.g("commentEnable") || !Ibos.app.g("commentStatus")) {
		$("#isread_tab").tab("show");
	}

	//点击审核通过
	$("#approval_btn").on("click", function(){
        Ui.confirm(L.ART.CAN_NOT_REVOKE_OPERATE, function(){
            var articleid = Ibos.app.g("articleId");
            $.post(Ibos.app.url("article/default/edit", {op: 'verify'}),{ articleids: articleid },function(res){
                if(res.isSuccess){
                    Ui.tip(L.ART.APPROVAL_SUCCESS);
                    window.location.href=document.referrer;
                } else {
					Ui.tip(res.msg, 'warning');
				}
            });
        });
    });

	//点击回退，填写回退理由
    $("#art_rollback").on("click", function(){
    	Ui.dialog({
    		id: "art_rollback",
    		title: L.ART.DOC_ROLLBACK,
    		content: document.getElementById("rollback_reason"),
    		cancel: true,
    		ok: function(){
				var articleid = Ibos.app.g('articleId'),
					reason = $("#rollback_textarea").val();
				$.post(Ibos.app.url("article/default/edit", {op: 'back'}),{ articleids: articleid, reason: reason },function(res){
					if(res.isSuccess){
						Ui.tip(L.OPERATION_SUCCESS);
						window.location.href=document.referrer;
					}else{
						Ui.tip(L.ART.REASON_IS_EMPTY, "danger");
					}
				}, 'json');
    		}
    	});
    });

	Ibos.evt.add({
		//转发到邮件
		"forwardArticleByMail": function(){
			window.location = Ibos.app.url("email/content/add", {
				"op": "forwardNew",
				"relatedid": Ibos.app.g("articleId")
			});
		},

		// 打印
		"printArticle": function(){
			window.print();
		}
	})
});
