<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/index.css?<?php echo VERHASH; ?>">
<div class="mtw">
    <div class="mtw-portal-nav-wrap">
        <ul class="portal-nav clearfix">
            <li class="active">
                <a href="<?php echo Ibos::app()->urlManager->createUrl( 'main/default/index' ); ?>">
                    <i class="o-portal-office"></i>
                    办公门户
                </a>
            </li>
            <li>
                <a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/home/index' ); ?>">
                    <i class="o-portal-personal"></i>
                    个人门户
                </a>
            </li>
            <?php if ( ModuleUtil::getIsEnabled( 'app' ) ): ?>
                <li >
                    <a href="<?php echo Ibos::app()->urlManager->createUrl( 'app/default/index' ); ?>">
                        <i class="o-portal-app"></i>
                        应用门户
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <span class="pull-right"><?php echo Ibos::app()->setting->get( 'lunar' ); ?></span>
</div>
<div>
    <!-- 常用菜单 -->
    <div class="cm-menu mbs" id="cm_menu">
        <ul class="cm-menu-list clearfix">
            <?php if ( !empty( $menus['commonMenu'] ) ): ?>
                <?php foreach ( $menus['commonMenu'] as $index => $menu ): ?>
                    <?php if ( !$menu['iscustom'] ): ?>
                        <li data-module="<?php echo $menu['module']; ?>">
                            <a href="<?php echo Ibos::app()->urlManager->createUrl( $menu['url'] ); ?>" title="<?php echo $menu['description']; ?>" <?php if ( $menu['openway'] == 0 ): ?>target="_blank"<?php endif; ?>>
                                <div class="posr">
                                    <img width="64" height="64" class="mbs" src="<?php echo STATICURL; ?>/image/trans.gif" data-src="
                                         <?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                                    <span class="bubble" data-bubble="<?php echo $menu['module']; ?>"></span>
                                </div>
                                <div class="cm-menu-title"><?php echo $menu['name']; ?></div>
                            </a>
                        </li>
                    <?php else: ?>
                        <li data-module="<?php echo $menu['module']; ?>">
                            <a href="<?php echo UrlUtil::getUrl( $menu['url'] ); ?>" title="<?php echo $menu['description']; ?>" <?php if ( $menu['openway'] == 0 ): ?>target="_blank"<?php endif; ?>>
                                <div class="posr">
                                    <img width="64" height="64" class="mbs" src="<?php echo STATICURL; ?>/image/trans.gif" data-src="
                                         <?php echo 'data/icon/' . $menu['icon']; ?>">
                                    <span class="bubble" data-bubble="<?php echo $menu['module']; ?>"></span>
                                </div>
                                <div class="cm-menu-title"><?php echo $menu['name']; ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div id="module_panel" class="in-mod-wrap clearfix">
        <?php if ( CloudApi::getInstance()->exists( 'conf' ) ): ?>
            <div class="mbox">
                <div class="mbox-header"><h4>会议</h4></div>
                <div class="mbox-body">
                    <div style="padding: 20px;">
                        <textarea rows="3" placeholder="输入邀请加入的会议成员手机号码，以;号分隔多个" id="confnumbers"></textarea><br/><br/>
                        <button id="createconf" class="btn">创建会议</button>
                    </div>
                </div>
            </div>
            <script>
                $('#createconf').on('click', function() {
                    var phone = $('#confnumbers').val();
                    var arr = phone.split(';'), member = [];
                    $.each(arr, function(i, n) {
                        if (n !== '' && (U.regex(n, 'mobile') || U.regex(n, 'tel'))) {
                            member.push(n);
                        }
                    });
                    if (member.length > 1) {
                        var url = '<?php echo CloudApi::getInstance()->build('Api/Ivr/Confirmconf'); ?>';
                        Ui.openFrame(url + '&member=' + encodeURI(member.join(';')), {width: '830px', height: '560px', title: '语音会议'}
                        );
                    } else {
                        Ui.tip('会议成员必须大于1个', 'error')
                    }
                })
            </script>
        <?php endif; ?>
    </div>
