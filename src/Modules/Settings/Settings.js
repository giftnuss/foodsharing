/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import './Settings.css'
import 'jquery-jcrop'
import 'jquery-dynatree'
import { attachAddressPicker } from '@/addressPicker'
import {
  fotoupload,
  picFinish,
  pulseSuccess,
  pulseError,
  collapse_wrapper,
  GET,
} from '@/script'
import { expose } from '@/utils'
import i18n from '@/i18n'
import { subscribeForPushNotifications, unsubscribeFromPushNotifications } from '@/pushNotifications'
import { confirmDeleteUser } from '../Foodsaver/Foodsaver'
import { vueApply, vueRegister } from '@/vue'
import Calendar from './components/Calendar'

if (GET('sub') === 'calendar') {
  vueRegister({
    Calendar,
  })
  vueApply('#calendar')
}

expose({
  fotoupload,
  picFinish,
  confirmDeleteUser,
  collapse_wrapper,
})

if (GET('sub') === 'general') {
  attachAddressPicker()
}

// Fill the Push Notifications module with life
refreshPushNotificationSettings()

async function refreshPushNotificationSettings () {
  const pushNotificationsLabel = document.querySelector('#push-notification-label')

  if (!pushNotificationsLabel) {
    return // we seem to be on some settings page that doesn't contain no push notification settings
  }

  const pushNotificationsButton = document.querySelector('#push-notification-button')

  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    pushNotificationsLabel.textContent = i18n('settings.push.not-supported')
    pushNotificationsButton.style.display = 'none'
    return
  }

  if (Notification.permission === 'denied') {
    pushNotificationsLabel.textContent = i18n('settings.push.denied')
    pushNotificationsButton.style.display = 'none'
    return
  }

  const subscription = await (await navigator.serviceWorker.ready).pushManager.getSubscription()
  if (subscription === null) {
    pushNotificationsLabel.textContent = i18n('settings.push.info-on')
    pushNotificationsButton.text = i18n('settings.push.enable')
    pushNotificationsButton.addEventListener('click', async () => {
      try {
        await subscribeForPushNotifications()
        pulseSuccess(i18n('settings.push.success'))
        refreshPushNotificationSettings()
      } catch (error) {
        pulseError(i18n('error_ajax'))
        refreshPushNotificationSettings()
        throw error
      }
    }, { once: true })
    return
  }

  pushNotificationsLabel.textContent = i18n('settings.push.info-off')
  pushNotificationsButton.text = i18n('settings.push.disable')
  pushNotificationsButton.addEventListener('click', async () => {
    try {
      await unsubscribeFromPushNotifications()
      pulseSuccess(i18n('settings.push.disabled'))
      refreshPushNotificationSettings()
    } catch (error) {
      pulseError(i18n('error_ajax'))
      refreshPushNotificationSettings()
      throw error
    }
  }, { once: true })
}
