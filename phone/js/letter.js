var Letter = function(options) {
	var $container = $.ui.viewportContainer;
	this.$elem = $.query("#letter");
	// 没有字母索引节点时，则创建
	if(!this.$elem.length) {
		this.$elem = $.create("div", {
			id: "letter",
			className: "letter"
		}).appendTo($container);
		this._createHash();
		this._bindEvent();
	}

	this.options = $.extend({}, Letter.defaults, options);
	this.hash = "";
	this.letterInfo = [];
	
	this.$elem.css({
		top: $.ui.header.offsetHeight + this.options.top,
		bottom: $.ui.navbar.offsetHeight + this.options.bottom
	})

}
Letter.defaults = {
	top: 5,
	bottom: 5,
	prefix: ""
}
Letter.prototype = {
	constructor: Letter,

	_createHash: function() {
		// 生成通用匹配节点#
		this.$elem.append('<a data-hash="#">#</a>');

		// 生成A-Z节点，65-90 为大写字母Unicode
		for (var i = 65; i <= 90; i++) {
			var ca = String.fromCharCode(i);
			this.$elem.append('<a data-hash="' + ca + '">' + ca + '</a>');
		}
	},

	// 此函数用于刷新字母位置信息，当屏幕大小变化时或字母位置变化时，应刷新信息。
	_refreshLetterInfo: function() {
		var that = this;
		this.letterInfo.length = 0;

		this.$elem.find("a").each(function() {

			var offset = $(this).offset();

			that.letterInfo.push({
				hash: this.getAttribute("data-hash"),
				from: offset.top - document.body.scrollTop,
				to: offset.bottom - document.body.scrollTop
			});

		});
	},

	_bindEvent: function() {
		var that = this,
			$doc = $(document),
			// 字母拖拽定位是否已开始
			_hasStartDrag = false,
			// 侧栏菜单是否可用
			_slideMenuEnable = $.ui.isSideMenuEnabled();		

		// 字母滑动开始
		this.$elem.on("touchstart.letter", function(evt) {
			var hash;
			// 每次开始拖拽时，都重新计算各字母的位置
			that._refreshLetterInfo();
			that.$elem.addClass("active");
			
			_hasStartDrag = true;

			hash = evt.target.getAttribute("data-hash");
			if(hash && hash !== this.hash) {
				// 定位
				that.setHash(hash);
				// 显示字符提示框
				that.showBox(hash.charAt(hash.length - 1));
			}
			// 当侧栏菜单位于可用状态时，需要禁用
			_slideMenuEnable && $.ui.disableSideMenu(); 
		});

		$doc.on("touchmove.letter", function(evt) { //mousemove.letter 
			var y;
			// 尚未开始字母拖拽时，就算进入了touchmove事件，也应该让其直接返回
			if(!_hasStartDrag) {
				return true;
			}
			// 获取touch事件的页面y坐标
			y = evt.y || (evt.changedTouches && evt.changedTouches[0].clientY);

			// 遍历计算，当触摸y坐标，在字母范围内时，滚动定位
			for (var i = 0; i < that.letterInfo.length; i++) {
				var info = that.letterInfo[i];
				if (y > info.from && y < info.to) {
					if(info.hash !== this.hash) {
						// 定位
						that.setHash(info.hash);
						// 显示字符提示框
						that.showBox(info.hash.charAt(info.hash.length-1))
					}
				}
			}
		// 字母拖拽完成
		}).on("touchend.letter", function(evt) {
			// 隐藏字符提示框
			that.hideBox();
			that.$elem.removeClass("active");
			// 重置开始状态
			_hasStartDrag = false;
			// 侧栏菜单可用时，应还原可用状态
			_slideMenuEnable && $.ui.enableSideMenu()
			
		});
	},
	/**
	 * 滚动到hash值地应的y轴坐标
	 * @param {[type]} hash [description]
	 */
	setHash: function(hash) {
		var $node;
		if (hash !== this.hash) {
			$node = $.query("#" + this.options.prefix + hash);
			if($node.length){	
				// 调动scroller对象的scrollToItem方法
				$.ui.scrollingDivs[$.ui.activeDiv.id].scrollToItem($node);
				this.hash = hash;
			}

		}
	},

	showBox: function(content){
		if(!$.is$(this.$box) || !this.$box.length){
			this.$box = $("<div class='letter-box'></div>").appendTo(document.body);
		}
		this.$box.html(content);
	},

	hideBox: function(){
		this.$box && this.$box.remove();
		this.$box = null;
	},

	destory: function() {
		$(document).off(".letter");
		this.$elem.off(".letter");
		if(this.$box){
			this.$box.remove();
			this.$box = null;
		}
		this.$elem.remove();
		this.$elem = null;
	}
}

// app.letter = {
// 	ins: null,
// 	on: function(prefix) {
// 		prefix = prefix || "";
// 		this.ins = new Letter({
// 			prefix: prefix
// 		});
// 	},
// 	off: function() {
// 		this.ins.destory();
// 		this.ins = null;
// 	},

// 	init: function(panel, prefix) {

// 		var that = this;
// 		if (!this.ins) {
// 			$("#" + panel)
// 			.off("loadpanel unloadpanel")
// 			.on("loadpanel", function(){
// 				that.on(prefix) 
// 			})
// 			.on("unloadpanel", function(){ 
// 				that.off();
// 			})
// 		}
// 	}
// }