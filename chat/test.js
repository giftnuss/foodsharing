'use strict'

require('browser-env')();
const request = require('request');
const test = require('tape');
const {spawn} = require('child_process');
const {serialize} = require('cookie')
const {request: httpRequest} = require('http');
const {stringify} = require('querystring')

const HTTP_URL = "http://127.0.0.1:1338";
const WS_URL = "http://127.0.0.1:1337";

// Start the server in a child process ...
const server = spawn(process.execPath, ['server']);

// ... kill it after the tests are done
test.onFinish(function(){
	server.kill();
});

// Would like to use this one, but extraHeaders seems not to work ok...
//var io = require('../js/socket.io-1.5.0.min.js');
var io = require('socket.io-client');

test('simple connection', function(t){
	t.plan(1);
	var socket = connect('somesessionid');
	socket.on("connect",function() {
		t.pass('connected to socket.io server');
		socket.disconnect();
		socket.emit('end');
	});
});

test('multiple connections', function(t){
	t.plan(3);
	var socket1 = connect('somesessionid1');
	var socket2 = connect('somesessionid2');
	var socket3 = connect('somesessionid3');
	socket1.on("connect",function() {
		t.pass('connected to socket.io server');
		socket1.disconnect();
	});
	socket2.on("connect",function() {
		t.pass('connected to socket.io server');
		socket2.disconnect();
	});
	socket3.on("connect",function() {
		t.pass('connected to socket.io server');
		socket3.disconnect();
	});
});

test('requesting stats', function(t){
	t.plan(2);
	fetchStats(function(err, stats){
		t.error(err, 'does not error');
		t.deepEqual(
			Object.keys(stats).sort(),
			['connections', 'registrations', 'sessions'],
			'has all the expected keys'
		);
	});
});

test('registering', function(t){
	t.plan(4);
	var socket = connect('somesessionid');
	socket.on("connect",function() {
		socket.emit("register");
		assertStats(t, 1, 1, 1, function(err){
			socket.disconnect();
			t.error(err, 'does not error');
		});
	});
});

test('multiple registrations for one session', function(t){
	t.plan(4);
	var sessionId = 'sharedsessionid'
	var socket1 = connect(sessionId);
	var socket2 = connect(sessionId);
	var socket3 = connect(sessionId);
	register(socket1, function(){
		register(socket2, function(){
			register(socket3, function(){
				assertStats(t, 3, 3, 1, function(err){
					socket1.disconnect();
					socket2.disconnect();
					socket3.disconnect();
					t.error(err, 'does not error');
				});
			});
		});
	});
});

test('multiple registrations with unique sessions', function(t){
	t.plan(4);
	var socket1 = connect('myownsession1');
	var socket2 = connect('myownsession2');
	var socket3 = connect('myownsession3');
	register(socket1, function(){
		register(socket2, function(){
			register(socket3, function(){
				assertStats(t, 3, 3, 3, function(err){
					socket1.disconnect();
					socket2.disconnect();
					socket3.disconnect();
					t.error(err, 'does not error');
				});
			});
		});
	});
});

test('3 connections, 2 registrations, 1 session', function(t){
	t.plan(4);
	var sessionId = 'sharedsessionid2';
	var socket1 = connect(sessionId);
	var socket2 = connect(sessionId);
	var socket3 = connect(sessionId);
	register(socket1, function(){
		register(socket2, function(){
			// NOT registering socket3
			assertStats(t, 3, 2, 1, function(err){
				socket1.disconnect();
				socket2.disconnect();
				socket3.disconnect();
				t.error(err, 'does not error');
			});
		});
	});
});

test('unregistering', function(t){
	t.plan(5);
	var socket = connect('somesessionid');
	socket.on("connect",function() {
		socket.emit("register");
		fetchStats(function(err, stats){
			t.error(err, 'does not error');
			socket.disconnect();
			fetchStats(function(err, stats){
				t.error(err, 'does not error');
				t.equal(stats.connections, 0, 'correct connection count');
				t.equal(stats.registrations, 0, 'correct registration count');
				t.equal(stats.sessions, 0, 'correct session count');
			});
		});
	});
});

test('can send a message', function(t){
	t.plan(1);
	sendMessage({
		c: 'ignored',
		a: 'ignored',
		m: 'ignored',
		o: 'ignored'
	}, function(err){
		t.error(err, 'does not error');
	});
});

