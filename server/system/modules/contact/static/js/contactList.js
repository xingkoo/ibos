
$(function() {
	//侧栏信息栏的现实与隐藏的操作，已经搜索栏长度的改变
	var display = {
		show: function($elem, $search) {
			$elem.animate({
				width: '520px',
				marginLeft: '261px'
			}, 200);
			$search.removeClass('span8').addClass('w230');
		},
		hide: function($elem, $search) {
			$elem.animate({
				width: '0',
				marginLeft: '780px'
			}, 200);
			$search.removeClass('w230').addClass('span8');
		}
	}

	//计算侧栏信息栏和字母导航栏的定位及高度的计算
	function calculate() {
		var cwtop = $('#cl_list_header').offset().top;
		var dctop = $(document).scrollTop();
		var windowHeight = $(window).height();
		var mcheight = $('.mc').height();

		var slidtop = dctop - cwtop;
		var linkheight = mcheight - slidtop;
		var rollingSlideHeight = linkheight + 'px';
		var mcheightval = mcheight + 'px';
		var slidtopval = -slidtop + 'px';
		var nletterHeightVal = mcheight - 60 + 'px';
		var rletterHeightVal = linkheight - 60 + 'px';

		if (slidtop > 0) {
			$("#cl_rolling_sidebar").css({"top": '60px', "height": rollingSlideHeight});
			$("#cl_letter_sidebar").css({'height': rletterHeightVal});
			$("#cl_letter_sidebar").addClass('sidebar-rolling').removeClass('sidebar-normal');
			$("#cl_funbar").addClass('funbar-rolling').removeClass('funbar-normal');
		} else {
			$("#cl_rolling_sidebar").css({"top": slidtopval, "height": mcheightval});
			$("#cl_letter_sidebar").css({'height': nletterHeightVal});
			$("#cl_letter_sidebar").addClass('sidebar-normal').removeClass('sidebar-rolling');
			$("#cl_funbar").addClass('funbar-normal').removeClass('funbar-rolling');
		}

	}

	$(document).ready(function() {
		calculate();
		$("#search_area").focus();
	});

	$(window).resize(function() {
		calculate();
	});

	$(window).scroll(function() {
		calculate();
	});

	var $slide = $("#personal_info");

	//公司通讯录，点击列表单行，侧栏信息显示，改变选择行背景色
	$("tr", ".contact-list").on('click', function() {
		var $elem = $(this),
				$sidebar = $("#cl_rolling_sidebar"),
				$search = $('#name_search');
		$('tr').removeClass('active');
		$elem.addClass('active');
		display.show($sidebar, $search);
		var id = $elem.attr('data-id');
		$slide.waitingC();
		$.post(Ibos.app.url('contact/default/ajaxApi', {op: 'getProfile', uid: id}), function(res) {
			if (res.isSuccess) {
				$slide.stopWaiting();
				var user = res.user;
				if (res.uid == user.uid) {
					$("#card_pm").hide();
				} else {
					$("#card_pm").show();
				}
				$("#card_home_url").attr("href", Ibos.app.url('user/home/index', {uid: user.uid}));
				$("#card_avatar").attr("src", user.avatar_big);
				$("#card_email_url").attr("href", Ibos.app.url('email/content/add', {toid: user.uid}));
				$("#card_pm").attr("href", "javascript:Ui.showPmDialog(['u_" + user.uid + "'],{url:'" + Ibos.app.url('message/pm/post') + "'});");
				$("#card_mark").attr("data-id", user.uid);
				$("#card_realname").text(user.realname);
				$("#card_deptname").text(user.deptname);
				$("#card_posname").text(user.posname);
				if (user.deptname !== '' && user.posname !== '') {
					$("#card_connect").show();
				} else {
					$("#card_connect").hide();
				}
				$("#card_telephone").text(user.telephone == '' ? '暂无' : user.telephone);
				$("#care_mobile").text(user.mobile == '' ? '暂无' : user.mobile);
				$("#card_email").text(user.email == '' ? '暂无' : user.email);
				$("#card_qq").text(user.qq == '' ? '暂无' : user.qq);
				$("#card_birthday").text(user.birthday == '' ? '暂无' : user.birthday);
				$("#card_fax").text(user.fax == '' ? '暂无' : user.fax);
				if (user.gender == '1') {
					$("#card_gender").attr("class", "om-male");
				} else {
					$("#card_gender").attr("class", "om-female");
				}
				if (res.cuids.length > 0 && $.inArray(id.toString(), res.cuids) !== -1) {
					$("#card_mark").attr("class", "o-si-mark");
				} else {
					$("#card_mark").attr("class", "o-si-nomark");
				}
				$("#card_bg").attr("src", user.bg_big);
			}
		});
	});

	//关闭侧栏个人信息
	$("#cl_window_ctrl").on('click', function() {
		var $sidebar = $("#cl_rolling_sidebar"),
				$search = $('#name_search');
		display.hide($sidebar, $search);
		$("tr").removeClass('active');
	});

	//公司通讯录，点击添加常用联系人
	$(".o-nomark", ".cl-info-table").on('click', function(evt) {
		evt.stopPropagation();
		var $elem = $(this),
				toFocus = $elem.hasClass("o-mark"),
				status = toFocus ? 'unmark' : 'mark',
				id = $elem.attr('data-id'),
				$trelem = $("i[data-id='" + id + "']"),
				$aelem = $("a[data-id='" + id + "']"),
				url = Ibos.app.url('contact/default/ajaxApi'),
				param = {op: 'changeConstant', cuid: id, status: status};
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$elem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark')});
				$trelem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark')});
				$aelem.attr({'class': (toFocus ? 'o-si-nomark' : 'o-si-mark')});
				Ui.tip(U.lang("OPERATION_SUCCESS"));
			}
		});
	});

	//公司通讯录，点击取消常用联系人
	$(".o-mark", ".cl-info-table").on('click', function(evt) {
		evt.stopPropagation();
		var $elem = $(this),
				toFocus = $elem.hasClass("o-mark"),
				status = toFocus ? 'unmark' : 'mark',
				id = $elem.attr('data-id'),
				$trelem = $("i[data-id='" + id + "']"),
				$aelem = $("a[data-id='" + id + "']"),
				url = Ibos.app.url('contact/default/ajaxApi'),
				param = {op: 'changeConstant', cuid: id, status: status};
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$elem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark')});
				$trelem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark')});
				$aelem.attr({'class': (toFocus ? 'o-si-nomark' : 'o-si-mark')});
				Ui.tip(U.lang("OPERATION_SUCCESS"));
			}
		});
	});

	//常用联系人，点击取消常用联系
	$(".o-mark", ".common-uer-table").on("click", function(evt) {
		evt.stopPropagation();
		var $elem = $(this),
				$tr = $elem.parents('tr').eq(0),
				id = $elem.attr('data-id'),
				url = Ibos.app.url('contact/default/ajaxApi'),
				param = {op: 'changeConstant', cuid: id, status: 'unmark'};
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$tr.remove();
				Ui.tip(U.lang("OPERATION_SUCCESS"));
			}
		});
	});

	//侧栏个人信息头像点击添加常用联系人操作
	$(".o-si-nomark").on('click', function() {
		var $elem = $(this),
				toFocus = $elem.hasClass("o-si-mark"),
				status = toFocus ? 'unmark' : 'mark',
				id = $elem.attr('data-id'),
				$trelem = $("i[data-id='" + id + "']"),
				url = Ibos.app.url('contact/default/ajaxApi'),
				param = {op: 'changeConstant', cuid: id, status: status};
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$elem.attr({'class': (toFocus ? 'o-si-nomark' : 'o-si-mark')});
				$trelem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark')});
				Ui.tip(U.lang("OPERATION_SUCCESS"));
			}
		});
	});

	$(".letter-mark").on("click", function(){
		var $elem = $(this);
		$(".letter-mark").removeClass('active');
		$elem.addClass('active');
		$(".letter-mark").css({"color":"#82939e"});
		$elem.css({"color":"#ee8c0c"});
		var id = $elem.attr("data-id");
		var targetid = "#target_" +  id;
		var target = "target_" + id;
		$(".cl-letter-title").removeClass("active");
		Ui.scrollYTo(target, -120, function(){ $(targetid).addClass("active") });
	});

	//导出通讯录
	$("#educe_concatcList").on("click", function() {
		var $elem = $(this),
				uids = $elem.attr("data-uids"),
				url = Ibos.app.url('contact/default/export');
		window.location = url + '&uids=' + uids;
	});

	var isInit = false;

		//打印通讯录
	$("#print_concatcList").on("click", function() {
		var $elem = $(this),
			uids = $elem.attr("data-uids"),
			url = Ibos.app.url('contact/default/printContact');
		$.post(url, {uids: uids, deptid: Ibos.app.g("deptid")}, function(res) {
			if (res.isSuccess) {
				if(!isInit){
					$("body").append(res.view);
					isInit = true;
				}
			}
			window.print();
		});
	});

	// 搜索
	$(document).keyup(function(event) {
		calculate();
		var searchStr = $("#search_area").val().toLowerCase(),
				nTrs = $(".contact-list-item"),
				$noDataTip = $(".inexist-data");
		nTrs.each(function() {
			var $elem = $(this),
				pregName = $elem.attr("data-preg");
			if (pregName.indexOf(searchStr) === -1) {
				$elem.removeClass('show').addClass('hide');
			} else {
				$elem.removeClass('hide').addClass('show');
			}
		});
		var groupItems = $(".group-item");
		groupItems.each(function() {
			var $elem = $(this),
			$userItem = $elem.find(".contact-list-item.show");
			if ($userItem.length == 0) {
				$elem.removeClass('show').addClass('hide');
			} else {
				$elem.removeClass('hide').addClass('show');
			}
		});

		if(searchStr === ""){
			$(".group-item").removeClass("hide").addClass("show");
		}
	});

	$(".org-dept-table tr").on("click", function(){
		var $elem = $(this);
		$(".org-dept-table tr").removeClass("active");
		$elem.addClass("active");
	});

	setInterval(function(){
		calculate();

		//当搜索结果无数据时的信息提示
		var $data = $(".group-item.hide"),
			hideDataLength = $data.length,
			allDataLength = $(".exist-data .group-item").length,
			$noDataTip = $(".inexist-data");
		if(allDataLength == hideDataLength){
			$noDataTip.removeClass("hide").addClass("show");
		}else{
			$noDataTip.removeClass("show").addClass("hide");
		}
	}, 200);
});
