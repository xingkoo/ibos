<!-- Sidebar -->
<div class="aside">
	<div class="sbb sbbf sbbl">
		<ul class="nav nav-strip nav-stacked">
			<li <?php if ( $this->getId() === 'department' ): ?>class="active"<?php endif; ?>>
				<a href="<?php echo $this->createUrl( 'department/index' ); ?>">
					<i class="o-org-department"></i>
					<?php echo $lang['Department manage']; ?>
				</a>
			</li>
			<li <?php if ( $this->getId() === 'user' ): ?>class="active"<?php endif; ?>>
				<a href="<?php echo $this->createUrl( 'user/index' ); ?>">
					<i class="o-org-user"></i>
					<?php echo $lang['User manager']; ?>
				</a>
				<?php if ( $this->getId() === 'user' ): ?><ul id="utree" class="ztree"></ul><?php endif; ?>
			</li>
			<li <?php if ( $this->getId() === 'position' ): ?>class="active"<?php endif; ?>>
				<a href="<?php echo $this->createUrl( 'position/index' ); ?>">
					<i class="o-org-position"></i>
					<?php echo $lang['Position manager']; ?>
				</a>
				<ul id="ptree" class="ztree"></ul>
				<span id="ptree_ctrl" class="tree-ctrl" ></span>
			</li>
		</ul>
	</div>
</div>
<!-- Template: 分类编辑 -->
<script type="text/template" id="tpl_category_edit">
	<form action="javascript:;" class="form-horizontal form-compact" style="width: 300px;">
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Category name']; ?></label>
			<div class="controls">
				<input type="text" class="input-small" name="name" value="<%=name%>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Parent dir']; ?></label>
			<div class="controls">
				<select class="input-small" name="pid">
					<option value="0"><?php echo Ibos::lang( 'None'); ?></option>
					<%= optionHtml %>
				</select>
			</div>
		</div>
	</form>
</script>