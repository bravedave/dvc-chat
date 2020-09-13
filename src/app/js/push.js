/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

( _ => {
  _.push = {
    url : '',
    serviceWorker : '',
    applicationServerKey : ''

  };

  let urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  };

  let checkNotificationPermission = () => {
    return new Promise((resolve, reject) => {
      if (Notification.permission === 'denied') {
        // console.log('Push messages are blocked.');
        reject(new Error('Push messages are blocked.'));

      }

      if (Notification.permission === 'granted') {
        console.log('granted ..');
        resolve();

      }
      else if (Notification.permission === 'default') {
        Notification.requestPermission().then(result => {
          if (result !== 'granted') {
            // console.log('Bad permission result');
            reject(new Error('Bad permission result'));
          } else {
            // console.log('Good permission result');
            resolve();
          }
        });

      }
      else {
        console.log('Unknown permission');
        return reject(new Error('Unknown permission'));

      }

    });

  }

  _.push.load = () => {
    // Check the current Notification permission.
    // If its denied, the button should appears as such, until the user changes the permission manually

    if (Notification.permission === 'denied') {
      console.warn('Notifications are denied by the user');

    }
    else {
      navigator.serviceWorker.register( _.push.serviceWorker).then(
        () => {
          console.log('[SW] Service worker has been registered');
          _.push.updateSubscription();

        },
        e => {
          console.error('[SW] Service worker registration failed', e);

        }

      );

    }

  };

  _.push.sendSubscriptionToServer = (subscription) => {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');
    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

    console.log( 'sendSubscriptionToServer');

    return _.post({
      url : _.push.url,
      data : {
        action : 'subscription-save',
        json: JSON.stringify({
          endpoint: subscription.endpoint,
          publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
          authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
          contentEncoding,
        }),
        endpoint: subscription.endpoint,
        publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
        authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
        encoding: contentEncoding,

      },

    }).then( d => {
      _.growl( d);
      return subscription;

    });

  };

  _.push.subscribe = () => {
    // console.log('subscribe ..');

    return checkNotificationPermission()
      .then( () => {
        navigator.serviceWorker.register( _.push.serviceWorker)
          .then( serviceWorkerRegistration => {
            serviceWorkerRegistration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: urlBase64ToUint8Array(_.push.applicationServerKey),
            })
            .then( subscription => {
              return _.push.sendSubscriptionToServer(subscription);

            });

          })

        })
        .catch(e => {
          if (Notification.permission === 'denied') {
            console.warn('Notifications are denied by the user.');

          }
          else {
            console.error('Impossible to subscribe to push notifications', e);

          }

        });



      // .then(() => navigator.serviceWorker.ready)
      // .then(serviceWorkerRegistration => {
      //   console.log('checkNotificationPermission - success..');
      //   serviceWorkerRegistration.pushManager.subscribe({
      //     userVisibleOnly: true
      //   });

      // })
      // .then(subscription => {
      //   // Subscription was successful
      //   // create subscription on your server
      //   console.log( 'checkNotificationPermission - success...');
      // })

  };

  _.push.updateSubscription = () => {
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
      .then(subscription => {
        if (!subscription) {
          console.log( 'We aren\'t subscribed to push, so set UI to allow the user to enable push');
          return;

        }

        console.log( 'Keep your server in sync with the latest endpoint');
        return _.push.sendSubscriptionToServer(subscription);

      })
      .catch(e => {
        console.error('Error when updating the subscription', e);

      });

  }

}) (_brayworth_);

