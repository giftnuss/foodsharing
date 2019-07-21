<template>
  <div
    class="bootstrap"
  >
    <div
      class="card rounded"
    >
      <div class="card-header text-white bg-primary">
        <div class="row align-items-center">
          <div class="col text-truncate font-weight-bold">
            {{ $i18n('pickup.dates') }}
          </div>
          <div class="col col-5 text-right">
            <div
              class="btn-group"
              role="group"
            >
              <button
                v-if="isCoordinator"
                v-b-tooltip
                @click="loadEditRecurringPickupModal"
                :title="$i18n('pickup.edit_recurring_pickups')"
                class="btn btn-secondary btn-sm"
              >
                <i class="fa fa-pen" />
              </button>
              <button
                v-if="isCoordinator"
                v-b-tooltip
                @click="loadAddPickupModal"
                :title="$i18n('pickup.add_onetime_pickup')"
                class="btn btn-secondary btn-sm"
              >
                <i class="fa fa-plus" />
              </button>
            </div>
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
import { VBTooltip } from 'bootstrap-vue'
import Pickup from './Pickup'
import { setPickupSlots, confirmPickup, joinPickup, leavePickup, listPickups } from '@/api/stores'
import { sendMessage } from '@/api/conversations'
import { user } from '@/server-data'
import { ajreq, pulseError, pulseSuccess } from '@/script'
import $ from 'jquery'

export default {
  components: { Pickup },
  directives: { VBTooltip },
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
        pulseError('Das Eintragen hat leider nicht funktioniert. Dies liegt vermutlich daran, dass jemand anderes schneller war.<br /><br />Versuche es nach einem Neuladen einfach noch mal!')
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
      try {
        await sendMessage(this.teamConversationId, msg)
        pulseSuccess(this.$i18n('pickup.team_message_success'))
      } catch (e) {
        console.error(e)
        pulseError('Error while sending message')
      }
    },
    loadAddPickupModal () {
      ajreq(
        'adddate',
        {
          app: 'betrieb',
          id: this.storeId
        }
      )
    },
    loadEditRecurringPickupModal () {
      $('#bid').val(this.storeId)
      $('#dialog_abholen').dialog('open')
    }
  }
}
</script>

<style scoped>

</style>
