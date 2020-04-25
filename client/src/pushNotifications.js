import * as ajax from '@/api/base'

/**
 * @param {PushSubscriptionOptions} [options] – You can save a request if you provide the application server key.
 * @return {Promise<any>} - A promise resolving to the server's response (usually empty)
 */
export async function subscribeForPushNotifications (options = { userVisibleOnly: true, applicationServerKey: null }) {
  if (options.applicationServerKey === null) {
    options.applicationServerKey = urlBase64ToUint8Array((await ajax.get('/pushnotification/webpush/server-information')).key)
  }

  const serviceWorkerRegistration = await navigator.serviceWorker.ready
  const subscription = await serviceWorkerRegistration.pushManager.subscribe(options)

  return sendPushSubscriptionToServer(subscription)
}

/**
 * @return {Promise<boolean>}
 */
export async function unsubscribeFromPushNotifications () {
  const serviceWorkerRegistration = await navigator.serviceWorker.ready
  const subscription = await serviceWorkerRegistration.pushManager.getSubscription()

  return subscription.unsubscribe()
}

/**
 * @param {PushSubscription} subscription
 * @return {Promise<{}|*|undefined>}
 */
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

/**
 * @param {String} base64String
 * @return {Uint8Array}
 */
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
