var WorkTodo = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("work_todo_list", $.query("#work_todo_tpl").val(), { id: 'runid'});
		mainListIns = new MainList(listIns, { url: app.appUrl + '/work' + "&type=todo"})
		mainListIns.load();
	}

	// 读取列表
	return {
		init: init
	}
})();

app.evt.add({
	// 主办工作
	"sponsorWork": function(param){
		$LAB.script("js/work/workstart.js")
		.wait(function(){
			WorkStart.startFlow(param.key, "sponsor");
		})
	},
	// 经办工作
	"operateWork": function(param){
		$LAB.script("js/work/workstart.js")
		.wait(function(){
			WorkStart.startFlow(param.runId, "operate");
		})
	},
	// 查看工作
	"viewWork": function(param) {
		$LAB.script("js/work/workstart.js")
		.wait(function(){
			WorkStart.startFlow(param.runId, "view");
		})
	}
})