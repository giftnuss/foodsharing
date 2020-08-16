import '@/core'
import '@/globals'
import './Profile.css'
import $ from 'jquery'
import { expose } from '@/utils'
import { sendBanana } from '@/api/user'
import { pulseError, pulseInfo, profile } from '@/script'
import i18n from '@/i18n'
import { vueRegister, vueApply } from '@/vue'
import PublicProfile from './components/PublicProfile'

expose({ trySendBanana })

async function trySendBanana (id) {
  try {
    await sendBanana(id, $('#bouch-ta').val().trim())
    pulseInfo(i18n('profile.bananaSent'))
    profile(id)
  } catch (err) {
    if (err.code === 400) {
      pulseError(i18n('profile.bananaMessageTooShort'))
    } else {
      pulseError(i18n('error_unexpected'))
    }
  }
}

vueRegister({
  PublicProfile
})

vueApply('#profile-public', true)
