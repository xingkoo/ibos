/**
* News
* @author Aeolus
* @copyright IBOS
*/
var news = (function(){
	var newsCatId = 0, // 当前分类, 0为默认，显示所有
		newsId = 0, //
		newsPage = 1, // 当前页码
		newsUrl = function (){ return app.appUrl + '/news' };

	var list;
		
	/**
	* 初始化新闻模块时，载入一些基础数据，比如分类，默认页新闻，未读条数等
	*/
	function init(){
		list = new List('newsList', $("#newsListTpl").val(), {"id": "articleid"});
		loadCat();
		loadList(newsCatId);
	}
	//------ News List
	function loadList(catid, page, title){
		//$(dom).parent().addClass("active"); //选中分类
		//$(dom).parent().siblings().removeClass("active"); //选中分类
		var pageurl;
		$.ui.showMask();

		title = title || $.query("#news").data("title")
		$.query("#title_news").html(title)
		
		// 目录变更
		if(catid !== newsCatId) {
			newsCatId = catid;
			newsPage = 1;
		}

		// 页码变更
		if(typeof page !== "undefined" && page !== newsPage){
			newsPage = page;
		}
		pageurl = "&page=" + newsPage;

		$.jsonP({
			url: 		newsUrl() + "&callback=?&catid=" + newsCatId + pageurl,
			success: 	showList,
			error: 		core.error
		});
	}

	function showList(json){
		if(newsPage > 1){
			list.add(json.datas)
		}else{
			if(json.datas.length){
				list.set(json.datas);
			}else{
				$("#newsList").html("<li class='no-info'></li>");
			}
			$("#newsList").hide()
			setTimeout(function(){ $("#newsList").show() },0);
		}
		
		$("#readMoreNews").remove();
		if( json.pages.pageCount > newsPage ){
			$("#newsList").append('<li id="readMoreNews" class="list-more"><a href="javascript:;" onclick="news.loadList(' + newsCatId + ','+( newsPage + 1) +')">加载更多</a></li>');
		}

		
		$.ui.hideMask();
		return;
	}
	
	//------ News Catelog
	function loadCat(){
		$.jsonP({
			url: 		newsUrl() + "/category&callback=?",
			success: 	showCat,
			error: 		core.error
		});
	}
	function showCat(json){
		// var $tpl = $("#newsCatTpl"),
			// $target = ;
		// var tp = $tpl.val(),
			// newTp = '',
			// obj = {};
			// for(var val in json){
				// if(newsCatId!=0 && json[val].catid == newsCatId){
					// json[val].classname = 'class="active"';
				// }else{
					// json[val].classname = ' ';
				// }
				// obj = json[val];
				// newTp += $.template(tp, obj);
			// }
		// $target.append(newTp);
		$("#newsCat").html(json);
	}
	
	//------ News View
	function loadNews(id,dom){
		// 清空内容层
		$.ui.updatePanel("news_view", "")
		// ajax 加载新的内容
		$.ui.loadContent("view/news/news_view.html", 0, 0);

		$.ui.showMask();
		//取消未读
		$(dom).parent().removeClass("new"); 

		if(typeof id === 'undefined'){
			id = news.newsId;
		}
		
		$.jsonP({
			url: 		newsUrl() + "/show&callback=?&id="+id,
			success: 	showNews,
			error: 		core.error
		});
	}
	function showNews(json){
		var tpl = $.query("#newsContentTpl").val(),
			$target = $.query("#newsContent"),
			//可以需要对json数据做一些处理
			newTp = $.template(tpl, json);
			
		$target.html(newTp).css3Animate({ time: "500ms", opacity: 1 });
		$.ui.hideMask();
	}
	
	//------ Search
	function search(data){
		// 发起搜索时，重置页码为1
		newsPage = 1;
		$.jsonP({
			url: 		newsUrl() + "&callback=?&search=" + data,
			success: 	showList,
			error: 		core.error
		});
	}
	
	return {
		init:			init,
		loadList: 		loadList,
		loadCat:		loadCat,
		loadNews:		loadNews,
		search:			search
	}
})();
