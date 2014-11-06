var http = require('http');



sendtoclient = function(client,a,m,o){
	if(connected_clients[client]) {
		connected_clients[client].emit(a, {"m":m,"o":o});
		return true;
	} else {
		return false;
	}
}

var app = http.createServer(function  (req, res) {
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
		res.writeHead(404);
		res.end("one or more clients not found!\n\n\n"+client+"\n"+app+"\n"+method+"\n"+options+"\n");
	}	
	///// http://127.0.0.1:1338/?c=123456&a=msg&m=module&o=[aaa,bbb,ccc]
});
app.listen(1338);
console.log("http server started on port ", 1338);


var app2 = http.createServer(function  (req, res) {
	res.writeHead(200);
	res.end("Hello, nothing to see here ;)");
});
var io = require('socket.io')(app2);
//var io = require('socket.io').listen(app);
//io.set("transports", ['websocket', 'xhr-polling', 'htmlfile']);
//io.set("polling duration", 10);

app2.listen(1337);
console.log("socket.io started on port ", 1337);

var connected_clients = {};
io.on('connection', function (socket) {
	var sid;
	socket.on('register', function (id) {
		sid = id;
		console.log("client", id, "registered");
		connected_clients[id] = socket;
	});
	socket.on('disconnect',function(){
		delete connected_clients[sid];
		console.log(sid, "disconncted");
	});
});
