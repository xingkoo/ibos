/**
* mail
* @author Aeolus
* @copyright IBOS
*/
var Email = (function(){
	var mailCatId = 0,
		mailId = 0,
		mailPage = 1,
		mailUrl = function (){ return app.appUrl + '/mail' };
	var thisMail;

	/**
	* 初始化新闻模块时，载入一些基础数据，比如分类，默认页新闻，未读条数等
	*/
	//-------- Mail Catelog
	function loadCat(){
		$.jsonP({
			url: 		mailUrl() + "/category&callback=?",
			success: 	Email.showCat,
			error: 		core.error
		});
	}
	function showCat(json){
		var $tpl = $("#mailCatTpl"),
			$target = $("#mailCat");
		var tp = $tpl.val(),
			newTp = '',
			obj = {};
			for(var val in json.folders){
				if(mailCatId!=0 && json.folders[val].catid == mailCatId){
					json.folders[val].classname = 'class="active"';
				}else{
					json.folders[val].classname = ' ';
				}
				obj = json.folders[val];
				newTp += $.template(tp, obj);
			}
			if(json.noread>0){
				$.ui.updateBadge("#inbox", json.noread);
			}
			$target.append(newTp);
	}
	
	function _loadMail(url, param, callback) {
		$.ui.showMask();
		$.jsonP({
			url:     url + "&callback=?&" + $.param(param),
			success: function(res) {
				res && callback && callback(res);
				$.ui.hideMask();
			},
			error: core.error
		});
	}

	// --------- Mail View
	function loadMail(id, dom){
		id = typeof id === 'undefined' ? Email.mailId : id;

		if(typeof dom !="undefined"){
			$(dom).parent().removeClass("new"); //取消未读
		}
		$(document).one("loadpanel", function(){
			_loadMail(mailUrl() + "/show", { id: id }, showMail)
		})
		$.ui.loadContent("view/mail/mail_view.html", 0, 0)
	}

	function loadDraft(id) {
		id = typeof id === 'undefined' ? Email.mailId : id;
		$(document).one("loadpanel", function(){
			_loadMail(mailUrl() + "/draftshow", { bodyid: id }, showDraft)
		})
		$.ui.loadContent("view/mail/mail_view.html", 0, 0)
	}

	function _show (tpl, json) {
		var $target = $("#mailContent"),
			newTp = $.template(tpl, json);
		$target.html(newTp).css3Animate({ time: "500ms", opacity: 1 });
		mailId = json.emailid;
	}

	function showMail(json){
		_show($("#mailContentTpl").val(), json);
		mailId = json.emailid;
		thisMail = json;
		bodyId = 0;
	}

	function showDraft(json){
		_show($("#mailContentTpl").val(), json);
		bodyId = json.bodyid;
		mailId = 0;
	}
	function search(data){
		MailInbox.loadList({ "search": data });;
	}

	function editMail(data){
		data = $.extend({
			subject: "",
			content: "",
			user: null
		}, data);

		app.param.set("mailEditData", data)

		$.ui.loadContent("view/mail/mail_edit.html", 0, 0);
	}
	
	// ----------- Send
	function sendMail(){
		var data = {},
			user = User.get("mail_user").get();
			toids = [],
			ccids = [],
			mcids = [];

		data.subject = $("#mailSubjectInput").val(),
		data.content = $("#mailContentInput").val();
		if(user.length){
			for(var i = 0; i < user.length; i++) {
				// 抄送
				if(user[i].type === "green") {
					ccids.push(user[i].id);
				// 密送
				} else if(user[i].type === "red") {
					mcids.push(user[i].id);
				//  收件人
				}else{
					toids.push(user[i].id);
				}
			}
			data.toids = toids.join(",");
			data.ccids = ccids.join(",");
			data.mcids = mcids.join(",");
		}
		if( data.subject=="" || data.content=="" || !data.toids) {
			$.ui.popup("收件人(蓝色)、主题和内容都不可为空！");
			return false;
		};
		$.ui.showMask("邮件发送中...");

		$.jsonP({
			url: 		mailUrl() + "/edit&callback=?&" + $.param(data),
			success: 	function(){
				$.ui.hideMask();
				MailInbox.loadList()
				$.ui.goBack();
			},
			error: 		core.error
		});
	}
	// Todo
	function markMail(){
		if(mailId) {
			ismark =thisMail.ismark=="1"?false:true;
			$.jsonP({
				url: 		mailUrl() + "/mark&callback=?&emailid=" + mailId + "&ismark="+ismark,
				success: 	function(){
					$.ui.hideMask();
					MailInbox.loadList()
					$.ui.goBack();
				},
				error: 		core.error
			});
		}
	}

	function replyMail(){
		var subject = $("#mailSubjectHidden").val(),
			toid = $("#mailFromIdHidden").val();
		var user = [{ id: toid, text: app.getUserName(toid)}];
		editMail({
			subject: "回复：" + subject,
			user: user
		})
	}

	function replyAll(){
		var subject = $("#mailSubjectHidden").val(),
			toid = $("#mailFromIdHidden").val(),
			copyArr = $("#mailCopyToIdsHidden").val().split(",")

		var user = [{ id: toid, text: app.getUserName(toid)}];

		for(var i = 0; i < copyArr.length; i++){
			if(copyArr[i]){
				user.push({id: copyArr[i], text: app.getUserName(copyArr[i])})
			}
		}

		editMail({
			subject: "回复：" + subject,
			user: user
		})
	}

	/**
	 * 删除邮件
	 * @method deleteMail
	 * @return {[type]} [description]
	 */
	function deleteMail(){
		var param = {}
		if(mailId) {
			param.emailid = mailId
		} else {
			param.bodyid = bodyId
		}
		$.ui.showMask();
		EmailModel.deleteMail(param, function(){
			$.ui.goBack();
			if(mailId) {
				$("#mail_item_" + mailId, $.ui.activeDiv).remove();
				mailId = 0;
			} else {
				$("#mail_item_" + bodyId, $.ui.activeDiv).remove();
				bodyId = 0;
			}
			$.ui.hideMask();
		})
	}

	/**
	 * 选择收件人
	 * @method selectRecipient
	 * @return {[type]} [description]
	 */
	function selectRecipient(){
		var defValues = User.get("mail_user").get();
		app.openSelector({
			buttons: [{text: "抄送", type: "green"}, {text: "密送", type: "red"}],
			values: defValues,
			onSave: function(evt, data){
				User.get("mail_user").set(data.values);
				$.ui.goBack();
			}
		})
	}

	return {
		loadCat:		loadCat,
		loadMail:		loadMail,
		loadDraft:      loadDraft,
		showCat:		showCat,

		sendMail:       sendMail,
		editMail:       editMail,

		markMail:       markMail,  
		replyMail:      replyMail,
		replyAll:       replyAll,
		deleteMail:     deleteMail,

		selectRecipient: selectRecipient,
		search:			search
	}
})();


var EmailModel = {
	_mailUrl: app.appUrl + "/mail",
	deleteMail: function(param, callback){
		param = param || {}
		$.jsonP({
			url: this._mailUrl + "/del&callback=?&" + $.param(param),
			success: callback,
			error: core.error
		})
	}
}


var MailInbox = (function(){
	var listIns,
		mainListIns;

	var init = function(){
		listIns = new List("mailList", $.query("#mailListTpl").val());
		mainListIns = new MainList(listIns, { url: app.appUrl + '/mail' })
		mainListIns.load({ "type": "inbox" });;
	}

	var loadList = function(param, callback){
		
		param = $.extend({
			type: "inbox",
			catid: 0,
			page: 1
		}, param) ;

		mainListIns.load(param);;
	}

	// 读取列表
	return {
		init: init,
		loadList: loadList
	}
})();


app.evt.add({
	deleteMailFormList: function(param, elem){
		$.ui.showMask();
		Slide.instants.mailList.close();
		EmailModel.deleteMail(param, function(){
			$(elem).closest("li").remove();
			$.ui.hideMask();
		});
	}
})

