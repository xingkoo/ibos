<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($this->id == "default") echo 'class="active"';?> >
                <a href="<?php echo $this->createUrl("default/index");?>">
                    <i class="o-rp-personal"></i>
					<?php echo $lang["Personal"];?>
                </a>
            </li>
            <li <?php if ($this->id == "review") echo 'class="active"';?> >
                <a href="<?php echo $this->createUrl("review/index");?>">
                    <i class="o-rp-appraise"></i>
					<?php echo $lang["Reveiw"];?>
                </a>
				<!-- 汇报类型 -->
				<?php 
					$typeid = EnvUtil::getRequest("typeid");
					$getUid = EnvUtil::getRequest("uid");
					$getUser = EnvUtil::getRequest("user");
				?>
				<div class="rp-cycle">
					<div class="rp-cycle-header">
						<strong><?php echo $lang["Report type"];?></strong>
					</div>
					<ul class="aside-list" id="rp_type_aside_list">
						<?php foreach ($reportTypes as $reportType ) : ?>
							<?php $typeid = EnvUtil::getRequest("typeid");?>
							<li <?php if ($reportType["typeid"] == $typeid) echo 'class="active"';?> data-id="<?php echo $reportType["typeid"];?>">
								<a href="<?php echo $this->createUrl("review/index", array("typeid" => $reportType["typeid"], "uid" => $getUid, "user" => $getUser));?>">
									<i>&gt;</i> <?php echo $reportType["typename"];?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<!--下属-->
                <div>
                    <ul class="mng-list" id="mng_list">
						<?php if (!empty($deptArr)) : ?>
						<?php foreach ($deptArr as $dept ) : ?>
	                        <li>
	                            <div class="mng-item mng-department active">
	                                <span class="o-caret dept"><i class="caret"></i></span>
	                                <a href="<?php echo $this->createUrl("review/index", array("subUids" => $dept["subUids"], "typeid" => $typeid));?>">
	                                    <i class="o-org"></i>
										<?php echo $dept["deptname"];?>
	                                </a>
	                            </div>
	                            <ul class="mng-scd-list">
									<?php foreach ($dept["user"] as $user ) : ?>
		                                <li>
		                                    <div class="mng-item">
		                                        <span class="o-caret g-sub" <?php if ($user["hasSub"]) echo ' data-action="toggleSubUnderlingsList" ';?> data-uid="<?php echo $user["uid"];?>"><?php if ($user["hasSub"]) echo '<i class="caret"></i>';?></span>
		                                        <a href="<?php echo $this->createUrl("review/index", array("op" => "personal", "uid" => $user["uid"], "typeid" => $typeid));?>" <?php if (EnvUtil::getRequest("uid") == $user["uid"]) echo 'style="color:#3497DB;"';?> >
		                                            <img src="avatar.php?uid=<?php echo $user["uid"];?>&size=middle&engine=<?php echo ENGINE;?>" alt=""><?php echo $user["realname"];?></a>
		                                    </div>
		                                    <!--下属资料,ajax调用生成-->
		                                </li>
									<?php endforeach; ?>
	                            </ul>
	                        </li>
						<?php endforeach; ?>
						<?php endif; ?>
                    </ul>
                </div>
            </li>
			<?php if (ModuleUtil::getIsEnabled("statistics") && isset($statModule["report"])) : ?>
				<?php echo $this->widget(StatCommonUtil::getWidgetName("report", StatConst::SIDEBAR_WIDGET), array("hasSub" => $this->checkIsHasSub()), true);?>
			<?php endif; ?>
        </ul>
    </div>
</div>
<script>
	Ibos.app.setPageParam({
		'currentSubUid': "<?php echo EnvUtil::getRequest("uid") ? EnvUtil::getRequest("uid") : 0;?>"
	});
</script>
<script>
	$(function(){
		// 侧栏伸缩
		var $mngList = $("#mng_list");
		$mngList.on("click", ".g-sub", function() {
			var $el = $(this),
				$item = $el.parents(".mng-item").eq(0),
				$next = $item.next();
			if (!$el.attr('data-init')) {
				$.get('<?php echo $this->createUrl("review/index", array("op" => "getsubordinates"));?>', {uid: $el.attr('data-uid')}, function(data) {
					$el.parent().after(data);
					$item.addClass('active');
				});
			}
			$el.attr('data-init', '1');
			
			if ($next.is("ul")) {
				Report.toggleTree($next, function(isShowed) {
					if (isShowed) {
						$item.removeClass("active");
					} else {
						$item.addClass("active");
					}
				});
			}
		});

		//展开部门
		$mngList.on("click", ".dept", function() {
			var $el = $(this),
				$item = $el.parents(".mng-item").eq(0),
				$next = $item.next();
			Report.toggleTree($next, function(isShowed) {
				if (isShowed) {
					$item.removeClass("active");
				} else {
					$item.addClass("active");
				}
			});
		});
		
		//查看所有下属
		$mngList.on("click", ".view-all", function() {		
			var $el = $(this);
			$.get('<?php echo $this->createUrl("review/index", array("op" => "getsubordinates", "item" => "99999"));?>', {uid: $el.attr('data-uid')}, function(data) {
				$el.parent().replaceWith(data);
			});
		});
		
		$('[data-action="toggleSubUnderlingsList"][data-uid="' + Ibos.app.g("currentSubUid") + '"]').click();
	})
</script>