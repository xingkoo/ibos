var Slide = (function(){
	var id = 0;
	var Slide = function(ct, opt){
		opt = $.extend(Slide.defaults, opt);

		if(typeof ct === "string" && ct.indexOf("#") !== 0) {
			ct = "#" + ct;
		}
		ct = $(ct);

		var slideId = ct[0].id || "slide_" + id++;
		Slide.instants[slideId] = this;

		if(!ct.length) {
			return false;
		}
		var _this = this;
		var opened = null;
		var touch = {};
		var sideMenuEnabled = false;
		// 当开始滚动时不允许滑动， 反之亦然。
		var status; // 标识当前状态，["", "scroll", "slide"]

		function getItem(node) {
			if(node === ct.get(0)) {
				return null;
			}
			if(!('tagName' in node) || !$(node).is(opt.selector)) {
		    	return getItem(node.parentNode);
			}
			return node;
		};

		$("#afui").on("touchstart", function(e){
			var ignore = $(e.target).closest("[slide-ignore]").get(0);
			if(ignore) return true;
			if(opened) {
	        	_this.close(opened, true);
	        }
		})
		ct.bind("touchstart", function(e){

			if(!opt.enable) return true;
			e = e.originalEvent || e;
	        if(!e.touches||e.touches.length===0) return true;


	        touch.x1 = e.touches[0].pageX;
	        touch.y1 = e.touches[0].pageY;

	        touch.el = $(getItem(e.touches[0].target));
	        if(!touch.el.length) return true;

	        ct.trigger("slidestart", touch);
	        
	        sideMenuEnabled = $.ui.isSideMenuEnabled();
	        if(sideMenuEnabled) {
	        	$.ui.disableSideMenu();
	        }
	        status = "";
		})
		.bind("touchmove", function(e){
			if(!opt.enable || status === "scroll") return true;
			e = e.originalEvent || e;
			if(!touch.el.length) return true;

			touch.x2 = e.touches[0].pageX;
			touch.y2 = e.touches[0].pageY;

			if(status == "") {
				if (Math.abs(touch.x2 - touch.x1) < 5) return true;
				// 滚动
				if (Math.abs(touch.y2 - touch.y1) > Math.abs(touch.x2 - touch.x1) || Math.abs(touch.y2 - touch.y1) > 100) {
					status = "scroll";
					return true;
				}
			}

			status = "slide";
			// 在展开状态时， 向右滑动至原位置
			// setTimeout(function(){
	        	ct.trigger("sliding ", touch);
				if(touch.el.attr("data-slide")) {
					if(touch.x1 < touch.x2 && touch.x2 - touch.x1 <= opt.distance) {
						touch.el.css3Animate({ x: touch.x2 - touch.x1 - opt.distance});
					}
				//向左滑动
				} else {
					if(touch.x1 > touch.x2 && touch.x1 - touch.x2 <= opt.distance) {
						touch.el.css3Animate({ x: touch.x2 - touch.x1});
					}
				}
			// }, 0)

			e.preventDefault();
			e.stopPropagation();
		})
		.bind("touchend", function(e){
			if(!opt.enable) return true;
			e = e.originalEvent || e;
			if(!touch.el.length) return true;

			if(sideMenuEnabled){
				$.ui.enableSideMenu();
			}

			if(status == "slide") {
				if(touch.x1 - touch.x2 > opt.distance/2) {
					_this.open(touch.el);
				} else {
					_this.close(touch.el);
				}
	        	ct.trigger("slideend", touch);
				e.preventDefault();
				e.stopPropagation();
			}

			status = "";
			return true;
		});

		this.open = function(elem) {
			elem.attr("data-slide", true)
			.css3Animate({ x: -opt.distance, time: opt.time, success: function(){
				opened = elem;
	        	ct.trigger("slideopen", { el: elem });
			}});
		}
		this.close = function(elem, passive){
			elem = elem || opened;
			if(!elem || !elem[0]) return;
			elem.removeAttr("data-slide")
			.css3Animate({ x: 0, time: opt.time, success: function(){
				if(!passive) {				
					opened = null;
				}
	        	ct.trigger("slideclose", { el: elem });
			}});
		}
		// this.setDistance = function(distance) {
		// 	distance = parseInt(distance);
		// 	if(!isNaN(distance)) {
		// 		opt.distance = distance
		// 	}
		// }
	}

	Slide.instants = {}

	Slide.defaults = {
		selector: "li",
		enable: true,
		distance: 80,
		time: 200
	}

	return Slide;
})();



/**/
var SlideMenu = function(ct, opt){

}