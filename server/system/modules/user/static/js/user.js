/**
 * center.js
 * 个人中心
 * IBOS
 * @module		Global
 * @submodule	Center
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-18
 */


// Common

// Echarts

var contactForce = (function(node, options) {
	var myChart;

	if (typeof require === "undefined") {
		return false;
	} else {
		require.config({
			packages: [
				{
					name: 'echarts',
					location: Ibos.app.getStaticUrl('/js/lib/echarts/src'),
					main: 'echarts'
				},
				{
					name: 'zrender',
					location: Ibos.app.getStaticUrl('/js/lib/zrender/src'),
					main: 'zrender'
				}
			]
		});
	}
	var defaults = {
		tooltip: {
			trigger: 'item',
			formatter: '{a} : {b}'
		},
		legend: {
			orient: 'vertical',
			x: 'right',
			y: 'bottom',
			data: ['直属领导', '部门同事']
		},
		series: [
			{
				type: 'force',
				categories: [
					{
						name: '本人',
						itemStyle: {
							normal: {
								color: '#82939E'
							}
						}
					},
					{
						name: '直属领导',
						itemStyle: {
							normal: {
								color: '#3497DB'
							}
						}
					},
					{
						name: '部门同事',
						itemStyle: {
							normal: {
								color: '#91CE31'
							}
						}
					}
				],
				itemStyle: {
					normal: {
						label: {
							show: true,
							textStyle: {
								color: '#FFFFFF'
							}
						},
						nodeStyle: {
							brushType: 'both',
							strokeColor: 'rgba(130, 147, 158, 0.4)',
							lineWidth: 10
						},
						linkStyle: {
							strokeColor: '#B2C0D1'
									// opacity: '.1'
						}
					},
					emphasis: {
						label: {
							show: false,
							// textStyle: null      // 默认使用全局文本样式，详见TEXTSTYLE
						},
						nodeStyle: {
							r: 30,
							strokeColor: 'rgba(0, 0, 0, .1)'
						}
					}
				},
				minRadius: 25,
				maxRadius: 30,
				density: 0.05,
				attractiveness: 1.2
			}
		]
	};

	return function(node, options) {
		var opt = $.extend(true, {}, defaults, options);
		require(
				['echarts', 'echarts/chart/force'],
				function(ec) {
					if (myChart && myChart.dispose) {
						myChart.dispose();
					}
					myChart = ec.init(node);
					myChart.setOption(opt)
				}
		);
		return myChart;
	};
})();



var bind = function(urls) {
	$('[data-act="bind"]').on('click', function() {
		var that = this;
		var type = $(this).data('type');
		var dialog = $.artDialog({
			id: 'bind_box',
			title: U.lang("USER.BIND_OPERATION"),
			width: '500px',
			cancel: true,
			ok: function() {
				if ($.trim($('#inputVerify').val()) === '') {
					$('#inputVerify').blink().focus();
					return false;
				}
				$.get(urls.checkUrl, {data: encodeURI($('#inputVerify').val()), op: type}, function(res) {
					if (res.isSuccess) {
						Ui.tip(U.lang('OPERATION_SUCCESS'));
						window.location.reload();
					} else {
						Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
						return false;
					}
				}, 'json');
			}
		});
		// 加载对话框内容
		$.ajax({
			url: urls.url,
			data: {op: type},
			success: function(data) {
				dialog.content(data);
			},
			cache: false
		});
	});
};

/**
 * 动态进度条，使进度条有从0开始读取的效果
 * @param {Jquery} $elem   容器节点
 * @param {Number} value   初始值
 * @param {Object} options 配置项
 */
var Progress = function($elem, value, options) {
	this.$elem = $elem;
	this.value = this._reviseValue(value);
	this.options = $.extend({}, Progress.defaults, options);
	this.style = "";
	this._init();
}
Progress.defaults = {
	roll: true,
	speed: 20,
	active: false
};
Progress.prototype = {
	constractor: Progress,
	_init: function() {
		this.$elem.addClass("progress");
		this.$progress = this.$elem.find(".progress-bar");
		if (this.$progress.length === 0) {
			this.$progress = $("<div class='progress-bar'></div>").appendTo(this.$elem);
		}
		this.setStyle(this.options.style);
		this.setActive(this.options.active);
		if (!isNaN(this.value)) {
			this._setValue();
		}
	},
	/**
	 * 修正值的大小，值必须在0到100之间
	 * @param  {[type]} value [description]
	 * @return {[type]}       [description]
	 */
	_reviseValue: function(value) {
		value = parseInt(value, 10);
		// NaN
		value = value < 0 ? 0 : value > 100 ? 100 : value;
		return value;
	},
	setStyle: function(style) {
		var styles = ["danger", "info", "warning", "success"],
				styleStr = "",
				pre = "progress-bar-";

		if (this.style !== style) {
			this.style = style;
			for (var i = styles.length; i--; ) {
				styleStr += pre + styles[i] + " ";
			}
			this.$progress.removeClass(styleStr);

			if ($.inArray(style, styles) !== -1) {
				this.$progress.addClass(pre + style);
			}
		}
	},
	setActive: function(toStriped) {
		this.$elem.toggleClass("progress-striped", toStriped);
		this.$elem.toggleClass("active", toStriped);
	},
	_setValue: function() {
		if (!isNaN(this.value)) {
			// 动态进度条
			if (this.options.roll) {
				var that = this,
						interval = this.options.speed,
						current = 0,
						transTemp,
						timer;
				// 由于css3的transition会与setInterval计算冲突，transitionEnd回调不兼容，所以先去掉该属性
				transTemp = this.$progress.css("transition");
				this.$progress.css("transition", "none");

				that.$elem.trigger("rollstart");

				timer = setInterval(function() {
					that.$progress.css("width", current + "%");
					that.$elem.trigger("rolling", {
						value: current
					});
					if (current >= that.value) {
						clearInterval(timer);
						that.$elem.trigger("rollend");
						that.$progress.css("transition", transTemp);
					}
					current++;
				}, interval);
			} else {
				this.$progress.css("width", this.value + "%");
			}
		}

	},
	setValue: function(value) {
		this.value = this._reviseValue(value);
		this._setValue();
	}
};
