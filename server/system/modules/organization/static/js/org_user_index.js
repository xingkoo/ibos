/**
 * Organization/user/index
 */

$(document).ready(function() {
	
	// 接
	if (U.getCookie('hooksyncuser') == '1') {
		Ui.openFrame(U.getCookie('syncurl'), {
			title: U.lang("ORG.SYNC_USER"),
			cancel: true
		});
		U.setCookie('hooksyncuser', '');
		U.setCookie('syncurl', '');
	}
	//搜索
	Ibos.search.init();
	Ibos.search.disableAdvance();


	Ibos.evt.add({
		"setUserStatus": function(param, elem){
			var uid = U.getCheckedValue("user");
			if(!uid) {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
				return false;
			}
			$("#org_user_table").waiting(null, "normal");
			$.get(Ibos.app.url('organization/user/edit'), {op: param.op, uid: uid}, function(res) {
				$("#org_user_table").waiting(false);
				if (res.isSuccess) {
					Ui.tip(U.lang("OPERATION_SUCCESS"));
					window.location.reload();
				} else {
					Ui.tip(U.lang("OPERATION_FAILED"));
				}
			}, 'json');
		},
		"exportUser": function(){
			var uid = U.getCheckedValue("user");
			if(!uid) {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
				return false;
			}
			window.location.href = Ibos.app.url('organization/user/export', { uid: encodeURI(uid) });
		}
	})

	// 初始化右栏树
	var settings = {
		data: {
			simpleData: { enable: true }
		},
		view: {
			showLine: false,
			selectedMulti: false
		}
	},
	$tree = $("#utree");
	$tree.waiting(null, 'mini');
	$.get(Ibos.app.url('organization/user/index', {'op': 'tree'}), function(data) {
		var selectedDeptId = Ibos.app.g("selectedDeptId");
		$.fn.zTree.init($tree, settings, data);
		$tree.waiting(false);
		// 有catid才初始化选中
		if(selectedDeptId && selectedDeptId > 0) {
			var treeObj = $.fn.zTree.getZTreeObj("utree");
			var node = treeObj.getNodeByParam("id", selectedDeptId, null);
			treeObj.selectNode(node);
		}
	}, 'json');
});