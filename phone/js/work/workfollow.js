var WorkFollow = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("work_follow_list", $.query("#work_follow_tpl").val(), { id: 'runid'});
		mainListIns = new MainList(listIns, { url: app.appUrl + '/work/follow'})
		mainListIns.load();
	}

	// 读取列表
	return {
		init: init
	}
})();
