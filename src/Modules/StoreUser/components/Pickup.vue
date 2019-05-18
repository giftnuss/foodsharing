<template>
  <div>
    <b-card
      :title="date | dateFormat('full-long')"
      title-tag="span"
    >
      <b-card-text>
        <b-spinner v-if="loading" />
        <template
          v-for="slot in occupiedSlots"
        >
          <TakenPickup
            :profile="slot.profile"
            :confirmed="slot.confirmed"
            :date="date"
            :allow-leave="slot.profile.id == user.id"
            :allow-kick="storeCoordinator"
          />
        </template>
        <span
          v-for="n in totalSlots-occupiedSlots.length"
          :key="`${date} free ${n}`"
        >
          <b-img
            v-on:click="showSignupModal = true"
            alt="empty"
            src="/img/nobody.gif"
          />
        </span>
      </b-card-text>
    </b-card>
    <b-modal
      v-model="showSignupModal"
      @ok="signup"
      title="Really sign up?"
      ok-variant="secondary"
    >
      Are you sure you want to sign in for the pickup at <b>{{ date | dateFormat('full-long') }}</b>?
      <template slot="modal-cancel">
        Doch nicht eintragen
      </template>
    </b-modal>
  </div>
</template>

<script>
import bCard from '@b/components/card/card'
import bCardText from '@b/components/card/card-text'
import bImg from '@b/components/image/img'
import bModal from '@b/components/modal/modal'
import bSpinner from '@b/components/spinner/spinner'
import moment from 'moment'
import { signup } from '@/api/stores'
import { pulseError } from '@/script'
import TakenPickup from '@php/Modules/StoreUser/components/TakenPickup'
import { user } from '@/server-data'

export default {
  components: { TakenPickup, bCard, bCardText, bImg, bModal, bSpinner },
  props: {
    storeId: {
      type: Number,
      default: null
    },
    date: {
      type: Date,
      default: null
    },
    isAvailable: {
      type: Boolean,
      default: false
    },
    totalSlots: {
      type: Number,
      default: 0
    },
    occupiedSlots: {
      type: Array,
      default: () => []
    },
    storeCoordinator: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      loading: false,
      showSignupModal: false,
      user: user
    }
  },
  methods: {
    async signup () {
      this.loading = true
      try {
        await signup(this.storeId, moment(this.date).format())
      } catch (e) {
        pulseError('Signup failed: ' + e)
      }

      this.loading = false
    }
  }
}
</script>

<style scoped>

</style>
