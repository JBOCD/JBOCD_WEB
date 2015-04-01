// Javascript worker
self.addEventListener("message", function(e){
	//console.log(e.data);
	var data = e.data;
	if(data == "close"){
		console.log("Closing");
		self.close();
		return;
	}
	//console.log(data[0]);
	self.importScripts(data[0]);
	var codingMachine = new Coding(data[1]);
	switch(data[3][0]){
		case 'encode':
			codingMachine.encode(data[2], data[3][1]);
			break;
		case 'decode':
			codingMachine.decode(data[2]);
			break;
	}
});

