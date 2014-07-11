<!-- 单行文本框 -->
<div id="ic_text_menu_content">
	<div class="mb">
		<label for="ic_text_title">控件名称</label>
		<input type="text" id="ic_text_title" value="单行输入框">
	</div>
	<div class="mb">
		<label for="ic_text_value">默认值</label>
		<input type="text" id="ic_text_value">
	</div>
	<div class="mb">
		<label for="ic_text_width">控件样式</label>
		<div class="input-group">
			<input type="text" id="ic_text_width">
			<span class="input-group-addon">宽</span>
		</div>
	</div>
	<div>
		<label>可见性</label>
		<label for="ic_text_hide" class="checkbox">
			<input type="checkbox" id="ic_text_hide">
			隐藏
		</label>
	</div>
</div>
<!-- 单行输入框模板 -->
<script type="text/template" id="ic_text_tpl">
	<ic data-id="<%=id%>" data-type="text" data-title="<%=title%>" data-value="<%=value%>" data-width="<%=width%>" data-hide="<%=hide%>" contenteditable="false">
		<input type="text" title="<%=title%>" name="data_<%=id%>" value="<%=value%>" style="width: <%=width%>px" />
	</ic>
</script>