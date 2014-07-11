<li class="cmt-item">
	<div class="avatar-box pull-left">
		<a href="<?php echo $user["space_url"];?>" class="avatar">
			<img src="<?php echo $user["avatar_middle"];?>" class="avatar avatar-small" width="30" height="30">
		</a>
	</div>
	<div class="cmt-body fss">
		<p class="xcm">
			<a href="<?php echo $user["space_url"];?>" class="anchor"><?php echo $user["realname"];?>：</a>
			<?php echo $reply["content"];?>
			<span class="tcm ilsep">(<?php echo ConvertUtil::formatDate($reply["edittime"], "u");?>)</span>
		</p>
		<div class="xar">
			<a href="javascript:;" data-click="setreply" data-param="{&quot;name&quot;:&quot;<?php echo $user["realname"];?>&quot;}"><?php echo $lang["Reply"];?></a>
			<a href="javascript:;" class="mls" data-click="delreply" data-param="{&quot;feedid&quot;:&quot;<?php echo $newID;?>&quot;}"><?php echo $lang["Delete"];?></a>
		</div>
	</div>
</li>