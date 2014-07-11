DROP TABLE IF EXISTS `{{auth_assignment}}`;
CREATE TABLE `{{auth_assignment}}` (
  `itemname` varchar(64) NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `bizrule` text NOT NULL COMMENT '关联到这个项目的业务逻辑',
  `data` text NOT NULL COMMENT '当执行业务规则的时候所传递的额外的数据',
  PRIMARY KEY (`itemname`,`userid`),
  CONSTRAINT `itemname` FOREIGN KEY (`itemname`) REFERENCES `{{auth_item}}` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{auth_item}}`;
CREATE TABLE `{{auth_item}}` (
  `name` varchar(64) NOT NULL COMMENT '项目名字',
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL COMMENT '项目描述',
  `bizrule` text NOT NULL COMMENT '关联到这个项目的业务逻辑',
  `data` text NOT NULL COMMENT '当执行业务规则的时候所传递的额外的数据',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{auth_item_child}}`;
CREATE TABLE `{{auth_item_child}}` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  CONSTRAINT `parent` FOREIGN KEY (`parent`) REFERENCES `{{auth_item}}` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `child` FOREIGN KEY (`child`) REFERENCES `{{auth_item}}` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{node}}`;
CREATE TABLE `{{node}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `module` varchar(30) NOT NULL COMMENT '模块名',
  `key` varchar(20) NOT NULL COMMENT '授权节点key',
  `node` varchar(20) NOT NULL COMMENT '子节点(如果有)',
  `name` varchar(20) NOT NULL COMMENT '节点名称',
  `group` varchar(20) NOT NULL COMMENT '分组',
  `category` varchar(20) NOT NULL COMMENT '分类',
  `type` enum('data','node') NOT NULL DEFAULT 'node' COMMENT '节点类型',
  `routes` text NOT NULL COMMENT '路由',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{node_related}}`;
CREATE TABLE `{{node_related}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `positionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '岗位id',
  `module` varchar(30) NOT NULL COMMENT '模块名称',
  `key` varchar(20) NOT NULL COMMENT '授权节点key',
  `node` varchar(20) NOT NULL COMMENT '节点（如果有）',
  `val` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '数据权限',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`module`,`key`,`positionid`,`node`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position}}`;
CREATE TABLE `{{position}}` (
  `positionid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '岗位id',
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '岗位分类',
  `posname` char(20) NOT NULL COMMENT '职位名称',
  `sort` mediumint(8) NOT NULL DEFAULT '0' COMMENT '排序序号',
  `goal` text NOT NULL COMMENT '职位权限',
  `minrequirement` text NOT NULL COMMENT '最低要求',
  `number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '在职人数',
  PRIMARY KEY (`positionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position_category}}`;
CREATE TABLE `{{position_category}}` (
  `catid` mediumint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '岗位分类id',
  `pid` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '职位权限',
  `name` char(20) CHARACTER SET utf8 NOT NULL COMMENT '职位名称',
  `sort` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序id 默认跟cid一致自动递增',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position_responsibility}}`;
CREATE TABLE `{{position_responsibility}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '职责范围与衡量标准id',
  `positionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属岗位的id',
  `responsibility` text NOT NULL COMMENT '职责范围',
  `criteria` text NOT NULL COMMENT '衡量标准',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position_related}}`;
CREATE TABLE `{{position_related}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `positionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '岗位id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{{position_category}}` (`catid`, `pid`, `name`, `sort`) VALUES ('1', '0', '默认分类', '1');
INSERT INTO `{{position}}` (`catid`, `posname`, `sort`, `goal`, `minrequirement`, `number`) VALUES ('1', '总经理', '1', '', '', '0');
INSERT INTO `{{position}}` (`catid`, `posname`, `sort`, `goal`, `minrequirement`, `number`) VALUES ('1', '部门经理', '2', '', '', '0');
INSERT INTO `{{position}}` (`catid`, `posname`, `sort`, `goal`, `minrequirement`, `number`) VALUES ('1', '职员', '3', '', '', '0');