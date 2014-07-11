// JavaScript Document

var FlowLog = {
		/**
		 * 获取列表已选项id的集合
		 * @method getCheckedId
		 * @param  {String} name 对应列表勾选input的value属性的值
		 * return {String} id的集合 形式为"1,2"
		*/
		getCheckedId: function(name) {
					return U.getCheckedValue(name);
				},
		/**
			 * 删除列表中的多行
			 * @method removeRows
			 * @param  {String} ids 对应列表多行的id的集合
		*/
		removeRows: function(ids) {
						var arr = ids.split(',');
						for (var i = 0, len = arr.length; i < len; i++) {
							$('#list_tr_' + arr[i]).remove();
						}
				},
		/**
			处理多项操作集合
			 @method access
			 * @param  {String} url 后台处理功能php的路径
			 * @param  {Array} param post请求发送的参数集合形式为{key:value}
			 * @param  {String} ids 处理对象的id的集合
			 * @param  {Function} success 当ajax请求成功后的操作函数
			 * @param  {String} msg 弹出提示框的提示语言
		*/
		access: function(url, param, name, success, msg) {
						var wfIds = this.getCheckedId(name);
						var _ajax = function(url, param, success) {
							$.post(url, param, function(res) {
								if (res.isSuccess) {
									if (success && $.isFunction(success)) {
										success.call(null, res, wfIds);
										Ui.tip(U.lang("OPERATION_SUCCESS"));
									}
								} else {
									Ui.tip(U.lang("OPERATION_FAILED"),"danger");
								}
							});
						}
						if (wfIds !== '') {
							param = $.extend({wfids: wfIds}, param);
							if (msg) {
								Ui.confirm(msg, function() {
									_ajax(url, param, success);
								});
							} else {
								_ajax(url, param, success);
							}
						} else {
							Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"),"warning");
					}
				}
	}

$(function(){
	//流程日志搜索页面,表单功能栏删除功能
	$("#log_delete").on("click",function(){
		var ids = FlowLog.getCheckedId("log");
		//url部分需要由后台最后确定
		FlowLog.access("http://iobs",null,"log",function(){
				WorkFlow.removeRows(ids);
			},"确定删除已选流程？");
		});	
		
	//流程日志搜索页面,表单功能栏导出功能
	$("#log_export").on("click",function(){
			var ids = FlowLog.getCheckedId("log");
			FlowLog.access("http://ibos",null,"log",function(res,ids){
				//后台返回的res中需有导出ZIP的地址
				window.location.href = res.url;
			});		
		});
	
	
	//高级查询和普通查询的显示和隐藏切换
	$(".type-toggle").toggle(function(){
			$(".text-toggle").text("基本搜索");
			$(this).addClass("active");
			$("#senior_search").slideDown("fast");
		},function(){
			$(".text-toggle").text("高级搜索");
			$(this).removeClass("active");
			$("#senior_search").slideUp("fast");
			});
			
	//日期范围选择初始化
	$("#time_user_defined").change(function(){
	  var val = $(this).val();
		//"8"是对应"自定义的value值"根据实际情况确定
	  if(val == "8"){
		$(".log-time-grorp").addClass("show");
		}else{
			$(".log-time-grorp").removeClass("show");
		}
	});
	
	//类别选择点击效果
	$(document).on("click",".range-select li",function(){
		$(this).addClass("active").siblings().removeClass("active");
	});
});