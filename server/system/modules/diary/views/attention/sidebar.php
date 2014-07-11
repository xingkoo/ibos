<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li>
                <a href="<?php echo $this->createUrl('default/index') ?>">
                    <i class="o-da-personal"></i>
                    <?php echo Ibos::lang( 'Personal'); ?>
                </a>
            </li>
			<?php if($this->checkIsHasSub()): ?>
            <li>
                <a href="<?php echo $this->createUrl('review/index') ?>">
                	<?php if($this->getUnreviews() != ''):?>
                    <span class="badge pull-right"><?php echo $this->getUnreviews(); ?></span>
               		<?php endif; ?>
                    <i class="o-da-appraise"></i>
                    <?php echo Ibos::lang( 'Review it'); ?>
                </a>
            </li>
			<?php endif; ?>
			<?php if( $this->issetShare() ): ?>
            <li>
                <a href="<?php echo $this->createUrl('share/index') ?>">
                    <i class="o-da-concerned"></i>
                    <?php echo Ibos::lang( 'Share diary'); ?>
                </a>
            </li>
			<?php endif; ?>
            <li class="active">
                <a href="<?php echo $this->createUrl('attention/index') ?>">
                    <i class="o-da-shared"></i>
                    <?php echo Ibos::lang( 'Attention diary'); ?>
                </a>
				<?php if(!empty($aUsers)): ?>
					<div class="mng-list">
						<ul class="mng-scd-list">
							<?php foreach ($aUsers as $aUser): ?>
								<li>
									<div class="mng-item">
										<a href="<?php echo $this->createUrl( 'attention/index', array( 'op' => 'personal', 'uid' => $aUser['uid'] ) ); ?>" <?php if ( EnvUtil::getRequest( 'uid' ) == $aUser['uid'] ): ?>style="color:#3497DB;"<?php endif; ?>>
											<img src="avatar.php?uid=<?php echo $aUser['uid']; ?>&size=middle&engine=<?php echo ENGINE; ?>" alt="">
											<?php echo $aUser['realname']; ?>
										</a>
										<!-- if 未关注 -->
										<?php if ( DiaryUtil::getIsAttention( $aUser['uid'] ) ): ?>
											<a href="javascript:;" data-node-type="udstar" class="o-gudstar pull-right" data-action="toggleAsteriskUnderling" data-param='{"id": "<?php echo $aUser['uid'] ?>"}'></a> 
										<?php else: ?>
											<a href="javascript:;" data-node-type="udstar" class="o-udstar pull-right" data-action="toggleAsteriskUnderling" data-param='{"id": "<?php echo $aUser['uid'] ?>"}'></a>
										<?php endif; ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
				<?php if ( ModuleUtil::getIsEnabled( 'statistics' ) && isset( $statModule['diary'] ) ): ?>
					<?php echo $this->widget( StatCommonUtil::getWidgetName( 'diary', StatConst::SIDEBAR_WIDGET ), array( 'hasSub' => $this->checkIsHasSub() ), true ); ?>
				<?php endif; ?>
            </li>
        </ul>
    </div>
</div>
