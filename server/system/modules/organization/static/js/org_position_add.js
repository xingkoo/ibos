/**
 * Organization/position/add
 */

$(function(){
	// 表单验证
	$.formValidator.initConfig({ formID:"position_add_form",errorFocus:true });
	// 岗位排序号
	$("#order_id").formValidator({
		onFocus: U.lang("RULE.ORDERID")
	})
	.regexValidator({
		regExp:"num1",
		dataType:"enum",
		onError: U.lang("RULE.ORDERID")
	})

	// 岗位名称
	$("#pos_name").formValidator({
		onFocus: U.lang("ORG.POSITION_NAME_CANNOT_BE_EMPTY")
	})
	.regexValidator({
		regExp:"notempty",
		dataType:"enum",
		onError: U.lang("ORG.POSITION_NAME_CANNOT_BE_EMPTY")
	});


	// 权限选择处理
	$("#limit_setup").bindEvents({
		// 选中功能
		"change [data-node='funcCheckbox']": function(){
			$(this).closest("label").toggleClass("active", this.checked);
		},
		// 选中模块 
		"change [data-node='modCheckbox']": function(evt){
			var id = $.attr(this, "data-id");
			Organization.auth.selectMod(id, $.prop(this, "checked"))
		},
		// 选中分类
		"click [data-node='cateCheckbox']": function(evt){
			var id = $.attr(this, "data-id"),
				checked = $.attr(this, "data-checked") === "1"
			Organization.auth.selectCate(id,  !checked);
			$.attr(this, "data-checked", checked ? "0" : "1")
		}
	});


	(function() {
		// 岗位说明表格
		var insTable = $("#org_ins_table"),
			insTbody = insTable.find("tbody"),
			orgInsTable = new Ibos.HandyTable(insTbody, {
				tplid: "org_ins_tpl"
			});
		$("#org_ins_add").on("click", function() {
			orgInsTable.addRow({val: ""});
		});

		insTable.on("click", ".o-trash", function() {
			var id = $.data(this, 'id');
			if (typeof id !== 'undefined') {
				var $removeId = $('#res_del_id'),
					removeId = $removeId.val();
				$removeId.val(removeId ? removeId + "," + id : id);
			}
			var $row = $(this).parents("tr").eq(0);
			orgInsTable.removeRow($row);
			orgInsTable.reorderRows($row);
		});

		// 岗位成员列表
		Organization.memberList.init();
	})();


	// 当选中岗位成员时出现新手引导
	$('#position_member_tab').on('shown.bs.tab', function (e) {
		Ibos.app.guide("org_pos_add", function() {
			Ibos.intro([
				{ 
					element: "#org_member_add", 
					intro: U.lang("ORG.INTRO.POSITION_ADD")
				}
			], function(){
				Ibos.app.finishGuide('org_pos_add')
			});
		})
	})
});