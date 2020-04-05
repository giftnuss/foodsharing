<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <label>{{ $i18n('register.login_mobile_phone') }}</label>
    </div>
    <div class="col-sm-auto">
      <vue-tel-input
        :value="mobile"
        v-bind="telInputProps"
        :class="{ 'is-invalid': !isValid }"
        @input="update"
        @validate="validate"
      />
    </div>
    <div
      v-if="!isValid"
      class="col-sm-auto invalid-feedback"
    >
      <span>{{ $i18n('register.phone_not_valid') }}</span>
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
        @click.prevent="redirect()"
      >
        {{ $i18n('register.next') }}
      </button>
    </div>
  </form>
</template>
<script>
import { VueTelInput } from 'vue-tel-input'
import i18n from '@/i18n'

export default {
  components: {
    VueTelInput
  },
  props: { mobile: { type: String, default: null } },
  data () {
    return {
      phoneNumberValid: false,
      telInputProps: {
        mode: 'international',
        defaultCountry: 'DE',
        disabledFetchingCountry: true,
        placeholder: i18n('register.phone_example'),
        preferredCountries: ['DE', 'AT', 'CH'],
        name: 'mobilephone',
        maxLen: 18,
        validCharactersOnly: true
      }
    }
  },
  computed: {
    isValid () {
      return this.phoneNumberValid || this.mobile === null || this.mobile === ''
    }
  },
  methods: {
    update (phoneNumber, phoneObject) {
      this.phoneNumberValid = phoneObject.isValid
      this.$emit('update:mobile', phoneNumber)
    },
    validate (phoneObject) {
      this.phoneNumberValid = phoneObject.isValid
    },
    redirect () {
      if (this.isValid) {
        this.$emit('next')
      }
    }
  }
}
</script>
<style lang="scss" scoped>
.is-invalid {
    outline: red auto 1px;
}
.invalid-feedback {
  font-size: 100%;
  display: unset;
}
</style>
