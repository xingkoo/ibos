<div id="dialog_transfer">
	<form class="form-horizontal form-compact" method="post" action="<?php echo $this->getController()->createUrl("handle/freenext");?>" id="turn_next_form" name="turnNextForm">
		<div class="">
			<span class="step-title">步骤信息</span>
			<div class="step-choose">
				<?php foreach ($prcsUser as $k => $val ) : ?>
					<label class="checkbox mb">
						<span class="label working-step mls"><?php echo $lang["The"] . $k . $lang["Step"];?></span><?php echo $val["userName"];?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<div id="wfSetnext">
			<?php if ($preset != "") : ?>
				<div class="options-choose">
					<div class="mb step-title"><?php echo $lang["The"] . $processid . $lang["Step"];?>:(<?php echo $lang["Next step"];?>)<span style="color:red;"><?php echo $lang["The steps for the preset"];?></span></div>
					<div class="control-group">
						<?php echo $preset;?>
						<input type="hidden" name="preset" value="1" />
						<input type="hidden" name="prcsUser" value="1" />
						<input type="hidden" name="prcsUserOp" id="prcsUserOp" value="1" />
						<input type="hidden" name="topflag" value="1" />
					</div>
				</div>
			<?php else : ?>
				<div class="options-choose">
					<div class="mb step-title"><?php echo $lang["The"] . $processid . $lang["Step"];?>:(<?php echo $lang["Next step"];?>)</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Opuser relate"];?></label>
						<div class="controls">
							<select id="topflag" name="topflag">
								<option value="0"><?php echo $lang["Host"];?></option>
								<option value="1"><?php echo $lang["First receiver host"];?></option>
								<option value="2"><?php echo $lang["No host"];?></option>
							</select>
						</div>
					</div>
					<div id="user_select_over" class="control-group">
						<label class="control-label"><?php echo $lang["Host"];?></label>
						<div class="controls">
							<input type="text" id="prcsUserOp" name="prcsUserOp" class="hoster-select"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Agent"];?></label>
						<div class="controls">
							<input id="prcsUser" class="operator-select" name="prcsUser" type="text" />
							<br />
							<a href="javascript:;" class="xi2" onclick="snf.showitem()"><?php echo $lang["Set write field"];?></a>
						</div>
					</div>
				</div>
				<script>
					$("#topflag").on("change", function(){
						$(this).closest(".control-group").next().toggle(this.value === "0")
					});
					$('#prcsUserOp').userSelect({data: Ibos.data.get('user'), type: 'user', maximumSelectionSize: '1'});
					$('#prcsUser').userSelect({data: Ibos.data.get('user'), type: 'user'});
				</script>
			<?php endif; ?>
		</div>
		<?php if (($preset == "") && $freePreset) : ?>
			<div class="options-choose" id="wf_option">
				<div class="control-group">
					<label class="control-label"><?php echo $lang["Default step"];?></label>
					<div class="controls">
						<span style="margin-right: 20px;">
							<a onclick="snf.add_prcs(<?php echo $processid + 1;?>);" href="javascript:;" class="xi2">
								<i class="o-plus"></i>
								<?php echo $lang["Add next default step"];?>
							</a>
						</span>
						<span id="del_btn" style="display:none">
							<a type="button" onclick="snf.del_prcs();" href="javascript:;" class="xi2">
								<i class="o-trash"></i>
								<?php echo $lang["Del last default step"];?>
							</a>
						</span>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group remind-block">
			<label class="control-label"><?php echo $lang["Remind content"];?></label>
			<div class="controls">
				<textarea name="message" rows="3" class="reminds"><?php echo Ibos::lang("Turn remind", "", array("{runname}" => $runName, "{me}" => Ibos::app()->user->realname));?></textarea>
			</div>
		</div>
		<input type="hidden" name="op" value="<?php echo $op;?>" />
		<input type="hidden" name="key" value="<?php echo $key;?>" />
		<input type="hidden" name="topflagOld" value="<?php echo $topflag;?>">
		<input type="hidden" name="freeItemOld" value="<?php echo implode(",", $itemArr);?>">
        <input type="hidden" id="tmpCount" value="" />
		<input type="hidden" name="lineCount" id="lineCount" value="0">
		<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>">
	</form>
	<div id="set_item" style="display:none;">
		<div class="wf_set_item_body">
			<table width="100%" class="tbl">
				<tr>
					<th><b><?php echo $lang["This step can write field"];?></b></th>
					<th>&nbsp;</th>
					<th><b><?php echo $lang["Optional field"];?></b></th>
				</tr>
				<tr>
					<td>
						<select name="select1" id="select1" ondblclick="snf.func_delete();" MULTIPLE style="width:200px;height:250px;"></select>
						<p style="margin-top: 5px;"><button type="button" onclick="snf.func_select_all1();" class="btn"><?php echo $lang["Select all"];?></button></p>
					</td>
					<td>
						<a onclick="snf.func_insert();"><i class="o-prev"></i></a>
						<br><br>
						<a onclick="snf.func_delete();"><i class="o-next"></i></a>
					</td>
					<td>
						<select name="select2" id="select2" ondblclick="snf.func_insert();" MULTIPLE style="width:200px;height:250px">
							<?php for ($i = 0; $i < $itemCount; $i++) : ?>
								<?php $itemName = $itemArr[$i]; ?>
								<?php if (($itemName != "[A@]") && ($itemName != "[B@]")) : ?>
									<option value='<?php echo $itemName;?>'><?php echo $itemName;?></option>
								<?php elseif ($itemName == "[B@]") : ?>
									<option value='[B@]'><?php echo $lang["Num slash name"];?></option>
								<?php elseif ($itemName == "[A@]") : ?>
									<option value='[A@]'><?php echo $lang["Flow global attach"];?></option>
								<?php endif; ?>
							<?php endfor; ?>
						</select>
						<p style="margin-top: 5px;"><button type="button" onclick="snf.func_select_all2();" class="btn"><?php echo $lang["Select all"];?></button></p>
					</td>
				</tr>
				<tr>
					<td align="center" colspan="3"><?php echo $lang["Select all tip"];?></td>
				</tr>
			</table>
		</div>
	</div>
