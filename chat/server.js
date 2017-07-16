'use strict'

const http = require('http');
const cookie = require('cookie');
const url = require('url');
const connectSocketIO = require('socket.io');

const inputPort = 1338;
const chatPort = 1337;
const listenHost = process.argv[2] || '127.0.0.1';

const connected_clients = {};
let num_registrations = 0;
let num_connections = 0;

const sendToClient = (client, a, m, o) => {
	if(connected_clients[client]) {
		for(var i=0; i<connected_clients[client].length; i++) {
			connected_clients[client][i].emit(a, {m: m, o: o});
		}
		return true;
	} else {
		return false;
	}
}

const inputServer = http.createServer((req, res) => {
	if (req.url == '/stats') {
		res.writeHead(200);
		res.end(JSON.stringify({
			connections: num_connections,
			registrations: num_registrations,
			sessions: Object.keys(connected_clients).length
		}));
		return;
	}

	var client, app, options, method;
	var query = url.parse(req.url, true).query;

	client = 	query.c;
	app = 		query.a;
	method = 	query.m;
	options = 	query.o;

	if(client) {
		sendToClient(client,app,method,options);
	}
	res.writeHead(200);
	res.end('\n');
});

const chatServer = http.createServer((req, res) => {
	res.writeHead(200);
	res.end('\n');
});
const io = connectSocketIO(chatServer);

io.use((socket, next) => {
	const cookie = socket.request.headers.cookie;
	if(cookie) {
		socket.sid = parseCookie(cookie).PHPSESSID;
		if (socket.sid) next();
	}
	next(new Error('not authorized'));
});

io.on('connection', (socket) => {
	const sid = socket.sid;
	num_connections++;
	socket.on('register', () => {
		num_registrations++;
		if(!connected_clients[sid]) connected_clients[sid] = [];
		connected_clients[sid].push(socket);
	});

	socket.on('disconnect', () => {
		num_connections--;
		const connections = connected_clients[sid];
		if(sid && connections) {
			const i = connections.indexOf(socket);
			if(i !== -1) {
				connections.splice(i, 1);
				num_registrations--;
			}
			if (connections.length === 0) {
				delete connected_clients[sid];
			}
		}
	});
});

inputServer.listen(inputPort, listenHost);
console.log('http server started on', listenHost + ':' + inputPort);

chatServer.listen(chatPort, listenHost);
console.log('socket.io started on port', listenHost + ':' + chatPort);

module.exports = {
	inputServer: inputServer,
	chatServer: chatServer
}
