var MailSend = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("mail_send_list", $.query("#mail_send_list_tpl").val());
		mainListIns = new MainList(listIns, { url: app.appUrl + '/mail' })
		mainListIns.load({ "type": "send" });;
	}

	// 读取列表
	return {
		init: init
	}
})();
