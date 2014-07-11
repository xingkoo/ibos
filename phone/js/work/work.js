// var MainList = function(list, options){
// 	this.list = list;
// 	this.options = options || {};

// 	this.currentCatid = this.options.catid || 0;
// 	this.currentPage = this.options.page || 1;
// 	this.options.url = this.options.url || app.appUrl;
// }
// MainList.prototype.load = function(param, callback){
// 	var _this = this;
// 	param = param || {}
// 	if(param.catid == null) {
// 		param.catid = this.currentCatid;
// 	}
// 	if(param.page == null){
// 		if(param.catid === this.currentCatid) {
// 			param.page = this.currentPage
// 		} else {
// 			param.page = this.options.page || 1;
// 		}
// 	}

// 	$.ui.showMask();

// 	$.jsonP({
// 		url:       _this.options.url + "&callback=?&" + $.param(param),
// 		success:   function(res){
// 			_this.currentCatid = param.catid;
// 			_this.currentPage = param.page;
// 			_this.show(res);
// 			callback && callback(res);
// 			$.ui.hideMask();
// 		},
// 		error:     core.error
// 	})
// }

// MainList.prototype.show = function(res) {
// 	var _this = this;
// 	// 如果是第一页之后的内容，则添加至列表底部
// 	if(this.currentPage > 1) {
// 		this.list.add(res.datas);
// 	// 否则重绘列表
// 	} else {
// 		this.list.set(res.datas);
// 	}
// 	// 页数大于当前页码时，显示加载更多
// 	if(this.$loadMore) {
// 		this.$loadMore.remove();
// 		this.$loadMore = null;
// 	}
// 	if(res.pages.pageCount > this.currentPage) {
// 		this.$loadMore = $('<li class="list-more"><a href="javascript:;">加载更多</a></li>');
// 		this.$loadMore.on("click", function(){
// 			_this.load({ page: _this.currentPage + 1 });
// 		});
// 		this.list.$list.append(this.$loadMore);
// 	}
// }
