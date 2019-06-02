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
      this.conversations = res.data.map(c => ({
        id: c.id,
        title: c.name,
        lastMessageTime: new Date(c.last_message_at),
        members: c.members.length ? c.members.map((m) => ({
          id: m.id,
          name: m.name,
          avatar: m.photo ? `/images/mini_q_${m.photo}` : null
        })) : [],
        lastMessage: {
          bodyRaw: c.last_message,
          authorId: c.last_message_author_id
        },
        hasUnreadMessages: c.has_unread_messages
      }))
    }
  }
})
