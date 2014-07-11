<!-- private css -->
<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/introjs/introjs.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/wf_form.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/preview.css?<?php echo VERHASH;?>">
<form method="post" enctype="multipart/form-data" action="<?php echo $this->createUrl("form/index");?>" id="wf_handle_form">
    <div class="wrap host-wrap">
        <!--超时提醒-->
        <?php if (($flow["type"] == "1") && (($process["timeout"] * 3600) < $timeUsed)) : ?>
            <div class="alert alert-danger" id="delay_remind">
            	<?php echo Ibos::lang("Your work has overtimed", "", array("{time}" => WfCommonUtil::getTime($timeUsed - ($process["timeout"] * 3600))));?>
			</div>
        <?php endif; ?>
        <!--表单区域begin-->
        <div class="application-form">
            <a href="#" name="application" class="wf-anchor"></a>
            <div class="mc-top clearfix">
                <div class="pull-left">
                    <span class="form-title"><i class="o-host-application"></i><?php echo $run["name"];?></span>
                </div>
                <div class="pull-right func-bar">
                    <ul class="list-inline">
                        <li>
                            <a href="javascript:void(0);" id="steps_preview" data-click="preview" data-param="{&quot;key&quot;:&quot;<?php echo $this->key;?>&quot;}" title="流程图"><i class="o-host-preview"></i></a>
                        </li>
                        <li>
                            <a href="<?php echo $this->createUrl("preview/print", array("key" => $this->key));?>" target="_blank" title="<?php echo $lang["Print"];?>"><i class="o-host-print"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="form-area">
                <div class="clearfix">
                    <span class="pull-left corner-1"></span>
                    <span class="pull-right corner-2"></span>
                </div>
                <div class="form-content grid-container">
                    <?php echo $model;?>
                </div>
                <span class="pull-left corner-3"></span>
                <span class="pull-right corner-4"></span>
            </div>
        </div>
        <!--表单区域end-->
        <?php if ($allowAttach) : ?>
            <div class="attachment-area" id="attachment_area">
                <a href="#" name="attachment" class="wf-anchor"></a>
                <span class="attachment-title"><i class="o-host-attachment"></i><?php echo $lang["Global attach area"];?></span>
                <div class="page-list attachment-list">
                    <div class="page-list-header">
                        <button type="button" class="btn btn-primary" id="global_upload_btn"></button>
                        <div id='g_file_target'></div>
                    </div>
                    <?php if (isset($attachData) && !empty($attachData)) : ?>
                        <div class="page-list-mainer attachment-mainer">
                            <div class="">
                                <table class="table attachment-table">
                                    <thead>
                                        <tr>
                                            <th width="42"></th>
                                            <th width="140"><?php $lang["Filename"];?></th>
                                            <th width="80"><?php $lang["Size"];?></th>
                                            <th width="90"><?php $lang["Uploader"];?></th>
                                            <th width="200"><?php $lang["Steps"];?></th>
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
                                                        <em>
                                                        	<?php if ($attach["down"]) :?>
                                                        		<a target='_blank' href="<?php echo $attach["downurl"];?>"><?php echo $attach["filename"];?></a>
                                                        	<?php else : ?>
																<?php echo $attach["filename"];?>
															<?php endif; ?>
														</em>
                                                        <span class="save-time"><?php echo $attach["date"];?></span>
                                                    </div>
                                                </td>
                                                <td><span class="attachment-size"><?php echo $attach["filesize"];?></span></td>
                                                <td><img src="<?php echo $guser["avatar_small"];?>" class="avatar avatar-small" width="30" height="30"><span class="uploader"><?php echo $guser["realname"];?></span></td>
                                                <td>
                                                    <span class="label attac-label"><?php echo $attach["description"];?></span>
                                                    <span class="attachment-content">
                                                    	<?php $flow["type"] == "1" ? $prcscache[$attach["description"]]["name"] : Ibos::lang("No.step", "", array("{step}" => $attach["description"]));?>
													</span>
                                                </td>
                                                <td>
                                                    <div class="pull-right">
                                                        <ul class="list-inline">
                                                            <?php if (isset($attach["officereadurl"])) : ?>
                                                                <li>
                                                                    <a href="javascript:;" class="o-host-read" data-action="viewOfficeFile" data-param='{"href": "<?php echo $attach["officereadurl"];?>"}' title="阅读"></a>
                                                                </li>
                                                            <?php endif; ?>
                                                            <?php if (isset($attach["officeediturl"])) : ?>
																<li><a href="<?php echo $attach["officeediturl"];?>" target="_blank" class="o-edit cbtn" title="编辑"></a></li>
															<?php endif; ?>
                                                            <?php if ($attach["down"] == 1) : ?>
																<li><a href="<?php echo $attach["downurl"];?>" target='_blank' class="o-host-download" title="下载"></a></li>
															<?php endif; ?>
															<?php if ($attach["delete"] == 1) : ?>
																<li><a href="javascript:void(0);" data-click='delAttach' data-param="{&quot;runid&quot;:&quot;<?php echo $run["runid"];?>&quot;,&quot;aid&quot;:&quot;<?php echo $attach["aid"];?>&quot;}" class="o-trash cbtn" title="删除"></a></li>
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
        <div class="opinion-area">
            <a href="#" name="opinion" class="wf-anchor"></a>
            <span class="opinion-title"><i class="o-host-opinion"></i><?php echo $lang["Form sign comments"];?></span>
            <div class="opinion-content">
                <ul class="option-ul clearfix">
                    <?php if (!empty($feedback)) : ?>
                    	<?php foreach ($feedback as $fb ) : ?>
                            <li class="clearfix" id="fb_<?php echo $fb["feedid"];?>">
                                <div class="opinion-type pull-left">
                                    <div class="type-number"><?php echo $fb["flowprocess"];?></div>
                                    <div class="type-content"><?php echo $fb["name"];</div>
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
                                                <span class="approver"><?php echo fb["user"]["realname"];?>:</span>
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
																		<a class="attachment-upload" target="_blank" href="<?php echo $attach["downurl"];?>"><?php echo $lang["Download"];</a>&nbsp;
																	<?php endif; ?>

																	<?php if (isset($attach["officereadurl"])) : ?>
                                                                        <a  href="javascript:;" class="attachment-upload" data-action="viewOfficeFile" data-param='{"href": "<?php echo $attach["officereadurl"];?>"}'>
                                                                            <?php echo $lang["Read"];?>
                                                                        </a>
                                                                    <?php endif; ?>

                                                                   	<?php if (isset($attach["officeediturl"]) && ($attach["uid"] == $this->uid)) : ?>
																		<a class="attachment-upload" target="_blank" href="<?php echo $attach["officeediturl"];?>"><?php echo $lang["Edit"];?></a>
																	<?php endif; ?>

																	<?php if ($attach["uid"] == $this->uid) : ?>
																		<a class="attachment-upload" data-click="delFbAttach" data-param="{&quot;feedid&quot;:&quot;<?php echo $fb["feedid"];?>&quot;,&quot;aid&quot;:&quot;<?php echo $attach["aid"];?>&quot;}" href="javascript:void(0);"><?php echo $lang["Delete"];?></a>
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
                                                                
                                                            <?php if ($allowFeedback) : ?>
																<li><a href="javascript:;" data-param='{&quot;feedid&quot;:&quot;<?php echo $fb["feedid"];?>&quot;}' data-click="loadFbReply" class="reply-option"><?php echo $lang["Reply"];?>(<?php echo $fb["count"];?>)</a></li>
															<?php endif; ?>

															<?php if ($fb["uid"] == $this->uid) : ?>
																&nbsp;|<li><a href="javascript:void(0);" data-click="delFb" data-param="{&quot;feedid&quot;:&quot;<?php echo $fb["feedid"];?>&quot;}"><?php echo $lang["Delete"];?></a></li>
															<?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="well well-small well-lightblue reply-list">
                                            <?php if ($processid == $fb["processid"]) : ?>
                                                <textarea class="mbs reply"></textarea>
                                                <div class="clearfix mbs">
                                                    <button type="button" data-click="reply" data-loading-text="<?php echo $lang["Reply ing"];?>..."
                                                            data-param='{&quot;replyid&quot;:&quot;<?php echo $fb["feedid"];?>&quot;,&quot;key&quot;:&quot;<?php echo $this->key;?>&quot;}' class="btn btn-primary btn-small pull-right"><?php echo $lang["Reply"];?></button>
                                                </div>
                                            <?php endif; ?>
                                            <ul class="cmt-sub"></ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        暂无会签意见
                    <?php endif; ?>

                    <?php if ($allowFeedback) : ?>
                        <li class="clearfix">
                            <div class="opinion-type pull-left">
                                <div class="type-number active-type"><?php echo $processid;?></div>
                                <div class="type-content"><?php echo $lang["Cur step"];?>
	                                <?php if (($flow["type"] == "1") && ($process["feedback"] == "2")) :?>
	                                	<span class="xcr"><?php echo $lang["Force sign"];?></span>
	                                <?php endif; ?>
								</div>
                            </div>
                            <div class="opinion-block clearfix">
                                <div data-toggle="usercard" data-param="uid=<?php echo Ibos::app()->user->uid;?>">
                                    <span class="avatar-circle pull-left">
                                        <img src="<?php echo Ibos::app()->user->avatar_middle;?>">
                                    </span>
                                </div>
                                <div class="opinion-form pull-left">
                                    <textarea rows="4" name="content" cols="108"></textarea>
                                    <div id="fb_file_target"></div>
                                    <div class="opinion-form-foot">
                                        <div class="pull-left">
                                            <?php if ($allowAttach) : ?>
                                                <button id="fb_upload_btn" type="button" class="btn btn-primary host-attachment"><i class="o-attachment-btn"></i></button>
                                            <?php endif; ?>
    <!--<label class="checkbox support-type"  id="support_type" data-toggle="tooltip" data-placement="top" data-html="true" data-original-title="仅支持<span class='support-support'>IE浏览器</span>">
    <input class="test" id="IE_Check" disabled="disabled" type="checkbox">
    <span class="support-tip">启用会签手写签章功能(试用版<span class="test-password">密码:111</span>,购买请联系我们.)</span>
    </label>-->
                                        </div>
                                        <!--<div class="pull-right">
                                            <button type="button" class="btn">";
	$lang["Handwritten"];
	"</button>
                                            <button type="button" class="btn">";
	$lang["Signature"];
	"</button>
                                        </div>-->
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
               </ul>
            </div>
        </div>
        <div class="wf-quick-link">
            <ul>
                <li><a href="#application" class="o-sb-form" title="<?php echo $lang["Form"];?>"></a></li>
                <?php if ($allowAttach) : ?>
					<li><a href="#attachment" class="o-sb-attachment" title="<?php echo $lang["Attachment"];?>"></a></li>
				<?php endif; ?>

				<?php if ($allowFeedback) : ?>
					<li><a href="#opinion" class="o-sb-sign" title="<?php echo $lang["Sign"];?>"></a></li>
				<?php endif; ?>

                <li><a href="javascript:Ui.scrollToTop();" class="o-sb-top" id="to_top" title="<?php echo $lang["Back to the top"];?>"></a></li>
            </ul>
        </div>

        <?php if (isset($backlist)) : ?>
            <div id="fallback_box" style="display: none;">
                <div>
                    <?php foreach ($backlist as $index => $backstep ) : ?>
                        <label class="radio "><input type="radio" <?php if ($index == 0) : ?>checked<?php endif; ?> name="prcs" value="<?php echo $backstep["id"];?>"/><?php echo $backstep["id"];?>:<?php echo $backstep["name"];?></label>
                    <?php endforeach; ?>
                </div>
                <div><input type="text" id="fallback_msg" placeholder="请输入回退原因" /></div>
            </div>
        <?php endif; ?>
    </div>
    <div class="host-footer">
        <div class="footer-bg"></div>
        <div class="footer-area">
            <div class="pull-left">
                <?php if (($run["parentrun"] !== "0") || ($this->processid !== "1") || !isset($this->autonew)) : ?>
                    <button type="button" data-click="return" class="btn"><?php echo $lang["Return"];?></button>
                <?php else : ?>
                    <button type="button" data-click="cancel" class="btn"><?php echo $lang["Form cancel"];?></button>
                <?php endif; ?>
            </div>
            <div class="pull-right">
                <?php if (isset($defaultEnd) || isset($otherEnd)) : ?>
                    <button type="button" data-click="end" class="btn"><?php echo $lang["Form endflow"];?></button>
                <?php endif; ?>

                <?php if ($rp["opflag"]) : ?>
                    <button type="button" data-click="save" class="btn"><?php echo $lang["Form save form"];?></button>
                <?php endif; ?>

                <?php if ($rp["opflag"] == "1") : ?>
					<?php if ($allowBack && ($this->flowprocess !== "1")) : ?>
                        <button type="button" id="fallback" data-click="fallback" data-param="{&quot;type&quot;:&quot;<?php echo $process["allowback"];?>&quot;}" class="btn"><?php echo $lang["Fall back"];?></button>
                    <?php endif; ?>
                    <button type="button" id="turn" data-click="turn" class="btn btn-primary"><?php echo $lang["Pass to the next step"];?></button>
                <?php else : ?>
                    <button type="button" data-click="finish" class="btn"><?php echo $lang["Form finish"];?></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="key" value="<?php echo $this->key;?>" />
    <input type="hidden" name="hidden" value="<?php echo $hidden;?>" />
    <input type="hidden" name="readonly" value="<?php echo $readonly;?>" />
    <input type="hidden" name="attachmentid" id='attachmentid' value="<?php echo $run["attachmentid"];?>" />
    <input type="hidden" name="fbattachmentid" id='fbattachmentid' value="" />
    <input type="hidden" name="topflag" value="<?php echo $rp["topflag"];?>" />
    <input type="hidden" name="saveflag" />
    <input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
