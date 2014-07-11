<!-- 进度条控件 -->
<div id="ic_progressbar_menu_content">
	<div class="mb">
		<label for="ic_progressbar_title">控件名称</label>
		<div>
			<input type="text" id="ic_progressbar_title">
		</div>
	</div>
	<div class="mb">
		<label for="ic_progressbar_width">控件样式</label>
		<div>
			<div class="input-group">
				<input type="text" id="ic_progressbar_width">
				<span class="input-group-addon">宽</span>
			</div>
		</div>
	</div>
	<div class="mb">
		<label>跨度</label>
		<div>
			<div id="ic_progressbar_slider"></div>
		</div>
	</div>
	<div>
		<label class="radio">
			<input type="radio" name="style" value="success">
			<div class="progress progress-striped active">
				<div class="progress-bar progress-bar-success" style="width: 25%"></div>
			</div>
			<span class="fss">表示完成、成功</span>
		</label>
		<label class="radio">
			<input type="radio" name="style" value="warning">
			<div class="progress progress-striped active">
				<div class="progress-bar progress-bar-warning" style="width: 50%"></div>
			</div>
			<span class="fss">表示进行中，比较重要的事项</span>
		</label>
		<label class="radio">
			<input type="radio" name="style" value="primary" checked>
			<div class="progress progress-striped active">
				<div class="progress-bar" style="width: 75%"></div>
			</div>
			<span class="fss">表示信息、进度</span>
		</label>
	</div>
</div>
<!-- 进度条控件模板 -->
<script type="text/template" id="ic_progressbar_tpl">
	<ic data-id=<%=id%> data-type="progressbar"  data-title="<%=title%>" data-width="<%=width%>" data-step="<%=step%>" data-progress-style="<%=style%>" contenteditable="false">
		<span class="progress progress-striped active" style="display: inline-block; width: <%=width%>px" title="<%=title%>">
			<span class="progress-bar progress-bar-<%=style%>" style="display: inline-block; width: <%=step%>%"><input type="hidden" /></span>
		</span>
	</ic>
</script>
<script>
	$(".radio input").label();
</script>