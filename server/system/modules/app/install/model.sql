DROP TABLE IF EXISTS `{{app}}`;
CREATE TABLE IF NOT EXISTS `{{app}}` (
  `appid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `catid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '应用名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '应用链接',
  `width` mediumint(5) NOT NULL DEFAULT '0' COMMENT '应用宽度',
  `height` mediumint(5) NOT NULL DEFAULT '0' COMMENT '应用高度',
  PRIMARY KEY (`appid`),
  KEY `CATID` (`catid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{app_category}}`;
CREATE TABLE IF NOT EXISTS `{{app_category}}` (
  `catid` int(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `pid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '父类id',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '应用分类名称',
  `description` varchar(100) NOT NULL DEFAULT '' COMMENT '应用分类名称描述',
  `sort` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  PRIMARY KEY (`catid`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{app_personal}}`;
CREATE TABLE IF NOT EXISTS `{{app_personal}}` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `widget` text NOT NULL COMMENT '板块应用，逗号隔开的应用ID',
  `shortcut` text NOT NULL COMMENT '其它应用，逗号隔开的应用ID',
  PRIMARY KEY (`id`),
  KEY `UID` (`uid`) USING BTREE
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{{app_category}}`( `pid`, `name`, `description`, `sort`) VALUES ('0','office', '办公应用', '0');
INSERT INTO `{{app_category}}`( `pid`, `name`, `description`, `sort`) VALUES ('0','personal', '个人应用', '1');
INSERT INTO `{{app_category}}`( `pid`, `name`, `description`, `sort`) VALUES ('0','tool', '网管工具', '2');
INSERT INTO `{{app_category}}`( `pid`, `name`, `description`, `sort`) VALUES ('0','express', '快递查询', '3');
INSERT INTO `{{app_category}}`( `pid`, `name`, `description`, `sort`) VALUES ('0','entertainment', '休闲娱乐', '4');

INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1','有道词典','http://apps1.bdimg.com/store/static/kvt/98ebb9d2b28acf259c820e12e187ae21.jpg','http://dict.youdao.com/app/baidu?bd_user=1497459799&bd_sig=b64a72260604ad2f3470282eee521ffd&canvas_pos=platform','0','500');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '发票真伪查询', 'http://apps2.bdimg.com/store/static/kvt/a66e44b16ed76f900af3b558c3532444.jpg', 'http://fapiao.youshang.com/app/baidu.html?bd_user=1497459799&bd_sig=a896dcd0a2c321a412ecca11a37f586e&canvas_pos=platform', '0', '500');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '个人所得税计算', 'http://apps2.bdimg.com/store/static/kvt/71dc48b31d8ebc75a986aff004509c6e.jpg', 'http://jisuanqi.duapp.com/geshui?bd_user=1497459799&bd_sig=4311155ee9331892dadb9002b615a591&canvas_pos=platform', '0', '600');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '身份证号码查询', 'http://apps2.bdimg.com/store/static/kvt/55ccc29065fd7238f1b9aa302608597f.gif', 'http://baidu.uqude.com/baidu_mobile_war/pages/identitycard/index_uqude.html?bd_user=1497459799&bd_sig=d9d5b64e2b97a5462a77ea73e2bb671b&canvas_pos=platform', '0', '450');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '违章查询', 'http://apps3.bdimg.com/store/static/kvt/60209125b9706bb81a16ebc856ed3b23.png', 'http://mobile.auto.sohu.com/wzcxWeb/baidu.at?bd_user=1497459799&bd_sig=6fbf2e1bad249b352a4ca3bebcbc630e&canvas_pos=platform', '0', '0');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '美图秀秀', 'http://apps.bdimg.com/store/static/kvt/9e80a4e76bdbf22ff6cfc8c9ae564dfe.gif', 'http://xiuxiu.web.meitu.com/baidu?bd_user=1497459799&bd_sig=7ef4eed6a0dbf1d675f213ef1a7d5462&canvas_pos=platform', '800', '500');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '宽带测速', 'http://apps1.bdimg.com/store/static/kvt/5994144c39a53cb0fbd2e8f73c76f0d4.gif', 'http://app.yesky.com/cms/a/speed?bd_user=1497459799&bd_sig=903d2bf62b7d386aa767d18b1655a888&canvas_pos=platform', '0', '500');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('1', '图片批量处理', 'http://apps1.bdimg.com/store/static/kvt/f622d8f95b8b3c8a5c614b2c5b65068f.jpg', 'http://picgod.duapp.com/app/baidu.php?bd_user=1497459799&bd_sig=c915ee1c3ca170341fbff0cce1df86b6&canvas_pos=platform', '578', '469');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '365日历', 'http://apps.bdimg.com/store/static/kvt/340238f487321ab424dd471680e314a3.jpg', 'http://baidu365.duapp.com/wnl.html?bd_user=1497459799&bd_sig=996e3fd785005091aa73f120602c9856&canvas_pos=platform', '575', '439');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '自我评价', 'http://apps3.bdimg.com/store/static/kvt/525f725d9b61a4026d9e6ac2c190bb8d.jpg', 'http://qiqiapp3.duapp.com/zwpingjia?bd_user=1497459799&bd_sig=9b22f68c454fa95c3ee477ac1a34c622&canvas_pos=platform', '0', '475');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '择吉良日', 'http://apps3.bdimg.com/store/static/kvt/8f44628d9c67b79109f279d209d860c1.jpg', 'http://www.nnhs.com.cn/time/rili.html?bd_user=1497459799&bd_sig=4134161297b7d949e23d8ee21407090a&canvas_pos=platform', '800', '570');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '励志签名', 'http://apps1.bdimg.com/store/static/kvt/cd58224b9c65657e41401b957f95347f.jpg', 'http://qianmingapp.duapp.com/?s_param=author%3D11&bd_param=author%3D11&bd_user=1497459799&bd_sig=c2aa49968ed8d14e276c8e64efe30940&canvas_pos=platform', '0', '540');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '简历模板', 'http://apps.bdimg.com/store/static/kvt/1b5aa4b32ce42b5bc78d294e43f368a0.jpg', 'http://qqdaquan.duapp.com/pages/jianli/index.html?bd_user=1497459799&bd_sig=b702f5009709ff7a8b63620272004039&canvas_pos=platform', '800', '490');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '提前还贷计算器', 'http://apps3.bdimg.com/store/static/kvt/550e24b0a81d36e810c90f6cb76f00c5.jpg', 'http://repayment.sinaapp.com/repayment/count.html?bd_user=1497459799&bd_sig=b942ed3de289a5b4156e526c1d1a8731&canvas_pos=platform', '0', '500');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('2', '围巾的系法图解', 'http://apps1.bdimg.com/store/static/kvt/a61162c360dcc53cd3091c847d17129c.jpg', 'http://qqgexingfenzu.duapp.com/weijing?bd_user=1497459799&bd_sig=be232d062fb18f0f80845108b952da99&canvas_pos=platform', '0', '540');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'linux命令', 'http://apps.bdimg.com/store/static/kvt/4d404d618a7e36d988c28526c26cf689.jpg', 'http://linuxso.duapp.com/?bd_user=1497459799&bd_sig=904a16db303eaac1f9fdc92a730c5973&canvas_pos=platform', '0', '350');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'dos命令大全', 'http://apps.bdimg.com/store/static/kvt/5f82d03a914aaf0602a87016743caf54.jpg', 'http://bcs.duapp.com/youxiok/dos/index.html?bd_user=1497459799&bd_sig=6b12b7b6550f0e7fcf11b1a9ee3d3f7b&canvas_pos=platform', '0', '384');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'URL编码解码', 'http://apps2.bdimg.com/store/static/kvt/02ecdf4cdb0c17baabdc0bbd74c44e1d.png', 'http://ydtool.duapp.com/url_en-decode/?bd_user=1497459799&bd_sig=075c2bb358271f86a7c7dca17f9dc749&canvas_pos=platform', '0', '410');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'GUID生成工具', 'http://apps2.bdimg.com/store/static/kvt/3c4955c2e4e65521164f931fb85e5c1b.jpg', 'http://toolmao.duapp.com/guid?bd_user=1497459799&bd_sig=6d4d14f220f1bc05a6d1250ffac25580&canvas_pos=platform', '0', '337');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'JSON数据解析', 'http://apps1.bdimg.com/store/static/kvt/8bc5a8aaff1cc511b8a0e84ce62ca2bc.jpg', 'http://xssreport.sinaapp.com/baiduapp/json?bd_user=1497459799&bd_sig=9bb645e0b3be830061d16cf2b2ec5146&canvas_pos=platform', '0', '337');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'base64在线解码', 'http://apps.bdimg.com/store/static/kvt/82afcc22ba0dfa8f2272a5a55b33d484.jpg', 'http://phpclubs.duapp.com/base64?bd_user=1497459799&bd_sig=c004a4e7632307088cbcebfb496ac93e&canvas_pos=platform', '0', '444');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'UTF8 转换工具', 'http://apps.bdimg.com/store/static/kvt/58539eae495c61f61522c50866299351.png', 'http://mytools.duapp.com/tooss/utf8?bd_user=1497459799&bd_sig=7f723a299f2fdf5048469ca364d83fea&canvas_pos=platform', '0', '448');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'JS代码压缩', 'http://apps3.bdimg.com/store/static/kvt/801c651923262726ca97b714470d2bbf.png', 'http://jsmin.51240.com/', '800', '580');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'HTML/UBB互转', 'http://apps3.bdimg.com/store/static/kvt/8b3a43fce2168fcabe5c33a32e6f0059.png', 'http://mytools.duapp.com/tooss/ubb?bd_user=1497459799&bd_sig=33ee5e53702340354a3918ce6f53f4f9&canvas_pos=platform', '0', '600');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('3', 'HTML to JS', 'http://apps.bdimg.com/store/static/kvt/961c5c5ac61fc8546cf3703a4f18238e.jpg', 'http://www.vsyo.com/app/htmltojs.htm?bd_user=1497459799&bd_sig=f313238e81eda24f9ea8ec5c39aa9540&canvas_pos=platform', '0', '455');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '顺丰快递查询', 'http://apps3.bdimg.com/store/static/kvt/08ec2f59089757006d40237604683e56.png', 'http://baidu.kuaidi100.com/all.html?com=shunfeng&bd_user=1497459799&bd_sig=d1d6e4b0f94e3290ecf28e9c1ad17b41&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '中通快递查询', 'http://apps1.bdimg.com/store/static/kvt/e260ad5f4706893e53de8fc2e2fa1ea5.jpg', 'http://baidu.kuaidi100.com/all2.html?com=zhongtong&bd_user=1497459799&bd_sig=3fce29b477b4ade590c05ae235432bb6&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '申通快递查询', 'http://apps3.bdimg.com/store/static/kvt/5c85f4fdf8da45f2b3c0a3b4870ddbd3.png', 'http://baidu.kuaidi100.com/all2.html?com=shentong&bd_user=1497459799&bd_sig=b05205a19eb175d5781ecd7db33a62c5&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '圆通快递查询', 'http://apps1.bdimg.com/store/static/kvt/f61e561f22d4e8e22b829e15efba11a0.jpg', 'http://baidu.kuaidi100.com/all.html?com=yuantong&bd_user=1497459799&bd_sig=e22f0bf2f01be7ba8db827ebd14e5b90&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '韵达快递查询', 'http://apps1.bdimg.com/store/static/kvt/5815d5b08a2bc52bb197719a5f8ca976.jpg', 'http://baidu.kuaidi100.com/all.html?com=yunda&bd_user=1497459799&bd_sig=5f40dc520b0476c168d75acfd3ae2f15&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', 'EMS快递查询', 'http://apps.bdimg.com/store/static/kvt/19670586e802adf4791aeb4a38088970.gif', 'http://baidu.kuaidi100.com/all1.html?com=ems&bd_user=1497459799&bd_sig=65ffc3c941d5c9b820fdedcdd08e0f8e&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '德邦物流查询', 'http://apps2.bdimg.com/store/static/kvt/e46dc81559edf56c7afd964ea08865d1.gif', 'http://baidu.kuaidi100.com/all.html?com=debangwuliu&bd_user=1497459799&bd_sig=549be5d8e0a35ae0511076236333e6b0&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '天天快递查询', 'http://apps3.bdimg.com/store/static/kvt/302a4c871d98db6a5ab14595a5bcf717.png', 'http://baidu.kuaidi100.com/all2.html?com=tiantian&bd_user=1497459799&bd_sig=3f1bb841db83d43ce01aaea3e95ac3e2&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '百世汇通快递查询', 'http://apps.bdimg.com/store/static/kvt/83b1d98815b1f7e9d36f724e223e14ae.png', 'http://baidu.kuaidi100.com/all.html?com=huitongkuaidi&bd_user=1497459799&bd_sig=8cc8052719d5c6817aa8295bddcb0977&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('4', '宅急送快递查询', 'http://apps1.bdimg.com/store/static/kvt/9e1ac9ebecacbfdf9527ef1d06fbf91c.jpg', 'http://baidu.kuaidi100.com/all2.html?com=zhaijisong&bd_user=1497459799&bd_sig=24c7e554efbc5a51b78ac7f218584a9a&canvas_pos=platform', '0', '380');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('5', 'PPTV直播', 'http://apps3.bdimg.com/store/static/kvt/64b39f0f7e6caa2b498f4dfcd073f168.jpg', 'http://app.aplus.pptv.com/tgapp/baidu/live/main?bd_user=1497459799&bd_sig=38189a33f187726d2aa79ad41aa32c42&canvas_pos=platform', '534', '800');
INSERT INTO `{{app}}`( `catid`, `name`, `icon`, `url`, `width`, `height`) VALUES ('5', '植物大战僵尸', 'http://apps3.bdimg.com/store/static/kvt/f5d3e48a5aedf458eae6f66a44623fe9.jpg', 'http://apps.bdimg.com/tools/popcap/plantsvszombies/game.html?bd_user=1497459799&bd_sig=77adf2f60db7d426b9bf00816fda923a&canvas_pos=platform', '0', '405');

INSERT INTO `{{app_personal}}`(`uid`, `widget`, `shortcut`) VALUES ('1','1,2','1,2');

INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('1','应用门户','app/default/index','0','1','0','3','app');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('app','应用门户','app/default/index','提供企业App应用功能','11','0');