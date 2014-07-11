<!-- 图片上传控件 -->
<div id="ic_imgupload_menu_content">
	<div class="mb">
		<label for="ic_imgupload_title">控件名称</label>
		<div>
			<input type="text" id="ic_imgupload_title">
		</div>
	</div>
	<div class="row">
		<div class="span6">
			<label for="ic_imgupload_width" class="input-group">
				<input type="text" id="ic_imgupload_width">
				<span class="input-group-addon">宽</span>
			</label>
		</div>
		<div class="span6">
			<label for="ic_imgupload_height" class="input-group">
				<input type="text" id="ic_imgupload_height">
				<span class="input-group-addon">高</span>
			</label>
		</div>
	</div>
</div>
<!-- 图片上传模板 -->
<script type="text/template" id="ic_imgupload_tpl">
	<ic data-id="<%=id%>" data-type="imgupload" data-title="<%=title%>" data-width="<%=width%>" data-height="<%=height%>" contenteditable="false">
		<span class="fake-imgupload" title="<%=title%>" style="width: <%=width%>px; height: <%=height%>px" /><input type="hidden" /></span>
	</ic>
</script>