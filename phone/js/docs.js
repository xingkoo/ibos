/**
* Docs
* @author Aeolus
* @copyright IBOS
*/
var docs = (function(){
	var docsCatId = 0, // 当前分类, 0为默认，显示所有
		docsId = 0, //
		docsPage = 1, // 当前页码
		docsUrl = function (){ return app.appUrl + '/docs' };

	var list;
		
	/**
	* 初始化新闻模块时，载入一些基础数据，比如分类，默认页新闻，未读条数等
	*/
	function init(){
		list = new List('docsList', $("#docsListTpl").val(), {"id": "docid"});
		loadCat();
		loadList(docsCatId);
	}
	//------ Docs List
	function loadList(catid, page, title){
		//$(dom).parent().addClass("active"); //选中分类
		//$(dom).parent().siblings().removeClass("active"); //选中分类
		var pageurl;

		$.ui.showMask();

		title = title || $.query("#docs").data("title")
		$.query("#title_docs").html(title);

		// 目录变更
		if(catid !== docsCatId) {
			docsCatId = catid;
			docsPage = 1;
		}
		// 页码变更
		if(typeof page !== "undefined" && page !== docsPage){
			docsPage = page;
		}

		pageurl = "&page=" + docsPage;


		$.jsonP({
			url: 		docsUrl() + "&callback=?&catid=" + docsCatId + pageurl,
			success: 	showList,
			error: 		core.error
		});
	}

	function showList(json){
		if(docsPage > 1){
			list.add(json.datas)
		}else{
			list.set(json.datas);
			$("#docsList").hide()
			setTimeout(function(){ $("#docsList").show() },0);
		}
		
		$("#readMoreDocs").remove();
		if( json.pages.pageCount > docsPage ){
			$("#docsList").append('<li id="readMoreDocs" class="list-more"><a onclick="docs.loadList(' + docsCatId + ','+( docsPage + 1) +')">加载更多</a></li>');
		}

		$.ui.hideMask();
		return;
	}
	
	//------ Docs Catelog
	function loadCat(){
		$.jsonP({
			url: 		docsUrl() + "/category&callback=?",
			success: 	showCat,
			error: 		core.error
		});
	}

	function showCat(json){
		// debugger;
		// var $tpl = $("#docsCatTpl"),
			// $target = ;
		// var tp = $tpl.val(),
			// newTp = '',
			// obj = {};
			// for(var val in json){
				// if(docsCatId!=0 && json[val].catid == docsCatId){
					// json[val].classname = 'class="active"';
				// }else{
					// json[val].classname = ' ';
				// }
				// obj = json[val];
				// newTp += $.template(tp, obj);
			// }
		// $target.append(newTp);
		$("#docsCat").html(json);
	}
	
	//------ docs View
	function loadDocs(id,dom){
		$.ui.updatePanel("docs_view", "")
		$.ui.loadContent("view/docs/docs_view.html", 0, 0);
		$.ui.showMask();

		$(dom).parent().removeClass("new"); //取消未读
		if(typeof id === 'undefined'){
			id = docs.docsId;
		}
		
		$.jsonP({
			url: 		docsUrl() + "/show&callback=?&id="+id,
			success: 	showDocs,
			error: 		function(err){ console.log(err) }
		});
	}
	function showDocs(json){
		var tpl = $("#docsContentTpl").val(),
			$target = $("#docsContent"),
			newTp = $.template(tpl, json.data);

		$target.html(newTp).css3Animate({ time: "500ms", opacity: 1 });
		$.ui.hideMask();
	}
	
	//------ Search
	function search(data){
		// 发起搜索时，重置页码为1
		docsPage = 1;
		$.jsonP({
			url: 		docsUrl() + "&callback=?&search=" + data,
			success: 	showList,
			error: 		function(err){ console.log(err) }
		});
		
	}


	return {
		init:			init,
		loadList: 		loadList,
		loadCat:		loadCat,
		loadDocs:		loadDocs,
		showList: 		showList,
		showCat:		showCat,
		showDocs:		showDocs,
		search:			search
	}
})();
