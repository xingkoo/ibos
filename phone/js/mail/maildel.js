
var MailDel = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("mail_del_list", $.query("#mail_del_list_tpl").val());
		mainListIns = new MainList(listIns, { url: app.appUrl + '/mail' })
		mainListIns.load({ "type": "del" });;
	}

	// 读取列表
	return {
		init: init
	}
})();
