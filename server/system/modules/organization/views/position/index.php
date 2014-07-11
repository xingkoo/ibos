<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<!-- Mainer nav -->
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<div class="btn-group">
						<button type="button" onclick="location.href = '<?php echo $this->createUrl( 'position/add', array( 'catid' => $catid ) ); ?>';" class="btn btn-primary" id="add_position"><?php echo $lang['Add position']; ?></button>
					</div>
					<div class="btn-group">
						<button type="button" data-action="removePositions" class="btn"><?php echo $lang['Delete']; ?></button>
					</div>
				</div>
				<form method="post" action="<?php echo $this->createUrl( 'position/index' ); ?>">
					<div class="search search-config pull-right span4">
						<input type="text" name="keyword" placeholder="<?php echo $lang['Position search tip']; ?>" id="mn_search" nofocus />
						<a href="javascript:;">search</a>
					</div>
					<input type="hidden" name="search" value="1" />
					<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
				</form>
			</div>
			<div class="page-list-mainer">
				<?php if ( !empty( $list ) ): ?>
					<table class="table table-striped table-hover org-positon-table" id="org_position_table">
						<thead>
							<tr>
								<th width="20">
									<label class="checkbox">
										<input type="checkbox" data-name="positionid">
									</label>
								</th>
								<th><?php echo $lang['Position name']; ?></th>
								<th><?php echo $lang['Position category']; ?></th>
								<th width="100"><?php echo $lang['Position users']; ?></th>
								<th width="100"><?php echo $lang['Operation']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $list as $key => $value ) : ?>
								<tr id="pos_<?php echo $value['positionid']; ?>">
									<td>
										<label class="checkbox">
											<input type="checkbox" name="positionid" value="<?php echo $value['positionid']; ?>" />
										</label>
									</td>
									<td><a href="#" class="anchor"><?php echo $value['posname']; ?></a></td>
									<td><?php echo isset($catData[$value['catid']]) ? $catData[$value['catid']]['name'] : '--'; ?></td>
									<td><?php echo $value['num']; ?></td>
									<td>
										<a href="<?php echo $this->createUrl( 'position/edit', array( 'id' => $value['positionid'] ) ); ?>" class="cbtn o-edit" title="<?php echo $lang['Edit']; ?>"></a>
										<a href="javascript:;" data-action="removePosition" data-param='{"id": "<?php echo $value['positionid']; ?>"}' class="cbtn o-trash mls" title="<?php echo $lang['Delete']; ?>"></a>
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
		'catId': <?php echo $catid; ?>
	})
</script>
<script src='<?php echo STATICURL ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_position_index.js?<?php echo VERHASH; ?>'></script>
