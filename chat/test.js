'use strict'

require('browser-env')();
const request = require('request');
const redis = require('redis');
const randomstring = require('randomstring');
const test = require('tape');
const {spawn} = require('child_process');
const {serialize} = require('cookie')
const {request: httpRequest} = require('http');
const {stringify} = require('querystring')

const HTTP_URL = "http://127.0.0.1:1338";
const WS_URL = "http://127.0.0.1:1337";

const redisClient = redis.createClient({
	host: process.env.REDIS_HOST || '127.0.0.1',
	port: process.env.REDIS_PORT || 6379
});

// Start the server in a child process ...
const server = spawn(process.execPath, ['server'], { stdio: 'inherit' });

// ... kill it after the tests are done
test.onFinish(() => {
	redisClient.flushdb();
	redisClient.end(true);
	server.kill();
});

// Would like to use this one, but extraHeaders seems not to work ok...
//const io = require('../js/socket.io-1.5.0.min.js');
const io = require('socket.io-client');

test('simple connection', t => {
	t.timeoutAfter(10000);
	t.plan(1);
	const socket = connect(t, 'somesessionid');
	socket.on("connect",() => {
		t.pass('connected to socket.io server');
	});
});

test('multiple connections', t => {
	t.timeoutAfter(10000);
	t.plan(3);
	const socket1 = connect(t, 'somesessionid1');
	const socket2 = connect(t, 'somesessionid2');
	const socket3 = connect(t, 'somesessionid3');
	socket1.on("connect", () => {
		t.pass('connected to socket.io server');
	});
	socket2.on("connect", () => {
		t.pass('connected to socket.io server');
	});
	socket3.on("connect", () => {
		t.pass('connected to socket.io server');
	});
});

test('requesting stats', t => {
	t.timeoutAfter(10000);
	t.plan(2);
	fetchStats((err, stats) => {
		t.error(err, 'does not error');
		t.deepEqual(
			Object.keys(stats).sort(),
			['connections', 'registrations', 'sessions'],
			'has all the expected keys'
		);
	});
});

test('registering', t => {
	t.timeoutAfter(10000);
	t.plan(4);
	const socket = connect(t, 'somesessionid');
	socket.on("connect",() => {
		socket.emit("register");
		assertStats(t, 1, 1, 1, err => {
			t.error(err, 'does not error');
		});
	});
});

test('multiple registrations for one session', t => {
	t.timeoutAfter(10000);
	t.plan(4);
	const sessionId = 'sharedsessionid'
	const socket1 = connect(t, sessionId);
	const socket2 = connect(t, sessionId);
	const socket3 = connect(t, sessionId);
	register(socket1, () => {
		register(socket2, () => {
			register(socket3, () => {
				assertStats(t, 3, 3, 1, err => {
					t.error(err, 'does not error');
				});
			});
		});
	});
});

test('multiple registrations with unique sessions', t => {
	t.timeoutAfter(10000);
	t.plan(4);
	const socket1 = connect(t, 'myownsession1');
	const socket2 = connect(t, 'myownsession2');
	const socket3 = connect(t, 'myownsession3');
	register(socket1, () => {
		register(socket2, () => {
			register(socket3, () => {
				assertStats(t, 3, 3, 3, err => {
					t.error(err, 'does not error');
				});
			});
		});
	});
});

test('3 connections, 2 registrations, 1 session', t => {
	t.timeoutAfter(10000);
	t.plan(4);
	const sessionId = 'sharedsessionid2';
	const socket1 = connect(t, sessionId);
	const socket2 = connect(t, sessionId);
	const socket3 = connect(t, sessionId);
	register(socket1, () => {
		register(socket2, () => {
			// NOT registering socket3
			assertStats(t, 3, 2, 1, err => {
				t.error(err, 'does not error');
			});
		});
	});
});

test('unregistering', t => {
	t.timeoutAfter(10000);
	t.plan(5);
	const socket = connect(t, 'somesessionid');
	socket.on("connect",() => {
		socket.emit("register");
		fetchStats((err, stats) => {
			socket.disconnect();
			t.error(err, 'does not error');
			fetchStats((err, stats) => {
				t.error(err, 'does not error');
				t.equal(stats.connections, 0, 'correct connection count');
				t.equal(stats.registrations, 0, 'correct registration count');
				t.equal(stats.sessions, 0, 'correct session count');
			});
		});
	});
});

test('can send a message', t => {
	t.timeoutAfter(10000);
	t.plan(1);
	sendMessage({
		c: 'ignored',
		a: 'ignored',
		m: 'ignored',
		o: 'ignored'
	}, err => {
		t.error(err, 'does not error');
	});
});

