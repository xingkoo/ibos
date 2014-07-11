<div id="dialog_transfer">
	<form class="form-horizontal form-compact" method="post" action="<?php echo $this->createUrl("handle/turnNextPost");?>" id="turn_next_form" name="turn_next_form">
		<div class="">
			<span class="step-title">选择下一步骤</span>
			<div class="step-choose">
				<?php foreach ($list as $key => $val ) : ?>
					<?php if ($val["isover"]) : ?>
						<label class="checkbox mb" id="prcs_title_<?php echo $key;?>" data-click="view_user_table" data-key="<?php echo $key;?>">
							<input type="checkbox" class="mls" name="prcs_check" id="prcs_check_<?php echo $key;?>" <?php echo $val["checked"] == "true" ? "checked" : "";?> />
							<span class="label working-step mls"><?php echo $lang["Form endflow"];?></span><?php echo $val["prcsname"];?>
						</label>
					<?php else : ?>
						<?php if (!isset($val["notpass"])) : ?>
							<label class="checkbox mb" id="prcs_title_<?php echo $key;?>" <?php if ($process["syncdeal"] !== "2") : ?>data-click="view_user_table" data-key="<?php echo $key;?>"<?php else : ?>data-click="view_user_table_all"<?php endif; ?> >
								<input type="checkbox" class="mls" name="prcs_check" id="prcs_check_<?php echo $key;?>" <?php echo $val["checked"] == "true" ? "checked" : "";?> />
								<span class="label working-step mls"><?php echo $prcsto[$key];?></span><?php echo $val["prcsname"];?>
							</label>
						<?php else : ?>
							<label class="checkbox mb" id="prcs_title_<?php echo $key;?>" title="<?php echo $val["notpass"];?>">
								<input type="checkbox" disabled class="mls" name="prcs_check" id="prcs_check_<?php echo $key;?>" <?php echo $val["checked"] == "true" ? "checked" : "";?> />
								<span class="label noconform-step mls"><?php echo $prcsto[$key];?></span><?php echo $val["prcsname"] . $lang["Mismatch condition"];?>
							</label>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ($process["syncdeal"] == "1") : ?>
			<div class="control-group">
				<label class="control-label">&nbsp;</label>
				<div class="controls">
					<a href="javascript:view_user_table_all();"><?php echo $lang["Select all work"];?></a>
					&nbsp;
					<a href="javascript:view_user_table_unall();"><?php echo $lang["Unselect all"];?></a>
				</div>
			</div>
		<?php elseif ($process["syncdeal"] == "2") : ?>
			<div class="control-group">
				<label class="control-label">&nbsp;</label>
				<div class="controls">
					<span class="label label-important"><?php echo $lang["This step for forced concurrent"];?></span>
				</div>
			</div>
		<?php endif; ?>
		<div class="options-choose">
			<div class="mb step-title"><?php echo $lang["Opuser relate"];?></div>
			<?php foreach ($list as $key => $val ) : ?>
				<?php if ($val["isover"]) : ?>
					<?php if (!empty($prcsback)) : ?>
						<div id="user_select_over" class="control-group" <?php if (!$val["display"]) echo 'style="display:none;"';?> >
							<label class="control-label"><?php echo $lang["Host"];?></label>
							<div class="controls">
								<input type="hidden" name="topflag" value="0">
								<input type="text" id="prcs_user_op" name="prcs_user_op" value="<?php echo $val["prcsopuser"];?>" class="hoster-select"/>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang["Agent"];?></label>
							<div class="controls">
								<input id="prcs_user" name="prcs_user"  value="<?php echo $val["backuser"];?>" type="text" />
							</div>
						</div>
						<script>
							$(function() {
								var prcsData = <?php echo $val["prcsusers"];?>;
								$('#prcs_user_op').userSelect({
									box: $('<div id="prcs_user_op_box"></div>').appendTo(document.body),
									data: Ibos.data.includes(prcsData),
									type: 'user',
									maximumSelectionSize: '1'
								});
								$('#prcs_user').userSelect({
									box: $('<div id="prcs_user_box"></div>').appendTo(document.body),
									data: Ibos.data.includes(prcsData),
									type: 'user'
								});
							});
						</script>
					<?php endif; ?>
				<?php else : ?>
					<?php if (!isset($val["notpass"])) : ?>
						<?php echo $val["selectstr"];?>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="control-group step-select">
				<label class="control-label"><?php echo $lang["Transaction to remind"];?></label>
				<div class="controls">
					<ul class="list-inline work-remind">
						<li>
							<label class="checkbox">
								<input type="checkbox" name="remind[1]" checked value="1"/><?php echo $lang["Next step host"];?>
							</label>
						</li>
						<li>
							<label class="checkbox">
								<input type="checkbox" name="remind[2]" value="2"/><?php echo $lang["Originator"];?>
							</label>
						</li>
						<li>
							<label class="checkbox">
								<input type="checkbox" name="remind[3]" value="3"/><?php echo $lang["All agent"];?>
							</label>
						</li>
					</ul>
				</div>
			</div>
			<div class="control-group remind-block">
				<label class="control-label"><?php echo $lang["Remind content"];?></label>
				<div class="controls">
					<textarea name="message" rows="3" class="reminds"><?php echo Ibos::lang("Turn remind", "", array("{runname}" => $run["name"], "{me}" => Ibos::app()->user->realname));?></textarea>
				</div>
			</div>
		</div>
		<input type="hidden" name="runid" value="<?php echo $runid;?>" />
		<input type="hidden" name="flowid" value="<?php echo $flowid;?>" />
		<input type="hidden" name="processid" value="<?php echo $processid;?>" />
		<input type="hidden" name="flowprocess" value="<?php echo $flowprocess;?>" />
		<input type="hidden" name="processto" value="<?php echo $process["processto"];?>" />
		<input type="hidden" name="prcsback" value="<?php echo $prcsback;?>" />
		<input type="hidden" name="prcs_choose" value="">
		<input type="hidden" name="op" value="<?php echo $op;?>">
		<input type="hidden" name="topflag" value="<?php echo $topflag;?>">
		<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>">
	</form>
