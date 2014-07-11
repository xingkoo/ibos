<!-- private css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="page-list">
			<div class="page-list-header">
			</div>
			<div class="page-list-mainer">
				<?php if ( !empty( $list ) ): ?>
					<ul class="main-list msg-list" id="atme_list">
						<?php foreach ( $list as $key => $at ) : ?>
							<li class="main-list-item">
								<div class="avatar-box pull-left">
									<?php if ( $at['source_table'] == 'comment' ): ?>
										<a href="<?php echo $at['comment_user_info']['space_url']; ?>" class="avatar-circle">
											<img class="mbm" src="<?php echo $at['comment_user_info']['avatar_middle']; ?>" alt="">
										</a>
										<span class="avatar-desc"><strong><?php echo $at['comment_user_info']['realname']; ?></strong></span>
									<?php else : ?>
										<a href="<?php echo $at['source_user_info']['space_url']; ?>" class="avatar-circle">
											<img class="mbm" src="<?php echo $at['source_user_info']['avatar_middle']; ?>" alt="">
										</a>
										<span class="avatar-desc"><strong><?php echo $at['source_user_info']['realname']; ?></strong></span>
									<?php endif; ?>
								</div>
								<div class="main-list-item-body">
									<div class="msg-box" id="msgbox_<?php echo $key; ?>">
										<span class="msg-box-arrow"><i></i></span>
										<div class="msg-box-body">
											<p class="xcm mbm"><?php echo $at['source_content'] ?></p>
											<p class="tcm mb">
												<?php echo $at['source_type'] ?>
											</p>
											<div>
												<span class="tcm fss"><?php if($at['source_table'] == 'comment' ): ?><?php echo date('n月j日H:i',$at['ctime']); ?><?php else: ?><?php echo $at['ctime']; ?><?php endif; ?></span>
												<!--<div class="pull-right">	
													<a href="javascript:;" data-act="reply" data-act-data="id=<?php echo $key; ?>&reply_name=<?php echo $at['source_user_info']['realname']; ?>" data-target="#msgbox_<?php echo $key; ?>"><?php echo $lang['Reply']; ?></a>
												</div>-->	
											</div>
										</div>
										<!--<div class="msg-box-reply">
											<span class="msg-box-reply-arrow"><i></i></span>
											<div class="mbs">
												<textarea name="" rows="3"></textarea>
											</div>
											<div class="clearfix">
												<a href="#" class="cbtn o-expression"></a>
												<div class="pull-right">
													<span class="msg-word-count">140</span>
													<button type="button" class="btn btn-small btn-primary" data-act-data="type=reply&touid=<?php echo $at['source_user_info']['uid']; ?>&rowid=<?php if($at['source_table'] == 'comment'){ echo $at['rowid'];} else { echo $at['feedid'];}  ?>&table=comment&module=message&sourceUrl=<?php echo urlencode(Yii::app()->urlManager->createUrl( 'message/comment/index')); ?>"><?php echo $lang['Send']; ?></button>
												</div>
											</div>
										</div>-->
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<div class="page-list-footer">
				<?php
					$this->widget( 'IWPage', array( 'pages' => $pages ) );
				?>
			</div>
		</div>
		<!-- Mainer content -->
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script>
	// 回复
	$atmeList = $("#atme_list");
	$atmeList.find(".msg-box-reply").each(function() {
		var $el = $(this),
			$input = $el.find("textarea"),
			$display = $el.find(".msg-word-count"),
			$submitBtn = $el.find(".btn-primary"),
			$replyBtn = $el.parent().find("[data-act='reply']");
		// 评论统计字数
		var wdc = new WordCounter($input, $display);
		// 提交回复
		var baseUrl = "<?php echo Yii::app()->urlManager->createUrl( 'message/comment/add' ); ?>";
		$submitBtn.on("click", function() {
			var actData = $.attr(this, "data-act-data"),
				param = Ibos.Util.urlToObj(actData);
			param.content = $input.val();
			// 为空时警告
			if (wdc.isEmpty() || wdc.isError()) {
				$input.blink();
				// 不为空时提交
			} else {
				Msg.submitReply(baseUrl, param, function(result) {
					Msg.toggleReply($replyBtn, $input);
					if (result.IsSuccess) {
						$.jGrowl('<?php echo $lang['Save succeed']; ?>',{theme: 'success'});
					} else {
						$.jGrowl(result.data,{theme: 'danger'});
					}
				});
			}
		});
		// 切换评论框显隐状态
		$replyBtn.on("click", function() {
			Msg.toggleReply($replyBtn, $input);
		});
		Ibos.atwho($input, {url: '<?php echo $this->createUrl( 'api/searchat' ); ?>'});
	});
</script>