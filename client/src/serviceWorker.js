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
