/**
 * officialdoc.js
 * 信息中心模块通用JS
 * @version		$Id$
 */

var Official = {
	// 数据操作
	op: {
		// 删除一篇或多篇公文
		removeDocs: function(ids, callback) {
			if (!ids) {
				return false;
			}

			Ui.confirm(U.lang("DOC.SURE_DEL_DOC"), function() {
				$.post(Ibos.app.url("officialdoc/officialdoc/del"), {docids: ids}, callback, "json");
			});
		},
		// 获取签收人员
		getSign: function(id, callback) {
			if (!id) {
				return false;
			}
			$.post(Ibos.app.url("officialdoc/officialdoc/index", {op: "getSign"}), {docid: id}, callback, "json");
		},
		getNoSign: function(id, callback) {
			if (!id) {
				return false;
			}
			$.post(Ibos.app.url("officialdoc/officialdoc/index", {op: "getUnSign"}), {docid: id}, callback, "json");
		},
		// 获取历史版本
		getVersion: function(id, callback) {
			if (!id) {
				return false;
			}
			$.post(Ibos.app.url("officialdoc/officialdoc/index", {op: "getVersion"}), {docid: id}, callback, "json");
		},
		// 签收公文
		sign: function(id, callback) {
			if (id) {
				$.post(Ibos.app.url("officialdoc/officialdoc/show", {op: "sign"}), {docid: id}, callback, "json");
			}
		},
		// 获取模板内容
		getTemplate: function(tplId, callback) {
			if (tplId) {
				$.post(Ibos.app.url('officialdoc/officialdoc/index', {'op': 'getRcType'}), {typeid: tplId}, callback, "json");
			}
		}
	},
	// 选择公文模板 
	selectTemplate: function(ue, tplId) {
		if (tplId) {
			// 取消模板时，询问是否清空编辑器
			if (tplId == "0") {
				Ui.confirm(U.lang("DOC.CANCEL_TEMPLATE_TIP"), function() {
					ue.ready(function() {
						ue.setContent("");
					});
				});
			} else {
				ue.ready(function() {
					var setTemplate = function() {
						Official.op.getTemplate(tplId, function(res) {
							ue.setContent(res.content);
						})
					}
					if (ue.getContent() !== "") {
						Ui.confirm(U.lang("DOC.USE_TEMPLATE_TIP"), setTemplate);
					} else {
						setTemplate();
					}
				})
			}
		}
	},
	//
	openPostWindow: function(data, name) {
		var tempForm = document.createElement("form");

		tempForm.id = "tempForm1";
		tempForm.method = "post";
		tempForm.action = Ibos.app.url("officialdoc/officialdoc/index", {'op': 'prewiew'});
		tempForm.target = name;

		var hideInput = document.createElement("input");

		hideInput.type = "hidden";
		hideInput.name = "content";
		hideInput.value = data;
		tempForm.appendChild(hideInput);

		//监听事件的方法 打开页面window.open(name);
		tempForm.addEventListener("onsubmit", function() {
			window.open(name);
		});
		document.body.appendChild(tempForm);

		tempForm.submit();
		document.body.removeChild(tempForm);
	},
	getImgsID: function($elem) {
		var ids = $elem.map(function() {
			return $(this).attr("data-id");
		}).get();
		return ids;
	}
}

// 初始化侧栏目录
$(function() {
	var $tree = $("#tree");

	// 左侧分类树初始化
	$tree.waiting(null, "mini");
	$.get(Ibos.app.url("officialdoc/category/index"), function(data) {
		var treeSettings = {
			data: {
				simpleData: {
					enable: true
				}
			},
			view: {
				showLine: false,
				selectedMulti: false
			}
		};

		var selectedNode;
		var treeObj = $.fn.zTree.init($tree, treeSettings, data);
		var sideTreeCategory = new SideTreeCategory(treeObj, {tpl: "tpl_category_edit"});
		sideTreeCategory.add = function(node, setting) {
			var _this = this,
					tpl = setting.tpl || this.settings.tpl || "",
					data = {},
					dialog;

			data.name = "";
			data.pid = node.id
			data.aid = "";
			// 创建父目录下拉选框
			data.optionHtml = this._createCateOptions(data.pid);

			Ui.closeDialog("d_tree_menu")

			dialog = Ui.dialog({
				id: "d_tree_menu",
				title: U.lang('TREEMENU.ADD_CATELOG'),
				content: $.template(tpl, data),
				ok: function() {
					var content = this.DOM.content,
							data = {
								name: content.find('[name="name"]').val(),
								pid: content.find('[name="pid"]').val(),
								aid: content.find('[name="aid"]').val(),
							}

					if ($.trim(data.name) !== "") {
						_this.treeCategory.add(data, setting)
					} else {
						// Ui.tip("", "warning")
					}
				}
			});
		}
		sideTreeCategory.update = function(node, setting) {
			var _this = this,
					tpl = setting.tpl || this.settings.tpl || "",
					data = {},
					dialog;

			// 创建父目录下拉选框
			data = $.extend({
				pid: "",
				name: ""
			}, node);
			data.optionHtml = this._createCateOptions(data.pid);

			Ui.closeDialog("d_tree_menu")

			dialog = Ui.dialog({
				id: "d_tree_menu",
				title: U.lang('TREEMENU.EDIT_CATELOG'),
				content: $.template(tpl, data),
				ok: function() {
					var content = this.DOM.content,
							data = {
								name: content.find('[name="name"]').val(),
								pid: content.find('[name="pid"]').val(),
								aid: content.find('[name="aid"]').val()
							};

					if ($.trim(data.name) !== "") {
						_this.treeCategory.update(node.id, data, setting)
					} else {
						// Ui.tip("", "warning")
					}
				}
			});
		}
		$tree.waiting(false);

		var treeMenu = [
			{
				name: "add",
				text: '<i class="o-menu-add"></i> ' + U.lang("NEW"),
				handler: function(treeNode, categoryMenu) {
					var aid = $("#approval_id").val();
					sideTreeCategory.add(treeNode, {
						url: Ibos.app.url('officialdoc/category/add'),
						success: function(node, tree) {
							var tNode = tree.getNodeByParam("id", node.id);
							tNode.aid = node.aid;
							tree.updateNode(tNode);
							Ui.tip(U.lang('TREEMENU.ADD_CATELOG_SUCCESS'));
						}
					}, {aid: aid});
					categoryMenu.menu.hide();
				}
			},
			{
				name: "update",
				text: '<i class="o-menu-edit"></i> ' + U.lang("EDIT"),
				handler: function(treeNode, categoryMenu) {
					sideTreeCategory.update(treeNode, {
						url: Ibos.app.url('officialdoc/category/edit'),
						success: function(node, tree) {
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
						url: Ibos.app.url('officialdoc/category/edit', {op: 'move'}),
						success: function() {
							Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
						},
						error: function() {
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
						url: Ibos.app.url('officialdoc/category/edit', {op: 'move'}),
						success: function() {
							Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
						},
						error: function() {
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
						url: Ibos.app.url('officialdoc/category/del'),
						success: function() {
							Ui.tip(U.lang('TREEMENU.DEL_CATELOG_SUCCESS'));
						},
						error: function(res) {
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

		// 选中当前所在分类
		if (Ibos.app.g("catId") && Ibos.app.g("catId") > 0) {
			var sbTree = $.fn.zTree.getZTreeObj("tree"),
					selectedNode = sbTree.getNodeByParam("id", Ibos.app.g("catId"), null);
			if (selectedNode) {
				sbTree.selectNode(selectedNode);
			}
		}
	}, 'json');

});