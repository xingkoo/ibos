/**
 * organization.js
 * 组织架构模块通用JS
 * IBOS
 * @module		Global
 * @submodule   Organization
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-02 
 */


// DataCard
var DataCard = function($context, options) {
	var markSelector = "[data-mark]";
	this.$context = $context;
	this.options = $.extend({}, DataCard.defaults, options);
	this.inputs = $context.find(markSelector);
	DataCard.instances.push(this);
}
DataCard.defaults = {
	prefix: "dep_data_"
}
DataCard.instances = [];

DataCard.prototype = {
	constructor: DataCard,
	show: function(callback) {
		// 当前实例显示时，其它实例隐藏，两者是互斥的
		for(var i = 0, len = DataCard.instances.length; i < len; i++){
			DataCard.instances[i] !== this && DataCard.instances[i].hide();
		}
		this.$context.animate({
			"right": 0
		}, 200, callback);
	},
	hide: function(callback) {
		var posRight = this.$context.outerWidth();
		this.$context.animate({
			"right": -posRight
		}, 200, callback);
	},
	// data-mark 用来标识一些会被动态修改值的表单控件，值与返回数据对象的属性名一一对应
	insertToForm: function(data) {
		var that = this;
		var inputs = this.inputs;
		for (var mark in data) {
			if (data.hasOwnProperty(mark)) {
				inputs.filter("[data-mark='" + mark + "']").each(function() {
					var $el = $(this), newVal = data[mark];
					// 开关
					if ($el.is("[data-toggle='switch']")) {
						var act = newVal === '1' ? "turnOn" : "turnOff";
						$el.iSwitch(act);
						// 用户选择器
					} else if ($el.is("[data-toggle='userSelect']")) {
						var val = newVal ? newVal.split(",") : [];
						$el.userSelect("setValue", val);
					} else {
						$el.val(newVal);
					}
				});
			}
		}
		that.$context.stopWaiting();
	},
	loadData: function(id) {
		var $context = this.$context,
			dataId = this.options.prefix + id,
			depData, 
			that = this;

		this.$context.waiting(null, "normal");
		if (typeof id !== "undefined") {
			$.get(Ibos.app.url('organization/department/index', {op: 'get', id: id, random: Math.random()}), function(res){
				that.insertToForm(res);
			}, "json");
		}
	},
	clearForm: function(callback) {
		var data = this.getData();
		for (var mark in data) {
			if (data.hasOwnProperty(mark)) {
				data[mark] = undefined;
			}
		}
		this.insertToForm(data);
		callback && callback();
	},
	getData: function() {
		var inputs = this.inputs,
				data = {};
		inputs.each(function() {
			var $el = $(this),
				mark = $(this).attr("data-mark");
			// 开关
			if ($el.is("[data-toggle='switch']")) {
				data[mark] = $el.prop("checked") ? "1" : "0";
			} else {
				data[mark] = $el.val();
			}
		});
		return data;
	},
	setData: function(id, data) {
		if (id) {
			var dataId = this.options.prefix + id;
			Ibos.local.set(dataId, data)
		}
	},
	removeData: function(id) {
		var dataId = this.options.prefix + id;
		Ibos.local.remove(dataId);
	},
	refreshData: function(id) {
		this.setData(id, this.getData())
	},
	waiting: function(isShow){
		if(isShow){
			this.$context.waitingC();
		} else {
			this.$context.stopWaiting();
		}
	}
};

