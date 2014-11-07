Array.prototype.remove = function(e) {
	for (var i = 0; i < this.length; i++) {
		if (e == this[i]) { return this.splice(i, 1); }
	}
};


var http = require('http');
var input_port = 1338
var client_port = 1337



sendtoclient = function(client,a,m,o){
	if(connected_clients[client]) {
		console.log(client);
		console.log(a);
		console.log(m);
		console.log(o);
		for(var i=0; i<connected_clients[client].length; i++) {
			connected_clients[client][i].emit(a, {"m":m,"o":o});
		}
		return true;
	} else {
		return false;
	}
}

var ccc = 0;
var app = http.createServer(function  (req, res) {
	if(req.url == "/stats") {
		res.writeHead(200);
		res.end(""+ccc);
		return;
	}
	var client,app,module,options;
	var query = require('url').parse(req.url,true).query;

	client = 	query.c;
	clients = 	query.clients ? JSON.parse(query.clients) : undefined;
	app = 		query.a;
	method = 	query.m;
	options = 	query.o;
	
	var success = true;
	if(client) {
		success = sendtoclient(client,app,method,options);
		console.log("send");
	} else if(clients) {
		for(var i = 0, l = client.length; i < l; i++) {
			success = success && sendtoclient(clients[i],app,method,options);
		}	
	}
	if(success) {
		res.writeHead(200);
		res.end("send!\n\n\n"+client+"\n"+app+"\n"+method+"\n"+options+"\n");
	} else {
		res.writeHead(200);
		res.end("one or more clients not found!\n\n\n"+client+"\n"+app+"\n"+method+"\n"+options+"\n");
	}	
	///// http://127.0.0.1:1338/?c=123456&a=msg&m=module&o=[aaa,bbb,ccc]
});
app.listen(input_port, 'localhost');
console.log("http server started on port ", input_port);


var app2 = http.createServer(function  (req, res) {
	res.writeHead(200);
	res.end("Hello, nothing to see here ;)");
});
var io = require('socket.io')(app2);
//var io = require('socket.io').listen(app);
//io.set("transports", ['websocket', 'xhr-polling', 'htmlfile']);
//io.set("polling duration", 10);

app2.listen(client_port, 'localhost');
console.log("socket.io started on port ", client_port);

var connected_clients = {};
io.on('connection', function (socket) {
	var sid;
	ccc++;
	socket.on('register', function (id) {
		sid = id;
		console.log("client", id, "registered");
		if(!connected_clients[id]) connected_clients[id] = new Array();
		connected_clients[id].push(socket);
	});
	socket.on('disconnect',function(){
		//delete connected_clients[sid];
		if( connected_clients[sid]) connected_clients[sid].remove(socket);
		if(!connected_clients[sid]) delete connected_clients[sid];
		console.log(sid, "disconncted");
		ccc--;
	});
});
