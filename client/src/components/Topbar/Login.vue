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
        type="email"
        name="login-email"
        class="form-control text-primary"
        placeholder="E-Mail"
        aria-label="E-Mail"
        @keydown.enter="submit"
      >
    </div>
    <div
      ref="inputgroup"
      class="input-group input-group-sm mr-2 my-1
"
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
        type="password"
        name="login-password"
        class="form-control text-primary"
        placeholder="Passwort"
        aria-label="Passwort"
        @keydown.enter="submit"
      >
    </div>
    <button
      v-if="!isLoading "
      href="#"
      class="btn btn-secondary btn-sm"
      @click="submit"
    >
      <i class="fas fa-arrow-right" />
    </button>
    <button
      v-else
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
        pulseError('Bitte gib Deine E-Mail-Adresse an!')
        return
      }
      if (!this.password) {
        pulseError('Bitte gib Dein Passwort an!')
        return
      }
      this.isLoading = true
      try {
        let user = await login(this.email, this.password)
        pulseSuccess(`<b>Wunderschönen Tag Dir, ${user.name}!</b><br />Du hast Dich erfolgreich eingeloggt und wirst gleich weitergeleitet.`)
        window.location = this.$url('dashboard')
      } catch (err) {
        this.isLoading = false
        if (err.code && err.code === 401) {
          pulseError('E-Mail-Adresse oder Passwort sind falsch')
          setTimeout(() => {
            window.location = '/?page=login&ref=%2F%3Fpage%3Ddashboard'
          }, 2000)
        } else {
          pulseError('Unknown error')
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
