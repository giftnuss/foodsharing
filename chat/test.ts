import { Tedis } from 'tedis';
import { spawn } from 'child_process';
import * as test from 'tape';
import * as randomString from 'randomstring';
import { Test } from 'tape';
import * as io from 'socket.io-client';
import { serialize } from 'cookie';
import * as superagent from 'superagent';
import { Response } from 'superagent';
import Socket = SocketIOClient.Socket;

const HTTP_URL = 'http://127.0.0.1:1338';
const WS_URL = 'http://127.0.0.1:1337';

const redisClient = new Tedis({
    host: process.env.REDIS_HOST ?? '127.0.0.1',
    port: Number(process.env.REDIS_PORT) || 6379
});

// Start the server in a child process ...
const server = spawn('ts-node', ['src/index.ts'], { stdio: 'inherit' });

// ... kill it after the tests are done
test.onFinish(() => {
    redisClient.command('FLUSHDB').then(() => redisClient.close()).catch(error => { console.log(error); });
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
    sendMessage([1, 2, 3], 'foo', 'bar', {}, (err: any) => {
        t.error(err, 'does not error');
    });
});

test('can send to users', t => {
    t.timeoutAfter(10000);
    t.plan(3);
    const sessionId = randomString.generate();
    const userId = 1;
    addPHPSessionToRedis(userId, sessionId, () => {
        const socket = connect(t, sessionId);
        socket.on('some-app', (data: any) => {
            t.equal(data.m, 'some-method', 'passed m param');
            t.deepEqual(data.o, { someKey: 'some-payload' }, 'passed o param');
        });
        register(socket, () => {
            sendMessage([userId], 'some-app', 'some-method', { someKey: 'some-payload' }, (err: any) => {
                t.error(err, 'does not error');
            });
        });
    });
});

test('works with two connections per user', t => {
    t.timeoutAfter(20000);

    const client1 = connect(t, 'test-1-user-1');
    const client2 = connect(t, 'test-1-user-1');
    addPHPSessionToRedis(1, 'test-1-user-1', () => {});

    t.plan(5); // checkEvent contains 2 checks and will be invoked for 2 sessions + the status code check
    const checkEvent = (ev: any): void => {
        t.deepEqual(ev.m, 'some-method');
        t.deepEqual(ev.o, { someKey: 'some-payload' });
    };
    client1.on('some-app', checkEvent);
    client2.on('some-app', checkEvent);

    register(client1, () => {
        register(client2, () => {
            sendMessage([1], 'some-app', 'some-method', { someKey: 'some-payload' },
                (error, res) => {
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

    addPHPSessionToRedis(1, 'test-2-user-1', () => {});
    addPHPSessionToRedis(2, 'test-2-user-2', () => {});

    t.plan(1 + 1);
    user1.on('some-event', () => t.pass('user 1 has received `some-event`'));
    user2.on('some-event', () => t.fail('user 2 has received `some-event`'));

    register(user1, () => {
        register(user2, () => {
            sendMessage([1], 'some-event', 'some-method', { foo: 'bar' },
                (error, res) => {
                    if (error) {
                        t.error(error);
                    }
                    t.equal(res.status, 200);
                    setTimeout(() => t.end(), 100); // 100ms window to see if user2 receives event...
                }
            );
        });
    });
});

test('online status is false for non-connected user', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-3-user-1', () => {});
    superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
        if (err) {
            t.error(err);
        }
        t.equal(response.type, 'application/json', 'content type is JSON');
        t.equal(response.body, false, 'response body is "false"');
    });
});

test('online status is true initially after user connected', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-4-user-1', () => {});
    const socket = connect(t, 'test-4-user-1');
    register(socket, () => {
        superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
            if (err) {
                t.error(err);
            }
            t.equal(response.type, 'application/json', 'content type is JSON');
            t.equal(response.body, true, 'response body is "true"');
        });
    });
});

test('online status is false after user window moved into the background', t => {
    t.plan(2);
    addPHPSessionToRedis(1, 'test-5-user-1', () => {});
    const socket = connect(t, 'test-5-user-1');
    register(socket, () => {
        socket.emit('visibilitychange', true); // hidden = true
        setTimeout(() => { // give the server some time to process the event
            superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
                if (err) {
                    t.error(err);
                }
                t.equal(response.type, 'application/json', 'content type is JSON');
                t.equal(response.body, false, 'response body is "false"');
            });
        }, 100);
    });
});

