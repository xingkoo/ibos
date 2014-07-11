<!-- 计算控件 -->
<div id="ic_calc_menu_content">
	<div class="mb">
		<label for="ic_calc_title">控件名称</label>
		<input type="text" id="ic_calc_title" value="计算控件">
	</div>
	<div class="mb">
		<label for="ic_calc_width">控件样式</label>
		<div class="input-group">
			<input type="text" id="ic_calc_width">
			<span class="input-group-addon">宽</span>
		</div>
	</div>
	<div class="mb">
		<label for="ic_calc_prec">计算精度(默认保留小数点后4位)</label>
		<input type="text" id="ic_calc_prec">
	</div>
	<div>
		<div>
			<label for="ic_calc_value">计算公式</label><a id="ins_open_btn" href="javascript:;" class="pull-right">说明</a>
		</div>
		<div>
			<textarea id="ic_calc_value" rows="5"></textarea>
		</div>
	</div>
</div>
<div id="control_calc_ins" style="display: none;padding: 20px; ">
	<div>
		<h5>
			计算控件说明
			<button type="button" title="回到控件属性页" class="btn btn-small" id="ins_close_btn"> <i class="glyphicon-home"></i></button>
		</h5>
	</div>
	<div>
		<blockquote>
			<p>
				在日常的工作中，填写表单时，经常会存在一些计算项目，比如金额的计算，比如天数的计算，使用计算控件可以简化人员操作，提高准确性。
			</p>
			<p>以下举例说明计算控件的使用方法(以日历控件计算天数为例)：</p>
		</blockquote>
		<p>首先，先建立好需要参与计算的项目，如图建立好开始时间和结束时间这两个日历控件，当然了每个日历控件都有对应的输入框控件</p>
		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iccalc/calc1.png";?>" width="100%" height="200">			
		<p>
			接下来点击计算控件按钮，新建一个计算控件，设定时需要输入计算公式，公式的规则就是四则运算规则，可以利用括号和加减乘除，公式的计算项目就是上面
			建立的单行输入框控件的名称，如图：
		</p>
		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iccalc/calc2.png";?>" width="100%" height="200">			
		<p>上面日期差的实例实现的效果如图，而且<strong>计算控件的输入内容是不允许修改的。</strong></p>
		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iccalc/calc3.png";?>" width="100%" height="200">	
		<p>
			计算公式支持+ - * / ^和英文括号以及特定计算函数，例如：(数值1+数值2)*数值3-ABS(数值4)，其中数值1、数值2等为表单控件名称。 计算控件支持的函数计算如下：
		</p>
		<ol>
			<li>MAX(数值1,数值2,数值3...) 输出最大值,英文逗号分割；</li>
			<li>MIN(数值1,数值2,数值3...) 输出最小值,英文逗号分割；</li>
			<li>ABS(数值1) 输出绝对值；</li>
			<li>AVG(数值1,数值2,数值3) 输出平均值；</li>
			<li>RMB(数值1) 输出人民币大写形式，数值范围0～9999999999.99；</li>
			<li>DAY(日期1-日期2) 输出时间差的整数天数；</li>
			<li>HOUR(日期1-日期2) 输出时间差的小时数；</li>
			<li>DATE(日期1-日期2) 输出时间差，形如：xx天xx小时xx分xx秒；</li>
			<li>LIST(列表控件名,第几列) 计算列表控件指定列的和；</li>
		</ol>
		<p>值得说明的是LIST函数，它可以读取列表控件某列数据的和，下面以实例说明一下：</p>

		<p>假如设计的列表控件如下图</p>
		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iccalc/calc4.png";?>" width="100%" height="200">			
		<p>我们现在用计算控件将价格这一列的数据取出来，添加计算控件，公式书写如下：</p>
		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iccalc/calc5.png";?>" width="100%" height="200">		
		<p>实现效果如下：</p>
		<img src="<?php echo STATICURL . "/js/lib/ueditor/dialogs/iccalc/calc6.png";?>" width="100%" height="200">		
		<p>LIST函数主要用于列表控件数据参与条件设置的情况。</p>
		<strong>注意：参与日期计算的控件必须为日期类型或者日期+时间类型。</strong>
	</div>
</div>
<!-- 计算控件模板 -->
<script type="text/template" id="ic_calc_tpl">
	<ic data-id="<%=id%>" data-type="calc" data-title="<%=title%>" data-prec="<%=prec%>" data-width="<%=width%>" data-value="<%=value%>" contenteditable="false" >
	<span class="fake-calc" style="width: <%=width%>px" title="<%=title%>"><%=value%></span><input type="hidden" />
	</ic>
</script>
<script>
	$("#ins_open_btn").click(function() {
		Ui.dialog({
			title: '控件说明',
			id: 'calc_desc',
			width:'500px',
			cancel:true,
			content: document.getElementById('control_calc_ins')
		});
	});
</script>