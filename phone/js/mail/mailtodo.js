
var MailTodo = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("mail_todo_list", $.query("#mail_todo_list_tpl").val());
		mainListIns = new MainList(listIns, { url: app.appUrl + '/mail' })
		mainListIns.load({ "type": "todo" });;
	}

	// 读取列表
	return {
		init: init
	}
})();
