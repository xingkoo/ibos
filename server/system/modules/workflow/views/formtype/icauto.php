<!-- 宏控件 -->
<div id="ic_auto_menu_content">
	<div class="mb">
		<label for="ic_auto_title">控件名称</label>
		<input type="text" id="ic_auto_title" value="宏控件">
	</div>
	<div class="mb">
		<label for="ic_auto_field">选择类型</label>
		<select id="ic_auto_field">
			<optgroup id="auto_field_input" label="单行输入框">
				<option value="sys_date">当前日期，形如 1999-01-01</option>
				<option value="sys_date_cn">当前日期，形如 2009年1月1日</option>
				<option value="sys_date_cn_short3">当前日期，形如 2009年</option>
				<option value="sys_date_cn_short4">当前年份，形如 2009</option>
				<option value="sys_date_cn_short1">当前日期，形如 2009年1月</option>
				<option value="sys_date_cn_short2">当前日期，形如 1月1日</option>
				<option value="sys_time">当前时间</option>
				<option value="sys_datetime">当前日期+时间</option>
				<option value="sys_week">当前星期中的第几天，形如 星期一</option>
				<option value="sys_userid">当前用户id</option>
				<option value="sys_realname">当前用户姓名</option>
				<option value="sys_deptname">当前用户部门(长名称)</option>
				<option value="sys_deptname_short">当前用户部门(短名称)</option>
				<option value="sys_userpos">当前用户岗位</option>
				<option value="sys_userposother">当前用户辅助岗位</option>
				<option value="sys_realname_date">当前用户姓名+日期</option>
				<option value="sys_realname_datetime">当前用户姓名+日期+时间</option>
				<option value="sys_formname">表单名称</option>
				<option value="sys_runname">工作名称/文号</option>
				<option value="sys_rundate">流程开始日期</option>
				<option value="sys_rundatetime">流程开始日期+时间</option>
				<option value="sys_runid">流水号</option>
				<option value="sys_autonum">文号计数器</option>
				<option value="sys_ip">经办人ip地址</option>
				<option value="sys_manager1">部门主管(本部门)</option>
				<option value="sys_manager2">部门主管(上级部门)</option>
				<option value="sys_manager3">部门主管(一级部门)</option>
				<option value="sys_sql">来自sql查询语句</option>
			</optgroup>
			<optgroup id="auto_field_select" label="下拉菜单">
				<option value="sys_list_dept">部门列表</option>
				<option value="sys_list_user">人员列表</option>
				<option value="sys_list_pos">角色列表</option>
				<option value="sys_list_prcsuser1">流程设置所有经办人列表</option>
				<option value="sys_list_prcsuser2">本步骤设置经办人列表</option>
				<option value="sys_list_manager1">部门主管(本部门)</option>
				<option value="sys_list_manager2">部门主管(上级部门)</option>
				<option value="sys_list_manager3">部门主管(一级部门)</option>
				<option value="sys_list_sql">来自sql查询语句的列表</option>
			</optgroup>
		</select>
	</div>
	<div id="ic_auto_normal_field">
		<div class="mb">
			<label for="ic_auto_width">控件样式</label>
			<div class="input-group">
				<input type="text" id="ic_auto_width">
				<span class="input-group-addon">宽</span>
			</div>
		</div>
		<div>
			<label>可见性</label>
			<label for="ic_auto_hide" class="checkbox">
				<input type="checkbox" id="ic_auto_hide">
			</label>
		</div>
	</div>
	<div id="ic_auto_src_field" style="display: none;">
		<span class="pull-right">
			<a href="javascript:;" id="ins_open">说明</a>
			<a href="javascript:;" id="src_test">测试</a>
		</span>
		<label for="ic_auto_src">SQL查询语句 ('号用`号替换)</label>
		<div class="input-group">
			<textarea rows="5" id="ic_auto_src"></textarea>
		</div>
	</div>
</div>
<!-- 宏控件模板 -->
<script type="text/template" id="ic_auto_tpl">
	<ic data-id="<%=id%>" data-type="auto" data-title="<%=title%>" data-src="<%=src%>" data-field="<%=field%>" data-width="<%=width%>" data-hide="<%=hide%>" contenteditable="false">
		<span class="fake-auto" style="width: <%=width%>px" title="<%=title%>">(宏)<%=fieldText%></span>
	</ic>
</script>