import '@/core'
import '@/globals'
import './Profile.css'
import $ from 'jquery'
import { expose } from '@/utils'
import { sendBanana } from '@/api/user'
import { sendBuddyRequest } from '@/api/buddy'
import { pulseError, pulseInfo, profile } from '@/script'
import i18n from '@/i18n'
import { vueRegister, vueApply } from '@/vue'
import PublicProfile from './components/PublicProfile'

expose({ trySendBanana, trySendBuddyRequest })

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

async function trySendBuddyRequest (userId) {
  try {
    const isBuddy = await sendBuddyRequest(userId)
    $('.buddyRequest').remove()
    if (isBuddy) { pulseInfo(i18n('buddy.request_accepted')) } else { pulseInfo(i18n('buddy.request_sent')) }
  } catch (err) {
    pulseError(i18n('error_unexpected'))
  }
}

vueRegister({
  PublicProfile
})

vueApply('#profile-public', true)
