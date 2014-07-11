/**
* 这是选人框的框
*
*/
var User = function(id, values, options) {
	var elem;
	if (id in this.constructor.instances) {
		return this.constructor.instances[id];
	}
	elem = document.getElementById(id);
	if (!elem || !elem.nodeName || !elem.nodeName) {
		throw new Error("User: elem 参数必须为ul节点");
	}
	this.$elem = $(elem);
	options = this.options = $.extend({}, User.defaults, options);

	// 将表单域中的值按格式还原出来
	var _toValue = function(selector, values, type){
		var $input = $(selector);
		if($(selector).length) {
			var dv = $(selector).val();
			dv.split(",").forEach(function(v){
				if(v) {
				values.push({
					id: v,
					text: app.getUserName(v),
					type: type || null
				})
				}
			})
		}
	}
	var _toInput = function(selector, values, type){
		var res = [];
		values.forEach(function(v){
			v.type == type && res.push(v.id);
		})
		$(selector).val(res.join(","));
	}
	// 如果设置默认values
	if(values && $.isArray(values)) {
		this.values = values.slice();
	// 否则尝试从关联表单控件中获取
	} else {
		this.values = [];
		if(options.input){
			_toValue(options.input, this.values, null);
			// 值改变时同步至表单控件
			this.$elem.on("userchange", function(evt, data){
				_toInput(options.input, data.values, null);
			})
		}
		if(options.inputs) {
			for(var type in options.inputs) {
				var selector = options.inputs[type];
				_toValue(selector, this.values, type);
				// 值改变时同步至表单控件
				this.$elem.on("userchange", function(evt, data){
					_toInput(selector, data.values, type);
				})
			}
		}
	}

	this.init();
	this.constructor.instances[id] = this;
};
User.instances = {};
User.get = function(id){
	return User.instances[id] || null;
}

User.defaults = {
	editable: true // 选项是否支持直接点击删除
};

User.prototype = {
	constructor: User,
	init: function() {
		var that = this;

		// 当没有user-selecor-list 样式类时，给其添加上
		this.$elem.addClass("user-selector-list");

		this.set(this.values.slice(), true);
		// 
		if (this.options.editable) {
			this.$elem.on("click", "li", function() {
				var elem = this;
				setTimeout(function() {
					that._remove(elem.getAttribute("data-id"))
				}, 0);
			})
		}
	},

	_add: function(value, silent) {
		var item;
		if (typeof value === "object" && "id" in value) {
			// 创建新节点
			item = document.createElement("li");
			item.setAttribute("data-id", value.id);
			item.id = this.$elem.get(0).id + value.id;

			value.text && (item.innerHTML = value.text);
			value.type && (item.className = value.type);

			this.$elem.append(item);
			this.values.push(value);
			if(!silent) {
				this.$elem.trigger("user:add", value);
				this._triggerChange();
			}
		}
	},

	add: function(values, silent) {
		// 判断如果为数组则循环添加，否则直接添加
		if ($.isArray(values)) {
			for (var i = 0; i < values.length; i++) {
				this._add(values[i], silent);
			}
		} else {
			this._add(values, silent);
		}
	},

	_remove: function(id, silent) {
		for (var i = 0; i < this.values.length; i++) {
			if (this.values[i].id == id) {
				var value = this.values[i];
				this.values.splice(i, 1);
				// $("[data-id='" + id + "']", this.$elem).remove();
				$("#" + this.$elem.get(0).id + id).remove();
				if(!silent) {	
					this.$elem.trigger("user:remove", value);
					this._triggerChange();
				}
				return;
			}
		}
	},

	remove: function(ids, silent) {
		var idarr;
		// 如果为字符串，则判断是否期待移除多个值
		if (typeof ids === "string") {
			// 以“,”作为分隔符
			idarr = ids.split(",")
			// 只有当分隔后大于两个值时，进入循环，否则，当作传入一个值处理
			if (idarr.length > 1) {
				for (var i = 0; i < idarr.length; i++) {
					this._remove(idarr[i], silent);
				};
				return;
			}
		}

		this._remove(ids, silent);
	},

	clear: function(silent) {
		this.$elem.empty();
		this.values.length = 0;
		if(!silent){
			this._triggerChange();
		}
	},

	set: function(values, silent) {
		// 设置值会先清空原有值
		this.clear(true);
		this.add(values, true);
		if(!silent) {
			this._triggerChange();
		}
	},

	get: function() {
		return this.values;
	},
	/**
	 * 使用Id获取条目信息
	 * @param  {String} id 条目Id
	 * @return {Object}    信息
	 */
	getById: function(id) {
		for (var i = 0; i < this.values.length; i++) {
			if (this.values[i].id === id) {
				return this.values[i];
			}
		}
		return null;
	},
	/**
	 * 获取选中项的ID值，返回以“,”分隔的字符串
	 * @return {String} ID值
	 */
	getIds: function() {
		var ids = "",
			len = this.values.length;
		if (len) {
			for (var i = 0; i < len; i++) {
				ids += this.values[i].id + ((i !== len - 1) ? "," : "");
			}
		}
		return ids;
	},

	_triggerChange: function() {
		var values = this.values;
		this.$elem.trigger("userchange", { values: values });
	},

	destory: function(){
		for(var i in User.instances) {
			if(User.instances[i] === this) {
				delete User.instances[i];
			}
		}
	}
}

