/**
 * 工作流简易编辑器
 */
// 拖拽处理
$(function() {
	var dataId = Ibos.app.g("dataId");
	// 清除多余的样式标签，给控件赋予Id
	var _clean = function($node) {
		if ($node && $node.length && !$node.data("cleaned")) {
			$ic = $node.find("ic");
			$view = $node.find(".grid-view");
			if ($view.length) {
				// 排版
				if (!$ic.length) {
					$node.html($view.html());
					// 控件
				} else {
					$ic.html($view.html()).attr("data-id", ++dataId);
				}
				$node.data("cleaned", true);
			}
		}
	}
	var _onReceive = function() {
		var sortIns = $(this).data("uiSortable");
		_clean(sortIns.currentItem);
	}
	var rowsSortSettings = {
		connectWith: ".grid-column",
		// items: ".grid-row",
		receive: _onReceive,
		zIndex: 1002
	};
	var colsSortSettings = {
		connectWith: ".grid-column",
		// cancel属性用于阻止文本框选中
		cancel: "",
		receive: _onReceive,
		zIndex: 1002
	}


	// 主容器排序，允许行排序，允许行插入列中
	$(".grid-container").sortable(rowsSortSettings);

	// 主容器列排序， 允许列内、列间的 行排序、盒排序，不允许插入主容器？
	$(".grid-container .grid-column").sortable(colsSortSettings);


	// 侧栏 Grid 拖拽， 允许插入主容器，不允许插入列
	$(".form-designer-sidebar .grid-row").draggable({
		connectToSortable: ".grid-container",
		helper: "clone",
		stop: function(evt, data) {
			// 重新初始列排序
			$(".grid-container .grid-column").sortable(colsSortSettings);
		}
	});

	// 侧栏 box 拖拽， 允许插入列中
	$(".form-designer-sidebar .grid-box").draggable({
		connectToSortable: ".grid-column",
		helper: "clone"
	});

	$("#wf_designer_recyclebin").droppable({
		accept: ".grid-container .grid-row, .grid-container .grid-box",
		activeClass: "active",
		hoverClass: "hover",
		tolerance: "pointer",
		drop: function(evt, data) {
			data.draggable.remove();
		}
	});

	// 撑开高度
	Ui.fillHeight('form_design', $(window));
	window.onresize = function() {
		Ui.fillHeight('form_design', $(window));
	};

	Ibos.evt.add({
		"saveForm": function() {
			var content = $("#form_design").html();
			if ($.trim(content) === "") {
				alert(U.lang('WF.EMPTY_FORM_CONTENT'));
				return false;
			}
			var textarea = document.createElement("textarea");
			textarea.name = "content";
			textarea.value = content;
			textarea.style.display = "none";
			$("#save_form").append(textarea).submit();
		},
		"previewForm": function() {
			Ui.openFullWindow(Ibos.app.url('workflow/formtype/preview', {formid: Ibos.app.getPageParam("formid")}));
		},
		"saveVersion": function() {
			var content = $("#form_design").html();
			if ($.trim(content) === "") {
				alert(U.lang('WF.EMPTY_FORM_CONTENT'));
				return false;
			}
			var textarea = document.createElement("textarea");
			textarea.name = "content";
			textarea.value = content;
			textarea.style.display = "none";
			$('#form_design_op').val('version');
			$("#save_form").append(textarea).submit();			
		},
		"viewVersion": function() {
			var d = Ui.dialog({
				id: "d_version",
				title: U.lang("HISTORICAL_EDITION"),
				init: function() {
					FormDesigner.formEdition.init();
				},
				cancel: true
			});
			$.get(Ibos.app.url('workflow/formversion/index', {id: Ibos.app.getPageParam("formid")}), function(res) {
				if (res.isSuccess) {
					// 用于生成历史版本选项
					FormDesigner.formEditionSelect.updateOptions(res.list);
					d.content(document.getElementById("dialog_edition"));
				}
			}, "json");
		}
	})
});

