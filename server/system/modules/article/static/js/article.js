/**
 * article.js
 * 信息中心模块通用JS
 * IBOS
 * @module		Global
 * @submodule   Article
 * @author		inaki
 * @version		$Id$
 * @modified	2013-05-16 
 */

var Article = Article || {};

// 数据交互
Article.op = {
	/**
	 * 删除新闻
	 * @method removeArticles
	 * @param  {String}   ids      单一的新闻id, 或以“,”分隔的多个id
	 * @param  {Function} [callback] 请求响应后的回调函数
	 * @return 
	 */
	removeArticles: function(ids, callback) {
		if (!ids) {
			return false;
		}

		Ui.confirm(U.lang("ART.SURE_DEL_ARTICLE"), function() {
			$.post(Ibos.app.url("article/default/del"), {articleids: ids}, callback);
		});
	},
	/**
	 * 获取新闻阅读人员
	 * @method getArticleReaders
	 * @param    {String}  id          新闻id
	 * @param    {Function} [callback] 回调函数
	 * @return   {Object}              阅读人员数据
	 */
	getArticleReaders: function(id, callback) {
		if (!id) {
			return false;
		}
		$.post(Ibos.app.url("article/default/index", {"op": "getReader"}), {articleid: id}, callback)
	}
}


$(function() {
	Article.pic = {
		$picIds: $("#picids"),
		_itemPrefix: "pic_item_",
		_getItem: function(id) {
			return $("#" + this._itemPrefix + id);
		},
		initPicItem: function(item, data) {
			var $item = $(item),
					$checkbox = $('<label class="checkbox"><input type="checkbox" name="pic" value="' + data.aid + '"></label>'),
					$img = $('<img class="pull-left" width="100" src="' + data.url + '" />');

			$item.find("i").replaceWith($img);
			$item.prepend($checkbox).find(".o-trash").attr("data-id", data.aid);

			$checkbox.find('input[type="checkbox"]').label();

			$item.attr("id", this._itemPrefix + data.aid);
			// this.addValue(data.aid);
		},
		removeSelect: function(ids) {
			// 数组方式
			if ($.isArray(ids)) {
				for (var i = 0, len = ids.length; i < len; i++) {
					this.remove(ids[i])
				}
				// 非数组时，假设其为字符串，以单个删除
			} else {
				this.remove(ids)
			}
		},
		moveUp: function(id) {
			var $item = this._getItem(id),
					index = $.inArray(id, this._values),
					temp;
			if (index === -1) {
				return false;
			}
			// 当已为最上一项时， 移动到最后面
			if (index === 0) {
				$item.appendTo($item.parent());
				this._values.push(this._values.shift())
			} else {
				// 交换节点位置
				$item.insertBefore($item.prev());
				// 交换数组中的位置
				temp = this._values[index];
				this._values[index] = this._values[index - 1];
				this._values[index - 1] = temp;
			}
			this.refresh();
		},
		moveDown: function(id) {
			var $item = this._getItem(id),
					index = $.inArray(id, this._values),
					temp;
			if (index === -1) {
				return false;
			}
			// 当已为最下一项时， 移动到最前面
			if (index === this._values.length - 1) {
				$item.prependTo($item.parent());
				this._values.unshift(this._values.pop())
			} else {
				// 交换节点位置
				$item.insertAfter($item.next());
				// 交换数组中的位置
				temp = this._values[index];
				this._values[index] = this._values[index + 1];
				this._values[index + 1] = temp;
			}
			this.refresh();
		}
	}


	// 初始化侧栏分类
	var $tree = $("#tree");

	// 左侧分类树初始化
	$tree.waiting(null, "mini");
	$.get(Ibos.app.url("article/category/index"), function(data) {
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
						url: Ibos.app.url('article/category/add'),
						success: function(node, tree) {
							var tNode = tree.getNodeByParam("id", node.id);
							tNode.aid = node.aid;
							tree.updateNode(tNode);
							Ui.tip(U.lang('TREEMENU.ADD_CATELOG_SUCCESS'))
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
						url: Ibos.app.url('article/category/edit'),
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
						url: Ibos.app.url('article/category/edit', {op: 'move'}),
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
						url: Ibos.app.url('article/category/edit', {op: 'move'}),
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
					var tree = categoryMenu.tree,
							topTreeNode = tree.getNodesByParam("pid", "0");
					// 当只有一个顶级节点且当前要删除的是该节点时，不可删除 
					if (topTreeNode.length <= 1 && topTreeNode[0].id === treeNode.id) {
						Ui.tip(U.lang("ART.LEAVE_AT_LEAST_A_CATEGORY"), "warning");
						return false;
					}
					Ui.confirm(U.lang("ART.SURE_DEL_CATEGORY"), function() {
						categoryMenu.$ctrl.hide().appendTo(document.body);
						sideTreeCategory.remove(treeNode, {
							url: Ibos.app.url('article/category/del'),
							success: function() {
								Ui.tip(U.lang('TREEMENU.DEL_CATELOG_SUCCESS'));
							},
							error: function(res) {
								Ui.tip(res.msg, "danger");
							}
						});
						categoryMenu.menu.hide();
					})
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
