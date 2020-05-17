import Vue from 'vue'
import { listStoresForCurrentUser } from '@/api/stores'

export default new Vue({
  data: {
    stores: null
  },
  methods: {
    async loadStores () {
      this.stores = await listStoresForCurrentUser()
    }
  }
})
