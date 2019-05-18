<template>
  <div
    :class="{disabledLoading: isLoading}"
    class="container bootstrap"
  >
    <div
      v-if="!isLoading"
      class="card rounded"
    >
      <div class="card-header text-white bg-primary">
        <div class="row">
          <div class="col text-truncate ml-2 pt-1 font-weight-bold">
            Nächste Abholtermine
          </div>
        </div>
      </div>
      <div class="card-body">
        <template v-for="pickup in pickups">
          <Pickup
            v-bind="pickup"
            :store-id="storeId"
            :store-coordinator="storeCoordinator"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import Pickup from './Pickup'
import { listPickups } from '@/api/stores'

export default {
  components: { Pickup },
  props: {
    storeId: {
      type: Number,
      default: null
    },
    storeCoordinator: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      pickups: [],

      isLoading: false
    }
  },
  async created () {
    await this.reload()
  },
  methods: {
    async reload () {
      this.isLoading = true
      this.pickups = await listPickups(this.storeId)
      this.isLoading = false
    }
  }
}
</script>

<style scoped>

</style>
