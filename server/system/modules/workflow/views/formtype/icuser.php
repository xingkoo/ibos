<!-- 部门人员控件 -->
<div id="ic_user_menu_content">
	<div class="mb">
		<label for="ic_user_title">控件名称</label>
		<input type="text" id="ic_user_title" value="部门人员控件">
	</div>
	<div class="mb">
		<label for="ic_user_value">选择类型</label>
		<select id="ic_user_type" class="mbs">
			<option value="user">选择人员</option>
			<option value="department">选择部门</option>
			<option value="position">选择岗位</option>
		</select>
		<label for="ic_user_single" class="checkbox">
			<input type="checkbox" id="ic_user_single">
			单选
		</label>
	</div>
	<div>
		<label for="ic_user_width">控件样式</label>
		<div class="input-group">
			<input type="text" id="ic_user_width">
			<span class="input-group-addon">宽</span>
		</div>
	</div>
</div>
<!-- 部门人员控件模板 -->
<script type="text/template" id="ic_user_tpl">
	<ic data-id="<%=id%>" data-type="user" data-title="<%=title%>" data-select-type="<%=selectType%>" data-width="<%=width%>" data-single="<%=single%>" contenteditable="false" >
		<!--<span class="fake-user" style="width: <%=width%>px" title="<%=title%>"><%=selectTypeText%></span>-->
		<input type="text" style="width: <%=width%>px" title="<%=title%>" value="<%=selectTypeText%>"/>
	</ic>
</script>