<!-- 标签控件 -->
<div id="ic_label_menu_content">
	<div class="mb">
		<label for="ic_label_text">标签文本</label>
		<input type="text" id="ic_label_text">
	</div>
	<div class="mb">
		<label for="ic_label_width">控件样式</label>
		<div class="input-group">
			<input type="text" id="ic_label_width">
			<span class="input-group-addon">宽</span>
		</div>
	</div>
	<div class="mb">
		<label>文字大小</label>
		<div class="row">
			<div class="span6">
				<label for="ic_label_fontsize" class="input-group">
					<input type="text" id="ic_label_fontsize">
					<span class="input-group-addon">PX</span>
				</label>
			</div>
			<div class="span6">
				<select id="ic_label_align">
					<option value="left">左对齐</option>
					<option value="center">居中</option>
					<option value="right">右对齐</option>
				</select>
			</div>
		</div>
	</div>
	<div>
		<label>文字样式</label>
		<div id="ic_label_et"></div>
	</div>
</div>
<!-- 标签控件模板 -->
<script type="text/template" id="ic_label_tpl">
	<ic data-id="<%=id%>" data-type="label" data-width="<%=width%>" data-text="<%=text%>" data-font-size="<%=fontSize%>" data-align="<%=align%>" data-bold="<%=bold%>" data-italic="<%=italic%>" data-underline="<%=underline%>" data-color="<%=color%>" contenteditable="false" >
		<span style="display: inline-block; width: <%=width%>px; text-align: <%=align%>; font-size: <%=fontSize%>px; color: <%=color%>; font-weight: <%=(bold ? "700" : "400")%>; font-style: <%= (italic ? "italic" : "normal") %>; text-decoration: <%= (underline ? "underline" : "normal") %> ">
			<%=text%>
		</span>
	</ic>
</script>