</form>
<style type="text/css">
    #footer{
        display: none;
    }
</style>
<script src='<?php echo STATICURL;?>/js/lib/qrcode/jquery.qrcode.min.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/introjs/intro.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/wfcomponents.js?<?php echo VERHASH;?>'></script>
<script>
    var params = {
        form: $('#wf_handle_form'),
        runID: '<?php echo $this->runid;?>',
        processID: '<?php echo $this->processid;?>',
        flowID: '<?php echo $this->flowid;?>',
        flowProcess: '<?php echo $this->flowprocess;?>',
        processItem: '<?php echo $flow["type"] == "1" ? $process["processitem"] : "";?>',
        topflag: '<?php echo $rp["topflag"];?>',
        autoNew: '<?php echo isset($this->autonew) ? 1 : 0;?>',
        timeout: '<?php echo $flow["type"] == "1" ? $process["timeout"] : "";?>',
        flowType: '<?php echo $flow["type"];?>',
        timeUsed: '<?php echo $timeUsed;?>',
        checkItem: '<?php echo $checkItem;?>',
        fbSigned: '<?php echo $fbSigned;?>',
        swfHash: '<?php echo $uploadConfig["hash"];?>',
        fileLimit: '<?php echo $uploadConfig["max"];?>',
        fileTypes: '<?php echo $uploadConfig["attachexts"]["ext"];?>',
        fileTypesDescription: '<?php echo $uploadConfig["attachexts"]["depict"];?>',
        uid: '<?php echo $this->uid;?>',
        feedback: '<?php echo $flow["type"] == "1" ? $process["feedback"] : "";?>',
        key: '<?php echo $this->key;?>'
    };
    Ibos.app.setPageParam(params);
    var wfc = new wfComponents(params.form);
    wfc.initItem();
