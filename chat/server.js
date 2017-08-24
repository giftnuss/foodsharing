'use strict'

const http = require('http');
const {parse: parseCookie} = require('cookie');
const {readFileSync} = require('fs');
const url = require('url');
const connectSocketIO = require('socket.io');
const redis = require('redis');

const inputPort = 1338;
const chatPort = 1337;
const listenHost = process.argv[2] || '127.0.0.1';

const redisClient = redis.createClient({
	host: process.env.REDIS_HOST || '127.0.0.1',
	port: process.env.REDIS_PORT || 6379
});

const sessionIdsScript = readFileSync(__dirname + '/session-ids.lua', 'utf8');

const connectedClients = {};
let numRegistrations = 0;
let numConnections = 0;

const sendToUser = (userId, channel, method, payload) => {
	fetchSessionIdsForUser(userId, function (err, sessionIds) {
		if (err) return console.error('could not get session ids for', userId, err);
		for (let sessionId of sessionIds) {
			sendToSession(sessionId, channel, method, payload);
		}
	});
}

const sendToSession = (sessionId, channel, method, payload) => {
	for (let connection of connectionsForSession(sessionId)) {
		connection.emit(channel, {m: method, o: payload});
	}
}

const fetchSessionIdsForUser = (userId, callback) => {
	redisClient.eval(sessionIdsScript, 0, userId, callback);
}

const connectionsForSession = (sessionId) => {
	if (connectedClients[sessionId]) {
		return connectedClients[sessionId];
	} else {
		return [];
	}
}

const inputServer = http.createServer((req, res) => {
	let url = require('url').parse(req.url,true);
	if (url.pathname == '/stats') {
		res.writeHead(200);
		res.end(JSON.stringify({
			connections: numConnections,
			registrations: numRegistrations,
			sessions: Object.keys(connectedClients).length
		}));
		return;
	}

	const query = url.query;
	const sessionId = query.c;
	const app = query.a;
	const method = query.m;
	const options = query.o;
	const userId = query.u;

	if (sessionId) {
		sendToSession(sessionId,app,method,options);
	}

	if (userId) {
		sendToUser(userId,app,method,options);
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
	const cookieVal = socket.request.headers.cookie;
	if (cookieVal) {
		let cookie = parseCookie(cookieVal);
		socket.sid = cookie.PHPSESSID || cookie.sessionid;
		if (socket.sid) next();
	}
	next(new Error('not authorized'));
});

io.on('connection', (socket) => {
	const sessionId = socket.sid;
	numConnections++;
	socket.on('register', () => {
		numRegistrations++;
		if (!connectedClients[sessionId]) connectedClients[sessionId] = [];
		connectedClients[sessionId].push(socket);
	});

	socket.on('disconnect', () => {
		numConnections--;
		const connections = connectedClients[sessionId];
		if (sessionId && connections) {
			if (connections.includes(socket)) {
				connections.splice(connections.indexOf(socket), 1);
				numRegistrations--;
			}
			if (connections.length === 0) {
				delete connectedClients[sessionId];
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