// 简易编辑器pop菜单类
var IcMenu = function(options){
	options = options || {};
	if(options){
		if(this._super.getIns(options.id)) {
			return this._super.getIns(options.id);
		}
	};

	var me = this,
		$menu = $("<div class='simple-editor-menu'></div>").appendTo(document.body),
		$content = $("<div></div>"),
		$body = $('<div class="simple-editor-menu-body"></div>'),
		$footer
	// 添加header
	if(options.title) {
		$content.append('<h4 class="simple-editor-menu-title">' + options.title + '</h4>')
	}
	// 添加body
	if(options.content) {
		$body.html(options.content);
	}
	$content.append($body);
	// 添加footer;
	$footer = $('<div class="simple-editor-menu-footer">' +		
		'<button type="button" class="btn">' + U.lang("CANCEL") + '</button>' +
		'<button type="button" class="btn btn-primary pull-right">' + U.lang("SAVE") + '</button>' +
	'</div>');
	$content.append($footer);
	// 关闭窗口
	$footer.find("button").eq(0).on("click", function(){
		if(options.cancel && options.cancel.call(me) === false) {
			return false;
		}
		me.hide();
	});
	// 保存
	$footer.find("button").eq(1).on("click", function(){
		if(options.ok && options.ok.call(me) === false) {
			return false;
		}
		me.hide();
	});

	var _options = $.extend(true, {}, IcMenu.defaults, options, {
		content: $content[0],
	});

	this.$body = $body;
	this._super.call(this, $menu, _options);
};
IcMenu.defaults = {
	clickToHide: true
};
IcMenu.prototype.setContent = function(content){
	this.$body.waiting(false).html(content);
	this.position(this.options.position);
	return this;
}
IcMenu.prototype.showLoading = function(){
	this.$body.waiting("small");
}

IcMenu.prototype.hideLoading = function(){
	this.$body.waiting(false);
}

Ibos.core.inherits(IcMenu, Ui.Menu);