</script>
<script src="<?php echo $assetUrl;?>/js/wfhandler.js?<?php echo VERHASH;?>"></script>
<script>
    // 用户自定义脚本
    <?php if (!empty($form["script"])) : ?>
        try {
        	<?php echo $form["script"];?>
        } catch (err) {
            Ui.tip(U.lang('WF.CUSTOM_SCRIPT_ERROR') + err);
        }
    <?php endif; ?>


    $(function() {
        //当公共附件区有附件时，附件表单的头部分割线显示
        var dataLength = $(".attachment-mainer", "#attachment_area").children().length;
        if (dataLength > 0) {
            $(".page-list-header", "#attachment_area").css({"border-bottom": "2px solid #3497db"});
        }

        // 新手引导
        setTimeout(function() {
            Ibos.app.guide("wf_form_index", function() {
                var guideData = [
                    {
                        element: "#steps_preview",
                        intro: U.lang("INTRO.WF_FORM_INDEX.STEPS_PREVIEW"),
                        position: "left"
                    },
                    {
                        element: "#turn",
                        intro: U.lang("INTRO.WF_FORM_INDEX.TURN"),
                        position: "top"
                    }
                ];
                if (document.getElementById("fallback")) {
                    guideData.push({
                        element: "#fallback",
                        intro: U.lang("INTRO.WF_FORM_INDEX.FALLBACK"),
                        position: "top"
                    })
                }

                var intro = Ibos.intro(guideData, function() {
                    Ibos.app.finishGuide('wf_form_index');
                });
                intro.onchange(function(elem) {
                    // 因为“下一步”、“回退”在fixed定位的节点中，所以在发生滚动后，要重新计算位置
                    if (elem.id == "turn" || elem.id == "fallback") {
                        setTimeout(function() {
                            intro.refresh();
                        }, 0)
                    }
                })
            });
        }, 1000)
    })
</script>
