<?php
$random = rand(1000, 9999);
?>
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/user.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/avatar.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>" />
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/jquery.Jcrop.min.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/js/uploadify-v3.1/uploadify.css?<?php echo VERHASH;?>">
<div class="pc-banner">
	<img src="<?php echo $user["bg_big"];?>&random=<?php echo $random;?>" />
	<?php if ($this->getIsMe()) : ?>
	<a href="javascript:;" id="skin_choose" title="<?php echo $lang["Custom banner"];?>"></a>
	<?php endif; ?>
</div>
<div class="pc-usi">
	<div class="pc-usi-bg"></div>
	<?php if ($this->getIsMe()) : ?>
	<a href="<?php echo Ibos::app()->createUrl("user/home/personal", array("op" => "avatar", "uid" => $user["uid"]));?>" class="pc-usi-avatar posr">
			<img src="<?php echo $user["avatar_big"];?>&random=<?php echo $random;?>" alt="<?php echo $user["realname"];?>" width="180" height="180" />
			<div class="pc-img-shade" style="display:none;">
				<div class="pc-bg"></div>
				<div class="pc-upload-tip"><?php echo $lang["Edit avatar"];?></div>
			</div>
		</a>
	<?php else : ?>
		<span class="pc-usi-avatar posr">
			<img src="<?php echo $user["avatar_big"];?>&random=<?php echo $random;?>" alt="<?php echo $user["realname"];?>" width="180" height="180" />
		</span>
	<?php endif; ?>

	<?php if (Ibos::app()->user->uid !== $user["uid"]) : ?>
		<a href="javascript:Ui.showPmDialog(['<?php echo StringUtil::wrapId($user["uid"], "u");?>'],{url:'<?php echo Ibos::app()->createUrl("message/pm/post");?>'});Ibos.userCard.hide();" class="private-letter" title="<?php echo $lang["Send message"];?>">
			<i class="o-private-letter <?php echo $onlineIcon;?>"></i>
		</a>
	<?php endif; ?>
	<div class="pc-usi-name">
		<?php if ($user["gender"] == "1") : ?>
			<i class="om-male"></i>
		<?php else : ?>
			<i class="om-female"></i>
		<?php endif; ?>
		<strong><?php echo $user["realname"];?></strong>
		<?php if (!empty($user["deptname"])) : ?>
			<span><?php echo $user["deptname"];?></span>
		<?php endif; ?>
		<?php if (!empty($user["posname"])) : ?>
			<span><?php echo $user["posname"];?></span>
		<?php endif; ?>
	</div>
	<div class="pc-usi-sign clearfix">
		<div class="pull-left">
			<?php if ($this->getIsMe()) : ?>
				<a href="<?php echo Ibos::app()->createUrl("user/home/personal", array("uid" => $user["uid"]));?>" class="btn btn-small"><?php echo $lang["Edit profile"];?></a>
			<?php else : ?>
				<!-- 关注的几种状态 -->
				<?php if (!$states["following"]) : ?>
					<a href="javascript:;" class="btn btn-small btn-warning" data-action="follow" data-param='{"fid": <?php echo $user["uid"];?>}' data-loading-text="关注中...">
						<i class="om-plus"></i>
						<?php echo $lang["Focus"];?>
					<!--关注-->
					</a>
				<?php else : ?>
					<?php if ($states["following"] && $states["follower"]) : ?>
					<a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow" data-param='{"fid": <?php echo $user["uid"];?>}' data-loading-text="取消中...">
						<i class="om-geoc"></i>
						<?php echo $lang["Focus on each other"];?>
					<!--互相关注-->
					</a>
					<?php elseif ($states["following"]) : ?>
					<a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow" data-param='{"fid": <?php echo $user["uid"];?>}' data-loading-text="取消中...">
						<i class="om-gcheck"></i>
						<?php echo $lang["Has been focused"]; ?>
					<!-- 已关注 -->
					</a>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
			<span><?php echo $user["bio"];?></span>
		</div>
		<!-- 个人信息关注，粉丝，微博信息 -->
		<div class="pull-right">
			<ul class="list-inline pc-info-list">
				<li class="ml">
					<a href="<?php echo Ibos::app()->urlManager->createUrl("weibo/personal/following", array("uid" => $user["uid"]));?>">
						<strong class="xcbu fsl"><?php echo isset($userData["following_count"]) ? $userData["following_count"] : 0;?></strong>
						<p><?php echo $lang["Focus"];?></p>
					</a>
				</li>
				<li class="ml">
					<a href="<?php echo Ibos::app()->urlManager->createUrl("weibo/personal/follower", array("uid" => $user["uid"]));?>">
						<strong class="xcbu fsl"><?php echo isset($userData["follower_count"]) ? $userData["follower_count"] : 0;?></strong>
						<p><?php echo $lang["Fans"];?></p>
					</a>
				</li>
				<li class="ml">
					<a href="<?php echo Ibos::app()->urlManager->createUrl("weibo/personal/index", array("uid" => $user["uid"]));?>">
						<strong class="xcbu fsl"><?php echo isset($userData["weibo_count"]) ? $userData["weibo_count"] : 0;?></strong>
						<p><?php echo $lang["Weibo"];?></p>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>
