// 除公用的语言外， 模块内的语言请写到各模块命名空间里，插件的语言写到对应的插件命名空间里
// 使用语言包时，统一用U.lang作为入口

// @Todo: 语言包归类

var L = {
	// 通用操作
	NEW: '新建',
	ADD: '增加',
	DELETE: '删除',
	MODIFY: '修改',
	EDIT: "编辑",
	SAVE: "保存",

	CONFIRM: "确定",
	CANCEL: "取消",
	CLOSE: "关闭",

	FROM: "从",
	TO: "至",

	MOVEUP: "向上移动",
	MOVEDOWN: "向下移动",

	REPLY: "回复",
	MARK: "标记",
	UNMARK: "取消标记",
	START: "开始",
	END: "结束",
	IMPORT: "导入",
	SEND: "发送",

	IN_SUBMIT: '提交中，请稍等...',
	READ_INFO: '读取数据，请稍等...',
	CONFIRM_POST: '确认提交',
	CONFIRM_DEL_ATTACH: '确认要删除附件吗？',

	// 通用关键字
	SUCCESS: "成功",
	FAILED: "失败",
	CONTENT: "内容",
	DIR: "文件夹",
	PORT: "端口",
	RECEIVER: "收信人",
	SERVER_URL: "服务器地址",
	SUBJECT: "主题",
	URL: "url",
	USERNAME: "用户名",
	PASSWORD: "密码",
	REALNAME: "真实姓名",
	MOBILE: "手机",
	EMAIL: "邮箱",
	JOBNUMBER: "工号",
	STAFF: "人员",
	DEPARTMENT: "部门",
	POSITION: "职位",
	COMPANY: "公司",
	SETTING: "设置",
	ADVANCED_SETTING: "高级设置",
	HISTORICAL_EDITION: "历史版本",

	// 通用状态提示
	OPERATION_SUCCESS: "操作成功",
	OPERATION_FAILED: "操作失败",
	SAVE_SUCEESS: "保存成功",
	SAVE_FAILED: "保存失败",
	DELETE_SUCCESS: "删除成功",
	DELETE_FAILED: "删除失败",
	SETUP_SUCCEESS: "设置成功",
	SELECT_AT_LEAST_ONE_ITEM: "请选择至少一项再进行操作",
	REPLY_SUCCESS: "回复成功",
	PARAM_ERROR: "参数错误！",

	// Email
	EXIST: "已经被注册，请重新输入",
	SERVER_URL_VALIDATE: "服务器地址格式不正确",
	PORT_VALIDATE: "端口格式不正确",
	PASSWORD_CANNOT_BE_EMPTY: "密码不能为空",
	RECEIVER_CANNOT_BE_EMPTY: "收信人不能为空",
	CONTENT_CANNOT_BE_EMPTY: "内容不能为空",
	KEYWORD_CANNOT_BE_EMPTY: "关键字不能为空",
	BEGIN_GREATER_THAN_END: '开始时间不能小于结束时间',
	TWICE_INPUT_INCONFORMITY: "两次输入不一致",
	
	CM: {
		NOT_AVAILABLE: '暂无',
		DEFAULT: '默认',
		MOVE_SUCCEED: '移动成功',
		MOVE_FAILED: '移动失败',
		MODIFY_SUCCEED: '修改成功',
		MODIFY_FAILED: '修改失败',
		COMPLETE: '完成',
		UNCOMPLETE: '未完成',
		LOAD_SUCCEED: '加载成功',
		LOAD_FAILED: '加载失败'
	},

	// 通用验证信息
	V: {
		INPUT_ACCOUNT: "请填入账号",
		INPUT_EMAIL: "请填入邮箱",
		INPUT_JOBNUM: "请填入工号",
		INPUT_MOBILE: "请填入手机",
		INPUT_USERNAME: "请填入用户名",
		INPUT_POSSWORD: "请填入密码",

		USERNAME_EXISTED: "用户名已存在",
		USERNAME_NOT_EXISTED: "用户名已存在",
		ACCOUNT_EXISTED: "账号已存在",
		MOBILE_EXISTED: "手机号码已存在",
		EMAIL_EXISTED: "邮箱地址已存在",
		JOBNUMBER_EXISTED: "工号已存在",
		USERNAME_VALIDATE: "请输入4-20位用户名",
		PASSWORD_INCORRECT: "密码错误",
		PASSWORD_LENGTH_RULE: "请填写<%=min%>-<%=max%>位密码",
		PASSWORD_LENGTH_RULE_REGEX: "请填写<%=min%>-<%=max%>位包含字母和数字的密码",
		PASSWORD_PREG: "请填写<%=min%>-<%=max%>位<%=mixed%>密码",
		ORIGINAL_PASSWORD_INPUT_INVALID: "原密码输入错误"
	},
		// 
	RULE: {
		NOT_NULL: '不为空',
		INVALID_FORMAT: '格式错误',
		CHINESE_ONLY: '必须为中文',
		ENGLISH_ONLY: '必须为英文',
		NUMERIC_ONLY: '必须为数字',
		CONTAIN_NUM_AND_LETTER: '需包含数字与字母',
		IDCARD_INVALID_FORMAT: '身份证格式错误',
		MOBILE_INVALID_FORMAT: '手机号码格式错误',
		MONEY_INVALID_FORMAT: '金额格式错误',
		PHONE_INVALID_FORMAT: '电话格式错误',
		ZIP_INVALID_FORMAT: '邮政编码格式错误',
		EMAIL_INVALID_FORMAT: '邮箱格式错误',
		URL_INVALID_FORMAT: "URL地址格式错误",
		// PASSWORD: "密码必须包含字母和数字",
		PASSWORD: "密码格式错误",
		REALNAME_CANNOT_BE_EMPTY: "真实姓名不能为空",
		BIRTHDAY_CANNOT_BE_EMPTY: "生日不能为空",
		ORDERID: "序号不能为空且只能为数字",
		SUBJECT_CANNOT_BE_EMPTY: "主题不能为空"
	},
	
	// PM
	SEND_PM: "发私信",

	NOTIFY: {
		UNREAD_TOTAL: "您有 <%=count%> 未读新提醒",
		TO_VIEW: "请点击查看"
	},

	// Dropnotify
	DN: {
		NEW_FOLOWER_COUNT: '位新粉丝',
		UNREAD_ATME: '条新@提到我',
		UNREAD_COMMENT: '条新的评论',
		UNREAD_GROUP_ATME: '条群聊@提到我',
		UNREAD_GROUP_COMMENT: '条群组评论',
		UNREAD_NOTIFY: '条新系统通知',
		UNREAD_MESSAGE: '条新的私信'
	},
	CNUM: {
		ONE: '一',
		TWO: '二', 
		THREE: '三', 
		FOUR: '四',
		FIVE: '五', 
		SIX: '六', 
		SEVEN: '七', 
		EIGHT: '八', 
		NINE: '九', 
		TEN: '十', 
		ELEVEN: '十一', 
		TWELVE: '十二'
	},
	TIME: {
		MONTH: '月',
		DAY: '天',
		HOUR: '小时',
		MIN: '分',
		SEC: '秒',
		INVALID_DATE: '日期格式无效',
		WEEK: "星期",
		WEEKDAYS: "日一二三四五六七"
	},
	YUANCAPITAL: {
		ZERO: '零',
		ONE: '壹',
		TWO: '贰',
		THREE: '叁',
		FOUR: '肆',
		FIVE: '伍',
		SIX: '陆',
		SEVEN: "柒",
		EIGHT: "捌",
		NINE: "玖",
		TEN: "拾",
		HUNDRED: "佰",
		THOUSAND: "仟",
		TEN_THOUSAND: "万",
		HUNDRED_MILLION: "亿",
		DOLLAR: "元",
		TEN_CENT: "角",
		CENT: "分",
		INTEGER: "整"
	},
	// UserSelect
	US: {
		CONTACT: "常用联系人",
		PER_DEPARTMENT: "按部门",
		PER_POSITION: "按岗位",
		SELECT_ALL: "选择全部",
		INPUT_TIP: "请输入部门或同事的名称或拼音",
		NO_MATCH: "没有查询结果",
		SELECTION_TO_BIG: "你最多只能选择<%=limit%>项",
		PLACEHOLDER_ALL: "请选择部门、岗位或人员",
		PLACEHOLDER_DEPARTMENT: "请选择部门",
		PLACEHOLDER_USER: "请选择人员",
		PLACEHOLDER_POSITION: "请选择岗位"
	},

	// Treemenu
	TREEMENU: {
		ADD_CATELOG: "新建分类",
		ADD_CATELOG_SUCCESS: "分类添加成功",
		EDIT_CATELOG: "编辑分类",
		EDIT_CATELOG_SUCCESS: "分类保存成功",
		MOVE_CATELOG_SUCCESS: "分类移动成功",
		MOVE_CATELOG_FAILED: "分类移动失败",
		CATELOG_IS_FIRST: "该分类已是第一个分类",
		CATELOG_IS_LAST: "该分类已是最后一个分类",
		DEL_CATELOG_SUCCESS: "分类删除成功",
		DEL_CATELOG_FAILED: "分类删除失败",
		CANCEL_OPERATE: "已取消操作"
	},

	// Select2
	S2: {
		NO_MATCHES: "没有查询结果",
		SELECTION_TO_BIG: "你最多只能选择<%=count%>项",
		SEARCHING: "查询中..."
	},

	// 上传
	UPLOAD: {
		WAITING:              "等待上传",//"上传中...",
		QUEUE_LIMIT_EXCEEDED:     "已达到文件上限",
		FILE_EXCEEDS_SIZE_LIMIT:   "文件太大",
		ZERO_BYTE_FILE:           "不能上传零字节文件",
		INVALID_FILETYPE:        "禁止上传该类型的文件",
		UNKNOWN_ERROR:           "未知错误",
		UPLOAD_COMPLETE:         "上传完成",
		DELETE_ATTACH:           "删除附件"
	},


	// 模块引导
	// @Todo: 迁移到各自对应模块 
	INTRO: {
		CAL_SCH_INDEX: {
			CALENDAR_ADD: "在开始时间点击并向下拖动至结束时间，即可添加日程。",
			CALENDAR_DRAG: "你可以随意拖动日程到不同的时间，赶紧试试吧！",
			VIEWTYPE: "点这儿，可切换日程的展示方式，方便你查看并安排事务！",
			WORKTIME: "这儿显示了公司规定的正常上下班时间<br />(可以通过 后台管理>模块>模块管理>日程安排 进行设置 )。"
		},
		CAL_TASK_INDEX: {
			TASK_ADD: "点这儿添加任务，按回车完成！",
			TASK_DRAG: "点这儿拖动改变任务顺序！",
			IMPORT_TO_SCH: "设定了截至时间的任务将会导入日程，且该任务的修改会同步至日程哦！"
		},
		// 下属日程
		CAL_SCH_SUB: {
			VIEW_UNDERLINGS: "点这儿可切换查看下属的日程、任务。"
		},
		// 办公首页
		INDEX: {
			MOD_DRAG: "尝试一下选中版块拖拽，手动DIY合适自己办公的布局。",
			MOD_SETTING: "在这儿选择你想要在首页显示的模块信息。"
		},
		// 增加日志
		DIA_DEF_ADD: {
			ADD_FROM_SCHE: "点这儿可以同步今天的日程记录到日志。",
			ADD_SHARE: "选择了共享人员后，共享人员可以在共享栏目中看到被共享的日志，设置为默认每次都共享。"
		},
		// 评阅下属日志
		DIA_REV_INDEX: {
			SHOW_UNDERLINGS: "TA们是您的直属下属和部门成员，点击 <span class='o-caret'><i class='caret'></i></span> 展开查看下属的下属",
			REMIND_UNDERLINGS: "点这儿提醒这群懒货写日志~！",
			MARK_UNDERLINGS: "点亮 <i class='o-da-unasterisk'></i> 关注即可添加此人到左侧“已关注日志”，方便查看。"	
		},
		
		// 新建新闻
		NEWS_DEF_ADD: {
			STATUS: "1、选“发布”将会直接发布<br /> 2、选“待审核”将会转交给新闻审核人<br />（可以在后台>模块>模块管理>审核人 里设置）"
		},
		// 工作流查看
		WF_PRE_PRINT: {
			DISPLAY_PANEL: "点这儿可以显示/隐藏“表单”、“公共附件区”、“会签意见区”、“流程图”",
			QUICK_LINK: "Hey! 点我们试试看~!"
		},
		// 工作流主办
		WF_FORM_INDEX: {
			STEPS_PREVIEW: "点这儿可以看到本条工作流具体的流程步骤",
			TURN: "点击转交本工作流到下一步骤",
			FALLBACK: "点击“回退”本工作流将返回给上一步骤的主办人"
		}
	},

	// 初始化引导
	GUIDE: {
		WRITE_PHONE_NUMBER: "填写手机号码",
		WRITE_EMAIL_ADDRESS: "填写邮箱地址",
		WRITE_PERSONAL_BIRTHDAY :"填写个人生日",
		UPLOAD_REAL_AVATAR: "上传真实头像",
		JUST_A_LITTLE_MORE: "就差一点点而已",
		IMMEDIATELY_TO_FILL_OUT: "即刻去填完!",
		DATA_HAS_NOT_FILLED_OUT: "您的资料尚未填完",
		CONTINUE_TO_IMPROVE: "继续完善"
	},

	// 登陆页 Login
	LOGIN: {
		LOGIN: "登录",
		TIMEOUT: "登录状态已超时，请重新登录",
		CLEAR_COOKIE_CONFIRM: "确定要清除登录痕迹吗？",
		CLEARED_COOKIE: "已清除所有登录痕迹！"
	},

	// 个人中心 User
	USER: {
		UPLOAD_PICTURE: '请先上传图片',
		BIND_OPERATION: '绑定操作',
		THE_FIRST_PAGE: "已经是第一页了",
		SETTING_NOT_SAVE: '您的设置还没有保存，确认关闭嘛？',
		INDIVIDUALITY_SETTING: '个性化设置',
		DELETE_CHOOSE_MODEL: '确定删除已选模板?',
		IS_LAST_MODEL: '已是最后一个模版，不能删除!',
		PERSONAL_SETTING_NOT_SAVE: '您的个性化设置还没有保存，确认关闭嘛？',
		SELECT_ONE_MODEL_PICTURE: "请选择一个模版图片"
	},

	// comment
	CONFIRM_DEL_COMMENT: '确认要删除这条信息？',

	/* --------- Workflow 工作流 */
	WF: {
		// 工作流管理
		CONDITION_INVAILD: "值中不能含有\'号",
		CONDITION_REPEAT: "条件重复",
		CONDITION_INCOMPLETE: "请补充完整条件",
		CONDITION_NEED: "请先添加条件",
		CONDITION_CANNOT_EDIT: "无法编辑已经存在关系的条件",
		CONDITION_FORMAT_ERROR: "条件表达式书写错误,请检查括号匹配",
		FLOW_NAME_CANNOT_BE_EMPTY: "流程名称不能为空",
		ENTER_FORM_NAME: "请输入表单名字",
		PROCESS_NOT_DONE: "流程尚未完成",
		PROCESS_RUNNING_WELL: "流程运行良好",
		PROCESS_ERROR: "流程运行出错",
		SPECIFY_THE_IMPORT_FILE: "请指定用于导入的xml文件",
		DELETE_FLOW_CONFIRM: "确认要删除选中流程吗？这将删除：<br/>1:流程描述与步骤设置 <br/> 2:依托于该流程的所有工作",
		CLEAR_FLOW_CONFIRM: "确认要清空依托于选中流程的工作数据吗？",
		NEW_MANAGE_PRIV: '新建管理权限',
		EDIT_MANAGE_PRIV: '编辑管理权限',
		INVALID_AUTH_OBJECT: '请选择授权对象',
		INVALID_CUSTOM_DEPT: '请选择自定义部门',
		MANAGER_LIST: '授权列表',
		TPLNAME_EXISTS: '模板名称已存在',
		NEW_QUERY_TPL: '新建查询模板',
		EDIT_QUERY_TPL: '编辑查询模板',
		FLOW_QUERY_TPL: '高级查询模板列表',
		NEW_FORM: "新建表单",
		EDIT_FORM: "编辑表单",
		IMPORT_FORM: "导入表单",
		HISTORICAL_EDITION: "历史版本",
		NO_HISTORICAL_EDITION: "暂无历史版本",
		SELECT_HISTORICAL_EDITION: "请选择历史版本",
		EDITION_RESTORE_CONFIRM: "确认要将此版本恢复为应用版本吗？这将不可恢复！", //确定要还原该历史版本吗？这将覆盖当前使用版本
		EDITION_DELETE_CONFIRM: "确认要删除选中的历史版本吗？这将不可恢复！",
		TPLNAME_CANNOT_BE_EMPTY: "模板名称不能为空",
		SET_THE_TIMING_TASK: "设置定时任务",
		TRANSACTOR_CANT_BE_EMPTY: "请选择原办理人",
		TRANS_OBJECT_CANT_BE_EMPTY: "请选择移交对象",
		PLEASE_SELECT_PROCESS: "请选择流程",
		SAVE_AND_DESIGN: "保存并设计",
		CONFIRM_DEL_FORM: "确定要删除选中表单吗？",
		WORK_HANDOVER: "工作移交",
		UNSAVE_FLOW_UNLOAD_TIP: "尚未保存流程，如果离开您的数据会丢失",
		VERIFY_RESULT: "校验结果",
		USING_STATE_TITLE: "使用状态说明",
		USING_STATE_DESC: "<strong>可见</strong>：所有用户都可以在前台新建工作里看到该流程，但无权限用户不能点击。<br/><strong>不可见</strong>：只有拥有权限的用户才能在前台新建工作中看到，并可点击。<br/><strong>锁定</strong>：无论用户有无权限，都不会在前台新建工作中显示。<br/><strong>注意</strong>：默认选择“可见”。",
		DELEGATE_TYPE_TITLE: '委托类型说明',
		DELEGATE_TYPE_DESC: '<strong>自由委托</strong>：用户可以在工作委托模块中设置委托规则,可以为委托给任何人。<br/><strong>按步骤设置的经办权限委托</strong>：仅能委托给流程步骤设置中经办权限范围内的人员。<br/><strong>按实际经办人委托</strong>：仅能委托给步骤实际经办人员。<br/><strong>禁止委托</strong>：办理过程中不能使用委托功能。<br/><strong>注意</strong>：只有自由委托才允许定义委托规则，委托后更新自己步骤为办理完毕，主办人变为经办人。',
		TYPE_FORM_CHANGE_TIP: "尚未保存流程，如果离开您的数据会丢失！",
		// 流程设计器
		CLEAR_CONNECTION_CONFIRM: "确认要清除所有链接吗？",
		PLEASE_SELECT_A_STEP: "请选择要删除的步骤",
		DELETE_STEP_CONFIRM: "确认要删除“<%=name%>”步骤吗？",
		OVERTIME_REMINDED: "已催办超时步骤",
		EXISTING_CORRESPONDENCE: "该对应关系已存在",
		PLUGIN_SELECT: "选择插件",
		NO_PLUGIN_AVAILABLE: "没有可用插件",
		ADD_STEP: "新建步骤",
		LOADING_PROCESS: '正在加载步骤信息',
		REPEAT_STEP: '步骤重复',
		STEP_EXISTS: '步骤序号已存在',
		NOT_FROM_START_TO_END: '连接不能从开始步骤直接指向结束步骤', // '你觉得这样连有意思吗？',
		EMPTY_STEP_NAME: '请填写步骤名称',
		EMPTY_CHILD_FLOW: '请选择子流程',
		// 工作流新建
		PREFIX_CANNOT_BE_EMPTY: '请填写前缀',
		SUFFIX_CANNOT_BE_EMPTY: '请填写后缀',
		RUNNAME_CANNOT_BE_EMPTY: '请填写工作名称/文号',
		CONFIRM_ENTRUST: '确认委托',
		SPECIFY_FREE_FLOW_NEW_USER: '指定自由流程新建人员',
		// 表单
		EMPTY_FORM_CONTENT: '表单内容不能为空',
		SAVE_VERSION_SUCEESS: '生成版本成功',
		INVALID_OPERATION: '无效操作',
		FORM_CONTROL: '表单控件',
		FAILED_TO_INITALIZE: '初始化失败',
		CUSTOM_SCRIPT_ERROR: '用户自定义脚本执行错误:',
		CONFIRM_END_FREE_PROCESS: '本流程为自由流程，可以随时结束，确认要结束该工作流程吗？',
		FORCED_SIGN_OPINION: '本步骤为强制会签，请填写会签意见',
		CONFIRM_FINISH_WORK: '确认该工作已经办理完毕吗？',
		CONFIRM_UNSAVE_WORK: '确认不保存此工作吗？',
		ATLEAST_SELECT_ONE_STEP: '请至少选择一个步骤进行转交',
		AGENT: '经办人',
		NOT_ALL_FINISHED_DESC: '尚未办理完毕，不能转交流程',
		CONFIRM_END_FLOW: '确认要结束流程吗',
		NOTFINISHED_CONFIRM: '尚未办理完毕，确认要转交下一步骤吗？',
		APPOINT_THE_HOST: '请指定所选步骤的主办人',
		TRUN_SUCEESS: '转交操作完成',
		COMPLETE_SUCEESS: '流程顺利办理完毕',
		DESIGNATED_THE_PRINCIPAL: '请指定委托人',
		DESIGNATED_THE_CLIENT: '请指定被委托人',
		HOW_LONG_IS_THE_DELAY: '要延期多久？',
		DELAY_CUSTOM_TIME_ERROR: '自定义时间不能小于当前时间',
		CONFIRM_RESTORE: '确认要恢复该工作流至办理中吗？',
		DELAY_OPT_SUCCESS: '已成功将工作延期',
		RESTORE_SUCCESS: '已恢复该工作流至办理中',
		CONFIRM_DEL_RUN: '确定要删除该工作吗？',
		CONFIRM_DEL_SELECT_RUN: '确定要删除选中工作吗？',
		CONFIRM_END_SELECT_RUN: '确定要结束选中工作吗？',
		HAS_ENDED : '已结束',
		GROUPING_FIELD_CAN_NOT_HIDE: '分组字段不能隐藏',
		SELECT_FLOW_TYPE: '请选择流程类型',
		// 表单设计器
		JS_EXT: 'js脚本扩展',
		CSS_EXT: 'css样式扩展',
		MACRO_EXT: '宏标记',
		SEND_TODO_REMIND: '发送超时催办提醒',
		ALREADY_SEND_REMIND: '已发送提醒',
		CONFIRM_REDO: '确定要让此经办人重新会签吗？',
		CONFIRM_DEL_FB: '确认要删除该条会签吗？这将会删除关联的附件及点评',
		CONFIRM_RESTORE_RUN: '确认要恢复选中工作至办理中吗？',
		CONFIRM_DESTROY_RUN: '确认要永久销毁选中工作吗？',
		CONFIRM_CALLBACK: '下一步骤尚未接收时可收回至本步骤重新办理，确认要收回吗？',
		ALREADY_RECEIVED: '对方已接收，不能收回',
		NO_ACCESS_TAKEBACK: '抱歉，您没有权限收回工作流',
		EXAM_FLOW: '校验流程',
		ENTER_FALLBACK_REASON: '回退至上一步骤，请输入回退原因',
		ADD_ENTRUST_RULE: '添加委托记录',
		TIME_CANNOT_BE_EMPTY:'请选择一个时间范围',
		CONFIRM_DEL_RULE:'确认要删除选中规则吗?',
		PLEASE_ENTER_THE_PACKAGE_NAME : '请输入方案名字',
		NEW_PLAN_EXAMPLE: '新建方案一',
		SELECT_FALLBACK_STEP:'选择回退步骤'
	},

	/* --------- Index*/
	IN: {
		MODULE_NOT_FOUND: '未找到模块<%=modname%>',
		MANAGE_MY_MODULE: '管理我的模块',
		RESET_SETTINGS: '恢复默认设置'
	},

	/* ---------- Report 工作总结与计划 */
	RP: {
		SURE_DEL_REPORT: '确认要删除选中的总结与计划吗？此操作不可恢复',
		REPORT_TYPE_SETTING: '汇报类型设置',
		SORT_CAN_NOT_BE_EMPTY: '序号不能为空',
		SORT_ONLY_BE_POSITIVEINT: '序号只能为正整数',
		TYPENAME_CAN_NOT_BE_EMPTY: '类型不能为空',
		INTERVALS_CAN_NOT_BE_EMPTY: '区间天数不能为空',
		INTERVALS_ONLY_BE_POSITIVEINT: '区间天数只能为正整数',
		SURE_DEL_REPORT_TYPE: '将会删除该类型下的所有汇报，确认要删除该汇报类型吗？'
	},
	VOTE: {
		MAX_ITEM: "最多可选择<%=count%>项",
		SINGLE_ITEM: "单项"
	},

	/* ----------- 微博 */
	WB: {
		PUBLISH_SUCCESS: "发布成功",
		FEED_NEW_MSG: "有 <%=count%> 条新微博，点击查看",
		FOLLOW: "关注",
		FOLLOWING: "关注中...",
		UNFOLLOW: "取消关注",
		UNFOLLOWING: "取消中...",
		FOLLOWBOTH: "相互关注",
		FOLLOWED: "已关注",
		FORWARD: "转发微博",
		FORWARD_SUCCESS: "转发成功",
		ISLOADING: "正在加载中，请稍候...",
		DIGGUSER: "赞过的人",
		REMOVE_FEED_CONFIRM: "确定要删除该条微博吗？",
		SPECIFY_FEED_USER: '请指定可见人员范围',
		// dashboard
		REMOVE_SELECT_FEEDS_CONFIRM: "确定要删除选中的动态吗？",
		DELETE_SELECT_FEEDS_CONFIRM : "确定要彻底删除选中的动态吗？",
		REMOVE_SELECT_COMMENTS_CONFIRM: "确定要删除选中的评论吗？",
		DELETE_SELECT_COMMENTS_CONFIRM : "确定要彻底删除选中的评论吗？",
		REMOVE_SELECT_TOPICS_CONFIRM: "确定要删除选中的话题吗？",
		RECOVER_SELECT_FEED_CONFIRM: "确定要恢复选中的动态？",
		RECOVER_SELECT_COMMENTS_CONFIRM: "确定要恢复选中的评论？",
		DIGG: '赞',
		DIGGED: '已赞',
		VIEWDIGGLIST:'查看赞我的人员',
		VIEWALLOWEDLIST:'允许查看的人员'
	},

	MSG: {
		NOTIFY_REMOVE_CONFIRM: '你确认删除选中提醒吗？该操作不可恢复'
	},

	// 评论
	COMMENT: {
		SUCCESS: "评论成功"
	},

	DATE: {
		DAYSTR: "日一二三四五六",
		MONTHSTR: "一月,二月,三月,四月,五月,六月,七月,八月,九月,十月,十一月,十二月"
	}
};
