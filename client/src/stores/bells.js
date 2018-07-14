import Vue from 'vue'
import { getBellList, deleteBell } from '@/api/bells'

export default new Vue({
  data: {
    bells: []
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
    }
  }
})
