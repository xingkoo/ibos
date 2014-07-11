// JavaScript Document

//正则表达式规则集合(可扩展)
	var rNoEmpty = /\S+/;//不为空
	var reg={
		username: rNoEmpty,
		DBpassword: rNoEmpty,
		account: rNoEmpty,
		ADpassword:/^[a-zA-Z0-9]{5,32}$/   //6到32位数字或者字母组成
	}
	
	//视图层的显示与隐藏
	var view = {
		show: function(elem){
			$(elem).css("display","inline-block");
			},
		hide:function(elem){
			$(elem).css("display","none");
			}
		}
	
	//对表单中每项进行验证	
	var validate = {
			//对数据库用户名进行验证
			username:function(elem){
				var value = $(elem).val();
				if(!reg.username.test(value)){
					view.show("#database_name_tip");
					return false;
					}
					return true;
				},
			//对数据库密码进行验证
			DBpassword:function(elem){
				var value = $(elem).val();
				if(!reg.DBpassword.test(value)){

					view.show("#database_password_tip");
					return false;
					}
					return true;
				},
			//对管理员账号进行验证
			account:function(elem){
				var value = $(elem).val();
				if(!reg.account.test(value)){
					view.show("#administrator_account_tip");
					return false;
					}
					return true;
				},
			//对管理员密码进行验证
			ADpassword:function(elem){
				var value = $(elem).val();
				if(!reg.ADpassword.test(value)){
					view.show("#administrator_password_tip");
					return false;
					}
					return true;
			}
		}


$(function(){
	//创建数据页面,点击显示更多后,隐藏部分信息显示
		$("#table_info").on("click",".show-info",function(){
			$(".hidden-info").slideDown(100, function(){
				$("#database_server").focus();
			});
			$(this).slideUp(100);
		});
		
		/*
			创建数据页面,
			1.勾选阅读协议后,自定义模块选项可勾选,立即安装按钮可用.
			2.取消勾选阅读协议后,自定义选项不可用,立即安装按钮不可用
		*/
		$("#protocol_choose").on("click",function(){	
			var value = $("#protocol_choose").is(":checked");
			if(value){
					$("#btn_install").addClass("btn-primary").removeAttr("disabled");
				}else{
					$("#btn_install").removeClass("btn-primary").attr("disabled","true");
				}
			});
			
		/*
			1.勾选自定义模块,立即安装按钮文字变为"下一步",同时表单提交至"下一步"
			2.取消勾选自定义模块后,下一步按钮文字变为"立即安装",同时表单提交至"立即安装"
		*/
		$("#user_defined").on("click",function(){	
			var value = $("#user_defined").is(":checked");
			if(value){
					$("#btn_install").text("下一步");
					//表单提交至"下一步"的url,需后台设置
				}else{
					$("#btn_install").text("立即安装");
					//表单提交至"立即安装"的url,需后台设置
					}
			});

		//对数据库账号在获取焦点和失去焦点时进行验证操作
		$("#database_name").on({
			"blur":function(){
					var $elem = $(this);
					validate.username($elem);
				},
			"focus":function(){
					view.hide("#database_name_tip");
					}
			});
			
		//对数据库密码在获取焦点和失去焦点时进行验证操作
		$("#database_password").on({
			"blur":function(){
					var $elem = $(this);
					validate.DBpassword($elem);
			},
			"focus":function(){
					view.hide("#database_password_tip");
				}
			});			
		
		//对管理员账号在获取焦点和失去焦点时进行验证操作
		$("#administrator_account").on({
			"blur":function(){
				var $elem = $(this);
				validate.account($elem);
			},
			"focus":function(){
				view.hide("#administrator_account_tip");
				}
			});	
				
		//对管理员密码在获取焦点和失去焦点时进行验证操作
		$("#administrator_password").on({
			"blur":function(){
				var $elem = $(this);
				validate.ADpassword($elem);
			},
			"focus":function(){
				view.hide("#administrator_password_tip");
				}
			});		
		
		//点击立即安装时,对表单进行验证
		$("#user_form").submit(function(){
			var elems = $(this).get(0).elements;
			for(var i = 0; i < elems.length; i++) {
				var elem = elems[i];
				var type = elem.getAttribute("data-type");
				if(validate[type] && !validate[type](elem)){
					// 重置站点数据
					U.clearCookie();
					Ibos.local.clear();
					$(elem).trigger("focus.submit");
					return false;
				};
			}
		});
	});