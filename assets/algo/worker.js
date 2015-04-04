// Javascript worker
self.addEventListener("message", (function(){
	var codingMachine;
	return function(e){
		//console.log(e.data);
		var data = e.data;
		if(data == "close"){
			console.log("Closing");
			self.close();
			return;
		}
		if(!codingMachine){
			//console.log(data[0]);
			self.importScripts(data[0]);
			codingMachine = new Coding(data[1]);
		}
		switch(data[3][0]){
			case 'encode':
				codingMachine.encode(data[2], data[3][1]);
				break;
			case 'decode':
				codingMachine.decode(data[2], data[3][1], data[3][2]);
				break;
			case 'getMinAcceptChunk':
				codingMachine.getMinAcceptChunk();
				break;
		}
	};
})());

