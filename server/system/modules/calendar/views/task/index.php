<!-- private css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<!-- Task start -->
<div class="mc clearfix">
    <!-- Sidebar start -->
	<?php echo $this->getSidebar(); ?>
	<!-- Sidebar end -->
    <!-- Task right start -->
    <div class="mcr">
        <div class="mc-header">
            <div class="mc-header-info clearfix">
                <div class="usi-terse">
                    <a href="" class="avatar-box">
                        <span class="avatar-circle">
                            <img class="mbm" src="<?php echo $user['avatar_middle']; ?>" alt="">
                        </span>
                    </a>
                    <span class="usi-terse-user"><?php echo $user['realname']; ?></span>
                    <span class="usi-terse-group"><?php echo $user['deptname']; ?></span>
                </div>
            </div>
        </div>

		<div class="page-list">
			<div class="page-list-header">
				<form action="<?php echo $this->createUrl( 'task/index', array( 'param' => 'search', 'uid' => $user['uid'], 'complete' => $complete ) ) ?>" method="post" id="form_serch">
					<div class="search search-config span3  pull-right ml">
						<input type="text" name="keyword" placeholder="Search" id="mn_search" nofocus>
						<a href="javascript:;">search</a>
						<input type="hidden" name="type" value="normal_search">
					</div>
				</form>
				<div class="btn-toolbar">
					<div class="btn-group ">
						<a href="<?php echo $this->createUrl( 'task/index', array( 'complete' => 0 ) ); ?>" class="btn <?php echo $complete == 1 ? "" : "active" ?>"><?php echo $lang['Uncompleted']; ?></a>
						<a href="<?php echo $this->createUrl( 'task/index', array( 'complete' => 1 ) ); ?>" class="btn <?php echo $complete == 0 ? "" : "active" ?>"><?php echo $lang['Completed']; ?></a>
					</div>
				</div>
			</div>
			<div class="page-list-mainer" id="uncomp-list">
				<?php if($complete == 0):  ?>
				<div class="todo-header" id="todo-header">
					<input type="text" placeholder="<?php echo $lang['Click to add a new task']; ?>" id="todo_add">
				</div>
				<?php endif; ?>
				<div class="no-data-tip" style="display:none" id="no_data_tip"></div>
				<div class="todo-list" id="todo_list">
				</div>
			</div>
			<div class="page-list-footer">
				<div class="pull-right">
					<?php
					if ( isset( $complete ) && $complete == 1 && isset( $pages ) ) {
						$this->widget( 'IWPage', array( 'pages' => $pages ) );
					}
					?>
				</div>
			</div>
		</div>
		<!-- Mainer content -->
	</div>
	<!-- Task right end -->
</div>
<!-- Task end -->
<script>
	Ibos.app.setPageParam({
		taskComplete: '<?php echo $complete; ?>'
	})
</script>
<script src='<?php echo STATICURL; ?>/js/lib/introjs/intro.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/todolist.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script>
	(function() {
		var $todoList = $("#todo_list"), 
			$todoAdd = $("#todo_add");
			// isTaskComplete = Ibos.app.getPageParam('taskComplete'),
			// isDisabled = (isTaskComplete === "0" ? false: true),
			window.todoList = new TodoList($todoList, {
				mark: function(id, mark) {
					_sendRequest(Ibos.app.url("calendar/task/edit", {op: "mark"}), {id: id, mark: (mark ? 1 : 0)});
				},
				complete: function(id, complete) {
					_sendRequest(Ibos.app.url("calendar/task/edit", {op: "complete"}), {id: id, complete: (complete ? 1 : 0)});
				},
				// start: function() {},
				// sort: function() {},
				// change: function() {},
				stop: function(data) {  //拖拽过程中
					data && _sendRequest(Ibos.app.url("calendar/task/edit", {op: sort}), data);
				},
				add: function(data) {
					_sendRequest(Ibos.app.url("calendar/task/add"), {
						id: data.id,
						pid: data.pid,
						text: data.text
					});
					$("#no_data_tip").hide()
				},
				remove: function(id) {  //删除任务
					_sendRequest(Ibos.app.url("calendar/task/del"), {id: id});
				},
				// edit: function() {},
				save: function(id, text) {  //保存(编辑或者添加最后步骤时都用到)
					_sendRequest(Ibos.app.url("calendar/task/edit", {op: "save"}), {id: id, text: text});
				},
				date: function(id, date) { //完成时间
					_sendRequest(Ibos.app.url("calendar/task/edit", {op: "date"}), {id: id, date: (+date) / 1000});
				}
				// disabled: isDisabled
			});
		/**
		 * 操作请求
		 * @param {String} url 请求路径
		 * @param {Object} param 请求参数
		 * @return
		 */
		function _sendRequest(url, param, callback) {
			param.formhash = Ibos.app.g('formHash');
			$.post(url, param, callback, "json");
		}

		// 新增一条Todo
		$todoAdd.on("keydown", function(evt) {
			var $add;
			if (evt.which === 13) {
				$add = $(this);
				todoList.addItem({text: $add.val()});
				$add.val("");
			}
		});
		var data = <?php echo $todolist; ?>;
		if(data && data.length){
			$.each(data, function(i, d){
				if(d.pid == ''){
					delete d.pid;
				}
			})
			todolist = data;
		}
		if(!data.length){
			$("#no_data_tip").show();
		} else {
			todoList.set(data);	
		}

		//搜索
		Ibos.search.init();
		Ibos.search.disableAdvance();

		// 新手引导
		// 保证至少有一条Todo纪录
		if(Ibos.app.g("taskComplete")=="0") {
			setTimeout(function(){
				Ibos.app.guide("cal_task_index", function() {
					var guideData = [
						{ 
							element: "#todo_add", 
							intro: U.lang("INTRO.CAL_TASK_INDEX.TASK_ADD")
						}
					];
					var $todoItems = $("#todo_list .todo-item");
					if($todoItems.length) {
						guideData.push(
							{
								element: "#todo_list .todo-item .o-todo-drag",
								intro: U.lang("INTRO.CAL_TASK_INDEX.TASK_DRAG")
							}, {
								element: "#todo_list .todo-item .o-date",
								intro: U.lang("INTRO.CAL_TASK_INDEX.IMPORT_TO_SCH")
							}	
						)
						$todoItems.eq(0).addClass("introing");
						Ibos.intro(guideData, function(){
							$todoItems.eq(0).removeClass("introing");
							Ibos.app.finishGuide('cal_task_index');
						});
					}
				})
			}, 1000)
		}
	})();


</script>