<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar goes here -->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Sidebar end -->
	<!-- Mainer right -->
	<div class="mcr">
		<!-- Mainer nav -->
		<div class="page-list">
			<div class="page-list-header">
				<div class="btn-toolbar pull-left">
					<button type="button" class="btn btn-primary pull-left <?php if ( !$perAdd ): ?>disabled<?php endif; ?>" data-action="addDept"><?php echo $lang['Add department']; ?></button>
				</div>
			</div>
			<div class="page-list-mainer org-dep-mainer">
				<table class="table table-hover org-dep-table" id="org_dep_table">
					<thead>
						<tr>
							<th><?php echo $lang['Department list']; ?></th>
							<th><?php echo $lang['Department manager']; ?></th>
							<th width="100"><?php echo $lang['Department type']; ?></th>
							<th width="60"></th>
						</tr>
					</thead>
					<tbody>
						<tr data-id='0' data-pid='0' data-node-type="departmentRow">
							<td>
								<a href='javascript:;' data-action='editDept' data-param='{"type": "Headquarters", "id": "0"}' class='org-dep-name'>
									<i class='os-company'></i>
									<span data-node-type="departmentName"><?php echo isset( $unit['fullname'] ) ? $unit['fullname'] : ''; ?></span>
								</a>
							</td>
							<td>--</td>
							<td><?php echo $lang['Headquarters']; ?></td>
							<td class='posr'></td>
						</tr>
						<?php
						Ibos::import( 'ext.Tree', true );
						if ( $perEdit ) {
							$editOp = "<a href='javascript:;' class='org-dep-btn' data-action='moveupDept' title='上移'><i class='glyphicon-arrow-up'></i></a>
									<a href='javascript:;' class='org-dep-btn' data-action='movedownDept' title='下移'><i class='glyphicon-arrow-down'></i></a>";
						} else {
							$editOp = '';
						}
						if ( $perDel ) {
							$delOp = "<a href='javascript:;' class='org-dep-btn' data-action='removeDept' title='删除'><i class='glyphicon-remove'></i></a>";
						} else {
							$delOp = '';
						}
						foreach ( $dept as $key => $value ) {
							$type = ($value['isbranch'] ? 'Branch' : 'Department');
							// 图标
							if($value['manager'] > 0){
								$manager = User::model()->fetchByUid($value['manager']);
								$value['manager'] = $manager['realname'];								
							}else{
								$value['manager'] =  '--';
							}
							$value['type'] = $type;
							$value['typeDesc'] = $lang[$type];
							$dept[$key] = $value;
						}
						$str = "
						<tr data-id='\$deptid' data-pid='\$pid' data-node-type='departmentRow'>
							<td>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; \$spacer<a href='javascript:;' data-action='editDept' data-param='{&quot;type&quot;: &quot;\$type&quot;, &quot;id&quot;: &quot;\$deptid&quot;}' class='org-dep-name'><i class='os-department'></i> <span data-node-type='departmentName'>\$deptname</span></a>
							</td>
							<td>\$manager</td>
							<td>\$typeDesc</td>
							<td>
								<div class='posr'>
									<div class='org-dep-operate'>
										{$editOp}
										{$delOp}
									</div>
								</div>	
							</td>
						</tr>";
						$categorys = StringUtil::getTree( $dept, $str );
						echo $categorys;
						?>
					</tbody>
				</table>
				<form action="" class="form-horizontal form-narrow">
					<!-- 新增 修改部门 -->
					<div id="dep_card" class="org-dep-card">
						<div class="org-dep-card-fold" data-action="cancelEditDept" data-param='{"type": "Department"}'></div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Up department']; ?></label>
							<div class="controls">
								<select data-mark="pid" id="pid">
									<option value="0" selected><?php echo $lang['Top department']; ?></option>
									<?php echo $tree; ?>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Department name']; ?></label>
							<div class="controls">
								<input type="text" data-mark="deptname" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Dept manager']; ?></label>
							<div class="controls">
								<input type="text" id="dep_manager" data-toggle="userSelect" data-mark="manager">
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label"><?php echo $lang['Dept leader']; ?></label>
							<div class="controls">
								<input type="text" id="superior_manager" data-toggle="userSelect" data-mark="leader">
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label"><?php echo $lang['Dept subleader']; ?></label>
							<div class="controls">
								<input type="text" id="superior_branched_manager" data-toggle="userSelect" data-mark="subleader">
							</div>
						</div>
						<div class="control-group" id="branch_intro">
							<label class="control-label"><?php echo $lang['As branch']; ?></label>
							<div class="controls">
								<input type="checkbox" data-toggle="switch" data-mark="isbranch">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Dept phone']; ?></label>
							<div class="controls">
								<input type="text" data-mark="tel">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Dept fax']; ?></label>
							<div class="controls">
								<input type="text" data-mark="fax">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Dept address']; ?></label>
							<div class="controls">
								<input type="text" data-mark="addr">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Dept duty']; ?></label>
							<div class="controls">
								<textarea rows="5" data-mark="func"></textarea>
							</div>
						</div>
						<div>
							<button type="button" class="btn btn-large btn-submit" data-action="cancelEditDept" data-param='{"type": "Department"}'><?php echo $lang['Cancel']; ?></button>
							<button type="button" class="btn btn-large btn-submit btn-primary pull-right <?php if ( !$perEdit || !$perAdd ): ?>disabled<?php endif; ?>" data-action="submitDept" data-param='{"type": "Department"}'><?php echo $lang['Save']; ?></button>
						</div>
					</div>
					<!-- 修改总部 -->
					<div id="head_dep_card" class="org-dep-card">
						<div class="org-dep-card-fold" data-action="cancelEditDept" data-param='{"type": "Headquarters"}'></div>
						<div class="control-group">
							<label for="" class="control-label"><?php echo $lang['Enterprise fullname']; ?></label>
							<div class="controls">
								<input type="text" data-mark="fullname" value="<?php echo $unit['fullname']; ?>" <?php if ( !empty( $license ) ): ?>disabled<?php endif; ?>>
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label"><?php echo $lang['Enterprise shortname']; ?></label>
							<div class="controls">
								<input type="text" data-mark="shortname" value="<?php echo $unit['shortname']; ?>" <?php if ( !empty( $license ) ): ?>disabled<?php endif; ?>>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Phone']; ?></label>
							<div class="controls">
								<input type="text" data-mark="phone" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Fax']; ?></label>
							<div class="controls">
								<input type="text" data-mark="fax" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Zipcode']; ?></label>
							<div class="controls">
								<input type="text" data-mark="zipcode" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Admin email']; ?></label>
							<div class="controls">
								<input type="text" data-mark="adminemail" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Address']; ?></label>
							<div class="controls">
								<input type="text" data-mark="address" />
							</div>
						</div>
						<div>
							<button type="button" class="btn btn-large btn-submit" data-action="cancelEditDept" data-param='{"type": "Headquarters"}'><?php echo $lang['Cancel']; ?></button>
							<button type="button" class="btn btn-large btn-submit btn-primary pull-right <?php if ( !$perEdit ): ?>disabled<?php endif; ?>" data-action="submitDept" data-param='{"type": "Headquarters"}'><?php echo $lang['Save']; ?></button>
						</div>
					</div>
					<input type="hidden" id="deptId" data-mark="deptid" />
				</form>
			</div>
			<div class="page-list-footer"></div>
		</div>
	</div>
</div>
<!-- Footer -->
<script>
	Ibos.app.setPageParam({
		"allowAddDept": <?php echo $perAdd; ?>,
		"allowEditDept": <?php echo $perEdit; ?>,
		"allowDeleteDept": <?php echo $perDel; ?>
	});
</script>
<script src='<?php echo STATICURL; ?>/js/lib/introjs/intro.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_department_index.js?<?php echo VERHASH; ?>'></script>
