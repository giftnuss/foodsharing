import { Tedis } from 'tedis';
import { spawn } from 'child_process';
import * as test from 'tape';
import * as randomString from 'randomstring';
import { Test } from 'tape';
import * as io from 'socket.io-client';
import { serialize } from 'cookie';
import * as superagent from 'superagent';
import Socket = SocketIOClient.Socket;

const HTTP_URL = 'http://127.0.0.1:1338';
const WS_URL = 'http://127.0.0.1:1337';

const redisClient = new Tedis({
    host: process.env.REDIS_HOST ?? '127.0.0.1',
    port: Number(process.env.REDIS_PORT) || 6379
});

// Start the server in a child process ...
const server = spawn(process.execPath, ['dist/index.js'], { stdio: 'inherit' });

// ... kill it after the tests are done
test.onFinish(() => {
    redisClient.command('FLUSHDB').then(redisClient.close).catch(error => { console.log(error); });
    server.kill();
});

test('simple connection', t => {
    t.timeoutAfter(10000);
    t.plan(1);
    const socket = connect(t, 'somesessionid');
    socket.on('connect', () => {
        t.pass('connected to socket.io server');
    });
});

test('multiple connections', t => {
    t.timeoutAfter(10000);
    t.plan(3);
    const socket1 = connect(t, 'somesessionid1');
    const socket2 = connect(t, 'somesessionid2');
    const socket3 = connect(t, 'somesessionid3');
    socket1.on('connect', () => {
        t.pass('connected to socket.io server');
    });
    socket2.on('connect', () => {
        t.pass('connected to socket.io server');
    });
    socket3.on('connect', () => {
        t.pass('connected to socket.io server');
    });
});

test('requesting stats', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    fetchStats((err: any, stats: any) => {
        t.error(err, 'does not error');
        t.deepEqual(
            Object.keys(stats).sort((a, b) => a.localeCompare(b)),
            ['connections', 'registrations', 'sessions'],
            'has all the expected keys'
        );
    });
});

test('registering', t => {
    t.timeoutAfter(10000);
    t.plan(4);
    const socket = connect(t, 'somesessionid');
    socket.on('connect', () => {
        socket.emit('register');
        assertStats(t, 1, 1, 1, (err: any) => {
            t.error(err, 'does not error');
        });
    });
});

