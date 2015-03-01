if(!window.JBOCD) window.JBOCD = {};
window.JBOCD.Socket = new ((
	function (){
		if(!Array.prototype.findIndex){
			Array.prototype.findIndex = function(callback){
				for(var i=0;i<this.length;i++){
					if(callback(this[i], i, this)) return i;
				}
				return -1;
			}
		}
		var Socket = function (){};
		var operation = new Array(256);
/*		var operation = [
			{
				request : {},
				response : {},
			}, ...
		];
*/
		var socket;
		Socket.prototype.init = function(uid, token){
			var suid = uid, stoken = token;
			socket = new WebSocket("ws://"+window.location.hostname+":3362", "JBOCD");
			socket.bunaryType = Blob;
			socket.onopen = function(){
				console.log("WebSocket: Start Connect");
//				send suid, token
			}
			socket.onmessage = function(evt){
				var fileReader = new FileReader();
				fileReader.onloadend = (function(){
					var blob = evt.data;
					return interpreter;
				})();
				fileReader.readAsArrayBuffer(evt.data.slice(0,2));
			}
		}
		Socket.prototype.close = function(){
			socket.close();
		}
		Socket.prototype.login = function(uid, token){
			var opID = operation.findIndex(isNull);
			if(opID >= 0){
				operation[opID] = {
					request : {
						command: 0x00
					}
				}
				socket.send(new Blob([
					JBOCD.Network.byteToBytes(0x00),
					JBOCD.Network.byteToBytes(opID),
					JBOCD.Network.intToBytes(uid),
					JBOCD.Network.charsToBytes(str.slice(0,32))
				]));
			}
		}
/*
		var textDecoder = new TextDecoder();
		// Network to Host function
		Network.prototype.toByte = function(arrayBuffer){
			return arrayBuffer[0];
		}
		Network.prototype.toShort = function(arrayBuffer){
			return arrayBuffer[0] << 8 | arrayBuffer[1];
		}
		Network.prototype.toInt = function(arrayBuffer){
			return arrayBuffer[0] << 24 | arrayBuffer[1] << 16 | arrayBuffer[2] << 8 | arrayBuffer[3];
		}
		Network.prototype.toLong = function(arrayBuffer){
			return arrayBuffer[0] << 56 | arrayBuffer[1] << 48 | arrayBuffer[2] << 40 | arrayBuffer[3] << 32 | arrayBuffer[4] << 24 | arrayBuffer[5] << 16 | arrayBuffer[6] << 8 | arrayBuffer[7];
		}
		Network.prototype.toString = function(arrayBuffer){
			return textDecoder.decode(arrayBuffer.subarray(2, this.toShort(arrayBuffer)));
		}
		Network.prototype.byteToBytes = function(val){
			return new Uint8Array([val]);
		}
		Network.prototype.shortToBytes = function(val){
			return new Uint8Array([val >> 8 & 0xFF, val]);
		}
		Network.prototype.intToBytes = function(val){
			return new Uint8Array([val >> 24 & 0xFF, val >> 16 & 0xFF, val >> 8 & 0xFF, val]);
		}
		Network.prototype.longToBytes = function(val){
			return new Uint8Array([val >> 56 & 0xFF, val >> 48 & 0xFF, val >> 40 & 0xFF, val >> 32 & 0xFF, val >> 24 & 0xFF, val >> 16 & 0xFF, val >> 8 & 0xFF, val]);
		}
		Network.prototype.charsToBlob = function(str){
			var strarr = textEncoder.encode("00"+str);
			strarr.set(this.shortToBytes(strarr.length-2), 0);
			return strarr;
		}
*/
		var isNull = function(e){return !e;};
		var interpreter = function(){
			var opID = JBOCD.Network.toByte(this.result.slice(1,2));
			operation[opID].response = {
				blob : blob
			}
			switch(JBOCD.Network.toByte(this.result)){
				case 0x00:
					// close socket; or resent?; or reload page?
					console.log("WebSocket: JBOCD: login fail.");
					delete operation[opID];
					break;
				case 0x01:
					console.log("WebSocket: JBOCD: login successful.");
					delete operation[opID];
					// get cloud drive; get logical drive;
					break;
				case 0x02:
					break;
			}
		};
		return Socket;
	}
)())();