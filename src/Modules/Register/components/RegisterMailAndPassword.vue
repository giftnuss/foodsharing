<template>
  <form class="my-1">
    <div class="col-sm-auto">
      <div class="msg-inside info">
        <i class="fas fa-info-circle" />
        {{ $i18n('register.mail_hint') }}
      </div>
      <label for="email">{{ $i18n('register.login_email') }}</label>
      <sup>
        <i class="fas fa-asterisk" />
      </sup>
    </div>
    <div class="col-sm-auto">
      <input
        id="email"
        :value="email"
        :class="{ 'is-invalid': $v.email.$error }"
        type="email"
        name="email"
        class="form-control"
        @blur="update"
      >
      <div
        v-if="$v.email.$error || !isMailValidForRegistration || isMailInvalid"
        class="invalid-feedback"
      >
        <span v-if="!$v.email.required">{{ $i18n('register.email_required') }}</span>
        <span v-else-if="!$v.email.email || !$v.email.foodsharing || isMailInvalid">{{ $i18n('register.email_invalid') }}</span>
        <span v-else-if="!isMailValidForRegistration">{{ $i18n('register.error_email_exist') }}</span>
      </div>
    </div>
    <div class="my-2">
      <div class="col-sm-auto">
        <label for="password">
          {{ $i18n('register.login_passwd1') }}
          <sup>
            <i class="fas fa-asterisk" />
          </sup>
        </label>
      </div>
      <div class="col-sm-auto">
        <input
          id="password"
          v-model.lazy="$v.password.$model"
          :class="{ 'is-invalid': $v.password.$error }"
          type="password"
          name="password"
          class="form-control"
          @input="$emit('update:password', $event.target.value)"
        >
        <div
          v-if="$v.password.$error"
          class="invalid-feedback"
        >
          <span v-if="!$v.password.required">{{ $i18n('register.password_required') }}</span>
          <span v-if="!$v.password.minLength">{{ $i18n('register.password_minLength') }}</span>
        </div>
      </div>
      <div class="my-1">
        <div class="col-sm-auto">
          <label for="confirmPassword">
            {{ $i18n('register.login_passwd2') }}
            <sup>
              <i class="fas fa-asterisk" />
            </sup>
          </label>
        </div>
        <div class="col-sm-auto">
          <input
            id="confirmPassword"
            v-model.lazy="$v.confirmPassword.$model"
            :class="{ 'is-invalid': $v.confirmPassword.$error }"
            type="password"
            name="confirmPassword"
            class="form-control"
          >
          <div
            v-if="$v.confirmPassword.$error"
            class="invalid-feedback"
          >
            <span
              v-if="!$v.confirmPassword.required"
            >{{ $i18n('register.confirmPassword_required') }}</span>
            <span
              v-else-if="!$v.confirmPassword.sameAsPassword"
            >{{ $i18n('register.confirmPassword_sameAsPassword') }}</span>
          </div>
        </div>
        <button
          class="btn btn-secondary ml-3 mt-3"
          type="submit"
          @click.prevent="redirect()"
        >
          {{ $i18n('register.next') }}
        </button>
        <span class="mr-3 d-flex flex-row-reverse">
          {{ $i18n('register.requiredFields') }}
          <sup>
            <i class="fas fa-asterisk" />
          </sup>
        </span>
      </div>
    </div>
  </form>
</template>

<script>
import { required, email, minLength, sameAs, not } from 'vuelidate/lib/validators'
import { testRegisterEmail } from '@/api/user'

const isFoodsharingDomain = (value) => value.match(/(.)+@foodsharing.((network)|(de))$/g)
const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms))

export default {
  props: { email: { type: String, default: '' }, password: { type: String, default: '' } },
  data () {
    return {
      confirmPassword: '',
      isMailValidForRegistration: false,
      isMailInvalid: false
    }
  },
  validations: {
    email: { required, email, foodsharing: not(isFoodsharingDomain) },
    password: { required, minLength: minLength(8) },
    confirmPassword: { required, sameAsPassword: sameAs('password') }
  },
  computed: {
    isValid () {
      return this.isMailValidForRegistration && !this.$v.$invalid && !this.isMailInvalid
    }
  },
  methods: {
    redirect () {
      this.$v.$touch()
      if (this.isValid) {
        this.$emit('next')
      }
    },
    async update ($event) {
      this.$emit('update:email', $event.target.value)
      this.$v.email.$touch()
      // Needs some delay, because touch cannot be awaited: https://github.com/vuelidate/vuelidate/issues/625
      await delay(20)
      this.isMailValidForRegistration = false
      this.isMailInvalid = false
      if (!this.$v.email.$error) {
        try {
          const MailExist = await testRegisterEmail($event.target.value)
          this.isMailValidForRegistration = MailExist.valid
        } catch (err) {
          if (err.code && err.code === 400) {
            this.isMailInvalid = true
            return this.isMailInvalid
          } else {
            throw err
          }
        }
        return this.isMailValidForRegistration
      }
    }
  }
}
</script>
<style lang="scss" scoped>
.invalid-feedback {
  font-size: 100%;
  display: unset;
}
</style>
