<!-- 多行文本框 -->
<div id="ic_textarea_menu_content">
	<div class="mb">
		<label for="ic_textarea_title">控件名称</label>
		<input type="text" id="ic_textarea_title" value="多行文本框">
	</div>
	<div class="mb">
		<label for="ic_textarea_value">默认值</label>
		<input type="text" id="ic_textarea_value">
	</div>
	<div class="mb">
		<label>控件样式</label>
		<div class="row">
			<div class="span6">
				<label for="ic_textarea_width" class="input-group">
					<input type="text" id="ic_textarea_width">
					<span class="input-group-addon">宽</span>
				</label>
			</div>
			<div class="span6">
				<label for="ic_textarea_rows" class="input-group">
					<input type="text" id="ic_textarea_rows">
					<span class="input-group-addon">行</span>
				</label>
			</div>
		</div>
	</div>
	<div>
		<label>增强</label>
		<label for="ic_textarea_rich" class="checkbox">
			<input type="checkbox" id="ic_textarea_rich">
			富文本形式（启用文本编辑器）
		</label>
	</div>
</div>
<!-- 多行输入框模板 -->
<script type="text/template" id="ic_textarea_tpl">
	<ic data-id="<%=id%>" data-type="textarea" data-title="<%=title%>" data-value="<%=value%>" data-width="<%=width%>" data-rows="<%=rows%>" data-rich="<%=rich%>" contenteditable="false" >
		<textarea name="data_<%=id%>" title="<%=title%>" rows="<%=rows%>" style="width:<%=width%>px"><%=value%></textarea>
	</ic>
</script>