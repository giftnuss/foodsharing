import Vue from 'vue'
import moment from 'moment'
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
      this.conversations = res.map(c => ({
        id: parseInt(c.id),
        title: c.name,
        lastMessageTime: moment(c.last).toDate(),
        members: c.member.length ? c.member.map((m) => ({
          id: parseInt(m.id, 10),
          name: m.name,
          avatar: m.photo ? `/images/mini_q_${m.photo}` : null
        })) : [],
        lastMessage: {
          bodyRaw: c.last_message,
          authorId: c.last_foodsaver_id
        },
        hasUnreadMessages: c.unread === '1'
      }))
    }
  }
})