test('can send and receive a message', t => {
	t.timeoutAfter(10000);
	t.plan(3);
	const sessionId = 'somesessionid';
	const socket = connect(t, sessionId);
	socket.on("connect",() => {
		socket.emit("register");
		socket.on("someapp", data => {
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
		}, err => {
			t.error(err, 'does not error');
		});
	});
});

test('can send and receive a message for multiple clients', t => {
	t.timeoutAfter(10000);
	t.plan(7);
	const sessionId = 'somesessionid';
	const socket1 = connect(t, sessionId);
	const socket2 = connect(t, sessionId);
	const socket3 = connect(t, sessionId);
	register(socket1, () => {
		register(socket2, () => {
			register(socket3, () => {
				socket1.on("someapp", data => {
					t.equal(data.m, 'foo', 'passed m param');
					t.equal(data.o, 'bar', 'passed o param');
				});
				socket2.on("someapp", data => {
					t.equal(data.m, 'foo', 'passed m param');
					t.equal(data.o, 'bar', 'passed o param');
				});
				socket3.on("someapp", data => {
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
				}, err => {
					t.error(err, 'does not error');
				});
			});
		});
	});
});

test('can send to php users', t => {
	t.timeoutAfter(10000);
	t.plan(4);
	let sessionId = randomstring.generate();
	let userId = 1;
	addPHPSessionToRedis(userId, sessionId, err => {
		t.error(err)
		let socket = connect(t, sessionId);
		socket.on("someapp", data => {
			t.equal(data.m, 'foo', 'passed m param');
			t.equal(data.o, 'bar', 'passed o param');
		});
		register(socket, () => {
			sendMessage({
				u: userId,
				// used as channel to recv on
				a: 'someapp',
				// m and o passed as payload
				m: 'foo',
				o: 'bar'
			}, (err) => {
				t.error(err, 'does not error');
			});
		});
	});
});

test('can send to api users', t => {
	t.timeoutAfter(10000);
	t.plan(4);
	let sessionId = randomstring.generate();
	let userId = 2;
	addAPISessionToRedis(userId, sessionId, err => {
		t.error(err)
		let socket = connect(t, sessionId, 'sessionid'); // django session cookie name
		socket.on("someapp", (data) => {
			t.equal(data.m, 'foo', 'passed m param');
			t.equal(data.o, 'bar', 'passed o param');
		});
		register(socket, () => {
			sendMessage({
				u: userId,
				// used as channel to recv on
				a: 'someapp',
				// m and o passed as payload
				m: 'foo',
				o: 'bar'
			}, (err) => {
				t.error(err, 'does not error');
			});
		});
	});
});

test('works with two connections per user', t => {
	t.timeoutAfter(10000);

	const client1 = connect(t, 'test-1-user-1')
	const client2 = connect(t, 'test-1-user-1')

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
		})
		.on('error', t.error)
		.end()
	}, 100)
})

test('does not send to other users', t => {
	t.timeoutAfter(10000);

	// two users
	const user1 = connect(t, 'test-2-user-1');
	const user2 = connect(t, 'test-2-user-2');

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
		})
		.on('error', t.error)
		.end()
	}, 100)
})

function connect(t, sessionId, cookieName = 'PHPSESSID') {
	let socket = io.connect(WS_URL, {
		extraHeaders: {
			cookie: serialize(cookieName, sessionId)
		}
	});
	t.on('end', () => socket.disconnect());
	return socket;
}

function register(socket, callback) {
	socket.on("connect", () => {
		socket.emit("register");
		callback();
	});
}

function sendMessage(params, callback){
	request(HTTP_URL, { qs: params }, (err, response, body) => {
		if (err) return callback(err);
		callback();
	});
};

function fetchStats(callback) {
	request(HTTP_URL + '/stats', (err, response, body) => {
		if (err) return callback(err);
		try {
			callback(null, JSON.parse(body));
		} catch (err) {
			callback(err);
		}
	});
}

function addPHPSessionToRedis(userId, sessionId, callback) {
	redisClient.multi()
		.set(`PHPREDIS_SESSION:${sessionId}`, 'foo')
		.sadd(`php:user:${userId}:sessions`, sessionId)
		.exec(err => {
			callback(err);
		});
}

function addAPISessionToRedis(userId, sessionId, callback) {
	redisClient.multi()
		.set(`:1:django.contrib.sessions.cache${sessionId}`, 'foo')
		.sadd(`api:user:${userId}:sessions`, sessionId)
		.exec(err => {
			callback(err);
		});
}

function assertStats(t, connections, registrations, sessions, callback){
	fetchStats((err, stats) => {
		if (err) return callback(err);
		t.equal(stats.connections, connections, 'correct connection count');
		t.equal(stats.registrations, registrations, 'correct registration count');
		t.equal(stats.sessions, sessions, 'correct session count');
		callback();
	});
};
