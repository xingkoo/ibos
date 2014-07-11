var Weibo = {
	url: app.appUrl + "/weibo",
	firstId: 0, // 最开始一条微博的ID
	lastId: 0, // 最新一条微博的ID
	loadId: 0,
	loadNewTime: 10000, // 获取最新微博的时间间隔
	loadMore: true, // 标识还有没有更多微博
	// cache:[],
	getData: function(param, callback){
		var _this = this;
		callback = callback || $.noop;
		param = $.param(param);
		$.jsonP({
			url: _this.url + (param ? "&" + param : ""),
			success: callback
		});
	},

	_parseToTpl: function(data){
		var tpl = "";
		if(data && data.length) {
			for(var i = 0; i < data.length; i++) {
				data[i].cache = escape(JSON.stringify(data[i]));
				tpl += $.template(document.getElementById("wb_feed_tpl").value, data[i]);
			}
		}
		return tpl;
	},

	// 读取最新微博
	loadNewFeed: function(param, callback){
		var _this = this;
		param = param || {}
		if(!_this.loadNewLock) {
			_this.loadNewLock = true;
			param.lastid = this.lastId;
			this.getData(param, function(res){
				// 更新各项数值
				_this.firstId = res.firstId
				_this.loadMore = res.loadMore;
				_this.loadNew = res.loadNew;
				_this.loadId = res.loadId;
				_this.loadNewLock = false;

				if(res.list && res.list.length) {
					// 更新视图
					_this.prepentToList(_this._parseToTpl(res.list));
					// 新条数提示
					_this.showNewTip(res.count);
				}
				// 更新“最后更新时间”
				_this.updateLastLoadTime();
				$.trigger(_this, 'loadnewfeed', [res])
				callback && callback(res);
			});
		}
	},
	
	// 读取最新微博数
	loadNewCount: function(){
		var _this = this;
		if(_this.loadNew) {
			this.getData({ lastid: this.lastId }, function(res){
				_this.updateNewCount(res.count);
			})
		}
	},
	
	// 读取更多微博（之前的微博）
	loadMoreFeed: function(param, callback){
		var _this = this;
		param = param || {};
		if(_this.loadMore && !_this.loadMoreLock) {
			_this.loadMoreLock = true;
			param.loadid = this.loadId;
			this.getData(param, function(res){
				_this.firstId = res.firstId
				_this.loadMore = res.loadMore;
				_this.loadNew = res.loadNew;
				_this.loadId = res.loadId;
				_this.loadMoreLock = false;

				if(res.list && res.list.length) {
					_this.appendToList(_this._parseToTpl(res.list));
				};
				$.trigger(_this, 'loadmorefeed', [res])
				callback && callback(res);
			});
		}
	},

	// 读取评论信息
	// param.feedid
	loadComment: function(param, callback){
		param = $.extend({ module: "weibo", table: "feed", moduleuid: app.uid }, param)
		$.jsonP({
			url: this.url + "/getcommentlist&" + $.param(param),
			success: callback || $.noop
		});
	},

	// 读取点赞人员信息
	loadDigg: function(param, callback){
		$.jsonP({
			url: this.url + "/digglist&" + $.param(param),
			success: callback || $.noop
		});
	},

	// 读取关注者列表
	loadFollower: function(param, callback){
		$.jsonP({
			url: this.url + "/follower&" + $.param(param),
			success: callback || $.noop
		});
	},

	// 读取关注列表
	loadFollowing: function(param, callback){
		$.jsonP({
			url: this.url + "/following&" + $.param(param),
			success: callback || $.noop
		});
	},

	// 

	// 赞、取消赞
	digg: function(param, callback){
		$.jsonP({
			url: this.url + "/digg&" + $.param(param),
			success: function(res){
				// @Debug: for test;
				//var res = { digg: 1, count: 6 };
				callback && callback(res);				
			}
		})
	},

	// 向列表后插入html内容
	appendToList: function(html){
		$.query('[data-node="feedList"]', $.ui.activeDiv).append(html);
	},

	// 向列表前插入html内容
	prepentToList: function(html){
		var list = $.query('[data-node="feedList"]', $.ui.activeDiv),
			children = list.children();

		if(children.length) {
			$(html).insertBefore(children.eq(0));
		} else {
			list.append(html);
		}
	},

	updateNewCount: function(count) {
		if(count) {
			$.ui.updateBadge("#navbar_weibo", count, "", "#E25050");
		} else {
			$.ui.removeBadge("#navbar_weibo");
		}
	},
	
	showNewTip: function(count){
		count && app.ui.tip("<strong>" + count + "</strong>条新微博", "weibo")
	},

	startLoadNew: function(){
		var _this = this;
		this._loadNewTimer = setInterval(function(){
			_this.loadNewCount();
		}, this.loadNewTime);
	},

	stopLoadNew: function(){
		clearInterval(this._loadNewTimer);
	},

	bindRefreshEvent: function(panelId, param){
		var scroller = $.ui.scrollingDivs[panelId || $.ui.activeDiv.id];
		scroller.addPullToRefresh();

		$.bind(scroller, "refresh-release", function(){ 
			// LoadNew
			Weibo.loadNewFeed(param);
		})
		// $.bind(scroller, "refresh-trigger", function(){ console.log('trigger'); })
		// $.bind(scroller, "refresh-cancel", function(){ console.log('cancel'); })
		// $.bind(scroller, "refresh-finish", function(){ console.log('finish'); })
	},

	bindLoadMoreEvent: function(panelId, param){
		app.ui.bindScrollInfinite(panelId, function(){
			// LoadMore
			var $morebar = $.query(".wb-morebar", $.ui.activeDiv);
			$morebar.show();
			Weibo.loadMoreFeed(param, function(res){
				if(res.list && res.list.length) {
					$morebar.hide();
				} else {
					$morebar.html("没有更多了...");
					Weibo.loadMore = false;
				}
			})
		});
	},
	
	// 进入个人页
	toPersonalPage: function(uid){
		app.param.set("personalUid", uid);
		$(document).one("loadpanel", function(){
			$LAB.script("js/weibo/weibopersonal.js?" + Math.random());
		});
		$.ui.loadContent("view/weibo/personal.html");
	},

	// 更新 最后更新时间
	updateLastLoadTime: function(){
		var refreshContainer = $.ui.scrollingDivs[$.ui.activeDiv.id].refreshContainer,
			timeNode = $.query("[data-node='feedLoadTime']", refreshContainer),
			time = core.toDatetime(+new Date/1000, "dt");
		timeNode.html(time);
	},

	// 进入@联系人选择
	toAt: function(target){
		app.openSelector({
			title: "联系人",
			tpl: "<dd data-id='<%=uid%>'><img width='30' height='30' style='vertical-align: middle' src='<%= app.getAvatar(uid, 'small')%>'> <%=realname%></dd>",
			onSelect: function(evt, data){
				$(target).insertText("@" + data.text + " ")
				$.ui.goBack();
			}
		})
	},

	// 发布 转发
	publish: function(param){
		// @Todo: 考虑附件及图片等情况
		// 生成预览
		var _this = this;
		var feed;
		param = $.extend({
			content: "",
			uid: app.uid,
			module: "weibo",
			table: "feed",
			ctime: false,
			type: "feed",
			view: 0,
			from: app.OS,
			repostcount: 0,
			diggcount: 0,
			commentcount: 0,
			isrepost: 0,
			feedid: "",
			attach_url: ""
		}, param);
		$.jsonP({
			url: 	this.url + "/add&callback=?&" + $.param(param),
			success: function(data) {
			//feed = $(this._parseToTpl([param]))[0]
			$.ui.loadContent("#weibo");
			//this.prepentToList(feed);
			// 在ajax后替换真实数据
			// this.getData({}, function(res){
				// 测试数据
				res = data.data;
				_this.loadNewFeed();
				app.ui.tip("发布成功", "weibo");
				// setTimeout(function(){
				// 	feed.parentNode.replaceChild($(_this._parseToTpl([res]))[0], feed);
				// 	app.ui.tip("发布成功", "weibo");
				// }, 2000);
			// })
			},
			error: 	core.error
		});
	},

	// 发布 转发
	share: function(param){
		var _this = this;
		var feed;
		param = $.extend({
			content: "",
			uid: app.uid,
			module: "weibo",
			table: "feed",
			ctime: false,
			type: "feed",
			view: 0,
			from: app.OS,
			repostcount: 0,
			diggcount: 0,
			commentcount: 0,
			isrepost: 0,
			feedid: "",
			attach_url: ""
		}, param);
		$.jsonP({
			url: 	this.url + "/share&callback=?&" + $.param(param),
			success: function(data) {
				$.ui.loadContent("#weibo");
				// res = data.data;
				_this.loadNewFeed();
				app.ui.tip("转发成功", "weibo");
			},
			error: 	core.error
		});
	},

	// 查看微博正文
	detailFromList: function(param){
		var _this = this,
			sourceNode;

		param = param || {};
		this.currentFeedId = param.feedId;
		sourceNode = $.query('[data-node="feedBox"][data-id="' + param.feedId + '"]', $.ui.activeDiv)[0];

		$(document).one("loadpanel", function(){
			// 由于从列表点入详细页时，微博内容是一样的，所以直接clone;
			var targetNode = document.getElementById("wb_feed_view"),
				cloneNode = sourceNode.cloneNode(true);
			// 移除工具条
			$.query('[data-evt="feedDetail"]', cloneNode).removeAttr("data-evt");
			$.query(".cmflex", cloneNode).remove();
			
			// 读取评论信息
			_this.loadComment({ feedid: param.feedId }, function(res){
				var cmHtml = "";
				if(res && res.length) {
					$.each(res, function(index, cm){
						cmHtml += $.template(document.getElementById("wb_comment_item_tpl").value, cm);
					});
					document.getElementById("wb_comment_count").innerHTML = res.length;
					document.getElementById("wb_comment_list").innerHTML = cmHtml;
				}
			});
			// 读取赞列表
			_this.loadDigg({ feedid: param.feedId }, function(res){
				var diggHtml = "";
				if(res.count != "0") {
					$.each(res.data, function(index, digg){
						diggHtml += $.template(document.getElementById("wb_digg_item_tpl").value, digg);
					});
					document.getElementById("wb_digg_count").innerHTML = res.count;
					document.getElementById("wb_digg_list").innerHTML = diggHtml;
				}
			});

			targetNode.parentNode.replaceChild(cloneNode, targetNode);
		});
		$.ui.loadContent("view/weibo/view.html");
	},

	// 评论
	comment: function(param, callback){
		param = $.extend({ op: "comment",table:"feed",rowid:param.feedId,module:"weibo",moduleuid:app.user.id }, param);
		$.jsonP({
			url: this.url + "/addcomment&" + $.param(param),
			success: callback
		})

		// this.getData(param, callback)
	},

	// 关注
	follow: function(param, callback){
		param = $.extend({ op: "follow" }, param);
		this.getData(param, callback);
	},

	// 取消关注
	unfollow: function(param, callback){
		param = $.extend({ op: "unfollow" }, param);
		this.getData(param, callback);
	},
	getpic : function(){
		appSdk.myCamera.getPicture(function(data){
            deferred.resolve(data);
        },"gogo.jpg",{quality: 80, targetWidth: 120, targetHeight: 120});
	},

	// 摄像头拍照
    takePicture: function () {
		var popover = new CameraPopoverOptions(300, 300, 100, 100, Camera.PopoverArrowDirection.ARROW_ANY);
        var deferred  = when.defer(),
            destinationType=navigator.camera.DestinationType,
            options = {
                quality: 50,
                destinationType: destinationType.FILE_URI,
                //sourceType: Camera.PictureSourceType.PHOTOLIBRARY,
                cameraDirection: Camera.Direction.FRONT,
                targetWidth: 300,
                targetHeight: 300,
                //correctOrientation: true
				popoverOptions: popover
        };
        navigator.camera.getPicture(function(data){
            deferred.resolve(data);
        }, null, options);
        
        return deferred.promise
    },
	//上传图片到服务器
    uploadPicture: function( imageURI ){
        var deferred  = when.defer(),
            options = new FileUploadOptions();
        options.fileKey = "avatar",
        options.fileName = imageURI.substr(imageURI.lastIndexOf('/')+1);
        options.mimeType = "image/jpeg";
		$.ui.showMask("正在上传");
        
        var ft = new FileTransfer();
        //上传回调
        ft.onprogress = setup.showUploadingProgress;
        navigator.notification.progressStart("", "当前上传进度");
		ft.upload( imageURI, encodeURI(app.appUrl + '/setting/upload'), function(){ 
            deferred.resolve( imageURI );
			navigator.notification.progressStop();
        } , null, options);
        return deferred.promise
    },
    // 显示上传进度
    showUploadingProgress: function( progressEvt ){
        if( progressEvt.lengthComputable ){
            navigator.notification.progressValue( Math.round( ( progressEvt.loaded / progressEvt.total ) * 100) );
        }
    },
    // 从缓存中删除图片
    deletePictureFromCache: function( imageURI ){
        window.resolveLocalFileSystemURI(fileURI, function( fileEntry ){
            fileEntry.remove();
        }, null);
    }
}


