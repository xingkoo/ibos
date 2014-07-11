DROP TABLE IF EXISTS `{{flow_category}}`;
CREATE TABLE `{{flow_category}}` (
  `catid` mediumint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '工作流分类id',
  `name` char(20) NOT NULL COMMENT '分类名称',
  `sort` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序id',
  `deptid` text NOT NULL COMMENT '所属部门ID',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{flow_type}}`;
CREATE TABLE `{{flow_type}}` (
  `flowid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流程主键ID',
  `name` varchar(200) NOT NULL COMMENT '流程名',
  `formid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '表单ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '流程类型(1:固定流程 2:自由流程)',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '1' COMMENT '流程排序号',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '流程分类ID',
  `autonum` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '自动编号计数器',
  `autolen` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '自动编号显示长度',
  `autoname` text NOT NULL COMMENT '自动文号表达式',
  `desc` text NOT NULL COMMENT '流程说明',
  `autoedit` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '新建工作时是否允许手工修改文号：(0:不允许，1:允许)',
  `allowattachment` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许附件 (1:允许 0:不允许)',
  `allowversion` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许版本控制 （0:不允许 1:允许）',
  `newuser` text NOT NULL COMMENT '自由流程新建权限：分为按用户、部门、岗位三种授权方式，形成“用户ID串|部门ID串|岗位ID串”格式的字符串，这三种字符串都是逗号分隔的字符串',
  `queryitem` text NOT NULL COMMENT '查询字段串',
  `deptid` text NOT NULL COMMENT '所属部门ID',
  `freepreset` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许预设步骤 （1:允许，0:否）',
  `freeother` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '委托类型 【0:禁止委托,1:仅允许委托当前步骤经办人(本步骤的实际经办人，该步骤可能指定了五个人，但是转交时选择了三个),2:自由委托，3:按步骤设置的经办权限(跟1的区别是按照定义的经办人来委托)】',
  `listfieldstr` text NOT NULL COMMENT '列表查询字段串查询页面仅查询该流程时生效',
  `forcepreset` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否强制修改文号(1-是,其他-否),新建工作时是否允许手工修改文号为(2-允许在自动文号前输入前缀,3-允许在自动文号后输入后缀,4-允许在自动文号前后输入前缀和后缀,时可设置该选项)',
  `modelid` text NOT NULL COMMENT '流程对应模块ID',
  `modelname` text NOT NULL COMMENT '流程对应模块名称',
  `attachmentid` text NOT NULL COMMENT '说明文档附件ID串',
  `usestatus` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '工作流前台状态。(1:可见,所有用户都可以在前台新建工作里看到该流程，但无权限用户不能点击。2:只有拥有权限的用户才能在前台新建工作中看到，并可点击,3:无论用户有无权限，都不会在前台新建工作中显示)',
  `guideprocess` varchar(20) NOT NULL DEFAULT '1' COMMENT '流程引导 - 当前完成步骤，用逗号分隔字符串相连',
  PRIMARY KEY (`flowid`),
  KEY `catid` (`catid`),
  KEY `formid` (`formid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='工作流类型表';

