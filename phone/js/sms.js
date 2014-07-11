var Sms = (function(){
	var smsId = 0,
		sinceId = 0,
		timeout = null,
		isInit = false,
		prevTime = 0,
		smsPage = 1,
		smsUrl = function (){ return app.appUrl + '/pm' };

	var _newSms;
		
	function init(){
		if(isInit){
			return false;
		}
		Sms.loadList();
		isInit = true;
	}
	
	function loadList(page){
		if(page != smsPage && typeof(page) != "undefined" ){
			smsPage = page;
			pageurl = "&page=" + page;
		}else{
			$("#smsList").empty();
			pageurl = ''
		}
		
		$.jsonP({
			url: 		smsUrl() + "/list&callback=?" + pageurl,
			success: 	showList,
			error: 		core.error
		});
	}
	
	function showList(json){
		var $tpl = $("#smsListTpl"),
			$target = $("#smsList");
		var tp = $tpl.val(),
			newTp = '',
			obj = {};
			for(var val in json.datas){
				obj = json.datas[val]; //没有特殊要处理的则直接对象赋于就行了
				//obj.id = json.datas[val].readStatus?1:0  //有特殊处理的额外写
				newTp += $.template(tp, obj);
			}
		$target.append(newTp);
	}
	
	
	function loadSms(id,sid){
		//$(dom).parent().removeClass("new"); //取消未读
		if(typeof sid == 'undefined'){
			sid = sinceId;
		}
		if(typeof id == 'undefined'){
			id = smsId;
		}else if(smsId != id){
			$("#smsView").empty();//.css3Animate({ time: "300ms", opacity: 0 });
			smsId = id;
			sid = 0;
		}
		
		$.jsonP({
			url: 		smsUrl() + "/show&callback=?&id="+id+"&sinceid="+sid,
			success: 	Sms.showSms,
			error: 		core.error
		});
	}
	function showSms(json){
		var $tpl = $("#smsViewTpl"),
			$target = $("#smsView");
		var tp = $tpl.val(),
			newTp = '',
			obj = {};
		if(json.data.length>0){
			for(var val in json.data){
				obj = json.data[val]; //没有特殊要处理的则直接对象赋于就行了
				newTp += $.template(tp, obj);
				Sms.prevTime = obj["mtime"]
			}
			$target.append(newTp);//.css3Animate({ time: "500ms", opacity: 1 });
			$.ui.scrollToBottom('sms_view');
			sinceId = json.sinceid;
		}
		//TODO::判断当前页面是否是私信页
		if(Sms.timeout) window.clearTimeout(Sms.timeout)
		Sms.timeout = setTimeout(Sms.loadSms,1500,smsId,sinceId); 
	}
	/**
	* 设置聊天面板
	*
	*/
	function setPanel(id){
		if(typeof id == 'undefined'){
			id = smsId;
		}
		if(id!=0){		
			$("pm_id").val(id);
		} else{
			$("pm_touid").val(_newSms.id);
		}
		$("formPm").attr("action",app.appUrl+"/pm/postimg");
	}
	function send(){
		
		var id = smsId,
			first = false,
			content = $("#smsInput").val();
		if(id==0){
			tourl = "/send&callback=?&touid="+_newSms.id+"&content="+content;
			first = true;
		}else{
			tourl = "/send&callback=?&id="+id+"&content="+content;
		}
		$("#smsInput").val("");
		$.jsonP({
			url: 		smsUrl() + tourl,
			success: 	function(json){
							clearTimeout(Sms.timeout);
							if(first){
								id = json.listid;
								sinceId = 0;
							}
							Sms.loadSms(id,sinceId);
						},
			error: 		core.error
		});
	}

	function addSms(data){
		app.openSelector({
			maxSelect: 1,
			filter: function(data){
				// 不能发送给自己
				return data.uid != app.uid
			},
			onSelect: function(evt, data){
				_newSms = data;
				$.ui.loadContent("#sms_view");
				$("#smsView").empty();
				$.ui.setTitle(_newSms.text);
			}
		})		
	}

	return {
		prevTime:		prevTime,
		timeout:		timeout,
		
		init:			init,
		loadList: 		loadList,
		loadSms:		loadSms,
		showList: 		showList,
		showSms:		showSms,
		setPanel:		setPanel,
		send:			send,
		addSms:         addSms
	}
})();


function cleartime(){
	clearTimeout(Sms.timeout);
	//$("#smsView").empty();
}