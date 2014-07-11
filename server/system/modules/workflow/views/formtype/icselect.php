<!-- 下拉菜单 -->
<div id="ic_select_menu_content">
	<div class="mb">
		<label for="ic_select_title">控件名称</label>
		<input type="text" id="ic_select_title" value="下拉菜单">
	</div>
	<div class="mb">
		<label for="ic_select_field">批量添加选项（每行一个）</label>
		<textarea id="ic_select_field" rows="5"></textarea>
	</div>
	<div class="mb">
		<label for="ic_select_check">默认选中值</label>
		<select id="ic_select_check"></select>
	</div>
	<div>
		<label>控件样式</label>
		<div class="row">
			<div class="span6">
				<label for="ic_select_width" class="input-group">
					<input type="text" id="ic_select_width">
					<span class="input-group-addon">宽</span>
				</label>
			</div>
			<div class="span6">
				<label for="ic_select_size" class="input-group">
					<input type="text" id="ic_select_size">
					<span class="input-group-addon">行</span>
				</label>
			</div>
		</div>
	</div>
</div>
<!-- 下拉选框模板 -->
<script type="text/template" id="ic_select_tpl">
	<ic data-id="<%=id%>" data-type="select" data-title="<%=title%>" data-select-check="<%=check%>" data-width="<%=width%>" data-size="<%=size%>" data-select-field="<%=field%>" contenteditable="false" >
		<select name="data_<%=id%>" title="<%=title%>" style="width:<%=width%>px;" size="<%=size%>" >
			<% for(var i = 0; i < fields.length; i++) { %>
				<option value="<%=fields[i]%>" <%= (fields[i] === check ? "selected" : "") %> ><%=fields[i]%></option>
			<% } %>
		</select>
	</ic>
</script>