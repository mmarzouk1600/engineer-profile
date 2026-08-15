self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        //notifications aren't supported or permission not granted!
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        console.log(msg)
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon,
            data: msg.data,
            actions: msg.actions
        }));
    }
});

self.addEventListener('notificationclick', (event) => {
    if (!event.action) {
        clients.openWindow(event.notification.data.ticketUrl)
        return;
    }

    switch (event.action) {
        case 'open-ticket':
            clients.openWindow(event.notification.data.ticketUrl)
            break;
        default:
            console.log(`Unknown action clicked: '${event.action}'`);
            break;
    }
});
