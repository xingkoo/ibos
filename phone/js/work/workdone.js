var WorkDone = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("work_done_list", $.query("#work_done_tpl").val(), { id: 'runid'});
		mainListIns = new MainList(listIns, { url: app.appUrl + '/work' + "&type=trans"})
		mainListIns.load();
	}

	// 读取列表
	return {
		init: init
	}
})();
