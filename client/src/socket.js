import io from 'socket.io-client'

// eslint-disable-next-line camelcase
import { session_id, GET } from '@/script'

import msg from '@/msg'
import conv from '@/conv'
import bellsStore from '@/stores/bells'

export default {
  connect: function () {
    var socket = io.connect(window.location.host, { path: '/chat/socket.io' })
    socket.on('connect', function () {
      console.log('WebSocket connected.')
      socket.emit('register', session_id())
    })

    socket.on('conv', function (data) {
      if (data.m === 'push') {
        if (GET('page') === 'msg') {
          msg.push(JSON.parse(data.o))
        } else {
          conv.push(JSON.parse(data.o))
        }
      }
    })

    socket.on('info', function (data) {
      if (data.m === 'badge') {
        // info.badge('info', data.o.count)
      }
    })

    socket.on('bell', function (data) {
      if (data.m === 'notify') {
        bellsStore.loadBells()
      }
    })
  }
}