DROP TABLE IF EXISTS `{{flow_process}}`;
CREATE TABLE `{{flow_process}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `flowid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程ID',
  `processid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '步骤ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '节点类型 (0:步骤节点 1:子流程节点 2:外部流转节点)',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '步骤名称',
  `processitem` text NOT NULL COMMENT '可写字段串',
  `processto` text NOT NULL COMMENT '转交步骤字符串',
  `hiddenitem` text NOT NULL COMMENT '保密字段串',
  `uid` text NOT NULL COMMENT '经办人ID串',
  `deptid` text NOT NULL COMMENT '经办部门ID串',
  `positionid` text NOT NULL COMMENT '经办岗位ID串',
  `setleft` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '节点横坐标',
  `settop` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '节点纵坐标',
  `plugin` text NOT NULL COMMENT '转交调用插件',
  `pluginsave` text NOT NULL COMMENT '保存调用插件',
  `processitemauto` text NOT NULL COMMENT '允许在不可写情况下自动赋值的宏控件',
  `feedback` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许会签 (0:允许会签 1:禁止会签 2:强制会签)',
  `autotype` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '自动选人规则 (1:自动选择流程发起人 2:自动选择本部门主管 3:指定自动选择默认人员 4:自动选择上级主管领导 5:自动选择部门一级主管 6:自动选择上级分管领导 7:按表单字段选择 8:自动选择指定步骤主办人 9:自动选择本部门助理 10:自动选择本部门内符合条件的所有人员，11:自动选择本一级部门内符合条件的所有人员,)',
  `autouserop` text NOT NULL COMMENT '指定自动选择默认人员 - 主办人',
  `autouser` text NOT NULL COMMENT '指定自动选择默认人员 - 经办人',
  `userfilter` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '选人过滤规则 (1:只允许选择本部门经办人 2:只允许选择本岗位经办人 3:只允许选择上级部门经办人 4:只允许选择下级部门经办人 )',
  `timeout` varchar(20) NOT NULL DEFAULT '0' COMMENT '办理时限',
  `timeoutmodify` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许转交时设置办理时限(0:不允许 1:允许)',
  `signlook` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '会签意见可见性 (0:总是可见 1:本步骤经办人之间不可见 2: 针对其他步骤不可见)',
  `topdefault` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '主办人相关选项 (0:明确指定主办人 1:先接收者为主办人 2:无主办人会签)',
  `userlock` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许修改主办人相关选项及默认经办人 （0：不允许,1:允许）',
  `mailto` text NOT NULL COMMENT '转交时邮件通知以下人员ID串',
  `syncdeal` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许并发( 0:禁止并发 1:允许并发 2:强制并发)',
  `turnpriv` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '强制转交，经办人未办理完毕时是否允许主办人强制转交 (0:不允许 1:允许)',
  `childflow` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '子流程的流程ID',
  `gathernode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '并发合并选项 (0:非强制合并 1:强制合并)',
  `allowback` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许回退 (0:不允许 1:允许回退上一步骤 2:允许回退之前步骤)',
  `attachpriv` varchar(10) NOT NULL DEFAULT '1,2,3,4,5' COMMENT '公共附件中的office文档详细权限设置',
  `autobaseuser` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '部门针对对象步骤的ID，0为当前步骤.配合自动选人规则使用。当自动选人规则为以下选项时启用(2-自动选择本部门主管,4-自动选择上级主管领导,6-自动选择上级分管领导,9-自动选择本部门助理,)',
  `relationin` text NOT NULL COMMENT '父流程 -> 子流程映射关系',
  `relationout` text NOT NULL COMMENT '子流程->父流程映射关系',
  `timeouttype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:本步骤接收后开始计时 1:上一步骤转交后开始计时',
  `attacheditpriv` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许本步骤经办人编辑附件 (0:不允许 1:允许)',
  `controlmode` text NOT NULL COMMENT '列表控件模式(1-修改模式,2-添加模式,3-删除模式,保存格式如下例：列表控件1,列表控件2,|1`2`3,1`2,)',
  `fileuploadpriv` text NOT NULL COMMENT '附件上传控件的权限(1:新建 2:编辑 3:删除 4: 下载 5:打印)',
  `checkitem` text NOT NULL COMMENT '要进行规则验证的字段',
  PRIMARY KEY (`id`),
  KEY `flowid` (`flowid`),
  KEY `processid` (`processid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{flow_manage_log}}`;
CREATE TABLE `{{flow_manage_log}}` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `flowid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程ID',
  `flowname` varchar(200) NOT NULL COMMENT '流程名称',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '操作用户唯一标识即用户表主键ID',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `ip` varchar(20) NOT NULL COMMENT '操作用户的IP地址',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '日志类型(1-增加,2-修改,3-删除,但实际1、2类型存的比较混乱)',
  `content` text NOT NULL COMMENT '日志内容',
  PRIMARY KEY (`id`),
  KEY `type` (`type`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `time` (`addtime`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='流程管理日志';

DROP TABLE IF EXISTS `{{flow_timer}}`;
CREATE TABLE `{{flow_timer}}` (
  `tid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `flowid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程ID',
  `uid` text NOT NULL COMMENT '发起人ID串',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '提醒类型(1-仅此一次,2-按日,3-按周,4-按月,5-按年,)',
  `reminddate` varchar(10) NOT NULL DEFAULT '' COMMENT '提醒日期(1-仅此一次，存具体日期,2-按日，为空,3-按周，存星期几,4-按月，存每月几号,5-按年，存每年几月几号,)',
  `remindtime` varchar(20) NOT NULL COMMENT '提醒时间',
  `lasttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最近一次提醒时间',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设计流程定时设置';

DROP TABLE IF EXISTS `{{flow_version}}`;
CREATE TABLE `{{flow_version}}` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `runid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程实例ID',
  `processid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程实例步骤ID',
  `flowprocess` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程步骤ID',
  `itemid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '表单字段ID',
  `itemdata` text NOT NULL COMMENT '表单字段的数据',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `mark` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '版本号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='流程版本控制(历史数据)';

DROP TABLE IF EXISTS `{{flow_run_process}}`;
CREATE TABLE `{{flow_run_process}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `runid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程实例ID',
  `processid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程实例步骤ID',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户ID',
  `processtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工作接收时间',
  `delivertime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工作转交/办结时间',
  `flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '步骤状态(1-未接收,2-办理中,3-转交下一步，下一步经办人无人接收,4-已办结,5-自由流程预设步骤,6-已挂起,)',
  `flowprocess` mediumint(8) NOT NULL DEFAULT '0' COMMENT '步骤ID[设计流程中的步骤号]',
  `opflag` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否主办(0-经办,1-主办)',
  `topflag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主办人选项(0-指定主办人,1-先接收者主办,2-无主办人会签,)',
  `parent` text NOT NULL COMMENT '上一步骤ID',
  `childrun` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '子流程的流程实例ID',
  `freeitem` text NOT NULL COMMENT '步骤可写字段[仅自由流程且只有主办人生效]',
  `otheruser` text NOT NULL COMMENT '工作委托用户ID串',
  `timeoutflag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否超时(1-超时,其他-不超时)',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '工作创建时间',
  `fromuser` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '工作移交用户ID',
  `activetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '取消挂起的时间',
  `comment` text NOT NULL COMMENT '批注',
  `processdept` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '超时统计查询增加部门',
  `isfallback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是回退步骤',
  PRIMARY KEY (`id`),
  KEY `processflag` (`flag`) USING BTREE,
  KEY `runid` (`runid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `processid` (`runid`,`processid`) USING BTREE,
  KEY `ruid` (`runid`,`uid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='流程实例步骤信息';

DROP TABLE IF EXISTS `{{flow_run_log}}`;
CREATE TABLE `{{flow_run_log}}` (
  `logid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `runid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程实例ID',
  `runname` varchar(200) NOT NULL DEFAULT '' COMMENT '流程实例名称',
  `flowid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程ID',
  `processid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程实例步骤ID[流程实例实际步骤号]',
  `flowprocess` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程步骤ID[设计流程步骤号]',
  `uid` mediumint(8) NOT NULL COMMENT '操作人ID',
  `toid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '被操作人ID',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人IP地址',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '日志类型：(1-新建、转交、回退、收回,2-委托,3-删除,4-销毁,5-还原被终止的流程,6-编辑数据,7-流转过程中修改经办权限,)',
  `content` text NOT NULL COMMENT '日志信息',
  PRIMARY KEY (`logid`),
  KEY `runid` (`runid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `ip` (`ip`) USING BTREE,
  KEY `runname` (`runname`) USING BTREE,
  KEY `flowid` (`flowid`) USING BTREE,
  KEY `time` (`time`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='流程实例日志';

DROP TABLE IF EXISTS `{{flow_run_feedback}}`;
CREATE TABLE `{{flow_run_feedback}}` (
  `feedid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `runid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程实例ID',
  `processid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程实例步骤ID[实际步骤号]',
  `flowprocess` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程步骤号[设计流程中的步骤号]',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `content` text NOT NULL COMMENT '会签意见内容',
  `attachmentid` text NOT NULL COMMENT '附件ID串',
  `edittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最近一次会签意见的保存时间',
  `feedflag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '会签意见类型(1-点评意见,其他-会签意见)',
  `signdata` text NOT NULL COMMENT '手写签批意见',
  `replyid` mediumint(8) NOT NULL COMMENT '回复的是哪条意见ID',
  PRIMARY KEY (`feedid`),
  KEY `runid` (`runid`) USING BTREE,
  KEY `processid` (`processid`) USING BTREE,
  KEY `replyid` (`replyid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会签意见';

DROP TABLE IF EXISTS `{{flow_run}}`;
CREATE TABLE `{{flow_run}}` (
  `runid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流程实例ID',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '流程实例名称',
  `flowid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程ID',
  `beginuser` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程发起人ID',
  `begindept` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程发起人部门ID',
  `begintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '流程实例创建时间',
  `endtime` int(10) NOT NULL DEFAULT '0' COMMENT '流程实例结束时间',
  `attachmentid` text NOT NULL COMMENT '附件ID串',
  `delflag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除标记(0-未删除,1-已删除)删除后流程实例可在工作销毁中确实删除或还原',
  `focususer` text NOT NULL COMMENT '关注该流程的用户',
  `parentrun` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父流程ID',
  `archive` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否归档(0-否,1-是)',
  `forceover` text NOT NULL COMMENT '强制结束信息',
  `worklevel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '工作等级 0-普通 1-重要 2-紧急',
  PRIMARY KEY (`runid`),
  KEY `runid` (`runid`) USING BTREE,
  KEY `flowid` (`flowid`) USING BTREE,
  KEY `runname` (`name`) USING BTREE,
  KEY `begintime` (`begintime`) USING BTREE,
  KEY `endtime` (`endtime`) USING BTREE,
  KEY `user and run` (`beginuser`,`runid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='流程实例基本信息';

DROP TABLE IF EXISTS `{{flow_rule}}`;
CREATE TABLE `{{flow_rule}}` (
  `ruleid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `flowid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '委托人ID',
  `toid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '被委托人ID',
  `begindate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `enddate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0-关闭,1-开启)',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `createuser` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `updateuser` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改人',
  PRIMARY KEY (`ruleid`),
  KEY `flowid` (`flowid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `begindate` (`begindate`) USING BTREE,
  KEY `enddate` (`enddate`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='工作委托';

DROP TABLE IF EXISTS `{{flow_query_tpl}}`;
CREATE TABLE `{{flow_query_tpl}}` (
  `seqid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tplname` varchar(100) NOT NULL DEFAULT '' COMMENT '模板名称',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `flowid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程ID',
  `viewextfields` text COMMENT '扩展显示字段',
  `sumfields` text COMMENT '可见字段里的统计字段',
  `groupbyfields` text COMMENT '分组字段',
  `flowconditions` text COMMENT '流程过滤条件',
  `condformula` varchar(200) NOT NULL COMMENT '查询条件公式',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`seqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='流程查询模板';

DROP TABLE IF EXISTS `{{flow_permission}}`;
CREATE TABLE `{{flow_permission}}` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `flowid` mediumint(8) unsigned NOT NULL COMMENT '流程ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '授权类型(0-所有,1-管理,2-监控,3-查询,4-编辑,5-点评)',
  `scope` text NOT NULL COMMENT '管理范围[selforg-本机构,alldept-所有部门selfdept-本部门,部门ID串]',
  `uid` text NOT NULL COMMENT '按人员设定授权范围',
  `deptid` text NOT NULL COMMENT '按部门设定授权范围',
  `positionid` text NOT NULL COMMENT '按岗位设定授权范围',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理权限';

DROP TABLE IF EXISTS `{{flow_print_tpl}}`;
CREATE TABLE `{{flow_print_tpl}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '模版ID',
  `flowid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '流程ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '模版类型(1-打印模版,2-手写呈批单)',
  `name` varchar(100) NOT NULL COMMENT '打印模版名称',
  `content` text NOT NULL COMMENT '打印模版内容',
  `flowprocess` text NOT NULL COMMENT '可使用该模版的步骤',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `flowid` (`flowid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='打印模版';

DROP TABLE IF EXISTS `{{flow_form_version}}`;
CREATE TABLE `{{flow_form_version}}` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `formid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '表单ID',
  `printmodel` text NOT NULL COMMENT '表单设计信息',
  `printmodelshort` text NOT NULL COMMENT '精简后的表单设计信息',
  `script` text NOT NULL COMMENT '表单拓展脚本',
  `css` text NOT NULL COMMENT '表单扩展样式',
  `time` int(10) NOT NULL DEFAULT '0' COMMENT '版本时间',
  `mark` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '版本号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设计表单版本库';

DROP TABLE IF EXISTS `{{flow_form_type}}`;
CREATE TABLE `{{flow_form_type}}` (
  `formid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '表单ID',
  `formname` varchar(200) NOT NULL DEFAULT '' COMMENT '表单名称',
  `printmodel` text NOT NULL COMMENT '表单设计信息',
  `printmodelshort` text NOT NULL COMMENT '精简后的表单设计信息',
  `deptid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '表单所属部门',
  `script` text NOT NULL COMMENT '表单拓展脚本',
  `css` text NOT NULL COMMENT '表单扩展样式',
  `itemmax` mediumint(8) NOT NULL DEFAULT '0' COMMENT '最大的项目编号',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '表单所属分类',
  `isnew` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '表单类型 1 - 新表单 0 - 老表单',
  PRIMARY KEY (`formid`),
  KEY `deptid` (`deptid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设计表单';

DROP TABLE IF EXISTS `{{flow_process_turn}}`;
CREATE TABLE `{{flow_process_turn}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `flowid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '流程ID',
  `processid` mediumint(8) NOT NULL COMMENT '步骤ID',
  `to` mediumint(8) NOT NULL DEFAULT '0' COMMENT '下一步的ID',
  `processout` text CHARACTER SET utf8 NOT NULL,
  `conditiondesc` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '不符合条件公式时，给用户的文字描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`flowid`,`processid`,`to`),
  KEY `processid` (`processid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{{flow_category}}` VALUES ('1', '财务', '1','0');
INSERT INTO `{{flow_category}}` VALUES ('2', '销售', '2','0');
INSERT INTO `{{flow_category}}` VALUES ('3', '人事', '3','0');
INSERT INTO `{{flow_category}}` VALUES ('4', '行政', '4','0');
INSERT INTO `{{flow_category}}` VALUES ('5', '项目', '5','0');

INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wfremindbefore', '3h');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wfremindafter', '6m');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('sealfrom', '1');

INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('工作流设置','0','workflow','dashboard','param','','0','0');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('0','工作流','workflow/list/index','0','1','0','7','workflow');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_new_notice', '工作流新待办工作提醒', 'workflow', 'workflow/default/New run title', '','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_focus_notice', '工作流被关注提醒', 'workflow', 'workflow/default/New focus title', '','0','1','0','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_turn_notice', '工作流转交提醒', 'workflow', 'workflow/default/Turn next title', '','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_sign_notice', '工作流会签提醒', 'workflow', 'workflow/default/Sign title', '','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_entrust_notice', '工作流委托提醒', 'workflow', 'workflow/default/Entrust title', '','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_restore_delay_notice', '工作流延期恢复提醒', 'workflow', 'workflow/default/Restore delay', '','0','1','0','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_todo_remind', '工作流超时催办提醒', 'workflow', 'workflow/default/Todo remind', '','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('workflow_goback_notice', '工作流回退提醒', 'workflow', 'workflow/default/Goback remind', '','1','1','1','2');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('workflow','工作流','workflow/list/index&op=category','用于办理企业工作流程','7','1');