/**
 * 用于初始化用户列表
 * @param {String} id 		容器节点Id
 * @param {Object} data     用于生成列表的节点，格式 { datas: { 1: {}, 2: {}}, group: { g: {} }}
 * @param {Object} options  配置项
 * @param {Array}  options.buttons 自定义按钮，格式 [{text: "button", type: "button"}]
 */
var UserList = function(id, data, options) {
	var me = this;
	// if (id in this.constructor.instances) {
	// 	return this.constructor.instances[id];
	// }
	this.id = id || "user_list";
	this.data = data || {};
	this.data.group = this.data.group || {};
	this.data.datas = this.data.datas || {};

	this.options = $.extend({
		tpl: "<dd data-id='<%=uid%>'><span class='ckb'></span><%=realname%></dd>"
	}, options);


	// 用于放置选中项，格式为[{id:1, text: "1", type: "haha"}];
	this.selected = [];
	// 对数据进行缓存
	this._cache = {};

	this.$elem = $("#" + this.id);
	this._build();
	this._bindEvent();

	this.constructor.instances[id] = this;

	if(this.options.values) {
		this.set(this.options.values);
	}
}
UserList.instances = {};
UserList.get = function(id){
	return UserList.instances[id] || null;
}

UserList.prototype = {
	constructor: UserList,
	/**
	 * 创建列表
	 * @method _createList
	 * @return {[type]} [description]
	 */
	_build: function() {
		var userids,
			group = this.data.group,
			datas = this.data.datas;

		this.$list = $("<dl class='userlist'></dl>");

		// 循环每字母组
		for (var letter in group) {
			if(group.hasOwnProperty(letter)){			
				this.$list.append("<dt id='" + this.id + "_" + letter + "'>" + letter + "</dt>");
				userids = group[letter];
				// 循环字母组内的成员
				for (var i = 0; i < userids.length; i++) {
					// 过滤
					if(!this.options.filter || this.options.filter.call(this, datas[userids[i]])) {
						this.$list.append(this._createItem(datas[userids[i]]));
					}
				}
			}
		};

		this.$elem.append(this.$list);
	},
	/**
	 * 创建列表项
	 * @method _createItem
	 * @param  {Object} data 列表项数据，格式 {uid: 1, realname: "abc"}
	 * @return {Object}      列表项$.afm节点
	 */
	_createItem: function(data) {
		var $node = $.tmpl(this.options.tpl, data);
		var $buttons = this._createButtons();

		$node[0].id = this.id + "_item_" + data.uid;

		if($.is$($buttons) && $buttons.length){
			$node.prepend($buttons);
		}

		// 将数据存入缓存
		this._cache[data.uid] = {
			elem: $node,
			text: data.realname
		}

		return $node;
	},
	/**
	 * 创建按钮及按钮层
	 * @method _createButtons
	 * @return {Object} 按钮层$.afm节点
	 */
	_createButtons: function() {
		var buttons = this.options.buttons,
			$wrap;
		if (buttons && $.isArray(buttons)) {
			$wrap = $("<div class='userlist-operate'></div>");
			for (var i = 0; i < buttons.length; i++) {
				$wrap.append("<a href='javascript:;' class='" + buttons[i].type + "' data-type='" + buttons[i].type + "'>" + buttons[i].text + "</a>");
			}
		}
		return $wrap;
	},

	_bindEvent: function() {
		var that = this;
		this.$elem.on("click.userlist", "dd", function(evt) {
			var elem = this,
				id = this.getAttribute("data-id"),
				type = evt.target.getAttribute("data-type");

			var info = that.getInfo(id);
			if (info && info.type === type) {
				that.unselect(id);
			} else {
				if(that.options.maxSelect && that.options.maxSelect > 0 && that.selected.length >= that.options.maxSelect){
					that.unselect(that.selected[that.selected.length - 1].id);
				}
				that.select(id, type);
			}
		})
	},

	_unbindEvent: function() {
		this.$elem.off(".userlist")
	},

	/**
	 * 获取Id对应的$.afm节点
	 * @method _getItem
	 * @param  {String} id  列表项id
	 * @return {Object}     列表项$.afm节点
	 */
	_getItem: function(id) {
		return this._cache[id].elem;
	},
	/**
	 * 获取Id对应的文本
	 * @method _getText
	 * @param  {String} id 列表项id
	 * @return {String}    id对应的文本
	 */
	_getText: function(id) {
		return this._cache[id].text;
	},
	/**
	 * 根据id从选中列表中取出信息
	 * @method getInfo
	 * @param  {String} id 列表项id
	 * @return {Object}    对应信息
	 */
	getInfo: function(id) {
		var index = this.indexOf(id);
		if (index === -1) {
			return null;
		}
		return this.selected[index];
	},
	/**
	 * 查询id是否已被选中，选中时返回在数据中的索引，否则返回-1
	 * @method indexOf
	 * @param  {String} id 列表项id
	 * @return {Number}    索引
	 */
	indexOf: function(id) {
		for (var i = 0; i < this.selected.length; i++) {
			if (this.selected[i].id === id) {
				return i;
			}
		}
		return -1;
	},

	_select: function(id, type) {
		var text = this._getText(id),
			data = {
				id: id,
				type: type,
				text: text
			};
		this.selected.push(data);
		this.$elem.trigger("userselect", data);
	},

	/**
	 * 选中某一列表项
	 * @method select
	 * @param  {String} id   要选中的列表项id
	 * @param  {String} type 选中的类型
	 * @return {[type]}      [description]
	 */
	select: function(id, type) {
		var that = this;
		setTimeout(function() {

			var info = that.getInfo(id);
			if (!info) {

				that._getItem(id).addClass("checked" + (type ? " " + type : ""));
				that._select(id, type);

			} else {

				if (info.type !== type) {
					that._getItem(id).removeClass(info.type).addClass(type);
					info.type = type;
				}
			}

		}, 0)
	},

	_unselect: function(id) {
		var index = this.indexOf(id);
		if (index !== -1) {
			this.selected.splice(index, 1);
			this.$elem.trigger("userunselect");
		}
	},

	/**
	 * 取消某一列表项的选中状态
	 * @method unselect
	 * @param  {String} id 要取消选中的列表项id
	 * @return {[type]}    [description]
	 */
	unselect: function(id) {
		var that = this,
			info = this.getInfo(id);

		setTimeout(function() {

			if (info) {
				that._getItem(id).removeClass("checked" + (info.type ? " " + info.type : ""));
				that._unselect(id);
			}

		}, 0)
	},

	/**
	 * 获取选中项
	 * @method	get
	 * @return {Array} 选中项数组
	 */
	get: function() {
		return this.selected;
	},

	clear: function() {
		var that = this,
			selected = this.selected,
			info;

		setTimeout(function() {

			if (selected.length) {

				for (var i = 0; i < selected.length; i++) {

					info = that.getInfo(selected[i].id);
					that._getItem(selected[i].id).removeClass("checked" + (info.type ? " " + info.type : ""));

				}
			}
			that.selected.length = 0;

		}, 0)
	},

	set: function(values) {

		this.clear();
		if (values && values.length) {
			for (var i = 0; i < values.length; i++) {
				this.select(values[i].id, values[i].type);
			}

		}
	},

	destory: function() {
		this._unbindEvent();
		this.$elem.remove();
		delete this.constructor.instances[this.id];
	}
};

