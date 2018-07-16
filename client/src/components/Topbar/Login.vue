<template>
    <form class="form-inline my-2 my-lg-0" style="flex-grow: 1">
        <div class="input-group mr-2" ref="inputgroup">
            <div class="input-group-prepend">
                <label class="input-group-text text-primary" for="login-username">
                    <i class="fa fa-user" />
                </label>
            </div>
            <input 
                type="text" 
                id="login-username"
                class="form-control text-primary" 
                placeholder="Benutzername" 
                aria-label="Benutzername"
                v-model="username"
                @keydown.enter="submit"
            >
        </div>
        <div class="input-group mr-2" ref="inputgroup">
            <div class="input-group-prepend">
                <label class="input-group-text text-primary" for="login-password">
                    <i class="fa fa-key" />
                </label>
            </div>
            <input 
                type="password" 
                id="login-password"
                class="form-control text-primary" 
                placeholder="Passwort" 
                aria-label="Passwort"
                v-model="password"
                @keydown.enter="submit"
            >
        </div>
        <a v-if="!isLoading " href="#" class="btn btn-secondary btn-sm" @click="submit">
            <i class="fa fa-arrow-right" />
        </a>
        <a v-else class="btn btn-light btn-sm loadingButton" @click="submit">
            <img src="/img/469.gif" />
        </a>
    </form>
</template>

<script>
import { login } from '@/api/user'

import { pulseError, pulseSuccess } from '@/script'
import serverData from '@/server-data'

export default {
    data() {
        return {
            username: serverData.isDev ? 'userbot@example.com' : '',
            password: serverData.isDev ? 'test' : '',
            isLoading: false,
            error: null
        }
    },
    methods: {
        async submit() {
            if(!this.username) {
                pulseError('Bitte gib einen Benutzernamen an')
                return    
            }
            if(!this.password) {
                pulseError('Bitte gib dein Passwort an')
                return    
            }
            this.isLoading = true
            try {
                let user = await login(this.username, this.password)
                pulseSuccess(`<b>Wunderschönen Tag Dir ${user.name}!</b><br />Du hast dich erfolgreich eingeloggt und wirst gleich weitergeleitet`)
                window.location = this.$url('dashboard')
            } catch(err) {
                if(err.code && err.code === 401) {
                    pulseError('Benutzername oder Passwort find falsch')
                } else {
                    pulseError('Unknown error')
                }
                this.isLoading = false
            }
        }
    }
}
</script>

<style lang="scss">

#topbar .input-group {
    margin-bottom: 0;
    width: 10em;
    img, i {
        height: 1em;
        width: 1em;
    }
    .input-group-text {
        background-color: white;
        border: none;
        padding: 0.1rem 0.4rem;
        font-size: .9em;
    }
    input.form-control {
        padding: 0.1rem 0.75rem;
        font-size: 1em;
        padding-left: 0;
        font-weight: bold;
        &:focus {
            box-shadow: none;
            border: none;
        }
    }
}
.loadingButton {
    img {
        height: 1em;
    }
}
</style>

