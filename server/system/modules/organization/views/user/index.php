<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="mc-header">
			<ul class="mnv nl clearfix">
				<li<?php if ( $type === 'enabled' ): ?> class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'enabled', 'deptid' => $deptId ) ); ?>"><i class="o-org-enabled"></i><?php echo $lang['Enabled']; ?></a>
				</li>
				<li<?php if ( $type === 'lock' ): ?> class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'lock', 'deptid' => $deptId ) ); ?>"><i class="o-org-locked"></i><?php echo $lang['Lock']; ?></a>
				</li>
				<li<?php if ( $type === 'disabled' ): ?> class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'disabled', 'deptid' => $deptId ) ); ?>"><i class="o-org-disabled"></i><?php echo $lang['Disabled']; ?></a>
				</li>
				<li<?php if ( $type === 'all' ): ?> class="active"<?php endif; ?>>
					<a href="<?php echo $this->createUrl( 'user/index', array( 'type' => 'all', 'deptid' => $deptId ) ); ?>"><i class="o-org-all"></i><?php echo $lang['All']; ?></a>
				</li>
			</ul>
		</div>
		<!-- Mainer nav -->
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<?php if ( $perManager ): ?>
						<div class="btn-group">
							<button type="button" onclick="location.href = '<?php echo $this->createUrl( 'user/add' ) . "&deptid=" . EnvUtil::getRequest( 'deptid' ); ?>';" class="btn btn-primary"><?php echo $lang['Add user']; ?></button>
						</div>
						<div class="btn-group">
							<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><?php echo $lang['More operation']; ?>
								<i class="caret"></i>
							</button>
							<ul class="dropdown-menu" id="list_act">
								<li><a data-action="setUserStatus" data-param='{"op": "enabled"}' href="javascript:;"><?php echo $lang['Enabled']; ?></a></li>
								<li><a data-action="setUserStatus" data-param='{"op": "lock"}' href="javascript:;"><?php echo $lang['Lock']; ?></a></li>
								<li><a data-action="setUserStatus" data-param='{"op": "disabled"}' href="javascript:;"><?php echo $lang['Disabled']; ?></a></li>
								<li><a data-action="exportUser" href="javascript:;"><?php echo $lang['Export']; ?></a></li>
							</ul>
						</div>
					<?php endif; ?>
				</div>
				<form method="post" action="<?php echo $this->createUrl( 'user/index', array( 'type' => $type ) ); ?>">
					<div class="search search-config pull-right span4">
						<input type="text" name="keyword" placeholder="<?php echo $lang['User search tip']; ?>" id="mn_search" nofocus>
						<a href="javascript:;">search</a>
					</div>
					<input type="hidden" name="search" value="1" />
					<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
				</form>
			</div>
			<div class="page-list-mainer">
				<?php if ( !empty( $list ) ) : ?>
					<table class="table table-striped table-hover org-user-table" id="org_user_table">
						<thead>
							<tr>
								<th width="20">
									<label class="checkbox">
										<input type="checkbox" data-name="user">
									</label>
								</th>
								<th width="40"></th>
								<th width="100"><?php echo $lang['Full name']; ?></th>
								<th><?php echo $lang['Department']; ?></th>
								<th><?php echo $lang['Position']; ?></th>
								<th width="60"><?php echo $lang['Operation']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $dept = DepartmentUtil::loadDepartment(); ?>
							<?php $position = PositionUtil::loadPosition(); ?>
							<?php foreach ( $list as $key => $value ) : ?>
								<tr>
									<td>
										<?php if ( $value['uid'] !== '1' && $value['perManager'] ): ?>
											<label class="checkbox">
												<input type="checkbox" name="user" value="<?php echo $value['uid']; ?>" />
											</label>
										<?php endif; ?>
									</td>
									<td>
										<div class="avatar-box" data-toggle="usercard" data-param="uid=<?php echo $value['uid']; ?>">
											<span class="avatar-circle">
												<img src="avatar.php?uid=<?php echo $value['uid']; ?>&size=middle&engine=<?php echo ENGINE; ?>" />
											</span>
										</div>
									</td>
									<td><?php echo $value['realname']; ?></td>
									<td><?php echo isset( $dept[$value['deptid']] ) ? $dept[$value['deptid']]['deptname'] : '—'; ?></td>
									<td><?php echo isset( $position[$value['positionid']] ) ? $position[$value['positionid']]['posname'] : '—'; ?></td>
									<td>
										<?php if ( $perManager && $value['perManager'] ): ?><a href="<?php echo $this->createUrl( 'user/edit', array( 'uid' => $value['uid'] ) ); ?>" class="cbtn o-edit"></a><?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<div class="page-list-footer">
				<?php
				if ( isset( $pages ) ) {
					$this->widget( 'IWPage', array( 'pages' => $pages ) );
				}
				?>
			</div>
		</div>
	</div>
</div>
<script>
	Ibos.app.setPageParam({
		selectedDeptId: <?php echo $deptId; ?>
	})
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_user_index.js?<?php echo VERHASH; ?>'></script>
