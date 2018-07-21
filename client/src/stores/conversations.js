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
    async loadConversations () {
      this.conversations = await getConversationList()
    }
  }
})
