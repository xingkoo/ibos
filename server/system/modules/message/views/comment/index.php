<!-- private css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="mc-header">
			<ul class="mnv nl clearfix">
				<li<?php if ( $type == 'receive' ): ?> class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'comment/index', array( 'type' => 'receive' ) ); ?>">
						<i class="o-msg-received"></i>
						<?php echo $lang['Receive']; ?>
					</a>
				</li>
				<li<?php if ( $type == 'sent' ): ?> class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'comment/index', array( 'type' => 'sent' ) ); ?>">
						<i class="o-msg-sent"></i>
						<?php echo $lang['Sent']; ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="page-list" id="msg_comment_list">
			<div class="page-list-header">
				<?php if ( Ibos::app()->user->isadministrator ): ?>
				<div class="pull-left msg-toolbar">
					<button type="button" class="btn" id="start_multiple_btn"><?php echo $lang['Batch delete']; ?></button>
				</div>
				<div class="pull-left msg-toolbar-multiple">
						<label class="checkbox btn"><input type="checkbox" data-name="comment"></label>
						<button type="button" class="btn btn-danger" data-action="removeComments"><?php echo $lang['Delete']; ?></button>
						<button type="button" class="btn" id="stop_multiple_btn"><?php echo $lang['Cancel']; ?></button>
				</div>
				<?php endif; ?>
				<!--<div class="search search-enter pull-right span3">
					<input type="text" placeholder="Search" data-toggle="search" id="mal_search">
					<a href="javascript:;">search</a>
				</div>-->
			</div>
			<div class="page-list-mainer">
				<?php if ( !empty( $list ) ): ?>
					<ul class="main-list msg-list msg-comment-list" id="">
						<?php foreach ( $list as $comment ): ?>
							<li class="main-list-item" id="comment_<?php echo $comment['cid']; ?>">
								<div class="avatar-box pull-left">
									<a href="<?php echo $comment['user_info']['space_url']; ?>" class="avatar-circle">
										<img class="mbm" src="<?php echo $comment['user_info']['avatar_middle']; ?>" />
									</a>
									<span class="avatar-desc"><strong><?php echo $comment['user_info']['realname']; ?></strong></span>
								</div>
								<div class="main-list-item-body">
									<div class="msg-box" id="msgbox_<?php echo $comment['cid']; ?>">
										<span class="msg-box-arrow"><i></i></span>
										<div class="msg-box-body">
											<p class="xcm mbm"><?php echo $comment['content']; ?></p>
											<p class="tcm mb">
												<?php //Todo::来源描述/*echo $comment['sourceInfo']['sourceDesc']*/ ?>
											</p>
											<div>
												<label class="checkbox checkbox-inline mbz">
													<input type="checkbox" name="comment" value="<?php echo $comment['cid']; ?>">
												</label>
												<span class="tcm fss"><?php echo ConvertUtil::formatDate( $comment['ctime'], 'u' ); ?></span>
												<div class="pull-right">
													<?php if ( $comment['isCommentDel'] ): ?><a href="javascript:;" data-action="removeComment" data-param='{"id": "<?php echo $comment['cid']; ?>"}' data-target="#comment_<?php echo $comment['cid']; ?>"><?php echo $lang['Delete']; ?></a><?php endif; ?>
													<!--<a href="javascript:;" data-act-data="id=<?php echo $comment['cid']; ?>&reply_name=<?php echo $comment['user_info']['realname']; ?>" data-act="reply" data-target="#msgbox_<?php echo $comment['cid']; ?>"><?php echo $lang['Reply']; ?></a>-->
												</div>
											</div>
										</div>
										<!--<div class="msg-box-reply">
											<span class="msg-box-reply-arrow"><i></i></span>
											<div class="mbs">
												<textarea rows="3"></textarea>
											</div>
											<div class="clearfix">
												<a href="#" class="cbtn o-expression"></a>
												<div class="pull-right">
													<span class="msg-word-count" data-count>140</span>
													<button type="button" data-act-data="type=reply&touid=<?php echo $comment['user_info']['uid']; ?>&rowid=<?php echo $comment['rowid']; ?>&table=comment&module=message" class="btn btn-small btn-primary"><?php echo $lang['Send']; ?></button>
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
				<?php $this->widget( 'IWPage', array( 'pages' => $pages ) ); ?>
			</div>
		</div>
		<!-- Mainer content -->
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script>
	(function() {
		var $msgCommentList = $("#msg_comment_list");
		function removeComments(ids) {
			$.get(Ibos.app.url('message/comment/del'), {cid: ids}, function(data) {
				if (data.isSuccess) {
					$.each(ids.split(','), function(n, i) {
						$('#comment_' + i).fadeOut(function() {
							$('#comment_' + i).remove();
						});
					});
					Ui.tip('<?php echo $lang['Del succeed']; ?>');
				} else {
					Ui.tip('<?php echo $lang['Del failed']; ?>', 'danger');
				}
			}, 'json');
		}

		Ibos.evt.add({
			// 删除
			"removeComment": function(param, elem){
				removeComments(param.id)
			},
			// 批量删除
			"removeComments": function(param, elem) {
				var ids = U.getCheckedValue("comment");
				if (ids) {
					removeComments(ids);
				} else {
					Ui.tip('<?php echo $lang['At least one action']; ?>', 'danger');
					return false;
				}	
			}
		})


		// @Todo: 对话历史
		/*$msgCommentList.find("[data-act='history']").each(function() {
		 var $el = $(this),
		 actData = $el.attr("data-act-data");
		 $el.ajaxPopover("test.html?" + actData)
		 });*/

		// 批量删除模式
		var multipleMode = Msg.multipleMode($msgCommentList);

		$("#start_multiple_btn").click(multipleMode.start);
		$("#stop_multiple_btn").click(multipleMode.stop);


		// 评论统计字数
		$msgCommentList.find(".msg-box-reply").each(function() {
			var $el = $(this),
					$input = $el.find("textarea"),
					$display = $el.find(".msg-word-count"),
					$submitBtn = $el.find(".btn-primary"),
					$replyBtn = $el.parent().find("[data-act='reply']");
			// 评论统计字数
			var wdc = new WordCounter($input, $display);
			// 提交回复
			$submitBtn.on("click", function() {
				var actData = $.attr(this, "data-act-data"),param = Ibos.Util.urlToObj(actData);
				param.content = $input.val();
				// 为空时警告
				if (wdc.isEmpty() || wdc.isError()) {
					$input.blink();
					// 不为空时提交
				} else {
					Msg.submitReply(Ibos.app.url('message/comment/add'), param, function(result) {
						Msg.toggleReply($replyBtn, $input);
						if (result.IsSuccess) {
							Ui.tip('<?php echo $lang['Save succeed']; ?>');
						} else {
							Ui.tip(result.data, 'danger');
						}
					});
				}
			});

			//回复
			$replyBtn.on("click", function() {
				Msg.toggleReply($replyBtn, $input);
			});
			Ibos.atwho($input, {url: Ibos.app.url( 'message/api/searchat' )});
		});
	})();
</script>
