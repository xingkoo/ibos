$(function() {
	Ibos.events.add({
		/**
		 * 管理员删除
		 * @returns {undefined}
		 */
		'admindel': function() {
			wfList.batchOpt(Ibos.app.url('workflow/handle/del'), null, function(res, ids) {
				var arr = ids.split(",");
				for (var i = 0; i < arr.length; i++) {
					$("#list_tr_" + arr[i]).remove();
				}
			}, U.lang('WF.CONFIRM_DEL_SELECT_RUN'));
		},
		/**
		 * 强制结束
		 * @returns {undefined}
		 */
		'forceend': function() {
			wfList.batchOpt(Ibos.app.url('workflow/handle/end'), null, function(res, ids) {
				var arr = ids.split(",");
				for (var i = 0; i < arr.length; i++) {
					var $elem = $('#list_tr_' + arr[i]).children().eq(4).children();
					$elem.removeClass("xcgn").addClass("tcm");
					$elem.text(U.lang('WF.HAS_ENDED'));
				}
			}, U.lang('WF.CONFIRM_END_SELECT_RUN'));
		}
	});
	// 列表条数设置
	var $pageNumCtrl = $("#page_num_ctrl"), $pageNumMenu = $("#page_num_menu"), pageNumSelect = new P.PseudoSelect($pageNumCtrl, $pageNumMenu, {
		template: '<i class="o-setup"></i> <span><%=text%></span> <i class="caret"></i>'
	});
	$pageNumCtrl.on("select", function(evt) {
		var url = $pageNumCtrl.attr("data-url") + "&pagesize=" + evt.selected;
		window.location.href = url;
	});

});