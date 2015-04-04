// property => [Number of Drive, Block Size]
var Coding = function (property) {
	this.noOfDrive = property[0];
	this.blockSize = property[1];
};

Coding.prototype.encode = function(file, fileId){
	console.log("File:", file);
	console.log("Encoding in RAID-1, blockSize:", this.blockSize, ", FileSize:", file.size, ",noOfDrive:", this.noOfDrive);
	
	var slideSize = this.blockSize * this.noOfDrive;
	var blocks = Math.ceil(file.size / this.blockSize);
	for(var i = 0; i < blocks; i++){
		var slice = file.slice(i * this.blockSize, (i+1) * this.blockSize, file.type);
		//console.log(slice);
		for(var j = 0; j < this.noOfDrive; j++){
			// [Chunk, file id, chunk id, drive sequence, slice size, totalNumOfChunks]
			postMessage([slice, fileId, (i*this.noOfDrive)+j, j, slice.size, blocks * this.noOfDrive]);
		}
	}
};

Coding.prototype.decode = function(listOfChunks, row, size){
	var blob = null;
	for(var i=0; i<listOfChunks.length; i++){
		if(!!listOfChunks[i] && listOfChunks[i].size == size[i]){
			blob = listOfChunks[i];
			break;
		}
	}
	postMessage(["decode", row, blob]);
};
Coding.prototype.getMinAcceptChunk = function(){
	postMessage(["getMinAcceptChunk", 1]);
};
