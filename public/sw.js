// Service Worker untuk Sistem Presensi Bidtik Polda Kalsel
// Menangani notifikasi Web Push dan aksi klik

self.addEventListener('push', function(event) {
    let payload = {
        title: 'Presensi Bidtik Polda Kalsel',
        body: 'Anda memiliki pemberitahuan baru.',
        icon: '/favicon.ico',
        url: '/presensi'
    };

    if (event.data) {
        try {
            payload = Object.assign(payload, event.data.json());
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon || '/favicon.ico',
        badge: '/favicon.ico',
        vibrate: [100, 50, 100],
        data: {
            url: payload.url || '/presensi'
        }
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const targetUrl = event.notification.data && event.notification.data.url 
        ? event.notification.data.url 
        : '/presensi';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes('/presensi') && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
