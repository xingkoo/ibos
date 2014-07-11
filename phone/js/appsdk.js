/**
* 手机API转接

* @author Aeolus
* @copyright IBOS
*/
var appSdk = {
	ready : function(func){
		document.addEventListener("deviceready", func, false);
	},
	pause : function(func){
		document.addEventListener("pause", func, false);
	},
	resume : function(func){
		document.addEventListener("resume", func, false);
	},
	onBack : function(func){
		document.addEventListener('backbutton', func, false);
	},
	removeOnBack: function(func){
		document.removeEventListener('backbutton', func, false);	
	},
	onLine :function(func){
		window.addEventListener("online", func);
	},
	offLine :function(func){
        window.addEventListener("offline", func);
	},
	alert : function(message,callback,title){
		callback =""
		title="ddf"
		navigator.notification.alert(message,callback,title);
	},
	confirm : function (message,callback,title,buttons) {
		navigator.notification.confirm(message,	function (r) {}, title,	buttons);
	},
	prompt : function (message,callback,title,buttons,value) {
		navigator.notification.prompt(
			message,  				// message
			callback,               // callback to invoke
			title,           		// title
			buttons,             	// buttonLabels
			value                 	// defaultText
		);
	},
	connectionState : function ()
	{
        var networkState = navigator.network.connection.type;

        var states = {};
			states[Connection.UNKNOWN]  = 'Unknown connection';
			states[Connection.ETHERNET] = 'Ethernet connection';
			states[Connection.WIFI] = 'WiFi connection';
			states[Connection.CELL_2G]  = 'Cell 2G connection';
			states[Connection.CELL_3G]  = 'Cell 3G connection';
			states[Connection.CELL_4G]  = 'Cell 4G connection';
			states[Connection.NONE]     = 'No network connection';
        return networkState;
    },
	doVibrate : function (time) {
		time = 1000;
		navigator.notification.vibrate(time);
	},
	doBeep : function (number) {
		number = 1;
		navigator.notification.beep(number);
	}
}
appSdk.camera = {
	// “Capture Photo”按钮点击事件触发函数
	getImage : function (callback) {
		navigator.camera.getPicture(callback, this.onError, { 
			quality: 50, 
			destinationType: navigator.camera.DestinationType.FILE_URI, 
			sourceType: navigator.camera.PictureSourceType.PHOTOLIBRARY 
		});
	},
	//“From Photo Library”/“From Photo Album”按钮点击事件触发函数
	getPhoto : function (callback) {
		// 从设定的来源处获取图像文件URI
		navigator.camera.getPicture(callback, this.onError, { 
			quality: 50, 
			destinationType: navigator.camera.DestinationType.FILE_URI, 
			sourceType : navigator.camera.PictureSourceType.CAMERA
		});
	},
	cleanUp : function (callback){
		navigator.camera.cleanup( callback, onError );
	},
	// 当有错误发生时触发此函数
	onError : function (error) {
		console.log('code: '    + error.code    + '\n' + 'message: ' + error.message + '\n');
	}
}
appSdk.gps = {
	getLocation : function(onSuccess,onError,param){
		navigator.geolocation.getCurrentPosition(onSuccess, onError, {enableHighAccuracy: true,frequency: 3000 });
	},
	getAddress : function(lat,lng,callback){
		$.jsonP({
			url: 		"http://api.map.baidu.com/geocoder/v2/?ak=C0d3e4e14997877a90874ddf73572261&callback=?&coordtype=wgs84ll&location="+lat+","+lng+"&output=json&pois=0",
			/**	百度参数返回
				+ result: 
					+ addressComponent: 
						city: "保定市"
						district: "安新县"
						province: "河北省"
						street: ""
						street_number: ""
					business: ""
					cityCode: 307
					formatted_address: "河北省保定市安新县"
					+ location: 
						lat: 39.000000106057
						lng: 116.00000003025
				status: 0
			*/
			success: 	callback,
			error: 		this.onError
		});
	},
	/**	获取位置信息成功时调用的回调函数
	*	该方法接受一个“Position”对象，包含当前GPS坐标信息
	* 	postion 
	*/
	onSuccess : function(position) {
		console.log(position);
		lat = position.coords.latitude;
		lng = position.coords.longitude;
		alt = position.coords.altitude;
		// alert('Latitude: '          + position.coords.latitude          + '\n' +
			// 'Longitude: '         + position.coords.longitude         + '\n' +
			// 'Altitude: '          + position.coords.altitude          + '\n' +
			// 'Accuracy: '          + position.coords.accuracy          + '\n' +
			// 'Altitude Accuracy: ' + position.coords.altitudeAccuracy  + '\n' +
			// 'Heading: '           + position.coords.heading           + '\n' +
			// 'Speed: '             + position.coords.speed             + '\n' +
			// 'Timestamp: '         + new Date(position.timestamp)      + '\n');
	},
	// onError回调函数接收一个PositionError对象
	onError : function (error) {
		console.log('GPS ERROR\ncode: '    + error.code    + '\n' + 'message: ' + error.message + '\n');
	}
}
appSdk.accel = {
	watchAccelId : null,
	status: 0,
	watchAccel : function (callback) {
		// Update acceleration every 1 sec
		var opt = {};
			opt.frequency = 50;
		this.watchAccelId = navigator.accelerometer.watchAcceleration(callback, onError, opt);	
		this.status = 1;
	},
	/** 获取当前加速度
	* 	a.x
	* 	a.y
	* 	a.z
	*/
	getAccel : function (callback) {
		// Make call
		var opt = {};
		navigator.accelerometer.getCurrentAcceleration(callback, onError, opt);
	},	
	stopAccel : function () {
		this.status = 0;
		if (this.watchAccelId) {
			navigator.accelerometer.clearWatch(this.watchAccelId);
			this.watchAccelId = null;
		}
	},
	// onError callback
	onError : function (error) {
		console.log('code: '    + error.code    + '\n' + 'message: ' + error.message + '\n');
	}
}
appSdk.media = {
	/**
	Media对象提供录制和回放设备上的音频文件的能力。
	var media = new Media(src, mediaSuccess, [mediaError], [mediaStatus]);
	
	备注：Media的当前实现并没有遵守W3C媒体捕获的相关规范，目前只是为了提供方便。未来的实现将遵守最新的W3C规范并可能不再支持当前的APIs。
	
	参数：
		src：							一个包含音频内容的URI。（DOMString类型）
		mediaSuccess：				（可选项）当一个Media对象完成当前的播放、录制或停止操作时触发的回调函数。（函数类型）
		mediaError：					（可选项）当出现错误时调用的回调函数。（函数类型）
		mediaStatus：					（可选项）当状态发生变化的时候调用的回调函数。（函数类型）
	方法：
		media.getCurrentPosition：	返回一个音频文件的当前位置。
		media.getDuration：			返回一个音频文件的总时长。
		media.play：					开始或恢复播放音频文件。
		media.pause：					暂停播放音频文件。
		media.release：				释放底层操作系统的音频资源。
		media.seekTo：				在音频文件中移动到相应的位置。
		media.startRecord：			开始录制音频文件。
		media.stopRecord：			停止录制音频文件。
		media.stop：					停止播放音频文件。
	*/
}
appSdk.capture ={
	image : function(callback,number){
		navigator.device.capture.captureImage( callback, onError,{limit:number});		
	},
	/**
	* mediaFile.name;
	* mediaFile.size;
	* mediaFile.fullPath
	* mediaFile.lastModifiedDate;
	* mediaFile.type;
	* <audio id="audio_player" src="" />
	*/
	audio : function(callback){
		navigator.device.capture.captureAudio( callback, onError,{ limit: 1 });
	},
	/**
	* 录制视频文件
	<video id="video_player" src="" onclick="playMedia();" >
		Browser does not support the video tag.
	</video>
	*/
	video : function(callback){
		navigator.device.capture.captureVideo( callback, onError ,{ limit: 1 });
	},
	// onError callback
	onError : function (e) {
		console.log("录制失败 " + e);
	}
}
appSdk.compass = {
	get : function(callback){
		navigator.compass.getCurrentHeading( callback, onError, opt);
	},
	stop : function(watchCompassId) {
		navigator.compass.clearWatch(watchCompassId);
		watchCompassId = null;
	},
	// onError callback
	onError : function (e) {
		console.log("录制失败 " + e);
	}
}
appSdk.scaner ={
	/** 
	*  callback Result: result.text 
	*					result.format 
	*					result.cancelled
	*/
	scan : function( callback, onError){
		cordova.plugins.barcodeScanner.scan( callback, onError );
	},
	// onError callback
	onError : function (e) {
		console.log("扫描失败 " + e);
	}	
}
appSdk.imagePicker = {
	getPictures : function(callback,options){
		window.imagePicker.getPictures(
		    callback, function (error) { console.log('Error: ' + error); }, options );
	}
}
appSdk.myCamera = {
	getPicture : function(callback,filename,options){
		navigator.customCamera.getPicture(filename, callback, function failure(error) { console.log('Error: ' + error);}, options);
	}
}
appSdk.browser = {
	show : function(url,options){
		if($.os.ios){
			options = $.extend({ showLocationBar: true ,showNavigationBar:true,showAddress:true }, options)
			window.plugins.ChildBrowser.showWebPage(url,options);
		}else{
			window.open(url, "_blank");
		}
	},
	close : function(){window.plugins.ChildBrowser.close();},
	open : function(url){
		window.plugins.ChildBrowser.openExternal(url);
	}
}
