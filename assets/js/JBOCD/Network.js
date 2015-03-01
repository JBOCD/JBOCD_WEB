if(!JBOCD) JBOCD = {};
JBOCD.Network = new ((
	function (){
		var Network = function (){};
		var textEncoder = new TextEncoder();
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
		return Network;
	}
)())();