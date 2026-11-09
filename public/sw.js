const CACHE_NAME = '5050-logistics-v1';
const STATIC_ASSETS = [
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/favicon.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Network-first for pages and API-ish requests
    if (request.mode === 'navigate' || url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request).catch(() => caches.match(request))
        );
        return;
    }

    // Cache-first for static assets (css, js, images, fonts)
    if (/\.(css|js|png|jpg|jpeg|svg|webp|woff2?|ico)$/.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const fetched = fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
                return cached || fetched;
            })
        );
    }
});
