// Pages and scores always come from the internet, so nobody sees an old score or an expired form.
// Only the offline page and its icon are saved on the phone.
const CACHE = 'pct-app-v1';
const OFFLINE = new URL('offline.html', self.registration.scope).href;
const ICON = new URL('images/icon-192.png', self.registration.scope).href;

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((c) => c.addAll([OFFLINE, ICON])).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys()
        .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
        .then(() => self.clients.claim()));
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE)));
    } else if (request.url === ICON) {
        event.respondWith(caches.match(ICON).then((hit) => hit || fetch(request)));
    }
});