// PrivilegeLevel
(function() {
	var PrivilegeLevel = function($element, options) {
		this.$element = $element;
		this.options = $.extend({}, PrivilegeLevel.defaults, options);
		this.value = this.options.value || $element.val() || 0;
		// this.value = parseInt(value, 10);
		this.text = this.options.text || $element.attr("data-text") || "";
		this.disabled = this.$element.prop("disabled");
		this._init();
	}
	PrivilegeLevel.prototype = {
		constructor: PrivilegeLevel,
		_init: function() {
			this.$element.hide();
			this._build();
		},
		_build: function() {
			var $anchor = $("<a class='privilege-level' href='javascript:;'><i></i><p></p></a>");
			this.$anchor = $anchor.insertBefore(this.$element);
			this._setLevel(this.value);
			this.setText(this.text);
			this._bindEvent();
			if (this.disabled) {
				this.setDisabled();
			}
		},
		_bindEvent: function() {
			var that = this;
			this._unbindEvent();
			this.$anchor.on("click.level", function() {
				if (that.value == 8) {
					that.setValue(0);
				} else if (that.value == 0) {
					that.setValue(1);
				} else {
					that.setValue(that.value * 2);
				}
			})
		},
		_unbindEvent: function() {
			this.$anchor.off(".level");
		},
		setValue: function(value) {
			// @Debug
			// console && console.assert((typeof value === "number"), "(Level.setLevel): typeof value must be number");
			if (!this.disabled) {
				this.$element.val(value);
				this._setLevel(value);
				this.value = value;
				this.$element.trigger("valuechange", {value: value})
			}
		},
		_setLevel: function(value) {
			// @Debug
			// console && console.assert((typeof value === "number"), "(Level.setLevel): typeof value must be number")
			var cls = "";
			if (value) {
				cls += "level" + value
			}
			this.$anchor.find("i").attr("class", cls);
		},
		setText: function(text) {
			this.$anchor.find("p").html(text)
		},
		setDisabled: function() {
			this._unbindEvent();
			this.disabled = true;
			this.$element.prop("disabled", true);
			this.$anchor.addClass("disabled");
		},
		setEnabled: function() {
			this._bindEvent();
			this.disabled = false;
			this.$element.prop("disabled", false);
			this.$anchor.removeClass("disabled")
		}
	}
	$.fn.privilegeLevel = function(options) {
		var argu = Array.prototype.slice.call(arguments, 1);
		return this.each(function() {
			var $el = $(this),
					data = $el.data("privilegeLevel");
			if (!data) {
				$el.data("privilegeLevel", data = new PrivilegeLevel($el, options))
			} else if (typeof options === "string") {
				data[options].apply(data, argu);
			}
		})
	}
})();



var Organization = {
	auth: {
		selectMod: function(pid, status){
			status = status === false ? false : true;
			$("#limit_setup").find("[data-node='funcCheckbox'][data-pid='" + pid + "']")
			.prop("checked", status)
			.trigger("change");
		},

		selectCate: function(pid, status){
			status = status === false ? false : true;
			$("#limit_setup").find("[data-node='modCheckbox'][data-pid='" + pid + "']")
			.prop("checked", status)
			.trigger("change");
		}
	}
};
Organization.DeptTable = {
	getRow: function(id) {
		return $('tr[data-id="' + id + '"]');
	},
	getParent: function(id) {
		var $row = this.getRow(id),
			pid = $row.attr("data-pid");
		return this.getRow(pid);
	},
	getChildrens: function(id) {
		return $('tr[data-pid="' + id + '"]');
	},
	getPrev: function(id) {
		var $row = this.getRow(id),
			pid = $row.attr("data-pid"),
			$prev = $row.prev();
		for (; $prev && $prev.length;) {
			if ($prev.attr("data-pid") !== pid) {
				if ($prev.attr("data-id") === pid) {
					break;
				}
				$prev = $prev.prev();
			} else {
				return $prev
			}
		}
	},
	getNext: function(id) {
		var $row = this.getRow(id),
			pid = $row.attr("data-pid"),
			$next = $row.next();
		for (; $next && $next.length;) {
			if ($next.attr("data-pid") !== pid) {
				if ($next.attr("data-id") === pid) {
					break;
				}
				$next = $next.next();
			} else {
				return $next
			}
		}
	},
	getDescendants: function(id) {
		var that = this,
			result = $(),
			_push = function($elems) {
				$elems.each(function() {
					var $elem = $(this);
					var $childs = that.getChildrens($elem.attr("data-id"));
					result = result.add($elem);
					if ($childs && $childs.length) {
						_push($childs)
					}
				})
			}
		_push(that.getChildrens(id))
		return result;
	},
	getDescendantsWithSelf: function(id) {
		return this.getRow(id).add(this.getDescendants(id));
	}
};



