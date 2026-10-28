// The admin's new-order notifications (App\Services\AdminPush, turned on in Admin > Settings). Each push
// must show a notification: an iPhone stops sending pushes to an app that receives them silently.
self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (event) { event.waitUntil(self.clients.claim()); });

self.addEventListener('push', function (event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) {}
    event.waitUntil(self.registration.showNotification(data.title || 'New order', {
        body: data.body || '',
        tag: data.tag || undefined,
        renotify: !!data.tag,
        icon: '/assets/img/logo/preloader2.png',
        data: {url: data.url || '/admin'}
    }));
});

// Tapping it opens the order (in the admin window already open, if there is one)
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/admin';
    event.waitUntil(self.clients.matchAll({type: 'window', includeUncontrolled: true}).then(function (windows) {
        for (var i = 0; i < windows.length; i++) {
            if ('navigate' in windows[i]) {
                return windows[i].focus().then(function (win) { return win.navigate(url); });
            }
        }
        return self.clients.openWindow(url);
    }));
});
