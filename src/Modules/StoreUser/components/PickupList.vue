<template>
  <div
    class="container bootstrap"
  >
    <div
      class="card rounded"
    >
      <div class="card-header text-white bg-primary">
        <div class="row">
          <div class="col text-truncate ml-2 pt-1 font-weight-bold">
            Nächste Abholtermine
          </div>
        </div>
      </div>
      <div
        :class="{disabledLoading: isLoading}"
        class="card-body text-center"
      >
        <b-btn
          v-if="isCoordinator"
          @click="loadAddPickupModal"
          class="m-2"
        >
          Zusatztermin eintragen
        </b-btn>
        <template v-for="pickup in pickups">
          <Pickup
            v-bind="pickup"
            :store-id="storeId"
            :is-coordinator="isCoordinator"
            :user="user"
            @leave="leave"
            @kick="kick"
            @join="join"
            @confirm="confirm"
            @add-slot="setSlots(pickup.date, pickup.totalSlots + 1)"
            @remove-slot="setSlots(pickup.date, pickup.totalSlots - 1)"
            class="mb-2"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import Pickup from './Pickup'
import { setPickupSlots, confirmPickup, joinPickup, leavePickup, listPickups } from '@/api/stores'
import { user } from '@/server-data'
import { ajreq, pulseError } from '@/script'
import bBtn from '@b/components/button/button'

export default {
  components: { bBtn, Pickup },
  props: {
    storeId: {
      type: Number,
      default: null
    },
    isCoordinator: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      pickups: [],
      isLoading: false,
      user: user
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
    },
    async join (date) {
      this.isLoading = true
      try {
        await joinPickup(this.storeId, date, this.user.id)
      } catch (e) {
        pulseError('join failed: ' + e)
      }
      this.reload()
    },
    async leave (date) {
      this.isLoading = true
      try {
        await leavePickup(this.storeId, date, this.user.id)
      } catch (e) {
        pulseError('leave failed: ' + e)
      }
      this.reload()
    },
    async kick (data) {
      this.isLoading = true
      try {
        await leavePickup(this.storeId, data.date, data.fsId)
      } catch (e) {
        pulseError('kick failed: ' + e)
      }
      this.reload()
    },
    async confirm (data) {
      this.isLoading = true
      try {
        await confirmPickup(this.storeId, data.date, data.fsId)
      } catch (e) {
        pulseError('confirm failed: ' + e)
      }
      this.reload()
    },
    async setSlots (date, totalSlots) {
      this.isLoading = true
      try {
        await setPickupSlots(this.storeId, date, totalSlots)
      } catch (e) {
        pulseError('change slot count failed: ' + e)
      }
      this.reload()
    },
    loadAddPickupModal () {
      ajreq(
        'adddate',
        {
          app: 'betrieb',
          id: this.storeId
        }
      )
    }
  }
}
</script>

<style scoped>

</style>
