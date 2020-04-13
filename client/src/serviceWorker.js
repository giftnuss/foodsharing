import { urls } from '@/urls'

self.addEventListener('push', function (event) {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return
  }

  if (!event.data) {
    return;
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

// Ensure new workers to replace old ones...
// https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerGlobalScope/skipWaiting

self.addEventListener('install', event => {
  event.waitUntil(self.skipWaiting())
})

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim())
})
