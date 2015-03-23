// Javascript worker
self.addEventListener("message", function(e){
	//console.log(e.data);
	var data = e.data;
	//console.log(data[0]);
	self.importScripts(data[0]);
	var codingMachine = new Coding(data);
	codingMachine.getProperty();
});