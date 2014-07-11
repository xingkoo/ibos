<form action="" id="crontab_form">
	<table class="table table-striped table-head-condensed table-head-inverse mbz" style="width: 740px;">
		<thead>
			<tr>
				<th><?php echo $lang["Initiator"];?></th>
				<th width="120"><?php echo $lang["Remind type"];?></th>
				<th width="200"><?php echo $lang["Start time"];?></th>
				<th width="40"></th>
			</tr>
		</thead>
		<tbody id="crontab_body"></tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<a href="javascript:;" id="add_crontab">
						<i class="cbtn o-plus"></i>
						<?php echo $lang["Add one item"];?>
					</a>
				</td>
			</tr>
		</tfoot>
	</table>
	<input type="hidden" name="delid" id="delid" />
	<input type="hidden" name="flowid" value="<?php echo $this->flowid;?>" />
	<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
</form>
<script type="text/template" id="tpl_crontab">
	<tr data-id="<%=id%>">
	<td>
	<input type="text" name="uid[<%=id%>]" data-toggle="userSelect" value="<%=value%>">
	</td>
	<td>
	<select name="type[<%=id%>]" data-node="period">
	<option value="1" <% if(period=='1') { %>selected<% } %> ><?php echo $lang["Timer Only this time"];?></option>
	<option value="2" <% if(period=='2') { %>selected<% } %> ><?php echo $lang["Timer everyday"];?></option>
	<option value="3" <% if(period=='3') { %>selected<% } %> ><?php echo $lang["Timer each week"];?></option>
	<option value="4" <% if(period=='4') { %>selected<% } %> ><?php echo $lang["Timer each month"];?></option>
	<option value="5" <% if(period=='5') { %>selected<% } %> ><?php echo $lang["Timer each year"];?></option>
	</select>
	</td>
	<td data-node="date"></td>
	<td><a href="javascript:;" class="cbtn o-trash" data-id="<%=id%>"></a></td>
	</tr>
</script>
<script>
	(function() {
		var crontabList = new Ibos.List($("#crontab_body"), "tpl_crontab");
		var createDateNode = (function() {
			var _createWeekOption = function(selected) {
				var tpl = "";
				selected = selected || 0;
				for (var i = 0; i < 7; i++) {
					tpl += '<option value="' + i + '" ' + (i == selected ? "selected" : "") + '>星期' + ("日一二三四五六").charAt(i) + '</option>';
				}
				return tpl;
			};
			var _createDayOption = function(selected) {
				var tpl = "";
				selected = selected || 1;
				for (var i = 1; i <= 31; i++) {
					tpl += '<option value="' + i + '" ' + (i == selected ? "selected" : "") + '>' + i + '号</option>';
				}
				return tpl;
			};
			var _datepickerTpl = '<div class="datepicker" data-toggle="datePicker">' +
					'<input type="text" class="datepicker-input" name="remindtime[<%=id%>]" value="<%=date%>">' +
					'<a href="javascript:;" class="datepicker-btn" ></a>' +
					'</div>' +
					'</div>';

			var _tpl = {
				1: _datepickerTpl, // 仅此一次
				2: _datepickerTpl, // 每日
				3: '<div class="row" data-period="3">' + 
						'<div class="span6">' +
						'<select name="reminddate[<%=id%>]"><%=weekOption%></select>' +
						'</div>' +
						'<div class="span6">' +
						_datepickerTpl +
						'</div>' +
						'</div>',
				4: '<div class="row" data-period="4">' +
						'<div class="span6">' +
						'<select name="reminddate[<%=id%>]"><%=monthOption%></select>' +
						'</div>' +
						'<div class="span6">' +
						_datepickerTpl +
						'</div>' +
						'</div>',
				5: _datepickerTpl // 每年
			};

			return function(data) {
				var datepickerSettings = {
					"1": { format: "yyyy-mm-dd hh:ii", pickTime: true, pickSeconds: false },
					"2": { format: "hh:ii", pickTime: true, pickDate: false, pickSeconds: false },
					"3": { format: "hh:ii", pickTime: true, pickDate: false, pickSeconds: false },
					"4": { format: "hh:ii", pickTime: true, pickDate: false, pickSeconds: false  },
					"5": { format: "yyyy-mm-dd hh:ii", pickTime: true, pickSeconds: false}
				}
				if (!data) {
					return false;
				}
				if (!data.id) {
					data.id = 'n_' + $.now();
				}
				if (data.period in _tpl) {
					var _dateTpl = _tpl[data.period];
					if (data.period === "3") {
						data.weekOption = _createWeekOption(data.selected);
					} else if (data.period === "4") {
						data.monthOption = _createDayOption(data.selected);
					}
					var $node = $.tmpl(_dateTpl, data);
					if ($node.is('[data-toggle="datePicker"]')) {
						$node.datepicker(datepickerSettings[data.period]);
					} else {
						$node.find('[data-toggle="datePicker"]').datepicker(datepickerSettings[data.period]);
					}
					return $node;
				}
			};
		})();

		var addCrontab = function(data) {
			var $item, datepickerSettings, id;
			crontabList.addItem(data);
			$item = crontabList.getItem(data.id);
			id = $item.id;
			$dateNode = createDateNode(data);
			$item.find('[data-node="date"]').append($dateNode);
			$item.find('[data-toggle="userSelect"]').userSelect({
				data: Ibos.data.get("user"),
				type: 'user'
			});
			$item.find("[data-node='period']").on("change", function(evt) {
				var $dateCell = $(this).parent().next();
				$dateCell.find("[data-toggle='datePicker']").datepicker("destroy");
				$dateCell.html(createDateNode({
					id: id,
					period: this.value,
					date: ''
				}));
			});
			$item.find(".o-trash").on("click", function() {
				var id = $(this).attr('data-id');
				var $row = $(this).closest("tr");
				if (id.charAt(0) !== 'n') {
					$('#delid').val($('#delid').val() + id + ',');
				}
				$row.find('[data-toggle="datePicker"]').datepicker("destroy");
				crontabList.removeItem(id);
			});
		};
		$("#add_crontab").on("click", function() {
			addCrontab({id: 'n_' + $.now(), value: "", date: "", period: "1"});
		});

		$.get('<?php echo $this->createUrl("timer/index", array("inajax" => 1, "flowid" => $this->flowid));?>', function(data) {
			if (data.count > 0) {
				var datas = data.list;
				for (var i = 0; i < datas.length; i++) {
					addCrontab(datas[i]);
				}
			}
		}, 'json');

	})();
</script>