/**
 * Organization/position/index
 */

$(document).ready(function() {

	//高级搜索
    Ibos.search.init();
    Ibos.search.disableAdvance();


	(function() {
		function removePositions(id) {
			var $listTable = $("#org_position_table");
			$listTable.waiting(null, 'mini');
			$.post(Ibos.app.url('organization/position/del'), {id: id}, function(data) {
				$listTable.waiting(false);
				if (data.IsSuccess) {
					var uid = id.split(',');
					$.each(uid, function(i, n) {
						$('#pos_' + n).remove();
					});
					Ui.tip(U.lang("DELETE_SUCCESS"));
				} else {
					Ui.tip(U.lang("DELETE_FAILED"), 'danger');
				}
			}, 'json');
		}
		
        Ibos.evt.add({
        	// 删除选中岗位
        	'removePositions': function(param, elem) {
        		var uid = U.getCheckedValue('positionid');
				if (uid.length > 0) {
					Ui.confirm(U.lang("ORG.DELETE_POSITIONS_CONFIRM"), function() {
						removePositions(uid);
					});
				} else {
					Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
				}
        	},
        	// 删除单个岗位
        	'removePosition': function(param, elem){
        		Ui.confirm(U.lang("ORG.DELETE_POSITIONS_CONFIRM"), function(){
        			removePositions(param.id)
        		})
        	}
        })
	})();


	// 树菜单
	(function() {
		// 左侧分类树初始化
		var settings = {
			data: {
				simpleData: { enable: true }
			},
			view: {
				showLine: false,
				selectedMulti: false
			}
		};

		var $tree = $("#ptree");
		
		$tree.waiting(null, 'mini');
		$.get(Ibos.app.url('organization/category/index'), function(data) {

			var treeObj = $.fn.zTree.init($tree, settings, data);
			$tree.waiting(false);

			var sideTreeCategory = new SideTreeCategory(treeObj, { tpl: "tpl_category_edit" });
			var treeMenu = [
				{ 
					name: "add", 
					text: '<i class="o-menu-add"></i> ' + U.lang("NEW"),
					handler: function(treeNode, categoryMenu){
						sideTreeCategory.add(treeNode, {
							url: Ibos.app.url('organization/category/add'),
							success: function(){
								Ui.tip(U.lang('TREEMENU.ADD_CATELOG_SUCCESS'))
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "update",
					text: '<i class="o-menu-edit"></i> ' + U.lang("EDIT"),
					handler: function(treeNode, categoryMenu) {
						sideTreeCategory.update(treeNode, {
							url: Ibos.app.url('organization/category/edit'),
							success: function(){
								Ui.tip(U.lang('TREEMENU.EDIT_CATELOG_SUCCESS'));
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "moveup",
					text: '<i class="o-menu-up"></i> ' + U.lang("MOVEUP"),
					handler: function(treeNode, categoryMenu) {
						sideTreeCategory.moveup(treeNode, {
							url: Ibos.app.url('organization/category/edit', {op: 'move'}),
							success: function(){
								Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
							},
							error: function(){
								Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_FAILED'), 'danger');
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "movedown",
					text: '<i class="o-menu-down"></i> ' + U.lang("MOVEDOWN"),
					handler: function(treeNode, categoryMenu) {
						sideTreeCategory.movedown(treeNode, {
							url: Ibos.app.url('organization/category/edit', {op: 'move'}),
							success: function(){
								Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
							},
							error: function(){
								Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_FAILED'), 'danger');
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "remove",
					text: '<i class="o-menu-trash"></i> ' + U.lang("DELETE"),
					handler: function(treeNode, categoryMenu) {
						categoryMenu.$ctrl.hide().appendTo(document.body);
						sideTreeCategory.remove(treeNode, {
							url: Ibos.app.url('organization/category/delete'),
							success: function(){
								Ui.tip(U.lang('TREEMENU.DEL_CATELOG_SUCCESS'));
							},
							error: function(res){
								Ui.tip(res.msg, "danger");
							}
						});
						categoryMenu.menu.hide();
					}
				}
			]
			var cate = new TreeCategoryMenu(treeObj, {
				menu: treeMenu
			});

			// 有catid默认选中该树节点
			if(Ibos.app.g('catId') && Ibos.app.g('catId') > 0) {
				var node = treeObj.getNodeByParam("id", Ibos.app.g('catId'), null);
				treeObj.selectNode(node);
			}
		}, 'json');
	})();

});