var Coding = function (property) {
	this.noOfDrive = property[1];
	this.blockSize = property[2];
};

Coding.prototype.getProperty = function(){
	console.log("D:", this.noOfDrive, ", blk:", this.blockSize);
};

Coding.prototype.encode = function(){

};

Coding.prototype.decode = function(){

};