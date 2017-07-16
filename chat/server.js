'use strict'

const http = require('http');
const {parse: parseCookie} = require('cookie');
const url = require('url');
const connectSocketIO = require('socket.io');

const inputPort = 1338;
const chatPort = 1337;
const listenHost = process.argv[2] || '127.0.0.1';

const connectedClients = {};
let numRegistrations = 0;
let numConnections = 0;

const sendToClient = (client, channel, method, payload) => {
	if (connectedClients[client]) {
		for (let connection of connectedClients[client]) {
			connection.emit(channel, {m: method, o: payload});
		}
	}
}

const inputServer = http.createServer((req, res) => {
	if (req.url == '/stats') {
		res.writeHead(200);
		res.end(JSON.stringify({
			connections: numConnections,
			registrations: numRegistrations,
			sessions: Object.keys(connectedClients).length
		}));
		return;
	}

	const query = url.parse(req.url, true).query;
	const client = query.c;
	const app = query.a;
	const method = query.m;
	const options = query.o;

	if (client) {
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
	if (cookie) {
		socket.sid = parseCookie(cookie).PHPSESSID;
		if (socket.sid) next();
	}
	next(new Error('not authorized'));
});

io.on('connection', (socket) => {
	const userId = socket.sid;
	numConnections++;
	socket.on('register', () => {
		numRegistrations++;
		if (!connectedClients[userId]) connectedClients[userId] = [];
		connectedClients[userId].push(socket);
	});

	socket.on('disconnect', () => {
		numConnections--;
		const connections = connectedClients[userId];
		if (userId && connections) {
			if (connections.includes(socket)) {
				connections.splice(connections.indexOf(socket), 1);
				numRegistrations--;
			}
			if (connections.length === 0) {
				delete connectedClients[userId];
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
