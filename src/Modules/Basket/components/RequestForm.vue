<template>
  <div
    :class="{disabledLoading: isLoading}"
  >
    <div class="ui-padding-bottom">
      <h5>{{ $i18n('contact_info') }}:</h5>
      <span
        v-if="mobileNumber"
      >
        {{ $i18n('handy') }}: <a v-bind:href="'tel:' + mobileNumber">{{ mobileNumber }}</a>
      </span>
      <span
        v-if="landlineNumber"
      >
        {{ $i18n('tel') }}: {{ landlineNumber }}
      </span>
    </div>
    <div
      v-if="allowRequestByMessage"
    >
      <div
        v-if="hasRequested"
        class="ui-padding-bottom"
      >
        <a
          @click="openChat"
          class="button button-big"
          href="#"
        >
          {{ $i18n('open_chat') }}
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
          {{ $i18n('withdraw_request') }}
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
      <div>
        <span v-if="requestCount == 0">
          {{ $i18n('no_requests') }}
        </span>
        <span
          v-if="requestCount > 0"
          v-html="$i18n('n_requests', { count: requestCount })"
        />
      </div>
    </div>
    <b-modal
      ref="modal_request"
      :title="$i18n('basket_request')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('send_request')"
      @ok="request(requestMessage)"
      modal-class="bootstrap"
    >
      <b-form-textarea
        id="contactmessage"
        v-model="requestMessage"
        rows="4"
      />
    </b-modal>
  </div>
</template>

<script>

import { BFormTextarea, BModal } from 'bootstrap-vue'

import { requestBasket, withdrawBasketRequest } from '@/api/baskets'
import { pulseSuccess, pulseError } from '@/script'
import i18n from '@/i18n'
import conv from '@/conv'

export default {
  components: { BFormTextarea, BModal },
  props: {
    basketId: {
      type: Number,
      default: null
    },
    basketCreatorId: {
      type: Number,
      default: null
    },
    initialHasRequested: {
      type: Boolean,
      default: false
    },
    initialRequestCount: {
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
    },
    allowRequestByMessage: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      isLoading: false,
      requestMessage: '',
      hasRequested: this.initialHasRequested,
      requestCount: this.initialRequestCount
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
        pulseSuccess(i18n('sent_request'))
      } catch (e) {
        if (e.code === 400) {
          pulseError(i18n('request_empty_message_error'))
        } else if (e.code === 403) {
          pulseError(i18n('request_denied_error'))
        } else if (e.code === 404) {
          pulseError(i18n('request_basket_404_error'))
        } else {
          pulseError('Request basket failed: ' + e)
        }
      }
      this.isLoading = false
    },
    async withdraw () {
      this.isLoading = true
      try {
        var response = await withdrawBasketRequest(this.basketId)
        this.requestCount = response.basket.requestCount
        this.hasRequested = false
        pulseSuccess(i18n('withdrawed_request'))
      } catch (e) {
        if (e.code === 404) {
          pulseError(i18n('request_basket_404_error'))
        } else {
          pulseError('Withdrawing basket request failed: ' + e)
        }
      }
      this.isLoading = false
    },
    openChat () {
      conv.userChat(this.basketCreatorId)
    }
  }
}
</script>

<style scoped>

</style>
