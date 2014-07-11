<form action="" id="handover_form" class="form-horizontal form-narrow">
	<div class="control-group">
		<label class="control-label"><?php echo $lang["Select flow"];?></label>
		<div class="controls">
			<input type="hidden" name="flowid" id="flow_select_input">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo $lang["Transactor"];?></label>
		<div class="controls">
			<input type="text" name="uid" id="work_handover_user_from">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo $lang["Trans object"];?></label>
		<div class="controls"><input type="text" name="toid" id="work_handover_user_to"></div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo $lang["Time scope"];?></label>
		<div class="controls">
			<div class="row">
				<div class="span6">
					<div class="datepicker" id="work_handover_date_from">
						<input type="text" class="datepicker-input" name="begin">
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
				</div>
				<div class="span6">
					<div class="datepicker" id="work_handover_date_to">
						<input type="text" class="datepicker-input" name="end">
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo $lang["Serial number range"];?></label>
		<div class="controls">
			<div class="row">
				<div class="span6"><input type="text" name="runbegin" /></div>
				<div class="span6"><input type="text" name="runend" /></div>
			</div>
		</div>
	</div>
	<input type="hidden" name="flowstr" id="flowstr" />
	<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />

</form>
<script>
	Ibos.app.setPageParam('flows', $.parseJSON('<?php echo addslashes(json_encode($flows));?>'));
	(function() {
		var flowData = Ibos.app.g('flows');
		$("#flow_select_input").ibosSelect({
			data: flowData,
			width: '100%',
			pinyin: true
		});

		var $userFrom = $("#work_handover_user_from"),
				$userTo = $("#work_handover_user_to"),
				$dateFrom = $("#work_handover_date_from"),
				$dateTo = $("#work_handover_date_to");

		$userFrom.add($userTo).userSelect({
			data: Ibos.data.get("user"),
			type: "user",
			maximumSelectionSize: "1"
		});

		$dateFrom.datepicker({target: $dateTo});
	})();
</script>