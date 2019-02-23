import router from '@/urls'

self.addEventListener('push', function (event) {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return
  }

  const sendNotification = (title, options) => {
    return self.registration.showNotification(title, options)
  }

  if (event.data) {
    const data = event.data.json()
    event.waitUntil(sendNotification(data.title, data.options))
  }
})

self.addEventListener('notificationclick', function (event) {
  if (event.notification.data.action) {
    const page = event.notification.data.action.page
    const params = event.notification.data.action.params
    const url = router[page](...params)
    self.clients.openWindow(url)
  }
})
