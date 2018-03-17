import io from 'socket.io-client'

import { info, session_id, GET } from '@/script'

export default {
  connect: function () {
    var socket = io.connect(window.location.host, {path: '/chat/socket.io'})
    socket.on('connect', function () {
      console.log('connected')
      socket.emit('register', session_id())
    })

    socket.on('conv', function (data) {
      switch (data.m) {
        case 'push':
          if (GET('page') === 'msg') {
            // TODO: ?? what is msg and conv??
            msg.push(JSON.parse(data.o))
          } else {
            conv.push(JSON.parse(data.o))
          }
          break
      }
    })

    socket.on('info', function (data) {
      switch (data.m) {
        case 'badge':
          info.badge('info', data.o.count)
          break
      }
    })
  }
}
