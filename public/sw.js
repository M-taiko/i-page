// i-Page service worker — deliberately conservative.
//
// This app is server-rendered and session/CSRF-token driven, so HTML pages
// are NEVER cached (caching one user's rendered page could leak their data
// to the next person on a shared device, or serve a stale CSRF token and
// break form submissions with a 419). Only static assets (css/js/images/
// fonts) are cached, plus a minimal offline fallback for navigation when
// there's truly no network.

const CACHE_NAME = 'ipage-static-v1';
const STATIC_ASSET_PATTERN = /\.(?:css|js|png|jpg|jpeg|svg|webp|gif|woff2?|ttf|ico)$/;

const PRECACHE_URLS = [
    '/css/design-system.css',
    '/css/components.css',
    '/images/brand/logo-mark.svg',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
    '/offline.html',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    // Static assets: cache-first, refreshing the cache in the background.
    if (STATIC_ASSET_PATTERN.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => cached);

                return cached || network;
            })
        );
        return;
    }

    // Page navigations: always try the network first (fresh session/CSRF
    // state), only falling back to a static offline notice if it fails.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
    }
});
