
$(function(){
	// 进度条初始化
	$("[data-toggle='bamboo-pgb']").each(function(){
		var $elem = $(this);
		return $elem.studyplay_star({
			CurrentStar: 10,
			prefix: $elem.attr("data-id")
		}, function(value, $elem){
			$elem.next().val(value);
		});
	});

	

	(function(){
		// 工作小结表格
		var $rpReportRows = $("#rp_report_rowspan"),
			$rpReportAdd = $("#rp_report_add"),
			$rpComplete = $("#rp_complete"),
			reportTable = new Ibos.HandyTable($rpComplete, {
				tplid: "rp_complete_tpl"
			});

		//格式化当天时间
		var today = Ibos.date.format(new Date(),"yyyy-mm-dd");

		function addReportRow(data){
			data = data || {};
			Report.addRow(reportTable, data, function($row){
				// 添加一行后，对应th的rowspan需要+1
				rowSpan.plus($rpReportRows);
				// 初始化进度条
				$star = $row.find("[data-toggle='bamboo-pgb']");
				$star.studyplay_star({
					CurrentStar: 10,
					prefix: $star.attr("data-id")
				}, function(value, $el){
					// 此处确保初始化目标的下一个节点为其对应input控件
					$el.next().val(value);
				});
			});
		}

		function removeReportRow($row){
			Report.removeRow(reportTable, $row, function(){
				// 移除一行后，对应th的rowspan需要-1
				rowSpan.minus($rpReportRows);
			});
		}

		// 工作小结添加一行
		$rpReportAdd.on("click", function(){
			addReportRow({});
		});

		// 工作小结添加移除快捷键
		$rpComplete.on("keydown", ".rp-input", function(evt){
			var $row = $(this).parents("tr").eq(0);
			Report.editKeydownHandler(evt, $row, function(){
				addReportRow({});
				evt.stopPropagation();
				evt.preventDefault();
			}, function($row){
				removeReportRow($row);
			});
		});

		// 工作总结移除一行
		$rpComplete.on("click", ".o-trash", function(){
			$row = $(this).parents("tr").eq(0);
			removeReportRow($row);
		});

		// 工作计划表格
		var $rpPlan = $("#rp_plan"),
			$rpPlanRows = $("#rp_plan_rowspan"),
			$rpPlanAdd = $("#rp_plan_add"),
			planTable = new Ibos.HandyTable($rpPlan, {
				tplid: "rp_plan_tpl"
			});

		function addPlanRow(data, callback){
			Report.addRow(planTable, data, function($row){
				// 添加一行后，对应th的rowspan需要+1
				rowSpan.plus($rpPlanRows);
				callback && callback($row);

				// 时间选择
				$('.remind-time-btn', $row).each(function(){
					var $elem = $(this);
					$elem.datepicker();
					$elem.datepicker('setStartDate', today).on("changeDate", function(evt) {
						var date = Ibos.date.format(evt.date, "yyyy-mm-dd");
						var $elemSiblings = $elem.siblings(".rp-remind-bar");
						$elem.css("display","none");
						$elemSiblings.css("display","inline-block");
						$elemSiblings.find('.remind-time').text(date);
						$elem.parents(".rp-plan-item").eq(0).find(".remind-value").val(date);
					});
				});

				//给动态添加的行绑定删除提醒已选时间功能
				$(".o-close-small", $row).on("click",function(){
					var $elemParent = $(this).parent();
					$elemParent.css("display","none");
					$elemParent.siblings(".remind-time-btn").css("display","inline-block");
				});				
			});

		}

		function removePlanRow($row){
			$row.find(".remind-time-btn").datepicker('destroy');
			Report.removeRow(planTable, $row, function(){
				// 移除一行后，对应th的rowspan需要-1
				rowSpan.minus($rpPlanRows);
			});
		}

		// 工作计划添加一行
		$rpPlanAdd.on("click", function(){
			addPlanRow({});	
		});

		// 工作计划添加移除快捷键
		$rpPlan.on("keydown", ".rp-input", function(evt){
			var $row = getParentRow($(this));
			Report.editKeydownHandler(evt, $row, function(){
				addPlanRow({});
				evt.stopPropagation();
				evt.preventDefault();
			}, function($row){
				removePlanRow($row);
			});
		});

		// 工作计划移除一行
		$rpPlan.on("click", ".o-trash", function(){
			$row = $(this).parents("tr").eq(0);
			removePlanRow($row);
		});

	})();
});