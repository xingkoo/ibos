<!-- 列表控件 -->
<div id="ic_listview_menu_content">
	<table class="table lv-prop-table">
		<tbody>
			<tr>
				<td width="70"><label for="ic_listview_title">控件名称 <span class="xcr">*</span></label></td>
				<td width="200"><input type="text" id="ic_listview_title"></td>
				<td><button type="button" id="ins_toggle_btn" class="btn pull-right">控件说明</button></td>
			</tr>
		</tbody>
	</table>
	<div class="lv-table-container">
		<div class="lv-table-header">
			<ul>
				<li><span>序号</span></li>
				<li><span>表头</span><span class="xcr">*</span></li>
				<li><span data-toggle="popover" data-content="计算公式说明：用[1] [2] [3]等代表某列的数值。运算符支持+,-,*,/,%等。">数据类型</span></li>
				<li><span data-toggle="popover" data-content="数据类型的默认值。多个值之间用英文逗号分隔。当数据类型为日期时，默认值无效。">默认值</span></li>
				<li><span data-toggle="popover" data-content="在该列的底部显示该列的合计数值"><abbr>合计数值</abbr></span></li>
			</ul>
		</div>
		<div class="lv-table-wrap" id="lv_table_wrap">
			<table class="table table-striped table-condensed lv-table" id="lv_table"></table>
		</div>
	</div>
	<div id="control_listview_ins" style="padding: 20px; display: none;">
		<p>以下举例说明列表控件的计算公式功能：</p>

		<p>首先，我们需要建立报销项目明细列表，包括物品名称、单价、数量、合计，</p>

		<p>其中，合计列选择为“计算公式”如图：</p>

		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iclistview/list1.png";?>" />

		<p>计算公式用[1] [2] [3]等代表某列的数值。运算符支持+,-,*,/,%等。</p>

		<p>因此，合计的计算公式填写为“[2]*[3]”,如图：</p>

		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iclistview/list2.png";?>" />

		<p>另外，亦可勾选“合计数值”对某列进行合计。</p>

		<p>最终实现效果如下：</p>

		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iclistview/list3.png";?>" />
	</div>
</div>
<!-- 列表控件模板 -->
<script type="text/template" id="ic_listview_tpl">
	<ic data-id="<%=id%>" data-type="listview" data-title="<%=title%>" data-lv-title="<%=lvTitle%>" data-lv-coltype="<%=lvColtype%>" data-lv-colvalue="<%=lvColvalue%>" data-lv-sum="<%=lvSum%>" contenteditable="false">
	<table class="table table-bordered">		
	<thead>
	<tr>
	<% for(var i = 0; i < lvTitles.length; i++) { %>
	<th><%=lvTitles[i]%></th>
	<%}%>
	</tr>
	</thead>
	<tbody>
	<tr>
	<% for(var i = 0; i < lvTitles.length; i++) { %>
	<td></td>
	<% } %>
	</tr>
	</tbody>
	</table>
	</ic>
</script>
<!-- 列表控件菜单表格模板 -->
<script type="text/template" id="ic_listview_table_tpl">
	<tbody>
	<tr>
	<% for(var i = 0; i < data.length; i++) { %>
	<td><span class="lv-num"><%=i+1%></span></td>
	<% } %>
	</tr>
	<tr>
	<% for(var i = 0; i < data.length; i++) { %>
	<td><input type="text" class="input-small" name="lv-title" value="<%=data[i].title%>"></td>
	<% } %>
	</tr>
	<tr>
	<% for(var i = 0; i < data.length; i++) { %>
	<td>
	<select name="lv-coltype" class="input-small">
	<option value="text" <%= data[i].type === "text" ? "selected" : ""%> >单行输入框</option>
	<option value="textarea" <%= data[i].type === "textarea" ? "selected" : ""%>>多行输入框</option>
	<option value="select" <%= data[i].type === "select" ? "selected" : ""%>>下拉菜单</option>
	<option value="radio" <%= data[i].type === "radio" ? "selected" : ""%>>单选框</option>
	<option value="checkbox" <%= data[i].type === "checkbox" ? "selected" : ""%>>复选框</option>
	<option value="date" <%= data[i].type === "date" ? "selected" : ""%>>日期</option>
	<option value="calc" <%= data[i].type === "calc" ? "selected" : ""%>>计算公式</option>
	</select>
	</td>
	<% } %>
	</tr>
	<tr>
	<% for(var i = 0; i < data.length; i++) { %>
	<td><input type="text" class="input-small" name="lv-colvalue" value="<%=data[i].value%>"></td>
	<% } %>
	</tr>
	<tr>
	<% for(var i = 0; i < data.length; i++) { %>
	<td><label class="checkbox"><input type="checkbox" name="lv-sum" <%= (data[i].sum == "1" ? "checked" : "") %>></label></td>
	<% } %>
	</tr>
	</tbody>
