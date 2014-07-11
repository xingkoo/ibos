// var FlowModel = function(){
// 	this.cache = {};
// }
// FlowModel.prototype = {
// 	constructor: FlowModel,
// 	add: function(id, data){
// 		if(typeof id !== "undefined" && typeof data !== "undefined") {
// 			if(id in this.cache){
// 				this.update(id, data);
// 			} else {
// 				this.cache[id] = data
// 			}
// 			return this.cache[id];	
// 		}
// 	},
// 	update: function(id, data){
// 		if(typeof id !== "undefined" && typeof data !== "undefined" && id in this.cache) {
// 			delete data.id;
// 			$.extend(this.cache[id], data);
// 			return this.cache[id];	
// 		}
// 	},
// 	remove: function(id){
// 		if(id in this.cache) {
// 			delete this.cache[id];
// 		}
// 	},
// 	get: function(id) {
// 		return typeof id === "undefined" ? this.cache : (this.cache[id] || null);
// 	},
// 	getByParam: function(param){
// 		var ret = [],
// 			key,
// 			value,
// 			cacheKey;

// 		if(typeof param === "object"){
// 			for(key in param) {
// 				if(param.hasOwnProperty(key)){
// 					value = param[key];
// 					for(cacheKey in this.cache) {
// 						if( key in this.cache[cacheKey] && value === this.cache[cacheKey][key]){
// 							ret.push(this.cache[cacheKey]);
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return ret;
// 	},
// 	clear: function() {
// 		this.cache = {};
// 	}
// }



