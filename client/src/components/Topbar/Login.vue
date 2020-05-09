<template>
  <b-popover
    target="login"
    custom-class="bootstrap login-popover"
    triggers="focus"
    placement="top"
    container="topbar"
    variant="secondary"
    @show="focusLogin=true"
    @shown="focusLogin=false"
  >
    <form
      id="login-form"
      class="my-lg-0 flex-grow-1"
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
          ref="email"
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
          autocomplete="on"
          @keydown.enter="submit"
        >
      </div>
      <b-overlay
        :show="isLoading"
      >
        <template v-slot:overlay>
          <img src="/img/469.gif">
        </template>
        <b-button
          :aria-label="$i18n('login.login_button_label')"
          href="#"
          secondary
          class="login-btn"
          @click="submit"
        >
          <span>
            Login
          </span>
          <i class="fas fa-arrow-right mr-auto" />
        </b-button>
      </b-overlay>
    </form>
  </b-popover>
</template>

<script>
import { login } from '@/api/user'

import { pulseError, pulseSuccess } from '@/script'
import i18n from '@/i18n'
import serverData from '@/server-data'
import { BPopover } from 'bootstrap-vue'

export default {
  components: { BPopover },
  data () {
    return {
      email: serverData.isDev ? 'userbot@example.com' : '',
      password: serverData.isDev ? 'user' : '',
      rememberMe: false,
      isLoading: false,
      error: null,
      focusLogin: false
    }
  },
  watch: {
    focus: function (val) {
      if (val) {
        this.$refs.email.select()
      }
    }
  },
  methods: {
    async submit () {
      if (!this.email) {
        pulseError(i18n('login.error_no_email'))
        // window.location = this.$url('login')
        return
      }
      if (!this.password) {
        pulseError(i18n('login.error_no_password'))
        // window.location = this.$url('login')
        return
      }
      this.isLoading = true
      try {
        const user = await login(this.email, this.password, this.rememberMe)
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
            // window.location = this.$url('login')
          }, 2000)
        } else {
          pulseError(i18n('error_unexpected'))
          throw err
        }
      }
    },
    focusRef (ref) {
      // Some references may be a component, functional component, or plain element
      // This handles that check before focusing, assuming a `focus()` method exists
      // We do this in a double `$nextTick()` to ensure components have
      // updated & popover positioned first
      this.$nextTick(() => {
        this.$nextTick(() => {
          ;(ref.$el || ref).focus()
        })
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  .b-link {
    color: rgb(100, 174, 036);
  }
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
  .login-btn {
    display: flex;
    align-items: center;
    span {
      width: 100%;
    }
  }
  .login-popover {
    box-shadow: -0.5em 0.5em 20px -3px #333;
    max-width: 100%;
  }
</style>
