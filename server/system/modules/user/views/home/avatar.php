<div class="mc mcf clearfix">
	<?php echo $this->getHeader($lang);?>
	<div>
		<div>
			<ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
				<li><a href="<?php echo $this->createUrl("home/index", array("uid" => $this->getUid()));?>"><?php echo $lang["Home page"];?></a></li>
				<?php if ($this->getIsWeiboEnabled()) : ?>
					<li><a href="<?php echo Ibos::app()->urlManager->createUrl("weibo/personal/index", array("uid" => $this->getUid()));?>"><?php echo $lang["Weibo"];?></a></li>
				<?php endif; ?>

				<?php if ($this->getIsMe()) : ?>
					<li><a href="<?php echo $this->createUrl("home/credit", array("uid" => $this->getUid()));?>"><?php echo $lang["Credit"];?></a></li>
				<?php endif; ?>
				<li class="active"><a href="<?php echo $this->createUrl("home/personal", array("uid" => $this->getUid()));?>"><?php echo $lang["Profile"];?></a></li>
			</ul>
		</div>
	</div>
</div>
<div class="pc-header clearfix">
    <ul class="nav nav-skid">
		<li>
			<a href="<?php echo $this->createUrl("home/personal", array("op" => "profile", "uid" => $this->getUid()));?>"><?php echo $lang["My profile"];?></a>
		</li>
		<?php if ($this->getIsMe()) : ?>
			<li class="active">
				<a href="<?php echo $this->createUrl("home/personal", array("op" => "avatar", "uid" => $this->getUid()));?>"><?php echo $lang["Upload avatar"];?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl("home/personal", array("op" => "password", "uid" => $this->getUid()));?>"><?php echo $lang["Change password"];?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl("home/personal", array("op" => "remind", "uid" => $this->getUid()));?>"><?php echo $lang["Remind setup"];?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl("home/personal", array("op" => "history", "uid" => $this->getUid()));?>"><?php echo $lang["Login history"];?></a>
			</li>
		<?php endif; ?>
	</ul>
</div>
<div>
	<div class="pc-container clearfix dib left-sidebar">
		<div class="data-title">
			<i class="o-upload"></i><span class="fsl vam"><?php echo $lang["Upload avatar"];?></span>
		</div>
		<div class="fill-nn clearfix">
			<form action="<?php echo $this->createUrl("info/cropimg");?>" method="post" id="pic" class="update-pic cf">
				<div class="mb">
					<div class="upload-area" id="upload_area">
						<input type="file" id="user-pic">	
						<div class="file-tips">
							<div class="tcm avater-upload-tip">
								<div class="mb xac">
									<i class="o-big-upload-tip"></i>
									<p class="upload-text-tip">上传头像</p>
								</div>
								<?php echo $lang["Avatar tip"];?>
							</div>
						</div>
						<div class="preview hidden" id="preview-hidden"></div>
					</div>
					<div class="preview-area">
						<input type="hidden" id="x" name="x" />	
						<input type="hidden" id="y" name="y" />	
						<input type="hidden" id="w" name="w" />	
						<input type="hidden" id="h" name="h" />	
						<input type="hidden" id='img_src' name='src' />	
						<input type="hidden" name="formhash" value='<?php echo FORMHASH;?>' />
						<input type="hidden" name="userSubmit" value="1" />	
						<input type="hidden" name="op" value="<?php echo $op;?>" />
						<input type="hidden" name="uid" value="<?php echo $this->getUid();?>" />
						<div class="tcrop">
							<?php echo $lang["Avatar review"];?>
						</div>
						<?php $random = rand(1000, 9999);?>
	
						<div class="crop crop180">
							<img id="crop-preview-180" src="avatar.php?uid=<?php echo $user["uid"];?>&size=big&engine=<?php echo ENGINE;?>&random=<?php echo $random;?>" />
						</div>
						<p class="tcm fss size-tip">180*180<?php echo $lang["Pixel"];?></p>
						<div class="crop crop60">
							<img id="crop-preview-60" src="avatar.php?uid=<?php echo $user["uid"];?>&size=middle&engine=<?php echo ENGINE;?>&random=<?php echo $random;?>" />
						</div>
						<p class="tcm fss size-tip">60*60<?php echo $lang["Pixel"];?></p>
						<div class="crop crop30">
							<img id="crop-preview-30" src="avatar.php?uid=<?php echo $user["uid"];?>&size=small&engine=<?php echo ENGINE;?>&random=<?php echo $random;?>" />
						</div>
						<p class="tcm fss size-tip">30*30<?php echo $lang["Pixel"];?></p>
					</div>
				</div>
				<div class="clearfix upload-btnbar" id="upload_btnbar">
					<a class="save-pic btn btn-primary btn-large pull-right" href="javascript:;"><?php echo $lang["Save"];?></a>
					<a class="reupload-img btn btn-large pull-left" href="javascript:$('#upload_btnbar').css('display','none'); void(0);"><?php echo $lang["Reupload"];?></a>
				</div>
			</form>
		</div>
	</div>
	<!-- 右栏 完善情况 -->
	<?php $this->widget("IWUserProfileTracker", array("user" => $user));?>