test('online status is true after window came into the foreground again', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-6-user-1', () => {});
    const socket = connect(t, 'test-6-user-1');
    register(socket, () => {
        socket.emit('visibilitychange', true);
        setTimeout(() => { // give the server some time to process the event
            socket.emit('visibilitychange', false);
            setTimeout(() => {
                superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
                    if (err) {
                        t.error(err);
                    }
                    t.equal(response.type, 'application/json', 'content type is JSON');
                    t.equal(response.body, true, 'response body is "true"');
                });
            }, 100);
        }, 100);
    });
});

test('online status is false if user has two windows and both are in the background', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-6-user-1', () => {});
    const socket1 = connect(t, 'test-7-user-1');
    const socket2 = connect(t, 'test-7-user-1');
    register(socket1, () => {
        register(socket2, () => {
            socket1.emit('visibilitychange', true);
            setTimeout(() => { // give the server some time to process the event
                socket2.emit('visibilitychange', true);
                setTimeout(() => {
                    superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
                        if (err) {
                            t.error(err);
                        }
                        t.equal(response.type, 'application/json', 'content type is JSON');
                        t.equal(response.body, false, 'response body is "false"');
                    });
                }, 100);
            }, 100);
        });
    });
});

test('online status is true if user has two windows and only one is in the background', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-8-user-1', () => {});
    const socket1 = connect(t, 'test-8-user-1');
    const socket2 = connect(t, 'test-8-user-1'); // second browser window
    register(socket1, () => {
        register(socket2, () => {
            socket1.emit('visibilitychange', false);
            setTimeout(() => { // give the server some time to process the event
                socket2.emit('visibilitychange', false);
                setTimeout(() => {
                    superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
                        if (err) {
                            t.error(err);
                        }
                        t.equal(response.type, 'application/json', 'content type is JSON');
                        t.equal(response.body, true, 'response body is "true"');
                    });
                }, 100);
            }, 100);
        });
    });
});

test('online status is false if user has two windows in different browsers and both are in the background', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-9-user-1-browser-1', () => {});
    addPHPSessionToRedis(1, 'test-9-user-1-browser-2', () => {});
    const socket1 = connect(t, 'test-9-user-1-browser-1');
    const socket2 = connect(t, 'test-9-user-1-browser-2');
    register(socket1, () => {
        register(socket2, () => {
            socket1.emit('visibilitychange', true);
            setTimeout(() => { // give the server some time to process the event
                socket2.emit('visibilitychange', true);
                setTimeout(() => {
                    superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
                        if (err) {
                            t.error(err);
                        }
                        t.equal(response.type, 'application/json', 'content type is JSON');
                        t.equal(response.body, false, 'response body is "false"');
                    });
                }, 100);
            }, 100);
        });
    });
});

test('online status is true if user has two windows in different browsers and only one is in the background', t => {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-10-user-1-browser-1', () => {});
    addPHPSessionToRedis(1, 'test-10-user-1-browser-2', () => {});
    const socket1 = connect(t, 'test-10-user-1-browser-1');
    const socket2 = connect(t, 'test-10-user-1-browser-2');
    register(socket1, () => {
        register(socket2, () => {
            socket1.emit('visibilitychange', true);
            setTimeout(() => { // give the server some time to process the event
                socket2.emit('visibilitychange', false);
                setTimeout(() => {
                    superagent.get(HTTP_URL + '/users/1/is-online').end((err, response) => {
                        if (err) {
                            t.error(err);
                        }
                        t.equal(response.type, 'application/json', 'content type is JSON');
                        t.equal(response.body, true, 'response body is "true"');
                    });
                }, 100);
            }, 100);
        });
    });
});

function connect (t: Test, sessionId: string, cookieName = 'PHPSESSID'): Socket {
    const socket = io.connect(WS_URL, {
        transports: ['websocket'],
        // @ts-expect-error - according to the socket.io client documentation, extraHeaders is a possible option when using node.js
        extraHeaders: {
            cookie: serialize(cookieName, sessionId)
        }
    });
    // @ts-expect-error - until https://github.com/DefinitelyTyped/DefinitelyTyped/pull/44442 is merged
    t.on('end', () => socket.disconnect());
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

function sendMessage (userIds: number[], channel: string, method: string, options: object, callback: (error: any, res: Response) => any): void {
    superagent.post(HTTP_URL + `/users/${userIds.join(',')}/${channel}/${method}`).send(options).end(callback);
}

function fetchStats (callback: (error: any, stats?: {connections: number, registrations: number, sessions: number}) => any): void {
    superagent.get(HTTP_URL + '/stats').end((error, response) => {
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
    redisClient.set(`PHPREDIS_SESSION:${sessionId}`, 'foo')
        .then(async () =>
            await redisClient.sadd(`php:user:${userId}:sessions`, sessionId)
        ).then(callback)
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
