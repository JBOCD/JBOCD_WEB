// property => [Number of Drive, Block Size]
var Coding = function (property) {
	this.noOfDrive = property[0];
	this.blockSize = property[1];
};

Coding.prototype.encode = function(file, fileId){
	console.log("Encoding in RAID-1, blockSize:", this.blockSize, ", FileSize:", file.size, ",noOfDrive:", this.noOfDrive);
	console.log("File:", file);
	var slideSize = this.blockSize * this.noOfDrive;
	var blocks = Math.ceil(file.size / this.blockSize);
	for(var i = 0; i < blocks; i++){
		var slice = file.slice(i * this.blockSize, (i+1) * this.blockSize, file.type);
		//console.log(slice);
		for(var j = 0; j < this.noOfDrive; j++){
			// [Chunk, file id, chunk id, drive sequence, slice size]
			postMessage([slice, fileId, i, j, slice.size]);
		}
	}
};

Coding.prototype.decode = function(listOfChunks){
	console.log("Decoding in RAID-1, blockSize:", this.blockSize, ",noOfDrive:", this.noOfDrive);
	console.log("File:", file);
};