<!-- 选择皮肤弹出框内容 -->
<div class="skin-bg" id="skin_bg" style="display:none;">
    <div class="sk-conten-top">
        <ul class="nav nav-skid skin-nav-skid">
            <li class="active">
            	<a href="javascript:;">选择模板</a>
            </li>
            <li>
            	<a href="javascript:;">自定义</a>
            </li>
        </ul>
    </div>
    <div class="skin-type-choose">
    	<div class="bg-choose mark">
    		<div class="template-bg">
    			<div>
    				<ul class="list-inline choose-list" id="choose_list">
    				</ul>
    			</div>
    		</div>
	    	<div class="model-page-choose" id="model_page_choose">
	    		<div id="perv_next" class="pager btn-group">
	    			<a class="btn btn-small" href="javascript:;" id="pre_bg_page">
						<i class="glyphicon-chevron-left"></i>
	    			</a>
	    			<a class="btn btn-small" href="javascript:;" id="next_bg_page">
	    				<i class="glyphicon-chevron-right"></i>
	    			</a>
					<span data-id="bg_page" data-value="1"></span>
	    		</div>
	    	</div>
    		<div class="sk-divider mbs"></div>
	    	<div class="clearfix">
				<?php if (Ibos::app()->user->uid == 1) : ?>
					<div class="pull-left delete-module">
						<a href="javascript:;" class="sk-delete-btn" id="sk_delete_btn">
							<i class="o-trash"></i>
							<span class="dib mlm">删除模板</span>
						</a>
					</div>
				<?php endif; ?>
	    		<div class="pull-right">
	    			<button type="button" class="btn" id="module_close">取消</button>
	    			<button type="button" class="btn btn-primary mlm" id="module_save">保存</button>
	    		</div>
	    	</div>
    	</div>
	    <div class="bg-choose model-skin mark">
	    	<div class="user-defined-bg mark">
				<form action="<?php echo Ibos::app()->urlManager->createUrl("user/skin/cropBg");?>" method="post" id="skin" class="update-pic cf">
	    			<div class="skin-choose-area mb active" id="skin_choose_area">
	    				<input type="file" id="skin_bg_choose">
						<input type="hidden" id="sk_x" name="x" />	
						<input type="hidden" id="sk_y" name="y" />	
						<input type="hidden" id="sk_w" name="w" />	
						<input type="hidden" id="sk_h" name="h" />	
						<input type="hidden" id='sk_img_src' name='src' />	
						<input type="hidden" name="formhash" value='<?php echo FORMHASH;?>' />
						<input type="hidden" name="uid" value="<?php echo Ibos::app()->user->uid;?>" />
						<input type="hidden" name="bgSubmit" value="1" />
	    				<div class="file-tips">
	    					<div class="mb xac">
								<i class="o-plus"></i>
								<span class="upload-text-tip">上传图片</span>
							</div>
	    					<div class="tcm upload-tip">
	    						<p>支持jpg、gif、png图片文件，且文件小于2MB，</p>
	    						<p>尺寸不小于1000x300。</p>
	    					</div>
	    				</div>
	    				<div class="preview hidden" id="preview_hidden" style="width: 9999px; height: 9999px;"></div>
	    			</div>
	    			<div class="sk-divider mbs"></div>
			        <div class="clearfix">
			        	<div class="pull-left upload-btn" style="display:none;">
			        		<a href="javascript:;" class="skin-reupload-img" id="skin_reupload_img">
			        			<i class="o-upload-btn"></i>
			        			<span>重新上传</span>
			        		</a>
			        	</div>
			        	<div class="pull-right">
							<?php if (Ibos::app()->user->uid == 1) : ?>
								<label class="checkbox dib sk-setting-model">
									<input type="checkbox" name="commonSet" value="同时设为公用模板" id="sk_setting_model" />同时设为公用模板
								</label>
							<?php endif; ?>
			        		<a class="btn" href="javascript:;" id="custom_close">取消</a>
			        		<a class="btn btn-primary mlm save-skin" id="save_skin" href="javascript:;">保存</a>
			        	</div>
			        </div>
	    		</form>
	    	</div>
	    </div>
	</div>
