<!doctype html>
<html lang="en">
    <head>
        <meta charset="<?php echo CHARSET;?>">
        <title><?php echo $lang["Workflow process viewer"];?></title>
        <!-- load css -->
        <link rel="stylesheet" href="<?php echo STATICURL;?>/css/base.css?<?php echo VERHASH;?>">
        <link rel="stylesheet" href="<?php echo STATICURL;?>/css/common.css?<?php echo VERHASH;?>">
        <link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>" />
        <!-- IE8 fixed -->
        <!--[if lt IE 9]>
            <link rel="stylesheet" href="<?php echo STATICURL;?>/css/iefix.css?<?php echo VERHASH;?>">
        <![endif]-->
        <!-- private css -->
        <link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH;?>">
        <link rel="stylesheet" href='<?php echo STATICURL;?>/js/lib/Select2/select2.css?<?php echo VERHASH;?>' />
        <link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
    </head>
    <body class="wf-designer wf-designer-view ibbody">
        <div class="wf-designer-header">
            <h1><?php echo $run["name"];?>，<?php echo $lang["Serial number"] . ":" . $run["runid"];?></h1>
            <a href="javascript:void(0);" title="<?php echo $lang["Print flow chart"];?>" class="cbtn o-print-bk" data-click="printProcess"></a>
            <a href="javascript:void(0);" title="<?php echo $lang["Closed viewer"];?>" class="cbtn o-close-bk" data-click="closeDesinger"></a>
        </div>
        <div class="wf-designer-body">
            <div class="wf-designer-mainer">
                <div class="alert" id="wf_alert">
                    <i class="bulb"></i>
                    <strong class="ilsep"><?php echo $lang["Color identification"];?></strong>
                    <span class="ilsep">
                        <i class="lump-processing"></i>
                        <?php echo $lang["Perform"];?>
                    </span>
                    <span class="ilsep">
                        <i class="lump-finish"></i>
                        <?php echo $lang["Form finish"];?>
                    </span>
                    <span class="ilsep">
                        <i class="lump-sub"></i>
                        <?php echo $lang["Subflow"];?>
                    </span>
                    <span class="ilsep">
                        <i class="lump-inactive"></i>
                        <?php echo $lang["Not receive"];?>
                    </span>
                    <span class="ilsep">
                        <i class="lump-suspend"></i>
                        <?php echo $lang["In delay"];?>
                    </span>
                    <span><?php echo $lang["Subflow desc"];?></span>
                </div>
                <div class="wf-designer-canvas wf-designer-view" id="wf_designer_canvas">
                </div>
            </div>
            <div class="wf-designer-sidebar" id="designer_sidebar">
                <div class="fill-zn">
                    <h5 class="xwb"><?php echo $lang["Current progress"];?></h5>
                    <div>
                        <?php foreach ($fl as $key => $val ) {
                            <div class="well well-white media" <?php if ($val["flag"] == "2") echo 'processing';?>">
                                <span class="label <?php if ($val["flag"] == "2") : ?>label-warning<?php else : ?>label-inverse<?php endif; ?> pull-left"><?php echo $key;?></span>
                                <div class="media-body">
                                    <div class="media-heading">
                                        <?php if (!isset($val["name"])) : ?>
                                        	<?php echo Ibos::lang("No.step", "", array("{step}" => $key));?>
                                        <?php else : ?>
                                        	[<?php echo $val["name"];?>]
                                        <?php endif; ?>
 
                                        <?php if (!empty($val["opuser"])) : ?>
											<?php echo $lang["From"];?>
										<?php else : ?>
											[<?php echo $val["opuser"];?>] <?php echo $lang["Host work"];?>
										<?php endif; ?>
 
                                        <?php if ($val["flag"] == "2") : ?>
                                        	<strong class="fss lhl xco "><?php echo $lang["Perform"];?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($val["flag"] == "3") || ($val["flag"] == "4")) : ?>
                                        <p class="fss lhl"><?php echo $lang["Used time"];?>：<?php echo $val["timestr"];?></p>
                                        <p class="fss lhl"><?php echo $lang["Begin"];?>：<?php echo $val["processtime"];?></p>
                                        <?php if (!empty($val["delivertime"])) : ?>
                                            <p class="fss lhl"><?php echo $lang["End"];?>：<?php echo $val["delivertime"];?></p>
                                        <?php endif; ?>
                                    <?php elseif ($val["flag"] == "2") : ?>
                                        <p class="fss lhl"><?php echo $lang["Continuous"];?>：<?php echo $val["timestr"];?>
                                        <?php if ($val["timeoutflag"]) : ?>
											<?php echo $lang["Timelimit"] . $val["timeout"] . $lang["Hour"];?>,<?php echo $lang["Timeout"] . ":" . $val["timeused"];?>
										<?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($val["redo"]) : ?>
                                        <p class="fss lhl"><a href="javascript:void(0);" data-click="redo" data-param="{&quot;key&quot;: &quot;<?php echo $key;?>&quot;,&quot;uid&quot;: &quot;<?php echo $val["prcsuid"];?>&quot;}" class="btn btn-small"><?php echo $lang["Redo"];?></a></p>
                                    <?php endif; ?>
                                    <?php if ($val["log"]) : ?>
                                        <p class="fss lhl xcbu"><?php echo $val["log"]["content"];?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset($fl[$key + 1])) : ?>
                                <div class="process-symbol"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if (!empty($remindUid)) : ?>
                    <div class="fill">
                        <div id="confirm_remind" style="width: 450px;display: none;">
                            <table class="table table-condensed" style="margin-bottom: 0;">
                                <tr>
                                    <th style="width: 80px;"><?php echo $lang["Recipient"];?></th>
                                    <td><?php echo User::model()->fetchRealnamesByUids($remindUid);?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $lang["Remind content"];?></th>
                                    <td>
                                        <input class="input input-small" type="text" id="message" name="message" value="<?php echo $lang["Remind content example"];?>" />
                                        <input type="hidden" id="toid" value="<?php echo implode(",", $remindUid);?>" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <button type="button" id="todo_remind_btn" class="btn btn-block btn-large btn-warning" data-click="overtimeRemind"><?php echo $lang["Timeout remind"];?></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <script src="<?php echo STATICURL;?>/js/src/core.js?<?php echo VERHASH;?>"></script>
        <script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
        <script src='<?php echo STATICURL;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
        <script src='<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>'></script>
        <script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>

        <script src='<?php echo STATICURL;?>/js/lib/jsPlumb/jquery.jsPlumb-1.5.3.js?<?php echo VERHASH;?>'></script>

        <script src='<?php echo $assetUrl;?>/js/wfdesigner.js?<?php echo VERHASH;?>'></script>
        <script src='<?php echo $assetUrl;?>/js/processView.js?<?php echo VERHASH;?>'></script>
        <script>
            var G = {
            SITE_URL:'<?php echo Ibos::app()->setting->get("siteurl");?>',
            STATIC_URL: '<?php echo STATICURL;?>',
            cookiePre: '<?php echo Ibos::app()->setting->get("config/cookie/cookiepre");?>',
            cookiePath: '<?php echo Ibos::app()->setting->get("config/cookie/cookiepath");?>',
            cookieDomain: '<?php echo Ibos::app()->setting->get("config/cookie/cookiedomain");?>',
            formHash: '<?php echo FORMHASH;?>'
            };
            Ibos.app.setPageParam({
            "flowId": <?php echo $flowID;?>,
            "runId": '<?php echo $run["runid"];?>',
            "flowKey": '<?php echo $key;?>'
            })
        </script>
        <script src="<?php echo $assetUrl;?>/js/wf_preview_flow.js?<?php echo VERHASH;?>"></script>
    </body>
</html>