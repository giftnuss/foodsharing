import * as ajax from '@/api/base'

async function subscribeForPushNotifications () {
  const applicationServerKey = (await ajax.get('/pushnotification/webpush/publickey')).key
  const serviceWorkerRegistration = await navigator.serviceWorker.ready
  const subscription = await serviceWorkerRegistration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(applicationServerKey)
  })
  return sendPushSubscriptionToServer(subscription)
}

async function unsubscribeFromPushNotifications () {
  const serviceWorkerRegistration = await navigator.serviceWorker.ready
  const subscription = await serviceWorkerRegistration.pushManager.getSubscription()
  await subscription.unsubscribe()
  return removePushSubscriptionFromServer(subscription)
}

function sendPushSubscriptionToServer (subscription) {
  const key = subscription.getKey('p256dh')
  const token = subscription.getKey('auth')
  const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

  return ajax.post('/pushnotification/webpush/subscription', {
    endpoint: subscription.endpoint,
    publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
    authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
    contentEncoding
  })
}

function removePushSubscriptionFromServer (subscription) {
  const key = subscription.getKey('p256dh')
  const token = subscription.getKey('auth')
  const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

  return ajax.remove('/pushnotification/webpush/subscription', {
    endpoint: subscription.endpoint,
    publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
    authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
    contentEncoding
  })
}

function urlBase64ToUint8Array (base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4)
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/')

  const rawData = window.atob(base64)
  const outputArray = new Uint8Array(rawData.length)

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i)
  }
  return outputArray
}

export { subscribeForPushNotifications, unsubscribeFromPushNotifications, askForPermission }