</div>
<div id="ava_progress" class="hide"></div>
<!-- 皮肤背景模板 -->
<script type="text/ibos-template" id="skin_template">
<li>
	<div class="posr">
		<a class="model-img posr" href="javascript:;">
			<img src="<%=imgUrl%>" data-id="<%=id%>">
			<i class="o-select-tip"></i>
		</a>
	</div>
</li>
</script>
<script>
	Ibos.app.setPageParam({
		allBg: <?php echo CJSON::encode(BgTemplate::model()->fetchAllBg());?>
	});
</script>
<script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/jquery.Jcrop.min.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/uploadify-v3.1/jquery.uploadify-3.1.min.js?<?php echo VERHASH;?>'></script>
<script>
	$(function() {
		//查看自己信息时，鼠标悬停在头部个人头像时，显示遮罩
		$(".pc-usi-avatar").hover(function() {
			$(".pc-img-shade").css("display", "block");
		}, function() {
			$(".pc-img-shade").css("display", "none");
		});

		//点击换肤,进行自定义和模板选择
		$("#skin_choose").on("click", function() {
			var bgHtml = getBgHtmlByPage(1);
			$("#choose_list").html(bgHtml);
			Ui.dialog({
				id: "skin_bg",
				title: U.lang("USER.INDIVIDUALITY_SETTING"),
				content: document.getElementById("skin_bg"),
				init: function(){
					initBgHover();
					isOnlyOnePage();
				},
				ok:false,
				padding: '0',
				drag: 'true'
			});
			
		});

		//切换选择"自定义"和"选择模板内容"
		$(".skin-nav-skid li").on('click', function() {
			var index = $(this).index();
			$(this).addClass("active").siblings().removeClass('active');
			$(".skin-type-choose .mark").eq(index).show().siblings().hide();
		});

		//点击选择模板时，显示边框和选择标识
		$("#choose_list").on("click", ".model-img", function() {
			$(".sk-delete-btn").css({"display":"inline-block"});
			var $elem = $(this);
			changeModelBorderStyle($elem);
		 });
		
		// 切换至上页皮肤
		$("#pre_bg_page").on("click", function(){
			var page = $("span[data-id='bg_page']").attr("data-value"),
				curPage = parseInt(page),
				prePage = curPage-1 > 0 ? curPage-1 : 1;
			if(curPage == 1){
				Ui.tip(U.lang("User.THE_FIRST_PAGE"), 'warning');
				return false;
			}
			switchPage(prePage);
		
		});

		// 切换至下页皮肤
		$("#next_bg_page").on("click", function(){
			var allBg = Ibos.app.g("allBg"),
				totlePage = Math.ceil(allBg.length/4),
				page = $("span[data-id='bg_page']").attr("data-value"),
				curPage = parseInt(page),
				nextPage = curPage+1;
			if(curPage == totlePage){
				Ui.tip(U.lang("USER.THE_FIRST_PAGE"), 'warning');
				return false;
			}
			switchPage(nextPage);
		});

		//模板选择栏时,取消按钮
		$("#module_close").on('click', function(){
			Ui.confirm(U.lang("USER.SETTING_NOT_SAVE"), function(){
				Ui.getDialog('skin_bg').close();
			});
		});

		//模板选择栏时,保存按钮
		$("#module_save").on('click', function(){
			var $selectLi = $("#choose_list").find('.model-img.active'),
				liLength = $selectLi.length,
				src = $selectLi.children().attr('src');
			var url = Ibos.app.url("user/skin/cropBg"),
				param = {bgSubmit: 1, noCrop: 1, src : src, uid: Ibos.app.g("uid"), selectCommon: 1};
			if(liLength){
				$.post(url, param, function(res) {
					if(res.isSuccess){
						$(".model-img").removeClass("active");
						$(".o-select-tip").css({"display":"none"});
						Ui.getDialog('skin_bg').close();
						//保存成功后刷新当前页面
						Ui.tip(U.lang("OPERATION_SUCCESS"));
						window.location.reload(); 
					}
				});
			}else{
				Ui.tip(U.lang("USER.SELECT_ONE_MODEL_PICTURE"), "danger");
			}
		});

		//删除选定模板
		$("#sk_delete_btn").on("click", function(){
			var $imgLength = Ibos.app.g("allBg").length,
				did = $("#choose_list").find('.model-img.active').children("img").attr("data-id");
			//当删除选定模版后只剩一张模版图片时，不能删除
			if($imgLength >=2){
				Ui.confirm(U.lang("USER.DELETE_CHOOSE_MODEL"), function(){
					var url = Ibos.app.url("user/skin/delbg"),
						param = {id : did};
					$.post(url, param, function(res){
						if(res.isSuccess){
							//视图层上删除节点
							$("#choose_list").find('.model-img.active').parents("li").remove();
							deleteSkinObj(did);	
							var page = $("span[data-id='bg_page']").attr("data-value"),
								curPage = parseInt(page);
							var bgHtml = getBgHtmlByPage(curPage);
							$("#choose_list").html(bgHtml);
							isOnlyOnePage();
							Ui.tip(U.lang("OPERATION_SUCCESS"));
						}
					}, "json");	
				});
			}else{
				Ui.tip(U.lang("USER.IS_LAST_MODEL"), "warning");
				return false;
			}
		});

		//提交裁剪好的图片
		$('#save_skin').on("click", function() {
			//判断是否将自定义上传图片设置为模板
			var commonSet = $("#sk_setting_model").is (":checked") ? 1 : 0;
			if ($('#preview_hidden').html() === '') {
				Ui.tip(U.lang("USER.UPLOAD_PICTURE"), 'danger');
				return false;
			} else {
				var updateBgUrl = Ibos.app.url("user/skin/cropBg"),
					bgData = getBgData();
				bgData.bgSubmit = "1";
				bgData.commonSet = commonSet;
				$.post(updateBgUrl, bgData, function(res){
					if(res.isSuccess){
						//上传图片成功后，重置图片选择栏
						Ui.getDialog('skin_bg').close();
						Ui.tip(U.lang("OPERATION_SUCCESS"), "success");
						// return
						window.location.reload();
					}
				}, 'json');
			}
		});

		//用户自定义时,取消按钮
		$("#custom_close").on('click', function(){
			var length = $('#preview_hidden').find('*').length;
			if(length !== 0){
				Ui.confirm(U.lang("USER.PERSONAL_SETTING_NOT_SAVE"), function(){
					Ui.getDialog('skin_bg').close();
					window.location.reload(); 
				});
			}else{
				Ui.getDialog('skin_bg').close();
			}
		});

		//初始化自定义模版上传功能
		var attachUpload = Ibos.upload.image({
			upload_url: Ibos.app.url("user/skin/uploadBg", { "uid": Ibos.app.g("uid"),"hash": '<?php echo $swfConfig["hash"];?>'} ),
			button_placeholder_id: "skin_bg_choose",
			file_size_limit: "2MB", //设置图片最大上传值
			button_width: "550",
			button_height: "170",
			button_image_url: "",
			custom_settings: {
				progressId: "skin_choose_area",
				//头像上传成功后的操作
				success: function(file, data) {
					if(data.isSuccess){
						$(".skin-choose-area").removeClass("active");
						var preview = $('#preview_hidden');
						preview.show().removeClass('hidden');
						//隐藏表单赋值
						$('#sk_img_src').val(data['file']);
						//绑定需要裁剪的图片
						var img = $('<img />');
						preview.append(img);
						preview.children('img').attr('src', data['data'] + '?random=' + Math.random());
						var crop_img = preview.children('img');
						crop_img.attr('id', "sk_cropbox").show();
						var img = new Image();
						img.src = data['data'] + '?random=' + Math.random();
						//根据图片大小在画布里居中
						img.onload = function() {
							var img_height = 0;
							var img_width = 0;
							var real_height = img.height;
							var real_width = img.width;
							if (real_height > real_width && real_height > 170) {
								var persent = real_height / 170;
								real_height = 170;
								real_width = real_width / persent;
							} else if (real_width > real_height && real_width > 550) {
								var persent = real_width / 550;
								real_width = 550;
								real_height = real_height / persent;
							}
							if (real_height < 170) {
								img_height = (170 - real_height) / 2;
							}
							if (real_width < 550) {
								img_width = (550 - real_width) / 2;
							}
							preview.css({width: (550 - img_width) + 'px', height: (170 - img_height) + 'px'});
							preview.css({paddingTop: img_height + 'px', paddingLeft: img_width + 'px'});
						};
						//裁剪插件
						$('#sk_cropbox').Jcrop({
							bgColor: '#333', //选区背景色
							bgFade: true, //选区背景渐显
							fadeTime: 1000, //背景渐显时间
							allowSelect: false, //是否可以选区，
							allowResize: true, //是否可以调整选区大小
							minSize: [170, 550], //可选最小大小
							boxWidth: 550, //画布宽度
							boxHeight: 170, //画布高度
							setSelect: [0, 0, 550, 170], //初始化时位置
							aspectRatio: 3.33,
							onSelect: function(c) {	//选择时动态赋值，该值是最终传给程序的参数！
								$('#sk_x').val(c.x);//需裁剪的左上角X轴坐标
								$('#sk_y').val(c.y);//需裁剪的左上角Y轴坐标
								$('#sk_w').val(c.w);//需裁剪的宽度
								$('#sk_h').val(c.h);//需裁剪的高度
							}
						});
						//上传图片成功后，将重新上传按钮隐藏
						$(".upload-btn").removeClass("hide").addClass("show");
					}else{
						Ui.tip(data.msg, 'danger');
						return false;
					}
				}
			}
		});



		//重新上传,清空裁剪参数
		$('#skin_reupload_img').on("click", function() {
			var $elem = $(this);
			$(".skin-choose-area").addClass("active");
			$elem.parent().removeClass("show").addClass("hide");
			$('#preview_hidden').find('*').remove();
			$('#preview_hidden').hide().addClass('hidden').css({'padding-top': 0, 'padding-left': 0, 'width':'9999px', 'height':'9999px'});
		});
		
		function getBgData(){
			var params = {
				  x: $("#sk_x").val(),
				  y: $("#sk_y").val(),
				  w: $("#sk_w").val(),
				  h: $("#sk_h").val(),
				  src: $("#sk_img_src").val(),
				  uid: $("input[name='uid']").val()
			};
			return params;
		}

		//点击重新上传时，上传按钮栏隐藏
		$("#reupload_bg").on("click", function() {
			$("#skin_btnbar").css("display", "none");
	    });
		
		/**
		 * 获取皮肤背景图html
		 */
		function getBgHtmlByPage(page){
			if(page<=0){
				page = 1;
			}
			var allBg = Ibos.app.g("allBg"),
				offset = (page-1)*4,
				bgHtml = '';
			for(var i=offset; i<offset+4 && i<allBg.length; i++){
				var data = {
					imgUrl: allBg[i].imgUrl,
					id: allBg[i].id,
					desc: allBg[i].desc
				};
				bgHtml += $.template('skin_template', data);
			}
			return bgHtml;
		}

		//初始化背景图片的hover效果
		function initBgHover(){
			//选择模板中，当鼠标悬停在模板时，显示模板信息
			$("#choose_list .model-img").hover(function() {
				$(this).find(".img-info-tip").css("display", "block");
			}, function() {
				$(this).find(".img-info-tip").css("display", "none");
			});
		}

		//删除模版图片数组中对应的图片对象
		function deleteSkinObj(id){
			var allBg = Ibos.app.g("allBg");
			for(var i = 0; i< allBg.length; i++){
				if(allBg[i].id == id){
					allBg.splice(i,1);
				}
			}
		}

		//切换上一页和下一页
		function switchPage(page){
			var bgHtml = getBgHtmlByPage(page);
			$("#choose_list").html(bgHtml);
			$("span[data-id='bg_page']").attr("data-value", page);
			$(".sk-delete-btn").css({"display":"none"});
		}

		//点击对应模版时，模版边框样式的改变
		function changeModelBorderStyle($elem){
			var $siblings = $elem.parents("li").eq(0).siblings();
			$siblings.find("a").removeClass("active");
			$elem.addClass("active");
			$siblings.find("i").css("display", "none");
			$elem.find(".o-select-tip").css("display", "block");
		}

		function isOnlyOnePage(){
			var allBg = Ibos.app.g("allBg"),
				totlePage = Math.ceil(allBg.length/4);
			if(totlePage == 1){
				$("#model_page_choose").css({"display":"none"});
			}
		}
	});
</script>