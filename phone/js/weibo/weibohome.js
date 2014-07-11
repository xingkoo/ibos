/**
 * 微博主页
 */
(function(){
	console.log("开始读微博");
	Weibo.bindRefreshEvent("weibo");
	Weibo.bindLoadMoreEvent("weibo");
	Weibo.loadNewFeed();

	// Weibo.startLoadNew();
})();