</script>
<script>
	(function() {
		// 列表控件
		var listviewTable = {
			$table: $("#lv_table"),
			$tableContainer: $("#lv_table").parent(),
			COLUMN_WIDTH: 150,
			COLUMN_DISPLAY_CONUT: 5,
			columnTpl: [
				'<td><span class="lv-num"><%=index%></span></td>',
				'<td><input type="text" name="lv-title" value="<%=title%>" class="input-small"></td>',
				'<td>' +
						'<select name="lv-coltype" value="<%=coltype%>" class="input-small">' +
						'<option value="text">单行输入框</option>' +
						'<option value="textarea">多行输入框</option>' +
						'<option value="select">下拉菜单</option>' +
						'<option value="radio">单选框</option>' +
						'<option value="checkbox">复选框</option>' +
						'<option value="date">日历控件</option>' +
						'<option value="calc">计算控件</option>' +
						'</select>' +
						'</td>',
				'<td><input type="text" name="lv-colvalue" value="<%=colvalue%>" class="input-small"></td>',
				'<td><label class="checkbox"><input type="checkbox" name="lv-sum" <% if(sum){ %> checked <% } %> ></label></td>'
			],
			tableTpl: "ic_listview_table_tpl",
			getCellCount: function() {
				return this.$table.find("tr").eq(0).find("td").length;
			},
			scrollToLeft: function() {
				this.$tableContainer[0].scrollLeft -= this.COLUMN_WIDTH;
			},
			scrollToRight: function() {
				this.$tableContainer[0].scrollLeft += this.COLUMN_WIDTH;
			},
			// 重新计算表格宽度
			// 根据列宽常量及列数计算出表格总宽度，超出可视列数时，自动向右滚动
			reflow: function() {
				var count = this.getCellCount();
				this.$table.width(count * this.COLUMN_WIDTH);

				if (count > this.COLUMN_DISPLAY_CONUT) {
					for (var i = 0; i < count - this.COLUMN_DISPLAY_CONUT; i++) {
						this.scrollToRight();
					}
				}
			},
			// 根据传入的数据生成一个单元格对象的数组
			createColumnCells: function(data) { // colvalue sum coltype title
				var ret = [];
				data = $.extend({
					title: "",
					coltype: "",
					colvalue: "",
					sum: "0"
				}, data);

				for (var i = 0; i < this.columnTpl.length; i++) {
					ret.push($.tmpl(this.columnTpl[i], data));
				}

				return ret;
			},
			insertColumn: function(data) {
				var $rows = this.$table.find("tr"),
						columnCells = this.createColumnCells($.extend({
							index: this.getCellCount() + 1
						}, data));

				for (var i = 0; i < columnCells.length; i++) {
					$rows.eq(i).append(columnCells[i]).find(".checkbox input[type='checkbox']").label();
				}
				this.reflow();
			},
			// 获取表格中表头不为空的控件数据
			getData: function() {
				var titles = [],
						coltypes = [],
						colvalues = [],
						sums = [],
						$titles = $("[name='lv-title']", this.$table),
						$coltypes = $("[name='lv-coltype']", this.$table),
						$colvalues = $("[name='lv-colvalue']", this.$table),
						$sums = $("[name='lv-sum']", this.$table);

				$titles.each(function(index, elem) {
					if (elem.value) {
						titles.push(elem.value);
						coltypes.push($coltypes.get(index).value);
						colvalues.push($colvalues.get(index).value);
						sums.push(+$sums.get(index).checked);
					}
				});

				return {
					lvTitle: titles.join("`"),
					lvColtype: coltypes.join("`"),
					lvColvalue: colvalues.join("`"),
					lvSum: sums.join("`"),
				}
			},
			// 写入表格数据
			setData: function(data) {
				var lvTitles,
						lvColtypes,
						lvColvalues,
						lvSums,
						tplData = [];

				// 当有列表头时，进入循环，生成用于模板的数据。
				if (data.lvTitle) {
					lvTitles = data.lvTitle.split("`");
					lvColtypes = data.lvColtype.split("`");
					lvColvalues = data.lvColvalue.split("`");
					lvSums = data.lvSum.split("`");
					for (var i = 0; i < lvTitles.length; i++) {
						tplData.push({
							title: lvTitles[i],
							type: lvColtypes[i],
							value: lvColvalues[i] || "",
							sum: lvSums[i]
						})
					}
					;
				}
				// 数据不足5列，生成空列数据，保证至少5列
				if (tplData.length < 5) {
					for (i = 0, len = 5 - tplData.length; i < len; i++) {
						tplData.push({});
					}
				}

				var htmlStr = $.template(this.tableTpl, {
					data: tplData
				});
				this.$table.html(htmlStr).find(".checkbox input").label();
			}
		}
		listviewTable.$table.on("input propertychange", "[name='lv-title']", function(evt) {
			// td 找不到下一个时，新增一列
			if (!$(this).parent().next().length) {
				listviewTable.insertColumn();
			}
		});
		// 绑定水平方向的滚动事件
		// jquery默认似乎没有mousewheel事件兼容性处理
		// 此处使用了jquery.mousewheel插件
		listviewTable.$tableContainer.on("mousewheel", function(e) {
			// 滚轮向下时， deltaY 为-1, 反之为1
			e.deltaY === 1 ? listviewTable.scrollToLeft() : listviewTable.scrollToRight();
			e.preventDefault();
		});
		Ui.Menu.getIns("ic_listview_menu").listviewTable = listviewTable;
		$("#ins_toggle_btn").click(function() {
			Ui.dialog({
				title: '控件说明',
				width: '700px',
				cancel: true,
				id: 'listview_desc',
				content: document.getElementById('control_listview_ins')
			});
		});
	})();
</script>