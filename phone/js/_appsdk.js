/**
* 手机API转接

* @author Aeolus
* @copyright IBOS
*/
var appSdk = (function(){
	var location= (function (){
		/**
		* 启动定位功能	
		*
		*
		*	回调示例 
			function getback(lat,log){
				appSdk.location.close();
				var addr = appSdk.location.getAddress(lat,log);				
			}
		*/
		function open(getback){
			//uexLog.sendLog(uexLocation);
			uexLocation.onChange = getback;
			uexLocation.openLocation();		//启动定位功能
			
			uexWidgetOne.cbError = function(opCode,errorCode,errorInfo){
				alert("errorCode:" + errorCode + "\nerrorInfo:" + errorInfo);
		　　}
		}
		
		/**
		* 根据经纬度获取具体地址：inLongitude为经度，inLatitude为纬度	
		*
		*
		*	回调示例 
			function getback(opCode,dataType,data){
				if(dataType==0){
					document.getElementById('adre').value = data;
				}
			}
		*/
		function getAddress( inLatitude , inLongitude , getback){
		　　uexLocation.cbGetAddress = getback;
			uexLocation.getAddress(inLatitude,inLongitude);
		}
		
		/**
		* 关闭定位功能
		*/
		function close(){
			uexLocation.closeLocation()
		}
		
		function getAddr(){
			appSdk.location.open(function(lat,log){
				$("#gpsInput").val(lat+","+log);
				appSdk.location.getAddress(lat,log,
					function(opCode,dataType,data){
						if(dataType==0){
							$("#addressInput").val(data);
							$("#myAddress").html(data);
						}
					}
				)
			})		
		}
		
		return {
			open:		open,
			getAddress:	getAddress,
			close:		close,
			getAddr:	getAddr
		}
	})()
	
	return {
			location:	location
	}	
})()