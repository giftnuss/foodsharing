<template>
    <form class="form-inline my-2 my-lg-0" style="flex-grow: 1">
        <div class="input-group mr-2" ref="inputgroup">
            <div class="input-group-prepend">
                <label class="input-group-text text-primary" for="login-email">
                    <i class="fa fa-user" />
                </label>
            </div>
            <input 
                type="text" 
                id="login-email"
                class="form-control text-primary" 
                placeholder="E-Mail" 
                aria-label="E-Mail"
                v-model="email"
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
            email: serverData.isDev ? 'userbot@example.com' : '',
            password: serverData.isDev ? 'user' : '',
            isLoading: false,
            error: null
        }
    },
    methods: {
        async submit() {
            if(!this.email) {
                pulseError('Bitte gib deine Email an')
                return    
            }
            if(!this.password) {
                pulseError('Bitte gib dein Passwort an')
                return    
            }
            this.isLoading = true
            try {
                let user = await login(this.email, this.password)
                pulseSuccess(`<b>Wunderschönen Tag Dir ${user.name}!</b><br />Du hast dich erfolgreich eingeloggt und wirst gleich weitergeleitet`)
                window.location = this.$url('dashboard')
            } catch(err) {
                this.isLoading = false
                if(err.code && err.code === 401) {
                    pulseError('E-Mail oder Passwort sind falsch')
                } else {
                    pulseError('Unknown error')
                    throw err
                }
            }
        }
    }
}
</script>

<style lang="scss">

#topbar .input-group {
    margin-bottom: 0;
    width: 10em;

    @media (max-width: 320px) {
        width: 80%;
    }
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
        padding: 0.1rem 0.4rem;
        font-size: 1em;
        padding-left: 0;
        font-weight: bold;
        border: none;
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

