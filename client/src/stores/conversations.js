import Vue from 'vue'
import { getConversationList } from '@/api/conversations'

export default new Vue({
  data: {
    conversations: []
  },
  computed: {
    unreadCount () {
      return this.conversations.filter(b => b.hasUnreadMessages).length
    }
  },
  methods: {
    async loadConversations (limit = 10) {
      const res = await getConversationList(limit)
      this.conversations = res.conversations.map(c => ({
        ...c,
        lastMessage: convertMessage(c.lastMessage),
        members: convertProfile(c.members)
      }))
    }
  }
})

export function convertMessage (val) {
  return {
    ...val,
    sentAt: new Date(val.sentAt)
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
