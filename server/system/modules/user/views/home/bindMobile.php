<div style="width: 450px;">
	<form class="form-horizontal">
		<div class="control-group">
			<input type="text" id="inputMobile" value="<?php echo $user["mobile"];?>" placeholder="<?php echo $lang["Bind Mobile tip"];?>">
		</div>
		<div class="control-group">
			<button type='button' id="verify" data-loading-text="<?php echo $lang["Send loading"];?>" class="btn"><?php echo $lang["Send verify"];?></button>
			<span id='send_status'></span>
		</div>
		<div class="control-group">
			<input type="text" id="inputVerify" placeholder="<?php echo $lang["Mobile verify"];?>">
		</div>
	</form>
</div>
<script>
	$('#verify').on('click', function() {
		var that = this;
		if ($.trim($('#inputMobile').val()) === '') {
			$('#inputMobile').blink().focus();
			return false;
		}
		var myReg = /^1[3|4|5|8][0-9]\d{8}$/;
		if (!myReg.test($('#inputMobile').val())) {
			Ui.tip('<?php echo $lang["Incorrect mobile"];?>', 'danger');
			$('#inputMobile').blink().focus();
			return false;
		}
		$(that).button('loading');
		var checkUrl = '<?php echo $this->createUrl("home/checkRepeat", array("op" => "mobile", "uid" => $this->getUid()));?>';
		$.get(checkUrl, {data: encodeURI($('#inputMobile').val())}, function(data) {
			if (data.isSuccess) {
				$('#inputMobile').parent().addClass('success');
				var wait = document.getElementById('counting'),time = --wait.innerHTML,
						interval = setInterval(function() {
							var time = --wait.innerHTML;
							if (time === 0) {
								$(that).button('reset');
								clearInterval(interval);
							}
						}, 1000);
			} else {
				$(that).button('reset');
				$('#inputMobile').parent().addClass('error');
				$('#send_status').html(data.msg);
			}
		}, 'json');
	});
</script>