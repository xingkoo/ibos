<!-- load script end -->
<div class="mtw">
	<div class="mtw-portal-nav-wrap">
        <ul class="portal-nav clearfix">
            <li>
                <a href="<?php echo Ibos::app()->urlManager->createUrl("main/default/index");?>">
                    <i class="o-portal-office"></i>
                    办公门户
                </a>
            </li>
            <li>
                <a href="<?php echo Ibos::app()->urlManager->createUrl("weibo/home/index");?>">
                    <i class="o-portal-personal"></i>
                    个人门户
                </a>
            </li>
			<?php if (ModuleUtil::getIsEnabled("app")) : ?>
            <li class="active">
                <a href="<?php echo Ibos::app()->urlManager->createUrl("app/default/index");?>">
                    <i class="o-portal-app"></i>
                    应用门户
                </a>
            </li>
			<?php endif; ?>
        </ul>
    </div>
	<span class="pull-right"><?php echo Ibos::app()->setting->get("lunar");?></span>
</div>
<!-- 公有 JS -->
<script src="<?php echo STATICURL;?>/js/src/base.js?<?php echo VERHASH;?>"></script>
<script src='<?php echo STATICURL;?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/zTree/jquery.ztree.all-3.5.min.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/lib/Select2/select2.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo FileUtil::fileName("data/org.js");?>?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/src/common.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/app/ibos.userSelect.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo STATICURL;?>/js/src/application.js?<?php echo VERHASH;?>'></script>

<!-- 实际需要 Html start -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/app.css?<?php echo VERHASH;?>">
<div class="app-portal clearfix">
	<div class="app-portal-mainer app-widget-empty" id="app_widget_panel">
		<?php foreach ($widget as $app ) : ?>
			<div class="mbox" data-node-type="appWidget" data-app-id="<?php echo $app["appid"];?>" data-app-url="<?php echo $app["url"];?>" data-app-name="<?php echo $app["name"];?>" data-app-icon="<?php echo $app["icon"];?>" data-app-width="<?php echo !empty($app["width"]) ? $app["width"] : 580;?>" data-app-height="<?php echo !empty($app["height"]) ? $app["height"] : 400;?>">
				<div class="mbox-header">
					<h4><?php echo $app["name"];?></h4>
					<a href="javascript:;" class="o-close-simple" data-action="removeWidget" data-param='{"id": <?php echo $app["appid"];?>}'></a>
				</div>
				<div class="mbox-body">
					<iframe src="<?php echo $app["url"];?>" frameborder="0" style="width: 580px; height: <?php echo !empty($app["height"]) ? $app["height"] : 400;?>px">

					</iframe>
				</div>
			</div>
		<?php endforeach; ?>
		<a href="javascript:;" class="app-widget-add" data-action="toAddWidget">
			<i class="o-plus"></i>
			<?php echo $lang["Add Application plates"];?>
		</a>
	</div>
	<div class="app-portal-sidebar">
		<div id="app_sc_container">
			<div class="mbox">
				<div class="mbox-header">
					<h4>
					<?php echo $lang["Other Applications"];?></h4>
					<a href="javascript:;" class="o-app-setup" data-action="setupShortcut"></a>
				</div>
				<div class="mbox-body bglb">
					<div class="app-shortcut">
						<ul class="app-shortcut-list clearfix" id="app_shortcut_list">
							<?php foreach ($shortcut as $app ) : ?>
								<li data-node-type="appItem" data-app-id="<?php echo $app["appid"];?>" data-app-url="<?php echo $app["url"];?>" data-app-name="<?php echo $app["name"];?>" data-app-icon="<?php echo $app["icon"];?>" data-app-width="<?php echo !empty($app["width"]) ? $app["width"] : 580;?>" data-app-height="<?php echo !empty($app["height"]) ? $app["height"] : 400;?>">
									<a href="javascript:;">
										<img class="app-shortcut-icon" src="<?php echo $app["icon"];?>" alt="<?php echo $app["name"];?>">
										<span class="app-shortcut-name"><?php echo $app["name"];?></span>
										<i class="app-shortcut-cover"></i>
										<i class="app-shortcut-hover"></i>
									</a>
									<i class='o-shortcut-remove' data-action='removeShortcut' title='<?php echo $lang["Remove application shortcuts"];?>'></i>
								</li>
							<?php endforeach; ?>
							<li>
								<a href="javascript:;" data-action="toAddShortcut">
									<i class="app-shortcut-icon app-shortcut-add"></i>
									<span class="app-shortcut-name"><?php echo $lang["Add Applications"];?></span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Template: app 快捷方式 -->
<script type="text/template" id="tpl_app_item">
	<li data-node-type="appItem" data-app-id="<%= appid %>" data-app-url="<%= url %>" data-app-name="<%= name %>" data-app-icon="<%= icon %>" data-app-width="<%= typeof width !== 'undefined' ? width : 580 %>" data-app-height="<%= typeof height !== 'undefined' ? height : 400 %>">
	<a href="javascript:;">
	<img class="app-shortcut-icon" src="<%= icon %>" alt="<%= name %>">
	<span class="app-shortcut-name"><%= name %></span>
	<i class="app-shortcut-cover"></i>
	<i class="app-shortcut-hover"></i>
	</a>
	</li>
</script>
<!-- Template: app 部件 -->
<script type="text/template" id="tpl_app_widget">
	<div class="mbox" data-node-type="appWidget" data-app-id="<%= appid %>" data-app-url="<%= url %>" data-app-name="<%= name %>" data-app-icon="<%= icon %>" data-app-width="<%= typeof width !== 'undefined' ? width : 580 %>" data-app-height="<%= typeof height !== 'undefined' ? height : 400 %>">
	<div class="mbox-header">
	<h4><%= name %></h4>
	<a href="javascript:;" class="o-close-simple" data-action="removeWidget" data-param='{"id": <%=appid%>}'></a>
	</div>
	<div class="mbox-body">
	<iframe src="<%= url %>" frameborder="0" style="width: 580px; height: <%= typeof height !== 'undefined' ? height : 400%>px">

	</iframe>
	</div>
	</div>
</script>

<script>
	Ibos.app.s({
	widgets: Ibos.local.get("appWidgets"),
	shortcuts: Ibos.local.get("appShortcuts")
	})
</script>

<script src="<?php echo $assetUrl;?>/js/app.js?<?php echo VERHASH;?>"></script>
