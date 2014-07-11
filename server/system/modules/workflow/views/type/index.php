<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
    <?php echo $this->widget("IWWfListSidebar", array("category" => $category, "catId" => $catId), true);?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <div class="btn-group">
                        <a href="<?php echo $this->createUrl("type/add", array("catid" => $this->catid));?>" class="btn btn-primary"><?php echo $lang["New"];?></a>
                    </div>
                    <div class="btn-group">
                        <a href="javascript:;" data-click="del" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl("type/del");?>&quot;}" class="btn"><?php echo $lang["Delete"];?></a>
                    </div>
                    <div class="btn-group">
                        <a href="javascript:;" class="btn dropdown-toggle" data-toggle="dropdown"><?php echo $lang["More operation"];?><i class="caret"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:;" data-click="handover"><?php echo $lang["Work handover"];?></a></li>
                            <li><a href="javascript:;" data-click="clear" data-param="{&quot;url&quot;:&quot;<?php echo $this->createUrl("type/del", array("op" => "clear"));?>&quot;}"><?php echo $lang["Work clear"];?></a></li>
                        </ul>
                    </div>
                </div>
                <form action="<?php echo $this->createUrl("type/index");?>" id="search_form" method="get">
                    <div class="search search-config pull-right span4">
                        <input type="hidden" name="r" value="workflow/type/index" />
                        <input type="text" placeholder="<?php echo $lang["Search tip"];?>" name="keyword" id="mn_search" nofocus />
                        <input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
                        <a href="javascript:;"></a>
                    </div>
                </form>
            </div>
            <div class="page-list-mainer ovh" id="wf_manage_mn">
                <?php if (!empty($list)) : ?>
                    <table class="table table-hover wf-manage-table" id="wf_manage_table">
                        <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox"><input type="checkbox" data-name="work"></label>
                                </th>
                                <th><?php echo $lang["Flow name"];?></th>
                                <th><?php echo $lang["Form"];?></th>
                                <th width="80"><?php echo $lang["Flow type"];?></th>
                                <th width="180"><?php echo $lang["Work nums"];?>（<?php echo $lang["All"];?>/<?php echo $lang["Deleted"];?>）</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $flowTypeDesc = array("1" => $lang["Fixed flow"], "2" => $lang["Free flow"]); ?>
                            <?php foreach ($list as $flow ) : ?>
                                <tr id="flow_tr_<?php echo $flow["flowid"];?>" data-type="<?php echo $flow["type"];?>" data-formid="<?php echo $flow["formid"];?>" data-id="<?php echo $flow["flowid"];?>">
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" value="<?php echo $flow["flowid"];?>" name="work" />
                                        </label>
                                    </td>
                                    <td><span class="fss"><?php echo $flow["name"];?></span></td>
                                    <td><span class="fss"><?php echo $flow["formname"];?></span></td>
                                    <td><span class="fss"><?php echo $flowTypeDesc[$flow["type"]];?></span></td>
                                    <td><span class="fss counter"><?php echo $flow["flowcount"];?> / <?php echo $flow["delcount"];?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-data-tip"></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($list)) : ?>
                <div class="page-list-footer">
                    <div class="page-num-select">
                        <div class="btn-group dropup">
                            <a class="btn btn-small dropdown-toggle" data-toggle="dropdown" id="page_num_ctrl" data-selected="<?php echo $pageSize;?>" data-url="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>">
                                <i class="o-setup"></i> <?php echo $lang["Each page"];?> <?php echo $pageSize;?> <i class="caret"></i>
                            </a>
                            <ul class="dropdown-menu" id="page_num_menu">
                                <li <?php if ($pageSize == 10) echo 'class="active"';?> ><a href="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>&pagesize=10"><?php echo $lang["Each page"];?> 10</a></li>
                                <li <?php if ($pageSize == 20) echo 'class="active"';?> ><a href="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>&pagesize=20"><?php echo $lang["Each page"];?> 20</a></li>
                                <li <?php if ($pageSize == 30) echo 'class="active"';?> ><a href="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>&pagesize=30"><?php echo $lang["Each page"];?> 30</a></li>
                                <li <?php if ($pageSize == 40) echo 'class="active"';?> ><a href="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>&pagesize=40"><?php echo $lang["Each page"];?> 40</a></li>
                                <li <?php if ($pageSize == 50) echo 'class="active"';?> ><a href="<?php echo $this->createUrl("type/index", array("catid" => $this->catid));?>&pagesize=50"><?php echo $lang["Each page"];?> 50</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="pull-right"><?php $this->widget("IWPage", array("pages" => $pages));?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div id="wf_slide" class="slide-window" style="display:none;">
        <a href="javascript:;" id="wf_slide_ctrl" class="slide-window-ctrl"></a>
        <div class="wf-guide" id="wf_guide">
            <div class="wf-guide-header" id="wf_guide_header">
                <i class="cbtn"></i>
                <h4><?php echo $lang["Flow name"];?></h4>
                <span><?php echo $lang["Flow status"];?></span>
            </div>
            <div class="shadow">
                <ul class="wf-guide-list" id="wf_guide_list">
                    <li class="active">
                        <div class="wf-guide-bk">
                            <h5>1、<?php echo $lang["Define the process"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="editFlow"><i class="cbtn o-edit"></i><?php echo $lang["Edit"];?></a>
                                <a href="javascript:;" data-guide="importFlow"><i class="cbtn o-import"></i><?php echo $lang["Import"];?></a>
                                <a href="javascript:;" data-guide="exportFlow"><i class="cbtn o-export"></i><?php echo $lang["Export"];?></a>
                            </div>
                            <p><?php echo $lang["Guide step1 desc"];?></p>
                        </div>
                    </li>
                    <li>
                        <div class="wf-guide-bk">
                            <h5>2、<?php echo $lang["Form design"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="designForm"><i class="cbtn o-form-design"></i><?php echo $lang["Design"];?></a>
                                <a href="javascript:;" data-guide="previewForm"><i class="cbtn o-view"></i><?php echo $lang["Review"];?></a>
                            </div>
                            <p><?php echo $lang["Guide step2 desc"];?></p>
                        </div>
                    </li>
                    <li class="bdbs" id="fixed_opt">
                        <div class="wf-guide-bk">
                            <h5>3、<?php echo $lang["Flow design"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="designFlow"><i class="cbtn o-flow-design"></i><?php echo $lang["Design"];?></a>
                                <a href="javascript:;" data-guide="verifyFlow"><i class="cbtn o-validate"></i><?php echo $lang["Check"];?></a>
                            </div>
                            <p><?php echo $lang["Guide step3 desc"];?></p>
                        </div>
                    </li>
                    <li class="bdbs" id="free_opt" style="display:none;">
                        <div class="wf-guide-bk">
                            <h5>3、<?php echo $lang["New permissions"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="freeNew"><i class="cbtn o-plus"></i><?php echo $lang["New"];?></a>
                            </div>
                            <p><?php echo $lang["Guide free desc"];?></p>
                        </div>
                    </li>
                    <li>
                        <div class="wf-guide-bk">
                            <h5>■&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang["Manage process permissions"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="addPermission"><i class="cbtn o-plus"></i><?php echo $lang["New"];?></a>
                                <a href="javascript:;" data-guide="showManagerPermissionList"><i class="cbtn o-list"></i><?php echo $lang["List"];?></a>
                            </div>
                            <p><?php echo $lang["Guide step4 desc"];?></p>
                        </div>
                    </li>
                    <li>
                        <div class="wf-guide-bk">
                            <h5>■&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang["Configure advanced query"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="addSearchTemplate"><i class="cbtn o-plus"></i><?php echo $lang["New"];?></a>
                                <a href="javascript:;" data-guide="showSearchTemplateList"><i class="cbtn o-list"></i><?php echo $lang["List"];?></a>
                            </div>
                            <p><?php echo $lang["Guide step5 desc"];?></p>
                        </div>
                    </li>
                    <li>
                        <div class="wf-guide-bk">
                            <h5>■&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang["Set timing task"];?></h5>
                            <div class="wf-guide-op">
                                <a href="javascript:;" data-guide="showTimerList"><i class="cbtn o-plus"></i><?php echo $lang["New"];?></a>
                                <a href="javascript:;" data-guide="showTimerList"><i class="cbtn o-list"></i><?php echo $lang["List"];?></a>
                            </div>
                            <p><?php echo $lang["Guide step6 desc"];?></p>
                        </div>
                    </li>
                </ul>
            </div> <!-- end shadow -->
        </div><!-- end guide -->
    </div><!-- end slide -->
</div>
<script>
    Ibos.app.setPageParam({
        flowid: <?php echo $this->flowid;?>,
        catid: <?php echo $this->catid;?>
    })
</script>
<script src='<?php echo $assetUrl;?>/js/wfsetup.js?<?php echo VERHASH;?>'></script>
<script>
    //搜索
    Ibos.search.init();
    Ibos.search.disableAdvance();
    var $slide = $("#wf_slide"), $mainer = $("#wf_manage_mn"), $manageTable = $("#wf_manage_table"), $slideCtrl = $("#wf_slide_ctrl");
    var slidewin = slideWindow($slide, $mainer, "right", {justify: true});
    $manageTable.on("click", "tbody tr", function() {
        var flowid = $.attr(this, "data-id");
        if (flowid) {
            guide.open(flowid);
        }
    }).on("click", ".checkbox", function(evt) {
        evt.stopPropagation();
    });
    $slideCtrl.click(function() {
        slidewin.slideOut();
        $slide.stopWaiting();
    });
    var guide = (function() {
        // 工作流ID
        var flowid, formid, $guideList = $("#wf_guide_list"), $guideHeader = $("#wf_guide_header");
        $guideList.on("mouseover", "li", function() {
            Ui.selectOne($(this));
        });
        var _setHeader = function(title, status) {
            var _headerTpl = '<i class="cbtn <%=cls%>"></i> <h4><%=title%></h4> <span><%=text%></span></div>';
            var _map = {
                warning: ["o-wf-warning", U.lang('WF.PROCESS_NOT_DONE')],
                success: ["o-wf-success", U.lang('WF.PROCESS_RUNNING_WELL')],
                error: ["o-wf-error", U.lang('WF.PROCESS_ERROR')]
            };
            var data = {
                title: title || "",
                cls: _map[status] ? _map[status][0] : "",
                text: _map[status] ? _map[status][1] : ""
            };
            $guideHeader.html($.template(_headerTpl, data));
        };

        var _setCurrent = function(index) {
            if (index && index > 0) {
                var $items = $guideList.find("li");
                $items.removeClass("active").eq(parseInt(index, 10) - 1).addClass("active");
            }
        };

        // 参数以逗号分隔的序号字符串，如"1,2,3,4"
        var _setFinished = function(indexes) {
            var $items = $guideList.find("li:visible");
            $items.removeClass("finished");
            if (indexes !== "") {
                var indexArr = indexes.split(",");
                for (var i = 0, len = indexArr.length; i < len; i++) {
                    var index = $items.eq(parseInt(indexArr[i], 10) - 1);
                    index.addClass("finished");
                }
            }
        };

        function _change(res) {
            _setHeader(res.title, res.status);
            _setCurrent(res.current);
            _setFinished(res.finished);
        }

        return {
            getId: function() {
                return flowid;
            },
            getFormId: function() {
                return formid;
            },
            open: function(id, callback) {
                var $tr = $('#flow_tr_' + id);
                if (id && $tr.is('tr')) {
                    flowid = id;
                    formid = $tr.attr('data-formid');
                    var type = $tr.attr('data-type');
                    if (type == '1') {
                        $('#fixed_opt').show();
                        $('#free_opt').hide();
                    } else {
                        $('#fixed_opt').hide();
                        $('#free_opt').show();
                    }
                    var guideInfoUrl = Ibos.app.url('workflow/type/getguide', {formhash: G.formHash});
                    Ui.selectOne($tr);
                    slidewin.slideIn(function() {
                        $slide.waitingC();
                        $.post(guideInfoUrl, {id: flowid}, function(res) {
                            _change(res);
                            $slide.stopWaiting();
                            callback && callback(res);
                        }, "json");
                    });
                }
            },
            dialog: function(url, param, options) {
                var opts;
                if (!url) {
                    return false;
                }
                url += (url.indexOf("?") === -1 ? "?flowid=" : "&flowid=") + this.getId() + (param ? "&" + $.param(param) : "");
                opts = $.extend({
                    padding: 0,
                    ok: true,
                    cancel: true
                }, options);

                this.hideDialog();
                this._dialog = Ui.ajaxDialog(url, opts);
            },
            hideDialog: function() {
                this._dialog && this._dialog.close();
            }
        };
    })();
    (function() {

        var flowType = {
            getCheckedId: function() {
                return U.getCheckedValue("work");
            },
            access: function(url, param, success, msg) {
                var flowIds = this.getCheckedId();
                var _ajax = function(url, param, success) {
                    $.post(url, param, function(res) {
                        if (res.isSuccess) {
                            if (success && $.isFunction(success)) {
                                success.call(null, res, flowIds);
                            }
                            Ui.tip(U.lang("OPERATION_SUCCESS"), 'success');
                        } else {
                            Ui.tip(res.errorMsg, 'danger');
                        }
                    }, 'json');
                };
                if (flowIds !== '') {
                    param = $.extend({flowids: flowIds, formhash: G.formHash}, param);
                    if (msg) {
                        Ui.confirm(msg, function() {
                            _ajax(url, param, success);
                        });
                    } else {
                        _ajax(url, param, success);
                    }
                } else {
                    Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'warning');
                }
            },
            removeRows: function(ids) {
                var arr = ids.split(',');
                for (var i = 0, len = arr.length; i < len; i++) {
                    $('#flow_tr_' + arr[i]).remove();
                }
            },
            refreshRows: function(ids, callback) {
                var arr = ids.split(',');
                for (var i = 0, len = arr.length; i < len; i++) {
                    callback && callback($('#flow_tr_' + arr[i]));
                }
            }
        };
        Ibos.events.add({
            "del": function(param, elem) {
                flowType.access(param.url, null, function(res, ids) {
                    flowType.removeRows(ids);
                    slidewin.slideOut();
                }, U.lang("WF.DELETE_FLOW_CONFIRM"));
            },
            "clear": function(param, elem) {
                flowType.access(param.url, null, function(res, ids) {
                    // 清空后统计数归0
                    flowType.refreshRows(ids, function(obj) {
                        obj.find('.counter').html('0 / 0');
                    });
                    slidewin.slideOut();
                }, U.lang("WF.CLEAR_FLOW_CONFIRM"));
            },
            "handover": function(param, elem) {
                var url = Ibos.app.url('workflow/type/trans');
                var dialog = Ui.ajaxDialog(url, {
                    title: U.lang('WF.WORK_HANDOVER'),
                    padding: '20px',
                    ok: function() {
                        if ($('#work_handover_user_from').val() === '') {
                            Ui.tip(U.lang('WF.TRANSACTOR_CANT_BE_EMPTY'), 'danger');
                            return false;
                        }
                        if ($('#work_handover_user_to').val() === '') {
                            Ui.tip(U.lang('WF.TRANS_OBJECT_CANT_BE_EMPTY'), 'danger');
                            return false;
                        }
                        $('#handover_form').waiting(U.lang('IN_SUBMIT'), "mini", true);
                        $.post(url, $('#handover_form').serializeArray(), function(data) {
                            $('#handover_form').stopWaiting();
                            if (data.isSuccess) {
                                Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
                                dialog.close();
                            } else {
                                Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
                            }
                        }, 'json');
                        return false;
                    },
                    cancel: true
                });
            }
        });

        $(document).ready(function() {
            // 接收传来的flowid，显示引导页面
            if (Ibos.app.g('flowid') && Ibos.app.g('catid')) {
                guide.open(Ibos.app.g('flowid'));
            }
        });
    })();
</script>