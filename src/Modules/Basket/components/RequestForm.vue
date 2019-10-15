<template>
  <div
    :class="{disabledLoading: isLoading}"
  >
    <div>
      <p
        v-if="mobileNumber"
      >
        {{ $i18n('handy') }}: <a v-bind:href="'tel:' + mobileNumber">{{ mobileNumber }}</a>
      </p>
      <p
        v-if="landlineNumber"
      >
        {{ $i18n('tel') }}: {{ landlineNumber }}
      </p>
    </div>
    <div>
      <div
        v-if="hasRequested"
        class="ui-padding-bottom"
      >
        <a
          class="button button-big"
          href="#"
        >
          Nachricht schreiben
        </a>
      </div>
      <div
        v-if="hasRequested"
        class="ui-padding-bottom"
      >
        <a
          @click="withdraw"
          class="button button-big"
          href="#"
        >
          Anfrage zurückziehen
        </a>
      </div>
      <div
        v-if="!hasRequested"
        class="ui-padding-bottom"
      >
        <a
          @click="$refs.modal_request.show()"
          class="button button-big"
          href="#"
        >
          {{ $i18n('basket_request') }}
        </a>
      </div>
    </div>
    <div>
      <p>
        Bereits <strong>{{ requestCount }}</strong> Anfragen
      </p>
    </div>
    <b-modal
      ref="modal_request"
      :title="'Essenskorb anfragen'"
      :cancel-title="'Abbrechen'"
      :ok-title="'Anfragen'"
      @ok="request(requestMessage)"
      modal-class="bootstrap"
    >
      <b-form-textarea
        v-model="requestMessage"
        rows="4"
      />
    </b-modal>
  </div>
</template>

<script>

import { requestBasket, withdrawBasketRequest } from '@/api/baskets'
import { pulseError } from '@/script'

export default {
  props: {
    basketId: {
      type: Number,
      default: null
    },
    hasRequested: {
      type: Boolean,
      default: false
    },
    requestCount: {
      type: Number,
      default: null
    },
    mobileNumber: {
      type: String,
      default: null
    },
    landlineNumber: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      isLoading: false,
      requestMessage: ''
    }
  },
  _interval: null,
  async created () {
  },
  destroyed () {
  },
  methods: {
    async request (message) {
      this.isLoading = true
      try {
        var response = await requestBasket(this.basketId, message)
        this.requestCount = response.basket.requestCount
        this.hasRequested = true
      } catch (e) {
        pulseError('Request basket failed: ' + e)
      }
      this.isLoading = false
    },
    async withdraw () {
      this.isLoading = true
      try {
        var response = await withdrawBasketRequest(this.basketId)
        this.requestCount = response.basket.requestCount
        this.hasRequested = false
      } catch (e) {
        pulseError('Withdrawing basket request failed: ' + e)
      }
      this.isLoading = false
    }
  }
}
</script>

<style scoped>

</style>
