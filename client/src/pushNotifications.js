function subscribeForPushNotifications() {
  fetch('api/pushnotification/webpush/publickey').then(response => {
    if (!response.ok) {
      throw new Error('HTTP error, status = ' + response.status);
    }
    response.json().then(json => {
      const applicationServerKey = json;
      console.log('now I\'m here');
      navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
      }))
      .then(subscription => {
        console.log('now I\'m there');
        // create subscription on your server
        return sendPushSubscriptionToServer(subscription);
      })
      .then()
      .catch(e => {
        if (Notification.permission === 'denied') {
          // The user denied the notification permission which
          // means we failed to subscribe and the user will need
          // to manually change the notification permission to
          // subscribe to push messages
          console.warn('Notifications are denied by the user.');
        } else {
          // A problem occurred with the subscription; common reasons
          // include network errors or the user skipped the permission
          console.error('Impossible to subscribe to push notifications', e);
        }
      });
    });
  });
}

function sendPushSubscriptionToServer(subscription) {
  const key = subscription.getKey('p256dh');
  const token = subscription.getKey('auth');
  const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

  console.log('I\'m here!');

  return fetch('api/pushnotification/webpush/subscription', {
    method: 'POST',
    body: JSON.stringify({
      endpoint: subscription.endpoint,
      publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
      authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
      contentEncoding,
    }),
  }).then(() => subscription);
}


function urlBase64ToUint8Array(base64String) {
  console.log('encoding...');
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
  .replace(/\-/g, '+')
  .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export default subscribeForPushNotifications;