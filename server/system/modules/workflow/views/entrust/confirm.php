<div id="dialog_entrust">
	<form class="form-horizontal form-compact" id="entrust_form" method="post" action="<?php echo $this->createUrl("entrust/confirmPost");?>">
		<span id="dialog_title"><?php echo $lang["Serial number"];?>:<?php echo $runID;?> - <?php echo $runName;?></span>
		<div class="step-content">
			<table class="entrust-dialog-tab">
				<thead>
				<th width="30"></th>
				<th width="150"></th>
				<th width="200"></th>
				</thead>
				<tbody>
					<?php foreach ($list as $index => $val ) : ?>
						<?php if ($val["count"] == 1) : ?>
							<tr>
								<?php if (($index != $processID) || ($val["flowprocess"] != $flowProcess)) : ?>
									<td>
										<span class="label l-pre-step"><?php echo $index;?></span>
									</td>
									<td>
										<span><?php echo $prcsName[$val["flowprocess"]]?></span>
									</td>
								<?php else : ?>
									<td>
										<span class="label l-cur-step"><?php $index;?></span>
									</td>
									<td>
										<span><?php $prcsName[$val["flowprocess"]];?></span>
									</td>
								<?php endif; ?>
								<td><span class="fss"><?php $val["userName"];?></span></td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["Entrust to"];?></label>
				<div class="controls">
					<input type="text" id="prcs_other" name="prcs_other" class="entruster"/>
				</div>
			</div>
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["Remind content"];?></label>
				<div class="controls">
					<input type="text" name="message" placeholder="<?php echo $lang["Enturst Remind desc"];?>" class="remind-content"/>
				</div>
			</div>
		</div>
		<input type="hidden" name="key" value="<?php echo $key;?>">
        <input type="hidden" name="opflag" value="<?php echo $opflag;?>">
        <input type="hidden" name="oldUid" value="<?php echo $oldUid;?>">
        <input type="hidden" name="formhash" value="<?php echo FORMHASH;?>">
	</form>
</div>
<script>
	$(function() {
		var prcsData = "<?php echo $prcsUser;?>";
		$('#prcs_other').userSelect({
			box: $('<div id="prcs_other_box"></div>').appendTo(document.body),
			data: Ibos.data.includes(prcsData),
			type: 'user',
			maximumSelectionSize: '1'
		});
		$('#entrust_form').on('submit', function() {
			if (this.prcs_other.value == '') {
				Ui.tip(U.lang('WF.DESIGNATED_THE_PRINCIPAL'), 'danger');
				return false;
			}
			return true;
		});
	});
</script>