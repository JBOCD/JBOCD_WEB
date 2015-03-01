if(!window.JBOCD) window.JBOCD = {};
window.JBOCD.Network = new ((
	function (){
		var Network = function (){};
		var textEncoder = new TextEncoder();
		var textDecoder = new TextDecoder();
		// Network to Host function
		Network.prototype.toByte = function(arrayBuffer){
			return new Uint8Array(arrayBuffer.slice(0,1))[0];
		}
		Network.prototype.toShort = function(arrayBuffer){
			var val = new Uint8Array(arrayBuffer.slice(0,2));
			return val[0] << 8 | val[1];
		}
		Network.prototype.toInt = function(arrayBuffer){
			var val = new Uint8Array(arrayBuffer.slice(0,4));
			return val[0] << 24 | val[1] << 16 | val[2] << 8 | val[3];
		}
		Network.prototype.toLong = function(arrayBuffer){
			var val = new Uint8Array(arrayBuffer.slice(0,8));
			return val[0] << 56 | val[1] << 48 | val[2] << 40 | val[3] << 32 | val[4] << 24 | val[5] << 16 | val[6] << 8 | val[7];
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
		Network.prototype.charsToBytes = function(str){
			var strarr = textEncoder.encode("00"+str);
			strarr.set(this.shortToBytes(strarr.length-2), 0);
			return strarr;
		}
		return Network;
	}
)())();