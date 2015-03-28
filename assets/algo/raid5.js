// property => [Number of Drive, Block Size]
var Coding = function (property) {
	//console.log("New RAID-5 Instance:", property);
	this.noOfDrive = property[0];
	this.blockSize = property[1];
	this.workList = [];
};

Coding.prototype.encode = function(file, fileId){
	//console.log("Encoding in RAID-5, blockSize:", this.blockSize, ", FileSize:", file.size, ",noOfDrive:", this.noOfDrive);
	//console.log("File:", file);
	var slideSize = this.blockSize * this.noOfDrive;
	var blocks = Math.ceil(file.size / this.blockSize);
	var dataDrive = this.noOfDrive - 1;
	var row = Math.ceil(file.size / (this.blockSize * dataDrive));
	console.log("ROW:", row);
	for(var i = 0; i < row; i ++){
		console.log("Slice", i);
		/*
		this.raid5RowAction(file.slice( i * this.blockSize * dataDrive, (i+1) * this.blockSize * dataDrive, file.type ), fileId, i * dataDrive + i);
		*/
		this.workList.push(this.raid5RowAction(file.slice( i * this.blockSize * dataDrive, (i+1) * this.blockSize * dataDrive, file.type ), fileId, i * dataDrive + i));
		
	}

	for(var i = 0; i < 5; i++){
		var ff = this.workList.pop();
		if(ff != undefined) ff.run();
	}
};

Coding.prototype.decode = function(listOfChunks){
	console.log("Decoding in RAID-5, blockSize:", this.blockSize, ",noOfDrive:", this.noOfDrive);
	console.log("File:", file);
};

Coding.prototype.raid5RowAction = function(file, fileId, startChunkId){
	//console.log("Performing row action in RAID-5, blockSize:", this.blockSize, ",noOfDrive:", this.noOfDrive);
	//console.log("Row: startOfChunk", startChunkId, ",parts:", this.noOfDrive);
	//console.log("File:", file);

	var that = this;
	var fr = new FileReader();
	fr.fileType = file.type;
	fr.fileId = fileId;
	fr.blockSize = this.blockSize;
	fr.noOfDrive = this.noOfDrive;
	fr.startChunkId = startChunkId;
	fr.onloadend = function(){
		var slice = fr.result;
		var chunk = [];
		var drivePointer = 0;
		var is8 = false;

		if(slice.byteLength == (fr.blockSize * (fr.noOfDrive - 1))){
			for(var i = 0; i < fr.noOfDrive - 1; i++){
				var tmp = slice.slice(i * fr.blockSize, (i+1) * fr.blockSize);
				chunk.push(new Uint32Array(tmp));
			}
		}else{
			for(var i = 0; i < Math.ceil(slice.byteLength / fr.blockSize); i++){
				if(slice.byteLength > (i+1) * fr.blockSize){
					var tmp = slice.slice(i * fr.blockSize, (i+1) * fr.blockSize);
					chunk.push(new Uint8Array(tmp));
				}else{
					var tmp = slice.slice(i * fr.blockSize, i * fr.blockSize + (slice.byteLength % fr.blockSize));
					chunk.push(new Uint8Array(tmp));
				}
			}
			is8 = true;
		}

		for(var i = 0; i < chunk.length; i++){
			that.output(fileId, startChunkId + drivePointer, drivePointer++, new Blob([chunk[i]], {type: fr.fileType}));
		}

		var parity;
		if(is8){
			parity = new Uint8Array(chunk[0].length);
		}else{
			parity = new Uint32Array(chunk[0].length);
		}
		for(var i = 0; i < chunk[0].length; i++){
			for(var j = 0; j < fr.noOfDrive - 1; j++){
				if(chunk[j] != undefined) if(chunk[j][i] != undefined) parity[i] ^= chunk[j][i];
			}
		}
		that.output(fileId, startChunkId + drivePointer, drivePointer, new Blob([parity], {type: "JBOCD/parity"}));

		var ff = that.workList.pop();
		if(ff != undefined) ff.run();
	}
	fr.run = function(){
		fr.readAsArrayBuffer(file);
	}
	return fr;
};

Coding.prototype.output = function(fileId, chunkId, driveSequence, file){
	//console.log("Output: ", [file, fileId, chunkId, driveSequence, file.size]);
	postMessage([file, fileId, chunkId, driveSequence, file.size]);
};