// 权限级别
(function(){

	var tip = {
		'0': U.lang("ORG.POWERLESS"),
		'1': U.lang("ORG.ME"),
		'2': U.lang("ORG.AND_SUBORDINATE"),
		'4': U.lang("ORG.CURRENT_BRANCH"),
		'8': U.lang("ORG.ALL")
	}
	$(function(){
		$("[data-toggle='privilegeLevel']").each(function(){
			var $elem = $(this),
				ins,
				title;

			$elem.privilegeLevel();
			ins = $.data(this, "privilegeLevel");
			title = tip[ins.value];

			ins.$anchor.tooltip({
				title: title,
				trigger: "hover"
			}).on("click", function(){
				var insTooltip = $.data(this, "tooltip");
				insTooltip.options.title = tip[$elem.val()];
				insTooltip.show();
				$(this).closest("label").find('[data-node="funcCheckbox"]').prop("checked", true).trigger("change");
			});
		});
	});
})();


// 岗位成员列表
Organization.memberList = (function(){
	// 根据ID从Ibos.data中获取相关信息，包括图像地址，所属部门及用户名
	var _getUserData = function(id){
		var userData,
			deptData,
			results;
		if(Ibos.data && typeof id !== "undefined"){
			userData = Ibos.data.getItem(id)[0];
			deptData = Ibos.data.getItem(userData.department)[0];
			results = {
				id: id,
				imgurl: userData.imgUrl || "",
				user: userData.name || "",
				department: (deptData && deptData.name) || ""
			}
		}
		return results||{};
	}


	// 值管理
	var valueManager = function(values){
		// 必须为Array
		if(!$.isArray(values)){
			values = [];
		}
		var _add = function(id, callback){
			// 已存在Id时返回
			if ($.inArray(id, values) === -1) {
				values.push(id);
				if($.isFunction(callback)){
					callback(id);
				}
			}
		};
		var _remove = function(id, callback){
			// 已存在Id时返回
			var index = $.inArray(id, values);
			if (index !== -1) {
				values.splice(index, 1);
				if($.isFunction(callback)){
					callback(id);
				}
			}
		}

		return {
			add: function(ids, callback) {
				ids = $.isArray(ids) ? ids : [ids];
				for(var i = 0; i < ids.length; i++) {
					_add(ids[i], callback);
				}
			},
			remove: function(ids, callback){
				ids = $.isArray(ids) ? ids : [ids];
				for(var i = 0; i < ids.length; i++) {
					_remove(ids[i], callback);
				}
			},
			get: function(){ return values.join(",") }
		}
	};

	var init = function(values){
		var member = valueManager(values);
		var $list = $("#org_member_list"),
			$add = $("#org_member_add"),
			$box = $("#member_select_box"),
			$value = $("#member"),
			member_tpl = "org_member_tpl",
			memberBox,
			menu;
		// 改变视图，同步更新表单对应控件的值
		var addMember = function(id){
			var data = _getUserData(id);
			$.tmpl(member_tpl, data).insertBefore($add);
			$value.val(member.get());
		}
		var removeMember = function(id){
			$("#member_" + id).remove();
			$value.val(member.get());
		}

		$box.selectBox({
			data: Ibos.data && Ibos.data.get(),
			type: "user",
			values: [].concat(values)
		});


		memberBox = $box.data("selectBox");
		$(memberBox).on("slbchange", function(evt, data){
			if (data.checked) {
				member.add(data.id, function(id){
					addMember(id)
				});
			} else {
				member.remove(data.id, function(id){
					removeMember(id)
				});
			}
			menu.show();
		});

		menu = new Ui.PopMenu($add, $box, {
			trigger: "click",
			position: {
				at: "center",
				my: "center",
				of: document.body
			},
		});
		// 打开选人框时即时刷新人员列表
		$box.on("show", function(){
			memberBox.refreshList();
		})

		// 移除成员
		$list.on("click", "[data-act='removeMember']", function(){
			var id = $.attr(this, "data-id");
			member.remove(id, function(id){
				removeMember(id);
				memberBox.removeValue(id);
			});
		})
	}

	return {
		init: init
	}
})();

