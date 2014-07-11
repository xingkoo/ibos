<form class="form-horizontal" id="new_form" action="javascript:;">
	<div id="new_dialog">
		<?php if (in_array($flow["autoedit"], array(2, 4))) : ?>
			<div class="crea-prefix">
				<span class="pre-label"><?php echo $lang["Prefix"];?></span>
				<input type="text" name="prefix" class="prefix-con" id="runPrefix"/>
			</div>
		<?php endif; ?>
		<div>
			<span class="refer-label"><?php echo $lang["Num slash name"];?></span>
			<input type="text" name="name" value="<?php echo $runName;?>" <?php if ($flow["autoedit"] !== "1") : ?>readonly class="refer-con disabled"<?php else : ?>class="refer-con"<?php endif; ?> id="runName" />
		</div>
		<?php if (in_array($flow["autoedit"], array(3, 4))) : ?>
			<div class="crea-prefix">
				<span class="pre-label"><?php echo $lang["Suffix"];?></span>
				<input type="text" name="suffix" class="prefix-con" id="runSuffix"/>
			</div>
		<?php endif; ?>
	</div>
	<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
</form>
<script>
	function newFlowSubmitCheck() {
		<?php if ($flow["forcepreset"] == "1") : ?>
			var $prefix = $('#runPrefix'), $suffix = $('#runSuffix');
			if ($prefix.is('input')) {
				if ($prefix.val() === '') {
					Ui.tip(U.lang('WF.PREFIX_CANNOT_BE_EMPTY'), 'danger');
					$prefix.blink();
					return false;
				}
			}
			if ($suffix.is('input')) {
				if ($suffix.val() === '') {
					Ui.tip(U.lang('WF.SUFFIX_CANNOT_BE_EMPTY'), 'danger');
					$suffix.blink();
					return false;
				}
			}
		<?php endif; ?>
		var $runname = $('#runName');
		if ($runname.val() === '') {
			Ui.tip(U.lang('WF.RUNNAME_CANNOT_BE_EMPTY'), 'danger');
			$runname.blink();
			return false;
		}
		return true;
	}
	$('#new_dialog').find('input:first').focus();
</script>