/**
 * report.js
 * 工作计划
 * IBOS
 * @module		Global
 * @submodule   Report
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-15
 */

// 工作计划模块命名空间

(function(){
	var TIME_PER_DAY = 86400000,
		keyboard = {
			ENTER: 13,
			DEL: 46,
			BACKSPACE: 8,
			UP: 38,
			DOWN: 40
		}

	var Report = {
		// 新增一行
		addRow: function(handyTable, data, callback) {
			data = data || {};
			handyTable.addRow(data, function($row){
				var $prevRow;
				// 向上移动一行，让“添加一行”选项在最下行
				$prevRow = $row.prev();
				$row.insertBefore($prevRow)
				// 聚焦点新建行的input
				.find("input[type='text']").focus();
				callback && callback($row);
			})
		},
		// 移除一行
		removeRow: function(handyTable, $row, callback){
			var $prevRow = $row.prev();
			// 聚焦点上一行的input
			$prevRow.find("input[type='text']").focus();
			handyTable.removeRow($row);
			handyTable.reorderRows();
			callback && callback($row)
		},
		// 快捷键处理
		editKeydownHandler: function(evt, $row, onAdd, onRemove){
			// 按下“回车键”时，添加一行
			if(evt.which === keyboard.ENTER){
				onAdd && onAdd($row)
			} else if (evt.which === keyboard.BACKSPACE) {
				if(evt.target.value === ""){
					onRemove && onRemove($row);
					evt.preventDefault();
				}
			} else if(evt.ctrlKey){
				// 按下“ctrl + up”, 选中上一行
				if(evt.which === keyboard.UP){
					$row.prev().find(".rp-input").focus();
				// 按下“ctrl + down”, 选中下一行
				}else if(evt.which === keyboard.DOWN){
					$row.next().find(".rp-input").focus();
				}
			}
		},
		/**
		 * 切换详细总结显隐状态
		 * @param  {Jquery} $el            触发对象
		 * @param  {String} [act="show"]   显隐，只有"show", "hide"两种状态
		 * @return {[type]}     [description]
		 */
		toggleDetail: function($el, act){
			var toggleSpeed = 100,
				$item = $el.parents("li").eq(0),
				$summary = $item.find(".rp-summary"),
				$detail = $item.find(".rp-detail");
			if(act === "hide"){
				$detail.slideUp(toggleSpeed);
				$summary.slideDown(toggleSpeed);
				$item.removeClass("open")
			}else{
				$detail.slideDown(toggleSpeed);
				$summary.slideUp(toggleSpeed);
				$item.addClass("open")
			}
		},
		toggleTree: function($tree, callback){
			var isShowed = $tree.css("display") !== "none" ? true : false;
			if(isShowed){
				$tree.hide();
			}else{
				$tree.show();
			}
			callback && callback(isShowed);
		}
	}

	window.Report = Report

})();

var rowSpan = {
	plus: function($el){
		var num = this.get($el);
		num = num ? num + 1 : 1;
		$el.attr("rowspan", num);
	},
	get: function($el){
		return parseInt($el.attr("rowspan"), "10");
	},
	minus: function($el){
		var num = this.get($el);
		num = (num && num > 1) ? num - 1 : 1; 
		$el.attr("rowspan", num);
	}
};

function getParentRow($el){
	return $el.parents("tr").eq(0);
}

// 在$ctx下，找出值为value的option选项并选中
function setOptionSelected($ctx, value){
	$ctx.find("option").each(function(){
		var $el = $(this);
		if($el.val() === value){
			$el.prop("selected", true);
		}
	})
}

//初始化表情函数
function initCommentEmotion($context) {
        //按钮[data-node-type="commentEmotion"]
    $('[data-node-type="commentEmotion"]', $context).each(function(){
        var $elem = $(this),
            $target = $elem.closest('[data-node-type="commentBox"]').find('[data-node-type="commentText"]');
            $elem.ibosEmotion({ target: $target });
        }
    )
}