</div>
<script type="text/ibos-template" id="def_tpl">
	<div class="options-choose" id="wfSetnext_<%=prcsId%>">
	<div class="mb step-title"><?php echo $lang["The"];?><%=prcsId%><?php echo $lang["Step"];?>:(<?php echo $lang["Default step"];?>)</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Opuser relate"];?></label>
			<div class="controls">
				<select name="topflag<%=id%>" id="topflag<%=id%>">
					<option value="0"><?php echo $lang["Host"];?></option>
					<option value="1"><?php echo $lang["No host"];?></option>
					<option value="2"><?php echo $lang["First receiver host"];?></option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Host"];?></label>
			<div class="controls">
				<input type="text" id="prcsUserOp<%=id%>" name="prcsUserOp<%=id%>" class="hoster-select"/>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang["Agent"];?></label>
			<div class="controls">
				<input id="prcsUser<%=id%>" class="operator-select" name="prcsUser<%=id%>" type="text" />
			</div>
		</div>
	</div>
</script>
<script>
	var processId = <?php echo $processid;?>;
	var lineCount = 0;
	var lineBegin = 0;
	function sncheckform() {
		for (var i = 0; i <= lineCount; i++) {
			var line = processId + i;
			if (i == 0) {
				var str = "";
			} else {
				var str = i;
			}
			//检查主办人
			if (document.getElementById('prcsUserOp' + str).value == "" && eval('document.turnNextForm.topflag' + str + '.value==0')) {
				Ui.tip('请为第' + line + '步指定主办人！', 'danger');
				return false;
			}
		}
		<?php if ($notAllFinished != "") : ?>
			var msg = "<?php echo $lang["Agent user"];?> [<?php echo $notAllFinished;?>] <?php echo $lang["Confirm not finished submit"];?>";
			if (!window.confirm(msg)) {
				return (false);
			}
		<?php endif; ?>
			return true;
	}
	var snf = {
		/**
		 * 增加步骤
		 */
		add_prcs: function(line) {
			if (lineCount == 0) {
				lineBegin = line;
				$("#del_btn").show(); //删除最后一个预设步骤按钮
			}
			lineCount++;
			$("#lineCount").val(lineCount);
			line = lineBegin + lineCount - 1;
			var tpl = $.template("def_tpl", {prcsId: line, id: lineCount}), node = $(tpl);
			$('#wfSetnext').append(node);
			// 主办类型
			$('#topflag' + lineCount).on("change", function(){
				$(this).closest(".control-group").next().toggle(this.value === "0");
			})
			// 主办人
			$('#prcsUserOp' + lineCount).userSelect({data: Ibos.data.get('user'), type: 'user', maximumSelectionSize: '1'});
			// 经办人
			$('#prcsUser' + lineCount).userSelect({data: Ibos.data.get('user'), type: 'user'});
		},
		/**
		 * 删除步骤
		 */
		del_prcs: function() {
			if (lineCount > 0) {
				var line = lineBegin + lineCount - 1;
				$('#wfSetnext_' + line).fadeOut('fast', function() {
					$('#wfSetnext_' + line).slideUp('slow', function() {
						$(this).remove();
					});
				});
				lineCount--;
				$("#lineCount").val(lineCount);
			}
			if (lineCount == 0) {
				$("#del_btn").hide();
			}
		},
		/**
		 *  可写字段
		 */
		showitem: function(count) {
			document.getElementById('select1').options.length = 0; //将可写字段下拉框重新置为空
			if (typeof count == 'undefined') {
				count = '';
			}
			document.getElementById('tmpCount').value = count;
			var obj = document.getElementById('freeItem' + count);
			if (obj && obj.value != "") {
				var item_arr = obj.value.split(",");
				for (i = 0; i < item_arr.length - 1; i++) {
					if (item_arr[i] != "") {
						var my_option = document.createElement("option");
						my_option.value = item_arr[i];
						my_option.text = item_arr[i];
						if (my_option.text == "[B@]") {
							my_option.text = "[<?php echo $lang["Num slash name"];?>]";
						}
						document.getElementById('select1').appendChild(my_option);
					}
				}
			}
			Ui.dialog({
				title: '<?php echo $lang["Set write field"];?>',
				width: '550',
				height: 'auto',
				modal: true,
				content:$('#set_item').html(),
				ok: function() {
					snf.set_item($('#tmpCount').val());
				},
				cancel:true
			});
		},
		/**
		 * 插入一项备选字段
		 */
		func_insert: function() {
			var item_str = "";
			for (var i = document.getElementById('select1').options.length - 1; i >= 0; i--) {
				item_str += document.getElementById('select1').options[i].value + ",";
			}
			for (var i = document.getElementById('select2').options.length - 1; i >= 0; i--) {
				var option_text = document.getElementById('select2').options[i].text;
				var option_value = document.getElementById('select2').options[i].value;
				if (document.getElementById('select2').options[i].selected && item_str.indexOf(option_value + ",") < 0) {
					var my_option = document.createElement("option");
					my_option.text = option_text;
					my_option.value = option_value;
					document.getElementById('select1').appendChild(my_option);
				}
			}//for
		},
		/**
		 * 删除一项备选字段
		 */
		func_delete: function() {
			for (i = document.getElementById('select1').options.length - 1; i >= 0; i--) {
				if (document.getElementById('select1').options[i].selected) {
					document.getElementById('select1').removeChild(document.getElementById('select1').options[i]);
				}
			}//for
		},
		/**
		 * 可写字段全选
		 */
		func_select_all1: function() {
			for (i = document.getElementById('select1').options.length - 1; i >= 0; i--) {
				document.getElementById('select1').options[i].selected = true;
			}
		},
		/**
		 * 备选字段全选
		 */
		func_select_all2: function() {
			for (i = document.getElementById('select2').options.length - 1; i >= 0; i--) {
				document.getElementById('select2').options[i].selected = true;
			}
		},
		/**
		 * 设置字段
		 */
		set_item: function(count) {
			if (typeof count == 'undefined') {
				count = "";
			}
			$("#tmp_count").val(count);
			var freeItem = document.getElementById('freeItem' + count);
			if (!freeItem) {
				freeItem = document.createElement("input");
				freeItem.type = "hidden";
				freeItem.name = "freeItem" + count;
				freeItem.id = "freeItem" + count;
				freeItem = document.turnNextForm.appendChild(freeItem);
			}
			var fldStr = "";
			for (var i = 0; i < document.getElementById('select1').options.length; i++) {
				var options_value = document.getElementById('select1').options[i].value;
				fldStr += options_value + ",";
			}
			freeItem.value = fldStr;
			$('#set_item').dialog('close');
		}
	};
</script>