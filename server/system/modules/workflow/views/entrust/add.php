<div>
	<form class="form-horizontal form-compact" id="entrust_form" method="post" action="<?php echo $this->createUrl("entrust/add");?>">
		<div class="step-content">
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["Select process"];?></label>
				<div class="controls">
					<input type="hidden" name="flowid" id="flow_select_input">
				</div>
			</div>
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["Be entrust user"];?></label>
				<div class="controls">
					<input type="text" name="uid" id="be_entrust_user" />
				</div>
			</div>
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["Effective date"];?></label>
				<div class="controls">
					<div class="datepicker" id="entrust_date_begin">
						<input type="text" class="datepicker-input" name="begindate">
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
				</div>
			</div>
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["End date"];?></label>
				<div class="controls">
					<div class="datepicker" id="entrust_date_end">
						<input type="text" class="datepicker-input" name="enddate">
						<a href="javascript:;" class="datepicker-btn"></a>
					</div>
				</div>
			</div>
			<div class="control-group"> 
				<label class="control-label"><?php echo $lang["Always effective"];?></label>
				<div class="controls">
					<input id="always_effected" type="checkbox" value="1" data-toggle="switch" />
				</div>
			</div>
		</div>
        <input type="hidden" name="formhash" value="<?php echo FORMHASH;?>">
	</form>
</div>
<script>
	Ibos.app.setPageParam("flowlist", $.parseJSON('";
addslashes(json_encode($flowlist));
"'));
	var $select = $("#flow_select_input"),
		$dateBegin = $('#entrust_date_begin'),
		$dateEnd = $('#entrust_date_end'),
		$user = $('#be_entrust_user');
	function postCheck() {
		if ($select.val() == "") {
			Ui.tip(U.lang('WF.PLEASE_SELECT_PROCESS'), 'danger');
			return false;
		}
		if ($user.val() == "") {
			Ui.tip(U.lang('WF.DESIGNATED_THE_CLIENT'), 'danger');
			return false;
		}
		var beginDateVal = $dateBegin.find('input').val();
		var endDateVal = $dateEnd.find('input').val();
		if ($('#always_effected').prop('checked') == false) {
			if (beginDateVal !== '' && endDateVal !== '') {
				var beginDate = Date.parse(beginDateVal);
				var endDate = Date.parse(endDateVal);
				if (beginDate > endDate) {
					Ui.tip(U.lang('BEGIN_GREATER_THAN_END'), 'danger');
					return false;
				}
			} else if (beginDateVal == '' && endDateVal == '') {
				Ui.tip(U.lang('WF.TIME_CANNOT_BE_EMPTY'), 'danger');
				return false;
			}
		}
		return true
	}
	$(function() {
		$select.ibosSelect({
			data: Ibos.app.g("flowlist"),
			width: '100%',
			multiple: false
		});
		$user.userSelect({
			data: Ibos.data.get("user"),
			type: "user",
			maximumSelectionSize: "1",
		});

		$dateBegin.datepicker({target: $dateEnd});
		$('#always_effected').iSwitch().on('change', function() {
			if (this.checked) {
				$dateBegin.datepicker("destroy").find('input').val('').addClass('readonly');
				$dateEnd.datepicker("destroy").find('input').val('').addClass('readonly');
			} else {
				$dateBegin.find('input').val('').removeClass('readonly');
				$dateEnd.find('input').val('').removeClass('readonly');
				$dateBegin.datepicker({target: $dateEnd});
			}
		});
	});
</script>
