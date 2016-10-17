Array.prototype.remove = function(e) {
	i = this.indexOf(e);
	if(i !== -1) {
		return this.splice(i, 1);
	}
};

var http = require('http');
var input_port = 1338;
var client_port = 1337;
var connected_clients = {};
var listenHost = process.argv[2] || '127.0.0.1';
var num_registrations = 0;
var num_connections = 0;

sendtoclient = function(client,a,m,o){
	if(connected_clients[client]) {
		for(var i=0; i<connected_clients[client].length; i++) {
			connected_clients[client][i].emit(a, {"m":m,"o":o});
		}
		return true;
	} else {
		return false;
	}
}

var app = http.createServer(function  (req, res) {
	if(req.url == "/stats") {
		var num_sessions = 0;
		res.writeHead(200);
		res.end('{"connections":'+num_connections+',"registrations":'+num_registrations+',"sessions":'+Object.keys(connected_clients).length+'}');
		return;
	}
	var client,app,options,method;
	var query = require('url').parse(req.url,true).query;

	client = 	query.c;
	app = 		query.a;
	method = 	query.m;
	options = 	query.o;
	
	if(client) {
		sendtoclient(client,app,method,options);
	}
	res.writeHead(200);
	res.end("\n");
});
app.listen(input_port, listenHost);
console.log("http server started on", listenHost + ':' + input_port);

var app2 = http.createServer(function  (req, res) {
	res.writeHead(200);
	res.end("\n");
});
var io = require('socket.io')(app2);

app2.listen(client_port, listenHost);
console.log("socket.io started on port", listenHost + ':' + client_port);

io.on('connection', function (socket) {
	var sid;
	num_connections++;
	socket.on('register', function (id) {
		num_registrations++;
		sid = id;
		if(!connected_clients[id]) connected_clients[id] = [];
		connected_clients[id].push(socket);
	});
	socket.on('disconnect',function(){
		num_connections--;
		if(sid && connected_clients[sid]) {
			num_registrations--;
			connected_clients[sid].remove(socket);
			if (connected_clients[sid].length === 0) {
				delete connected_clients[sid];
			}
		}
	});
});
