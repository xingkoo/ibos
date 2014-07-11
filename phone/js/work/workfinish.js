var WorkFinish = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("work_finish_list", $.query("#work_finish_tpl").val(), { id: 'runid'});
		mainListIns = new MainList(listIns, { url: app.appUrl + '/work' + "&type=done"})
		mainListIns.load();
	}

	// 读取列表
	return {
		init: init
	}
})();
