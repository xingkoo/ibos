var jsPlumbDefaults = {
	Endpoint: "Blank",
	// Endpoint:[ "Dot", { radius:5 } ],
	// EndpointStyle:{ fillStyle:"transparent" },
	// EndpointHoverStyle:{ fillStyle:"#ffa500" },
	
	Connector: "Flowchart",

	ConnectionOverlays : [
		[ "Arrow", { 
			location: 1,
			id: "arrow",
            length: 14,
            foldback: 0.6
		} ]
	],

	ConnectionsDetachable: false,
	ReattachConnections: false,

	// LogEnabled: true, // 调试模式
	// 锚点自动调整位置
	Anchor: "Continuous"
}

// 连接器样式
jsPlumb.registerConnectionTypes({
    "complete": {
        paintStyle:{ strokeStyle:"#EE8C0C", lineWidth: 3, outlineWidth: 4,  outlineColor: "transparent"},
    	overlays : [
    		[ "Arrow", { 
    			location: 1,
    			id: "arrow",
                length: 14,
                foldback: 0.6
    		} ]
    	]
    },
    "uncomplete":{
        paintStyle:{ strokeStyle:"#B2C0D1", lineWidth: 3, outlineWidth: 4,  outlineColor: "transparent", dashstyle:"4 1"},
        overlays : [
    		[ "Arrow", { 
    			location: 1,
    			id: "arrow",
                length: 14,
                foldback: 0.6
    		} ]
    	]
    }    
});

jsPlumb.importDefaults(jsPlumbDefaults);

var processView = {

	instance: new wfDesigner($("#wf_designer_canvas"), jsPlumb, {
		operation: {
			isSource: false,
			isTarget: false,
			detach: false
		}
	}),

	parseData: function(data){

		var ret = {
				steps: [],
				connects: []
			};

		var pushConnect = function(sourceId, targetIds) {
			var tids,
				d;
			if(typeof sourceId === "undefined" || typeof targetIds !== "string" || targetIds === "") {
				return false;
			}
			tids = targetIds.split(",");
			for(var i = 0; i < tids.length; i++) {
				ret.connects.push(sourceId + "," + tids[i]);
			}			
		}

		if(data && data.length) {
			for(var i = 0; i < data.length; i++) {
				// 只接收需要的属性
				d = {
					id: data[i].id,
					index: data[i].processid,
					top: data[i].top,
					left: data[i].left,
					name: data[i].name,
					to: data[i].to,
					cls: "wf-step wf-step-view wf-step-" + data[i].type,
					type: data[i].type
				}
				// 特殊步骤处理, (开始结束)
				if(d.index === -1){
					d.cls = "wf-step wf-step-start";
				} else if(d.index === 0){
					d.cls = "wf-step wf-step-end";
				}
				ret.steps.push(d);
				if(d.to != null) {
					pushConnect(d.index, d.to);
				}
			}
		}
		return ret;
	},

	set: function(data){
		var that = this,
			_data,
			endProgressId;

		if(data && data.length){
			_data = this.parseData(data);
			if(_data.steps){
				this.instance.addSteps(_data.steps);
			}
			if(_data.connects) {
				for(var i = 0; i < _data.connects.length; i++) {
					endProgressId = _data.connects[i].split(",")[1];
					this.instance.addConnect(_data.connects[i], this.instance.getData(endProgressId).type != "inactive" ? "complete" : "uncomplete");
				}
				// @Todo: 因为连线类型的细节还有讨论中，先全部使用完成的样式
			}
		}

	}
}