app.evt.add({
	"toPersonalPage": function(param){
		var uid = (param && param.uid) || app.uid;
		Weibo.toPersonalPage(uid);
	},

	// 进入发布微博页
	"toFeedPublish": function(param){
		$.ui.loadContent("view/weibo/publish.html");		
	},

	// 点赞，根据是否赞决定实际行为
	"feedDigg": function(param, elem){
		param = $.extend({
			feedid: Weibo.currentFeedId
		}, param);
		Weibo.digg(param, function(res){
			if(res.digg){
				elem.innerHTML = '<i class="ao16 ao-digg-orange"></i> <span class="xco">' + res.count + '</span>';
			} else {
				elem.innerHTML = '<i class="ao16 ao-digg-gray"></i> <span>' + res.count + '</span>';
			}
		})
	},
	// 点赞， 底部工具条触发
	"feedDiggFromBar": function(param, elem){
		param = $.extend({
			feedId: Weibo.currentFeedId
		}, param);
		Weibo.digg(param, function(res){
			elem.innerHTML = '<i class="ao16 ' + (res.digg ? "ao-digg-orange" : "ao-digg") + '"></i>';
		})
	},

	// 选择要@的联系人
	"toAt": function(param){
		Weibo.toAt(param.target);
	},

	// 发布
	"feedPublish": function(param){
		var content = document.getElementById("wb_publish_textarea").value;
		if(content.trim() === "") {
			app.ui.tip("请输入微博内容", "weibo");
			return false;
		}
		Weibo.publish({
			content: content,
			body:content
		});
	},

	// 进入转发页
	"toFeedForward": function(param){
		param = $.extend({
			feedId: Weibo.currentFeedId
		}, param);
		var dataStr = $.query("[data-id='" + param.feedId + "']", $.ui.activeDiv)[0].getAttribute("data-cache"),
			data = JSON.parse(unescape(dataStr));

		$(document).one("loadpanel", function(){
				res = data;
				document.getElementById("wb_fw_preview").innerHTML = $.template(document.getElementById("wb_forward_src_tpl").value, res);
				document.getElementById("wb_forward_cmuser").innerHTML = app.getUser(res.uid).realname;
				if(res.isrepost == "1") {
					document.getElementById("wb_forward_textarea").value = "//@" + app.getUser(res.uid).realname + "：" + res.content;
				}
				app.param.set("feedForwardData", res);
		});
		$.ui.loadContent("view/weibo/forward.html");
	},

	"feedForward": function(param){
		var content = document.getElementById("wb_forward_textarea").value;
		// 同时评论给
		var comment = document.getElementById("wb_forward_to_comment").checked;
		var fdData = app.param.get("feedForwardData");

		if(content.trim() === "") {
			app.ui.tip("请输入微博内容", "weibo");
			return false;
		}
		data = $.extend({
			content: content,
			body: content,
			comment: + comment,
			type: fdData.table,
			sid: fdData.feedid,
			curid: fdData.feedid,
			api_source: {
				type: fdData.type,
				attach_url: fdData.attach_url,
				feedcontent: fdData.content
			}
		},fdData);
		Weibo.share(data);

		//app.param.remove("feedForwardData");
	},

	// 进入正文详细页
	"feedDetail": function(param){
		param = $.extend({feedId: param.feedid}, param);
		Weibo.detailFromList(param);
	},

	// 进入评论页
	"toFeedComment": function(param){
		param = $.extend({
			feedid: Weibo.currentFeedId
		}, param);
		app.param.set("commentFeedParam", param);
		$.ui.loadContent("view/weibo/comment.html");
	},

	// 评论
	"feedComment": function(param){		
		var content = document.getElementById("wb_comment_textarea").value;
		// 同时转发
		var forward = document.getElementById('wb_comment_to_publish').checked;
		if(content.trim() === "") {
			app.ui.tip("请输入评论内容", "weibo");
			return false;
		}

		Weibo.comment($.extend(app.param.get("commentFeedParam"), {
			content: content,
			forward: +forward
		}), function(){
			// $.ui.goBack();
			// app.param.remove("commentFeedParam");
			// 
			$.ui.loadContent("#weibo");
			Weibo.loadNewFeed();
			app.ui.tip("评论成功", "weibo");
		})
	},

	// 查看粉丝列表
	// @Todo: 考虑实现滚动加载更多
	"viewFollowerList": function(param){
		$(document).one("loadpanel", function(){
			var _createTpl = function(data) {
				var _tpl = "";
				$.each(data, function(k, v){
					_tpl += $.template(document.getElementById('wb_follow_item_tpl').value, {
						uid: k,
						following: v.following,
						follower: 1
					});
				});
				return _tpl;
			}

			Weibo.loadFollower(param, function(res){
				document.getElementById("wb_follow_list").innerHTML = _createTpl(res.list);
			});

			app.ui.bindScrollInfinite("weibo_follower", function(){
				Weibo.loadFollowing(param, function(res){
					$.query("#wb_follow_list").append(_createTpl(res.list));
				});
			})
		});
		$.ui.loadContent("view/weibo/follower.html");
	},

	// 查看关注列表
	// @Todo: 考虑实现滚动加载更多
	"viewFollowingList": function(param){
		$(document).one("loadpanel", function(){
			setTimeout(function(){
				$.ui.setTitle("全部关注");
			}, parseInt($.ui.transitionTime, 10));

			var _createTpl = function(data) {
				var _tpl = "";
				$.each(data, function(k, v){
					_tpl += $.template(document.getElementById('wb_follow_item_tpl').value, {
						uid: k,
						following: 1,
						follower: v.follower
					});
				});
				return _tpl;
			}

			Weibo.loadFollowing(param, function(res){
				document.getElementById("wb_follow_list").innerHTML = _createTpl(res.list);
			});

			app.ui.bindScrollInfinite("weibo_follower", function(){
				Weibo.loadFollowing(param, function(res){
					$.query("#wb_follow_list").append(_createTpl(res.list));
				});
			})
		});
		$.ui.loadContent("view/weibo/follower.html");
	},

	// 关注
	"follow": function(param, elem){
		Weibo.follow(param, function(res){
			// 有互相关注，关注两种状态
			if(res.followEach == "1") {
				elem.innerHTML = '<i class="ao16 ao-mutual-gray"></i> 互相关注';
			} else {
				elem.innerHTML = '<i class="ao16 ao-ok-gray"></i> 已关注';
			}
			elem.setAttribute("data-evt", "unfollow");
			app.ui.tip("关注成功", "weibo");
		});
	},

	// 取消关注
	"unfollow": function(param, elem){
		Weibo.unfollow(param, function(res){
			elem.innerHTML = '<i class="ao16 ao-plus-green"></i> 关注';
			elem.setAttribute("data-evt", "follow");
			app.ui.tip("取消关注成功", "weibo");
		});
	},

	// 从列表关注
	"followFromList": function(param, elem){
		Weibo.follow(param, function(res){
			// 有互相关注，关注两种状态
			if(res.followEach == "1") {
				elem.innerHTML = '<i class="ao22 ao-mutual-gray"></i>'
			} else {
				elem.innerHTML = '<i class="ao22 ao-ok-gray"></i>'
			}
			elem.setAttribute("data-evt", "unfollowFromList");
			app.ui.tip("关注成功", "weibo");
		});
	},

	// 从列表取消关注
	"unfollowFromList": function(param, elem){
		Weibo.unfollow(param, function(res){
			elem.innerHTML = '<i class="ao22 ao-plus-green"></i>'
			elem.setAttribute("data-evt", "followFromList");
			app.ui.tip("取消关注成功", "weibo");
		});
	}
})


