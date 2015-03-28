var Coding = function (property) {
	this.noOfDrive = property[1];
	this.blockSize = property[2];
};

Coding.prototype.getProperty = function(){
	console.log("D:", this.noOfDrive, ", blk:", this.blockSize);
};

Coding.prototype.encode = function(){
	console.log("Encoding in RAID-0, blockSize:", this.blockSize, ", FileSize:", file.size, ",noOfDrive:", this.noOfDrive);
	console.log("File:", file);
	var slideSize = this.blockSize * this.noOfDrive;
	var blocks = Math.ceil(file.size / this.blockSize);
	for(var i = 0; i < blocks; i++){
		var slice = file.slice(i * this.blockSize, (i+1) * this.blockSize, file.type);
		//console.log(slice);
		postMessage([slice, fileId, i, i % this.noOfDrive, slice.size]);
	}
};

Coding.prototype.decode = function(){
	console.log("Decoding in RAID-0, blockSize:", this.blockSize, ", FileSize:", file.size, ",noOfDrive:", this.noOfDrive);
	console.log("File:", file);
};