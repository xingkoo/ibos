<!-- 宏控件 -->
<div id="ic_qrcode_menu_content">
	<div class="mb">
		<label for="qrcode_title">控件名称</label>
		<input type="text" id="ic_qrcode_title">
	</div>
	<div class="mb">
		<div class="btn-group btn-group-justified" id="qrcode_type" data-toggle="buttons-radio">
			<a href="#qrcode_type_text" data-toggle="tab" class="btn active"  data-value="text">内容</a>
			<a href="#qrcode_type_tel" data-toggle="tab" class="btn" data-value="tel">电话</a>
			<a href="#qrcode_type_sms" data-toggle="tab" class="btn" data-value="sms">短信</a>
			<a href="#qrcode_type_mecard" data-toggle="tab" class="btn" data-value="mecard">名片</a>
			<a href="#qrcode_type_wifi" data-toggle="tab" class="btn" data-value="wifi">WIFI</a>
		</div>
	</div>
	<div class="mb tab-content" id="qrcode_field">
		<div id="qrcode_type_text" class="tab-pane active">
			<textarea id="qrcode_text" rows="5" data-type="text"></textarea>
			<p class="qrcode-tip">推荐140字以内，手机摄像头更容易辨识。</p>
		</div>
		<div id="qrcode_type_tel" class="tab-pane">
			<label for="qrcode_tel">电话号码</label>
			<input type="text" data-type="tel" id="qrcode_tel">
		</div>
		<div id="qrcode_type_sms" class="tab-pane">
			<label for="qrcode_sms_tel">电话号码</label>
			<div><input type="text" id="qrcode_sms_tel" data-type="sms"></div>
			<label for="qrcode_sms_content">短信内容</label>
			<div><textarea id="qrcode_sms_content" data-type="sms" rows="3"></textarea></div>
		</div>
		<div id="qrcode_type_mecard" class="tab-pane mecard">
			<div class="row">
				<div class="span6 mbs">
					<label>姓名</label>
					<input type="text" data-type="mecard" id="qrcode_mecard_n">
				</div>
				<div class="span6 mbs">
					<label>电话</label>
					<input type="text" data-type="mecard" id="qrcode_mecard_tel">
				</div>
				<div class="span6 mbs">
					<label>公司</label>
					<input type="text" data-type="mecard" id="qrcode_mecard_org">
				</div>
				<div class="span6 mbs">
					<label>职位</label>
					<input type="text" data-type="mecard" id="qrcode_mecard_til">
				</div>
			</div>
			<div class="mbs">
				<label>邮箱</label>
				<input type="text" data-type="mecard" id="qrcode_mecard_email">
			</div>
			<div>						
				<label>地址</label>
				<input type="text" data-type="mecard" id="qrcode_mecard_adr">
			</div>
		</div>
		<div id="qrcode_type_wifi" class="tab-pane">
			<div class="mbs">
				<label>SSID</label>
				<input type="text" data-type="wifi" id="qrcode_wifi_s">
			</div>
			<div class="mbs">
				<label>密码</label>
				<input type="text" data-type="wifi" id="qrcode_wifi_p">
			</div>
			<div class="mbs">
				<label>加密类型</label>
				<select data-type="wifi" id="qrcode_wifi_t">
					<option value="WPA">WPA/WPA2</option>
					<option value="WEP">WEP</option>
					<option value="nopass">无密码</option>
				</select>
			</div>
		</div>
	</div>
	<div class="mb clearfix">
		<div class="qrcode-preview">
			<div>						
				<img src="system/modules/workflow/static/image/qrcode.png">
				<p>输入内容后将自动为您生成二维码预览</p>
			</div>
			<div id="qrcode_preview">
		</div>
		</div>
		<div class="qrcode-size">
			<div class="qrcode-size-title">二维码尺寸</div>
			<label class="radio"><input type="radio" name="size" value="0">小</label>
			<label class="radio"><input type="radio" name="size" value="1" checked>中</label>
			<label class="radio"><input type="radio" name="size" value="2">大</label>
		</div>
	</div>

</div>
<!-- 宏控件模板 -->
<script type="text/template" id="ic_qrcode_tpl">
	<ic data-id="<%=id%>" data-title="<%=title%>" data-type="qrcode" data-qrcode-type="<%=qrcodeType%>" data-value="<%=value%>" data-qrcode-size="<%=qrcodeSize%>" contenteditable="false">
		<span class="fake-qrcode"><input type="hidden" /></span>
	</ic>
