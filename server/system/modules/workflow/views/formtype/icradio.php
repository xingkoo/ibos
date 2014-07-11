<!-- 单选框 -->
<div id="ic_radio_menu_content">
	<div class="mb">
		<label for="ic_radio_title">控件名称</label>
		<input type="text" id="ic_radio_title" value="单选框">
	</div>
	<div class="mb">
		<label for="ic_radio_field">批量添加选项（每行一个）</label>
		<textarea id="ic_radio_field" rows="5"></textarea>
	</div>
	<div>
		<label for="ic_radio_check">默认选中值</label>
		<select id="ic_radio_check"></select>
	</div>
</div>
<!-- 单选框模板 -->
<script type="text/template" id="ic_radio_tpl">
	<ic data-id="<%=id%>" data-type="radio" data-title="<%=title%>" data-radio-check="<%=check%>" data-radio-field="<%=field%>" contenteditable="false">
		<% for(var i = 0; i < fields.length; i++) { %>
			<label title="<%=title%>" style="padding: 0 5px;">
				<input type="radio" value="<%=fields[i]%>" name="<%=id%>" <%= (fields[i] === check) ? "checked": "" %>/>
				<%=fields[i]%>
			</label>
		<% } %>
	</ic>
</script>