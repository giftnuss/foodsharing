import Vue from 'vue'
import dateFnsParseISO from 'date-fns/parseISO'
import { getConversationList, getConversation } from '@/api/conversations'
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
        Vue.set(this.conversations, conversation.id, {
          ...conversation,
          lastMessage: convertMessage(conversation.lastMessage)
        })
      }
    },
    async loadConversation (id) {
      if (this.conversations[id] === undefined || this.conversations[id].messages === undefined) {
        /* only load conversation when not already there */
        const res = await getConversation(id)
        ProfileStore.updateFrom(res.profiles)
        Vue.set(this.conversations, id, {
          ...res.conversation,
          messages: convertMessage(res.conversation.messages)
        })
      }
    }
  }
})

export function convertMessage (val) {
  if (Array.isArray(val)) {
    return val.map(convertMessage)
  } else {
    return {
      ...val,
      sentAt: new Date(val.sentAt)
    }
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
