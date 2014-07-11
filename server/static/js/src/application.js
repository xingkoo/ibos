/**
 * 放置一些全局范围内初始化的脚本或组件
 */

var Ibosapp = {
	op: {
		// 检查用户是否处于登入状态
		checkIsLogin: function(callback){
			$.get(Ibos.app.url("user/default/checklogin"), callback, "json");
		},

		ajaxLogin: function(info, callback){
			info = $.extend({
				formhash: Ibos.app.g("formHash"),
				logintype: "1"
			}, info);

			$.post(Ibos.app.url("user/default/ajaxlogin"), info, callback, "json");
		}
	},

	// 表单验证
	formValidate: {
		pageError: function(msg, elem, errorList){
			var data;
			// 如果设置了errorFocus(即出错后focus到第一个出错的控件)
			// 则判断控件的类型，对一些特殊控件做出相应的处理
			if(this.errorFocus){
				// 如果是下拉控件、选人控件
				if(data = $.data(elem, "select2")){
					data.focus();
				}
			}
		},
		setGroupState: function(input, state){
			var $group = $(input).closest(".input-group"),
				errorCls = "input-group-error",
				correctCls = "input-group-correct";

			switch(state) {
				case "correct": 
					$group.removeClass(errorCls).addClass(correctCls);
					break;
				case "error":
					$group.removeClass(correctCls).addClass(errorCls);
					break;
				default:
					$group.removeClass(correctCls).removeClass(errorCls);
			}
		}
	},

	/**
	 * 图章选取器
	 * @method stampPicker
	 * @param  {Jquery} $elem          触发节点
	 * @param  {Jquery} [$input]       用于储存值的input节点
	 * @param  {Number} [defaultValue] 默认值
	 * @return {[type]}                [description]
	 */
	stampPicker: function($elem, stamp, defaultValue) {
		stamp = stamp || [];
		var _currentValue = defaultValue || Number($elem.attr("data-value")),
			_hasBind = false,
			_stopPropa = function(e){
				e.stopPropagation();
			},
			_getContent = function(value){
				var content,
					isSelected;

				content = '<table class="stamp-table"><tbody>';
				for(var i = 0, len = stamp.length; i < len; i++) {
					isSelected = stamp[i].value === _currentValue;
					if(i === 0) {
						content += '<tr>';
					} else if(i % 5 === 0) {
						content += '</tr><tr>'
					}
					content += '<td>'+
							'<div class="stamp-item' + (isSelected ? " active" : "") +'" data-node-type="stampItem" title="' + stamp[i].title + '" data-stamp="' + stamp[i].stamp + '" data-path="'+ stamp[i].path + '" data-value="' + stamp[i].value + '">' + 
								'<div class="stamp-img-wrap"><img width="60" height="24" src="' + stamp[i].path + '" alt="' + stamp[i].title + '"/></div>' + 
								(typeof stamp[i].point !== "undefined" ?
								'<div class="stamp-point"><strong>' + (stamp[i].point||0) + '</strong>' + U.lang('YUANCAPITAL.CENT') + '</div>' :
								'') +
							'</div>' +
						'</td>';
					
					if(i === stamp.length - 1){
						content += '</tr>';
					}
				}

				return content;
			};

		Ui.popover($elem, {
			content: function(){ return _getContent(defaultValue) },
			html: true,
			container: document.body
		});

		// 当popover 显示时触发，shown 为popover自定义发布事件
		$elem.on("shown", function(evt){
			var popover = $.data(this, "popover"),
				def = $.attr(this, "data-value");

			// 选中项添加样式
			popover.$tip.find("[data-node-type='stampItem']").each(function(){
				if($.attr(this, "data-value") === def) {
					$(this).addClass("active");
				}
			})

			if(!_hasBind){
				popover.$tip.on("click", "[data-node-type='stampItem']", function(){

					var value = Number($.attr(this, "data-value")), path = $.attr(this, "data-path"), stamp = $.attr(this, "data-stamp");
					if(!isNaN(value)) {
						popover.$tip.find("a").removeClass("active");
						$(this).addClass("active");
						_currentValue = value;	
						
						// 此处发布stampChange事件
						$elem.attr("data-value", _currentValue).trigger("stampChange", {value: _currentValue,path:path,stamp:stamp});
						popover.hide();
					}

				}).on("mousedown", _stopPropa);
				_hasBind = true;
			}

		}).on("mousedown", _stopPropa);

		$(document).on("mousedown", function(e){
			$elem.popover("hide");
		});
	},

	dropnotify: {
		interval: 60000,
		init: function(dropclass, containerId, url) {
			this.dropclass = dropclass;
			this.$container = $("#" + containerId);
			this.startCount();
		},
		//显示父对象
		show: function() {
			this.$container.show();
		},

		//隐藏
		hide: function() {
			this.$container.hide();
			// 在没有新提醒前，两小时内不再显示提醒框
			if(this.data.unread_total !== 0) {
				U.setCookie('dropnotify', $.toJSON(this.data), 7200);
				$(this).trigger("ignore");
			}
		},
		getCount: function(){
			var _this = this;
			$.get(Ibos.app.url('message/api/getunreadcount', { random: Math.random() }), function(res) {
				var data = res.data,
					notifycookie;

				if (res && ("undefined" === typeof(res.data) || res.status !== 1)) {
					return false;
				} else {
					notifycookie = U.getCookie('dropnotify');
					// 若用户关闭了提示框(即cookie中有dropnotify字段)时，只有当有新消息时才显示
					if(notifycookie && U.isEqualObject($.parseJSON(notifycookie), res.data)) {
						return false;
					}
					// 若新消息与上次相同，即自次消息以来没有发生变化，则返回
					if(U.isEqualObject(_this._prev, res.data)) {
						return false;
					}

					// 清空cookie
					U.setCookie('dropnotify', '');

					_this.data = res.data;

					if (data.unread_total <= 0) {
						_this.hide();
					} else {
						_this.show();
					}

					$('.' + _this.dropclass + " li").each(function() {
						var name = $(this).attr('rel');
						num = data[name];
						if (num > 0) {
							$(this).find('span').html("<strong class='xco'>" + num + "</strong> " + U.lang("DN." + name.toUpperCase()));
							$(this).show();
						} else {
							$(this).hide();
						}
					});
					_this._prev = res.data;

					$(_this).trigger("new", res.data);
				}
			}, 'json');
		},

		startCount: function() {
			var _this = this;
			setInterval(function(){
				_this.getCount();	
			}, _this.interval); 
			this.getCount();
		}
	}
};

