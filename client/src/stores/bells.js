import Vue from 'vue'
import {getBellList, deleteBell, markBellAsRead} from '@/api/bells'

export default new Vue({
  data: {
    bells: []
  },
  computed: {
    unreadCount () {
      return this.bells.filter(b => !b.isRead).length
    }
  },
  methods: {
    async loadBells () {
      this.bells = await getBellList()
    },
    async delete (id) {
      let bell = this.bells.find(b => b.id === id)
      this.$set(bell, 'isDeleting', true)
      try {
        await deleteBell(id)
        this.bells.splice(this.bells.indexOf(bell), 1)
      } catch (err) {
        this.$set(bell, 'isDeleting', false)
        throw err
      }
    },
      async markAsRead(id) {
          let bell = this.bells.find(b => b.id === id)
          bell.isRead = true;
          await markBellAsRead(id)
      }
  }
})
