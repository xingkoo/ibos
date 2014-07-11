/**
 * reportType.js
 * 工作计划
 * IBOS
 * @module		Global
 * @submodule   Report
 * @author		gzhzh
 * @version		$Id$
 * @modified	2013-12-19
 */

// 工作计划模块命名空间

(function() {

	// 汇报类型设置
	$("#rp_type_setup").on("click", function() {
		$.artDialog({
			title: U.lang('RP.REPORT_TYPE_SETTING'),
			id: "report_type_dialog",
			content: Dom.byId("d_report_type"),
			ok: false,
			cancel: true,
			cancelVal: L.CLOSE,
			padding: "0 0"
		});
	});
	var $rpTypeTable = $("#rp_type_table"),
			$rpTypeTbody = $("#rp_type_tbody"),
			rpTypeTable = new Ibos.HandyTable($rpTypeTbody, {tplid: 'rp_type_tpl'}),
			$rpTypeAsideList = $("#rp_type_aside_list"),
			rpTypeAsideList = new Ibos.HandyTable($rpTypeAsideList, {tplid: 'rp_type_sidebar_tpl'});

	// 新增一行
		$rpTypeTable.on("click", ".o-plus", function() {
			var $el = $(this),
					$row = getParentRow($el),
					$sort = $row.find("input[name='sort']"),
					$typename = $row.find("input[name='typename']"),
					$intervaltype = $row.find("[name='intervaltype']"),
					$intervals = $row.find("[name='intervals']"),
					typeData = {
				sort: $sort.val(),
				typename: $typename.val(),
				intervaltype: $intervaltype.val(),
				intervals: $intervals.val()
			};
			if ($sort.val() === '') {
				Ui.tip(U.lang('RP.SORT_CAN_NOT_BE_EMPTY'), 'warning');
				$sort.blink();
				return false;
			} else if (!U.isPositiveInt($sort.val())) {
				Ui.tip(U.lang('RP.SORT_ONLY_BE_POSITIVEINT'), 'warning');
				$sort.blink();
				return false;
			} else if ($typename.val() === '') {
				Ui.tip(U.lang('RP.TYPENAME_CAN_NOT_BE_EMPTY'), 'warning');
				$typename.blink();
				return false;
			}
			if ($intervaltype.val() == '5'){ 
				if ($intervals.val() === '') {
					Ui.tip(U.lang('RP.INTERVALS_CAN_NOT_BE_EMPTY'), 'warning');
					$intervals.blink();
					return false;
				}
				if(!U.isPositiveInt($intervals.val())) {
					Ui.tip(U.lang('RP.INTERVALS_ONLY_BE_POSITIVEINT'), 'warning');
					$intervals.blink();
					return false;
				}
			}
			$.post(Ibos.app.url('report/type/add'), {typeData: typeData}, function(data) {
				// AJAX成功后，返回数据，添加一行
				if (data.isSuccess === true) {
					rpTypeTable.addRow(data);
					rpTypeAsideList.addRow(data);
					Ui.tip(data.msg, 'success');
				} else {
					Ui.tip(data.msg, 'danger');
				}
			}, 'json');
		})
		// 移除一行
		.on("click", "[data-click='delType']", function() {
			var $el = $(this),
					typeid = $el.attr("data-id"),
					$row = getParentRow($el);
			$.artDialog.confirm(U.lang('RP.SURE_DEL_REPORT_TYPE'), function() {
				$.post(Ibos.app.url('report/type/del'), {typeid: typeid}, function(data) {
					// AJAX成功后，移除一行
					if (data.isSuccess === true) {
						rpTypeTable.removeRow($row);
						$rpTypeAsideList.find("li[data-id='" + typeid + "']").remove();
						Ui.tip(data.msg, 'success');
					} else {
						Ui.tip(data.msg, 'danger');
					}
				}, 'json');
			});
		})
		// 编辑
		.on("click", "[data-click='editType']", function() {
			var $el = $(this),
					typeid = $el.attr("data-id"),
					$row = getParentRow($el),
					data,
					$newRow;
			// 进入编辑状态
			data = U.getFormData($row, {name: "data-name", value: "data-value"});
			data.typeid = typeid;
			$newRow = $.tmpl("rp_type_edit_tpl", data);
			$newRow.insertBefore($row);
			$row.remove();
			setOptionSelected($newRow, data.intervaltype);
			$newRow.find("[data-click='editType']").attr("data-click", "saveType").html(U.lang('SAVE')).next().attr("data-click", "cancel").html(U.lang('CANCEL'));
			var $oldIntervalType = $newRow.find("[name='intervaltype']");
			if($oldIntervalType.val() == '5'){
				$oldIntervalType.next().show();
			}
			// 点击取消
			$newRow.on("click", "[data-click='cancel']", function() {
				$row.insertBefore($newRow);
				$newRow.remove();
			})
					// 点击保存
					//////基本跟添加一样，TODO提取出来
					.on('click', "[data-click='saveType']", function() {
				var $el = $(this),
						typeid = $el.attr("data-id"),
						$row = getParentRow($el),
						$sort = $row.find("input[name='sort']"),
						$typename = $row.find("input[name='typename']"),
						$intervaltype = $row.find("[name='intervaltype']"),
						$intervals = $row.find("[name='intervals']"),
						typeData = {
					sort: $sort.val(),
					typename: $typename.val(),
					intervaltype: $intervaltype.val(),
					intervals: $intervals.val()
				};
				if ($sort.val() === '') {
					Ui.tip(U.lang('RP.SORT_CAN_NOT_BE_EMPTY'), 'warning');
					$sort.blink();
					return false;
				} else if (!U.isPositiveInt($sort.val())) {
					Ui.tip(U.lang('RP.SORT_ONLY_BE_POSITIVEINT'), 'warning');
					$sort.blink();
					return false;
				} else if ($typename.val() === '') {
					Ui.tip(U.lang('RP.TYPENAME_CAN_NOT_BE_EMPTY'), 'warning');
					$typename.blink();
					return false;
				}
				if ($intervaltype.val() == '5'){ 
					if ($intervals.val() === '') {
						Ui.tip(U.lang('RP.INTERVALS_CAN_NOT_BE_EMPTY'), 'warning');
						$intervals.blink();
						return false;
					}
					if(!U.isPositiveInt($intervals.val())) {
						Ui.tip(U.lang('RP.INTERVALS_ONLY_BE_POSITIVEINT'), 'warning');
						$intervals.blink();
						return false;
					}
				}
				$.post(Ibos.app.url('report/type/edit'), {typeid: typeid, typeData: typeData}, function(data) {
					// AJAX成功后，返回数据，添加一行
					if (data.isSuccess === true) {
						rpTypeTable.addRow(data, function($newRow) {
							$newRow.insertBefore($row);
							$rpTypeAsideList.find("li[data-id='" + typeid + "']").remove();
							$row.remove();
						});
						rpTypeAsideList.addRow(data);
						Ui.tip(data.msg, 'success');
					}
				}, 'json');
			});
		})
		// 区间改变，如果是其他，显示天数框，否则隐藏
		.on('change', "[name='intervaltype']", function(){
			var $el = $(this);
			if( $el.val() == '5' ){
				$el.next().show();
			} else {
				$el.next().hide();
			}
		});
})();