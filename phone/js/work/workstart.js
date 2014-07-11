var WorkStart = (function(){
	// @Todo: 优化内存
	var flowData;
	var init = function(){
		var itemTpl = $.query("#work_start_item_tpl").val(),
			cateTpl = $.query("#work_start_cate_tpl").val();
		
		// @Todo: 这里读取工作流程分类信息
		$.jsonP({
			url: app.appUrl + '/work/new' ,
			success: function(res){
				flowData = res;
				// flowData = {
				// 	common: [
				// 		{ flowid: 1, name: "请假申请" },
				// 		{ flowid: 2, name: "加班登记" },
				// 		{ flowid: 7, name: "项目下单流程" }
				// 	],
				// 	cate: [
				// 		{ 	
				// 			catid: 1,
				// 			name: "人事",
				// 			flowcount: 8,
				// 			flows: [
				// 				{ flowid: 1, name: "请假申请" },
				// 				{ flowid: 2, name: "加班登记" }
				// 			]
				// 		},
				// 		{ 
				// 			catid: 2, 
				// 			name: "行政", 
				// 			flowcount: 9,
				// 			flows: [
				// 				{ flowid: 3, name: "采购申请" },
				// 				{ flowid: 4, name: "外出登记" }
				// 			]
				// 		},
				// 		{ 
				// 			catid: 3, 
				// 			name: "财务", 
				// 			flowcount: 9,
				// 			flows: [
				// 				{ flowid: 5, name: "请款付款申请" },
				// 				{ flowid: 6, name: "发票申请" }
				// 			]
				// 		},
				// 		{ 
				// 			catid: 4, 
				// 			name: "项目", 
				// 			flowcount: 10,
				// 			flows: [
				// 				{ flowid: 7, name: "项目下单流程" }
				// 			]
				// 		}
				// 	]
				// }
				// 常用流程
				var itemList = new List("work_start_item_list", itemTpl, { id: "flowid" });
				itemList.set(flowData.common);
				
				// 按分类
				var cateList = new List("work_start_cate_list", cateTpl, { id: "catid"});
				cateList.set(flowData.cate);
			}
		})
	};

	// 获取分类数据
	var _getCateData = function(catid){
		if(flowData.cate && flowData.cate.length) {
			for(var i = 0; i < flowData.cate.length; i++) {
				if(flowData.cate[i].catid == catid) {
					return flowData.cate[i].flows || [];
				}
			}
		} else {
			return [];
		}
	};

	// 读取分类下的流程列表
	var loadCateList = function(catid, name) {
		var cateData = _getCateData(catid);
		$(document).one("loadpanel", function(){
			var tpl = $.query("#work_flow_item_tpl").val();
			var list = new List("work_flow_list", tpl, { id: "flowid" });

			$.query("#work_flow_list_title").html(name);
			list.set(cateData);
		});
		$.ui.loadContent("view/work/flowcate.html");
	};

	// 进入工作流主办
	// type => ["new", "operate", "sponsor", "view"]
	// 当发起工作流时， 第一个参数为工作流类型的 id
	// 其作情况为该流程 id
	var startFlow = function(key, type) {
		// debugger;
		// 如果是新建， 则需要先获取到runid
		if(type === "new") {
			$.jsonP({
				url: app.appUrl + '/work/add' + "&flowid=" + key,
				success: function(res){
					_start(res.key, "sponsor");
				}
			})
		} else {
			_start(key, type);		
		}
		function _start(key, type) {
			$(document).one("loadpanel", function(){
				// 这里读取工作流数据
				$.jsonP({
					url: app.appUrl + '/work/form' + '&key='+ key,
					success: function(json){
						// 渲染模板
						res = {
							data: {
								runId: 1,
								form: json.model,
								attachs: [
									// {
									// 	aid: 1,
									// 	flowProgress: 1,
									// 	flowType: "请假申请",
									// 	realname: "千千子",
									// 	fileType: "photo",
									// 	fileName: "keroro.png",
									// 	fileTime: "2014-01-01 12:30",
									// 	fileSize: "3.11M"
									// }
								],
								signs: json.feedback,
								progress: json.rpcache,
								feedback: json.allowFeedback, // 是否允许会签
								rollback: json.allowBack, // 是否允许回退
								laststep: 0, // 是否最后一步
							}
						}

						$.query("#work_handle_mainer").html($.template(document.getElementById("work_handle_mainer_tpl").value, res.data));
						$.query("#footer_work_handle").html($.template(document.getElementById("work_handle_footer_tpl").value, res.data));
					}
				})
			});
			$.ui.loadContent("view/work/handle.html");
		}

	};

	return {
		init: init,
		loadCateList: loadCateList,
		startFlow: startFlow
	}
})();

