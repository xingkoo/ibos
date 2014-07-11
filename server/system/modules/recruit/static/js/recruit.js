/**
 * recruit.js
 * 招聘管理模块JS
 * IBOS
 * @author		inaki
 * @version		$Id: recruit.js 3256 2014-04-24 03:23:52Z gzzcs $
 * @modified	2013-05-16 
 */


//
U.serializedToObject = function(array) {
	var data = {};
	for (var i = 0; i < array.length; i++) {
		data[array[i].name] = array[i].value;
	}
	return data;
};
var Recruit = {
	$contactDialogForm: $("#contact_dialog_form"),
	$interviewDialogForm: $("#interview_dialog_form"),
	$bgcheckDialogForm: $("#bgcheck_dialog_form"),
	
	_setData: function(data, $ctx) {
		var $elem, instance;
		for(var name in data){
			if(data.hasOwnProperty(name)){
				$elem = $("[name='" + name + "']", $ctx);
				instance = $elem.data("userSelect");
				if(instance){
					instance.setValue(data[name]);
				} else {
					$elem.val(data[name]);
				}
			}
		}
	},

	// 单项操作
	singleHandler: function(url, param, callback){
		if(url){
			$.post(url, param, function(res){
				//@Todo: 这里状态判断变量名应改为isSuccess, 返回消息应改为msg
				if(res.isSuccess === 1) {
					Ui.tip(res.msg);
					callback && callback(res);
				} else {
					Ui.tip(res.msg, 'danger');
				}
			}, "json");
		}
	},
	
	// 多项操作
	multiHandler: function(url, param, msg, callback){
		if(url){
			Ui.confirm(msg, function(){
				$.post(url, param, function(res){
					if(res.isSuccess === 1) {
						Ui.tip(res.msg);
						callback && callback(res);
					} else {
						Ui.tip(res.msg, "danger");
					}
				}, "json");
			});
		}
	},
	
	//删除多个单个或多个简历
	deleteResumes: function(ids, callback){
		if(ids){
			this.multiHandler(PAGE_PARAM.RESUME_DELETE_URL, {resumeids: ids}, U.lang('REC.DELETE_RESUMES_CONFIRM'), callback);
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), "warning");
		}
	},
	
	// 导出单个联系记录
	exportContact: function(ids){
		if(ids){
			window.location = PAGE_PARAM.CONTACT_EXPORT_URL + '&contactids=' + ids;
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), "warning");
		}
	},
	
	// 删除单个联系记录
	deleteContact: function(id, callback){
		id &&
		this.singleHandler(PAGE_PARAM.CONTACT_DELETE_URL, { contactids: id }, callback);
	},
	
	//删除多个联系记录
	deleteContacts: function(ids, callback){
		if(ids){
			this.multiHandler(PAGE_PARAM.CONTACT_DELETE_URL, {contactids: ids}, U.lang('REC.DETELE_CONTACTS_CONFIRM'), callback);
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), "warning" );
		}
	},
	
	_updateContact: function(id, data, callback){
		if(id) {
			$.post(PAGE_PARAM.CONTACT_UPDATE_URL, $.extend({ contactid: id }, data) , function(res){
				if(res.isSuccess !== 0){
					callback && callback(res);
					Ui.tip(U.lang('CM.MODIFY_SUCCEED'));
				} else {
					Ui.tip(U.lang('CM.MODIFY_FAILED'), 'danger');
				}
			}, "json");
		}
	},
	
	// 编辑联系记录
	editContact: function(id, callback){
		var that = this;
		$('#r_fullname').hide(); ///
		if(id){
			var dialog = Ui.dialog({
				id: "d_contact",
				title: U.lang('REC.EDIT_CONTACT'),
				ok: function(){
					var datas = that.$contactDialogForm.serializeArray(),
						data = U.serializedToObject(datas);
					that._updateContact(id, data, callback);
				},
				cancel: true
			});
			$.post(PAGE_PARAM.CONTACT_EDIT_URL, { contactid: id }, function(res){
				that._setData(res, that.$contactDialogForm);
				dialog.content(Dom.byId("contact_dialog"));
				// 联系时间选择器
				$("#contact_time").datepicker();

			}, "json");
		}
	},
	
	_saveContact: function(data, callback){
		if(data){
			$.post(PAGE_PARAM.CONTACT_SAVE_URL, data, function(res){
				callback && callback(res);
			});
		}
	},
	
	//添加联系记录
	addContact: function(param, callback){
		var that = this;
		$('#r_fullname').show(); ///
		that.$contactDialogForm.get(0).reset();
		Ui.dialog({
			id: "d_contact",
			title: U.lang('REC.ADD_CONTACT'),
			content: Dom.byId('contact_dialog'),
			init: function(){
				// 联系时间选择器
				$("#contact_time").datepicker();
			},
			ok: function() {
				var datas = that.$contactDialogForm.serializeArray(),
					data = $.extend({}, param, U.serializedToObject(datas));
				that._saveContact(data, callback);
			},
			cancel: true
		});
	},


	_saveInterview: function(data, callback){
		if(data){
			$.post(PAGE_PARAM.INTERVIEW_SAVE_URL, data, function(res){
				callback && callback(res);
			});
		}
	},
	
	// 增加面试记录
	addInterview: function(param, callback){
		var that = this;
		$('#r_fullname').show(); ///
		that.$interviewDialogForm.get(0).reset();
		Ui.dialog({
			id: "d_interview",
			title: U.lang('REC.ADD_INTERVIEW'),
			content: Dom.byId('interview_dialog'),
			width: 500,
			ok: function() {
				var datas = that.$interviewDialogForm.serializeArray(),
					data = $.extend({}, param, U.serializedToObject(datas));
				that._saveInterview(data, callback);
			},
			cancel: true
		});
	},
	
	// 删除一条面试记录
	deleteInterview: function(id, callback){
		id && 
		this.singleHandler(PAGE_PARAM.INTERVIEW_DELETE_URL, { interviewids: id }, callback);		
	},
	
	// 删除多条面试记录
	deleteInterviews: function(ids, callback){
		if(ids) {
			this.multiHandler(PAGE_PARAM.INTERVIEW_DELETE_URL, { interviewids: ids }, U.lang('REC.DETELE_INTERVIEWS_CONFIRM'), callback);		
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'),  'warning');
		}
	},
	
	_updateInterview: function(id, data, callback){
		if(id) {
			$.post(PAGE_PARAM.INTERVIEW_UPDATE_URL, $.extend({ interviewid: id }, data) , function(res){
				if(res.isSuccess !== 0){
					callback && callback(res);
					Ui.tip('MODIFY_SUCCEED')
				} else {
					Ui.tip('MODIFY_FAILED', 'danger')
				}
			}, "json");
		}
	},
	
	// 编辑面试记录
	editInterview: function(id, callback){
		var that = this;
		$('#r_fullname').hide(); //
		if(id){
			$.post(PAGE_PARAM.INTERVIEW_EDIT_URL, { interviewid: id }, function(res){
				
				that._setData(res, that.$interviewDialogForm);
				Ui.dialog({
					id: "d_interview",
					title: U.lang('REC.EDIT_INTERVIEW'),
					content: Dom.byId("interview_dialog"), /////
					ok: function(){
						var datas = that.$interviewDialogForm.serializeArray(),
							data = U.serializedToObject(datas);
						that._updateInterview(id, data, callback);
					},
					cancel: true
				});
			}, "json");
		}
	},
	
	// 导出面试记录
	exportInterview: function(ids){
		if(ids){
			window.location = PAGE_PARAM.INTERVIEW_EXPORT_URL + '&interviews=' + ids;
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	},

	_saveBgcheck: function(data, callback){
		if(data){
			$.post(PAGE_PARAM.BGCHECK_SAVE_URL, data, function(res){
				callback && callback(res);
			});
		}
	},
	
	// 增加背景调查记录
	addBgcheck: function(param, callback){
		var that = this;
		$('#r_fullname').show();///
		that.$bgcheckDialogForm.get(0).reset();
		Ui.dialog({
			id: "d_bgcheck",
			title: U.lang('REC.ADD_BGCHECK'),
			content: Dom.byId('bgcheck_dialog'),
			init: function(){
				// 时间选择
        		$("#entrytime_datepicker").datepicker({ target: $("#quittime_datepicker") });
			},
			ok: function() {
				var datas = that.$bgcheckDialogForm.serializeArray(),
					data = $.extend(U.serializedToObject(datas),{
						fullname: $("#fullname").val()
					}, param);
				that._saveBgcheck(data, callback);
			},
			width: 500,
			cancel: true
		});
	},
	
	// 删除一条背景调查记录
	deleteBgcheck: function(id, callback){
		id && 
		this.singleHandler(PAGE_PARAM.BGCHECK_DELETE_URL, { checkids: id }, callback);		
	},
	
	// 删除多条背景调查记录
	deleteBgchecks: function(ids, callback){
		if(ids) {
			this.multiHandler(PAGE_PARAM.BGCHECK_DELETE_URL, { checkids: ids }, U.lang('REC.DETELE_INTERVIEWS_CONFIRM'), callback);		
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	},
	
	_updateBgcheck: function(id, data, callback){
		if(id) {
			$.post(PAGE_PARAM.BGCHECK_UPDATE_URL, $.extend({ checkid: id }, data) , function(res){
				if(res.isSuccess !== 0){
					callback && callback(res);
					Ui.tip('CM.MODIFY_SUCCEED')
				} else {
					Ui.tip('CM.MODIFY_FAILED', 'danger')
				}
			}, "json");
		}
	},
	
	// 编辑背景调查记录
	editBgcheck: function(id, callback){
		var that = this;
		$('#r_fullname').hide();///
		if(id){
			var dialog = Ui.dialog({
				id: "d_bgcheck",
				title: U.lang('EDIT_BGCHECK'),
				ok: function(){
					var datas = that.$bgcheckDialogForm.serializeArray(),
						data = U.serializedToObject(datas);
					that._updateBgcheck(id, data, callback);
				},
				width: 500,
				cancel: true
			});

			$.post(PAGE_PARAM.BGCHECK_EDIT_URL, { checkid: id }, function(res){
				
				that._setData(res, that.$bgcheckDialogForm);
				dialog.content(Dom.byId("bgcheck_dialog"));
				$("#entrytime_datepicker").datepicker({ target: $("#quittime_datepicker") });

			}, "json");
		}
	},
	
	// 导出背景调查记录
	exportBgcheck: function(ids){
		if(ids){
			window.location = PAGE_PARAM.BGCHECK_EXPORT_URL + '&checkids=' + ids;
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	},
	// 发送邮件
	sendMail: function(ids){
		window.location.href = PAGE_PARAM.SEND_MAIL_URL + "&resumeids=" + ids;
	},

	// 更改状态
	changeResumeStatus: function(status){
		var $ckbs = U.getChecked("resume[]"),
			ids = $ckbs.map(function(){
				return this.value;
			}).get().join(",");
		if(ids !== "") {
			$.post(PAGE_PARAM.RESUME_STATUS_URL, {resumeid: ids, status: status } , function(res){
				if(res.isSuccess !== 0){
					$ckbs.each(function(){
						$(this).parent().parent().parent().find("td:eq(7)").text(res.showStatus);
					});
					Ui.tip(res.msg);
				}
			}, "json");
		} else {
			Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
		}
	}
};


(function(){

	var eventHandler = {
		// 展开所有详细栏目
		expandAll: function($elem){
			var ctx = $elem.attr("data-expand-all");
			$("#" + ctx).find("[data-expand-target]").show();
			$elem.parent().hide();
		},

		//展开栏目各个详细栏目
		expandItem: function($elem){
			var targetName = $elem.attr("data-expand");
			$("div[data-expand-target='" + targetName + "']").show();
			$elem.hide().next().hide();
		},

    	//显示/隐藏查看简历页面个人详细信息
		togglePersonalDetail: function($elem){
        	$("#rsm_psn_table").toggleClass("active");
        	$elem.toggleClass("active");
		},
				
		//删除单个或多个简历
        deleteResumes: function(){
            var $ckbs = U.getChecked("resume[]"),
                ids = $ckbs.map(function(){
                    return this.value;
                }).get().join(",");

            Recruit.deleteResumes(ids, function(ids){
                $ckbs.each(function(){
                    $(this).parent().parent().parent().remove();
                });
            });
        },

		//添加联系记录
        addContact: function() {
            Recruit.addContact({
                fullname: $("#fullname").val()
            }, function(res){
                if (res && res.contactid) {
                    $temp = $.tmpl('contact_template', res);
                    $temp.find("input[type='checkbox']").label();
                    $('#contact_tbody').prepend($temp);
					$("#no_contact_tip").hide();
					Ui.tip(U.lang('REC.ADD_SUCCESS'));
                } else {
					Ui.tip(res.msg, 'danger');
				}
            });
        },

        //删除单个联系记录
        deleteContact: function($elem) {
            var contactids = $elem.attr("data-id");
            Recruit.deleteContact(contactids, function(){
                $elem.parent().parent().remove();
            });
        },

        //删除多个联系记录
        deleteContacts: function(){
            var $ckbs = U.getChecked("contact[]"),
                ids = $ckbs.map(function(){
                    return this.value;
                }).get().join(",");

            Recruit.deleteContacts(ids, function(ids){
                $ckbs.each(function(){
                    $(this).parent().parent().parent().remove();
                });
            });
        },

        //编辑联系记录
        editContact: function($elem) {
            Recruit.editContact($elem.attr("data-id"), function(res){
                var $row;
                if (res && res.contactid) {
                    $row = $.tmpl('contact_template', res);
                    $row.find("input[type='checkbox']").label();
                    $elem.parent().parent().replaceWith($row);
                }
            });
        },

		//导出联系记录
        exportContact: function(){
			var ids = U.getCheckedValue("contact[]");
            Recruit.exportContact(ids);
        },

        //增加面试记录
        addInterview: function(){
            Recruit.addInterview({
                fullname: PAGE_PARAM.FULLNAME
            }, function(res){
				if (res && res.interviewid) {
					$row = $.tmpl('interview_template', res);                       
					$row.find("input[type='checkbox']").label();
					$('#interview_tbody').prepend($row);
					$("#no_interview_tip").hide();
					Ui.tip(U.lang('REC.ADD_SUCCESS'));
				} else {
					Ui.tip(res.msg, 'danger');
				}
            });
        },

        //删除单条面试记录
        deleteInterview: function($elem) {
            var interviewid = $elem.attr("data-id");
            Recruit.deleteInterview(interviewid, function(){
                $elem.parent().parent().remove();
            });
        },

        //删除多条面试记录
        deleteInterviews: function() {
            var $ckbs = U.getChecked("interview[]"),
                ids = $ckbs.map(function(){
                    return this.value;
                }).get().join(",");

            Recruit.deleteInterviews(ids, function(ids){
                $ckbs.each(function(){
                    $(this).parent().parent().parent().remove();
                });
            });
        },

        //修改面试记录
        editInterview: function($elem) {
            Recruit.editInterview($elem.attr("data-id"), function(res){
                $row = $.tmpl('interview_template', res);
                $row.find("input[type='checkbox']").label();
                $elem.parent().parent().replaceWith($row);
            });
        },

        //导出面试记录
        exportInterview: function(){
			var ids = U.getCheckedValue("interview[]");
            Recruit.exportInterview(ids);
        },

        //增加背景调查记录
        addBgcheck: function() {
            Recruit.addBgcheck(null, function(res){
				if (res && res.checkid) {
					$row = $.tmpl('bgchecks_template', res);                       
					$row.find("input[type='checkbox']").label();
					$('#bgchecks_tbody').prepend($row);
					$("#no_bgchecks_tip").hide();
					Ui.tip(U.lang('REC.ADD_SUCCESS'));
				} else {
					Ui.tip(res.msg, 'danger');
				}
            });
        },

        //编辑背景记录
        editBgcheck: function($elem) {
            Recruit.editBgcheck($elem.attr("data-id"), function(res){
                $row = $.tmpl('bgchecks_template', res);
                $row.find("input[type='checkbox']").label();
                $elem.parent().parent().replaceWith($row);
            });
        },

        //删除背景记录
        deleteBgcheck: function($elem) {
            Recruit.deleteBgcheck($elem.attr("data-id"), function(){
                $elem.parent().parent().remove();
            });
        },

		//删除多条背景背景调查数据
        deleteBgchecks: function() {
			var $ckbs = U.getChecked("bgcheck[]"),
                ids = $ckbs.map(function(){
                    return this.value;
                }).get().join(",");

            Recruit.deleteBgchecks(ids, function(){
                $ckbs.each(function(){
                    $(this).parent().parent().parent().remove();
                });
            });
        },

		//导出背景记录
		exportBgcheck: function(){
			var ids = U.getCheckedValue("bgcheck[]");
			Recruit.exportBgcheck(ids);
		},

		// 删除简历
		deleteResume: function($elem){
			var id = $elem.attr("data-id");
			if(id){
				Ui.confirm(U.lang('DELETE_RESUME_CONFIRM'), function(){
					Recruit.singleHandler(PAGE_PARAM.RESUME_DELETE_URL, { resumeid: id }, function(res){
						$elem.parent().parent().remove();
					});
				});
			}
		},

		// 切换简历标记/取消标记状态
		toggleResumeMark: function($elem){
			var id = $elem.attr("data-id"),
				flag = $elem.attr("data-flag");

			Recruit.singleHandler(PAGE_PARAM.RESUME_MARK_URL, {resumeid: id, flag: flag}, function(res){
				if(flag == "1"){
                    $elem.attr({"data-flag": "0", "title": U.lang("REC.MARKED")});
					$elem.find("i").attr("class", "o-rsm-mark");
				} else {
                   	$elem.attr({"data-flag": "1", "title": U.lang("REC.UNMARKED")});
					$elem.find("i").attr("class", "o-rsm-unmark");
				}
			});
		},
		
		//发送邮件
		sendMail: function(){
			var ids = U.getCheckedValue("resume[]");
			if(ids !== "") {
				Recruit.sendMail(ids);
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
			}
		},

		// 状态变更
		// 待安排
		moveToArranged: function(){
			Recruit.changeResumeStatus("4");
		},
		// 面试
		moveToInterview:function(){
			Recruit.changeResumeStatus("1");
		},
		// 录用
		moveToEmploy: function(){
			Recruit.changeResumeStatus("2");
		},
		// 淘汰
		moveToEliminate: function(){
			Recruit.changeResumeStatus("5");
		}
	};

	$(document).on("click", "[data-click]", function(){
		var type = $.attr(this, "data-click");
		if(type && type in eventHandler){
			eventHandler[type].call(eventHandler, $(this));
		}
	});
})();