// 新提醒
$(Ibosapp.dropnotify).on("new", function(evt, data){
	// 标题闪动
	if(data.unread_total > 0) {
		Ui.blinkTitle(U.lang("NOTIFY.UNREAD_TOTAL", { count: data.unread_total }));
		// 如果支持窗口通知且已获得权限
		var n = window.webkitNotifications;
		if(n && n.checkPermission() === 0 && U.getCookie("allow_desktop_notify") == "1") {
			var title = U.lang("NOTIFY.UNREAD_TOTAL", { count: data.unread_total }),
				content = U.lang("NOTIFY.TO_VIEW");
			var noti = n.createNotification(Ibos.app.getStaticUrl("/image/logo_pic.png"), title, content);
			noti.onclick = function(){
				window.focus();
				window.open(Ibos.app.url("message/notify/index"))
			}
			noti.ondisplay = function(evt){
				setTimeout(function(){
					evt.currentTarget.cancel();
				}, 1e4);
			}
			noti.replaceId = "noti_msg";
			noti.show();
		}
	} else {
		Ui.blinkTitle(false);
	}
})
.on("ignore", function(evt){
	Ui.blinkTitle(false);
})



$(function(){
	// 用户登录状态卡
	(function () {
		var $loginCtrl = $("#user_login_ctrl"),
			$loginCard = $("#user_login_card");

		var menu = new Ui.PopMenu($loginCtrl, $loginCard, {
			position: {
				at: "right bottom",
				my: "right top+25",
				of: $loginCtrl
			},
			showDelay: 0,
			animate: true
		});

		$loginCard.on("show", function() {
			$loginCtrl.addClass("active");
		})
		.on("hide", function(){
			$loginCtrl.removeClass("active")
		})

	})();


	/**
	 * 用户资料卡
	 * @module userCard
	 * @return {[type]} [description]
	 */
	(function() {
		var $card = $("#ui_card"),
			delay = 500,
			showTimer,
			hideTimer;

		if (!$card || !$card.length) {
			$card = $("<div id='ui_card' class='ui-card'></div>").appendTo(document.body);
		}

		var show = function(param, relative){
			var content = $card.data("usercard_" + param.uid),
				_position = function(){
					$card.position({
						at: "left top",
						my: "left bottom-5",
						of: relative
					})
				}
			clearTimeout(hideTimer);

			$card.show().empty()
			_position();
			$card.waiting(null, 'small');

			if (content) {
				$card.waiting(false).html(content);
				return
			}

			$.get(Ibos.app.url("user/info/usercard"), param, function(res) {
				if (res) {
					$card.waiting(false).html(res).data("usercard_" + param.uid, res);
				}
			});

		}

		var hide = function(){
			hideTimer = setTimeout(function() {
				$card.hide().waiting(false);
			}, delay)
		}

		$(document).on({
			"mouseenter": function() {
				var $elem = $(this),
					param = U.urlToObj($.attr(this, "data-param"));

				showTimer = setTimeout(function() {
					show(param, $elem);
				}, delay);

			},
			"mouseleave": function() {
				clearTimeout(showTimer);
				hide();
			}
		}, "[data-toggle='usercard']");

		$card.on({
			"mouseenter": function() {
				clearTimeout(hideTimer);
			},
			"mouseleave": hide
		})

		return {
			hide: hide,
			show: show
		}
	})();

	// 二级导航
	$("#nv li").each(function(){
		var $ctrl = $(this),
			$menu = $($ctrl.attr("data-target"));

		new Ui.PopMenu($ctrl, $menu, {
			position: {
				my: "left top-10"
			},
			hideDelay: 0,
			showDelay: 0
		});
		
		$menu.on({
			"show": function(evt){ $ctrl.addClass("open") },
			"hide": function(evt){ $ctrl.removeClass("open") }
		});
	});

	// 积分提示
	if(Ibos.app.g("creditRemind") && U.getCookie("creditremind")){
		Ui.showCreditPrompt();
	};
	// 全局表单提交，跳转，等成功提示，cookie来自各表单或消息提示，跳转的成功页等
	if(U.getCookie("globalRemind") && U.getCookie("globalRemindType")){
		Ui.tip(decodeURI(U.getCookie("globalRemind")), U.getCookie("globalRemindType"));
		U.setCookie('globalRemind', '');
		U.setCookie('globalRemindType', '');
	};
	
});



