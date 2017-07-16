'use strict'

const test = require('tape')
const client = require('socket.io-client')
const {serialize} = require('cookie')
const {request} = require('http')
const {stringify} = require('querystring')

const {inputServer, chatServer} = require('./server') // runs the servers

test('works with two connections per user', (t) => {
	const opt = {extraHeaders: {Cookie: serialize('PHPSESSID', 'test-1-user-1')}}
	const client1 = client('ws://localhost:1337', opt)
	const client2 = client('ws://localhost:1337', opt)

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
		request('http://localhost:1338/?' + query, (res) => {
			t.equal(res.statusCode, 200)

			t.end()
			client1.close()
			client2.close()
		})
		.on('error', t.ifError)
		.end()
	}, 100)
})

test('does not send to other users', (t) => {

	// two users
	const opt1 = {extraHeaders: {Cookie: serialize('PHPSESSID', 'test-2-user-1')}}
	const user1 = client('ws://localhost:1337', opt1)

	const opt2 = {extraHeaders: {Cookie: serialize('PHPSESSID', 'test-2-user-2')}}
	const user2 = client('ws://localhost:1337', opt2)

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
		request('http://localhost:1338/?' + query, (res) => {
			t.equal(res.statusCode, 200)

			t.end()
			user1.close()
			user2.close()
		})
		.on('error', t.ifError)
		.end()
	}, 100)
})

test.onFinish(() => {
	inputServer.close()
	chatServer.close()
})
