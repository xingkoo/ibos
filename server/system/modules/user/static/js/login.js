


$(function(){
	// 切换找回密码表格
	var lgPanel = $("#login_panel"),
		pswPanel = $("#get_password_panel");

	var togglePanel = function(){
		lgPanel.toggle();
		pswPanel.toggle();
	}

	Ui.focusForm(lgPanel);

	$("#to_get_password").on("click", function(){
		var userName;
		togglePanel();
		Ui.focusForm(pswPanel);
		// 同步登陆框用户名 至 找回密码面板 用户名
		userName = lgPanel.find("[name='username']").val();
		pswPanel.find("[name='username']").val(userName);
	}).tooltip();

	$("#to_login").on("click", function(){
		togglePanel();
		Ui.focusForm(lgPanel);
	});
	// 公告内容过多时自动滚动
	var $anc = $("#lg_anc_ct"),
		ANC_MAX_HEIGHT = 40,	// 公告内容最大高度
		mgt = 0, // margin-top 公告内容当前上边距值
		ancHeight = $anc.outerHeight(),
		scrollSpeed = 2000, // 毫秒
		timer;

	var autoScroll = function(){
		timer = setInterval(function(){
			mgt -= 20;
			if(-mgt >= ancHeight){
				mgt = ANC_MAX_HEIGHT;
				$anc.css({ "margin-top": mgt });
			}else{
				$anc.animate({ "margin-top": mgt });
			}
		}, scrollSpeed)
	};

	if(ancHeight > ANC_MAX_HEIGHT){
		autoScroll();
		$anc.hover(function(){
			clearInterval(timer)
		}, autoScroll);
	}
	$("#lg_help").tooltip();
	var $ctrl = $("#lg_pattern"),
		$menu = $("#lg_pattern_menu");

	var sl = new Ibos.Plugins.PseudoSelect($ctrl, $menu, {
		template: " <%=text%> <i class='o-lg-select'></i>"
	});

	$ctrl.on("select", function(evt) {
		$("#lg_pattern_val").val(evt.selected);
		var types = {
			1: "account",
			2: "email",
			3: "jobnum",
			4: "mobile"
		}
		accountValidator[types[evt.selected]]();
	});

	// 登录背景
	var imgArr = Ibos.app.g("loginBg")

	// LoadImage and set Image fullscreen
	var bgNode = document.getElementById("bg"),
		bgWrap = bgNode.parentNode,
		index = Math.ceil(imgArr.length * Math.random()) - 1;

	$(document.body).waiting(null, "normal");

	U.loadImage(imgArr[index], function(img) {
		var imgRatio = img.width / img.height;
		img.style.width = "100%";
		img.style.height = "100%";
		var setWrapSize = function(width, height) {
			bgWrap.style.width = width + "px";
			bgWrap.style.height = height + "px";
		};
		var resize = function(ratio) {
			var $doc = $(document),
				docWidth = $doc.width(),
				docHeight = $doc.height(),
				ImgTotalWidth,
				ImgTotalHeight;

			setWrapSize(docWidth, $(window).height());

			if (docWidth / docHeight > ratio) {
				ImgTotalHeight = docWidth / ratio;
				// 适配宽度
				bgNode.style.width = docWidth + 'px';
				// 按图片比例放大高度，保持图片不变形
				bgNode.style.height = ImgTotalHeight + 'px';
				// 图片垂直居中
				bgNode.style.marginTop = (docHeight - ImgTotalHeight) / 2 + 'px';
				bgNode.style.marginLeft = 'auto';
			} else {
				ImgTotalWidth = docHeight * ratio;
				// 适配高度
				bgNode.style.height = docHeight + 'px';
				// bgNode.style.height = docHeight + 'px';
				// 按图片比例放大高度，保持图片不变形
				bgNode.style.width = ImgTotalWidth + 'px';
				// 图片水平居中
				bgNode.style.marginLeft = (docWidth - ImgTotalWidth) / 2 + 'px';
				bgNode.style.marginTop = 'auto';
			}
			$(document.body).stopWaiting();
			$(bgNode).fadeIn();
		};

		resize(imgRatio);
		window.onresize = function() {
			setWrapSize(0, 0);
			resize(imgRatio);
		};
		bgNode.appendChild(img);
	});

	// Op
	Ibos.evt.add({
		// 清除痕迹
		"clearCookie": function(){
			var result = window.confirm(U.lang("LOGIN.CLEAR_COOKIE_CONFIRM"));
			if(result){
				U.clearCookie();
				Ui.tip(U.lang("LOGIN.CLEARED_COOKIE"))
			}
		}
	});

	// 表单验证
	var getAccountSettings = function(focusMsg){
		return {
			onFocus: function(){
				Ibosapp.formValidate.setGroupState("#account")
				return focusMsg
			},
			onCorrect: function(){
				Ibosapp.formValidate.setGroupState("#account", "correct");
			},
			relativeID: "account_wrap"
		}
	}
	var getAccountRegSettings = function(reg, msg){
		return {
			regExp: reg,
			dataType: "enum",
			onError: function(){
				Ibosapp.formValidate.setGroupState("#account", "error");
				return msg;
			}
		}
	}
	var accountValidator = {
		account: function(){
			$("#account").formValidator(getAccountSettings(U.lang("V.INPUT_ACCOUNT")))
			.regexValidator(getAccountRegSettings("notempty", U.lang("V.INPUT_ACCOUNT")));
		},
		email: function(){
			$("#account").formValidator(getAccountSettings(U.lang("V.INPUT_EMAIL")))
			.regexValidator(getAccountRegSettings("email", U.lang("RULE.EMAIL_INVALID_FORMAT")));
		},
		jobnum: function(){
			$("#account").formValidator(getAccountSettings(U.lang("V.INPUT_JOBNUM")))
			.regexValidator(getAccountRegSettings("notempty", U.lang("V.INPUT_JOBNUM")));
		},
		mobile: function(){
			$("#account").formValidator(getAccountSettings(U.lang("V.INPUT_MOBILE")))
			.regexValidator(getAccountRegSettings("mobile", U.lang("RULE.MOBILE_INVALID_FORMAT")));
		}
	}

	$.formValidator.initConfig({formID: "login_form",errorFocus:true});

	accountValidator.account();

	$("#password").formValidator({
		onFocus: function(){
			Ibosapp.formValidate.setGroupState("#password")
			return U.lang("V.INPUT_POSSWORD");
		},
		onCorrect: function(){
			Ibosapp.formValidate.setGroupState("#password", "correct");
		}
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: function(){
			Ibosapp.formValidate.setGroupState("#password", "error");
			return U.lang("V.INPUT_POSSWORD");
		}
	})
})