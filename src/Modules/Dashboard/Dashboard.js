import '@/core'
import '@/globals'
import './Dashboard.css'

import activity from '@/activity'
import i18n from '@/i18n'
import { subscribeForPushNotifications } from '@/pushNotifications'
import { pulseSuccess, pulseError } from '@/script'

activity.init()

// Push Notification Banner
const pushnotificationsBanner = document.querySelector('#top-banner-pushnotifications')
if (('PushManager' in window) && (Notification.permission === 'default') && !document.cookie.includes('pushNotificationBannerClosed=true')) {
  pushnotificationsBanner.style.display = ''

  const pushnotificationsButton = document.querySelector('#button-pushnotifications')
  pushnotificationsButton.addEventListener('click', async () => {
    try {
      await subscribeForPushNotifications()
      pulseSuccess(i18n('push_notifications_activation_success'))
      pushnotificationsBanner.classList.add('top-banner-pushnotifications-closed')
    } catch (error) {
      pulseError(i18n('error_ajax'))
      throw error
    }
  })
}
const closeButton = document.querySelector('#close-top-banner-pushnotifications')
closeButton.addEventListener('click', () => {
  pushnotificationsBanner.classList.add('top-banner-pushnotifications-closed')
  document.cookie = 'pushNotificationBannerClosed=true;'
})