</div>
<div id="ava_progress" class="hide"></div>
<script src='<?php echo $assetUrl;?>/js/user.js?<?php echo VERHASH;?>'></script>
<script>

	(function() {
		var attachUpload = Ibos.upload.image({
			upload_url: Ibos.app.url('user/info/uploadavatar', { "uid": Ibos.app.g("uid"), "hash": "<?php echo $swfConfig["hash"];?>" }),
			button_placeholder_id: "user-pic",
			file_size_limit: "2MB", //设置图片最大上传值
			button_width: "408",
			button_height: "408",
			button_image_url: "",
			custom_settings: {
				progressId: "upload_area",

				//头像上传成功后的操作
				success: function(file, data) {
					if(data.IsSuccess){
						var preview = $('.upload-area').children('#preview-hidden');
						preview.show().removeClass('hidden');
						//三个预览窗口赋值
						$('.crop').children('img').attr('src', data['data'] + '?random=' + Math.random());
						//隐藏表单赋值
						$('#img_src').val(data['file']);
						//绑定需要裁剪的图片
						var img = $('<img />');
						preview.append(img);
						preview.children('img').attr('src', data['data'] + '?random=' + Math.random());
						var crop_img = preview.children('img');
						crop_img.attr('id', "cropbox").show();
						var img = new Image();
						img.src = data['data'] + '?random=' + Math.random();
						//根据图片大小在画布里居中
						img.onload = function() {
							var img_height = 0;
							var img_width = 0;
							var real_height = img.height;
							var real_width = img.width;
							if (real_height > real_width && real_height > 408) {
								var persent = real_height / 408;
								real_height = 408;
								real_width = real_width / persent;
							} else if (real_width > real_height && real_width > 408) {
								var persent = real_width / 408;
								real_width = 408;
								real_height = real_height / persent;
							}
							if (real_height < 408) {
								img_height = (408 - real_height) / 2;
							}
							if (real_width < 408) {
								img_width = (408 - real_width) / 2;
							}
							preview.css({width: (408 - img_width) + 'px', height: (408 - img_height) + 'px'});
							preview.css({paddingTop: img_height + 'px', paddingLeft: img_width + 'px'});
						};
						//裁剪插件
						$('#cropbox').Jcrop({
							bgColor: '#333', //选区背景色
							bgFade: true, //选区背景渐显
							fadeTime: 1000, //背景渐显时间
							allowSelect: false, //是否可以选区，
							allowResize: true, //是否可以调整选区大小
							aspectRatio: 1, //约束比例
							minSize: [180, 180], //可选最小大小
							boxWidth: 408, //画布宽度
							boxHeight: 408, //画布高度
							onChange: showPreview, //改变时重置预览图
							onSelect: showPreview, //选择时重置预览图
							setSelect: [0, 0, 180, 180], //初始化时位置
							onSelect: function(c) {	//选择时动态赋值，该值是最终传给程序的参数！
								$('#x').val(c.x);//需裁剪的左上角X轴坐标
								$('#y').val(c.y);//需裁剪的左上角Y轴坐标
								$('#w').val(c.w);//需裁剪的宽度
								$('#h').val(c.h);//需裁剪的高度
							}
						});
						//提交裁剪好的图片
						$('.save-pic').click(function() {
							if ($('#preview-hidden').html() === '') {
								Ui.tip(U.lang("USER.UPLOAD_PICTURE"), 'danger');
								return false;
							} else {
								//由于GD库裁剪gif图片很慢，所以显示loading
								$('.fill-nn').waitingC();
								$('#pic').submit();
							}
						});
						//重新上传,清空裁剪参数
						var i = 0;
						$('.reupload-img').click(function() {
							$('#preview-hidden').find('*').remove();
							$('#preview-hidden').hide().addClass('hidden').css({'padding-top': 0, 'padding-left': 0});
						});
						//当头像上传成功后,显示重新上传和保存按钮
						$("#upload_btnbar").css("display", "block");
					}else{
						Ui.tip(data.msg, 'danger');
						return false;
					}
				}
			}
		});
		
		//预览图
		function showPreview(coords) {
			var img_width = $('#cropbox').width();
			var img_height = $('#cropbox').height();
			//根据包裹的容器宽高,设置被除数
			var rx = 180 / coords.w;
			var ry = 180 / coords.h;
			$('#crop-preview-180').css({
				width: Math.round(rx * img_width) + 'px',
				height: Math.round(ry * img_height) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});
			rx = 60 / coords.w;
			ry = 60 / coords.h;
			$('#crop-preview-60').css({
				width: Math.round(rx * img_width) + 'px',
				height: Math.round(ry * img_height) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});
			rx = 30 / coords.w;
			ry = 30 / coords.h;
			$('#crop-preview-30').css({
				width: Math.round(rx * img_width) + 'px',
				height: Math.round(ry * img_height) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});
		}
	})();
</script>