/**
 * 搜索模块，此模块页面单一且需使用指定 ID
 * @Todo: 重写..
 * @deprecated 
 * @return {[type]} [description]
 */
Ibos.search = (function(){
	var searchId = "mn_search",
		$searchInput,
		$searchBtn,
		$searchBox,
		advanceEnable = true, // 当此值为true时，高级设置功能可用
		toAdvance = true; // 当此值为true时，点击按钮会进入高级设置，否则则搜索

	// 默认交互
	var submit = function(){
			if(!hasVal()){
				$.jGrowl(L.KEYWORD_CANNOT_BE_EMPTY, { theme: "warning" });
				return false
			}
			$searchInput.get(0).form.submit();
		},
		advanceSubmit = function(){ return true; },
		advance = function () {
			var $form = $("#mn_search_advance_form");
			$.artDialog({
				id: "d_advance",
				title: L.ADVANCED_SETTING,
				content: document.getElementById("mn_search_advance"),
				cancel: true,
				init: function(){
					$form.get(0) && $form.get(0).reset();
				},
				ok: function(){
					var data = $form.serializeArray();
					advanceSubmit.call(null, $form, data);
				}
			})
		};

	var hasVal = function(){
		return !!$.trim($searchInput.val());
	}

	var focus = function(evt){
		$searchBox.addClass("has-focus");
		toAdvance = false;
	}

	var blur = function(evt){
		// 搜索框内容不为空时，保持搜索状态
		if(!hasVal()){
			$searchBox.removeClass("has-focus");
			// 当高级设置可用时，此时按钮点击进入高级设置
			if(advanceEnable){
				toAdvance = true;
			}
		}
	}

	var bindEvent = function(){
		$searchInput.on({
			"keydown": function(evt){
				// 按按下 enter 键时，触发搜索
				if(evt.which === 13) {
					submit();
					return false;
					// evt.stopPropagation();
				}
			},
			"focus": focus,
			"blur": blur
		});

		$searchBtn.on("click", function(){
			if(toAdvance) {
				advance();
			} else {
				submit();
			}
		})
	}

	// 禁用高级设置功能
	var disableAdvance = function(){
		$searchBox.removeClass("search-config");
		advanceEnable = false;
	}
	// 启用高级设置功能	
	var enableAdvance = function(){
		$searchBox.addClass("search-config");
		advanceEnable = true;
	}

	var init = function(){
		$searchInput = $("#" + searchId);
		// 没有搜索框时，不进行初始化
		if(!$searchInput.get(0)){
			return false;
		} else {
			$searchBox = $searchInput.parent();
			$searchBtn = $searchInput.next();
			bindEvent();
		}
	}

	var setSubmit = function(func){
		if($.isFunction(func)){
			submit = func;
		}
	}
	var setAdvance = function(func){
		if($.isFunction(func)){
			advance = func;
		}
	}
	var setAdvanceSubmit = function(func){
		if($.isFunction(func)){
			advanceSubmit = func;
		}
	}
	return {
		init: init,
		disableAdvance: disableAdvance,
		enableAdvance: enableAdvance,
		setSubmit: setSubmit,
		setAdvance: setAdvance,
		setAdvanceSubmit: setAdvanceSubmit
	}
})();



