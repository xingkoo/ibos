/**
 * login.js
 * 后台登录
 * @module Dashboard
 * @submodule Login
 * @author inaki
 * @version $Id$
 * @modified 2013-05-04
 */

(function(){
	// @Todo: 居中
	var resizeBackground = function(img){
		var $win = $(window),
			winWidth = $win.width(),
			winHeight = $win.height(),
			imgWidth, imgHeight,
			resize = function(){
				imgWidth = img.width;
				imgHeight = img.height;
				// 适配高度
				if(imgWidth / imgHeight >= winWidth / winHeight){
					img.style.height = winHeight + 'px';
					// 宽度自适应http://yozora.ink/static/7b951dfa/image/bg_body.jpg
					img.style.width = 'auto';

				// 适配宽度
				} else {
					img.style.width = winWidth + 'px';
					// 高度自适应
					img.style.height = 'auto';
				}
				$(img).fadeIn();
			};
		if(img.complete){
			resize();
		}else{
			img.onload = resize;
		}
	}

	var img = document.getElementById("bg");
	window.onload =	window.onresize = function(){
		resizeBackground(img);
	}
	var btn = document.getElementById('submit-btn');
	$(btn).on('click',function(){
		$(this).button('loading');
	});
})();