(function(){
	app.evt.add({
		// 滚动定位
		"workHandleScroll": function(domid, elem){
			app.ui.scrollTo(domid, 200, { y: -44 });
			$(elem).addClass("pressed").siblings().removeClass("pressed")
			// @Todo: 考虑滚动监控的实现
		},

		// 工作流回退
		"workRollback": function(param){
			var $form = $.query("#form_work_handle");
			app.ui.prompt("请输入回退理由:", function(val){
				$.ui.showMask();
				$.jsonP({
					url: app.appUrl + '/work/fallback&key=' + $form.get(0).key.value + '&topflag=0',
					success: function(res){
						console.log("回退理由: " + val)
						$.ui.hideMask();
						$.ui.loadContent("view/work/todo.html", 0, 0);
					},
					error: core.error
				})
			})
		},

		// 保存工作流表单
		"workSaveForm": function(param){
			var $form = $.query("#form_work_handle");
			$.ui.showMask();
			$form.attr("action", app.appUrl + '/work/form');
			$form.get(0).saveflag.value = "save";

			$form.get(0).submit();
			// @Todo: 数据有可能超过255字节
			// $.jsonP({
			// 	url: "&callback=?&runid=" + param.flowid + $form.serialize(),
			// 	success: function(res){
					$.ui.hideMask();
					app.ui.tip("保存成功");
			// 	},
			// 	error: core.error
			// })
		},

		// 进入工作转交页
		"toForwardWork": function(param){
			var $form = $.query("#form_work_handle");
			$.ui.showMask();
			$form.attr("action", app.appUrl + '/work/form');
			$form.get(0).saveflag.value = "turn";

			$form.get(0).submit();

			// app.param.set("runId", param.runId);
			// 先保存表单			
			$.jsonP({
				url: app.appUrl + '/work/shownext&key=' + $form.get(0).key.value + '&topflag=0',
				success: function(data){
					// 返回步骤信息
					res = {
						prcsto:data.prcsto,
						steps:data.list,
						run:data.run,
						process:data.process,
						runid:data.runid,
						flowid:data.flowid,
						processid:data.processid,						
						flowprocess:data.flowprocess
					}
					$(document).one("loadpanel", function(evt){
						var $stepItem = $.tmpl($.query("#work_forward_step_tpl").val(), res);
						$.query("#work_forward_step").append($stepItem);
						$.query(".user-selector-list", evt.target).each(function(){
							if(User.get(this.id)) {
								User.get(this.id).destory();
							}
							new User(this.id, null, {
								input: this.getAttribute("data-input")
							});
						})
					});

					$LAB.script("js/userselect.js")
					.wait(function(){
						$.ui.loadContent("view/work/forward.html");
					})
				},
				error: core.error
			})
		},

		// 转交工作
		"workForward": function(param){
			var $form = $.query("#form_work_forward");
				// sponsorIns = User.get("work_sponsor"),
				// operatorIns = User.get("work_operator"),
				// sponsorUid,
				// operatorUids;

			// if(!sponsorIns || !sponsorIns.get().length) {
			// 	app.ui.tip("请选择主办人");
			// 	return false;
			// }
			// sponsorUid = sponsorIns.get()[0].id;
			// if(operatorIns) {
			// 	var  operators = operatorIns.get();
			// 	if(operators.length) {
			// 		operators.forEach(function(o, i){
			// 			operatorUids = operatorUids ? (operatorUids + "," + o.id) : o.id;
			// 		})
			// 	}
			// }

			$.ui.showMask();
			console.log($form.serialize());
			$.jsonP({
				url: app.appUrl + '/work/turnNextPost&' + $form.serialize(),
				success: function(res){
					if(res.isSuccess==true){
						$.ui.hideMask();
						// sponsorIns.destory();
						// operatorIns && operatorIns.destory();
						app.param.remove("flowid");
						app.ui.tip("转交成功");
						$.ui.loadContent("view/work/todo.html", 0, 0);
					}else{
						$.ui.hideMask();
						alert(res.msg);
					}
				},
				error: core.error
			})
		},

		// 取消工作转交
		"cancelWorkForward": function(){
			// var sponsorIns = User.get("work_sponsor"),
			// 	operatorIns = User.get("work_operator");
			// sponsorIns && sponsorIns.destory();
			// operatorIns && operatorIns.destory();
			app.param.remove("runId");
			$.ui.goBack();
		},

		// 添加主办人
		"addWorkSponsor": function(param){
			var userIns = new User(param.id);
			app.openSelector({
				values: userIns.get(),
				maxSelect: 1,
				include: param.include,
				input: param.input,
				onSave: function(evt, data){
					userIns.set(data.values);
					$.ui.goBack();
				}
			});
		},

		// 添加经办人
		"addWorkOperator": function(param){
			var userIns =  new User(param.id);
			app.openSelector({
				values: userIns.get(),
				include: param.include,
				onSave: function(evt, data){
					userIns.set(data.values);
					$.ui.goBack();
				}
			});
		},

		// 查看原表
		"viewSourceForm": function(param){
			window.location.href = "http://www.baidu.com/&flowid=" + param.runId;
		},

		// 显示表单空项
		"showFormNullTerm": function(param){
			$.ui.showMask();
			$.jsonP({
				url: "&callback=?&runid=" + param.runId,
				success: function(res){
					$.query("#work_handle_form_content").append(res);
					$.ui.hideMask();
				},
				error: core.error
			})
		},

		// 会签
		"addWorkSign": function(param){
			app.param.set("signRunId", param.runId);
			$.ui.loadContent("view/work/countersign.html");
		},

		// 编辑会签
		"editWorkSign": function(param, elem){
			var content = $(elem).closest('[data-node="countersignBox"]').find('[data-node="countersignContent"]').html();
			app.param.set("signId", param.signId);
			app.param.set("signContent", content);
			$.ui.loadContent("view/work/countersign.html");
		},

		// 保存会签
		"saveWorkSign": function(){
			// var content = $.query("#countersign_content").val(),
			// 	signId = app.param.get("signId"),
			// 	runId = app.param.get("signRunId"),
			// 	url;
			

			// 编辑会签的情况
			// if(signId) {
			// 	url = "" + "&callback=?&signid=" + signId + "&content=" + content;
			// 	console.log("%c会签id: %c" + signId + " %c会签内容: %c" + content,  "", "color: red",  "", "color: red");
			// 新增会签的情况
			// } else {
			// 	url = "" + "&callback=?&runid=" + runId + "&content=" + content;
			// 	console.log("%c流程id: %c" + runId + " %c会签内容: %c" + content,  "", "color: red",  "", "color: red");
			// }

			// $.ui.showMask();
			
			var $form = $.query("#form_work_handle");
				$.ui.showMask();
				$form.attr("action", app.appUrl + '/work/form');
				$form.get(0).saveflag.value = "save";

				$form.get(0).submit();

			// @Todo: 附件处理

			// $.jsonP({
			// 	url: url,
			// 	success: function(res){
			// 		$.ui.hideMask();
			// 		var res = {
			// 			signId: 1,
			// 			realname: "张小牛",
			// 			avatar: "",
			// 			date: "2014-02-22",
			// 			flowProgress: 2,
			// 			flowType: "加班登记",
			// 			signContent: content,
			// 			attachments: [
			// 				{ aid: 1, name: "悠悠飘落.jpg", size: "1M", uploadTime: "2014-02-24 14:20", type: "photo" },
			// 				{ aid: 2, name: "直至世界的终结.mp3", size: "13M", uploadTime: "2014-02-24 14:20", type: "music" }
			// 			]
			// 		}
			// 		var $item = $.tmpl($.query("#countersign_tpl").val(), res);

			// 		// 编辑会签的情况
			// 		if(signId) {
			// 			$.query("#work_handle_sign_list").find('[data-signid="' + signId + '"]').remove();
			// 			app.param.remove("signId");
			// 		}
			// 		$.query("#work_handle_sign_list").append($item);
			// 		$.ui.goBack();
			// 		app.param.remove("signRunId");
			// 	},
			// 	error: core.error
			// })
		},

		// 取消会签
		"cancelWorkSign": function(){
			app.param.remove("signId");
			app.param.remove("signRunId");
			$.ui.goBack();
		},

		// 删除会签
		"removeWorkSign": function(param, elem){
			app.ui.confirm("确定要删除这条会签吗？", function(){
				var $countersignBox = $(elem).closest('[data-node="countersignBox"]');
				// $.jsonP({
				// 	url: "" + "&callback=?&signid=" + param.signId,
					// success: function(res){
						app.ui.fadeRemove($countersignBox)
					// }
				// })
			})
		},

		// 上传会签图片
		"addWorkSignPic": function(){
			console.log("上传会签图片");
		},

		// 拍照并上传会签图片
		"addWorkSignPht": function(){
			console.log("拍照并上传会签图片");
		},

		// 上传会签文件
		"addWorkSignFile": function(){
			console.log("上传会签文件");
		},

		// 催办提醒
		"addWorkUrge": function(param){
			app.param.set("urgeRunId", param.runId);
			// 获取催办人员，通过jsonP或者数据缓存
			// $.jsonP({
			// 	url: "callback=?&runid=" + param.runId,
			// 	success: function(res){
					var res = [
						{ uid: 1, realname: "张小牛" },
						{ uid: 2, realname: "陈小西" }
					]
					$(document).one("loadpanel", function(){
						core.autoTextarea(document.getElementById("urge_content"));
						var realnames = res.map(function(o, i){ 
							return o.realname;
						}).join("; ");
						var uids = res.map(function(o, i){
							return o.uid;
						}).join(",");
						$.query("#work_urge_realname").html(realnames);
						$.query("#work_urge_uid").val(uids);
					});
					$.ui.loadContent("view/work/urge.html");
			// 	},
			// 	error: core.error
			// })

		},

		// 取消催办
		"cancelWorkUrge": function(){
			app.param.remove("urgeRunId");
			$.ui.goBack();
		},

		// 保存催办
		"saveWorkUrge": function(){
			var uids = $.query("#work_urge_uid").val(),
				content = $.query("#urge_content").val(),
				runId = app.param.get("urgeRunId");
			$.ui.showMask()

			console.log("%c流程id: %c" + runId + " %c用户id: %c" + uids + " %c催办内容: %c" + content,  "", "color: red",  "", "color: red", "", "color: red");
			// jsonP success
			// app.ui.tip("催办工作成功");
			$.ui.hideMask();
			$.ui.goBack();
			app.param.remove("urgeRunId");
		},

		// 上传附件（伪）
		"addWorkAttach": function(){
			var data = {
				aid: 1,
				flowProgress: 2,
				flowType: "部门审批",
				realname: "郭小四",
				fileType: "video",
				fileName: "万万没想到",
				fileTime: "2013-10-10 11:11",
				fileSize: "201M"
			};
			// 插入附件节点
			var $item = $.tmpl($.query("#work_handle_attach_tpl").val(), data);
			var $container = $.query("#work_handle_attach_content");
			$item.insertBefore($container.children().eq(-1));
			// 将附件id赋予指定隐藏域
			core.util.addValue($.query("#work_handle_attach_value"), data.aid);
		},

		// 删除附件（伪）
		"removeWorkAttach": function(param, elem) {
			app.ui.confirm("确定要删除该附件吗？", function(){
				// $.jsonP({
				// 	url: "&callback=?&runid=" + param.runId + "&aid=" + param.aid,
				// 	success: function(res){
						// 移除附件节点
						var $attachBox = $(elem).closest('[data-node="workAttachBox"]');
						app.ui.fadeRemove($attachBox);
						// 将附近id从隐藏域移除
						core.util.addValue($.query("#work_handle_attach_value"), param.aid);
				// 	},
				// 	error: core.error
				// })
			})
		}
	})
})();