var Wfs = {}

Wfs.list = new Ibos.List($("#wf_form_list"), "tpl_form_item");

Wfs.formEdition = (function() {
	var $editionDialog = $("#dialog_edition"),
			$editionSelect = $editionDialog.find("select"),
			_fid,
			_getEdition = function() {
				return $editionSelect.val();
			},
			_checkEdition = function() {
				var edition = _getEdition();
				if (!edition || edition === "0") {
					$editionSelect.blink();
					return false;
				}
				return true;
			},
			_createOptionTpl = function(datas) {
				var tpl = "<option value='0'>" + U.lang("WF.SELECT_HISTORICAL_EDITION") + "</option>";
				if (datas.length) {
					for (var i = 0, len = datas.length; i < len; i++) {
						tpl += "<option value='" + datas[i].value + "'>" + datas[i].text + "</option>";
					}
				}
				return tpl;
			},
			removeOption = function(value) {
				$editionSelect.find("option").each(function() {
					if (this.value === "" + value) {
						$(this).remove();
						return false;
					}
				});
			},
			show = function(param) {
				Ui.closeDialog("d_historical_edition");
				var d = Ui.dialog({
					id: "d_historical_edition",
					title: U.lang("WF.HISTORICAL_EDITION"),
					cancel: function() {
						_fid = void(0);
					},
					cancelVal: U.lang("CLOSE")
				});
				$.get(Ibos.app.url('workflow/formversion/index', param), function(res) {
					var opts = res.list;
					$editionSelect.html(_createOptionTpl(opts));
					d.content($editionDialog.get(0));
					_fid = param.id;
				}, 'json');
			},
			hide = function() {
				Ui.closeDialog("d_historical_edition");
				_fid = void(0);
			},
			preview = function(fid, version) {
				if (fid && version && version !== "0") {
					window.open(Ibos.app.url('workflow/formtype/preview', {
						verid: version
					}));
				}
			},
			restore = function(fid, version) {
				if (fid && version && version !== "0") {
					Ui.confirm(U.lang("WF.EDITION_RESTORE_CONFIRM"), function() {
						$.get(Ibos.app.url('workflow/formversion/restore'), {verid: version}, function(res) {
							if (res.isSuccess) {
								window.location.reload();
							} else {
								Ui.alert(res.msg);
							}
						});
					});
				}
			},
			del = function(fid, version) {
				if (fid && version && version !== "0") {
					Ui.confirm(U.lang("WF.EDITION_DELETE_CONFIRM"), function() {
						$.get(Ibos.app.url('workflow/formversion/del'), {verid: version}, function(res) {
							if (res.isSuccess) {
								Ui.tip(U.lang('DELETE_SUCCESS'), 'success');
								removeOption(version);
							} else {
								Ui.tip(U.lang('DELETE_FAILED'), 'danger');
							}
						});
					});
				}
			};
	var editionEvts = new Ibos.Event($editionDialog, "click", "edition");
	editionEvts.add({
		preview: function() {
			if (_checkEdition()) {
				preview(_fid, _getEdition());
			}
		},
		restore: function() {
			if (_checkEdition()) {
				restore(_fid, _getEdition());
			}
		},
		del: function() {
			if (_checkEdition()) {
				del(_fid, _getEdition());
			}
		}
	});
	return {
		show: show,
		hide: hide,
		preview: preview,
		restore: restore,
		del: del
	};
})();

