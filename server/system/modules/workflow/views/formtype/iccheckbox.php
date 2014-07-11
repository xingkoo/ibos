<!-- 复选框 -->
<div id="ic_checkbox_menu_content">
	<div class="mb">
		<label for="ic_checkbox_title">控件名称</label>
		<input type="text" id="ic_checkbox_title" class="mbs" value="复选框">
		<label for="ic_checkbox_checked" class="checkbox">
			<input type="checkbox" id="ic_checkbox_checked">
			默认选中
		</label>
	</div>
</div>
<!-- 复选框模板 -->
<script type="text/template" id="ic_checkbox_tpl">
	<ic data-id="<%=id%>" data-type="checkbox" data-title="<%=title%>" data-checkbox-checked="<%=checked%>" contenteditable="false">
		<label title="<%=title%>">
			<input type="checkbox" name="data_<%=id%>" <%= (checked == "1") ? "checked": "" %>/>
		</label>
	</ic>
</script>