</div>
<div id="in_operate" class="in-operate">
    <a href="javascript:;" class="o-in-menu mbs" data-action="setupMenu" title="<?php echo $lang['Common menu settings']; ?>"></a> 
    <a href="javascript:;" class="o-in-plus mbs" id="manager_ctrl" data-action="openManager" title="<?php echo $lang['Add module']; ?>"></a>
    <a href="javascript:;" class="o-in-totop" data-action="totop" title="<?php echo $lang['Back to top']; ?>"></a>
</div>
<div class="mbox in-mu" id="in_mu">
    <div class="in-inmenu rdt">
        <ul class="in-inmenu-list clearfix">
            <?php if ( !empty( $menus['commonMenu'] ) ): ?>
                <?php foreach ( $menus['commonMenu'] as $index => $menu ): ?>
                    <?php if ( !$menu['iscustom'] ): ?>
                        <li>
                            <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo Ibos::app()->urlManager->createUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>" 
                                 data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                                <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                                <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                                <div title="<?php echo $menu['description']; ?>">
                                    <img width="64" height="64" _src="<?php echo STATICURL; ?>/image/trans.gif" src="
                                         <?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                                </div>
                                <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                            </div>
                        </li>
                    <?php else: ?>
                        <li>
                            <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo UrlUtil::getUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>" 
                                 data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo 'data/icon/' . $menu['icon']; ?>">
                                <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                                <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                                <div title="<?php echo $menu['description']; ?>">
                                    <img width="64" height="64" _src="<?php echo STATICURL; ?>/image/trans.gif" src="
                                         <?php echo 'data/icon/' . $menu['icon']; ?>">
                                </div>
                                <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php $emptyLi = 8 - count( $menus['commonMenu'] ); ?>
            <?php for ( $i = 1; $i <= $emptyLi; $i++ ): ?>
                <li></li>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="in-outmenu">
        <h5><?php echo $lang['Without adding modules']; ?></h5>
        <div class="in-outmenu-list clearfix">
            <?php if ( !empty( $menus['notUsedMenu'] ) ): ?>
                <?php foreach ( $menus['notUsedMenu'] as $index => $menu ): ?>
                    <?php if ( !$menu['iscustom'] ): ?>
                        <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo Ibos::app()->urlManager->createUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>"
                             data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                            <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                            <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                            <div title="<?php echo $menu['description']; ?>">
                                <img width="64" height="64" _src="<?php echo STATICURL; ?>/image/trans.gif" src="
                                     <?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                            </div>
                            <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                        </div>
                    <?php else: ?>
                        <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo UrlUtil::getUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>"
                             data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo 'data/icon/' . $menu['icon']; ?>">
                            <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                            <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                            <div title="<?php echo $menu['description']; ?>">
                                <img width="64" height="64" _src="<?php echo STATICURL; ?>/image/trans.gif" src="
                                     <?php echo 'data/icon/' . $menu['icon']; ?>">
                            </div>
                            <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="fill-sn clearfix">
        <div class="pull-right">
            <!--管理员，设置通用菜单按钮-->
            <?php if ( Ibos::app()->user->uid == 1 ): ?>
                <a href="javascript:;" class="btn" style="" data-action="setDefaultMent"><?php echo $lang['Set as default menu']; ?></a>
            <?php endif; ?>
            <a href="javascript:;" class="btn" data-action="restoreDefaultMenu"><?php echo $lang['Restore default settings']; ?></a>
            <a href="javascript:;" class="btn btn-primary" data-action="saveCommonMenu"><?php echo $lang['Save']; ?></a>
        </div>
    </div>
</div>
<!-- Template: 模块管理器模板 -->
<script type="text/ibos-template" id="tpl_manager">
    <div class="mbox mod-manager">
    <div class="mbox-header">
    <div class="fill-hn">
    <strong><%= U.lang("IN.MANAGE_MY_MODULE") %></strong>
    </div>
    </div>
    <div>
    <ul class="slist mod-list">
    <li>
    <% for(var i = 0; i < data.length; i++) { %>
    <% if(i !== 0 && i % 2 === 0) { %>
    </li><li>
    <% } %>
    <label class="checkbox">
    <input type="checkbox" value="<%= data[i].name %>" data-title="<%= data[i].title %>">
    <%= data[i].title%>
    </label>
    <% } %>
    </li>
    </ul>
    </div>
    <div class="mod-manager-footer">
    <a href="javascript:;" data-act="reset">
    <i class="o-cancel"></i>
    <%= U.lang("IN.RESET_SETTINGS") %>
    </a>
    </div>
    </div>
