// JavaScript Document
$(function(){
	//日志查询页面,表单功能栏删除功能
	$("#log_delete").on("click",function(){
		var ids = WorkFlow.getCheckedId("log");
		//url部分需要由后台最后确定
		WorkFlow.access("http://iobs",null,"log",function(){
				WorkFlow.removeRows(ids);
			},"确定删除已选流程？");
		});	
		
	//日志查询页面,表单功能栏导出功能
	$("#log_export").on("click",function(){
			var ids = WorkFlow.getCheckedId("log");
			WorkFlow.access("http://ibos",null,"log",function(res,ids){
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
			
	//自定义时间范围选择
	$("#time_user_defined").change(function(){
	  var val = $(this).val();
		//"8"是对应"自定义的value值"根据实际情况确定
	  if(val == "8"){
		$(".log-time-grorp").addClass("show");
		}else{
			$(".log-time-group").removeClass("show");
		}
	});
	
	//鼠标悬停在操作人员时,提示ip信息
	$(".ipinfo-tip").tooltip({trigger: "hover"});
});