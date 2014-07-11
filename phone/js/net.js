var netSetting = (function(){
	var netSetList = {};
	var maxID = 0;
		
	function showList(){
		// 全局变量 * 2
		defautlUrl = localStorage.getItem("defaultUrl");
		defautlID = localStorage.getItem("defaultID");

		netSetList = core.getStorage("netSetList");
		// 要判定为null的情况
		if(netSetList && typeof netSetList === "object" ){
			var $tpl = $("#netSettingTpl"),
				$target = $("#netSettingList");
				
			var tp = $tpl.val(),
				newTp = '',
				obj = {};
				for(var val in netSetList){
					obj = netSetList[val];
					newTp += $.template(tp, obj);
					maxID = maxID < val ? val:maxID ;
				}
			$target.html(newTp);
		}else{
			netSetList = {};
			$.ui.loadContent('netEdit',false,false,'fade');
		}
	}
	function setDefault(i){
		app.appUrl = netSetList[i].url;
		app.defaultUrl = app.appUrl;
		localStorage.setItem("defaultUrl", netSetList[i].url);
		localStorage.setItem("defaultName", netSetList[i].name);
		localStorage.setItem("defaultID", netSetList[i].id);
		app.isInit = false;
		app.init();
	}
	function save(myid){
		var i = myid ? myid : ++maxID;
		url = $("#netUrlInput").val();
		name = $("#netNameInput").val();
		if( url == "" || name == "" ){ 
			$.ui.showMask("请填写完整");
			setTimeout(function(){
                    $.ui.hideMask();
                 },1200);
			return false;
		}
		netSetList[i] = {
			id: i,
			url: url,
			name: name
		}
		core.setStorage("netSetList",netSetList);
		setDefault(i);
		//返回
		$.ui.goBack();
	}
	function edit(myid){
		var id="",name="",url="";
		if(myid){
			id = netSetList[myid].id;
			name = netSetList[myid].name;
			url = netSetList[myid].url;
		}
		$("#netIDInput").val(id);
		$("#netUrlInput").val(url);
		$("#netNameInput").val(name);
		$.ui.loadContent('netEdit',false,false,"fade");
	}
	function del(myid){
		if(!myid){
			myid = $("#netIDInput").val();
		}
		delete netSetList[myid];
		core.setStorage("netSetList",netSetList);		
		$.ui.goBack();
	}
	return {
		showList:	showList,
		setDefault:	setDefault,
		save:		save,
		edit:		edit,
		del:		del,
		netSetList:netSetList
	}
})()