test('can send and receive a message', function(t){
	t.plan(3);
	var sessionId = 'somesessionid';
	var socket = connect(sessionId);
	socket.on("connect",function() {
		socket.emit("register");
		socket.on("someapp", function(data){
			socket.disconnect();
			t.equal(data.m, 'foo', 'passed m param');
			t.equal(data.o, 'bar', 'passed o param');
		});
		sendMessage({
			c: sessionId,
			// used as channel to recv on
			a: 'someapp',
			// m and o passed as payload
			m: 'foo',
			o: 'bar'
		}, function(err){
			t.error(err, 'does not error');
		});
	});
});

test('can send and receive a message for multiple clients', function(t){
	t.plan(7);
	var sessionId = 'somesessionid';
	var socket1 = connect(sessionId);
	var socket2 = connect(sessionId);
	var socket3 = connect(sessionId);
	register(socket1, function(){
		register(socket2, function(){
			register(socket3, function(){
				socket1.on("someapp", function(data){
					socket1.disconnect();
					t.equal(data.m, 'foo', 'passed m param');
					t.equal(data.o, 'bar', 'passed o param');
				});
				socket2.on("someapp", function(data){
					socket2.disconnect();
					t.equal(data.m, 'foo', 'passed m param');
					t.equal(data.o, 'bar', 'passed o param');
				});
				socket3.on("someapp", function(data){
					socket3.disconnect();
					t.equal(data.m, 'foo', 'passed m param');
					t.equal(data.o, 'bar', 'passed o param');
				});
				sendMessage({
					c: sessionId,
					// used as channel to recv on
					a: 'someapp',
					// m and o passed as payload
					m: 'foo',
					o: 'bar'
				}, function(err){
					t.error(err, 'does not error');
				});
			});
		});
	});
});



test('works with two connections per user', (t) => {
	const opt = {extraHeaders: {Cookie: serialize('PHPSESSID', 'test-1-user-1')}}
	const client1 = io.connect(WS_URL, opt)
	const client2 = io.connect(WS_URL, opt)

	t.plan(2 * 2 + 1)
	const checkEvent = (ev) => {
		t.deepEqual(ev.m, 'some-method')
		t.deepEqual(ev.o, 'some-payload')
	}
	client1.on('some-event', checkEvent)
	client2.on('some-event', checkEvent)

	client1.emit('register')
	client2.emit('register')

	setTimeout(() => {
		const query = stringify({
			c: 'test-1-user-1', // client
			a: 'some-event', // app a.k.a channel/event
			m: 'some-method', // method
			o: 'some-payload', // options a.k.a payload
		})
		httpRequest(HTTP_URL + '?' + query, (res) => {
			t.equal(res.statusCode, 200)

			t.end()
			client1.close()
			client2.close()
		})
		.on('error', t.error)
		.end()
	}, 100)
})

test('does not send to other users', (t) => {

	// two users
	const opt1 = {extraHeaders: {Cookie: serialize('PHPSESSID', 'test-2-user-1')}}
	const user1 = io.connect(WS_URL, opt1)

	const opt2 = {extraHeaders: {Cookie: serialize('PHPSESSID', 'test-2-user-2')}}
	const user2 = io.connect(WS_URL, opt2)

	t.plan(1 + 1)
	user1.on('some-event', () => t.pass('user 1 has received `some-event`'))
	user2.on('some-event', () => t.fail('user 2 has received `some-event`'))

	user1.emit('register')
	user2.emit('register')

	setTimeout(() => {
		const query = stringify({
			c: 'test-2-user-1', // client
			a: 'some-event', // app a.k.a channel/event
			m: 'some-method', // method
			o: 'some-payload', // options a.k.a payload
		})
		httpRequest('http://localhost:1338/?' + query, (res) => {
			t.equal(res.statusCode, 200)

			t.end()
			user1.close()
			user2.close()
		})
		.on('error', t.error)
		.end()
	}, 100)
})

function connect(sessionId) {
	return io.connect(WS_URL, {
		extraHeaders: {
			cookie: 'PHPSESSID=' + sessionId
		}
	});
}

function register(socket, callback) {
	socket.on("connect", function(){
		socket.emit("register");
		callback();
	});
}

function sendMessage(params, callback){
	request(HTTP_URL, { qs: params }, function(err, response, body){
		if (err) return callback(err);
		callback();
	});
};

function fetchStats(callback) {
	request(HTTP_URL + '/stats', function(err, response, body) {
		if (err) return callback(err);
		try {
			callback(null, JSON.parse(body));
		} catch (err) {
			callback(err);
		}
	});
}

function assertStats(t, connections, registrations, sessions, callback){
	fetchStats(function(err, stats){
		if (err) return callback(err);
		t.equal(stats.connections, connections, 'correct connection count');
		t.equal(stats.registrations, registrations, 'correct registration count');
		t.equal(stats.sessions, sessions, 'correct session count');
		callback();
	});
};
