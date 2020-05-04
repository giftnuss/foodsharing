import io from 'socket.io-client'

// eslint-disable-next-line camelcase
import { session_id, GET } from '@/script'

import msg from '@/msg'
import conv from '@/conv'
import bellsStore from '@/stores/bells'
import conversationStore, { convertMessage } from '@/stores/conversations'

export default {
  connect: function () {
    var socket = io.connect(window.location.host, { path: '/chat/socket.io' })
    socket.on('connect', function () {
      console.log('WebSocket connected.')
      socket.emit('register', session_id())

      document.addEventListener('visibilitychange', () => {
        socket.emit('visibilitychange', document.hidden) // send tab/window visibility change to socket server, so it can use it to determine if the user is online or not
      })
    })

    socket.on('conv', async function (data) {
      if (data.m === 'push') {
        const obj = data.o
        /* temporary workaround to at least do something with messages retrieved from production */
        if (!obj.message) {
          const message = {
            id: obj.id,
            authorId: obj.fs_id,
            body: obj.body,
            sentAt: obj.time
          }
          obj.message = message
        }
        const message = convertMessage(obj.message)
        obj.message = message
        await conversationStore.updateFromPush(obj)
        if (GET('page') === 'msg') {
          msg.push(obj)
        } else {
          conv.push(obj)
        }
      }
    })

    socket.on('info', function (data) {
      if (data.m === 'badge') {
        // info.badge('info', data.o.count)
      }
    })

    socket.on('bell', function (data) {
      if (data.m === 'update') {
        bellsStore.loadBells()
      }
    })
  }
}