$(function(){
	// 全局级别的事件
	Ibos.evt.add({
		// 使用外部文档阅读器
		"viewOfficeFile": function(param, elem, evt){
			Ui.openFrame(param.href, {
				title: false,
				id: "d_office_file",
				width: 800,
				height: 600
			})
		},

		// 前台查看证书
		"showCert": function(){
			Ui.ajaxDialog(Ibos.app.url('main/default/getCert'), {
				id: "d_cert",
				title: false,
				ok: false,
				width: 661,
				height: 471,
				padding: 0
				// cancel: false
			})
		},

		// 微博关注
		"follow": function(param, elem) {
			var $elem = $(elem);
			$elem.button("loading");
			param.formhash = Ibos.app.g('formHash');
			$.post(Ibos.app.url('message/api/dofollow'), param, function(res) {
				$elem.button("reset");
				if (res.isSuccess) {
					// 改变按钮状态，“相互关注”和“已关注”视图不一样
					$elem.html(res.both ? '<i class="om-geoc"></i> ' + U.lang("WB.FOLLOWBOTH") : '<i class="om-gcheck"></i> ' + U.lang("WB.FOLLOWED"))
					.removeClass("btn-warning")
					.attr({
						"data-action": "unfollow",
						"data-node-type": "unfollowBtn",
						"data-loading-text": U.lang("WB.UNFOLLOWING")
					})
					// 使用attr设置loading-text后，data的缓存并没有即时更新，所以这里还需要重新设置data
					.data("loading-text", U.lang("WB.UNFOLLOWING"))
					// 更新资料卡缓存, 延迟到按钮恢复原状态后执行
					setTimeout(function(){
						$("#ui_card").data("usercard_" + param.fid, $("#ui_card").html());
					}, 0)
				} else {
					Ui.tip(res.msg, 'danger');
					return false;
				}
			}, 'json');
		},

		// 取消微博关注
		"unfollow": function(param, elem) {
			var $elem = $(elem);
			$elem.button("loading");
			param.formhash = Ibos.app.g('formHash');
			$.post(Ibos.app.url('message/api/unfollow'), param, function(res) {
				$elem.button("reset");
				if (res.isSuccess) {
					// 改变按钮状态
					$elem.html('<i class="om-plus"></i> ' + U.lang("WB.FOLLOW"))
					.removeClass("btn-danger").addClass("btn-warning")
					.attr("data-action", "follow")
					.attr("data-loading-text", U.lang("WB.FOLLOWING"))
					.data("loading-text", U.lang("WB.FOLLOWING"))
					.removeAttr("data-node-type");
					// 更新资料卡缓存, 延迟到按钮恢复原状态后执行
					setTimeout(function(){
						$("#ui_card").data("usercard_" + param.fid, $("#ui_card").html());
					}, 0)
				} else {
					Ui.tip(res.msg, 'danger');
					return false;
				}
			}, 'json');
		},

		"back": function(){
			window.history.go(-1);
		}
	})

	/**
	 * 定时消息提醒
	 */
	if(document.getElementById("message_container")){
		setTimeout(function() {
			Ibosapp.dropnotify.init('reminder-list', 'message_container');
		}, Ibos.app.g("settings").notifyInterval);
	}

	var  $doc = $(document);

	// 切换显示隐藏状态
	$doc.on("click", "[data-toggle='display']", function(){
		var showSelector = $.attr(this, "data-toggle-show"),
			hideSelector = $.attr(this, "data-toggle-hide");
		showSelector && $(showSelector).show();
		hideSelector && $(hideSelector).hide();
	})

	$doc.on({
		"focus": function(){
			$(this).parent().addClass("has-focus");
		},
		"blur": function(){
			 $(this).parent().removeClass('has-focus');
		}
	}, "input:not([nofocus])")

	// 悬停取消关注
	$doc.on({
		"mouseenter": function(){
			var $elem = $(this);
			$elem.data("oldHtml", $elem.html()).addClass("btn-danger")
			.html('<i class="om-chi"></i> ' + U.lang("WB.UNFOLLOW"));
		},
		"mouseleave": function(){
			var $elem = $(this);
			$elem.removeClass("btn-danger").html($elem.data("oldHtml"));
		}
	}, '[data-node-type="unfollowBtn"]');

	// 支持 IE 9 及以下的 placeholder功能 
	if(!("placeholder" in document.createElement("input"))) {
		$.getScript(Ibos.app.getStaticUrl("/js/lib/jquery.placeholder.js"), function(){
			$("[placeholder]").placeholder();
		});
	}

	// 跳转后的成功提示
	if(U.getCookie("success_tip")) {
		Ui.tip(U.getCookie("success_tip"));
		U.setCookie("success_tip", "")
	}

	// 登录超时时，弹出登录框
	(function(){
		var page = Ibos.app.g("page"),
			timeout = +Ibos.app.g("loginTimeout"),
			timer;
		// 当前页不为登陆页, 定时检查登录状态, 5 分钟一次
		if(page !== "login" && timeout) {
			timer = setInterval(function(){
				var lastActivity = +U.getCookie("lastactivity"),
					nowTime = +new Date/1000;
				// 当前时间与上次活动时间的时间差 大于 超时限定时间时
				// 发送 ajax 到后端检测此时的登陆状态，若此时已离线，则弹出登陆窗口
				// 否则继续定时检测
				if(lastActivity && (nowTime - lastActivity >= timeout )){
					Ibosapp.op.checkIsLogin(function(res){
						if(!res.islogin) {
							Ui.showLoginDialog({
								username: res.username
							});
						}
					})
				}
			}, 300000);
		}

	})();

	$(document).on("change", "input[type='checkbox']", function(evt, data){
		if(data && data.outloop) return false;
		var leaderName = $.attr(this, "data-name"),
			isChecked = this.checked,
			name;
		if(leaderName) {
			$('[name="' + leaderName + '"], [data-check="'+ leaderName +'"]').prop("checked", isChecked).trigger("change", { outloop: true });
		} else {
			name = $.attr(this, "data-check") || $.attr(this, "name");
			if(name) {
				if(!isChecked) {
					$('[data-name="' + name + '"]').prop("checked", false).trigger("change", { outloop: true });
				}
			}
		}
	});

	// IE 8 以下跳转至浏览器升级页
	if($.browser.msie && parseInt($.browser.version, 10) < 8) {
		window.location.href = Ibos.app.url("main/default/unsupportedBrowser");
	}
});

