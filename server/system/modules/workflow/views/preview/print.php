<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/introjs/introjs.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/wf_form.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/preview.css?<?php echo VERHASH;?>" />
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/wf_preview_print.css?<?php echo VERHASH;?>">

<div class="wrap">
	<!--头部功能栏 开始-->
	<div class="rolling-bar-wrap">
		<div class="rolling-bar">
			<div class="bar-bg"></div>
			<div class="function-bar clearfix">
				<div class="pull-left">
					<a href="<?php echo $this->createUrl("list/index");?>" class="btn"><?php echo $lang["Return"];?></a>
					<!--<div class="btn-group">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<i class="o-view-transpond"></i>转发到<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#">新闻</a></li>
							<li><a href="#">公文</a></li>
							<li><a href="#">邮件</a></li>
						</ul>
					</div>
					<div class="btn-group">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<i class="o-view-export"></i>导出到<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#">WORD文档</a></li>
							<li><a href="#">HTML文档</a></li>
						</ul>
					</div>-->
					<button type="button" class="btn" onclick="window.print();"><i class="o-view-print"></i>打印</button>
				</div>
				<div class="pull-right" id="choose_display">
					<span class="choose-template">选择显示板块</span>
					<span class="view-application" id="view-application"><a href="javascript:void(0);" data-id="1" <?php if ($formview) : ?>data-check="1" style="background-position: 0px -20px;"<?php else : ?>style="background-position: -61px -20px;"<?php endif; ?> class="o-view-application" title="表单"></a></span>
					<span class="view-attachment" id="view-attachment"><a href="javascript:void(0);" data-id="2" <?php if ($attachview) : ?>data-check="2" style="background-position: 0px -180px;"<?php else : ?>style="background-position: -100px -20px;"<?php endif; ?> class="o-view-attachment" title="附件"></a></span>
					<span class="view-opinion" id="view-opinion"><a href="javascript:void(0);" data-id="3" <?php if ($signview) : ?>data-check="3" style="background-position: -20px -180px;"<?php else : ?>style="background-position: -141px -20px;"<?php endif; ?> class="o-view-opinion" title="会签"></a></span>
					<span class="view-chart" id="view-chart"><a href="javascript:void(0);" data-id="4" <?php if ($chartview) : ?>data-check="4" style="background-position: -160px -20px;"<?php else : ?>style="background-position: -181px -20px;"<?php endif; ?> class="o-view-chart" title="流程图"></a></span>          
				</div>
			</div>
		</div>
	</div>
	<!--头部功能栏 结束-->
	<!--表单区域 开始-->
	<?php if ($formview) : ?>
		<div class="application-form" id="application_form">
			<div class="mc-top clearfix">
				<div class="pull-left">
					<span class="form-title"><i class="o-host-application"></i><?php echo $runname;?></span>
				</div>
			</div>
			<div class="form-area">
				<div class="clearfix">
					<span class="pull-left corner-1"></span>
					<span class="pull-right corner-2"></span>
				</div>
				<div class="form-content grid-container" id="form_container">
					<?php echo $model;?>
				</div>
				<span class="pull-left corner-3"></span>
				<span class="pull-right corner-4"></span>
			</div>
		</div>
	<?php endif; ?>
	<!--表单区域 结束-->
	<!--公共附件区 开始-->
	<?php if ($attachview) : ?>
		<div class="attachment-area" id="attachment_area">
			<div class="page-list attachment-list">
				<div class="page-list-header">
					<span class="attachment-caption"><i class="o-host-attachment"></i><?php echo $lang["Global attach area"];?></span>
				</div>
				<?php if (isset($attachData) && !empty($attachData)) : ?>
					<div class="page-list-mainer attachment-mainer">
						<div class="">
							<table class="table attachment-table">
								<thead>
									<tr>
										<th width="42"></th>
										<th width="140"><?php echo $lang["Filename"];?></th>
										<th width="80"><?php echo $lang["Size"];?></th>
										<th width="90"><?php echo $lang["Uploader"];?></th>
										<th width="200"><?php echo $lang["Steps"];?></th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($attachData as $attach ) : ?>
										<?php $guser = User::model()->fetchByUid($attach["uid"]);?>
										<tr id='attach_<?php echo $attach["aid"];?>'>
											<td><img src="<?php echo $attach["iconsmall"];?>" alt="<?php echo $attach["filename"];?>" /></td>
											<td>
												<div class="attachment-name">
													<em><?php if ($attach["down"]) : ?><a target='_blank' href="<?php echo $attach["downurl"];?>"><?php echo $attach["filename"];?></a><?php else : ?><?php echo $attach["filename"];?><?php endif; ?></em>
													<span class="save-time"><?php echo $attach["date"];?></span>
												</div>
											</td>
											<td><span class="attachment-size"><?php echo $attach["filesize"];?></span></td>
											<td><img src="<?php echo $guser["avatar_small"];?>" class="avatar avatar-small" width="30" height="30"><span class="uploader"><?php echo $guser["realname"];?></span></td>
											<td>
												<span class="label attac-label"><?php echo $attach["description"];?></span>
												<span class="attachment-content"><?php echo $prcscache[$attach["description"]]["name"];?></span>
											</td>
											<td>
												<div class="pull-right">
													<ul class="list-inline">
														<?php if ($attach["down"] == 1) : ?>
															<li><a href="<?php echo $attach["downurl"];?>" target='_blank' class="o-host-download" title="<?php echo $lang["Download"];?>"></a></li>
														<?php endif; ?>

														<?php if (isset($attach["officereadurl"])) : ?>
                                                            <li>
                                                                <a href="javascript:;" class="o-host-read" data-action="viewOfficeFile" data-param='{"href": "<?php echo $attach["officereadurl"];?>"}' title="<?php echo $lang["Read"];?>"></a>
                                                            </li>
                                                        <?php endif; ?>
													</ul>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<!--公共附件区 结束-->
	<?php if ($signview) : ?>
		<!--会签意见区 开始-->
		<div class="opinion-area" id="opinion_area">
			<span class="opinion-title"><i class="o-host-opinion"></i><?php echo $lang["Form sign comments"];?></span>
			<div class="opinion-content">
				<ul class="option-ul clearfix">
					<?php if (!empty($feedback)) : ?>
						<?php foreach ($feedback as $fb ) : ?>
							<li class="clearfix" id="fb_<?php echo $fb["feedid"];?>">
								<div class="opinion-type pull-left">
									<div class="type-number"><?php echo $fb["flowprocess"];?></div>
									<div class="type-content"><?php echo $fb["name"];?></div>
								</div>
								<div class="opinion-text pull-left">
									<div data-toggle="usercard" data-param="uid=<?php echo $fb["user"]["uid"];?>">
										<span class="avatar-circle pull-left">
											<img src="<?php echo $fb["user"]["avatar_middle"];?>">
										</span>
									</div>
									<div class="opinion-body">
										<ul class="opinion-list">
											<li>
												<span class="approver"><?php echo $fb["user"]["realname"];?>:</span>
												<span class="approve-content"><?php echo $fb["content"];?></span>
											</li>
											<?php if (!empty($fb["attachment"])) : ?>
												<li>
													<?php foreach ($fb["attachment"] as $attach ) : ?>
														<div>
															<img src="<?php echo $attach["iconsmall"];?>" alt="<?php echo $attach["filename"];?>" class="pull-left">
															<div class="type-attachment" class="pull-right">
																<div>
																	<span><?php echo $attach["filename"];?></span>
																	<span class="attachment-size">(<?php echo $attach["filesize"];?>)</span>
																</div>
																<span>
																	<?php if ($attach["down"]) : ?>
																	<a class="attachment-upload" target="_blank" href="<?php echo $attach["downurl"];?>"><?php echo $lang["Download"];?></a>&nbsp;
																	<?php endif; ?>

																	<?php if (isset($attach["officereadurl"])) : ?>
                                                                        <a  href="javascript:;" class="attachment-upload" data-action="viewOfficeFile" data-param='{"href": "<?php echo $attach["officereadurl"];?>"}'>
                                                                            <?php echo $lang["Read"];?>
                                                                        </a>
                                                                    <?php endif; ?>
																</span>
															</div>
														</div>
													<?php endforeach; ?>
												</li>
											<?php endif; ?>
											<li>
												<div class="clearfix">
													<div class="pull-left approve-time"><?php echo $fb["edittime"];?></div>
													<div class="pull-right approve-handle" id="leader_reply">
														<ul class="list-inline">
															<?php if ($fb["signdata"] !== "") : ?>
																<li><a href=""><?php echo $lang["Check the stamp"];?></a></li>&nbsp;|
															<?php endif; ?>
														</ul>
													</div>
												</div>
											</li>
										</ul>
										<?php if (isset($fb["reply"])) : ?>
											<div class="well well-small well-lightblue">
												<ul class="cmt-sub">
													<?php foreach ($fb["reply"] as $rp ) : ?>
														<li class="cmt-item">
															<div class="avatar-box pull-left">
																<a href="<?php echo $rp["user"]["space_url"];?>" class="avatar">
																	<img src="<?php echo $rp["user"]["avatar_middle"];?>" class="avatar avatar-small" width="30" height="30">
																</a>
															</div>
															<div class="cmt-body fss">
																<p class="xcm">
																	<a href="<?php echo $rp["user"]["space_url"];?>" class="anchor"><?php echo $rp["user"]["realname"];?>：</a>
																	<?php echo $rp["content"];?>
																	<span class="tcm ilsep">(<?php echo ConvertUtil::formatDate($rp["edittime"], "u");?>)</span>
																</p>
															</div>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	<!--会签意见区 结束-->
	<!--流程图部分 开始-->
		<div class="flow-chart" id="flow_chart">
	<?php if ($chartview) : ?>
			<span class="flow-title"><i class="o-flow-chart"></i><?php echo $lang["Flow chart"];?></span>
			<div class="flow-content">
				<div class="type-prompts">
					<ul>
						<li><span class="step-type step-going"></span><?php echo $lang["Perform"];?></li>
						<li><span class="step-type step-complete"></span><?php echo $lang["Form finish"];?></li>
						<li><span class="step-type step-child"></span><?php echo $lang["Subflow"];?></li>
						<li><span class="step-type step-noreceive"></span><?php echo $lang["Not receive"];?></li>
						<li><span class="step-type step-hanging"></span><?php echo $lang["In delay"];?></li>
					</ul>
				</div>
				<!--放置流程图容器-->
				<div class="chart-area">
					<div class="wf-designer-view scroll" style="position: relative;min-height: 600px;overflow: auto;" id="wf_designer_canvas">
					</div>
				</div>
			</div>
	<?php endif; ?>
		<!--流程图部分 结束-->
	</div>
	<div class="wf-quick-link">
		<ul>
			<?php if ($formview) : ?>
				<li><a href="javascript:;" data-action="scrollToForm" class="o-sb-form" title="<?php echo $lang["Form"];?>"></a></li>
			<?php endif; ?>

			<?php if ($attachview) : ?>
				<li><a href="javascript:;" data-action="scrollToAttachment" class="o-sb-attachment" title="<?php echo $lang["Attachment"];?>"></a></li>
			<?php endif; ?>

			<?php if ($signview) : ?>
				<li><a href="javascript:;" data-action="scrollToOpinion" class="o-sb-sign" title="<?php echo $lang["Sign"];?>"></a></li>
			<?php endif; ?>

			<?php if ($chartview) : ?>
				<li><a href="javascript:;" data-action="scrollToChart" class="o-sb-chart" title="<?php echo $lang["Flow chart"];?>"></a></li>
			<?php endif; ?>
			<li><a href="javascript:;" data-action="scrollToTop" class="o-sb-top" id="to_top" title="<?php echo $lang["Back to the top"];?>"></a></li>
		</ul>
	</div>
	<script src="<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>"></script>
	<script src='<?php echo STATICURL;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo STATICURL;?>/js/lib/introjs/intro.js?<?php echo VERHASH;?>'></script>
	<script src='<?php echo $assetUrl;?>/js/wfcomponents.js?<?php echo VERHASH;?>'></script>
	<?php if ($chartview) : ?>
		<script src='<?php echo STATICURL;?>/js/lib/jsPlumb/jquery.jsPlumb-1.5.3.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/wfdesigner.js?<?php echo VERHASH;?>'></script>
		<script src='<?php echo $assetUrl;?>/js/processView.js?<?php echo VERHASH;?>'></script>
	<?php endif; ?>
	<script>
		$(function() {
			<?php if ($formview) : ?>
							// 初始化组件 (如果有)
							var wfc = new wfComponents($('#form_container'));
							wfc.initItem();
			<?php endif; ?>

			<?php if ($chartview) : ?>
							$(function() {
								var url = Ibos.app.url('workflow/preview/getprcs', {flowid: <?php echo $flowid;?>, runid: <?php echo $runid;?>});
								$.get(url, function(data) {
									processView.set(data);
								}, 'json');
							});
			<?php endif; ?>
			$(".rolling-bar").affix({
				offset: { top: 70 }
			})

			function getView() {
				var view = "";
				$('[data-check]').each(function(i, n) {
					view += $(this).data('check');
				});
				return view;
			}

			$('#choose_display span').on('click', function() {
				var target = $(this).find('a');
				if (target.data('check') > 0) {
					target.removeAttr('data-check');
				} else {
					target.attr('data-check', target.data('id'));
				}
				var view = getView();
				location.href = Ibos.app.url('workflow/preview/print', {key: '<?php echo $key;?>', view: view});
			});
			Ibos.evt.add({
				"scrollToForm": function(){ 
					Ui.scrollYTo("application_form", -70);
				},
				"scrollToAttachment": function(){
					Ui.scrollYTo("attachment_area", -70);
				},
				"scrollToOpinion": function(){
					Ui.scrollYTo("opinion_area", -70);
				},
				"scrollToChart": function(){
					Ui.scrollYTo("flow_chart", -70);
				},
				"scrollToTop": function(){
					Ui.scrollToTop();
				}
			});

			//当公共附件区有附件时，附件表单的头部分割线显示
			var dataLength = $(".attachment-mainer", "#attachment_area").children().length;
			if(dataLength > 0){
				$(".page-list-header", "#attachment_area").css({"border-bottom":"2px solid #3497db"});
			}

			// 新手引导
			setTimeout(function(){
				Ibos.app.guide("wf_pre_print", function() {
					var guideData = [
						{ 
							element: "#choose_display", 
							intro: U.lang("INTRO.WF_PRE_PRINT.DISPLAY_PANEL")
						},
						{
							element: ".wf-quick-link",
							intro: U.lang("INTRO.WF_PRE_PRINT.QUICK_LINK"),
							position: "left"
						}
					];
					Ibos.intro(guideData, function(){
						Ibos.app.finishGuide('wf_pre_print');
					});
				})
			}, 1000)
		});
	</script>
</div>