<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <label for="mobile">{{ $i18n('register.login_mobile_phone') }}</label>
    </div>
    <div class="col-sm-auto">
      <VuePhoneNumberInput
        :value="computedMobile"
        :preferred-countries="telCountries"
        :show-code-on-list="true"
        :translations="{
          countrySelectorLabel: 'Ländercode',
          countrySelectorError: 'Wähle einen Ländercode',
          phoneNumberLabel: 'Handynummer',
          example: 'Beispiel :'
        }"
        @update="$emit('update:mobile', $event.formattedNumber ? $event.formattedNumber : $event.phoneNumber)"
      />
    </div>
    <div class="mt-3 col-sm-auto">
      <div class="msg-inside info">
        <i class="fas fa-info-circle" /> {{ $i18n('register.login_phone_info') }}
      </div>
    </div>
    <div class="col-sm-auto">
      <button
        class="btn btn-secondary mt-3"
        type="button"
        @click="$emit('prev')"
      >
        {{ $i18n('register.prev') }}
      </button>
      <button
        class="btn btn-secondary mt-3"
        type="submit"
        @click.prevent="$emit('next')"
      >
        {{ $i18n('register.next') }}
      </button>
    </div>
  </form>
</template>
<script>
import VuePhoneNumberInput from 'vue-phone-number-input'
import 'vue-phone-number-input/dist/vue-phone-number-input.css'

export default {
  components: {
    VuePhoneNumberInput
  },
  props: { mobile: { type: String, default: null } },
  data () {
    return {
      telCountries: ['DE', 'AT', 'CH']
    }
  },
  computed: {
    computedMobile: function () {
      // cut of the country code if it is already added.
      return this.mobile && this.mobile.startsWith('+') ? this.mobile.slice(3) : this.mobile
    }
  }
}
</script>