// 编辑器本地存储
(function(){
	var _getInstant = function(ue){
		if(UE) {
			if(typeof ue === "string") {
				for(var i in UE.instants) {
					if(UE.instants[i].key === ue) {
						return UE.instants[i];
					}
				}
			} else if( ue instanceof UE.Editor ){
				return ue;
			}

		}
		return false;
	}
	var _timer;
	Ibosapp.editor = {
		setLocal: function(ue){
			var content;
			ue = _getInstant(ue);
			if(ue) {
				content = ue.getContent(false, false, false, true);
			
				Ibos.local.set("ue_" + ue.key, content);
				return true;

			}
			return false;
		},
		getLocal: function(ue){
			var ueid = typeof ue === "string" ? ue : ue.key;
			return Ibos.local.get("ue_" + (ueid || "")) || "";
		},
		restoreLocal: function(ue){
			var _this = this,
				content;
			ue = _getInstant(ue);
			if(ue){
				content = _this.getLocal(ue.key);
				content && ue.setContent(content);
				Ibos.ignoreFormChange();
			}
		},
		clearLocal: function(ue){
			clearInterval(_timer);
			var ueid = typeof ue === "string" ? ue : ue.key;
			return Ibos.local.remove("ue_" + (ueid || ""));
		},
		initLocal: function(ue){
			var _this = this;
			this.restoreLocal(ue);
			_timer = setInterval(function(){
				_this.setLocal(ue);
				// console && console.log("Editor: " + (typeof ue === "string" ? ue : ue.key) + " save to localstorage");
			}, 3000);
		}
	}
})();

