/**
 * core
 * 将用于放置核心函数
 */

$.noop = function(){};
$.ui.isAjaxApp = true;
$.ui.loadingText = "读取中...";
// 此方法用于解决编辑器内的链接点击后无法跳转的bug
// 目前的规则，完整的url地址会作跳转处理
// @Debug: 未在手机端真实环境作测试
$.ui.customClickHandler = function(target, evt){
	var href = target.getAttribute("href");
	if(/http[s]?:\/\//.test(href)){
		appSdk.browser.show(href);
		return true;
	}
}

// 全局命名空间
var core = {
	util: {
		addValue: function(input, val){
			var $input = $(input),
				source = $input.val();
			$input.val(source !== "" ? (source + "," + val) : val);
		},
		removeValue: function(input, val){
			// @Todo: 改为使用正则
			var $input = $(input),
				source = $(input).val(),
				strArr = (source + "").split(","),
				index = strArr.indexOf(val);
			
			if(index !== -1) {
				strArr.splice(index, 1);
			}
			$input.val(strArr.join(","));
		},

		replaceEditorSrc: function(source){
			if(typeof source === 'string') {
				var reg = /src=['"](data\/.*?)['"]/g;
				return source.replace(reg, function($0, $1){
					return "src='" + app.defaultUrl + "/" + $1 + "' onclick='appSdk.browser.show(this.src)'";
				})
			// 当 source 为节点时
			} else {
				$.query('img[src^="data"]', source).each(function() {
					this.setAttribute("src", app.defaultUrl + "/" + this.getAttribute("src"));
					this.setAttribute("onclick", "appSdk.browser.show(this.src)");
				});
				return source;
			}
		},

		openAttch: function(url){
			
		}
	},
	str: {},
	/* 判断对象是否为$.afm对象且不为空 */
	isset: function($elem){
		return $.is$($elem) && $elem.length;
	},

	// Date
	/*------ funcion -------*/
	// unix  秒数时间戳
	toDatetime: function(unix, type) {
	    var dt = new Date(parseInt(unix) * 1000);
		var now = new Date();
		var datetime,Y,M,D,h,m,s;
		// @Todo: 前置补零
		Y = dt.getFullYear();
		M = dt.getMonth() + 1 ;
		D = dt.getDate();
		h = dt.getHours();
		m = dt.getMinutes();
		s = dt.getSeconds();
		W = dt.getDay();

		switch(type){
			case "u":
			default:
				var time = (now - dt)/1000;
					// 七天以前
					if (time > 604800){
						datetime = Y + "年" + M + "月" + D +"日";
					// 一天至七天
					} else if (time > 86400){
						datetime = Math.floor(time / 86400) + "天前";
					}else if (time > 3600) {
						datetime = Math.floor(time / 3600) + "小时前";
					} else if (time > 1800) {
						datetime = "半小时前";
					} else if (time > 60) {
						datetime = Math.floor(time / 60) + "分钟前" ;
					} else if (time >= 0) {
						datetime = Math.floor(time) + "秒前" ;
					} else {
						datetime = Y + "-" + M + "-" + D;
					}
				break;
			case "d":
				datetime = Y + "-" + ("00" + M).substr(("" + M).length) + "-" + ("00" + D).substr(("" + D).length);
				break;
			case "dt":
				datetime = Y + "-" + M + "-" + D + " " + h + ":" + m + ":" + s;
				break;
			case "cn":
				datetime = Y + "年" + M + "月" + D + "日";
				break;
			case "cnday":
				datetime = Y + "年" + M + "月" + D + "日 星期" + ("日一二三四五六".charAt(W));
				break;
				
		}
		return datetime;
	},


	// Storage
	// 获取和储存本地缓存
	setStorage: function(name, value){
		value = JSON.stringify(value);
		localStorage.setItem(name, value);
	},

	getStorage: function(name){
		var result = localStorage.getItem(name);
		return result ? JSON.parse(result) : result;
	},
	removeStorage: function(name){
		var result = localStorage.removeItem(name);
		return result
	},
	// @Todo: 这里只有自动增高的功能，是否有在内容减少后自动减去高度的需要
	adjustTextarea: function(elem, extra, maxHeight){
		extra = extra || 20;
		// 这个判断也许没有必要
		var isOpera = !! window.opera && !! window.opera.toString().indexOf('Opera'),
			getStyle = function(name) { return $(elem).css(name); },
			minHeight = parseFloat(getStyle('height'));

		// getStyle('heigth')有可能返回auto值, 此时将最小高度设置offsetHeight
		isNaN(minHeight) && (minHeight = elem.offsetHeight);

		// 有最小高度会限制高度的增长，所以去掉
		elem.style.maxHeight = elem.style.resize = 'none';

		var change = function() {
			// console.count()
			var scrollTop, height,
				padding = 0,
				style = elem.style;

			if (elem._length === elem.value.length) return;
			elem._length = elem.value.length;

			if (!isOpera) {
				padding = parseInt(getStyle('paddingTop'), 10) + parseInt(getStyle('paddingBottom'), 10);
			};

			scrollTop = document.body.scrollTop || document.documentElement.scrollTop;

			elem.style.height = minHeight + 'px';

			if (elem.scrollHeight > minHeight) {
				if (maxHeight && elem.scrollHeight > maxHeight) {
					height = maxHeight - padding;
					style.overflowY = 'auto';
				} else {
					height = elem.scrollHeight - padding;
					style.overflowY = 'hidden';
				};

				style.height = height + extra + 'px';
				scrollTop += parseInt(style.height, 10) - elem.currHeight;
				document.body.scrollTop = scrollTop;
				document.documentElement.scrollTop = scrollTop;
				elem.currHeight = parseInt(style.height, 10);
			};
		};
		change();
	},
	// Input 
	/**
	 * textarea高度自适应
	 * @param  {[type]} elem      [description]
	 * @param  {[type]} extra     [description]
	 * @param  {[type]} maxHeight [description]
	 * @return {[type]}           [description]
	 */
	autoTextarea: function(elem, extra, maxHeight) {
		var that = this;
		elem.addEventListener('input', function(){
			that.adjustTextarea(elem, extra, maxHeight)
		}, false);
		elem.addEventListener('focus', function(){
			that.adjustTextarea(elem, extra, maxHeight)
		}, false);
	},

	error: function(err){ console && console.error("Error: " + err) }
}

/// 用于创建列表结构
var List = function(id, tpl, options){
	this.id = id;
	this.tpl = tpl,
	this.options = $.extend({}, List.defaults, options);

	this.$list = $("#" + this.id);

	if(!core.isset(this.$list) || !tpl || typeof tpl !== "string") {
		throw new Error("List: 初始化失败，参数不正确")
	}

	this._cache = {};

}
List.defaults = {
	id: "id"
}
List.prototype = {
	constructor: List,

	_add: function(data){

		var $item = $.tmpl(this.tpl, data);
		var id = this.options.id;

		this.$list.append($item);

		if(id in data) {
			this._cache[data[id]] = $item;
		}
	},

	add: function(data){
		data = $.isArray(data) ? data : [data];
		for(var i = 0; i < data.length; i++){
			var $item = $.tmpl(this.tpl, data[i]);

			this.$list.append($item);

			if(this.options.id in data[i]) {
				this._cache[data[i][this.options.id]] = $item;
			}
		}
	},

	remove: function(id){
		if(id in this._cache) {
			this._cache[id].remove();
			delete this._cache[id];
		}
	},

	clear: function(){
		this.$list.empty();
		this._cache = {};
	},

	get: function(id){
		return this._cache[id] || null;
	},

	set: function(data){
		this.clear();
		// @Todo: 为了提高效率可能考虑直接拼接模板而不是调用 add 方法
		this.add(data);
	}
}

if(!localStorage.getItem("CLOUDURL")){
	localStorage.setItem("CLOUDURL","http://cloud.ibos.cn");
// 测试专用
	// localStorage.setItem("defaultID","1");
	// localStorage.setItem("defaultName","云端");
	// localStorage.setItem("netSetList",'{"1":{"id":"1","url":"http://uweboa.sinaapp.com","name":"云端"}}');
}

/**
* app

* @author Aeolus
* @copyright IBOS
*/
var app = (function(){
	var isLogin = false,
		isInit = false,
		appUrl = defaultUrl =  localStorage.getItem("defaultUrl"),
		user = core.getStorage("user"),
		uid	= localStorage.getItem("uid"),
		APPID = localStorage.getItem("APPID"),
		TOKEN = localStorage.getItem("TOKEN"),
		CLOUDURL = localStorage.getItem("CLOUDURL"),
		formHash = '',
		OS = 1,
		address ="";

	function init(){
		if($.os.android){ app.OS = 2 }			
		if($.os.iphone){ app.OS = 3 }					
		if($.os.ipad){ app.OS = 4 }
		if($.os.ieTouch){ app.OS = 5 }

		defaultUrl =  localStorage.getItem("defaultUrl");
		//初始化完整的路径
		app.appUrl = defaultUrl + "/?r=mobile";
		
		if(!app.isInit){
			app.isInit = true;
			UserNP = core.getStorage("defaultLogin");
			if(UserNP){
				$.jsonP({
					url: 		app.appUrl + '/default/login&callback=?&username='+UserNP.u+"&password="+UserNP.p,
					success: 	checkLogin,
					error: 		function(err){	console.log(err)	}
				});
			}
			if(!uid || !user){
				setTimeout(function(){$.ui.loadContent('login',false,false,'fade')},500);
			}
		}
	}

	function login(){
		var username = $("#username").val(),
			password = $("#password").val(),
			gps = $("#gpsInput").val(),
			address = $("#addressInput").val();

			if(defaultUrl==null){				
				$.ui.popup('请先设置地址');
				return;
			}
			$.ui.showMask("登录中...");
			if(core.getStorage("ibosUserData")!=""){
				_isset = false;
			}else{
				_isset = true;
			}
			if(localStorage.getItem("lastUrl")!=defaultUrl){
				_isset = false;
			}
			localStorage.setItem("lastUrl", defaultUrl);
			
						
		//以下登录换用了rpc
		// doLogin(username,password);		
		$.jsonP({
			url: 		app.appUrl + '/default/login&callback=?&username=' + username +'& password=' + password + '&gps=' + gps + '&address=' + address + '&issetuser=' + _isset,
			success: 	function(res){				
				core.setStorage("defaultLogin", {"u":username,"p":password});
				core.setStorage("lastUser", username);
				//$("#loginbtn").html('登录');
				$.ui.hideMask();
				checkLogin(res);
			},
			error: 		function(err){	
				$.ui.popup('服务器错误,请检查');
				//$("#loginbtn").html('登录');
				$.ui.hideMask();
				console.log(err);
			}
		});
	}
	//这是用rpc形式请求的登录
	// function doLogin(a,b){
		// var client = new HproseHttpClient(appUrl + "/api", ["login"]);
		// client.login(a,b, function(result) {
			// $.ui.loadContent('main',false,false,'fade');
		// });
		
	// }
	
	function logout(){
		$.jsonP({
			url: 		app.appUrl + '/default/logout&callback=?&formhash=' + formHash,
			success: 	checkLogin,
			error: 		function(err){	console.log(err) }
		});
		core.removeStorage("defaultLogin");
		core.removeStorage("user");
		core.removeStorage("uid");
		core.removeStorage("APPID");
		core.removeStorage("TOKEN");
		core.removeStorage("ibosUserData");
		$.ui.loadContent('login',false,false,'fade');
		window.location.reload();
	}
	
	function checkLogin(json){
		$.ui.hideMask();
		if(json.login==true){
			$.ui.loadContent('main',false,false,'fade');
			formHash = json.formhash;
			isLogin = true;
			app.user = json.user;
			app.uid = json.uid;
			app.APPID = json.APPID;
			app.TOKEN = json.TOKEN;
			app.CLOUDURL = json.CLOUDURL;
			localStorage.setItem("uid", app.uid);
			localStorage.setItem("APPID", app.APPID);
			localStorage.setItem("TOKEN", app.TOKEN);
			localStorage.setItem("CLOUDURL", app.CLOUDURL);
			core.setStorage("user", app.user);
			
			if(json.userData){
				userData = json.userData;
				core.setStorage("ibosUserData", json.userData);
			}
			getpush();
			appSdk.ready(getpush);
		}else{
			if(json.msg){
				$.ui.popup(json.msg);
				//appSdk.alert(json.msg);
			}
			$.ui.loadContent('login',false,false,'fade');
			console.log("lgoin fail");
		}
	}

	function getUserData(include){
		// 完整复制一份数据
		var userData = core.getStorage("ibosUserData");
		var ret;
		if(include && include.length) {
			// 将数组元素转为字符串格式
			ret = {
				group: {},
				datas: {}
			}
			// 过渡分组信息
			_.each(userData.group, function(v, k){
				// 取出数据交集
				var insec = _.intersection(v, include);
				if(insec && insec.length) {
					ret.group[k] = insec;
				}
			});

			_.each(userData.datas, function(v, k){
				if(include.indexOf(+k) !== -1) {
					ret.datas[k] = v
				}
			});
		} else {
			ret = userData;
		}
		return ret;
	}
	function getUser(uid){
		var userData = core.getStorage("ibosUserData");
		var datas = userData.datas;
		for(var i in datas) {
			if(datas[i].uid == uid) {
				return datas[i];
			}
		}
		return null;
	}
	function getUserName(ids){
		var argu = ids.split(",");
		var results = [];
		for(var i = 0; i < argu.length; i++){
			var user = getUser(argu[i]);
			if(user){
				results.push(user.realname);
			}
		}
		return results.join(",");
	}

	function getAvatar(uid, size){
		var ud = app.getUser(uid);
		if(ud) {
			return app.defaultUrl + "/" + ud["avatar_" + (size || "middle")];
		}
	}

	function getCustomBg(uid, size){
		var ud = app.getUser(uid);
		if(ud) {
			return app.defaultUrl + "/" + ud["bg_" + (size || "big")];
		}
	}

	return {
		isInit:		isInit,
		defaultUrl:	defaultUrl,
		appUrl:		appUrl,
		APPID: 		APPID,
		TOKEN: 		TOKEN,
		CLOUDURL: 	CLOUDURL,
		uid:		uid,
		user:		user,
		init:		init,
		login:		login,
		logout:		logout,
		checkLogin:	checkLogin,

		getUserData: getUserData,
		getUser: 	getUser,
		getUserName:getUserName,

		getAvatar: getAvatar,
		getCustomBg: getCustomBg,
		OS:			OS,
		address: 	address
	}
})();

//
(function(app){
	var _params = {};
	app.param = {
		get: function(key){
			return key == null ? _params : _params[key];
		},
		set: function(key, value){
			return _params[key] = value;
		},
		remove: function(key){
			delete _params[key];
		}
	}

})(app);

(function(){
	var _evts = {}

	$(document).on("click", "[data-evt]", function(){
		var evtName = $(this).attr("data-evt"),
			param = JSON.parse($(this).attr("data-param"));

		if(evtName && _evts[evtName]) {
			_evts[evtName].call(this, param, this);
		}
	})


	app.evt = {
		add: function(name, func){
			if(typeof name === "string") {
				_evts[name] = func;
			} else {
				$.extend(_evts, name)
			}
		},
		remove: function(name){
			var names = ((name || "").toString()).split(" ");
			for(var i = 0; i < names.length; i++) {
				_evts[name[i]] = null;
				delete _evts[name[i]];
			}
		},
		fire: function(name, param, elem) {
			return _evts[name] && _evts[name].call(null, param, elem);
		},
		all: function(){ console.log(_evts); return _evts; }
	}
})();

// 打开人员选择器
app.openSelector = function(settings){
	var panelId = "selector",
		containerId = "user_selector",
		defData;

	settings = settings || {};
	defData = settings.data || app.getUserData(settings.include);
	settings.onCancel = settings.onCancel || function(){
		$.ui.goBack();
	};
	var letterIns;

	$(document).one("loadpanel", function(){
		// 设置标题, 延时至头部动画效果执行完成后
		if(settings.title) {
			$("#header_selector h1", $.ui.header).html(settings.title);
		}

		$LAB.script("js/userselect.js", "js/letter.js")
		.wait(function(){
			var ulIns = new UserList(containerId, defData, settings);

			// 初始化字母索引功能
			letterIns = new Letter({ prefix: containerId + "_" });
			letterIns.$elem.hide();
			setTimeout(function(){
				letterIns.$elem.show()
			}, parseInt($.ui.transitionTime, 10))

			// $.query("#" + panelId).one("unloadpanel", function(){
			// 	letterIns.destory();
			// 	letterIns = null
			// })

			if(settings.onSelect) {
				$("#" + containerId).on("userselect", function(){
					settings.onSelect.apply(this, arguments)
				})
			}

			// 因为切页有动画过渡，需要延时绑定事件
			setTimeout(function(){

				// @Todo: 可能需要定义回调的参数
				$($.ui.header).find(".ao-cancel")
				.off("click.cancelSelector")
				.on("click.cancelSelector", settings.onCancel);
				
				if(settings.onSave) {
					$($.ui.header).find(".ao-ok").show()
					.off("click.saveSelector")
					.on("click.saveSelector", function(evt){
						// 回调 onSave
						settings.onSave(evt, { values:  ulIns.get() });
					})
				} else {
					$($.ui.header).find(".ao-ok").hide();
				}
			}, 300)
		});
	});
	$(document)
	.off("loadpanel.selector")
	.on("loadpanel.selector", "#" + panelId, function(){
		letterIns && letterIns.$elem.show();
	})
	.off("unloadpanel.selector")
	.on("unloadpanel.selector", "#" + panelId, function(){
		letterIns && letterIns.$elem.hide();
	})
	// cube, default, down, fade, flip, none, pop, slide, up
	$.ui.loadContent("view/selector/selector.html", 0, 0, "pop");
};

// 打开通用通讯录
app.openPhonebook = function(){
	app.openSelector({
		title: "通讯录",
		tpl: "<dd data-id='<%=uid%>'><img width='30' height='30' style='vertical-align: middle' src='<%= app.getAvatar(uid, 'small')%>'> <%=realname%></dd>",
		maxSelect: 1,
		onSelect: function(evt, data) {
			app.param.set("phonebookUid", data.id);
			$.ui.loadContent("view/phonebook/phonebook_view.html", 0, 0);
		}
	})
}

app.goHome = function(){
	if($.ui.history.length && $.ui.history[$.ui.history.length - 1].target == "#main"){
		$.ui.goBack();
	} else {
		$.ui.loadContent("#main", 0, 0);
		$.ui.clearHistory();
	}
	$.ui.hideMask();
}

app.ui = {
	alert: function(msg, cancel){
		$.ui.popup({
			id: "popup_alert",
		    suppressTitle: true,
		    message: msg,
		    cancelText: "确定",
		    cancelCallback: cancel,
		    cancelOnly: true
		});
	},
	confirm: function(msg, done, cancel){
		$.ui.popup({
			id: "popup_confirm",
		    suppressTitle: true,
		    message: msg,
		    cancelText: "取消",
		    cancelCallback: cancel,
		    doneText: "确定",
		    doneCallback: done,
		    cancelOnly:false
		});
	},
	prompt: function(msg, done, cancel){
		var tpl = '<p>' + (msg||"") + '</p><input type="text"/>'

		$.ui.popup({
			id: "popup_prompt",
		    suppressTitle: true,
		    message: tpl,
		    cancelText: "取消",
		    cancelCallback: cancel,
		    doneText: "确定",
		    doneCallback: function(popup){
		    	var val = $.query("#popup_prompt input[type='text']").val();
		    	done && done.call(null, val, popup);
		    },
		    cancelOnly:false
		});
	},
	fadeRemove: function(elem, time){
		elem = $(elem);
		time = time || 200;
		elem.css3Animate({
			opacity: 0,
			time: time,
			success: function(){
				elem.remove();
			}
		})
	},
	scrollTo: function(elem, time, fix){
		var panelId = $.ui.activeDiv.id,
			scorller = $.ui.scrollingDivs[panelId];
		if(!scorller) {
			return false;
		}
		time = time || 0;
		fix = fix || {};

		if (!$.is$(elem)) elem = $(elem);
        var newTop,itemPos,panelTop,itemTop;
        itemTop = elem.offset().top;
        newTop = itemTop - document.documentElement.scrollTop;
        panelTop = scorller.afEl.offset().top;
        if (document.documentElement.scrollTop < panelTop) {
            newTop -= panelTop;
        }
        newTop -= 4; //add a small space
		
		scorller.scrollBy({
            y: newTop + (fix.y || 0),
            x: 0 + (fix.x || 0)
        }, time);
	}
};
// Tip;
(function(){
	var Tip = function(options){
		this.options = $.extend({
			msg: ""
		}, options)
	}
	Tip.prototype.show = function(msg, theme){
		var tipContainer = $.query("#cm_tip").show(),
			tip = $.query(".tip", tipContainer[0]);
		setTimeout(function(){
			tip.html(msg || this.options.msg).addClass("active");
			theme && tip.addClass(theme);
		}, 100);
	}
	Tip.prototype.hide = function(){
		var tipContainer = $.query("#cm_tip"),
			tip = $.query(".tip", tipContainer[0]);
		tip.removeClass("active");
		setTimeout(function(){
			tipContainer.hide()
			tip.attr("class", "tip");
		}, 100);
	}

	var tipIns = new Tip();
	var hideTimer;
	app.ui.tip = function(msg, theme) {
		clearTimeout(hideTimer);
		tipIns.show(msg, theme);
		hideTimer = setTimeout(function(){
			tipIns.hide();
		}, 2000)
	}
})();

// Tab
(function(){
	var Tab = function(ct){
		var _this = this;
		this.$ct = $(ct);
		if(this.$ct && this.$ct.length) {
			this.$ct.on("click", "[data-node='tab']", function(){
				_this.show(this)
			});
		}
	}
	Tab.prototype.show = function(ctrl){
		var target;
		if(typeof ctrl === "string") {
			ctrl = this.$ct.find("[data-tab='" + ctrl + "']") 
		} else {
			ctrl = $(ctrl);
		}
		if(ctrl && ctrl.length) {
			target = ctrl[0].getAttribute("data-tab");
			ctrl.parent().addClass("active").siblings().removeClass("active");
			$(target).show().siblings("[data-node='tabPane']").hide();
		}
	}

	app.ui.tab = function(container) {
		return new Tab(container)
	}
})();

app.ui.bindScrollInfinite = function(panelId, listener) {
	var scroller;
	panelId = panelId || $.ui.activeDiv.id;
	listener = listener || $.noop;

	scroller = $.ui.scrollingDivs[panelId];
	scroller.addInfinite();
	
	$.bind(scroller, "infinite-scroll", listener);
	$.bind(scroller, "infinite-scroll-end", function(){
		if(this.infiniteTriggered) {
			this.clearInfinite();
		}
	})
}

/**
 * 向文本框光标处插入文本
 * @param  {String} text 文本
 * @return {[type]}      [description]
 */
$.fn.insertText = function(text) {
	this.each(function() {
		if (this.tagName !== 'INPUT' && this.tagName !== 'TEXTAREA') {
			return;
		}
		if (document.selection) {
			this.focus();
			var cr = document.selection.createRange();
			cr.text = text;
			cr.collapse();
			cr.select();
		} else if (this.selectionStart || this.selectionStart == '0') {
			var start = this.selectionStart, end = this.selectionEnd;
			this.value = this.value.substring(0, start) + text + this.value.substring(end, this.value.length);
			this.selectionStart = this.selectionEnd = start + text.length;
		} else {
			this.value += text;
		}
	});
	return this;
}


var MainList = function(list, options){
	this.list = list;
	this.options = options || {};

	this.currentCatid = this.options.catid || 0;
	this.currentPage = this.options.page || 1;
	this.options.url = this.options.url || app.appUrl;
	this._lastLoadParam = null;
}
MainList.prototype.load = function(param, callback){
	// 使用_lastLoadParam缓存上一次读取的参数，这一部分参数与currentCatid及currentPage有重复
	// 整理之后可以去掉
	var _this = this;
	param = param || {}
	if(param.catid == null) {
		param.catid = this.currentCatid;
	}
	if(param.page == null){
		if(param.catid === this.currentCatid) {
			param.page = this.currentPage
		} else {
			param.page = this.options.page || 1;
		}
	}

	$.ui.showMask();

	$.jsonP({
		url:       _this.options.url + "&callback=?&" + $.param(param),
		success:   function(res){
			_this.currentCatid = param.catid;
			_this.currentPage = param.page;
			_this.show(res);
			_this._lastLoadParam = param;
			callback && callback(res);
			$.ui.hideMask();
		},
		error:     core.error
	})
}

MainList.prototype.show = function(res) {
	var _this = this;
	// 如果是第一页之后的内容，则添加至列表底部
	if(this.currentPage > 1) {
		this.list.add(res.datas);
	// 否则重绘列表
	} else {
		this.list.set(res.datas);
	}
	// 页数大于当前页码时，显示加载更多
	if(this.$loadMore) {
		this.$loadMore.remove();
		this.$loadMore = null;
	}
	if(res.pages.pageCount > this.currentPage) {
		this.$loadMore = $('<li class="list-more"><a href="javascript:;">加载更多</a></li>');
		this.$loadMore.on("click", function(){
			_this.load($.extend({}, _this._lastLoadParam, { page: _this.currentPage + 1 }));
		});
		this.list.$list.append(this.$loadMore);
	}
};


// 一些全局初始化事件

$(document).ready(function(){
	var $doc = app.$doc = $(this),
		$body = app.$body = $("body");

	var $fixedDivs;

	// 文本框自动高度
	$doc.on("focusin input", "[data-auto-height]", function(){
		core.adjustTextarea(this)
	});

	// 侧栏菜单的处理，在加载panel时, 如果panel上有 data-nav="none" 值，则禁止菜单，否则开启菜单
	$doc.on("loadpanel", function(evt){
		var navId = evt.target.getAttribute('data-nav');
		if(navId && navId !== "none") {
			$.ui.enableSideMenu()
		} else {
			$.ui.disableSideMenu();
		}
		// 固定定位, 将页面内有指定节点抽离出来;
		// $fixedDivs = $.query('[data-node="fixedElem"]', evt.target);
		// if($fixedDivs.length) {
		// 	$fixedDivs.each(function(index, elem){
		// 		var $elem = $(elem);
		// 		$elem.attr("prevElem", elem.previousSibling);
		// 		$elem.appendTo($.query("#afui"));
		// 	})
		// }
	})
	.on("unloadpanel", function(evt){
		// 还原因固定定位抽离出来的节点
		if($fixedDivs && $fixedDivs.length) {
			$fixedDivs.each(function(index, elem){
				var $elem = $(elem),
					prevElem = $elem.attr("prevElem");
				if(prevElem) {
					$elem.insertAfter(prevElem);
				} else {
					$elem.remove();
				}
				$elem.removeAttr("prevElem");
			})
			$fixedDivs = null;
		}
	});
});

