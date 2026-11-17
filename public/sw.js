const CACHE_NAME = 'civilize-cache-v5-nocache';
const STATIC_ASSETS = [
    '/images/logo/logo.png',
    '/manifest.json'
];

// Install: Skip waiting immediately
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

// Activate: Clean up ALL old caches immediately and claim clients
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(keys.map((key) => caches.delete(key)));
        }).then(() => self.clients.claim())
    );
});

// Fetch: NEVER cache HTML pages or navigation requests so UI changes appear instantly
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests, local development, APIs, admin, and HTML navigation
    if (
        request.method !== 'GET' ||
        !url.protocol.startsWith('http') ||
        request.mode === 'navigate' ||
        url.pathname.startsWith('/api') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.includes('webhook') ||
        url.hostname === 'localhost' ||
        url.hostname === '127.0.0.1'
    ) {
        return;
    }

    // Static media only: Cache-first with network fallback
    if (
        request.destination === 'image' ||
        request.destination === 'font'
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            })
        );
    }
});
