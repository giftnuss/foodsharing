import Vue from 'vue'
import { getConversationList, getConversation, getMessages } from '@/api/conversations'
import ProfileStore from '@/stores/profiles'

export default new Vue({
  data: {
    conversations: {}
  },
  computed: {
    unreadCount () {
      return Array.from(this.conversations).filter(b => b.hasUnreadMessages).length
    }
  },
  methods: {
    async loadConversations (limit = 10) {
      const res = await getConversationList(limit)
      ProfileStore.updateFrom(res.profiles)
      for (const conversation of res.conversations) {
        const c = this.conversations[conversation.id] ?? { messages: {} }
        Vue.set(this.conversations, conversation.id, {
          ...conversation,
          messages: Object.assign({}, c.messages),
          lastMessage: convertMessage(conversation.lastMessage)
        })
      }
    },
    async loadConversation (id) {
      const c = this.conversations[id] ?? { messages: {} }
      if (Object.keys(c.messages).length === 0) {
        /* only load conversation when not already there */
        const res = await getConversation(id)
        ProfileStore.updateFrom(res.profiles)
        for (const message of res.conversation.messages) {
          c.messages[message.id] = convertMessage(message)
        }
        Vue.set(this.conversations, id, {
          ...res.conversation,
          messages: c.messages,
          lastMessage: convertMessage(res.conversation.lastMessage)
        })
      }
    },
    async loadMoreMessages (cid) {
      const c = this.conversations[cid]
      const res = await getMessages(cid, Object.keys(c.messages)[0])
      ProfileStore.updateFrom(res.profiles)
      for (const message of res.messages) {
        c.messages[message.id] = convertMessage(message)
      }
      return res.messages.length
    },
    async updateFromPush (data) {
      const cid = data.cid
      if (!(cid in this.conversations)) {
        await this.loadConversation(cid)
      }
      const message = convertMessage(data.message)
      Vue.set(this.conversations[cid].messages, message.id, message)
      Vue.set(this.conversations[cid], 'lastMessage', message)
    }
  }
})

export function convertMessage (val) {
  if (val !== null) {
    return {
      ...val,
      sentAt: new Date(val.sentAt)
    }
  } else {
    return null
  }
}

export function convertProfile (val) {
  if (Array.isArray(val)) {
    return val.map(convertProfile)
  } else {
    return {
      ...val,
      avatar: val.avatar ? `/image/mini_q_${val.avatar}` : null
    }
  }
}
