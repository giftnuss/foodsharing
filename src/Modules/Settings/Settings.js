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
    pushNotificationsLabel.textContent = 'Dein Browser unterstützt leider keine Push-Benarichtigungen.'
    pushNotificationsButton.style.display = 'none'
    return
  }

  if (Notification.permission === 'denied') {
    pushNotificationsLabel.textContent = 'Du hast das Anzeigen von Push-Benachrichtigungen durch Foodsharing in deinem Browser abgelehnt. Wenn du Push-Nachrichten empfangen willst, ändere deine Browser-Einstellungen für Foodsharing und lade die Seite neu.'
    pushNotificationsButton.style.display = 'none'
    return
  }

  pushNotificationsLabel.textContent = 'Wenn du Push-Benachrichtigungen für dieses Gerät aktivierst, werden Chat-Nachrichten zukünftig direkt an dein Gerät zugestellt, auch dann, wenn du nicht eingeloggt bist. Dein Gerät zeigt dann eine entprechende Benachrichtigung bzw. reagiert mit einem Ton oder einer Vibration.'
  pushNotificationsButton.text = 'Push-Benachrichtigungen aktivieren'

  const subscription = await (await navigator.serviceWorker.ready).pushManager.getSubscription()
  if (subscription === null) {
    pushNotificationsButton.addEventListener('click', async () => {
      await subscribeForPushNotifications()
      pulseSuccess('Push-Benachrichtigungen erfolgreich aktiviert')
      refreshPushNotificationSettings()
    })
    return
  }

  /*

  This does not work currently, neither in Firefox nor Chrom(ium). As long as I have no better idea, we'll have to live
  with some 'dead subscriptions' in our database.

  // This is for the rare case that a user has subscribed for push notifications, but then set their browser permissions
  // back to 'ask for push permissions'. In that case, we don't want to create a new subscription, as this would produce
  // 'dead' push subscriptions in our database.
  if (Notification.permission !== 'granted') {
    pushNotificationsButton.addEventlistener('click', async () => {
      await askForPermission()
      refreshPushNotificationSettings()
    })
    return
  }
  */

  pushNotificationsLabel.textContent = 'Auf diesem Gerät sind die Push-Benachrichtigungen von Foodsharing eingeschaltet. Wenn du die Push-Benachrichtigungen deaktivierst, werden Chat-Nachrichten zukünftig nicht mehr direkt an dein Gerät zugestellt. Dein Gerät zeigt dann keine Benachrichtigungen mehr wenn du nicht eingeloggt bist und reagiert auch nicht mehr mit einem Ton oder einer Vibration.'
  pushNotificationsButton.text = 'Push-Benachrichtigungen deaktivieren'
  pushNotificationsButton.addEventListener('click', async () => {
    await unsubscribeFromPushNotifications()
    pulseSuccess('Push-Benachrichtigungen erfolgreich deaktivert')
    refreshPushNotificationSettings()
  })
}