var wfDesigner = (function(){
	var Wp = {
		sourceOptions: { filter: ".ep, .ep i" },
		targetOptions: { dropOptions: { hoverClass: "wf-step-hover" } },
		addStep: function($steps, options){
			options = options || {};
			// 初始化拖拽				
			if(options.draggable !== false) {
				jsPlumb.draggable($steps, {
				  containment:"parent"
				});
			}
			// 初始化连接源
			if(options.isSource !== false) {
				jsPlumb.makeSource($steps, this.sourceOptions);	
			}


			// 初始化连接目标
			if(options.isTarget !== false) {
		    	jsPlumb.makeTarget($steps, this.targetOptions);
			}
		    
		    // jsPlumb.repaintEverything();
			return $steps;
		},
	}
	var defaults = {
		prefix:  "step_", // ID前缀
		offset:  80, //自动步骤间距
		autoNamePrefix: L.WF.ADD_STEP,
		stepTpl: "<div class='<%=cls%>' id='<%=prefix%><%=index%>' data-id='<%=id%>' style='top: <%=top%>px; left: <%=left%>px;'> <span class='ep'><i></i><%=index%></span> <%=name%></div>",
		cls: "wf-step",
		activeCls: "wf-step-active",
		operation: {
			draggable: true,
			isSource: true,
			isTarget: true,
			detach: true
		}
	}
	
	var W = function($container, plumb, options){
		var options = $.extend(true, {}, defaults, options),
			that = this,
			currentIndex = 0, // 当前使用序号，新建时无序号时，序号按currentIndex + 1,
			prevId = -1, // 用于储存上一步骤Id, 由于初始化时，第一个步骤为“开始”, 其Id 为 -1
			selectedId,
			stepCache = {}, // 用于储存步骤对应的数据
			stepElemCache = {},
			connectCache = {};

		var _validateParam = function(params){
				if(!params || typeof params.id === "undefined") {
					// $.error("参数错误，未接收到ID");
					return false;
				}
				if(that.hasId(params.id)){
					// $.error("ID重复");
					return false;
				}
				if(that.hasIndex(params.index)){
					// $.error("index重复");
					return false;
				}			

				return true;
			},

			_addNode = function(params){
				return $.tmpl(options.stepTpl, params).appendTo($container);
			},

			_getIdByIndex = function(index){
				if(index) {
					for(var id in stepCache){
						if(stepCache[id].index === index){
							return id;
						}
					}
				}
			},

			// 用于获取对应的jq对象
			// param: id 
			_getElem = function(id){ 
				return stepElemCache[id] || null;
			},


			// 获取jq对象的宽高，返回格式{width: n, height: n}
			_getOffset = function($node){
				return {
					width: $node.outerWidth(),
					height: $node.outerHeight()
				}
			},
			// 当新建步骤时，会自动计算其出现的位置，此函数用于计算出该位置
			// return  { left: n, top: n }
			_reverse = false,
			_getCalculatedPosition = function($step){
				var $prevStepElem,
					prevStepOffset,
					prevStepData,
					prevStepPosition,
					containerOffset,
					stepOffset,
					stepPosition = {
						left: options.offset,
						top: options.offset
					};

				$prevStepElem = _getElem(prevId);
				
				if($prevStepElem && $prevStepElem.length){
					// 上一步骤的数据
					prevStepData = that.getData(prevId);
					// 上一步骤的宽高值
					prevStepOffset = _getOffset($prevStepElem);
					// 上一步骤的位置
					prevStepPosition = {
						left: parseInt(prevStepData.left, 10),
						top: parseInt(prevStepData.top, 10)
					};
					// 窗口的宽高值
					containerOffset = _getOffset($container);
					// 当前(即新建)步骤的宽高值
					stepOffset = _getOffset($step)

					// 让当前步骤出现在上一步骤的右边 20px(offset) 处 (反转情况下则是左边20px处)
					stepPosition.left = prevStepPosition.left + (_reverse ? -(prevStepOffset.width + options.offset) : (prevStepOffset.width + options.offset));
					stepPosition.top = prevStepPosition.top;

					// 当新建步骤超出容器右边时，向下延伸, 反转水平方向
					if(stepPosition.left + stepOffset.width > containerOffset.width){
						_reverse = true;

						stepPosition.left = prevStepPosition.left;
						stepPosition.top = prevStepPosition.top + prevStepOffset.height + options.offset;
						// 当新建步骤超出容器高度时，停留在底部
						if(stepPosition.top + stepOffset.height > containerOffset.height) {
							stepPosition.top = containerOffset.height - stepOffset.height;
						}
					}
					// 当新建步骤超出容器左边时，向下延伸, 反转水平方向
					if(stepPosition.left < 0) {
						_reverse = false;

						stepPosition.left = prevStepPosition.left;
						stepPosition.top = prevStepPosition.top + prevStepOffset.height + options.offset;
						// 当新建步骤超出容器高度时，停留在底部
						if(stepPosition.top + stepOffset.height > containerOffset.height) {
							stepPosition.top = containerOffset.height - stepOffset.height;
						}
					}
				}

				return stepPosition
			},
				
			_getOriginalId = function(id){
				if(typeof id === "string") {
					if(id.indexOf(options.prefix) !== -1){
						return id.replace(options.prefix, "");
					}
				}
			},
			// 增加普通步骤，isNew用来标识此步骤是否手动新增，true为新增，false从数据库中还原
			_addStep = function(param, opt, isNew) { //
				var $step,
					calculatedPosition;

				// 当新增步骤时，预期param参数只包含id字段 -- { id: n }
				param = $.extend({
					prefix: options.prefix,
					top: options.offset,
					left: options.offset,
					cls: options.cls
				}, param);

				opt = $.extend(options.operation, opt)


				if(typeof param.index === "undefined"){
					param.index = that.getMaxIndex() + 1;
				}
				if(typeof param.name === "undefined") {
					param.name = options.autoNamePrefix + " " + param.index;
				}

				$step = _addNode(param);

				// 新增时自动计算生成位置，当步骤没有默认top,left时也需要自动计算位置
				if(isNew || (param.left == '0' && param.top == '0')) {
					calculatedPosition = _getCalculatedPosition($step);
					$step.css(calculatedPosition);
					$.extend(param, calculatedPosition);
				}

				prevId = param.index;

				stepCache[param.index] = param;
				stepElemCache[param.index] = $step;
				// 作为流程图的一部分初始化(使其可连接)
				Wp.addStep($step, opt);
				return $step;
			},

			_bindEvent = function(){
				// 连线事件
				plumb.bind("connection", function(con) { 
					var id = con.connection.id,
						sourceId = _getOriginalId(con.sourceId),
						targetId = _getOriginalId(con.targetId)

					if(id && sourceId && targetId) {
						connectCache[id] = sourceId + "," + targetId;
					}

				});

				if(options.operation.detach) {
					// 连接器移除时，同步移除缓存数据
					plumb.bind("connectionDetached", function(con){ 
						if(con && con.connection){
							delete connectCache[con.connection.id];
						}
					})
					// 点击连接器时，将连接器删除
					plumb.bind("click", function(con){
						plumb.detach(con);
					});
				}

				if(options.operation.draggable) {
					// 移动结束时，更新数据
					$container.on( "dragstop",  ".wf-step", function( event, ui ) { 
						var id = _getOriginalId(ui.helper.attr("id")),
							position = ui.position;
						that.updateData(id, position);
					} );
				}
			}

		this.getMaxIndex = function(){
			var max = 0;
			for(var i in stepCache) {
				max = Math.max(max, stepCache[i].index);
			}
			return max;
		};

		this.hasIndex = function(index){
			return typeof index !== "undefined" && stepCache.hasOwnProperty(index);
		};

		this.hasId = function(id) {
			if(typeof id !== "undefined"){			
				for(var i in stepCache){
					if(stepCache[i].id === id){
						return true;
					}	
				}
			}
			return false;
		};

		// 用于获取缓存的数据，
		this.getData = function(id){
			return stepCache[id] || null;
		};
			
		this.updateData = function(id, newData){
			var data = that.getData(id);
			if(newData && data){
				delete newData.id;
				delete newData.index;
				$.extend(data, newData);
			}
			return data;
		};

		this.addStep = function(param, opt, isNew) {
			if(_validateParam(param)) {
				return _addStep(param, opt, isNew)
			}
		};

		this.addSteps = function(params, opt, isNew) {
			var that = this,
				$result = $();

			if($.isArray(params)) {
				params.sort(function(a, b){
					return a.index > b.index;
				})
				$.each(params, function(index, param){
					$result = $result.add(that.addStep(param,	opt, isNew))
				})
			} else {
				$result = that.addStep(params, opt, isNew)
			}
			return $result;
		};

		this.updateStep = function(id, param){
			// if(param){
			// 	this.updateData(id, param);
			// };
			// console.log(_getElem(id))
		}

		this.addConnect = function(connect, type){
			var con;
			if(typeof connect === "string") {
				con = connect.split(",", 2);
				return plumb.connect({
					source: options.prefix + con[0],//stepCache[con[0]].index,
					target: options.prefix + con[1],//stepCache[con[1]].index,
					type: type
				})
			}
		},

		this.addConnects = function(connects, type) {
			var that = this,
				ret = [];
			if($.isArray(connects)) {
				$.each(connects, function(index, connect){
					ret.push(that.addConnect(connect, type));
				})
			} else {
				ret.push(that.addConnect(connects, type));
			}
			return ret;
		};

		this.clearConnect = function(){
			plumb.detachEveryConnection();
			connectCache = {};
		};

		this.removeStep = function(id){
			var $elem = _getElem(id);
			$elem && plumb.remove($elem);
			delete stepCache[id]
			delete stepElemCache[id];
			selectedId = void(0);
			if(prevId === id) {
				prevId = _getIdByIndex(this.getMaxIndex());
			}
		};

		this.getConnects = function(){
			return connectCache;
		};

		this.getSteps = function(){
			return stepCache;
		};

		this.unselect = function(){
			$container.find(options.activeCls).removeClass(options.activeCls);
			selectedId = void(0);
		}

		this.select = function(id){
			if(typeof id !== "undefined"){
				for(var i in stepCache) {
					if(stepCache[i].index === id) {
						stepElemCache[i].addClass(options.activeCls);
						selectedId = id;
					} else {
						stepElemCache[i].removeClass(options.activeCls);
					}
				}
			}
		};

		this.getSelect = function(){
			return selectedId;
		};

		this.render = function(params){
			params = params || {};
			// 还原步骤
			if(params.steps && params.steps.length) {
				this.addSteps(params.steps);
				// 还原连接器
				if(params.connects) {
					this.addConnects(params.connects)
				}
			}
		};

		this.clear = function(){
			// plumb.reset();
			this.clearConnect();
			plumb.deleteEveryEndpoint();
			$container.empty();
			stepCache = {};
			selectedId = void(0);
		};

		this.repaint = function(){
			var steps = [],
				connects = [];
			for(var i in stepCache) {
				steps.push(stepCache[i])
			}
			for(i in connectCache) {
				connects.push(connectCache[i])
			}
			this.clear();

			this.render({
				steps: steps,
				connects: connects
			});
			selectedId = void(0);
		}

		_bindEvent();
	}

	return W;
})()