</script>
<script>
	(function(){
		var Qr = {
			// 获取二维码尺寸
			getSize: function(){
				var radios = document.getElementsByName("size");
				for(var i = 0, len = radios.length; i < len; i++) {
					if(radios[i].checked){
						return radios[i].value;
					}
				}
			},
			// 设置二维码尺寸
			setSize: function(value){
				var radios = document.getElementsByName("size");
				for(var i = 0, len = radios.length; i < len; i++) {
					if(radios[i].value === value){
						radios[i].checked = true;
						break;
					}
				}
			},
			// 获取类型
			getType: function(){
				return $("#qrcode_type").find(".active").attr("data-value");
			},
			setType: function(type){
				$("#qrcode_type").find("a").each(function(){
					if($.attr(this, "data-value") === type) {
						$(this).button("toggle").tab("show");
						return false;
					}
				})
			},
			getValue: function() {
				var value = "",
					type = this.getType(),
					objToStr = function(obj) {
						var str = "";
						for(var i in obj) {
							if(obj.hasOwnProperty(i) && obj[i] !== "") {
								str += i + ":" + obj[i] + ";"
							}
						}
						return str;
					}
				switch (type) {
					case 'text':
						value = Dom.byId('qrcode_text').value;
						break;
					case 'tel':
						var tel = $.trim(Dom.byId('qrcode_tel').value);
						value = tel ? "tel:" + tel : tel;
						break;
					case 'sms':
						var smsTel = $.trim(Dom.byId('qrcode_sms_tel').value),
							smsContent = $.trim(Dom.byId('qrcode_sms_content').value);
						value = (smsTel && smsContent) ? ( "smsto:" + smsTel + ":" + smsContent) : "";
						break;
					case 'mecard':
						var data = {
							N: $.trim(Dom.byId('qrcode_mecard_n').value),
							ORG: $.trim(Dom.byId('qrcode_mecard_org').value),
							TIL: $.trim(Dom.byId('qrcode_mecard_til').value),
							TEL: $.trim(Dom.byId('qrcode_mecard_tel').value),
							EMAIL: $.trim(Dom.byId('qrcode_mecard_email').value),
							ADR: $.trim(Dom.byId('qrcode_mecard_adr').value)
						}
						if(data.N && data.TEL) {
							value = "MECARD:" + objToStr(data);
						}
						break;
					case 'wifi':
						var t = Dom.byId('qrcode_wifi_t').value;
							p = t === "nopass" ? "" : Dom.byId('qrcode_wifi_p').value;
						var data = {
							S: $.trim(Dom.byId('qrcode_wifi_s').value),
							T: $.trim(t),
							P: p
						}
						if(data.S) {
							value = "WIFI:" + objToStr(data);
						}
						break;
				}
				return value;
			},
			setValue: function(type, value){
				var reg, result;
				if(!value){ 
					return false;
				}
				switch (type) {
					case 'text':
						Dom.byId('qrcode_text').value = value;
						break;
					case 'tel':
						reg = /tel:(.*)/
						Dom.byId('qrcode_tel').value = reg.exec(value)[1];
						break;
					case 'sms':
						reg = /smsto:(.*):(.*)/;
						result = reg.exec(value);
						Dom.byId('qrcode_sms_tel').value = result[1];
						Dom.byId('qrcode_sms_content').value = result[2];
						break;
					case 'mecard':
						reg = /MECARD:(.*);/;
						result = reg.exec(value)[1].split(";");
						var data = {};
						for(var i = 0; i < result.length; i++) {
							if(result[i]){
								var kv = result[i].split(":");
								data[kv[0]] = kv[1];
							}
						}
						Dom.byId('qrcode_mecard_n').value = data.N;
						Dom.byId('qrcode_mecard_tel').value = data.TEL;
						Dom.byId('qrcode_mecard_org').value = data.ORG || "";
						Dom.byId('qrcode_mecard_til').value = data.TIL || "";
						Dom.byId('qrcode_mecard_email').value = data.EMAIL || "";
						Dom.byId('qrcode_mecard_adr').value = data.ADR || "";

						break;
					case 'wifi':
						reg = /WIFI:(.*)/
						result = reg.exec(value)[1].split(";");
						var data = {};
						for(var i = 0; i < result.length; i++) {
							if(result[i]){
								var kv = result[i].split(":");
								data[kv[0]] = kv[1];
							}
						}
						Dom.byId('qrcode_wifi_s').value = data.S;
						Dom.byId('qrcode_wifi_t').value = data.T;
						Dom.byId('qrcode_wifi_p').value = data.P || "";
						break;
				}
				return value;
			},
			createPreview: function(text) {
				var $container = $("#qrcode_preview");
				text = Ibos.string.utf16to8(text);
				if($.trim(text) !== ""){
					$container.empty().qrcode({
						text: text,
						width: 148,
						height: 148,
						render: ($.browser.msie && +$.browser.version < 9) ? 'table': 'canvas'
					}).prev().hide();
				} else {
					$container.empty().prev().show();
				}
			}
		}
		Ui.Menu.getIns('ic_qrcode_menu').Qr = Qr;

		var timer,
			delayQrcodeCreate = function(){
				clearTimeout(timer);
				timer = setTimeout(function() {
					Qr.createPreview(Qr.getValue());
				}, 500);
			}
		$("#qrcode_field").on("keyup", "input[type='text'], textarea", delayQrcodeCreate)
		.on("change", "select", delayQrcodeCreate);
		$("#qrcode_type").on("click", ">a", delayQrcodeCreate);
		
	})();
</script>