</div>
<script>
	var line_choose;
	var count = <?php echo $count;?>;
	var stop = "<?php echo $prcsStop;?>";
	var runparent = <?php echo $run["parentrun"];?>;
	var prcsback = '<?php echo $prcsback;?>';
	function _id(dom) {
		return document.getElementById(dom);
	}
	/**
	 * 初始化可见元素
	 **/
	function init_user_table() {
		for (var i = 0; i < count; i++) {
			var obj = _id("user_select_" + i);
			var obj_check = _id("prcs_check_" + i);
			if (obj_check) {
				var status = obj_check.checked;
				if (status) {
					_id("prcs_title_" + i).style.color = "#49A2DF";
					if (i == stop) {
						line_choose = i;
						break;
					}
					if (obj) {
						obj.style.display = "";
					}
				}
			}
		}
	}

	/**
	 * 选中美化后的checkbox和真实的checkbox；
	 **/
	function check(obj) {
		$(obj).label("check");
	}
	/**
	 * 反之
	 **/
	function uncheck(obj) {
		$(obj).label("uncheck")
	}

	/**
	 * 选中步骤后的处理函数
	 **/
	function view_user_table(line_count) {
		line_choose = line_count;

<?php if (intval($process["syncdeal"]) == 0) : ?>
			//----- 非并发-----
			for (var i = 0; i < count; i++) {
				var obj = _id("user_select_" + i); //每一个步骤对应的主办人及经办人选项
				if (i == line_count) {       //如果只有一个步骤，只能是选中状态
					_id("prcs_title_" + i).style.color = "#49A2DF";
					if (_id("prcs_check_" + i))
						check(_id("prcs_check_" + i));

					if (i == stop && runparent !== 0 && prcsback !== '') {
						var obj_over = _id("user_select_over");
						if (obj_over)
							obj_over.style.display = "";
					} else {
						if (obj)
							obj.style.display = "";
					}
				} else { //反之，切换值和状态还有可见元素
					_id("prcs_title_" + i).style.color = "";
					if (_id("prcs_check_" + i)) {
						uncheck(_id("prcs_check_" + i));
					}
					if (i == stop && runparent !== 0 && prcsback !== '') {
						var obj_over = _id("user_select_over");
						if (obj_over) {
							obj_over.style.display = "none";
						}
					} else {
						if (obj) {
							obj.style.display = "none";
						}
					}
				}
			}
<?php else : ?>
			//----- 并发-----
			if (line_count !== stop) { //非点结束
				var obj = _id("prcs_check_" + line_count);
				if (obj.checked) {
					uncheck(obj);
					if (_id("user_select_" + line_count)) {
						_id("user_select_" + line_count).style.display = 'none';
					}
					_id("prcs_title_" + line_count).style.color = "";
				} else {
					check(obj);
					if (_id("user_select_" + line_count)) {
						_id("user_select_" + line_count).style.display = '';
					}
					_id("prcs_title_" + line_count).style.color = "#49A2DF";
				}
				var obj = _id("prcs_check_" + stop);
				if (obj) {
					uncheck(obj);
					_id("prcs_title_" + stop).style.color = "";
				}
			} else {//点结束
				for (var i = 0; i < count; i++) {
					var obj1 = _id("prcs_title_" + i);
					var obj2 = _id("prcs_check_" + i);
					if (i == line_count) {//结束选区
						obj1.style.color = "red";
						if (obj2) {
							check(obj2);
							if (_id("user_select_" + i)) {
								_id("user_select_" + i).style.display = '';
							}
						}
					} else {
						if (obj1) {
							obj1.style.color = "";
						}
						if (obj2) {
							uncheck(obj2);
							if (_id("user_select_" + i)) {
								_id("user_select_" + i).style.display = 'none';
							}
						}
					}
				}
			}
<?php endif; ?>
	}

	//全选所有步骤
	function view_user_table_all() {
		for (var i = 0; i < count; i++) {
			if (!_id("prcs_check_" + i).checked) {
				view_user_table(i);
			}
		}
	}
	//反选所有步骤
	function view_user_table_unall() {
		for (var i = 0; i < count; i++) {
			var obj = _id("user_select_" + i);
			if (obj) {
				if (_id("prcs_check_" + i).checked) {
					view_user_table(i);
				}
			}
		}
	}

	/**
	 * 下一步动作提交前检查
	 */
	function sncheckform() {
		var msg = '';
		//结束流程
		if (line_choose == stop) {
			//检查有无尚未办理完毕的经办人
<?php if (($notAllFinished != "") && ($process["turnpriv"] == "0")) : ?>
				alert(U.lang('WF.AGENT') + '<?php echo $notAllFinished;?>' + U.lang('WF.NOT_ALL_FINISHED_DESC'));
				return false;
<?php else : ?>
	<?php if (($notAllFinished != "") && ($process["turnpriv"] == "1")) : ?>
				msg += U.lang('WF.AGENT') + '[<?php echo $notAllFinished;?>]' + U.lang('WF.NOT_ALL_FINISHED_DESC');
	<?php endif; ?>
				msg += U.lang('WF.CONFIRM_END_FLOW');
				if (!window.confirm(msg)) {
					return false;
				}
<?php endif; ?>
		} else { //转交
			/* if(document.wfnext.message.value=="" && (document.wfnext.nextuser.checked || document.wfnext.beginuser.checked || document.wfnext.alluser.checked )) {
			 $("body").iTips({content:'{lang message_cannot_be_empty}',css:'warning'});
			 return false;
			 }*/
			var obj;
			document.turn_next_form.prcs_choose.value = "";
			for (var i = 0; i < count; i++) {
				obj = _id("prcs_check_" + i);
				if (obj) {
					if (obj.checked) {
						//所选步骤的主办人
						if (eval('document.turn_next_form.topflag' + i) && eval('document.turn_next_form.topflag' + i + '.value==0') && _id("prcs_user_op" + i).value == "") {
							Ui.tip(U.lang('WF.APPOINT_THE_HOST'), 'danger');
							return false;
						}
						document.turn_next_form.prcs_choose.value += i + ",";
					}
				}
			}//for
			if (document.turn_next_form.prcs_choose.value == "") {
				Ui.tip(U.lang('WF.ATLEAST_SELECT_ONE_STEP'), 'danger');
				return false;
			}
<?php if (($notAllFinished != "") && ($process["turnpriv"] == "1")) : ?>
				msg = U.lang('WF.AGENT') + '[<?php echo $notAllFinished;?>]' + U.lang('WF.NOTFINISHED_CONFIRM')
				if (!window.confirm(msg)) {
					return false;
				}
<?php else : ?>
	<?php if (($notAllFinished != "") && ($process["turnpriv"] == "0")) : ?>
				alert(U.lang('WF.AGENT') + '<?php echo $notAllFinished;?>' + U.lang('WF.NOT_ALL_FINISHED_DESC'));
				return false;
	<?php endif; ?>
<?php endif; ?>
		}//turn
		return true;
	}

	$(document).ready(function() {
		$('#turn_next_form').find('input[type=checkbox]').label();
		init_user_table();
		/*if (_id('prcs_user_op')) {
			$('#prcs_user_op').userSelect({
				type: 'user',
				box: $('<div></div>').appendTo(document.body),
				maximumSelectionSize: '1',
				data: Ibos.data.get('user')
			});
			$('#prcs_user').select2({
				type: 'user',
				box: $('<div></div>').appendTo(document.body),
				data: Ibos.data.get('user')
			});
		}*/

		$('[data-click="view_user_table"]').on('click', function() {
			var key = $(this).data('key');
			view_user_table(key);
		});
		$('[data-click="view_user_table_all"]').on('click', function() {
			var key = $(this).data('key');
			view_user_table_all(key);
		});
	});
</script>