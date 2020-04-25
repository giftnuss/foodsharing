'use strict'

const http = require('http')
const { parse: parseCookie } = require('cookie')
const { readFile } = require('fs')
const connectSocketIO = require('socket.io')
const redis = require('redis')

const inputPort = 1338
const chatPort = 1337
const listenHost = process.argv[2] || '127.0.0.1'

const redisClient = redis.createClient({
  host: process.env.REDIS_HOST || '127.0.0.1',
  port: process.env.REDIS_PORT || 6379
})

const sessionIdsScriptFilename = `${__dirname}/../session-ids.lua`
let sessionIdsScriptSHA

const connectedClients = {}
let numRegistrations = 0
let numConnections = 0

const sendToUser = (userId, channel, method, payload) => {
  fetchSessionIdsForUser(userId, (err, sessionIds) => {
    if (err) return console.error('could not get session ids for', userId, err)
    for (const sessionId of sessionIds) {
      sendToSession(sessionId, channel, method, payload)
    }
  })
}

const sendToSession = (sessionId, channel, method, payload) => {
  for (const connection of connectionsForSession(sessionId)) {
    if (channel != null) {
      connection.emit(channel, { m: method, o: payload })
    }
  }
}

const loadSessionIdsScript = callback => {
  readFile(sessionIdsScriptFilename, 'utf8', (err, contents) => {
    if (err) return callback(err)
    redisClient.script('LOAD', contents, (err, sha) => {
      if (err) return callback(err)
      console.log('loaded session ids script', sha)
      sessionIdsScriptSHA = sha
      callback(null, sha)
    })
  })
}

const getSessionIdsScriptSHA = callback => {
  if (sessionIdsScriptSHA) {
    callback(null, sessionIdsScriptSHA)
  } else {
    loadSessionIdsScript(err => {
      callback(err, sessionIdsScriptSHA)
    })
  }
}

const fetchSessionIdsForUser = (userId, callback) => {
  getSessionIdsScriptSHA((err, sha) => {
    if (err) return callback(err)
    redisClient.evalsha(sha, 0, userId, (err, res) => {
      if (err && err.code === 'NOSCRIPT') {
        sessionIdsScriptSHA = null
        loadSessionIdsScript(err => {
          if (err) return callback(err)
          fetchSessionIdsForUser(userId, callback)
        })
      } else {
        callback(err, res)
      }
    })
  })
}

const connectionsForSession = (sessionId) => {
  if (connectedClients[sessionId]) {
    return connectedClients[sessionId]
  } else {
    return []
  }
}

const parseRequestURL = (req) => {
  // url.parse is being deprecated, but URL does not accept relative URLS so we specify a dummy base
  // see https://github.com/nodejs/node/issues/12682
  return new URL(req.url, 'http://localhost')
}

/**
 * API of this server
 * This server supports GET-Requests on
 * - /
 *  Sends a message to clients.
 *  arguments:
 *    c: int (optional) - the session id the message should be sent to
 *    u: int (optional) - the foodsaver id the message should be sent to
 *    us: comma-separated list of ints (optional) - the foodsaver ids the message should be sent to if it should be sent to multiple foodsavers
 *    Either one of c, u or us is required.
 *    a: string (required) - the frontend component the message should be sent to, e. g. 'conv' or 'bell'
 *    o: json-encoded array (required) - options for the frontend component that should receive the message
 *
 *  - /stats
 *    Will return a JSON object containing the number of connections, the number of registrations and the number of sessions,
 *    e. g. {connections: 24, registrations: 42, sessions: 34}
 *
 *  - /is-connected
 *    Returns "true" if the specified foodsaver has an open connection to the websocket and "false" if not.
 *    arguments:
 *      u: int (required) - foodsaver id
 */
const inputServer = http.createServer((req, res) => {
  const url = parseRequestURL(req)
  if (url.pathname === '/stats') {
    res.writeHead(200)
    res.end(JSON.stringify({
      connections: numConnections,
      registrations: numRegistrations,
      sessions: Object.keys(connectedClients).length
    }))
    return
  }

  if (url.pathname === '/is-connected') {
    const userId = url.searchParams.get('u')
    if (userId === null) {
      res.writeHead(400)
      res.end('Parameter u must be specified and be a foodsaver id.')
      return
    }

    fetchSessionIdsForUser(userId, (err, sessionIds) => {
      if (err) {
        res.writeHead(500)
        res.end('Error matching the user id to a session.')
        return
      }

      res.writeHead(200)
      for (const sessionId of sessionIds) {
        if (sessionId in connectedClients) {
          res.end('true') // there is at least one session for userId
          return
        }
      }
      res.end('false') // there's no session for userId
    })
    return // avoid res.end() to be called later on in this function before the callback resolves
  }

  const sessionId = url.searchParams.get('c')
  const app = url.searchParams.get('a')
  if (app === null) {
    res.writeHead(400)
    res.end('Parameter a must be specified.')
    return
  }
  const method = url.searchParams.get('m')
  const options = url.searchParams.get('o')
  const userId = url.searchParams.get('u')
  const userIds = url.searchParams.get('us')

  if (sessionId) {
    sendToSession(sessionId, app, method, options)
  }

  if (userId) {
    sendToUser(userId, app, method, options)
  }

  if (userIds) {
    for (const userId of userIds.split(',')) {
      sendToUser(userId, app, method, options)
    }
  }

  res.writeHead(200)
  res.end('\n')
})

const chatServer = http.createServer((req, res) => {
  res.writeHead(200)
  res.end('\n')
})
const io = connectSocketIO(chatServer)

io.use((socket, next) => {
  const cookieVal = socket.request.headers.cookie
  if (cookieVal) {
    const cookie = parseCookie(cookieVal)
    socket.sid = cookie.PHPSESSID || cookie.sessionid
    if (socket.sid) return next()
  }
  next(new Error('not authorized'))
})

io.on('connection', (socket) => {
  const sessionId = socket.sid
  numConnections++
  socket.on('register', () => {
    numRegistrations++
    if (!connectedClients[sessionId]) connectedClients[sessionId] = []
    connectedClients[sessionId].push(socket)
  })

  socket.on('disconnect', () => {
    numConnections--
    const connections = connectedClients[sessionId]
    if (sessionId && connections) {
      if (connections.includes(socket)) {
        connections.splice(connections.indexOf(socket), 1)
        numRegistrations--
      }
      if (connections.length === 0) {
        delete connectedClients[sessionId]
      }
    }
  })
})

loadSessionIdsScript((err, sha) => {
  if (err) return console.error('failed to load session ids script', err)
})

inputServer.listen(inputPort, listenHost, () => {
  console.log('http server started on', `${listenHost}:${inputPort}`)
})

chatServer.listen(chatPort, listenHost, () => {
  console.log('socket.io started on port', `${listenHost}:${chatPort}`)
})

module.exports = {
  inputServer: inputServer,
  chatServer: chatServer
}
