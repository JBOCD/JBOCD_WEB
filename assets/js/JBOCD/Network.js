if(!window.JBOCD) window.JBOCD = {};
window.JBOCD.Network = (function (){
	var Network = function (){};
	var textEncoder = new TextEncoder();
	var textDecoder = new TextDecoder();
	// Network to Host function
	Network.prototype.toByte = function(arrayBuffer, start){
		start = !start? 0 : start;
		if(arrayBuffer.byteLength >= start+1){
			return new Uint8Array(arrayBuffer.slice(start,start+1))[0];
		}
		return 0;
	}
	Network.prototype.toShort = function(arrayBuffer, start){
		start = !start? 0 : start;
		if(arrayBuffer.byteLength >= start+2){
			var val = new Uint8Array(arrayBuffer.slice(start,start+2));
			return val[0] << 8 | val[1];
		}
		return 0;
	}
	Network.prototype.toInt = function(arrayBuffer, start){
		start = !start? 0 : start;
		if(arrayBuffer.byteLength >= start+4){
			var val = new Uint8Array(arrayBuffer.slice(start,start+4));
			return val[0] << 24 | val[1] << 16 | val[2] << 8 | val[3];
		}
		return 0;
	}
	Network.prototype.toLong = function(arrayBuffer, start){
		start = !start? 0 : start;
		if(arrayBuffer.byteLength >= start+8){
			var val = new Uint8Array(arrayBuffer.slice(start+0,start+8));
			return val[0] << 56 | val[1] << 48 | val[2] << 40 | val[3] << 32 | val[4] << 24 | val[5] << 16 | val[6] << 8 | val[7];
		}
		return 0;
	}
	Network.prototype.toString = function(arrayBuffer, start){
		start = !start? 0 : start;
		var size = this.toByte(arrayBuffer, start);
		if(arrayBuffer.byteLength >= start+size+1){
			return textDecoder.decode(arrayBuffer.slice(start+1, start+1+size));
		}
		return 0;
	}
	Network.prototype.byteToBytes = function(val){
		return new Uint8Array([val]);
	}
	Network.prototype.shortToBytes = function(val){
		return new Uint8Array([val >> 8, val]);
	}
	Network.prototype.intToBytes = function(val){
		return new Uint8Array([val >> 24, val >> 16, val >> 8, val]);
	}
	Network.prototype.longToBytes = function(val){
		return new Uint8Array([val >> 56, val >> 48, val >> 40, val >> 32, val >> 24, val >> 16, val >> 8, val]);
	}
	Network.prototype.charsToBytes = function(str){
		var strarr = textEncoder.encode("0"+str);
		strarr.set(this.byteToBytes(strarr.length-1), 0);
		return strarr;
	}
	return new Network();
})();