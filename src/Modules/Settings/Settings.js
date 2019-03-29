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
  goTo,
  collapse_wrapper,
  GET
} from '@/script'
import { expose } from '@/utils'
import i18n from '@/i18n'
import { deleteUser } from '@/api/user'
import { subscribeForPushNotifications, unsubscribeFromPushNotifications } from '@/pushNotifications'

expose({
  fotoupload,
  picFinish,
  collapse_wrapper,
  confirmDeleteAccount
})

if (GET('sub') === 'general') {
  attachAddressPicker()
}

async function confirmDeleteAccount (fsId) {
  if (window.confirm(i18n('foodsaver.delete_account_sure'))) {
    try {
      await deleteUser(fsId)
      pulseSuccess(i18n('success'))
      goTo('/')
    } catch (err) {
      pulseError(i18n('error_unexpected'))
      throw err
    }
  }
}

// Fill the Push Notifications module with life
refreshPushNotificationSettings()

async function refreshPushNotificationSettings () {
  const pushNotificationsLabel = document.querySelector('#push-notification-label')
  const pushNotificationsButton = document.querySelector('#push-notification-button')

  if (!('PushManager' in window)) {
    pushNotificationsLabel.textContent = i18n('push_notifications_not_supported')
    pushNotificationsButton.style.display = 'none'
    return
  }

  if (Notification.permission === 'denied') {
    pushNotificationsLabel.textContent = i18n('push_notifications_denied_by_user')
    pushNotificationsButton.style.display = 'none'
    return
  }

  const subscription = await (await navigator.serviceWorker.ready).pushManager.getSubscription()
  if (subscription === null) {
    pushNotificationsLabel.textContent = i18n('push_notifications_activation_explanation')
    pushNotificationsButton.text = i18n('push_notifications_activation_button_text')
    pushNotificationsButton.addEventListener('click', async () => {
      await subscribeForPushNotifications()
      pulseSuccess(i18n('push_notifications_activation_success'))
      refreshPushNotificationSettings()
    })
    return
  }

  pushNotificationsLabel.textContent = i18n('push_notifications_deactivation_explanation')
  pushNotificationsButton.text = i18n('push_notifications_deactivation_button_text')
  pushNotificationsButton.addEventListener('click', async () => {
    await unsubscribeFromPushNotifications()
    pulseSuccess(i18n('push_notifications_deactivation_success'))
    refreshPushNotificationSettings()
  })
}
