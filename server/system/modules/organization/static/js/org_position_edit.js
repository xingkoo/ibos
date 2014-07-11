/**
 * Organization/position/edit
 */

$(function(){
	// 表单验证
	$.formValidator.initConfig({ formID:"position_edit_form", errorFocus:true });
	$("#order_id").formValidator()
	.regexValidator({
		regExp:"num1",
		dataType:"enum",
		onError: U.lang("RULE.ORDERID")
	})
	$("#pos_name").formValidator()
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



	// 快捷表格操作
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
});