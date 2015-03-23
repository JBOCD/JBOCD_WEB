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
	var delFileCB;
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
	var operation = new Array(256);
/*	var operation = [
		{
			request : {},
			response : {},
		}, ...
	];
*/
	Socket.prototype.init = function(delFileCallback){
		if(!socket){
			if(!delFileCallback || delFileCallback.constructor !== Function){
				console.log("JBOCD.Socket.init(): Missing delFileCallback or delFileCallback is not a function.");
				return ;
			}
			operation[255] = { request: { cb: delFileCallback } };
			socket = new WebSocket("wss://"+window.location.hostname+":9443", "JBOCD");
			socket.bunaryType = Blob;
			socket.onopen = function(){
				console.log("WebSocket: Start Connect");
				//send suid, token
			}
			socket.onmessage = function(evt){
				var fileReader = new FileReader();
				fileReader.onloadend = interpreter;
				fileReader.blob = evt.data;
				fileReader.readAsArrayBuffer(evt.data.slice(0,2));
			}
			socket.onend = function(){
				console.log("WebSocket: End Connect");
				socket = null;
			}
		}else{
			console.log("WebSocket: Already Started.");
		}
	}
	Socket.prototype.close = function(){
		socket.close();
		socket = null;
	}
	Socket.prototype.login = function(uid, token){
		var opID = operation.findIndex(isNull);
		if(opID >= 0){
			operation[opID] = {
				request : {
					command: 0x00,
					opID: opID
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
	Socket.prototype.list = function(callback, logicalDriveID, parentID){
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
	Socket.prototype.createFile = function(callback, logicalDriveID, parentID, fileSize, name){
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
	Socket.prototype.putChunk = function(callback, logicalDriveID, cloudDriveID, fileID, seqNum, name, blob){
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
				blob
			]), 1);
		}
		return opID;
	}
	Socket.prototype.getFile = function(callback, logicalDriveID, fileID){
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
	Socket.prototype.delFile = function(callback, logicalDriveID, fileID){
		send(new Blob([
			JBOCD.Network.byteToBytes(0x28),
			JBOCD.Network.byteToBytes(255),
			JBOCD.Network.intToBytes(logicalDriveID),
			JBOCD.Network.longToBytes(fileID)
		]), 0);
		return 255;
	}
	var isNull = function(e){return !e;};
	var interpreter = function(){
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
				// close socket; or resent?; or reload page?
				console.log("WebSocket: JBOCD: login fail.");
				!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
				delete operation[opID];
				break;
			case 0x01:
				console.log("WebSocket: JBOCD: login successful.");
				!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
				delete operation[opID];
				// get cloud drive; get logical drive;
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
				fileReader(processPutChunkInfo, this.blob);
				break;
			case 0x22:
				fileReader(processGetChunkInfo, this.blob);
				break;
			case 0x23:
				fileReader(processGetChunk, this.blob);
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
						size : JBOCD.Network.toInt(this.result, shift+4)
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
			for(;i<res.numOfLD;i++){
				res.fileList[i] = {
					fID : JBOCD.Network.toLong(this.result, shift),
					size : JBOCD.Network.toLong(this.result, shift+8),
					name : JBOCD.Network.toString(this.result, shift+16)
				}
				shift+=17+res.list[i].name.length;
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
		res.seqNum = JBOCD.Network.toInt(this.result, 8);
		!!operation[opID].request.cb && operation[opID].request.cb.constructor == Function && operation[opID].request.cb(operation[opID]);
		delete operation[opID];
	}
	var processGetChunkInfo = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		res.numOfChunk = JBOCD.Network.toInt(this.result, 2);
		res.chunkList = new Array(res.numOfChunk);
	}
	var processGetChunk = function(){
		var opID = JBOCD.Network.toByte(this.result, 1);
		var res = operation[opID].response;
		var seqNum = JBOCD.Network.toInt(this.result, 2);
		if(!!res.chunkList[seqNum]){
			var thisSize = JBOCD.Network.toInt(this.result, 10);
			var seqInfo = res.chunkList[seqNum];
			var blobInfo = {
				start: JBOCD.Network.toInt(this.result, 6),
				length: thisSize,
				blob: this.blob.slice(14)
			};
			var i;
			seqInfo.getSize += thisSize;
			seqInfo.isError |= this.blob.size - 14 == thisSize;
			for(i=0; blobInfo.start < seqInfo.blobList[i].start || i < seqInfo.blobList.length; i++);
			seqInfo.blobList.splice(i,0,blobInfo);
		}else{
			var thisSize = JBOCD.Network.toInt(this.result, 10);
			res.chunkList[seqNum] = {
				seqNum: seqNum,
				size: JBOCD.Network.toInt(this.result, 6),
				blobList: [
					{
						start: 0,
						length: thisSize,
						blob: this.blob.slice(14)
					}
				],
				getSize: thisSize,
				isError: this.blob.size - 14 == thisSize
			}

		}
		if(res.chunkList[seqNum].getSize >= res.chunkList[seqNum].size){
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