// 组件配置
(function(){
	// 用于给select元素生成options
	//  [HTMLSelectElement, Array, String]
	var buildSelect = function(node, options, selected){
		var tpl = "";
		if(options && options.length) {
			for(var i = 0; i < options.length; i++) {
				tpl += '<option value="' + options[i] + '" ' + (options[i] === selected ? 'selected' : '') + '>' + options[i] + '</option>';
			}
		}
		$(node).html(tpl);
	}

	// 各控件对应的菜单配置项
	var icSettings = {
		"label": {
			id: "ic_label_menu",
			title: U.lang("WF.IC.LABEL"),
			ok: function(){
				var text = Dom.byId("ic_label_text").value;

				if($.trim(text) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TEXT"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_label_tpl", {
					id: this.param.id,
					text: text,
					width: Dom.byId("ic_label_width").value,
					fontSize: Dom.byId("ic_label_fontsize").value,
					align: Dom.byId("ic_label_align").value,
					bold: +this.editor.getValue('bold'),
					italic: +this.editor.getValue('italic'),
					underline: +this.editor.getValue('underline'),
					color: this.editor.getValue("color") || " "
				}));
			},
			show: function(){
				// 标签控件编辑器
				this.editor = P.miniEditor($("#ic_label_et"));
				Dom.byId("ic_label_text").value = this.param.text;
				Dom.byId("ic_label_width").value = this.param.width || "";
				Dom.byId("ic_label_fontsize").value = this.param.fontSize || "";
				Dom.byId("ic_label_align").value = this.param.align;
				this.editor.setValue({
					bold: this.param.bold == "1" ? true : false,
					italic: this.param.italic == "1" ? true : false,
					underline: this.param.underline == "1" ? true : false,
					color: this.param.color || ""
				});
			}
		},
		"text": {
			id: "ic_text_menu",
			title: U.lang("WF.IC.TEXT"),
			ok: function(){
				var title = Dom.byId("ic_text_title").value,
					width = Dom.byId("ic_text_width").value,
					value = Dom.byId("ic_text_value").value,
					hide = Dom.byId("ic_text_hide").checked;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_text_tpl", {
					id: this.param.id,
					title: title,
					width: width || 200,
					value: value || " ",
					hide: +hide
				}));
			},
			show: function(){
				Dom.byId("ic_text_title").value = this.param.title;	
				Dom.byId("ic_text_width").value = this.param.width || "";
				Dom.byId("ic_text_value").value = this.param.value || "";
				$("#ic_text_hide").label(this.param.hide == "1" ? "check" : "uncheck");
			}
		},
		"textarea": {
			id: "ic_textarea_menu",
			title: U.lang("WF.IC.TEXTAREA"),
			ok: function(){
				var title = Dom.byId("ic_textarea_title").value,
					width = Dom.byId("ic_textarea_width").value,
					rows = Dom.byId("ic_textarea_rows").value,
					value = Dom.byId("ic_textarea_value").value,
					rich = Dom.byId("ic_textarea_rich").checked;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_textarea_tpl", {
					id: this.param.id,
					title: title,
					width: width || 200,
					value: value || " ",
					rows: rows || 5,
					rich: +rich
				}));
			},
			show: function(){
				Dom.byId("ic_textarea_title").value = this.param.title;
				Dom.byId("ic_textarea_width").value = this.param.width || "";
				Dom.byId("ic_textarea_rows").value = this.param.rows || "";
				Dom.byId("ic_textarea_value").value = this.param.value || "";
				$("#ic_textarea_rich").label(this.param.rich == "1" ? "check" : "uncheck")
			}
		},
		"select": {
			id: "ic_select_menu",
			title: U.lang("WF.IC.SELECT"),
			ok: function(){
				var title = Dom.byId("ic_select_title").value,
					width = Dom.byId("ic_select_width").value,
					size = Dom.byId("ic_select_size").value,
					check = Dom.byId("ic_select_check").value,
					field = Dom.byId("ic_select_field").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}
				if($.trim(field) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_OPTIONS"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_select_tpl", {
					id: this.param.id,
					title: title,
					width: width || 200,
					check: check || " ",
					field: field.replace(/\n|\r/g, "`") || " ",
					fields: field.split(/\n|\r/),
					size: size || 1
				}));				
			},
			show: function(){
				var field = this.param.selectField || "",
					check = this.param.selectCheck || "";

				buildSelect("#ic_select_check", field.split("`"), "" + check)

				Dom.byId("ic_select_title").value = this.param.title;
				Dom.byId("ic_select_field").value = field.replace(/\`/g, "\n");
				Dom.byId("ic_select_width").value = this.param.width || "";
				Dom.byId("ic_select_size").value = this.param.size || "";

				$("#ic_select_field").off("change.icselect").on("change.icselect", function(){
					buildSelect("#ic_select_check", this.value.split(/\n|\r/));
				})
			}
		},
		"radio": {
			id: "ic_radio_menu",
			title: U.lang("WF.IC.RADIO"),
			ok: function(){
				var title = Dom.byId("ic_radio_title").value,
					check = Dom.byId("ic_radio_check").value,
					field = Dom.byId("ic_radio_field").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}
				if($.trim(field) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_OPTIONS"));
					return false;
				}
				
				$(this.target).replaceWith($.tmpl("ic_radio_tpl", {
					id: this.param.id,
					title: title,
					field: field.replace(/\n|\r/g, "`") || " ",
					fields: field.split(/\n|\r/),
					check: check || " "
				}))				
			},
			show: function(){
				var field = this.param.radioField || "",
					check = this.param.radioCheck || "";

				buildSelect("#ic_radio_check", field.split("`"), "" + check)

				Dom.byId("ic_radio_title").value = this.param.title;
				Dom.byId("ic_radio_field").value = field.replace(/\`/g, "\n");

				$("#ic_radio_field").off("change.icradio").on("change.icradio", function(){
					buildSelect("#ic_radio_check", this.value.split(/\n|\r/));
				})
			}
		},
		"checkbox": {
			id: "ic_checkbox_menu",
			title: U.lang("WF.IC.CHECKBOX"),
			ok: function(){
				var title = Dom.byId("ic_checkbox_title").value,
					checked = Dom.byId("ic_checkbox_checked").checked;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_checkbox_tpl", {
					id: this.param.id,
					title: title,
					checked: +checked
				}))				
			},
			show: function(){
				Dom.byId("ic_checkbox_title").value = this.param.title;
				$("#ic_checkbox_checked").label(this.param.checkboxChecked == "1" ? "check" : "uncheck");
			}
		},
		"user": {
			id: "ic_user_menu",
			title: U.lang("WF.IC.USER"),
			ok: function(){
				var title = Dom.byId("ic_user_title").value,
					checked = Dom.byId("ic_user_single").checked;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_user_tpl", {
					id: this.param.id,
					title: title,
					width: Dom.byId("ic_user_width").value || 200,
					selectType: Dom.byId("ic_user_type").value,
					selectTypeText: Dom.byId("ic_user_type").options[Dom.byId("ic_user_type").selectedIndex].text,
					single: +Dom.byId("ic_user_single").checked
				}))				
			},
			show: function(){
				Dom.byId("ic_user_title").value = this.param.title;
				Dom.byId("ic_user_width").value = this.param.width || "";
				Dom.byId("ic_user_type").value = this.param.selectType;
				$("#ic_user_single").label(this.param.single == "1" ? "check" : "uncheck");
			}
		},
		"date": {
			id: "ic_date_menu",
			title: U.lang("WF.IC.DATE"),
			ok: function(){
				var title = Dom.byId("ic_date_title").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_date_tpl", {
					id: this.param.id,
					title: title,
					width: Dom.byId("ic_date_width").value || 200,
					format: Dom.byId("ic_date_format").value,
					formatText: Dom.byId("ic_date_format").options[Dom.byId("ic_date_format").selectedIndex].text
				}))				
			},
			show: function(){
				Dom.byId("ic_date_title").value = this.param.title;
				Dom.byId("ic_date_width").value = this.param.width || "";
				Dom.byId("ic_date_format").value = this.param.dateFormat;
			}
		},
		"auto": {
			id: "ic_auto_menu",
			title: U.lang("WF.IC.AUTO"),
			ok: function(){
				var title = Dom.byId("ic_auto_title").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_auto_tpl", {
					id: this.param.id,
					title: title,
					width: Dom.byId("ic_auto_width").value || 200,
					field: Dom.byId("ic_auto_field").value,
					fieldText: Dom.byId("ic_auto_field").options[Dom.byId("ic_auto_field").selectedIndex].text,
					src: Dom.byId("ic_auto_src").value || " ",
					hide: +Dom.byId("ic_auto_hide").checked,
				}))				
			},
			show: function(){
				var needSrc = function(f){ return f === "sys_sql" || f === "sys_list_sql" }
				var toggleSrc = function(f){
					var isSrc = needSrc(f);
					$("#ic_auto_normal_field").toggle(!isSrc);
					$("#ic_auto_src_field").toggle(isSrc);
				}

				Dom.byId("ic_auto_title").value = this.param.title;
				Dom.byId("ic_auto_width").value = this.param.width || "";
				Dom.byId("ic_auto_field").value = this.param.field;
				$("#ic_auto_hide").label(this.param.hide == "1" ? "check" : "uncheck");
				if(needSrc(this.param.field)) {
					Dom.byId("ic_auto_src").value = this.param.src;
				}

				toggleSrc(this.param.field);
				$("#ic_auto_field").off("change.icauto").on("change.icauto", function(){
					toggleSrc(this.value);
				});
			}
		},
		"calc": {
			id: "ic_calc_menu",
			title: U.lang("WF.IC.CALC"),
			ok: function(){
				var title = Dom.byId("ic_calc_title").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_calc_tpl", {
					id: this.param.id,
					title: title,
					width: Dom.byId("ic_calc_width").value || 200,
					prec: Dom.byId("ic_calc_prec").value || 4,
					value: Dom.byId("ic_calc_value").value || " "
				}))				
			},
			show: function(){
				Dom.byId("ic_calc_title").value = $.attr(this.target, "data-title");
				Dom.byId("ic_calc_width").value = $.attr(this.target, "data-width") || "";
				Dom.byId("ic_calc_prec").value = $.attr(this.target, "data-prec") || 4;
				Dom.byId("ic_calc_value").value = $.attr(this.target, "data-value") || "";
			}
		},
		"listview": {
			id: "ic_listview_menu",
			title: U.lang("WF.IC.LISTVIEW"),
			addClass: "simple-editor-listview-menu",
			ok: function(){
				var title = Dom.byId("ic_listview_title").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				var data = this.listviewTable.getData(),
					lvTitles = data.lvTitle.split("`")

				if(!lvTitles.length) {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_COLUMN_DATA"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_listview_tpl", {
					id: this.param.id,
					title: title,
					lvSum: data.lvSum || " ",
					lvTitle: data.lvTitle || " ",
					lvTitles: lvTitles,
					lvColtype: data.lvColtype || " ",
					lvColvalue: data.lvColvalue || " ",
				}))
			},
			show: function(){
				var tableCellCount = this.listviewTable.getCellCount(),
					dataCount = (this.param.lvTitle|| "").split("`").length;

				Dom.byId("ic_listview_title").value = this.param.title;
				this.listviewTable.setData(this.param);
				// 当数据超出5组时，自动在表格后插入一列
				if(dataCount >= 5){
					this.listviewTable.insertColumn();
				}
			}
		},
		"progressbar": {
			id: "ic_progressbar_menu",
			title: U.lang("WF.IC.PROGRESSBAR"),
			addClass: "simple-editor-progressbar-menu",
			ok: function(){
				var title = Dom.byId("ic_progressbar_title").value;
				// 获取进度条类型
				var _getStyle = function() {
					return $("[name='style']").map(function() {
						if (this.checked) {
							return this.value;
						}
					}).get(0);
				};

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_progressbar_tpl", {
					id: this.param.id,
					title: title,
					width: Dom.byId("ic_progressbar_width").value || 200,
					style: _getStyle(),
					step: $("#ic_progressbar_slider").slider("value")
				}))				
			},
			show: function(){
				var data = $(this.target).data();
				// 还原进度条类型
				var _setStyle = function(style) {
					$("[name='style']").each(function(index, elem) {
						elem.value === style && $(elem).label("check");
					});
				};
				// 进度条
				Dom.byId("ic_progressbar_title").value = data.title;
				Dom.byId("ic_progressbar_width").value = data.width || "";

				_setStyle(data.progressStyle);
				// 设置滑动条的值
				$("#ic_progressbar_slider").ibosSlider({
					range: 'min',
					scale: true,
					tip: true,
					value: isNaN(+data.step) ? 0 : +data.step
				});
			}
		},
		"imgupload": {
			id: "ic_imgupload_menu",
			title: U.lang("WF.IC.IMGUPLOAD"),
			ok: function(){
				var title = Dom.byId("ic_imgupload_title").value;

				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_imgupload_tpl", {
					id: this.param.id,
					title: title,
					width: Dom.byId("ic_imgupload_width").value || 200,
					height: Dom.byId("ic_imgupload_height").value || 200
				}))				
			},
			show: function(){
				Dom.byId("ic_imgupload_title").value = this.param.title;
				Dom.byId("ic_imgupload_width").value = this.param.width;
				Dom.byId("ic_imgupload_height").value = this.param.height;
			}
		},
		"sign": {
			id: "ic_sign_menu",
			title: U.lang("WF.IC.SIGN"),
			ok: function(){
				var title = Dom.byId("ic_sign_title").value;
				var stamp, write;
				if($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				stamp = Dom.byId("ic_sign_type_stamp").checked ? 1 : 0;
				write = Dom.byId("ic_sign_type_write").checked ? 1 : 0;

				$(this.target).replaceWith($.tmpl("ic_sign_tpl", {
					id: this.param.id,
					title: title,
					stamp: stamp,
					write: write,
					signColor: this.colorPicker.get() || "",
					signType: stamp + "," + write,
					signField: Dom.byId("ic_sign_field").value.replace(/\n|\r/g, "`") || " "
				}));		
			},
			show: function(){
				// 签章控件
				this.colorPicker = (function(){
					var $btn = $("#ic_sign_color_btn"),
						$input = $btn.next(),
						set = function(hex){
							$input.val(hex);
							$btn.css("background-color", hex);
						},
						get = function(){
							return $input.val();
						}
					$btn.colorPicker({ onPick: set });
					return { set: set, get: get};
				})();

				Dom.byId("ic_sign_title").value = this.param.title;

				this.param.signColor && this.colorPicker.set(this.param.signColor);
				if(this.param.signType) {
					$("#ic_sign_type_stamp").label(this.param.signType.charAt(0) == "1" ? "check" : "uncheck");
					$("#ic_sign_type_write").label(this.param.signType.charAt(2) == "1" ? "check" : "uncheck");
				}
				if(this.param.signField) {
					Dom.byId("ic_sign_field").value = this.param.signField.replace(/\`/g, "\n");
				}
			}
		},
		"qrcode": {
			id: "ic_qrcode_menu",
			title: U.lang("WF.IC.QRCODE"),
			addClass: "simple-editor-qrcode-menu",
			ok: function(){
				var data,
					Qr = this.Qr,
					title = Dom.byId("ic_qrcode_title").value,
					value = Qr.getValue();

				if ($.trim(title) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_TITLE"));
					return false;
				}

				if ($.trim(value) === "") {
					alert(U.lang("WF.ICTIP.PLEASE_INPUT_QRCODE_VALUE"));
					return false;
				}

				$(this.target).replaceWith($.tmpl("ic_qrcode_tpl", {
					id: this.param.id,
					title: title,
					value: value,
					qrcodeType: Qr.getType(),
					qrcodeSize: Qr.getSize()
				}));		
			},
			show: function(){
				Dom.byId("ic_qrcode_title").value = this.param.title || "";
				this.$menu.find(".radio input").label();
				this.Qr.setType(this.param.qrcodeType + "");
				this.Qr.setSize(this.param.qrcodeSize + "");
				this.Qr.setValue(this.param.qrcodeType, this.param.value);
				this.Qr.createPreview(this.Qr.getValue());
			}
		}
	}

	var editIc = function(elem, evt, data){
		data = data || {};
		var type = $(elem).attr("data-type");
		var setting = icSettings[type];
		var icMenu = new IcMenu(setting);
		icMenu.target = elem;
		icMenu.param = data;
		$.ajax({
			url: Ibos.app.url('workflow/formtype/design',{op:'showcomponet',type:type}),
			type: "get",
			dataType: "html",
			success: function(res){
				icMenu.setContent(res).show()
				.position({
					of: elem,
					my: "left center",
					collision: "fit fit"
				});
			}
		});
	};
	$(document).on("click", ".grid-container ic", function(evt){
		var data = $(this).data();
		editIc(this, evt, data);
	})
	// 阻止所有控件的默认事件
	.on("mousedown click", ".grid-container ic input, .grid-container ic textarea, .grid-container ic select, .grid-container ic label", function(e){
		e.preventDefault();
	})
	.on("mousedown", "#jquery-colour-picker", function(e){
		e.stopPropagation();
	});
})();