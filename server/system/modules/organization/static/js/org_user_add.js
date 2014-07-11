


$(function(){

		// 通用AJAX验证配置
		var ajaxValidateSettings = {
			type : 'GET',
			dataType : "json",
			async : true,
			url : Ibos.app.url("organization/user/isRegistered"),
			success : function(res){
				//数据是否可用？可用则返回true，否则返回false
				return !!res.isSuccess;
			},
			buttons: $(".btn btn-large btn-submit btn-primary"),
		}

		$.formValidator.initConfig({ formID:"user_form", errorFocus:true });
		// 用户名
		$("#username").formValidator({ onFocus: U.lang("V.USERNAME_VALIDATE") })
		.inputValidator({
			min: 4,
			max: 20,
			onError: U.lang("V.USERNAME_VALIDATE")
		})
		//验证用户名是否已被注册
		.ajaxValidator($.extend(ajaxValidateSettings, {
			onError : U.lang("V.USERNAME_EXISTED")
		}));

		// 密码
		var pwdSettings = Ibos.app.g("password"),
			pwdErrorTip = U.lang("V.PASSWORD_LENGTH_RULE", { 
				min: pwdSettings.minLength, 
				max: pwdSettings.maxLength
			})
		$("#password")
		.formValidator({ 
			onFocus: pwdErrorTip, 
			empty: true
		})
		.inputValidator({
			min: pwdSettings.minLength,
			max: pwdSettings.maxLength,
			onError: pwdErrorTip
		})
		.regexValidator({
			regExp: pwdSettings.regex,
			dataType:"string",
			onError: U.lang("RULE.CONTAIN_NUM_AND_LETTER")
		});
		
		// 真实姓名
		$("#realname").formValidator()
		.regexValidator({
			regExp:"notempty",
			dataType:"enum",
			onError: U.lang("RULE.REALNAME_CANNOT_BE_EMPTY")
		});
		
		$("#mobile").formValidator()
		.regexValidator({
			regExp:"mobile",
			dataType:"enum",
			onError: U.lang("RULE.MOBILE_INVALID_FORMAT")
		})
		//验证手机是否已被注册
		.ajaxValidator($.extend(ajaxValidateSettings, {	
			onError : U.lang("V.MOBILE_EXISTED"),
		}));
		
		$("#email").formValidator()
		.regexValidator({
			regExp:"email",
			dataType:"enum",
			onError: U.lang("RULE.EMAIL_INVALID_FORMAT")
		})
		//验证邮箱是否已被注册
		.ajaxValidator($.extend(ajaxValidateSettings, {	
			onError : U.lang("V.EMAIL_EXISTED"),
		}));
		
		$("#jobnumber").formValidator({ empty: true })
		//验证工号是否已被注册
		.ajaxValidator($.extend(ajaxValidateSettings, {	
			onError : U.lang("V.JOBNUMBER_EXISTED"),
		}));

		(function() {
			// 用户 部门 岗位选择 		
			var userData = Ibos.data.get("user"),
				depData = Ibos.data.get("department"),
				posData = Ibos.data.get("position");
			$("#user_supervisor").userSelect({
				type: "user",
				maximumSelectionSize: "1",
				data: userData
			});
			$("#user_department").userSelect({
				type: "department",
				maximumSelectionSize: "1",
				data: depData
			});
			$('#auxiliary_department').userSelect({
				type: "department",
				data: depData
			});
			$("#user_position").userSelect({
				type: "position",
				maximumSelectionSize: "1",
				data: posData
			});
			$('#auxiliary_position').userSelect({
				type: "position",
				data: posData
			});
			$('.display_auxiliary').on('click', function() {
				var target = $(this).hide().data('target');
				$(target).show();;
			});
			var settings = {
					data: {
						simpleData: {
							enable: true
						}
					},
					view: {
						showLine: false,
						selectedMulti: false
					}
				}, 
				$tree = $("#utree");
			$tree.waiting(null, "mini");
			$.get(Ibos.app.url('organization/user/index', { 'op': 'tree'}), function(data) {
				$.fn.zTree.init($tree, settings, data);
				$tree.stopWaiting();
			}, 'json');
			
		})();
		
		// 新手引导
		Ibos.app.guide('org_user_add', function() {
			setTimeout(function(){
				Ibos.intro([
					{ 
						element: "#supervisor_intro",
						intro: U.lang("ORG.INTRO.SUPERVISOR")
					},
					{ 
						element: "#auxiliary_dept_intro",
						intro: U.lang("ORG.INTRO.AUXILIARY_DEPT")
					},
					{ 
						element: "#position_intro",
						intro: U.lang("ORG.INTRO.POSITION")
					},
					{ 
						element: "#account_status_intro",
						intro: U.lang("ORG.INTRO.ACCOUNT_STATUS")
					}
				], function(){
					Ibos.app.finishGuide('org_user_add')
				});
			}, 1000);
		})


})