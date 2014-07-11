/**
 * 应用列表
 * @version $Id$
 */
//App.getAppList({ type: "office", page: 1 });
App.getAppList({catid: "0", page: 1});

$('[data-toggle="tab"]').on("show", function() {
	var catid = $.attr(this, "data-id");
	App.getAppList({catid: catid, page: 1});
//	var type = $.attr(this, "href").substr(1);
//	App.getAppList({ type: type, page: 1 });
});

$("#app_dialog").bindEvents({
	// 上一页
	"click [data-node-type='appPrevPageBtn']": function() {
		var $elem = $(this),
				param = Ibos.app.g("appListParam");
		
		param = $.extend({}, param, {
			page: param.page - 1
		});
		App.getAppList(param)
	},
	// 下一页
	"click [data-node-type='appNextPageBtn']": function() {
		var $elem = $(this),
				param = Ibos.app.g("appListParam");
		
		param = $.extend({}, param, {
			page: param.page + 1
		});
		App.getAppList(param)
	},
	// 添加 app
	"click [data-node-type='appItem']": function(evt) {
		var addType = Ibos.app.g("addAppType");
		var data = $(this).data();
		data = {
			height: data.appHeight,
			icon: data.appIcon,
			appid: data.appId,
			name: data.appName,
			url: data.appUrl,
			width: data.appWidth
		};
		if (addType === "shortcut") {
			App.op.addShortcut(data /* { appId: data.appId } */, function(res) {
				if (res.isSuccess) {
					App.addShortcut(data);
				}
			})
		} else if (addType === "widget") {
			App.op.saveWidgets(data /* { appId: data.appId } */, function(res) {
				if (res.isSuccess) {
					App.addWidget(data);
					Ui.closeDialog("d_app")
				}
			})
//			App.addWidget(data);
//			App.saveWidgets(data);
//			Ui.closeDialog("d_app")
		}
	}
});

$("#app_search").search(function(val) {
	var param = Ibos.app.g("appListParam");
	param = {
		type: param.type,
		page: 1,
		keyword: val
	};
	App.getAppList(param);
})