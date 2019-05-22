<template>
  <div
    class="bootstrap"
  >
    <div
      class="card rounded"
    >
      <div class="card-header text-white bg-primary">
        <div class="row">
          <div class="col text-truncate ml-2 pt-1 font-weight-bold">
            Nächste Abholtermine
          </div>
          <div class="col col-2 p-0">
            <button
              v-if="isCoordinator"
              v-b-tooltip
              @click="loadAddPickupModal"
              class="btn btn-secondary btn-sm"
              title="Zusatztermin eintragen"
            >
              <i class="fa fa-plus" />
            </button>
          </div>
        </div>
      </div>
      <div
        :class="{disabledLoading: isLoading}"
        class="card-body"
      >
        <template v-for="pickup in pickups">
          <Pickup
            v-bind="pickup"
            :key="pickup.date.valueOf()"
            :store-id="storeId"
            :is-coordinator="isCoordinator"
            :user="user"
            @leave="leave"
            @kick="kick"
            @join="join"
            @confirm="confirm"
            @delete="setSlots(pickup.date, 0)"
            @add-slot="setSlots(pickup.date, pickup.totalSlots + 1)"
            @remove-slot="setSlots(pickup.date, pickup.totalSlots - 1)"
            @team-message="sendTeamMessage"
            class="mb-2"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import bTooltip from '@b/directives/tooltip/tooltip'
import Pickup from './Pickup'
import { setPickupSlots, confirmPickup, joinPickup, leavePickup, listPickups } from '@/api/stores'
import { user } from '@/server-data'
import { ajreq, pulseError } from '@/script'

export default {
  components: { Pickup },
  directives: { bTooltip },
  props: {
    storeId: {
      type: Number,
      default: null
    },
    isCoordinator: {
      type: Boolean,
      default: false
    },
    teamConversationId: {
      type: Number,
      default: null
    }
  },
  data () {
    return {
      pickups: [],
      isLoading: false,
      user: user
    }
  },
  _interval: null,
  async created () {
    await this.reload()

    // pull for updates every 30 seconds
    this._interval = setInterval(() => {
      this.reload(true) // reload without loading indicator
    }, 30 * 1000)
  },
  destroyed () {
    clearInterval(this._interval)
  },
  methods: {
    async reload (silent = false) {
      if (!silent) this.isLoading = true
      try {
        this.pickups = await listPickups(this.storeId)
      } catch (e) {
        pulseError('failed loading pickup list ' + e)
      }

      if (!silent) this.isLoading = false
    },
    async join (date) {
      this.isLoading = true
      try {
        await joinPickup(this.storeId, date, this.user.id)
      } catch (e) {
        console.error(e)
        pulseError('Das Eintragen hat leider nicht funktioniert. Dies liegt vermutlich daran, dass jemensch anderes wohl schneller war.<br /><br />Versuche es ansonsten einfach noch mal!')
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
    async sendTeamMessage (msg) {
      pulseError('Message sending not implemented!')
      console.error('Not implemented yet, will be done in separate MR')
      console.log('would send message', msg)
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
