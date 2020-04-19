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
        v-model.lazy="$v.email.$model"
        :class="{ 'is-invalid': $v.email.$error }"
        type="email"
        name="email"
        class="form-control"
        @input="$emit('update:email', $event.target.value)"
      >
      <div
        v-if="$v.email.$error"
        class="invalid-feedback"
      >
        <span v-if="!$v.email.required">{{ $i18n('register.email_required') }}</span>
        <span v-if="!$v.email.email">{{ $i18n('register.email_invalid') }}</span>
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
import { required, email, minLength, sameAs } from 'vuelidate/lib/validators'

export default {
  props: { email: { type: String, default: '' }, password: { type: String, default: '' } },
  data () {
    return {
      confirmPassword: ''
    }
  },
  validations: {
    email: { required, email },
    password: { required, minLength: minLength(8) },
    confirmPassword: { required, sameAsPassword: sameAs('password') }
  },

  methods: {
    redirect () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$emit('next')
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
