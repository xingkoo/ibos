/**
 * 后台模块中文语言包
 * 
 */

var L = L || {};
L.DB = {
	"TIP": "提示",
	// Index
	"SHUTDOWN_SYSTEM_FAILED": "关闭系统失败",
	"LOAD_SECURITY_INFO_FAILED": "载入提示失败",
	"LICENSE_KEY": "授权KEY",
	"ENTER_LICENSEKEY": "请输入授权码...",

	// 全局 -- 积分设置
	"CREDIT_RULE_NUM_OVER": "已经超过新增规则指定条数",
	"CREDIT_TIP": "总积分是衡量用户级别的唯一标准，您可以在此设定用户的总积分计算公式，公式中可使用包括 + - * / () 在内的运算符号",

	// 全局 -- 性能优化
	"SPHINX_PORT_TIP": "例如，9312，主机名填写 socket 地址的，则此处不需要设置",
	"SPHINX_HOST_TIP": "本地主机填写“localhost”，或者填写 Sphinx 服务 socket 地址，必须是绝对地址：例如，/tmp/sphinx.sock",

	// 全局 -- 即时通讯绑定
	"BIND_USER": "绑定用户",
	"BIND_SUCCESS": "绑定成功", 
	"BIND_FAILED": "未知错误，绑定失败", 

	"RTX_SYNC_CONFIRM": "确认要开始同步吗？该操作不可恢复！", 
	"RTX_SYNC_TITLE": "同步组织架构",
	"RTX_SYNC_CONTENT": "同步中，请稍等……", 
	"RTX_SYNC_SUCCESS": "同步成功完成",

	// 界面 -- 快捷导航设置
	"REMOVE_QUICKNAV_CONFIRM" :"确定要删除该快捷导航吗？",

	//界面 -- 后台导航设置
	"REMOVE_CHILD_NAVIGATION" :"请先将子导航移除!",

	//界面 -- 导航设置
	"SINGLE_PAGE" : "单页图文",
	"PREVIEW" : "预览"
}