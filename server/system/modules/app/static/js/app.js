var App = {
	op: {
		getAppList: function(param, callback) {
			$.post(Ibos.app.url("app/default/getapp"), param, callback, "json");
			// @Debug:
			/*var data = {
			 office: [
			 {
			 appId: 1, 
			 appName: "有道词典", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/98ebb9d2b28acf259c820e12e187ae21.jpg", 
			 appUrl: "http://dict.youdao.com/app/baidu",
			 appHeight: 500
			 },
			 {
			 appId: 2,
			 appName: "发票真伪查询", 
			 appIcon: "http://apps2.bdimg.com/store/static/kvt/a66e44b16ed76f900af3b558c3532444.jpg", 
			 appUrl: "http://fapiao.youshang.com/app/baidu.html",
			 appHeight: 500
			 },
			 // {
			 // 	appId: 3,
			 // 	appName: "组织机构核查", 
			 // 	appIcon: "http://apps1.bdimg.com/store/static/kvt/fe7de04e7d8964c60b64dc0fe61aa826.jpg", 
			 // 	appUrl: "http://125.35.63.22/?bd_sig=b9d322e7849d9cb3131575fc9758e6c7&canvas_pos=platform",
			 // 	appHeight: 460
			 // },
			 {
			 appId: 4,
			 appName: "个人所得税计算", 
			 appIcon: "http://apps2.bdimg.com/store/static/kvt/71dc48b31d8ebc75a986aff004509c6e.jpg", 
			 appUrl: "http://jisuanqi.duapp.com/geshui",
			 appHeight: 600
			 },
			 {
			 appId: 5,
			 appName: "身份证号码查询", 
			 appIcon: "http://apps2.bdimg.com/store/static/kvt/55ccc29065fd7238f1b9aa302608597f.gif", 
			 appUrl: "http://baidu.uqude.com/baidu_mobile_war/pages/identitycard/index_uqude.html",
			 appHeight: 450
			 },
			 {
			 appId: 6,
			 appName: "违章查询", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/60209125b9706bb81a16ebc856ed3b23.png", 
			 appUrl: "http://mobile.auto.sohu.com/wzcxWeb/baidu.at"
			 },
			 { 
			 appId: 7, 
			 appName: "美图秀秀", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/9e80a4e76bdbf22ff6cfc8c9ae564dfe.gif", 
			 appUrl: "http://xiuxiu.web.meitu.com/baidu",
			 appWidth: 800,
			 appHeight: 500
			 },
			 // { 
			 // 	appId: 8, 
			 // 	appName: "批量加水印", 
			 // 	appIcon: "http://apps3.bdimg.com/store/static/kvt/b3b490ad016f5591f390464d1b3338ed.jpg", 
			 // 	appUrl: ""
			 // },
			 { 
			 appId: 9, 
			 appName: "宽带测速", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/5994144c39a53cb0fbd2e8f73c76f0d4.gif", 
			 appUrl: "http://app.yesky.com/cms/a/speed",
			 appHeight: 500
			 },
			 { 
			 appId: 10, 
			 appName: "图片批量处理", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/f622d8f95b8b3c8a5c614b2c5b65068f.jpg", 
			 appUrl: "http://picgod.duapp.com/app/baidu.php",
			 appWidth: 578,
			 appHeight: 469
			 }
			 ],
			 personal: [
			 { 
			 appId: 11,
			 appName: "365日历", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/340238f487321ab424dd471680e314a3.jpg", 
			 appUrl: "http://baidu365.duapp.com/wnl.html",
			 appWidth: 575,
			 appHeight: 439
			 },
			 // { 
			 // 	appId: 12,
			 // 	appName: "工作计划", 
			 // 	appIcon: "http://apps.bdimg.com/store/static/kvt/340238f487321ab424dd471680e314a3.jpg", 
			 // 	appUrl: "http://app.baidu.com/app/enter?appid=222724",
			 // },
			 { 
			 appId: 13,
			 appName: "自我评价", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/525f725d9b61a4026d9e6ac2c190bb8d.jpg", 
			 appUrl: "http://qiqiapp3.duapp.com/zwpingjia",
			 appHeight: 475
			 },
			 { 
			 appId: 14,
			 appName: "择吉良日", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/8f44628d9c67b79109f279d209d860c1.jpg", 
			 appUrl: "http://www.nnhs.com.cn/time/rili.html",
			 appWidth: 800,
			 appHeight: 570
			 },
			 {
			 appId: 15,
			 appName: "励志签名", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/cd58224b9c65657e41401b957f95347f.jpg", 
			 appUrl: "http://qianmingapp.duapp.com/?s_param=author%3D11",
			 appHeight: 540
			 },
			 {
			 appId: 16,
			 appName: "简历模板", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/1b5aa4b32ce42b5bc78d294e43f368a0.jpg", 
			 appUrl: "http://qqdaquan.duapp.com/pages/jianli/",
			 appWidth: 800,
			 appHeight: 490
			 },
			 {
			 appId: 17,
			 appName: "提前还贷计算器", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/550e24b0a81d36e810c90f6cb76f00c5.jpg", 
			 appUrl: "http://repayment.sinaapp.com/repayment/",
			 appHeight: 500
			 },
			 // {
			 // 	appId: 18,
			 // 	appName: "图解领带打法", 
			 // 	appIcon: "http://apps3.bdimg.com/store/static/kvt/5def40dfc1908d9686c8fbfee417daaa.jpg", 
			 // 	appUrl: "http://www.nnhs.com.cn/time/rili.html?bd_sig=4134161297b7d949e23d8ee21407090a&canvas_pos=platform",
			 // 	appHeight: 570
			 // },
			 {
			 appId: 19,
			 appName: "围巾的系法图解", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/a61162c360dcc53cd3091c847d17129c.jpg", 
			 appUrl: "http://qqgexingfenzu.duapp.com/weijing",
			 appHeight: 540
			 }
			 // {
			 // 	appId: 20,
			 // 	appName: "鞋带的系法图解", 
			 // 	appIcon: "http://apps3.bdimg.com/store/static/kvt/550e24b0a81d36e810c90f6cb76f00c5.jpg", 
			 // 	appUrl: "http://repayment.sinaapp.com/repayment/count.html?bd_sig=b942ed3de289a5b4156e526c1d1a8731&canvas_pos=platform",
			 // 	appHeight: 500
			 // }
			 ],
			 tool: [
			 {
			 appId: 21,
			 appName: "linux命令", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/4d404d618a7e36d988c28526c26cf689.jpg", 
			 appUrl: "http://linuxso.duapp.com/",
			 appHeight: 350
			 },
			 {
			 appId: 22,
			 appName: "dos命令大全", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/5f82d03a914aaf0602a87016743caf54.jpg", 
			 appUrl: "http://bcs.duapp.com/youxiok/dos/index.html",
			 appHeight: 384
			 },
			 {
			 appId: 23,
			 appName: "URL编码解码", 
			 appIcon: "http://apps2.bdimg.com/store/static/kvt/02ecdf4cdb0c17baabdc0bbd74c44e1d.png", 
			 appUrl: "http://ydtool.duapp.com/url_en-decode/",
			 appHeight: 410
			 },
			 {
			 appId: 24,
			 appName: "GUID生成工具", 
			 appIcon: "http://apps2.bdimg.com/store/static/kvt/3c4955c2e4e65521164f931fb85e5c1b.jpg", 
			 appUrl: "http://toolmao.duapp.com/guid",
			 appHeight: 337
			 },
			 {
			 appId: 25,
			 appName: "JSON数据解析", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/8bc5a8aaff1cc511b8a0e84ce62ca2bc.jpg", 
			 appUrl: "http://xssreport.sinaapp.com/baiduapp/json",
			 appHeight: 337
			 },
			 {
			 appId: 26,
			 appName: "base64在线解码", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/82afcc22ba0dfa8f2272a5a55b33d484.jpg", 
			 appUrl: "http://phpclubs.duapp.com/base64",
			 appHeight: 444
			 },
			 {
			 appId: 27,
			 appName: "UTF8 转换工具", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/58539eae495c61f61522c50866299351.png", 
			 appUrl: "http://mytools.duapp.com/tooss/utf8",
			 appHeight: 448
			 },
			 {
			 appId: 28,
			 appName: "JS代码压缩", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/801c651923262726ca97b714470d2bbf.png", 
			 appUrl: "http://jsmin.51240.com/",
			 appWidth: 800,
			 appHeight: 580
			 },
			 {
			 appId: 29,
			 appName: "HTML/UBB互转", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/8b3a43fce2168fcabe5c33a32e6f0059.png", 
			 appUrl: "http://mytools.duapp.com/tooss/ubb",
			 appHeight: 600
			 },
			 {
			 appId: 30,
			 appName: "HTML to JS", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/961c5c5ac61fc8546cf3703a4f18238e.jpg", 
			 appUrl: "http://www.vsyo.com/app/htmltojs.htm",
			 appHeight: 455
			 }
			 ],
			 express: [
			 { 
			 appId: 31, 
			 appName: "顺丰快递查询", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/08ec2f59089757006d40237604683e56.png", 
			 appUrl: "http://baidu.kuaidi100.com/all.html?com=shunfeng",
			 appHeight: 380
			 },
			 { 
			 appId: 32, 
			 appName: "中通快递查询", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/e260ad5f4706893e53de8fc2e2fa1ea5.jpg", 
			 appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhongtong",
			 appHeight: 380
			 },
			 { 
			 appId: 33, 
			 appName: "申通快递查询", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/5c85f4fdf8da45f2b3c0a3b4870ddbd3.png", 
			 appUrl: "http://baidu.kuaidi100.com/all2.html?com=shentong",
			 appHeight: 380
			 },
			 { 
			 appId: 34, 
			 appName: "圆通快递查询", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/f61e561f22d4e8e22b829e15efba11a0.jpg", 
			 appUrl: "http://baidu.kuaidi100.com/all.html?com=yuantong",
			 appHeight: 380
			 },
			 
			 { 
			 appId: 35, 
			 appName: "韵达快递查询", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/5815d5b08a2bc52bb197719a5f8ca976.jpg", 
			 appUrl: "http://baidu.kuaidi100.com/all.html?com=yunda",
			 appHeight: 380
			 },
			 { 
			 appId: 36, 
			 appName: "EMS快递查询", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/19670586e802adf4791aeb4a38088970.gif", 
			 appUrl: "http://baidu.kuaidi100.com/all1.html?com=ems",
			 appHeight: 380
			 },
			 { 
			 appId: 37, 
			 appName: "德邦物流查询", 
			 appIcon: "http://apps2.bdimg.com/store/static/kvt/e46dc81559edf56c7afd964ea08865d1.gif", 
			 appUrl: "http://baidu.kuaidi100.com/all.html?com=debangwuliu",
			 appHeight: 380
			 },
			 { 
			 appId: 38, 
			 appName: "天天快递查询", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/302a4c871d98db6a5ab14595a5bcf717.png", 
			 appUrl: "http://baidu.kuaidi100.com/all2.html?com=tiantian",
			 appHeight: 380
			 },
			 { 
			 appId: 39, 
			 appName: "百世汇通快递查询", 
			 appIcon: "http://apps.bdimg.com/store/static/kvt/83b1d98815b1f7e9d36f724e223e14ae.png", 
			 appUrl: "http://baidu.kuaidi100.com/all.html?com=huitongkuaidi",
			 appHeight: 380
			 },
			 { 
			 appId: 40, 
			 appName: "宅急送快递查询", 
			 appIcon: "http://apps1.bdimg.com/store/static/kvt/9e1ac9ebecacbfdf9527ef1d06fbf91c.jpg", 
			 appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhaijisong",
			 appHeight: 380
			 }
			 ],
			 entertainment: [
			 // 五子棋         http://app.baidu.com/app/enter?appid=178559
			 // 合金弹头       http://app.baidu.com/app/enter?appid=100998
			 // 拳皇           http://app.baidu.com/app/enter?appid=103874
			 // 植物大战僵尸   http://app.baidu.com/app/enter?appid=103488
			 // 连连看         http://app.baidu.com/app/enter?appid=100924
			 // 奔跑哥         http://app.baidu.com/app/enter?appid=136247
			 { 
			 appId: 41, 
			 appName: "PPTV直播", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/64b39f0f7e6caa2b498f4dfcd073f168.jpg", 
			 appUrl: "http://app.aplus.pptv.com/tgapp/baidu/live/main",
			 appHeight: 534,
			 appWidth: 800
			 },
			 // { 
			 // 	appId: 42, 
			 // 	appName: "中国象棋", 
			 // 	appIcon: "http://apps.bdimg.com/store/static/kvt/19280d435062ffb667c893b56c4f66dc.jpg", 
			 // 	appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhaijisong&bd_sig=24c7e554efbc5a51b78ac7f218584a9a&canvas_pos=platform",
			 // 	appHeight: 380
			 // },
			 // { 
			 // 	appId: 43, 
			 // 	appName: "奥维斗地主", 
			 // 	appIcon: "http://apps1.bdimg.com/store/static/kvt/9e1ac9ebecacbfdf9527ef1d06fbf91c.jpg", 
			 // 	appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhaijisong&bd_sig=24c7e554efbc5a51b78ac7f218584a9a&canvas_pos=platform",
			 // 	appHeight: 380
			 // },
			 // { 
			 // 	appId: 44, 
			 // 	appName: "美女打麻将", 
			 // 	appIcon: "http://apps1.bdimg.com/store/static/kvt/9e1ac9ebecacbfdf9527ef1d06fbf91c.jpg", 
			 // 	appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhaijisong&bd_sig=24c7e554efbc5a51b78ac7f218584a9a&canvas_pos=platform",
			 // 	appHeight: 380
			 // },
			 // { 
			 // 	appId: 45, 
			 // 	appName: "跳棋", 
			 // 	appIcon: "http://apps1.bdimg.com/store/static/kvt/9e1ac9ebecacbfdf9527ef1d06fbf91c.jpg", 
			 // 	appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhaijisong&bd_sig=24c7e554efbc5a51b78ac7f218584a9a&canvas_pos=platform",
			 // 	appHeight: 380
			 // },
			 // { 
			 // 	appId: 46, 
			 // 	appName: "五子棋", 
			 // 	appIcon: "http://apps1.bdimg.com/store/static/kvt/9e1ac9ebecacbfdf9527ef1d06fbf91c.jpg", 
			 // 	appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhaijisong&bd_sig=24c7e554efbc5a51b78ac7f218584a9a&canvas_pos=platform",
			 // 	appHeight: 380
			 // },
			 { 
			 appId: 48, 
			 appName: "植物大战僵尸", 
			 appIcon: "http://apps3.bdimg.com/store/static/kvt/f5d3e48a5aedf458eae6f66a44623fe9.jpg", 
			 appUrl: "http://apps.bdimg.com/tools/popcap/plantsvszombies/game.html",
			 appHeight: 405
			 }
			 ]
			 }
			 
			 data.all = data.office.concat(data.personal, data.express, data.tool, data.entertainment);
			 data.all.sort(function(a, b){
			 return a.appId > b.appId;
			 })
			 
			 callback && callback({
			 isSuccess: true,
			 pageCount: 1,
			 app: data[param["type"] || "all"]
			 })
			 
			 // $.post("", param, function(){
			 // 	var data = {
			 // 		isSuccess: true,
			 // 		pageCount: 1,
			 // 		app: [
			 // 			{ 
			 // 				appId: 1, 
			 // 				appName: "在线网速测试器", 
			 // 				appIcon: "http://apps2.bdimg.com/store/static/kvt/f621dc96da5e6fcea2b05683b1633d7d.jpg", 
			 // 				appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhongtong",
			 // 				appWidth:　800,
			 // 				appHeight: 490
			 // 			},
			 // 			{ 
			 // 				appId: 2, 
			 // 				appName: "有道词典网页版", 
			 // 				appIcon: "http://apps1.bdimg.com/store/static/kvt/98ebb9d2b28acf259c820e12e187ae21.jpg", 
			 // 				appUrl: "http://dict.youdao.com/app/baidu",
			 // 				appWidth: 540,
			 // 				appHeight: 488
			 // 			},
			 // 			{ 
			 // 				appId: 3, 
			 // 				appName: "金山快快打字测试", 
			 // 				appIcon: "http://apps2.bdimg.com/store/static/kvt/30eb75e82a0e2a75700f4be5fe975ae4.jpg", 
			 // 				appUrl: "http://www.51dzt.com/testgo.html",
			 // 				appWidth: 540,
			 // 				appHeight: 335
			 // 			},
			 // 			{ 
			 // 				appId: 4, 
			 // 				appName: "美图秀秀网页版", 
			 // 				appIcon: "http://apps.bdimg.com/store/static/kvt/9e80a4e76bdbf22ff6cfc8c9ae564dfe.gif", 
			 // 				appUrl: "http://xiuxiu.web.meitu.com/baidu"
			 // 			},
			 // 			{ 
			 // 				appId: 5, 
			 // 				appName: "中通快递查询", 
			 // 				appIcon: "http://apps1.bdimg.com/store/static/kvt/e260ad5f4706893e53de8fc2e2fa1ea5.jpg", 
			 // 				appUrl: "http://baidu.kuaidi100.com/all2.html?com=zhongtong"
			 // 			},
			 // 			{ 
			 // 				appId: 6, 
			 // 				appName: "条形码批量生成器", 
			 // 				appIcon: "http://apps3.bdimg.com/store/static/kvt/ae7ecf1cb0d3194185da840a8edcec79.jpg", 
			 // 				appUrl: "http://apps.99wed.com/baiduapp/barcode",
			 // 				appWidth: 540,
			 // 				appHeight: 526
			 // 			}
			 // 		]
			 // 	}
			 // 	callback && callback(data);
			 // }/*, "json")*/
		},
		addShortcut: function(param, callback) {
			// @Debug:
//			var sc = Ibos.local.get("appShortcuts") || [];
//			sc.push(param);
//			Ibos.local.set("appShortcuts", sc);
//			callback && callback({ isSuccess: true });

			$.post(Ibos.app.url("app/default/add", {'type': 'shortcut'}), param, function(res) {
				if (res.isSuccess) {
					callback && callback(res);
				}
			}, "json");
		},
		saveShortcut: function(param, callback) {
//			Ibos.local.set("appShortcuts", param.shortcuts || []);
//			callback && callback({ isSuccess: true });
			$.post(Ibos.app.url("app/default/edit"), param, function(res) {
				if (res.isSuccess) {
					callback && callback(res);
				}
			}, "json");
		},
		saveWidgets: function(param, callback) {
			// @Debug;
//			Ibos.local.set("appWidgets", param.widgets || []);
//			callback && callback({ isSuccess: true });

			$.post(Ibos.app.url("app/default/add", {'type': 'widget'}), param, function(res) {
				if (res.isSuccess) {
					callback && callback(res);
				}
			}, "json");

			// @Debug: 
		}
	},
	// 渲染窗口内的 app 备选列表
	renderAppList: function(tpl, data) {
		var content = "";
		if (tpl && data && data.length) {
			for (var i = 0; i < data.length; i++) {
				content += $.template(tpl, data[i]);
			}
		}
		$("#app_pool").html(content);
	},
	getAppList: function(param, callback) {
		var _this = this;
//		console.log(param);
		this.op.getAppList(param, function(res) {
			if (res.isSuccess) {
				_this.renderAppList("tpl_app_item", res.data);
				Ibos.app.s({"appListParam": param});
				// 刷新页码按钮状态
					_this.updatePageTurnBtn({
						page: param.page,
						pageCount: res.pages
					})
				callback && callback(res);
			} else {
				Ui.tip("获取 app 列表失败", "danger")
			}
		})
	},
	// 更新翻页按钮状态
	updatePageTurnBtn: function(param) {
//		console.log("abc");
//		console.log(param);
		var $prevBtn = $("#app_dialog [data-node-type='appPrevPageBtn']"),
				$nextBtn = $("#app_dialog [data-node-type='appNextPageBtn']");
		if (!param) {
			$prevBtn.prop("disabled", true);
			$nextBtn.prop("disabled", false);
		} else {
			$prevBtn.prop("disabled", param.page <= 1);
			$nextBtn.prop("disabled", param.page >= param.pageCount);
		}
	},
	showAppDialog: function() {
		Ui.closeDialog("d_app");
		Ui.ajaxDialog(Ibos.app.url("app/default/applist"), {
			id: "d_app",
			// title: "添加应用",
			title: false,
			padding: 0,
			width: 760,
			height: 420,
			lock: true,
			skin: "art-autoheight"
		})
	},
	// 添加一个桌面快捷方式
	toAddShortcut: function() {
		Ibos.app.s({"addAppType": "shortcut"});
		this.showAppDialog();
	},
	addShortcut: function(data) {
		var $elem = $.tmpl("tpl_app_item", data);
		$elem.append("<i class='o-shortcut-remove' data-action='removeShortcut' title='移除应用快捷方式'></i>");
		$("#app_shortcut_list li:last").before($elem)
	},
	setupShortcut: function() {
		$("#app_shortcut_list").addClass("editable").sortable({
			tolerance: "pointer",
			cursor: "move"
		})
				.find("li:last").hide();
	},
	saveShortcut: function(callback) {
//		var shortcutIds = $("#app_shortcut_list li").map(function() {
//			return $.attr(this, "data-appid");
//		}).get().join(",");

		var scDatas = $("#app_shortcut_list [data-node-type='appItem']").map(function() {
			var data = $(this).data();
			return data.appId;
		}).get()


		this.op.saveShortcut({shortcuts: scDatas}, /*{ ids: shortcutIds }*/ function(res) {
			if (res.isSuccess) {
				$("#app_shortcut_list").removeClass("editable").sortable("destroy")
						.find("li:last").show();
				callback && callback(res);
			}
		});
	},
	// 获取桌面上所有部件的 id
	getWidgetsId: function() {
		return $("#app_widget_panel .mbox").map(function() {
			return $.attr(this, "data-id");
		}).get().join(",");
	},
	saveWidgets: function() {
		var data = $("#app_widget_panel .mbox").map(function() {
			var data = $(this).data();
			return  data.appId;
		}).get();
//		this.op.saveWidgets({widgets: data});

		// var ids = this.getWidgetsId();
		// this.op.saveWidgets({ ids: ids });
	},
	// 打开添加 app 部件窗口
	toAddWidget: function() {
		Ibos.app.s({"addAppType": "widget"});
		this.showAppDialog();
	},
	// 添加 app 部件
	addWidget: function(data) {
		var a = $("#app_widget_panel").children().last().before($.template("tpl_app_widget", data));
		$("#app_widget_panel").removeClass("app-widget-empty");
	},
	// 移除 app 部件
	removeWidget: function($elem) {
		$elem.remove();
		if ($("#app_widget_panel .mbox").length === 0) {
			$("#app_widget_panel").addClass("app-widget-empty");
		}
//		this.saveWidgets();
	}
}
// showAppDialog();


