/**
* Diary
* @author Aeolus
* @copyright IBOS
*/

var diaryModel = {
	_baseUrl: app.appUrl + "/diary",

	// 读取单条日志的数据
	loadDiary: function(id, callback) {
		if(id == null) {
			return false;
		}
		$.jsonP({
			url: 	this._baseUrl + "/show&callback=?&id=" + id,
			success:  function(){
				callback && callback.apply(this, arguments);
			},
			error: 	core.error
		});
	}
}

var diaryView = {
	renderDiaryView: function(data){
		var tpl = $("#diaryContentTpl").val(),
			$container = $("#diaryContent");
		$container.html($.template(tpl, data));
	},
	renderDiaryEdit: function(data){
		var $container = $("#diaryEditContent"),
			tpl = $("#diaryEditTpl").val();
		$container.html($.template(tpl, data));
	}
}

var diary = (function(){
	var diaryCatId = 0, // 当前分类, 0为默认，显示所有
		diaryId = 0, //

		diaryPage = 1, // 当前页码
		reviewPage = 1,
		reviewUid = 0,
		diaryUrl = app.appUrl + '/diary';

	var list;
		
	/**
	* 初始化新闻模块时，载入一些基础数据，比如分类，默认页新闻，未读条数等
	*/
	function init(){

		list = new List('diaryList', $("#diaryListTpl").val(), {"id": "diaryid"});
		//diary.loadCat();
		diary.loadList(diaryCatId);

		isInit = true;
	}
	//------ diary List
	function loadList(catid, page){
		//$(this).parent().addClass("active"); //选中分类
		//$(this).parent().siblings().removeClass("active"); //选中分类
		var pageurl;

		$.ui.showMask();
		// 目录变更
		if(catid !== diaryCatId) {
			diaryCatId = catid;
			diaryPage = 1;
		}
		// 页码变更
		if(typeof page !== "undefined" && page !== diaryPage){
			diaryPage = page;
		}

		pageurl = "&page=" + diaryPage;

		$.jsonP({
			url: 		diaryUrl + "&callback=?&catid=" + diaryCatId + pageurl,
			success: 	showList,
			error: 		core.error
		});
	}

	function showList(json){
		if(diaryPage > 1){
			list.add(json.datas)
		}else{
			list.set(json.datas);
		}
		
		$("#readMorediary").remove();
		if( json.pages.pageCount > diaryPage ){
			$("#diaryList").append('<li id="readMorediary" class="list-more"><a onclick="diary.loadList(' + diaryCatId + ','+( diaryPage + 1) +')">加载更多</a></li>');
		}
		$.ui.hideMask();
		return;
	}

	function loadReview(date,uid,page){
		// ajax 加载新的内容
		$.ui.loadContent("view/diary/diary_comment_list.html", 0, 0);

		url="";
		if(typeof uid != "undefined") {
			url += "&uid=" + uid;
		}
		if(typeof date != "undefined") {
			url += "&date=" + date;
		}
		// 页码变更
		if(typeof page !== "undefined" && page !== reviewPage){
			reviewPage = page;
		}
		pageurl = "&page=" + reviewPage;
		
		$.jsonP({
			url: 		diaryUrl + "/review&callback=?"+ url + pageurl,
			success: 	showReview,
			error: 		core.error
		});
	}

	function loadAttention(date,uid,page){
		// ajax 加载新的内容
		$.ui.loadContent("view/diary/diary_attention_list.html", 0, 0);

		url="";
		if(typeof uid != "undefined") {
			url += "&uid=" + uid;
		}
		if(typeof date != "undefined") {
			url += "&date=" + date;
		}
		// 页码变更
		if(typeof page !== "undefined" && page !== reviewPage){
			reviewPage = page;
		}
		pageurl = "&page=" + reviewPage;
		
		$.jsonP({
			url: 		diaryUrl + "/attention&callback=?"+ url + pageurl,
			success: 	showReview,
			error: 		core.error
		});
	}

	function showReview(json){
		var tp = $("#reviewTpl").val(),
			$target = $("#reviewList");
	
		$target.html($.template(tp, json));
		$.ui.hideMask();
		return;
	}
	
	
	//------ diary Catelog
	function loadCat(){
		$.jsonP({
			url: 		diaryUrl + "/category&callback=?",
			success: 	diary.showCat,
			error: 		core.error
		});
	}
	
	function showCat(json){
		// var $tpl = $("#diaryCatTpl"),
			// $target = ;
		// var tp = $tpl.val(),
			// newTp = '',
			// obj = {};
			// for(var val in json){
				// if(diaryCatId!=0 && json[val].catid == diaryCatId){
					// json[val].classname = 'class="active"';
				// }else{
					// json[val].classname = ' ';
				// }
				// obj = json[val];
				// newTp += $.template(tp, obj);
			// }
		// $target.append(newTp);
		$("#diaryCat").append(json);
	}
	
	//------ diary View
	function loadDiary(id){
		$.ui.showMask();
		diaryModel.loadDiary(id, function(res){
			$.ui.hideMask();
			showDiary(res);
			diaryId = id;
		})
	}

	function loadAdjacentDiary(id){
		$.ui.showMask();
		diaryModel.loadDiary(id, function(res){
			diaryView.renderDiaryView(res);
			$.ui.hideMask();
		})
	}

	function showDiary(json){
		$(document).one("loadpanel", function(){
			diaryView.renderDiaryView(json);
		})

		$.ui.loadContent("view/diary/diary_view.html", 0, 0);
	}
	
	
	function editDiary(id, callback){
		diaryModel.loadDiary(id, function(res){
			$(document).one("loadpanel", function(){
				diaryView.renderDiaryEdit(res);
			});
			$.ui.loadContent("view/diary/diary_edit.html", 0, 0);
		})
	}
	
	function addDiary(date, callback){
		$.jsonP({
			url: 	diaryUrl + "/add&callback=?",
			success: function(res) {
				// 当今日已经写了日志时，给予提示
				if(res.msg) {
					app.ui.alert(res.msg);
					return false;
				}
				$(document).one("loadpanel", function(){
					diaryView.renderDiaryEdit(res)
				});
				$.ui.loadContent("view/diary/diary_edit.html", 0, 0)
			},
			error: 	core.error
		});
	}
	
	//------ Search
	function search(data){
		// 发起搜索时，重置页码为1
		diaryPage = 1;
		$.jsonP({
			url: 		diaryUrl + "&callback=?&search=" + data,
			success: 	showList,
			error: 		core.error
		});
	}

	function _addItemBeforeLast(container, tpl, data){
		var $item = $($.template(tpl, data)).insertBefore($(container).find("li").eq(-1));
		// 如果新建时正在编辑状态，则给新建项赋予焦点，目的是不用关闭键盘
		if(/input|textarea|select/.test(document.activeElement.nodeName.toLowerCase())) {
			$item.find("input").focus(); 
		}
		return $item;
	}
	function _removeItem(elem){
		return $(elem).parent().parent().remove();
	}

	function addDiaryRecord(){
		var tpl = document.getElementById('diaryRecordTpl').value,
			container = document.getElementById("diaryRecordList");
		return _addItemBeforeLast(container, tpl, {timestamp:(new Date()).valueOf()})
	}

	function addDiaryPlan(){
		var tpl = document.getElementById('diaryPlanTpl').value,
			container = document.getElementById("diaryPlanList");
		return _addItemBeforeLast(container, tpl, {})
	}

	// 避免重复提交
	var submitLock = false;
	function submit(){
		if(submitLock) {
			return false;
		}
		submitLock = true;
		var diaryEditForm = document.getElementById("diaryEditForm");
			contentElem = diaryEditForm.diaryContent;

		if(diaryEditForm.diaryContent.value.trim() === "") {
			app.ui.alert("需要完整填好各项内容才能提交");
			return false;
		}

		$.ui.showMask("提交中");
		$.query("#hidden_frame").one("load", function(){
			$.ui.loadContent("diary", 0, 1);
			$.ui.hideMask();
			diary.loadList(0);
			setTimeout(function(){
				submitLock = false;
			}, 230);
		})
		
		diaryEditForm.submit();
	}

	return {
		init:			init,
		loadList: 		loadList,
		loadCat:		loadCat,
		loadDiary:		loadDiary,
		loadAdjacentDiary: loadAdjacentDiary,

		editDiary:      editDiary,
		search:			search,
		loadReview:		loadReview,
		loadAttention:	loadAttention,
		addDiary:		addDiary,
		submit:			submit,

		addDiaryPlan:   addDiaryPlan,
		removeDiaryPlan: _removeItem,
		addDiaryRecord: addDiaryRecord,
		removeDiaryRecord: _removeItem
	}
})();


// Events
(function(){

	app.evt.add({
		// 编辑日志
		"editDiary": function(){
			var currentDiaryId = document.getElementById("diary_current_id").value;
			diary.editDiary(currentDiaryId);
		},

		// 上一篇日志
		"prevDiary": function(){
			var prevDiaryId = document.getElementById("diary_prev_id").value;
			if(prevDiaryId && prevDiaryId != "0") {
				diary.loadAdjacentDiary(prevDiaryId);
			} else {
				app.ui.alert("没有更早的日志了")
			}
		},

		// 下一篇日志
		"nextDiary": function(){
			var nextDiaryId = document.getElementById("diary_next_id").value;
			if(nextDiaryId && nextDiaryId != "0") {
				diary.loadAdjacentDiary(nextDiaryId);			
			} else {
				app.ui.alert("没有更新的日志了")
			}
		},

		"removeDiary": function(){
			var currentDiaryId = document.getElementById("diary_current_id").value;
			// diary.removeDiary();
			diary.loadList();
			$.ui.loadContent("#diary");
		}
	})
})();