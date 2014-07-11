/**
 * Officialdoc/officialdoc/index
 */

 $(document).ready(function() {

 	// 选中一条或多条公文时，出现操作菜单
	$(document).on("change", 'input[type="checkbox"][name="officialdoc[]"]', function(){
		var $opBtn = $('#doc_more'),
			hasSelected = !!U.getChecked('officialdoc[]').length;
    	$opBtn.toggle(hasSelected);
    	setTimeout(function(){
    		$opBtn.toggleClass("open", hasSelected)
    	}, 0)
	});

	//高级搜索
    $("#mn_search").search(null, function(){
        Ui.dialog({
            id: "d_advance_search",
            title: U.lang("ADVANCED_SETTING"),
            content: document.getElementById("mn_search_advance"),
            cancel: true,
            init: function(){
                var form = this.DOM.content.find("form")[0];
                form && form.reset();
                // 初始化日期选择
                $("#date_start").datepicker({ target: $("#date_end") });
            },
            ok: function(){
                this.DOM.content.find("form").submit();
            },
        })
    });


    Ibos.evt.add({
    	// 移动公文
    	"moveDoc": function(){
    		Ui.dialog({
    			id: "d_doc_move",
    			title: U.lang("DOC.MOVETO"),
    			content: Dom.byId('dialog_doc_move'),
    			cancel: true,
    			ok: function(){
    				var catid = $('#articleCategory').val(),
    					docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table"));

    				$.post(Ibos.app.url('officialdoc/officialdoc/edit', { op: 'move' }), {'docids':docids,'catid':catid},function(data){
    					if(data.isSuccess === true){
    						Ui.tip(U.lang("CM.MOVE_SUCCEED"))
    						window.location.reload();
    					}else{
    						Ui.tip(U.lang("CM.MOVE_FAILED", 'danger'))
    					}
    				});
    			}
    		});
    	},

    	// 高亮公文
    	"highlightDoc": function(){
    		Ui.dialog({
    			id: "d_art_highlight",
    			title: U.lang("ART.HIGHLIGHT"),
				content: Dom.byId('dialog_art_highlight'),
				cancel: true,
				init: function(){
					// highlightForm
					var hf = this.DOM.content.find("form")[0], 
						$sEditor = $("#simple_editor");

					// 防止重复初始化
					if(!$sEditor.data('simple-editor')){
						//初始化简易编辑器
						var se = new P.SimpleEditor($('#simple_editor'), {
							onSetColor: function(hex){
								hf.highlight_color.value = hex;
							},
							onSetBold: function(status){
								// 转换为数字类型
								hf.highlight_bold.value = +status;
							},
							onSetItalic: function(status){
								hf.highlight_italic.value = +status;
							},
							onSetUnderline: function(status){
								hf.highlight_underline.value = +status;
							}
						});
						$sEditor.data('simple-editor', se);
					}

                    $("#date_time_highlight").datepicker();
				},
				ok: function(){
                    var hf = this.DOM.content.find("form")[0],
                    	hlData = {
                    		docids: U.getCheckedValue("officialdoc[]", $("#officialdoc_table")),
                    		highlightEndTime: hf.highlightEndTime.value,
                    		color: hf.highlight_color.value,
                    		bold: hf.highlight_bold.value,
                    		italic: hf.highlight_italic.value,
                    		underline: hf.highlight_underline.value
                    	}

					$.post(Ibos.app.url('officialdoc/officialdoc/edit', { op: 'highLight' }), hlData, function(data){
						if(data.isSuccess === true){
							Ui.tip(data.info);
							window.location.reload();
						}
					});
				}
    		})
    	},

    	// 置顶公文
    	"topDoc": function(){
    		Ui.dialog({
    			id: "d_art_top",
    			title: U.lang('ART.SET_TOP'),
    			content: Dom.byId('dialog_art_top'),
    			cancel: true,
                init: function(){
                      $("#date_time_totop").datepicker();
                },
    			ok: function(){
    				// topform
    				var tf = this.DOM.content.find("form")[0];

    				$.post(Ibos.app.url('officialdoc/officialdoc/edit', { op: 'top' }), { 
    					'docids': U.getCheckedValue("officialdoc[]",  $("#officialdoc_table")),
    					'topEndTime': tf.topEndTime.value
    				}, function(data){
    					if(data.isSuccess===true){
    						Ui.tip(data.info);
    						window.location.reload();
    					}
    				});
    			}
    		})
    	},

    	// 删除一条公文
    	"removeDoc": function(param, elem) {
    		Official.op.removeDocs(param.id, function(res) {
    			if( res.isSuccess === true ){
    				Ui.tip(res.info);
    				$(elem).closest("tr").remove();
    			}
    		})
    	},

    	// 删除多条公文
    	"removeDocs": function() {
    		var docids = U.getCheckedValue("officialdoc[]");
    		Official.op.removeDocs(docids, function(res){
    			if( res.isSuccess === true ){
					Ui.tip( res.info);
    				$.each(docids.split(","), function(index, docid){
    					$("[data-node-type='docRow'][data-id='" + docid + "']").remove();
    				})
    			}
    		});
    	},

    	// 审核公文
    	"verifyDoc": function(){
    		var docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table"));
    		if(docids.length > 0){
    		    $.post(Ibos.app.url("officialdoc/officialdoc/edit", { op: "verify" }),{ docids: docids },function(data){
    		        if( data.isSuccess === true ){
    		            Ui.tip( data.info);
    		            window.location.reload();
    		        }else{
						Ui.tip( data.info , 'warning');
					}
    		    });
    		}else{
    		    Ui.tip( U.lang("SELECT_AT_LEAST_ONE_ITEM") , 'warning');
    		}
    	},
		
		// 回退公文
    	"backDocs": function(){
    		var docids = U.getCheckedValue("officialdoc[]", $("#officialdoc_table"));
    		if(docids.length > 0){
				Ui.dialog({
					id: "doc_rollback",
					title: L.DOC.DOC_ROLLBACK,
					content: document.getElementById("rollback_reason"),
					cancel: true,
					ok: function(){
						var reason = $("#rollback_textarea").val();
						
						$.post(Ibos.app.url('officialdoc/officialdoc/edit', { op: 'back' }), { docids: docids, reason: reason },function(res){
							if(res.isSuccess===true){
								Ui.tip(res.info);
								window.location.reload();
							}else{
								Ui.tip( res.info , 'warning');
							}
						});
					}
				});
    		}else{
    			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning')
    		}
    	}
		
    })
 });