$(function() {
	Ibos.evt.add({
		"toAddShortcut": function() {
			App.toAddShortcut();
		},
		"setupShortcut": function(param, elem) {
			App.setupShortcut();
			$(elem).addClass("active").attr("data-action", "saveShortcut");
		},
		"saveShortcut": function(param, elem) {
			App.saveShortcut(function(res) {
				if (res.isSuccess) {
					$(elem).removeClass("active").attr("data-action", "setupShortcut");
					Ui.tip("保存应用快捷方式成功");
				}
			});
		},
		"removeShortcut": function(param, elem) {
			$(elem).closest("li").remove();
		},
		"toAddWidget": function() {
			App.toAddWidget();	},

		"removeWidget": function(param, elem, callback) {
			$.post(Ibos.app.url("app/default/del"), param, function(res) {
				console.log(param);
				if (res.isSuccess) {
					App.removeWidget($(elem).closest(".mbox"));
					callback && callback(res);
				}
			}, "json");
		}
	});

	$(document).on("click", ".app-portal-sidebar .app-shortcut-list:not(.editable) [data-node-type='appItem']", function() {
		var data = $(this).data();

		Ui.closeDialog("d_app_ins");
		Ui.openFrame(data.appUrl, {
			id: "d_app_ins",
			title: data.appName,
			width: data.appWidth || 580,
			height: data.appHeight || 400,
			skin: "art-autoheight",
			padding: 0,
			lock: true,
			resize: true
		});
	});

	// 部件排序
	$("#app_widget_panel").sortable({
		items: ".mbox",
		tolerance: "pointer",
		handle: ".mbox-header",
		cursor: "move"
	}).on("sortupdate", function() {
		App.saveWidgets();
	});

	// 初始化部件和快捷方式
	var widgetData = Ibos.app.g("widgets");
	if (widgetData && widgetData.length) {
		$.each(widgetData, function(i, w) {
			App.addWidget(w);
		})
	}
	;

	var shortcutData = Ibos.app.g("shortcuts");
	if (shortcutData && shortcutData.length) {
		$.each(shortcutData, function(i, s) {
			App.addShortcut(s);
		})
	}
	;

	$("#app_sc_container").affixTo("#app_widget_panel")
});