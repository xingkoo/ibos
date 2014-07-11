var Comment = {
	init: function($ctx, options) {
		if (!$ctx || !$ctx.length) {
			return false;
		}
		this.$ctx = $ctx;
		this.replyLock = 0;
		this.options = options || {};
		this.customParam = {};
		this.defCommentOffset = options ? this.options.defCommentOffset : 0;
		this.defCommentLimit = options ? this.options.defCommentLimit : 10;
		this.defReplyOffset = options ? this.options.defReplyOffset : 0;
		this.defReplyLimit = options ? this.options.defReplyLimit : 10;
		this._bindEvents();
		// 初始化@功能 
		Ibos.atwho($ctx.find("textarea"), {url: Ibos.app.url('message/api/searchat')});
	},
	_bindEvents: function() {
		var that = this;
		this.$ctx.on("click", function(evt) {
			var $target = $(evt.target),
					$item = that._getItemByChild($target),
					$txa = $item.find("textarea"),
					act = $target.attr("data-act"),
					$area,
					content;
			var param = $target.attr("data-param");
			param = param ? $.parseJSON(param) : {};
			switch (act) {
				// 回复
				case "reply":
					var $reply = $($item.find("[data-act='addreply']"));
					$reply.data('touid', param.touid);
					$reply.data('tocid', param.tocid);
					that._setDefaultAt($txa, param.name);
					break;
					// 主回复，会导致回复面板显隐切换
				case "getreply":
					$area = $txa.parent();
					that._setDefaultAt($txa, param.name);
					// 关闭回复面板
					if ($target.hasClass("focus")) {
						$target.removeClass("focus");
						that._hideArea($area);
						// 打开回复面板
					} else {
						$target.addClass("focus");
						that._showArea($area);
					}
					if (!$target.hasClass('loaded')) {
						// modify by banyan：修改为只执行一个进程只执行一次加载
						that._getReply($area.find(".cmt-sub"), param);
						$target.addClass("loaded");
					}
					break;
					// 加载更多评论
				case "loadmorecomment":
					$area = $target.parent().parent();
					that._loadMoreComment($target, $area, that.defCommentLimit, param);
					break;
					// 加载更多回复
				case "loadmorereply":
					$area = $txa.parent().parent();
					that._loadMoreReply($target, $area.find(".cmt-sub"), that.defReplyLimit, param);
					break;
					// 提交回复
				case "addreply":
					if (that.replyLock === 1) {
						return;
					}
					var touid = $target.data("touid"), tocid = $target.data("tocid");
					$target.button("loading");
					content = $txa.val();
					if ($.trim(content) === "") {
						$txa.blink();
						$target.button("reset");
						return false;
					}
					$area = $txa.parent();
					that._addReply($area.find(".cmt-sub"), $.extend({
						content: content,
						touid: touid,
						tocid: tocid
					}, param), function() {
						$txa.val("");
						$target.button("reset");
					});
					// 计数器，预防狂刷回复
					that.replylock = 1;
					setTimeout(function() {
						that.replyLock = 0;
					}, 5000);
					break;
					//提交评论
				case "addcomment":
					content = $txa.val();
					if ($.trim(content) === "") {
						$txa.blink();
						return false;
					}
					$target.button("loading");
					that._addComment($.extend({
						content: content,
						formhash: Ibos.app.g('formHash'),
					}, param, that.customParam), function($elem) {
						var elemId = $elem.attr("id");
						// 定位最新评论
						// 由于最上方有60px fixed层，所以定位需向上修正
						Ui.scrollYTo(elemId, -60)
						$('.no-comment-tip').remove();
						$target.button("reset");
						$txa.val("");
						that.$ctx.trigger("commentAdd", $elem);
					});
					break;
					// 删除回复
				case 'delreply' :
					that._delReply(param, function() {
						var parent = $target.parentsUntil('.cmt-sub');
						parent.fadeOut(function() {
							parent.remove();
						});
					});
					break;
					// 删除评论
				case 'delcomment':
					Ui.confirm(U.lang("CONFIRM_DEL_COMMENT"), function() {
						that._delReply(param, function() {
							var parent = $target.parentsUntil('.cmt');
							parent.fadeOut(function() {
								parent.remove();
							});
							Ui.showCreditPrompt();
						});
					});
					break;
			}
			// Ctrl + enter 发送回复
		}).on("keydown", ".cmt textarea", function() {
			var $elem = $(this);
			if (evt.ctrlKey && evt.which === 13 && $elem.hasClass('reply')) {
				$elem.next().find('button').click();
				$elem.focus();
			}
		});
	},
	_getItemByChild: function($child) {
		return $child.parents("div.cmt-item").eq(0);
	},
	/**
	 * 初始化文本框的值
	 * @param {Jquery} $input 文本框节点
	 * @param {String} name   要@的名字
	 */
	_setDefaultAt: function($input, name) {
		var val = L.REPLY + " @" + name + " ： ";
		$input.focus().val(val);
	},
	_showArea: function($area) {
		$area.show();
	},
	_hideArea: function($area) {
		$area.hide();
	},
	/**
	 * ajax获取最新的回复
	 * @method _getReply 
	 * @private
	 * @param {Jquery}   $feedList    回复列表对象
	 * @param {Object}   data         ajax要传递的参数
	 * @param {Function} callback     ajax成功后的回调, 新增的节点将作为参数
	 */
	_getReply: function($repList, data, callback) {
		if (!this.options.getReplyUrl) {
			return false;
		}
		data.formhash = Ibos.app.g('formHash');
		var that = this;
		// 临时设置高度用于显示 “读取中”状态
		$repList.empty().height(60).waiting(null, "mini");
		$.ajax({
			url: that.options.getReplyUrl,
			data: data,
			type: "post",
			dataType: "json",
			success: function(data) {
				if (data.isSuccess) {
					var $res = $(data.data);
					$repList.height("").stopWaiting().replaceWith($res);
					that._call(callback, $res);
				}
			}
		});
	},
	/**
	 * ajax增加一条回复
	 * @method _addReply 
	 * @private
	 * @param {Jquery}   $feedList    回复列表对象
	 * @param {Object}   data         ajax要传递的参数
	 * @param {Function} callback     ajax成功后的回调, 新增的节点将作为参数
	 */
	_addReply: function($feedList, data, callback) {
		if (!this.options.addUrl) {
			return false;
		}
		var that = this;
		data.formhash = Ibos.app.g('formhash');
		$feedList.waiting(null, "mini");
		$.ajax({
			url: that.options.addUrl,
			data: data,
			type: "post",
			dataType: "json",
			success: function(data) {
				if (data.isSuccess) {
					var $res = $(data.data);
					$feedList.prepend($res).stopWaiting();
					that._call(callback, $res);
				} else {
					Ui.tip(data.msg, 'danger');
					return false;
				}
			}
		});
	},
	_delReply: function(data, callback) {
		if (!this.options.delUrl) {
			return false;
		}
		var that = this;
		$.ajax({
			url: that.options.delUrl,
			data: data,
			type: "get",
			dataType: "json",
			success: function(data) {
				if (data.isSuccess) {
					that._call(callback, data);
				} else {
					Ui.tip(data.msg, 'danger');
					return false;
				}
			}
		});
	},
	/**
	 * ajax增加一条评论
	 * @method _addComment 
	 * @private
	 * @param {Object}   data         ajax要传递的参数
	 * @param {Function} callback     ajax成功后的回调, 新增的节点将作为参数
	 */
	_addComment: function(data, callback) {
		if (!this.options.addUrl) {
			return false;
		}
		var that = this;
		this.$ctx.waiting(null, "mini");
		$.ajax({
			url: that.options.addUrl,
			data: data,
			type: "post",
			dataType: "json",
			success: function(data) {
				if (data.isSuccess) {
					var $res = $(data.data);
					$res.hide().prependTo(that.$ctx);
					that.$ctx.stopWaiting();
					$res.fadeIn();
					// 对新增的textarea初始化@功能
					Ibos.atwho($res.find("textarea"));
					that._call(callback, $res);
				} else {
					Ui.tip(data.msg, 'danger');
					return false;
				}
			}
		});
	},
	_loadMoreComment: function(dom, $comList, offset, data) {
		var that = this;
		data.limit = that.defCommentLimit;
		data.offset = that.defCommentOffset + offset;
		data.loadmore = 1;
		data.type = 'comment';
		data.formhash = Ibos.app.g('formhash');
		$(dom).hide().parent().waitingC();
		var num = data.offset;
		$.ajax({
			url: that.options.getCommentUrl,
			data: data,
			type: "post",
			dataType: "json",
			success: function(data) {
				if (data.isSuccess) {
					var $res = $(data.data);
					that.defCommentOffset = offset;
					$comList.find('#commentMoreFoot').before($res);
					// 如果没有更多条数了，把 ‘更多’操作隐藏
					if (parseInt(data.count, 10) - num < offset) {
						$(dom).show().parent().stopWaiting().hide();
					} else {
						$(dom).show().parent().stopWaiting().show();
					}
				}
			}
		});
	},
	_loadMoreReply: function(dom, $repList, offset, data) {
		var that = this;
		data.limit = that.defReplyLimit;
		data.offset = that.defReplyOffset + offset;
		data.loadmore = 1;
		data.type = 'reply';
		data.formhash = Ibos.app.g('formhash');
		$(dom).hide().parent().waitingC();
		var num = data.offset;
		$.ajax({
			url: that.options.getReplyUrl,
			data: data,
			type: "post",
			dataType: "json",
			success: function(data) {
				if (data.isSuccess) {
					var $res = $(data.data);
					that.defReplyOffset = offset;
					$repList.append($res);
					// 如果没有更多条数了，把 ‘更多’操作隐藏
					if (parseInt(data.count, 10) - num < offset) {
						$(dom).show().parent().stopWaiting().hide();
					} else {
						$(dom).show().parent().stopWaiting().show();
					}
				}
			}
		});
	},
	_call: function(callback /*, args */) {
		var args;
		if ($.isFunction(callback)) {
			args = Array.prototype.slice.call(arguments, 1);
			callback.apply(this, args);
		}
	},
	setParam: function(param) {
		return $.extend(this.customParam, param);
	}
};