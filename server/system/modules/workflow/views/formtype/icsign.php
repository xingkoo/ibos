<!-- 签章控件 -->
<div id="ic_sign_menu_content">
	<div class="mb">
		<label for="sign_title">控件名称</label>
		<div>
			<input type="text" id="ic_sign_title">
		</div>
	</div>
	<div class="mb">
		<label>控件类型</label>
		<div>
			<label class="checkbox checkbox-inline"><input type="checkbox" id="ic_sign_type_stamp" checked name="type">盖章</label>
			<label class="checkbox checkbox-inline"><input type="checkbox" id="ic_sign_type_write" checked name="type">手写</label>
		</div>
	</div>
	<div class="mb">
		<label for="sign_color">手写颜色</label>
		<a href="javascript:;" id="ic_sign_color_btn"></a>
		<input type="hidden" id="ic_sign_color">
	</div>
	<div>
		<label>验证锁定标签(每行一个)</label>
		<div>
			<textarea id="ic_sign_field" rows="5"></textarea>
		</div>
	</div>
</div>
<!-- 签章控件模板 -->
<script type="text/template" id="ic_sign_tpl">
	<ic data-id="<%=id%>" data-type="sign" data-title="<%=title%>" data-sign-color="<%=signColor%>" data-sign-type="<%=signType%>" data-sign-field="<%=signField%>" contenteditable="false">
		<% if(stamp) { %><button type="button" class="btn" style="margin-right: 5px">签章</button><% }%>
		<% if(write) { %><button type="button" class="btn">手写</button><% }%>
	</ic>
</script>