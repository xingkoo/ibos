<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<!-- Task start -->
<div class="mc clearfix">
    <!-- Sidebar start -->
	<?php echo $this->getSubSidebar(); ?>
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
				<form action="<?php echo $this->createUrl( 'task/subtask', array( 'param' => 'search','uid'=>$user['uid'], 'complete'=>$complete ) ) ?>" method="post" id="form_serch">
					<div class="search search-config span3  pull-right ml">
						<input type="text" name="keyword" placeholder="Search" id="mn_search" nofocus>
						<a href="javascript:;">search</a>
						<input type="hidden" name="type" value="normal_search">
					</div>
				</form>
				<div class="btn-toolbar">
					<div class="btn-group ">
						<a href="<?php echo $this->createUrl( 'task/subtask', array( 'complete'=>0, 'uid'=>$user['uid'])); ?>" class="btn <?php echo $complete == 1 ? "" : "active" ?>"><?php echo $lang['Uncompleted']; ?></a>
						<a href="<?php echo $this->createUrl( 'task/subtask', array( 'complete'=>1, 'uid'=>$user['uid'])); ?>" class="btn <?php echo $complete == 0 ? "" : "active" ?>"><?php echo $lang['Completed']; ?></a>
					</div>
				</div>
			</div>
			<div class="page-list-mainer" id="uncomp-list">
				<?php if($allowEditTask == "1" &&  $complete == 0):  ?>
				<div class="todo-header" id="todo-header">
					<input type="text" placeholder="<?php echo $lang['Click to add a new task']; ?>" id="todo_add">
				</div>
				<?php endif; ?>
				<div class="no-data-tip" style="display:none" id="no_data_tip"></div>
				<div class="todo-list" id="todo_list"></div>
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
		taskComplete: '<?php echo $complete; ?>',
		allowEditTask: '<?php echo $allowEditTask?>'
	})
</script>
<script src='<?php echo $assetUrl; ?>/js/todolist.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script>
	(function() {
	
		var $todoList = $("#todo_list"), 
			$todoAdd = $("#todo_add");
			// isTaskComplete = Ibos.app.getPageParam('taskComplete'),
			allowEditTask = Ibos.app.getPageParam('allowEditTask'),
			window.isDisabled = allowEditTask == 1 ? false: true,
			todoList = new TodoList($todoList, {
				mark: function(id, mark) {
					_sendRequest("<?php echo $this->createUrl( 'task/edit&op=mark&uid=' . $user['uid'] ); ?>", {id: id, mark: (mark ? 1 : 0)});
				},
				complete: function(id, complete) {
					_sendRequest("<?php echo $this->createUrl( 'task/edit&op=complete&uid=' . $user['uid'] ); ?>", {id: id, complete: ( complete ? 1 : 0)});
				},
				stop: function(data) {  //拖拽过程中
						data && _sendRequest("<?php echo $this->createUrl( 'task/edit&op=sort' ); ?>", data);
				},
				add: function(data) {
					_sendRequest("<?php echo $this->createUrl( 'task/add&uid=' . $user['uid'] ); ?>", {
						id: data.id,
						pid: data.pid,
						text: data.text
					});
					$("#no_data_tip").hide()
				},
				remove: function(id) {  //删除任务
					_sendRequest("<?php echo $this->createUrl( 'task/del&uid=' . $user['uid'] ); ?>", { id: id });
				},
				save: function(id, text) {  //保存(编辑或者添加最后步骤时都用到)
					_sendRequest("<?php echo $this->createUrl( 'task/edit&op=save&uid=' . $user['uid'] ); ?>", {id: id, text: text});
				},
				date: function(id, date) { //完成时间
					_sendRequest("<?php echo $this->createUrl( 'task/edit&op=date&uid=' . $user['uid'] ); ?>", {id: id, date: (+date)/1000});
				},
				disabled: isDisabled
			});
		/**
		 * 操作请求
		 * @param {str} url 请求路径
		 * @param {sbj} param 请求参数
		 * @returns {isSuccess=true} 返回操作成功
		 */
		function _sendRequest(url, param, callback) {
			param.formhash = '<?php echo FORMHASH; ?>';
			$.post(url, param, function(res) { // res = { isSucees: , msg }
				callback && callback(res)
			}, "json");
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
			$("#no_data_tip").show()
		} else {
			todoList.set(data);	
		}
	
        //搜索
        Ibos.search.init();
        Ibos.search.disableAdvance();
		
		//拿到活动的uid，展开侧边栏
		var supUid = <?php if(isset($supUid)){echo $supUid;}else{echo 0;} ?>;
		if(supUid !== 0){
			var $sub = $('.g-sub[data-uid='+supUid+']');
			$sub.trigger("click");
		    $sub.parent().addClass('active');
		}
	})();
</script>