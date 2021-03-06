if(!window.JBOCD) window.JBOCD = {};
window.JBOCD.Socket = (function (){
	if(!Array.prototype.findIndex){
		Array.prototype.findIndex = function(callback){
			for(var i=0;i<this.length;i++){
				if(callback(this[i], i, this)) return i;
			}
			return -1;
		}
	}
	var socket;
	var delFileCB = function(e) { console.log(e) };
	var Socket = function (){};
	var fileReader = function (cb, blob, len){
		var fr = new FileReader();
		if(!len) len = blob.size;
		fr.onloadend = cb;
		fr.blob = blob;
		fr.readAsArrayBuffer(blob.slice(0,len));
	}
	var sendTimer = 0;
	var sendQueue = [];
	var send = function(blob, priority){
		if(blob){
			if(priority === 0){
				socket.send(blob);
			}else{
				sendQueue.push(blob);
			}
		}
		if(sendQueue.length > 0 && socket.bufferedAmount == 0){
			socket.send(sendQueue.shift());
		}
		if(sendQueue.length > 0){
			clearTimeout(sendTimer);
			sendTimer = setTimeout(send, 100); // this time can be approximate by thoughput ( (remain byte prev - remain byte now) / time)
		}
	}
	var operation;
	// index 255 is reserved for delete file
/*	var operation = [
		{
			request : {},
			response : {},
		}, ...
	];
*/
	Socket.prototype.init = function(openedCallback){
		if(!socket){
			if(!openedCallback || openedCallback.constructor !== Function){
				console.log("JBOCD.Socket.init(): Missing callback or callback is not a function.");
				return ;
			}
			var cb = openedCallback;
			operation = operation ? operation : new Array(256);
			operation[255] = true;
			socket = new WebSocket("wss://"+window.location.hostname+":9443", "JBOCD");
			socket.bunaryType = Blob;
			socket.onopen = function(){
				console.log("WebSocket: Start Connect");
				//send suid, token
				cb();
			}
			socket.onmessage = function(evt){
				var fileReader = new FileReader();
				fileReader.onload = interpreter;
				fileReader.blob = evt.data;
				fileReader.sliceBlob = evt.data.slice(0,2);
				fileReader.readAsArrayBuffer(fileReader.sliceBlob);
			}
			socket.onerror = socket.onend = socket.onclose = this.close;
		}else{
			console.log("WebSocket: Already Started.");
		}
	}
	Socket.prototype.setDelFileCallback = function(delFileCallback){
		if(!delFileCallback || delFileCallback.constructor !== Function){
			console.log("JBOCD.Socket.init(): Missing delFileCallback or delFileCallback is not a function.");
			return false;
		}
		delFileCB = delFileCallback;
		return true;
	}
	Socket.prototype.close = function(){
		if(socket){
			console.log("WebSocket: End Connect");
			socket.close();
			socket = null;
			for(var i=0; i<256; i++) delete operation[i];
		}
	}
	Socket.prototype.login = function(uid, token, callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x00,
					opID: opID,
					cb: callback
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x00),
				JBOCD.Network.byteToBytes(opID),
				JBOCD.Network.intToBytes(uid),
				JBOCD.Network.charsToBytes(token.slice(0,128)).subarray(1, 129)
			]), 0);
		}
		return opID;
	}
	Socket.prototype.getCloudDrive = function(callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x02,
					cb: callback,
					opID: opID
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x02),
				JBOCD.Network.byteToBytes(opID)
			]), 0);
		}
		return opID;
	}
	Socket.prototype.getLogicalDrive = function(callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x03,
					cb: callback,
					opID: opID
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x03),
				JBOCD.Network.byteToBytes(opID)
			]), 0);
		}
		return opID;
	}
	Socket.prototype.list = function(logicalDriveID, parentID, callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x04,
					opID: opID,
					cb: callback,
					ldID: logicalDriveID,
					parentID: parentID
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x04),
				JBOCD.Network.byteToBytes(opID),
				JBOCD.Network.intToBytes(logicalDriveID),
				JBOCD.Network.longToBytes(parentID)
			]), 0);
		}
		return opID;
	}
	Socket.prototype.createFile = function(logicalDriveID, parentID, fileSize, name, callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x20,
					opID: opID,
					cb: callback,
					ldID: logicalDriveID,
					pID: parentID,
					size: fileSize,
					name: name
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x20),
				JBOCD.Network.byteToBytes(opID),
				JBOCD.Network.intToBytes(logicalDriveID),
				JBOCD.Network.longToBytes(parentID),
				JBOCD.Network.longToBytes(fileSize),
				JBOCD.Network.charsToBytes(name)
			]), 0);
		}
		return opID;
	}
	Socket.prototype.putChunk = function(logicalDriveID, cloudDriveID, fileID, seqNum, name, blob, callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x21,
					opID: opID,
					cb: callback,
					ldID: logicalDriveID,
					cdID: cloudDriveID,
					fID: fileID,
					seqNum: seqNum,
					size: blob.size,
					name: name,
					blob: blob
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x21),
				JBOCD.Network.byteToBytes(opID),
				JBOCD.Network.intToBytes(logicalDriveID),
				JBOCD.Network.intToBytes(cloudDriveID),
				JBOCD.Network.longToBytes(fileID),
				JBOCD.Network.intToBytes(seqNum),
				JBOCD.Network.charsToBytes(""), // name is do nothing
				JBOCD.Network.intToBytes(blob.size),
				blob
			]), 1);
		}
		return opID;
	}
	Socket.prototype.getFile = function(logicalDriveID, fileID, callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x22,
					opID: opID,
					cb: callback,
					ldID: logicalDriveID,
					fID: fileID
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x22),
				JBOCD.Network.byteToBytes(opID),
				JBOCD.Network.intToBytes(logicalDriveID),
				JBOCD.Network.longToBytes(fileID)
			]), 0);
		}
		return opID;
	}
	Socket.prototype.delFile = function(logicalDriveID, fileID){
		send(new Blob([
			JBOCD.Network.byteToBytes(0x28),
			JBOCD.Network.byteToBytes(255),
			JBOCD.Network.intToBytes(logicalDriveID),
			JBOCD.Network.longToBytes(fileID)
		]), 0);
		return 255;
	}
	Socket.prototype.delFileChunk = function(logicalDriveID, fileID, callback){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x22,
					opID: opID,
					cb: callback,
					ldID: logicalDriveID,
					fID: fileID
				}
			}
			send(new Blob([
				JBOCD.Network.byteToBytes(0x29),
				JBOCD.Network.byteToBytes(opID),
				JBOCD.Network.intToBytes(logicalDriveID),
				JBOCD.Network.longToBytes(fileID)
			]), 0);
		}
		return opID;
	}
	var isNull = function(e){return !e;};
	var interpreter = function(){
console.log("Slice Blob Size: "+this.sliceBlob.size, this.sliceBlob);
console.log("Blob Size: "+this.blob.size, this.blob);
console.log("ArrayBuffer Size: "+this.result.byteLength, this.result);

		var command = JBOCD.Network.toByte(this.result, 0);
		var opID = JBOCD.Network.toByte(this.result, 1);
		if(!operation[opID]) return ; // operationID has been release
		if(!!operation[opID].response && !!operation[opID].response.blob){
			operation[opID].response.blob.push(this.blob);
		}else{
			operation[opID].response = {
				blob : [this.blob]
			}
		}
		switch(command){
			case 0x00:
			case 0x01:
				command === 0x00 ? console.log("WebSocket: JBOCD: login fail.") : console.log("WebSocket: JBOCD: login successful.");
			case 0x29:
				!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
				delete operation[opID];
				break;
			case 0x02:
				fileReader(processCloudDrive, this.blob);
				break;
			case 0x03:
				fileReader(processLogicalDrive, this.blob);
				break;
			case 0x04:
				fileReader(processFileList, this.blob);
				break;
			case 0x20:
				fileReader(processCreateFile, this.blob);
				break;
			case 0x21:
				fileReader(processPutChunk, this.blob);
				break;
			case 0x22:
				fileReader(processGetChunkInfo, this.blob);
				break;
			case 0x23:
				fileReader(processGetChunk, this.blob, 15);
				break;
			case 0x28:
				fileReader(processDelFile, this.blob);
				break;
		}
	}
	var processCloudDrive = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		var shift = 2;
		var i;
		if(!res.numOfCD){
			res.numOfCD = JBOCD.Network.toShort(this.result, shift);
			res.cdList = new Array(res.numOfCD);
			shift = 4;
		}
		if((i=res.cdList.findIndex(isNull)) >= 0){
			for(;i<res.numOfCD;i++, shift+=4){
				res.cdList[i] = JBOCD.Network.toInt(this.result, shift);
			}
		}
		if(i<0 || i>=res.numOfCD){
			!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
			delete operation[opID];
		}
	}
	var processLogicalDrive = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		var shift = 2;
		var i;
		if(!res.numOfLD){
			res.numOfLD = JBOCD.Network.toShort(this.result, shift);
			res.ldList = new Array(res.numOfLD);
			shift = 4;
		}
		if((i=res.ldList.findIndex(isNull)) >= 0){
			for(;i<res.numOfLD;i++){
				res.ldList[i] = {
					ldID : JBOCD.Network.toInt(this.result, shift),
					algoID : JBOCD.Network.toInt(this.result, shift+4),
					size : JBOCD.Network.toLong(this.result, shift+8),
					name : JBOCD.Network.toString(this.result, shift+16)
				}
				shift+=19+res.ldList[i].name.length;
				res.ldList[i].numOfCD = JBOCD.Network.toShort(this.result, shift-2);
				res.ldList[i].cdList = [];
				for(var j=0;j<res.ldList[i].numOfCD; j++, shift+=12){
					res.ldList[i].cdList.push({
						cdID : JBOCD.Network.toInt(this.result, shift),
						size : JBOCD.Network.toLong(this.result, shift+4)
					});
				}
			}
		}
		if(i<0 || i>=res.numOfLD){
			!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
			delete operation[opID];
		}
	}
	var processFileList = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		var shift = 2;
		var i;
		if(!res.numOfFile){
			res.numOfFile = JBOCD.Network.toShort(this.result, shift);
			res.fileList = new Array(res.numOfFile);
			shift = 4;
		}
		if((i=res.fileList.findIndex(isNull)) >= 0){
			for(;i<res.numOfFile;i++){
				res.fileList[i] = {
					fID : JBOCD.Network.toLong(this.result, shift),
					size : JBOCD.Network.toLong(this.result, shift+8),
					name : JBOCD.Network.toString(this.result, shift+16)
				}
				shift+=17+res.fileList[i].name.length;
			}
		}
		if(i<0 || i>=res.numOfFile){
			!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
			delete operation[opID];
		}
	}
	var processCreateFile = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		operation[opID].response.fID = JBOCD.Network.toLong(this.result, 2);
		!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
		delete operation[opID];
	}
	var processPutChunk = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		res.seqNum = JBOCD.Network.toInt(this.result, 2);
		res.status = JBOCD.Network.toByte(this.result, 6);
		res.size = JBOCD.Network.toInt(this.result, 7);
		!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
		delete operation[opID];
	}
	var processGetChunkInfo = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		res.numOfChunk = JBOCD.Network.toInt(this.result, 2);
		res.size = JBOCD.Network.toLong(this.result, 6);
		res.chunkList = new Array(res.numOfChunk);
		if(res.numOfChunk === 0){
			!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
			if(res.chunkList.findIndex(isNull) < 0){
				delete operation[opID];
			}
		}
	}
	var processGetChunk = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		var seqNum = JBOCD.Network.toInt(this.result, 2);
		if(!!res.chunkList[seqNum]){
			var thisSize = JBOCD.Network.toInt(this.result, 11);
			var seqInfo = res.chunkList[seqNum];
			var blobInfo = {
				start: JBOCD.Network.toInt(this.result, 7),
				length: thisSize,
				blob: this.blob.slice(15)
			};
			var i;
			seqInfo.getSize += thisSize;
			seqInfo.isEnd = seqInfo.isEnd || (this.blob.size == 15) || (seqInfo.getSize == seqInfo.size);
			seqInfo.isError = seqInfo.isError || ( seqInfo.isEnd && (seqInfo.getSize != seqInfo.size) );
			for(i=0; i < seqInfo.blobList.length && blobInfo.start < seqInfo.blobList[i].start; i++);
			seqInfo.blobList.splice(i,0,blobInfo);
		}else{
			var thisSize = JBOCD.Network.toInt(this.result, 11);
			res.chunkList[seqNum] = {
				seqNum: seqNum,
				size: JBOCD.Network.toInt(this.result, 7),
				blobList: [
					{
						start: 0,
						status: JBOCD.Network.toByte(this.result, 6),
						length: thisSize,
						blob: this.blob.slice(15)
					}
				],
				getSize: thisSize,
				isEnd: (this.blob.size - 15 == thisSize) || (this.blob.size == 15),
				isError: this.blob.size == 15
			}

		}
		if(res.chunkList[seqNum].isEnd){
			if(!res.seqQueue){
				res.seqQueue = [seqNum];
			}else{
				res.seqQueue.push(seqNum);
			}
			!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
			if(res.chunkList.findIndex(isNull) < 0){
				delete operation[opID];
			}
		}
	}
	var processDelFile = function(){
		var res = {};
		res.ldID = JBOCD.Network.toInt(this.result, 2);
		res.pID = JBOCD.Network.toLong(this.result, 6);
		res.fID = JBOCD.Network.toLong(this.result, 14);
		res.name = JBOCD.Network.toString(this.result, 22);
		delFileCB(res);
	}
	return new Socket();
})();
