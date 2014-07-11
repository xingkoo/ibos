var msg = (function(){
	var msgId = 0,
		sinceId = 0,
		isInit = false,
		prevTime = 0,
		msgPage = 1,
		msgUrl = function (){ return app.appUrl + '/msg' };
		
	function init(){
		if(isInit){
			return false;
			msg.loadList();
		}
		
		list = new List('msgList', $("#msgListTpl").val(), {"id": "id"});
		msg.loadList();
		
		isInit = true;
	}
	
	function loadList(page){

		//$.ui.showMask();
		
		$.jsonP({
			url: 		msgUrl() + "/index&callback=?",
			success: 	showList,
			error: 		function(err){	console.log(err) }
		});
	}
	
	function showList(json){
		$.ui.hideMask();
		list.add(json);
	}
	function loadMsg(module,dom){
		$.ui.showMask();
		$("#msgView").empty()
		$.jsonP({
			url: 		msgUrl() + "/list&callback=?&module="+module,
			success: 	msg.showMsg,
			error: 		function(err){	console.log(err)	 }
		});
		$.ui.removeBadge("#mod_"+module);
	}
	function showMsg(json){
		var $tpl = $("#msgViewTpl"),
			$target = $("#msgView");
		var tp = $tpl.val(),
			newTp = '',
			obj = {};
		if(json.datas){
			for(var val in json.datas){
				if(json.datas.hasOwnProperty(val)){
					obj = json.datas[val]; //没有特殊要处理的则直接对象赋于就行了
					for(var v in obj){
						obj.hasOwnProperty(v) && (newTp += $.template(tp, obj[v]));
						msg.prevTime = obj[v]["ctime"];
					}
				}
			}
			$target.append(newTp);
			$.ui.scrollToBottom('msg_view');
			$.ui.hideMask();
		}
	}	
	return {
		init:			init,
		sinceId:		sinceId,
		loadList: 		loadList,
		loadMsg:		loadMsg,
		showList: 		showList,
		showMsg:		showMsg,
	}
})();