Wfs.formItem = (function() {
	var dialogNode = document.getElementById("dialog_form_setting"),
			formNode = document.getElementById("form_setting_form"),
			$name = $("#form_setting_name"),
			$catelog = $("#form_setting_catelog"),
			$department = $("#form_setting_department"),
			$formid = $("#form_setting_id"),
			_selectorIsInit = false,
			initSelector = function() {
				if (!_selectorIsInit) {
					$department.userSelect({
						data: Ibos.data.get("department"),
						maximumSelectionSize: "1",
						type: "department",
						box: $("<div></div>").appendTo(document.body)
					});
					_selectorIsInit = true;
				}
			},
			add = function(param) {
				Ui.closeDialog();
				param.url = Ibos.app.url('workflow/formtype/add');
				Ui.dialog({
					id: "d_add_form",
					title: U.lang("WF.NEW_FORM"),
					content: dialogNode,
					init: initSelector,
					button: [{
							name: U.lang('SAVE'),
							callback: function() {
								if ($.trim($name.val()) == '') {
									$name.blink();
									return false;
								}
								if($.trim($catelog.val()) == ''){
									$catelog.blink();
									return false;
								}
								param.nextopt = 'quit';
								_save(param, 'd_add_form');
							},
							focus: true
						}, {
							name: U.lang('WF.SAVE_AND_DESIGN'),
							callback: function() {
								if ($.trim($name.val()) == '') {
									$name.blink();
									return false;
								}
								if($.trim($catelog.val()) == ''){
									$catelog.blink();
									return false;
								}
								param.nextopt = 'design';
								_save(param, 'd_add_form');
							}
						}],
					cancel: function() {
						// 重置表单
						formNode.reset();
						$department.userSelect("setValue");
					}
				});
			},
			edit = function(param) {
				var data;
				if (!param.formid) {
					return false;
				}
				Ui.closeDialog();
				if (param.inajax) {
					$.get(Ibos.app.url('workflow/formtype/edit', {
						inajax: 1,
						formid: param.formid
					}), function(res) {
						data = res.data[0];
						_editAct(param, data);
					}, 'json');
				} else {
					param.url = Ibos.app.url('workflow/formtype/edit');
					data = Wfs.list.getItemData(param.formid);
					_editAct(param, data);
				}
			},
			importForm = function(param) {
				Ui.closeDialog("d_import_form");
				Ui.openFrame(Ibos.app.url('workflow/formtype/import', param), {
					id: "d_import_form",
					title: U.lang("WF.IMPORT_FORM"),
					padding: 20,
					ok: function() {
						this.iframe.contentDocument.forms[0].submit();
						return false;
					},
					okVal: U.lang("IMPORT"),
					cancel: true
				});
			},
			edition = function(param) {
				param && Wfs.formEdition.show(param);
			},
			design = function(param) {
				var winParam = "width=" + screen.availWidth + ", height=" + screen.availHeight + ", left=0, top=0, menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1"
				window.open(Ibos.app.url('workflow/formtype/design', param), "designForm", winParam);
			},
			_editAct = function(param, data) {
				Ui.dialog({
					id: "d_edit_form",
					title: U.lang("WF.EDIT_FORM"),
					init: function() {
						initSelector();
						$name.val(data.name);
						$catelog.val(data.catelog);
						if ($.trim(data.departmentId) !== '') {
							$department.userSelect("setValue", data.departmentId);
						}
						$formid.val(param.formid);
					},
					content: dialogNode,
					button: [{
							name: U.lang('SAVE'),
							callback: function() {
								if ($.trim($name.val()) == '') {
									$name.blink();
									return false;
								}
								param.nextopt = 'quit';
								_save(param, 'd_edit_form');
								return false;
							},
							focus: true
						}, {
							name: U.lang('WF.SAVE_AND_DESIGN'),
							callback: function() {
								if ($.trim($name.val()) == '') {
									$name.blink();
									return false;
								}
								param.nextopt = 'design';
								_save(param, 'd_edit_form');
								return false;
							}
						}],
					cancel: function() {
						// 重置表单
						formNode.reset();
						$department.userSelect("setValue");
					}
				});
			},
			_save = function(param, dom) {
				$(formNode).waiting(U.lang('IN_SUBMIT'), "mini", true);
				$.post(param.url, $(formNode).serializeArray(), function(data) {
					if (data.isSuccess) {
						Ui.closeDialog(dom);
						if (param.nextopt === 'quit') {
							U.setCookie('form_save_success', 1, 30);
						}
						if (param.nextopt === 'design') {
							U.setCookie('formid', data.formid, 30);
							U.setCookie('form_save_success', 2, 30);
						}
						// 跳转到所属分类下
						window.location.href = data.url;
					} else {
						Ui.tip(U.lang('SAVE_FAILED'), 'danger');
						
					}
				}, 'json');
			};
	return {
		add: add,
		edit: edit,
		design: design,
		importForm: importForm,
		edition: edition
	};
})();

Wfs.formList = (function() {
	var addItem = function(data) {
		Wfs.list.addItem(data);
		var $item = Wfs.list.getItem();
		$item.find(".checkbox input").label();
		return $item;
	};
	var removeItem = function(ids, param) {
		if (ids) {
			Ui.confirm(U.lang('WF.CONFIRM_DEL_FORM'), function() {
				$.post(Ibos.app.url('workflow/formtype/del', {
					id: ids
				}), function(res) {
					if (res.isSuccess) {
						Wfs.list.removeItem(ids);
						Ui.tip(U.lang('DELETE_SUCCESS'));
					} else {
						Ui.tip(res.msg, 'danger');
					}
				}, 'json');
			});
		}
	};

	return {
		addItem: addItem,
		removeItem: removeItem,
		importForm: Wfs.formItem.importForm
	};
})();



(function() {
	var formEvent = new Ibos.Event($("#wf_form_list"), "click", "form");
	formEvent.add({
		"edit": function(param, $elem) {
			Wfs.formItem.edit(param);
		},
		"design": function(param) {
			Wfs.formItem.design(param);
		},
		"import": function(param) {
			Wfs.formItem.importForm(param);
		},
		"edition": function(param) {
			Wfs.formItem.edition(param);
		}
	});

	Ibos.events.add({
		addForm: function(param) {
			Wfs.formItem.add(param);
		},
		delForm: function(param) {
			var ids = U.getCheckedValue("form");
			if (ids) {
				Wfs.formList.removeItem(ids, param);
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
			}
		},
		importForm: function(param) {
			Wfs.formList.importForm(param);
		},
		exportForm: function(param) {
			var ids = U.getCheckedValue("form");
			if (ids) {
				window.location.href = Ibos.app.url('workflow/formtype/export', {
					id: ids
				});
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
			}
		}
	});
})();