</script>
<!-- Template:常用菜单项模板 -->
<script type="text/ibos-template" id="mu_item_tpl">
    <li data-module="<%=mod%>">
    <a href="<%=href%>" title="<%=desc%>">
    <div class="posr">
    <img width="64" height="64" class="mbs" src="<%=src%>">
    <span class="bubble" data-bubble="<%=mod%>"></span>
    </div>
    <div class="cm-menu-title"><%=title%></div>
    </a>
    </li>
</script>
<script>
    Ibos.app.s({
        "guideNextTime": <?php
            if ( MainUtil::getCookie( 'guideNextTime' ) == md5( Ibos::app()->user->uid ) ) {
                echo 1;
            } else {
                echo 0;
            }
            ?>,
        "assetUrl": "<?php echo $assetUrl; ?>",
        "refreshInterval": 10000 // @debug: 原本为 10000
    })
</script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/introjs/intro.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.mbox.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/index.js?<?php echo VERHASH; ?>'></script>
<script>
    (function() {
        // 默认配置，已安装的模块，由PHP端判断输出
        var moduleInstalled = [
<?php foreach ( $widgetModule as $index => $module ): ?>
                {name: "<?php echo $index; ?>", title: "<?php echo $module['name']; ?>", show: true},
<?php endforeach; ?>
        ];
        // 按上面的输出方式在Ie 8下 数组多出一位null，所以要去掉
        if (moduleInstalled[moduleInstalled.length - 1] == null) {
            moduleInstalled.pop();
        }

        var getModuleSettings = (function() {
            var moduleSettings = $.parseJSON('<?php echo $moduleSetting; ?>');
            return function(name, options) {
                if (!moduleSettings[name]) {
                    $.error("(getModuleSettings): 不存在标识为" + name + "的模块配置")
                }
                return $.extend(true, {}, moduleSettings[name], options);
            };
        })();

        // 获取已安装模块名称组合的字符串
        var getInstalledModuleName = function() {
            return $.map(moduleInstalled, function(d, i) {
                return d.name;
            }).join(",");
        }

        var loadModuleUrl = Ibos.app.url("main/api/loadmodule"),
                loadNewUrl = Ibos.app.url("main/api/loadnew"),
                refreshInterval = Ibos.app.g("refreshInterval");

        // 配置存储管理器
        var storage = moduleStorage(moduleInstalled);
        // 模块面板
        var $modulePanel = $("#module_panel");
        panel = modulePanel($modulePanel, moduleInstalled);

        // 拖拽配置
        var dragSettings = {handle: ".mbox-header", tolerance: "pointer"},
        dragUpdateHandler = function() {
            var srg = [];
            $modulePanel.find(".mbox").each(function() {
                var name = $.attr(this, "data-name");
                srg.push({
                    name: name
                });
                storage.set(srg);
            });
        };

        // 模块管理器
        var $managerCtrl = $("#manager_ctrl");
        manager = moduleManager($managerCtrl, moduleInstalled, {
            onchange: function(name, isChecked) {
                if (isChecked) {
                    // 插入面板并写入存储，读取内容
                    panel.add(name, getModuleSettings(name), function(name) {
                        storage.add({'name': name});
                        indexModule.load(loadModuleUrl, {module: name});
                    });
                } else {
                    // 移除面板并写入存储
                    panel.remove(name, function(name) {
                        storage.remove(name);
                    });
                }
            },
            onreset: function() {
                // 清除本地存储的配置，使用默认设置
                storage.clear();
                // 重载页面
                window.location.reload();
            }
        });

        // 模块加载
        $(function() {
            // 从存储器中读取设置为显示状态的模块
            var getModuleNames = function() {
                var mods = storage.get(), modNames = [];
                for (var i = 0, len = mods.length; i < len; i++) {
                    modNames.push(mods[i].name);
                }
                return modNames;
            };
            var modNames = getModuleNames(),
                    modstr;
            var $cmMenu = $("#cm_menu"),
                    bubble = menuBubble($cmMenu); // 消息数目提醒;
            for (var i = 0, len = modNames.length; i < len; i++) {
                // 当存储器中的模块已卸载时，删除对应的存储记录
                if (nameIndexOf(modNames[i], moduleInstalled) === -1) {
                    storage.remove(modNames[i]);
                } else {
                    // 第二个参数false表示不写入存储
                    panel.add(modNames[i], getModuleSettings(modNames[i], {
                        onremove: function(name) {
                            // 模块移除时，删除对应存储，uncheck对应项
                            storage.remove(name);
                            manager.unCheck(name);
                        }
                    }), function(name, $container) {
                        // 加载模块后，更新模块管理器的选中项
                        manager.check(modNames[i]);
                        $container.waitingC();
                    });
                }
            }

            modstr = modNames.join(",");
            var requestTime = +new Date();
            // 模块读取
            indexModule.load(loadModuleUrl, {module: modstr, random: Math.random()}, function(name, $container) {
                $container.stopWaiting();
            });
            // 读取后初始化拖拽排序功能
            $modulePanel.sortable(dragSettings).on("sortupdate", dragUpdateHandler);

            // 消息数目提醒
            bubble.load(loadNewUrl, {module: getInstalledModuleName()});
            // 定时刷新未读消息数
            setInterval(function() {
                if (modstr) {
                    // indexModule.load(loadModuleUrl, {module: modstr});
                    bubble.load(loadNewUrl, {module: getInstalledModuleName(), d: requestTime}, function(data) {
                        var modNeedFresh = [];
                        requestTime = data.timestamp;
                        for (var name in data) {
                            if (name !== 'timestamp') {
                                if (parseInt(data[name], 10) > 0) {
                                    modNeedFresh.push(name);
                                }
                            }
                        }
                        ;
                        if (modNeedFresh.length) {
                            indexModule.load(loadModuleUrl, {module: modNeedFresh.join(",")});
                        }
                    });
                }
            }, refreshInterval);

            // 图片延迟
            U.delayImage($cmMenu);
        });
    })();
