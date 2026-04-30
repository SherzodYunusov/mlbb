const IMAGE_CACHE = 'mlbb-images-v1';
const API_CACHE   = 'mlbb-api-v1';

// O'rnatishda hech narsa qilmaymiz, tezda faollashadi
self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', e => {
    // Eski kesh versiyalarini tozalash
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(k => k !== IMAGE_CACHE && k !== API_CACHE)
                    .map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);

    // Rasmlarni keshga olish (storage/accounts/...)
    if (
        e.request.destination === 'image' ||
        url.pathname.startsWith('/storage/accounts/')
    ) {
        e.respondWith(
            caches.open(IMAGE_CACHE).then(cache =>
                cache.match(e.request).then(cached => {
                    if (cached) return cached;                      // keshdan qayt
                    return fetch(e.request).then(res => {
                        if (res.ok) cache.put(e.request, res.clone());
                        return res;
                    }).catch(() => cached);                        // oflayn bo'lsa keshdan
                })
            )
        );
        return;
    }

    // /api/accounts — network-first, xato bo'lsa keshdan
    if (url.pathname === '/api/accounts' && e.request.method === 'GET') {
        e.respondWith(
            caches.open(API_CACHE).then(cache =>
                fetch(e.request).then(res => {
                    if (res.ok) cache.put(e.request, res.clone());
                    return res;
                }).catch(() => cache.match(e.request))
            )
        );
        return;
    }
});
