<ul class="cmt-sub">
    <?php if (!empty($list)) : ?>
        <?php foreach ($list as $fb ) : ?>
            <li class="cmt-item">
                <div class="avatar-box pull-left">
                    <a href="<?php echo $fb["user"]["space_url"];?>" class="avatar">
                        <img src="<?php echo $fb["user"]["avatar_middle"];?>" class="avatar avatar-small" width="30" height="30">
                    </a>
                </div>
                <div class="cmt-body fss">
                    <p class="xcm">
                        <a href="<?php echo $fb["user"]["space_url"];?>" class="anchor"><?php echo $fb["user"]["realname"];?>：</a>
                        <?php echo $fb["content"];?>
                        <span class="tcm ilsep">(<?php echo ConvertUtil::formatDate($fb["edittime"], "u");?>)</span>
                    </p>
                    <div class="xar">
                        <a href="javascript:;" data-click="setreply" data-param="{&quot;name&quot;:&quot;<?php echo $fb["user"]["realname"];?>&quot;}"><?php echo $lang["Reply"];?></a>
                        <?php if ($fb["uid"] == $this->uid) : ?>
							<a href="javascript:;" class="mls" data-click="delreply" data-param="{&quot;feedid&quot;:&quot;<?php echo $fb["feedid"];?>&quot;}"><?php echo $lang["Delete"];?></a>
						<?php endif; ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else : ?>
            <li class="cmt-item">
                <div class="no-comment-tip"></div>
            </li>
    <?php endif; ?>
</ul>