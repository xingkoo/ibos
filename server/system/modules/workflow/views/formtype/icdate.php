<!-- 日历控件 -->
<div id="ic_date_menu_content">
	<div class="mb">
		<label for="ic_date_title">控件名称</label>
		<input type="text" id="ic_date_title" value="日历控件">
	</div>
	<div class="mb">
		<label for="ic_date_format">日期显示格式</label>
		<select id="ic_date_format">
			<option value="yyyy-mm-dd hh:ii:ss">年-月-日 时:分:秒</option>
			<option value="yyyy-mm-dd hh:ii">年-月-日 时:分</option>
			<option value="yyyy-mm-dd hh">年-月-日 时</option>
			<option value="yyyy-mm-dd">年-月-日</option>
			<option value="yyyy-mm">年-月</option>
			<option value="yyyy">年</option>
		</select>
	</div>
	<div>
		<label for="ic_date_width">控件样式</label>
		<div class="input-group">
			<input type="text" id="ic_date_width">
			<span class="input-group-addon">宽</span>
		</div>
	</div>
</div>
<!-- 日历控件模板 -->
<script type="text/template" id="ic_date_tpl">
	<ic data-id="<%=id%>" data-type="date" data-title="<%=title%>" data-date-format="<%=format%>" data-width="<%=width%>" contenteditable="false" >
		<input type="text" style="width: <%=width%>px" title="<%=title%>" value="<%=formatText%>"/>
	</ic>
</script>