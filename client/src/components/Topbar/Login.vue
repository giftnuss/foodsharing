<template>
  <form
    id="login-form"
    class="form-inline my-2 my-lg-0 flex-grow-1"
    @submit.prevent
  >
    <div
      ref="inputgroup"
      class="input-group input-group-sm mr-2 my-1"
    >
      <div class="input-group-prepend">
        <label
          class="input-group-text text-primary"
          for="login-email"
        >
          <i class="fas fa-user" />
        </label>
      </div>
      <input
        id="login-email"
        v-model="email"
        :placeholder="$i18n('login.email_address')"
        :aria-label="$i18n('login.email_address')"
        type="email"
        name="login-email"
        class="form-control text-primary"
        @keydown.enter="submit"
      >
    </div>
    <div
      ref="inputgroup"
      class="input-group input-group-sm mr-2 my-1"
    >
      <div class="input-group-prepend">
        <label
          class="input-group-text text-primary"
          for="login-password"
        >
          <i class="fas fa-key" />
        </label>
      </div>
      <input
        id="login-password"
        v-model="password"
        :placeholder="$i18n('login.password')"
        :aria-label="$i18n('login.password')"
        type="password"
        name="login-password"
        class="form-control text-primary"
        @keydown.enter="submit"
      >
    </div>
    <button
      v-if="!isLoading "
      :aria-label="$i18n('login.login_button_label')"
      href="#"
      class="btn btn-secondary btn-sm"
      @click="submit"
    >
      <i class="fas fa-arrow-right" />
    </button>
    <button
      v-else
      :aria-label="$i18n('login.login_button_label')"
      class="btn btn-light btn-sm loadingButton"
      @click="submit"
    >
      <img src="/img/469.gif">
    </button>
  </form>
</template>

<script>
import { login } from '@/api/user'

import { pulseError, pulseSuccess } from '@/script'
import i18n from '@/i18n'
import serverData from '@/server-data'

export default {
  data () {
    return {
      email: serverData.isDev ? 'userbot@example.com' : '',
      password: serverData.isDev ? 'user' : '',
      isLoading: false,
      error: null
    }
  },
  methods: {
    async submit () {
      if (!this.email) {
        pulseError(i18n('login.error_no_email'))
        window.location = this.$url('login')
        return
      }
      if (!this.password) {
        pulseError(i18n('login.error_no_password'))
        window.location = this.$url('login')
        return
      }
      this.isLoading = true
      try {
        const user = await login(this.email, this.password)
        pulseSuccess(i18n('login.success', { user_name: user.name }))

        const urlParams = new URLSearchParams(window.location.search)

        if (urlParams.has('ref')) {
          window.location = decodeURIComponent(urlParams.get('ref'))
        } else {
          window.location = this.$url('dashboard')
        }
      } catch (err) {
        this.isLoading = false
        if (err.code && err.code === 401) {
          pulseError(i18n('login.error_no_auth'))
          setTimeout(() => {
            window.location = this.$url('login')
          }, 2000)
        } else {
          pulseError(i18n('error_unexpected'))
          throw err
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  .loadingButton {
    img {
      height: 1em;
    }
  }

  #login-form .input-group {
    @media (max-width: 575px) {
      width: 80%;
    }
  }
</style>