// (function(){
// 	var selectorSettings = {
// 		'common': {
// 			panelId: 'selector',
// 			userId: 'common_user',
// 			userListId: 'user_selector'
// 		},
// 		'phonebook':{
// 			panelId: 'phonebook',
// 			userId: 'pb_user',
// 			userListId: 'phonebook_content'
// 		}
// 	}
// 	app.userSelector = {
// 		user: "",
// 		userListId: "",
// 		panelId: "",

// 		get: function(type) {
// 			var settings = selectorSettings[type],
// 				that = this,
// 				$panel;
// 			if(settings) {
// 				var user = new User(settings.userId),
// 					userValue = user.get();

// 				$panel = $("#" + settings.panelId);
// 				$panel.off(".getuser").on("loadpanel.getuser", function(){
// 					var userlist = new UserList(settings.userListId, app.getUserData(), settings.userListSettings);
// 					userlist.set(userValue);
			
// 					that.panelId = settings.panelId;
// 					that.user = user;
// 					that.userlist = userlist;
// 				});


// 				$.ui.loadContent("#" + settings.panelId);
// 			}
// 		},

// 		save: function(){
			
// 			var userlist = this.userlist,
// 				user = this.user;

// 			if(user && userlist) {
// 				user.set(userlist.get());
// 				this.userId = this.userListId = this.panelId = "";
// 				$.ui.goBack();
// 			}
// 		}
// 	}

// 	app.selectOneUser = function(callback){
// 		var settings = selectorSettings['common'];
// 		var list;

// 		$("#" + settings.panelId).on("loadpanel", function(){

// 			list = new UserList(settings.userListId, app.getUserData());
// 			$("#" + settings.userListId).off("userlist:select").on("userlist:select", function(evt, data){
				
// 				callback && callback(data);
// 				list.clear();
// 				// sms.addSms(data);
// 				// $.ui.loadContent("#sms_view");

// 			});
// 		});

// 		$.ui.loadContent("#" + settings.panelId);
// 	}
// })();
