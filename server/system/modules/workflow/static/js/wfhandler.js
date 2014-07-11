/**
 * wfhandler.js
 * 工作流模块表单办理JS
 * IBOS
 * @author		banyan
 * @version	$Id: wfhandler.js 3510 2014-05-30 07:22:31Z gzzcs $
 */

$(function() {
	/**
	 * 
	 * @type String
	 */
	var DISABLED_FEEDBACK = '1';
	/**
	 * 
	 * @type String
	 */
	var FORCE_FEEDBACK = '2';

	var replyLock = 0;
	/**
	 * 
	 * @type type
	 */
	var REGMAPPING = {
		'notempty': {
			'reg': /\S+/,
			'tip': U.lang('RULE.NOT_NULL')
		},
		'chinese': {
			'reg': /^[\u4E00-\u9FA5\uF900-\uFA2D]+$/,
			'tip': U.lang('RULE.CHINESE_ONLY')
		},
		'letter': {
			'reg': /[A-Za-z]+$/,
			'tip': U.lang('RULE.ENGLISH_ONLY')
		},
		'num': {
			'reg': /^([+-]?)\d*\.?\d+$/,
			'tip': U.lang('RULE.NUMERIC_ONLY')
		},
		'idcard': {
			'reg': /^[1-9]([0-9]{14}|[0-9]{17})$/,
			'tip': U.lang('RULE.IDCARD_INVALID_FORMAT')
		},
		'mobile': {
			'reg': /^13[0-9]{9}|15[012356789][0-9]{8}|18[0256789][0-9]{8}|147[0-9]{8}$/,
			'tip': U.lang('RULE.MOBILE_INVALID_FORMAT')
		},
		'money': {
			'reg': /^(-)?(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/,
			'tip': U.lang('RULE.MONEY_INVALID_FORMAT')
		},
		'tel': {
			'reg': /^(([0\+]\d{2,3}-)?(0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/,
			'tip': U.lang('RULE.PHONE_INVALID_FORMAT')
		},
		'zipcode': {
			'reg': /^\d{6}$/,
			'tip': U.lang('RULE.ZIP_INVALID_FORMAT')
		},
		'email': {
			'reg': /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/,
			'tip': U.lang('RULE.EMAIL_INVALID_FORMAT')
		}
	};
	var commonUploadSettings = {
		post_params: {module: 'workflow'},
	};
	/**
	 * 公共附件
	 */
	Ibos.upload.attach({
		upload_url: Ibos.app.url('main/attach/upload', {uid: Ibos.app.g('uid'), type: 'workflow', flowprocess: Ibos.app.g('flowProcess'), hash: Ibos.app.g('swfHash')}),
		post_params: {module: 'workflow'},
		button_placeholder_id: 'global_upload_btn',
		custom_settings: {
			containerId: "g_file_target",
			inputId: "attachmentid",
		}
	});
	/**
	 * 会签附件
	 */
	Ibos.upload.attach({
		post_params: {module: 'workflow'},
		button_placeholder_id: 'fb_upload_btn',
		custom_settings: {
			containerId: "fb_file_target",
			inputId: "fbattachmentid",
		}
	});

	/**
	 * 
	 * @returns {Boolean}
	 */
	function checkItem() {
		var checkStr = Ibos.app.g('checkItem');
		var check = true, $form = getForm();
		if ($.trim(checkStr) !== '') {
			var items = checkStr.split(',');
			$.each(items, function(i, item) {
				var sep = item.split('=');
				var regObj = REGMAPPING.hasOwnProperty(sep[1]) ? REGMAPPING[sep[1]] : null;
				if (regObj) {
					var $el = $form.find("input[title='" + sep[0] + "'], textarea[title='" + sep[0] + "']");
					if ($el.is('input') || $el.is('textarea')) {
						var reg = new RegExp(regObj.reg);
						if (!reg.test($el.val() || $el.html())) {
							check = false;
							Ui.tip(sep[0] + ':' + regObj.tip, 'danger');
							$el.focus().blink();
							return false;
						}
					}
				}
			});
		}
		return check;
	}
	/**
	 * 
	 * @param {boolean} dom
	 * @returns object
	 */
	function getForm(dom) {
		return dom ? Ibos.app.g("form").get(0) : Ibos.app.g("form");
	}

	/**
	 * 
	 * @param {type} isFree
	 * @returns {undefined}
	 */
	function showNext(isFree) {
		var turnType = isFree ? 'freenext' : 'shownext';
		var url = Ibos.app.url('workflow/handle/' + turnType, {key: Ibos.app.g('key'), topflag: Ibos.app.g('topflag')});
		Ui.ajaxDialog(url, {
			title: U.lang('CONFIRM_POST'),
			lock: true,
			ok: function() {
				if (sncheckform()) {
					$('#turn_next_form').submit();
				}
				return false;
			},
			cancel: true
		});
	}

	var wfHandler = {
		'beforeSubmit': function(saveFlag) {
			var $form = getForm();
			// Todo::表单加载状态判断
			if (Ibos.app.g('feedback') !== DISABLED_FEEDBACK) {
				// Todo::如果不禁止会签，签章控件的判定
			}
			window.onbeforeunload = function() {
			};
			if (!checkItem()) {
				return false;
			}
			// 处理列表控件提交
			wfc && wfc.lvBeforeSubmit() && wfc.richBeforeSubmit();
			// 写入保存状态
			$form.get(0).saveflag.value = saveFlag;
			return true;
		},
		'delFb': function(feedid, $dom) {
			$.ajax({
				url: Ibos.app.url('workflow/handle/delfb'),
				data: {feedid: feedid},
				type: "get",
				dataType: "json",
				success: function(data) {
					if (data.isSuccess) {
						$dom.fadeOut(function() {
							$dom.remove();
						});
					} else {
						Ui.tip(U.lang('DELETE_FAILED'), 'danger');
						return false;
					}
				}
			});
		}
	};

	Ibos.events.add({
		/**
		 * 保存表单
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'save': function(param, $ctx) {
			if (wfHandler.beforeSubmit('save')) {
				getForm(true).submit();
			}
		},
		/**
		 * 主办人转交下一步
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'turn': function(param, $ctx) {
			if (wfHandler.beforeSubmit('turn')) {
				getForm(true).submit();
			}
		},
		/**
		 * 自由流程结束当前工作 
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'end': function(param, $ctx) {
			Ui.confirm(U.lang('WF.CONFIRM_END_FREE_PROCESS'), function() {
				if (wfHandler.beforeSubmit('end')) {
					getForm(true).submit();
				}
			});
		},
		/**
		 * 经办人办理完毕工作
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {Boolean}
		 */
		'finish': function(param, $ctx) {
			// 强制会签检查
			if (Ibos.app.g('feedback') === FORCE_FEEDBACK) {
				if ($.trim(getForm(true).content.value) === '') {
					Ui.tip(U.lang('WF.FORCED_SIGN_OPINION'), 'danger');
					$(getForm(true).content).blink();
					return false;
				}
			}
			Ui.confirm(U.lang('WF.CONFIRM_FINISH_WORK'), function() {
				if (wfHandler.beforeSubmit('finish')) {
					getForm(true).submit('finish');
				}
			});
		},
		/**
		 * 取消保存工作(即第一次新建工作进来办理页面时出现)
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'cancel': function(param, $ctx) {
			Ui.confirm(U.lang('WF.CONFIRM_UNSAVE_WORK'), function() {
				var url = Ibos.app.url('workflow/new/cancel', {key: Ibos.app.g('key')});
				window.location.href = url;
			});
		},
		/**
		 * 返回列表
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'return': function(param, $ctx) {
			window.location.href = Ibos.app.url('workflow/list/index', {op: 'list', type: 'todo', sort: 'all'});
		},
		/**
		 * 预览流程图
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'preview': function(param, $ctx) {
			var paramStr = "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1,top=0, left=0, width=" + screen.availWidth + ", height=" + screen.availHeight;
			window.open(Ibos.app.url('workflow/preview/flow', {key: param.key}), 'viewFlow', paramStr);
		},
		/**
		 * 加载会签意见回复
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'loadFbReply': function(param, $ctx) {
			var $target = $ctx.parents('ul .opinion-list').next();
			// 关闭回复面板
			if ($target.hasClass("focus")) {
				$target.removeClass("focus");
				$target.hide();
				// 打开回复面板
			} else {
				$target.addClass("focus");
				$target.show();
			}
			if (!$target.hasClass('loaded')) {
				var $repList = $target.find('.cmt-sub');
				$repList.empty().height(60).waiting(null, "mini");
				$.ajax({
					url: Ibos.app.url('workflow/handle/loadreply', {feedid: param.feedid}),
					type: "get",
					dataType: "json",
					success: function(data) {
						if (data.isSuccess) {
							var $res = $(data.data);
							$repList.height("").stopWaiting().replaceWith($res);
						}
					}
				}, 'json');
				$target.addClass("loaded");
			}
		},
		/**
		 * 
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {Boolean}
		 */
		'reply': function(param, $ctx) {
			if (replyLock === 1) {
				return;
			}
			var $target = $ctx.parent().prev(), $rpList = $ctx.parent().next();
			$ctx.button("loading");
			var content = $target.val();
			if ($.trim(content) === "") {
				$target.blink();
				$target.button("reset");
				return false;
			}
			$rpList.waiting(null, "mini");
			$.ajax({
				url: Ibos.app.url('workflow/handle/addreply', param),
				data: {content: content, formhash: Ibos.app.g('formHash')},
				type: "post",
				dataType: "json",
				success: function(data) {
					if (data.isSuccess) {
						var $res = $(data.data);
						$rpList.prepend($res).stopWaiting();
						$target.val("");
						$ctx.button("reset");
					} else {
						Ui.tip(data.data, 'danger');
						return false;
					}
				}
			});
			// 计数器，预防狂刷回复
			replylock = 1;
			setTimeout(function() {
				replyLock = 0;
			}, 5000);
		},
		/**
		 * 点评设置 输入区域内容
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'setreply': function(param, $ctx) {
			var target = $ctx.parents('ul .cmt-sub').prev().prev();
			target.focus().val(U.lang('REPLY') + ' ' + param.name + ' ： ');
		},
		/**
		 * 删除会签点评
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'delreply': function(param, $ctx) {
			var $target = $ctx.parents('li .cmt-item');
			wfHandler.delFb(param.feedid, $target);
		},
		/**
		 * 删除会签
		 * @returns {undefined}
		 */
		'delFb': function(param, $ctx) {
			Ui.confirm(U.lang('WF.CONFIRM_DEL_FB'), function() {
				wfHandler.delFb(param.feedid, $('#fb_' + param.feedid));
			});
		},
		/**
		 * 
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'delFbAttach': function(param, $ctx) {
			Ui.confirm(U.lang('CONFIRM_DEL_ATTACH'), function() {
				var url = Ibos.app.url('workflow/handle/delfbattach');
				$.get(url, param, function(data) {
					if (data.isSuccess) {
						var $target = $ctx.parentsUntil('li');
						$target.fadeOut(function() {
							$target.remove();
						});
					} else {
						Ui.tip(U.lang('DELETE_FAILED'), 'danger');
					}
				}, 'json');
			});
		},
		/**
		 * 删除公共附件
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'delAttach': function(param, $ctx) {
			Ui.confirm(U.lang('CONFIRM_DEL_ATTACH'), function() {
				var url = Ibos.app.url('workflow/handle/delattach');
				$.get(url, param, function(data) {
					if (data.isSuccess) {
						var $target = $('#attach_' + param.aid);
						$target.fadeOut(function() {
							$target.remove();
						});
					} else {
						Ui.tip(U.lang('DELETE_FAILED'), 'danger');
					}
				}, 'json');
			});
		},
		'fallback': function(param, $ctx) {
			// 回退上一步骤
			if (param.type == 1) {
				Ui.prompt(U.lang('WF.ENTER_FALLBACK_REASON'), function(msg) {
					if (msg === '') {
						Ui.tip(U.lang('WF.ENTER_FALLBACK_REASON'), 'danger');
						return false;
					}
					var form = getForm();
					form.waitingC(U.lang('IN_SUBMIT'), true);
					var url = Ibos.app.url('workflow/handle/fallback', {key: Ibos.app.g('key')});
					$.post(url, {formhash: Ibos.app.g('formHash'), 'remind': msg}, function(data) {
						if (data.isSuccess) {
							window.location.href = Ibos.app.url('workflow/list/index');
						} else {
							form.stopWaiting();
							Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
						}
					}, 'json');
				});
			} else {
				// 回退之前的步骤
				Ui.dialog({
					title: U.lang('WF.SELECT_FALLBACK_STEP'),
					content: document.getElementById('fallback_box'),
					cancel: true,
					ok: function() {
						var selected = $('#fallback_box').find('input[type=radio]:checked').val(), url = '';
						var msg = $('#fallback_msg').val();
						if (msg === '') {
							Ui.tip(U.lang('WF.ENTER_FALLBACK_REASON'), 'danger');
							return false;
						}
						if (selected) {
							url = Ibos.app.url('workflow/handle/fallback', {key: Ibos.app.g('key'), id: selected});
						}
						if (url) {
							var form = getForm();
							form.waitingC(U.lang('IN_SUBMIT'), true);
							$.post(url, {formhash: Ibos.app.g('formHash'),'remind': msg}, function(data) {
								if (data.isSuccess) {
									window.location.href = Ibos.app.url('workflow/list/index');
								} else {
									form.stopWaiting();
									Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
								}
							}, 'json');
						} else {
							Ui.alert(U.lang('WF.SELECT_FALLBACK_STEP'));
						}
						return false;
					}
				});
			}
		}
	});
	// 判断当前浏览器是否为IE浏览器,然后处理签章手写checkbox是否可用
	if ($.browser.msie) {
		$("#IE_Check").removeAttr("disabled");
	}
	// 提示信息的初始化
	$("#support_type").tooltip({trigger: "hover"});
	if (U.getCookie('save_flag') == 1) {
		Ui.tip(U.lang('SAVE_SUCEESS'), 'success');
		U.setCookie('save_flag', '');
	}
	if (U.getCookie('turn_flag') == 1) {
		if (Ibos.app.g('flowType') == '1') {
			showNext(false);
		} else {
			showNext(true);
		}
		U.setCookie('turn_flag', '');
	}

});
