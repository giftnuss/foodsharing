import Vue from 'vue'
import { getConversationList } from '@/api/conversations'

export default new Vue({
  data: {
    conversations: []
  },
  methods: {
    async loadConversations () {
      this.conversations = await getConversationList()
    }
  }
})
