import Vue from 'vue'
import { getBaskets } from '@/api/baskets'
import { expose } from '@/utils'

const basketStore = new Vue({
  data: {
    baskets: []
  },
  methods: {
    async loadBaskets () {
      this.baskets = await getBaskets()
    }
  }
})

expose({
  basketStore
})

export default basketStore
