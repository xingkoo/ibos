<div style="width: 450px;">
	<form class="form-horizontal">
		<div class="control-group">
			<input type="text" id="inputEmail" value="<?php echo $user["email"];?>" placeholder="<?php echo $lang["Bind email tip"];?>">
		</div>
		<div class="control-group">
			<button type='button' id="verify" data-loading-text="<?php echo CHtml::encode($lang["Send loading"]);?>" class="btn"><?php echo $lang["Send verify"];?></button>
			<span id='send_status'></span>
		</div>
		<div class="control-group">
			<input type="text" id="inputVerify" placeholder="<?php echo $lang["Email verify"];?>">
		</div>
	</form>
</div>
<script>
	$('#verify').on('click', function() {
		var that = this;
		if ($.trim($('#inputEmail').val()) === '') {
			$('#inputEmail').blink().focus();
			return false;
		}
		var myReg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
		if (!myReg.test($('#inputEmail').val())) {
			$.jGrowl('<?php echo $lang["Incorrect email address"];?>', {theme: 'danger'});
			$('#inputEmail').blink().focus();
			return false;
		}
		$(that).button('loading');
		var checkUrl = '<?php echo $this->createUrl("home/checkRepeat", array("op" => "email", "uid" => $this->getUid()));?>';
		$.get(checkUrl, {data: encodeURI($('#inputEmail').val())}, function(data) {
			if (data.isSuccess) {
				$('#inputEmail').parent().addClass('success');
				var wait = document.getElementById('counting'),
						time = --wait.innerHTML,
						interval = setInterval(function() {
							var time = --wait.innerHTML;
							if (time === 0) {
								$(that).button('reset');
								clearInterval(interval);
							}
						}, 1000);
			} else {
				$(that).button('reset');
				$('#inputEmail').parent().addClass('error');
				$('#send_status').html(data.msg);
			}
		}, 'json');
	});
</script>