test('multiple registrations for one session', t => {
    t.timeoutAfter(10000);
    t.plan(4);
    const sessionId = 'sharedsessionid';
    const socket1 = connect(t, sessionId);
    const socket2 = connect(t, sessionId);
    const socket3 = connect(t, sessionId);
    register(socket1, () => {
        register(socket2, () => {
            register(socket3, () => {
                assertStats(t, 3, 3, 1, (err: any) => {
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
                assertStats(t, 3, 3, 3, (err: any) => {
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
    connect(t, sessionId);
    register(socket1, () => {
        register(socket2, () => {
            // NOT registering the third socket connection
            assertStats(t, 3, 2, 1, (err: any) => {
                t.error(err, 'does not error');
            });
        });
    });
});

test('unregistering', t => {
    t.timeoutAfter(10000);
    t.plan(5);
    const socket = connect(t, 'somesessionid');
    socket.on('connect', () => {
        socket.emit('register');
        fetchStats((err: any) => {
            socket.disconnect();
            t.error(err, 'does not error');
            fetchStats((err: any, stats: any) => {
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
    }, (err: any) => {
        t.error(err, 'does not error');
    });
});

test('can send and receive a message', t => {
    t.timeoutAfter(10000);
    t.plan(3);
    const sessionId = 'somesessionid';
    const socket = connect(t, sessionId);
    socket.on('connect', () => {
        socket.emit('register');
        socket.on('someapp', (data: any) => {
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
        }, (err: any) => {
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
                socket1.on('someapp', (data: any) => {
                    t.equal(data.m, 'foo', 'passed m param');
                    t.equal(data.o, 'bar', 'passed o param');
                });
                socket2.on('someapp', (data: any) => {
                    t.equal(data.m, 'foo', 'passed m param');
                    t.equal(data.o, 'bar', 'passed o param');
                });
                socket3.on('someapp', (data: any) => {
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
                }, (err: any) => {
                    t.error(err, 'does not error');
                });
            });
        });
    });
});

test('can send to php users', t => {
    t.timeoutAfter(10000);
    t.plan(4);
    const sessionId = randomString.generate();
    const userId = 1;
    addPHPSessionToRedis(userId, sessionId, (err: any) => {
        t.error(err);
        const socket = connect(t, sessionId);
        socket.on('someapp', (data: any) => {
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
            }, (err: any) => {
                t.error(err, 'does not error');
            });
        });
    });
});

test('can send to api users', t => {
    t.timeoutAfter(10000);
    t.plan(4);
    const sessionId = randomString.generate();
    const userId = 2;
    addAPISessionToRedis(userId, sessionId, (err: any) => {
        t.error(err);
        const socket = connect(t, sessionId, 'sessionid'); // django session cookie name
        socket.on('someapp', (data: any) => {
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
            }, (err: any) => {
                t.error(err, 'does not error');
            });
        });
    });
});

test('works with two connections per user', t => {
    t.timeoutAfter(10000);

    const client1 = connect(t, 'test-1-user-1');
    const client2 = connect(t, 'test-1-user-1');

    t.plan(2 * 2 + 1);
    const checkEvent = (ev: any): void => {
        t.deepEqual(ev.m, 'some-method');
        t.deepEqual(ev.o, 'some-payload');
    };
    client1.on('some-event', checkEvent);
    client2.on('some-event', checkEvent);

    register(client1, () => {
        register(client2, () => {
            const query = {
                c: 'test-1-user-1', // client
                a: 'some-event', // app a.k.a channel/event
                m: 'some-method', // method
                o: 'some-payload' // options a.k.a payload
            };
            superagent.get(HTTP_URL).query(query).end((error, res) => {
                if (error) {
                    t.error(error);
                }
                t.equal(res.status, 200);
            });
        });
    });
});

test('does not send to other users', t => {
    t.timeoutAfter(10000);

    // two users
    const user1 = connect(t, 'test-2-user-1');
    const user2 = connect(t, 'test-2-user-2');

    t.plan(1 + 1);
    user1.on('some-event', () => t.pass('user 1 has received `some-event`'));
    user2.on('some-event', () => t.fail('user 2 has received `some-event`'));

    register(user1, () => {
        register(user2, () => {
            const query = {
                c: 'test-2-user-1', // client
                a: 'some-event', // app a.k.a channel/event
                m: 'some-method', // method
                o: 'some-payload' // options a.k.a payload
            };
            superagent.get(HTTP_URL).query(query).end((error, res) => {
                if (error) {
                    t.error(error);
                }
                t.equal(res.status, 200);
                setTimeout(() => t.end(), 100); // 100ms window to see if user2 receives event...
            });
        });
    });
});
function connect (t: Test, sessionId: string, cookieName = 'PHPSESSID'): Socket {
    const socket = io.connect(WS_URL, {
        transports: ['websocket'],
        transportOptions: {
            polling: {
                extraHeaders: {
                    cookie: serialize(cookieName, sessionId)
                }
            }
        }
    });
    test.onFinish(() => socket.disconnect());
    return socket;
}

function register (socket: Socket, callback: () => any): void {
    if (socket.connected) {
        setTimeout(handler, 0);
    } else {
        socket.on('connect', handler);
    }
    function handler (): void {
        socket.emit('register');
        callback();
    }
}

function sendMessage (params: object, callback: (error?: any) => any): void {
    superagent.get(HTTP_URL).query(params).end((err) => {
        if (err) return callback(err);
        callback();
    });
}

function fetchStats (callback: (error: any, stats?: {connections: number, registrations: number, sessions: number}) => any): void {
    superagent.get(`${HTTP_URL}/stats`).end((error, response) => {
        if (error) {
            callback(error);
        }
        try {
            callback(null, response.body);
        } catch (err) {
            callback(err);
        }
    });
}

function addPHPSessionToRedis (userId: number, sessionId: string, callback: (error: any) => any): void {
    redisClient
        .set(`PHPREDIS_SESSION:${sessionId}`, 'foo')
        .catch(error => callback(error));
    redisClient
        .sadd(`php:user:${userId}:sessions`, sessionId)
        .catch(error => callback(error));
}

function addAPISessionToRedis (userId: number, sessionId: string, callback: (error: any) => any): void {
    redisClient
        .set(`:1:django.contrib.sessions.cache${sessionId}`, 'foo')
        .catch(error => callback(error));
    redisClient
        .sadd(`api:user:${userId}:sessions`, sessionId)
        .catch(error => callback(error));
}

function assertStats (t: Test, connections: number, registrations: number, sessions: number, callback: (error?: any) => any): void {
    fetchStats((err, stats) => {
        if (err) return callback(err);
        t.equal(stats?.connections, connections, 'correct connection count');
        t.equal(stats?.registrations, registrations, 'correct registration count');
        t.equal(stats?.sessions, sessions, 'correct session count');
        callback();
    });
}
