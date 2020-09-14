"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
var _a;
exports.__esModule = true;
var tedis_1 = require("tedis");
var child_process_1 = require("child_process");
var test = require("tape");
var randomString = require("randomstring");
var io = require("socket.io-client");
var cookie_1 = require("cookie");
var superagent = require("superagent");
var HTTP_URL = 'http://127.0.0.1:1338';
var WS_URL = 'http://127.0.0.1:1337';
var redisClient = new tedis_1.Tedis({
    host: (_a = process.env.REDIS_HOST) !== null && _a !== void 0 ? _a : '127.0.0.1',
    port: Number(process.env.REDIS_PORT) || 6379
});
// Start the server in a child process ...
var server = child_process_1.spawn('ts-node', ['src/index.ts'], { stdio: 'inherit' });
// ... kill it after the tests are done
test.onFinish(function () {
    redisClient.command('FLUSHDB').then(function () { return redisClient.close(); })["catch"](function (error) { console.log(error); });
    server.kill();
});
test('simple connection', function (t) {
    t.timeoutAfter(10000);
    t.plan(1);
    var socket = connect(t, 'somesessionid');
    socket.on('connect', function () {
        t.pass('connected to socket.io server');
    });
});
test('multiple connections', function (t) {
    t.timeoutAfter(10000);
    t.plan(3);
    var socket1 = connect(t, 'somesessionid1');
    var socket2 = connect(t, 'somesessionid2');
    var socket3 = connect(t, 'somesessionid3');
    socket1.on('connect', function () {
        t.pass('connected to socket.io server');
    });
    socket2.on('connect', function () {
        t.pass('connected to socket.io server');
    });
    socket3.on('connect', function () {
        t.pass('connected to socket.io server');
    });
});
test('requesting stats', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    fetchStats(function (err, stats) {
        t.error(err, 'does not error');
        t.deepEqual(Object.keys(stats).sort(function (a, b) { return a.localeCompare(b); }), ['connections', 'registrations', 'sessions'], 'has all the expected keys');
    });
});
test('registering', function (t) {
    t.timeoutAfter(10000);
    t.plan(4);
    var socket = connect(t, 'somesessionid');
    socket.on('connect', function () {
        socket.emit('register');
        assertStats(t, 1, 1, 1, function (err) {
            t.error(err, 'does not error');
        });
    });
});
test('multiple registrations for one session', function (t) {
    t.timeoutAfter(10000);
    t.plan(4);
    var sessionId = 'sharedsessionid';
    var socket1 = connect(t, sessionId);
    var socket2 = connect(t, sessionId);
    var socket3 = connect(t, sessionId);
    register(socket1, function () {
        register(socket2, function () {
            register(socket3, function () {
                assertStats(t, 3, 3, 1, function (err) {
                    t.error(err, 'does not error');
                });
            });
        });
    });
});
test('multiple registrations with unique sessions', function (t) {
    t.timeoutAfter(10000);
    t.plan(4);
    var socket1 = connect(t, 'myownsession1');
    var socket2 = connect(t, 'myownsession2');
    var socket3 = connect(t, 'myownsession3');
    register(socket1, function () {
        register(socket2, function () {
            register(socket3, function () {
                assertStats(t, 3, 3, 3, function (err) {
                    t.error(err, 'does not error');
                });
            });
        });
    });
});
test('3 connections, 2 registrations, 1 session', function (t) {
    t.timeoutAfter(10000);
    t.plan(4);
    var sessionId = 'sharedsessionid2';
    var socket1 = connect(t, sessionId);
    var socket2 = connect(t, sessionId);
    connect(t, sessionId);
    register(socket1, function () {
        register(socket2, function () {
            // NOT registering the third socket connection
            assertStats(t, 3, 2, 1, function (err) {
                t.error(err, 'does not error');
            });
        });
    });
});
test('unregistering', function (t) {
    t.timeoutAfter(10000);
    t.plan(5);
    var socket = connect(t, 'somesessionid');
    socket.on('connect', function () {
        socket.emit('register');
        fetchStats(function (err) {
            socket.disconnect();
            t.error(err, 'does not error');
            fetchStats(function (err, stats) {
                t.error(err, 'does not error');
                t.equal(stats.connections, 0, 'correct connection count');
                t.equal(stats.registrations, 0, 'correct registration count');
                t.equal(stats.sessions, 0, 'correct session count');
            });
        });
    });
});
test('can send a message', function (t) {
    t.timeoutAfter(10000);
    t.plan(1);
    sendMessage([1, 2, 3], 'foo', 'bar', {}, function (err) {
        t.error(err, 'does not error');
    });
});
test('can send to users', function (t) {
    t.timeoutAfter(10000);
    t.plan(3);
    var sessionId = randomString.generate();
    var userId = 1;
    addPHPSessionToRedis(userId, sessionId, function () {
        var socket = connect(t, sessionId);
        socket.on('some-app', function (data) {
            t.equal(data.m, 'some-method', 'passed m param');
            t.deepEqual(data.o, { someKey: 'some-payload' }, 'passed o param');
        });
        register(socket, function () {
            sendMessage([userId], 'some-app', 'some-method', { someKey: 'some-payload' }, function (err) {
                t.error(err, 'does not error');
            });
        });
    });
});
test('works with two connections per user', function (t) {
    t.timeoutAfter(20000);
    var client1 = connect(t, 'test-1-user-1');
    var client2 = connect(t, 'test-1-user-1');
    addPHPSessionToRedis(1, 'test-1-user-1', function () { });
    t.plan(5); // checkEvent contains 2 checks and will be invoked for 2 sessions + the status code check
    var checkEvent = function (ev) {
        t.deepEqual(ev.m, 'some-method');
        t.deepEqual(ev.o, { someKey: 'some-payload' });
    };
    client1.on('some-app', checkEvent);
    client2.on('some-app', checkEvent);
    register(client1, function () {
        register(client2, function () {
            sendMessage([1], 'some-app', 'some-method', { someKey: 'some-payload' }, function (error, res) {
                if (error) {
                    t.error(error);
                }
                t.equal(res.status, 200);
            });
        });
    });
});
test('does not send to other users', function (t) {
    t.timeoutAfter(10000);
    // two users
    var user1 = connect(t, 'test-2-user-1');
    var user2 = connect(t, 'test-2-user-2');
    addPHPSessionToRedis(1, 'test-2-user-1', function () { });
    addPHPSessionToRedis(2, 'test-2-user-2', function () { });
    t.plan(1 + 1);
    user1.on('some-event', function () { return t.pass('user 1 has received `some-event`'); });
    user2.on('some-event', function () { return t.fail('user 2 has received `some-event`'); });
    register(user1, function () {
        register(user2, function () {
            sendMessage([1], 'some-event', 'some-method', { foo: 'bar' }, function (error, res) {
                if (error) {
                    t.error(error);
                }
                t.equal(res.status, 200);
                setTimeout(function () { return t.end(); }, 100); // 100ms window to see if user2 receives event...
            });
        });
    });
});
test('online status is false for non-connected user', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-3-user-1', function () { });
    superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
        if (err) {
            t.error(err);
        }
        t.equal(response.type, 'application/json', 'content type is JSON');
        t.equal(response.body, false, 'response body is "false"');
    });
});
test('online status is true initially after user connected', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-4-user-1', function () { });
    var socket = connect(t, 'test-4-user-1');
    register(socket, function () {
        superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
            if (err) {
                t.error(err);
            }
            t.equal(response.type, 'application/json', 'content type is JSON');
            t.equal(response.body, true, 'response body is "true"');
        });
    });
});
test('online status is false after user window moved into the background', function (t) {
    t.plan(2);
    addPHPSessionToRedis(1, 'test-5-user-1', function () { });
    var socket = connect(t, 'test-5-user-1');
    register(socket, function () {
        socket.emit('visibilitychange', true); // hidden = true
        setTimeout(function () {
            superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
                if (err) {
                    t.error(err);
                }
                t.equal(response.type, 'application/json', 'content type is JSON');
                t.equal(response.body, false, 'response body is "false"');
            });
        }, 100);
    });
});
test('online status is true after window came into the foreground again', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-6-user-1', function () { });
    var socket = connect(t, 'test-6-user-1');
    register(socket, function () {
        socket.emit('visibilitychange', true);
        setTimeout(function () {
            socket.emit('visibilitychange', false);
            setTimeout(function () {
                superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
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
test('online status is false if user has two windows and both are in the background', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-6-user-1', function () { });
    var socket1 = connect(t, 'test-7-user-1');
    var socket2 = connect(t, 'test-7-user-1');
    register(socket1, function () {
        register(socket2, function () {
            socket1.emit('visibilitychange', true);
            setTimeout(function () {
                socket2.emit('visibilitychange', true);
                setTimeout(function () {
                    superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
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
test('online status is true if user has two windows and only one is in the background', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-8-user-1', function () { });
    var socket1 = connect(t, 'test-8-user-1');
    var socket2 = connect(t, 'test-8-user-1'); // second browser window
    register(socket1, function () {
        register(socket2, function () {
            socket1.emit('visibilitychange', false);
            setTimeout(function () {
                socket2.emit('visibilitychange', false);
                setTimeout(function () {
                    superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
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
test('online status is false if user has two windows in different browsers and both are in the background', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-9-user-1-browser-1', function () { });
    addPHPSessionToRedis(1, 'test-9-user-1-browser-2', function () { });
    var socket1 = connect(t, 'test-9-user-1-browser-1');
    var socket2 = connect(t, 'test-9-user-1-browser-2');
    register(socket1, function () {
        register(socket2, function () {
            socket1.emit('visibilitychange', true);
            setTimeout(function () {
                socket2.emit('visibilitychange', true);
                setTimeout(function () {
                    superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
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
test('online status is true if user has two windows in different browsers and only one is in the background', function (t) {
    t.timeoutAfter(10000);
    t.plan(2);
    addPHPSessionToRedis(1, 'test-10-user-1-browser-1', function () { });
    addPHPSessionToRedis(1, 'test-10-user-1-browser-2', function () { });
    var socket1 = connect(t, 'test-10-user-1-browser-1');
    var socket2 = connect(t, 'test-10-user-1-browser-2');
    register(socket1, function () {
        register(socket2, function () {
            socket1.emit('visibilitychange', true);
            setTimeout(function () {
                socket2.emit('visibilitychange', false);
                setTimeout(function () {
                    superagent.get(HTTP_URL + '/users/1/is-online').end(function (err, response) {
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
function connect(t, sessionId, cookieName) {
    if (cookieName === void 0) { cookieName = 'PHPSESSID'; }
    var socket = io.connect(WS_URL, {
        transports: ['websocket'],
        // @ts-expect-error - according to the socket.io client documentation, extraHeaders is a possible option when using node.js
        extraHeaders: {
            cookie: cookie_1.serialize(cookieName, sessionId)
        }
    });
    // @ts-expect-error - until https://github.com/DefinitelyTyped/DefinitelyTyped/pull/44442 is merged
    t.on('end', function () { return socket.disconnect(); });
    return socket;
}
function register(socket, callback) {
    if (socket.connected) {
        setTimeout(handler, 0);
    }
    else {
        socket.on('connect', handler);
    }
    function handler() {
        socket.emit('register');
        callback();
    }
}
function sendMessage(userIds, channel, method, options, callback) {
    superagent.post(HTTP_URL + ("/users/" + userIds.join(',') + "/" + channel + "/" + method)).send(options).end(callback);
}
function fetchStats(callback) {
    superagent.get(HTTP_URL + '/stats').end(function (error, response) {
        if (error) {
            callback(error);
        }
        try {
            callback(null, response.body);
        }
        catch (err) {
            callback(err);
        }
    });
}
function addPHPSessionToRedis(userId, sessionId, callback) {
    var _this = this;
    redisClient.set("PHPREDIS_SESSION:" + sessionId, 'foo')
        .then(function () { return __awaiter(_this, void 0, void 0, function () { return __generator(this, function (_a) {
        switch (_a.label) {
            case 0: return [4 /*yield*/, redisClient.sadd("php:user:" + userId + ":sessions", sessionId)];
            case 1: return [2 /*return*/, _a.sent()];
        }
    }); }); }).then(callback)["catch"](function (error) { return callback(error); });
}
function assertStats(t, connections, registrations, sessions, callback) {
    fetchStats(function (err, stats) {
        if (err)
            return callback(err);
        t.equal(stats === null || stats === void 0 ? void 0 : stats.connections, connections, 'correct connection count');
        t.equal(stats === null || stats === void 0 ? void 0 : stats.registrations, registrations, 'correct registration count');
        t.equal(stats === null || stats === void 0 ? void 0 : stats.sessions, sessions, 'correct session count');
        callback();
    });
}
