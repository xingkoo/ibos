var loadLogin = function (what){
	$.ui.disableSideMenu();
	$.ui.toggleHeaderMenu();
	var selectNet = localStorage.getItem("defaultName");
	if(selectNet){ $("#selectNet").html(selectNet); }
}
var unloadLogin = function (){
	$.ui.enableSideMenu();
}
function mainload(){
	$.ui.hideMask();
}
function loadSms(){
	$LAB.script("js/sms.js").wait(function(){
		Sms.init();
	});
	loadMsg()
}
function loadMsg(){
	$LAB.script("js/msg.js").wait(function(){
		msg.init();
	});	
}
function showNetList(){
	$LAB.script("js/net.js").wait(function(){
		netSetting.showList();
	});	
}
function loadSetting(){
	$LAB.script("js/profile.js").wait(function(){
		setup.loadSetting();
	});	
}

//因为面板的data-load限制，而特意写的调用函数
function loadNews(){
	$LAB.script("js/news.js").wait(function(){
		news.init(); 
	});
}
function loadDocs(){
	$LAB.script("js/docs.js").wait(function(){
		docs.init(); 
	});
}
function loadDiary(){
	$LAB.script("js/diary.js").wait(function(){
		diary.init();
	})
}

function scaner(){
	cordova.plugins.barcodeScanner.scan(
		function (result) {
		  alert("We got a barcode\n" +
				"Result: " + result.text + "\n" +
				"Format: " + result.format + "\n" +
				"Cancelled: " + result.cancelled);
		}, 
		function (error) {
		  alert("Scanning failed: " + error);
		}
	);
}

// 微博
$(document).on("loadpanel", "#weibo", function(){
	$LAB.script("js/weibo/weibo.js")
	.script("js/weibo/weibohome.js");
});