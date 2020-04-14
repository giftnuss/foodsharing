import { urls } from '@/urls'
import { subscribeForPushNotifications } from '@/pushNotifications'

self.addEventListener('push', function (event) {
  if (!self.Notification || self.Notification.permission !== 'granted') {
    return
  }

  if (!event.data) {
    return
  }

  const data = event.data.json()
  event.waitUntil(self.registration.showNotification(data.title, data.options))
})

self.addEventListener('notificationclick', function (event) {
  if (event.notification.data.action) {
    const page = event.notification.data.action.page
    const params = event.notification.data.action.params
    const url = urls[page](...params)
    self.clients.openWindow(url)
  }
})

// Time to time, browsers decide to reset their push subscription data. Then all subscriptions for this browser become invalid, and we need to register a new one.
self.addEventListener('pushsubscriptionchange', function (event) {
  event.waitUntil(subscribeForPushNotifications(event.oldSubscription.options))
  // we don't need to care about the old subscription on the server, it's going to get removed automatically as soon as the server realizes it's invalid
})

// Ensure new workers to replace old ones...
// https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerGlobalScope/skipWaiting

self.addEventListener('install', event => {
  event.waitUntil(self.skipWaiting())
})

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim())
})