</script>
<script>
    (function() {
        // 初始化引导
        var guideUrl = Ibos.app.url('main/default/guide'),
                guideNextTime = Ibos.app.g("guideNextTime");

        if (!guideNextTime) {

            $.post(guideUrl, {op: 'checkIsGuided'}, function(res) {
                if (res.isNewcommer) {
                    $("body").append(res.guideView);
                    // 使用 formValidate ajax 验证的输入框使用placeholder时
                    // 需要把placeholder初始化放在 ajaxValidate之前
                    // 否则会由于事件执行顺序干扰造成取值失败
                    if ($.fn.placeholder) {
                        $("#initialize_guide [placeholder][type='text']").placeholder();
                    }

                    var guideJs = res.isadministrator ? 'administrator_guide.js' : 'initialize_guide.js';
                    $.when(
                            $.getScript(Ibos.app.getStaticUrl("/js/lib/SWFUpload/swfupload.packaged.js")),
                            $.getScript(Ibos.app.getStaticUrl("/js/lib/SWFUpload/handlers.js"))
                            )
                            .done(function() {
                                $.getScript(Ibos.app.g("assetUrl") + "/js/" + guideJs)
                                        .done(function() {
                                            var left = $(window).width() / 2 - 340;
                                            //设置初始化时，初始化页面和遮罩的层级关系
                                            $("#initialize_guide").css({"top": "100px", "left": left, "position": "absolute", "zIndex": "12"});
                                            Ui.modal.show({zIndex: 11, backgroundColor: "black"});

                                            // 当密码框同时需要表单验证和 placeholder 初始化时
                                            // 表单验证需要在placeholder 之前
                                            // 否则会由于 placeholder 替换导致事件绑错节点
                                            if ($.fn.placeholder) {
                                                $("#initialize_guide [placeholder][type='password']").placeholder();
                                            }
                                        })
                            })
                }
            }, 'json');
        }
    })();
</script>
