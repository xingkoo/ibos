/**
 * Organization/department/index
 */

$(function() {
		var $depManager = $("#dep_manager"), //部门主管
			$superiorManager = $("#superior_manager"), //上级主管领导
			$superiorBranchedManager = $("#superior_branched_manager"), //上级分管领导
			userData = Ibos.data.get("user"), //用户数据
			$depCard = $("#dep_card"), // 部门修改资料卡
			$headDepCard = $("#head_dep_card"), //总部修改资料卡
			$deptId = $('#deptId'); //部门id字段

		// 用户选择框
		var commonSettings = {
			data: userData,
			type: "user",
			maximumSelectionSize: "1"
		};

		$depManager.userSelect(commonSettings);
		$superiorManager.userSelect(commonSettings);
		$superiorBranchedManager.userSelect(commonSettings);


		// 资料卡
		var depTable = $("#org_dep_table"),
			headDepCard = new DataCard($headDepCard),
			depCard = new DataCard($depCard);
			
		var T = Organization.DeptTable,
			selectedId = '0', // 当前选中部门的ID，用于新增时作为默认选中部门
			editingId = '';	 // 当前编辑中部门的ID
		// 选中行
		depTable.on("click", "tr", function(){
			$(this).addClass("active").siblings().removeClass("active");
			selectedId = $.attr(this, "data-id");
		});

		var deptOp = {
			edit: function(params){
				// 当为总部时
				if(params.type === "Headquarters") {
					headDepCard.show(function() {
						headDepCard.loadData(params.id);
					});
				// 其它情况下， 即修改部门时
				}else{
					depCard.show(function() {
						// 清空数据重新读取
						depCard.clearForm(function() {
							depCard.loadData(params.id);
						});
					});
				}
			},

			add: function(defaultData){
				depCard.show();
				depCard.clearForm();
				depCard.insertToForm(defaultData)
			},

			cancel: function(params){
				if(params.type === "Headquarters") {
					headDepCard.hide();
				}else{
					depCard.hide();
				}
			},

			submit: function(id, card, isHead){
				var formData = card.getData(),
					url;

				// 非顶部部门名称不能为空
				if ($.trim(formData.deptname) === '' && !isHead) {
					$('[data-mark="deptname"]').blink().focus();
					return false;
				}
				// 不能设置上级部门为当前部门
				if (!isHead && id === formData.pid) {
					Ui.tip(U.lang("ORG.WRONG_PARENT_DEPARTMENT"), "warning")
					$('[data-mark="pid"]').focus().blink();
					return false;
				}
		

				if (!isHead && id === "") {
					url = Ibos.app.url("organization/department/add");
				} else {
					formData.deptid = id;
					url = Ibos.app.url("organization/department/edit");
				}

				$depCard.waiting(U.lang('IN_SUBMIT'), "mini", true);

				$.post(url, formData, function(data) {
					if (data.IsSuccess) {
						// 刷新页面
						// @Todo: 交互优化
						window.location.reload();
						Ui.tip(data.msg ? data.msg : U.lang("OPERATION_SUCCESS"));
					} else {
						Ui.tip(data.msg ? data.msg : U.lang("OPERATION_FAILED"), "danger");
					}
					$depCard.waiting(false);
				}, 'json');
			},

			moveup: function($row){
				var id = $row.attr("data-id"),
					pid = $row.attr("data-pid"),
					$prev = T.getPrev(id),
					prevId,
					$nodes;

				if($prev && $prev.length){
					prevId = $prev.attr("data-id");
					// 根节点不能移动
					if(prevId === "0"){
						return false;
					}
					depTable.waiting(null, "normal");
					$.ajax({
						url: Ibos.app.url("organization/department/edit", { "op": "structure" }),
						data: {curid: id, objid: prevId},
						success: function(data) {
							if (data.IsSuccess) {
								$nodes = T.getDescendantsWithSelf(id);
								$nodes.insertBefore($prev);
							} else {
								Ui.tip(U.lang("ORG.MOVEUP_FAILED"), "danger");
							}
							depTable.waiting(false);
						},
						dataType: "json",
						cache: false
					});
				}
			},

			movedown: function($row){
				var id = $row.attr("data-id"),
					pid = $row.attr("data-pid"),
					
					$next = T.getNext(id),
					nextId,
					$nodes,
					$nextLast;

				if($next && $next.length){
					nextId = $next.attr("data-id");

					depTable.waiting(null, "normal");
					$.ajax({
						url: Ibos.app.url("organization/department/edit", { "op": "structure" }),
						data: {curid: id, objid: nextId},
						success: function(data) {
							if (data.IsSuccess) {
								$nodes = T.getDescendantsWithSelf(id);
								$nextLast = T.getDescendantsWithSelf(nextId).eq(-1);
								$nodes.insertAfter($nextLast);
							} else {
								Ui.tip(U.lang("ORG.MOVEDOWN_FAILED"), "danger");
							}
							depTable.waiting(false);
						},
						dataType: "json",
						cache: false
					});
				}
			},

			del: function($row, callback){
				var id = $row.attr("data-id");

				Ui.confirm(U.lang("ORG.DELETE_DEPARTMENT_CONFIRM"), function() {
					$.post(Ibos.app.url("organization/department/del"), {id: id}, function(data) {
						if (data.IsSuccess) {
							$row.remove();
							callback && callback($row, id);
							Ui.tip(U.lang("OPERATION_SUCCESS"));
						} else {
							Ui.tip(data.msg, "danger");
						}
					}, 'json');
				});
			}
		}

		Ibos.evt.add({
			// 新建部门
			"addDept": function() {
				if(Ibos.app.g("allowAddDept")){
					editingId = '';
					deptOp.add({ pid: selectedId });
					depTable.addClass("open");
				}
			},
			// 编辑部门
			"editDept": function(params){
				deptOp.edit(params);
				editingId = params.id;
				depTable.addClass("open");
			},
			// 取消编辑
			"cancelEditDept": function(params){
				deptOp.cancel(params);
				editingId = '';
				depTable.removeClass("open");
			},
			// 提交数据
			"submitDept": function(params){
				if(params.type === "Headquarters"){
					Ibos.app.g("allowEditDept") && deptOp.submit(editingId, headDepCard, true);
				}else{
					(Ibos.app.g("allowEditDept") || Ibos.app.g("allowAddDept")) &&
					deptOp.submit(editingId, depCard);
				}
			},
			"moveupDept": function(params, elem){
				if(Ibos.app.g("allowEditDept")) {
					deptOp.moveup($(elem).closest("tr"));
				}
			},
			"movedownDept": function(params, elem){
				if(Ibos.app.g("allowEditDept")) {
					deptOp.movedown($(elem).closest("tr"));
				}
			},
			// 移除部门
			"removeDept": function(params, elem){
				var $row = $(elem).closest("tr");
				deptOp.del($row, function($row, id){
					depCard.removeData(id);
				})
			}
		})

		// 初始时弹出新增框
		if(Ibos.app.g("allowAddDept")) {
			deptOp.add({ pid: selectedId });
			depTable.addClass("open");
		}


		// 新手引导
		Ibos.app.guide('org_dep_index', function() {
			setTimeout(function(){
				Ibos.intro([
					{ 
						element: "#branch_intro", 
						intro: U.lang("ORG.INTRO.BRANCH")
					}
				], function(){
					Ibos.app.finishGuide('org_dep_index')
				});
			}, 1000);
		})
	});