$(function(){
	var reportModel = {
		// 删除总结
		removeReport: function(ids, callback) {
			if(ids) {
				$.post(Ibos.app.url("report/default/del"), { repids: ids }, callback, "json");
			}
		}
	}

	Ibos.evt.add({
		// 从查看页删除总结
		"removeReport": function(param, elem){
			Ui.confirm(U.lang('RP.SURE_DEL_REPORT'), function(){
				reportModel.removeReport(param.id, function(res){
					if(res.isSuccess) {
						Ui.tip(U.lang("OPERATION_SUCCESS"));
						window.location.href= Ibos.app.url("report/default/index");
					} else {
			            Ui.tip(U.lang("OPERATION_FAILED"), "danger");
					}
				})
			});
		},

		// 从列表页删除总结
		"removeReportFromList": function(param, elem){
			Ui.confirm(U.lang('RP.SURE_DEL_REPORT'), function() {
				reportModel.removeReport(param.id, function(res){
					if(res.isSuccess) {
						Ui.tip(res.msg);
						window.location.reload();
					} else {
						Ui.tip(res.msg, "danger");
					}
				});
			});
		},

		// 从列表页删除多篇总结
		"removeReportsFromList": function(param, elem) {
			var repids = U.getCheckedValue('report[]');
			if(repids){
			   Ui.confirm(U.lang('RP.SURE_DEL_REPORT'), function(){
				   	reportModel.removeReport(repids, function(res){
				   		if(res.isSuccess) {
							Ui.tip(res.msg);
							window.location.reload();
				   		} else {
							Ui.tip(res.msg, "danger");
				   		}
				   	})
			   });
			} else {
			   Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
			}
		},

		"showReportDetail": function(param, elem) {
			var $el = $(elem),
				$item = $el.closest("li"),
				$detail = $item.find(".rp-detail"),
				hasInit = $item.attr("data-init") === "1" ? true : false;

			// 若已缓存，则直接显示
			// 否则AJAX读取内容后，缓存并显示
			if (!hasInit) {
				$item.waiting(null, 'normal');
				$.ajax({
					url: Ibos.app.url('report/default/index', {op: 'showDetail', repid: param.id, fromController: param.fromController}),
					type: "get",
					dataType: "json",
					cache: false,
					success: function(res) {
						if (res.isSuccess === true) {
							$detail.append(res.data);
							// 读取内容后初始化进度条
							$detail.find("[data-toggle='bamboo-pgb']").each(function() {
								var $pgb = $(this),
										defaultValue = +$pgb.parent().find('input').val();
								$pgb.studyplay_star({
									CurrentStar: defaultValue,
									Enabled: false
								});
							});
							$item.attr("data-init", "1");

					// 图章
					if(Ibos.app.g('stampEnable')) {
					    var $stampBtn = $detail.find('[data-toggle="stampPicker"]');
					    if($stampBtn.length) {
					        Ibosapp.stampPicker($stampBtn, Ibos.app.g('stamps'));
					        $stampBtn.on("stampChange", function(evt, data) {
					            // Preview Stamp
					            var stamp = '<img src="' + Ibos.app.g('stampPath') + data.stamp + '" width="150px" height="90px" />',
					                smallStamp = '<img src="'+ data.path + '" width="60px" height="24px" />',
					                $parentRow = $stampBtn.closest("div");

										$("#preview_stamp_" + param.id).html(stamp);
										$parentRow.find(".preview_stamp_small").html(smallStamp);
										Comment && Comment.setParam({stamp: data.value});
									});
								}
								if (Ibos.app.g('autoReview') == '1') {
									$.get(Ibos.app.url("report/review/edit", {'op': 'changeIsreview'}), {repid: param.id});
								}
							}
							Report.toggleDetail($el, "show");
							$detail.show();
							$item.waiting(false);
						} else {
							Ui.tip(res.msg, 'warning');
						}
					}
				});
			} else {
				Report.toggleDetail($el, "show");
			}
		},
		// 收起总结详细
		"hideReportDetail": function(param, elem) {
			Report.toggleDetail($(elem), "hide");
		}

	});


	// 阅读人员ajax
	$("[data-node-type='loadReader']").each(function() {
	    $(this).ajaxPopover(Ibos.app.url("report/default/index", { op: "getReaderList", repid: $.attr(this, "data-id")}));
	});

	//点评人员ajax
	$("[data-node-type='loadCommentUser']").each(function(){
	    $(this).ajaxPopover(Ibos.app.url("report/default/index", { op: "getCommentList", repid: $.attr(this, "data-id")}));
	})
})


