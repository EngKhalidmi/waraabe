/* eslint-disable no-restricted-globals */
const CACHE_NAME = 'waraabe-pwa-v13';
const SCOPE_URL = new URL(self.registration.scope);
const toScopeUrl = (path) => new URL(String(path || '').replace(/^\//, ''), SCOPE_URL).toString();
const OFFLINE_URL = toScopeUrl('offline');
const PRECACHE_URLS = [
    toScopeUrl(''),
    toScopeUrl('login'),
    OFFLINE_URL,
    toScopeUrl('manifest.json'),
    toScopeUrl('pwa/pwa.js'),
    toScopeUrl('offline/config/business-tables.js'),
    toScopeUrl('offline/dexie.min.js'),
    toScopeUrl('offline/database.js'),
    toScopeUrl('offline/queue.js'),
    toScopeUrl('offline/repository.js'),
    toScopeUrl('offline/repositories/customer-repository.js'),
    toScopeUrl('offline/repositories/product-repository.js'),
    toScopeUrl('offline/repositories/fuel-sale-repository.js'),
    toScopeUrl('offline/repositories/business-repositories.js'),
    toScopeUrl('offline/config/sync-config.js'),
    toScopeUrl('offline/sync/sync-manager.js'),
    toScopeUrl('offline/sync/sync-client.js'),
    toScopeUrl('offline/offline-engine.js'),
    toScopeUrl('offline/customer-module.js'),
    toScopeUrl('offline/product-module.js'),
    toScopeUrl('offline/fuel-sales-module.js'),
    toScopeUrl('offline/inventory-module.js'),
    toScopeUrl('offline/purchase-module.js'),
    toScopeUrl('offline/sales-quotation-module.js'),
    toScopeUrl('offline/finance-module.js'),
    toScopeUrl('pwa/icons/icon-192.png'),
    toScopeUrl('pwa/icons/icon-512.png'),
    toScopeUrl('pwa/icons/apple-touch-icon.png'),
    toScopeUrl('Logo/icon.png'),
    toScopeUrl('theme/assets/img/favicon.png')
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

function isSameOrigin(request) {
    return new URL(request.url).origin === self.location.origin;
}

function isStaticAsset(request) {
    if (['style', 'script', 'image', 'font'].includes(request.destination)) {
        return true;
    }

    return /\.(?:css|js|mjs|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|json)$/i.test(new URL(request.url).pathname);
}

async function cacheAndReturn(request) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request, { ignoreSearch: true });

    try {
        const response = await fetch(request);

        if (response && (response.ok || response.type === 'opaque')) {
            cache.put(request, response.clone()).catch(() => {});
        }

        return response;
    } catch (error) {
        if (cachedResponse) {
            return cachedResponse;
        }

        throw error;
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone)).catch(() => {});
                    return response;
                })
                .catch(async () => {
                    const cachedPage = await caches.match(request, { ignoreSearch: true });
                    if (cachedPage) {
                        return cachedPage;
                    }

                    const offlinePage = await caches.match(OFFLINE_URL);
                    if (offlinePage) {
                        return offlinePage;
                    }

                    return Response.error();
                })
        );

        return;
    }

    if (isSameOrigin(request) || isStaticAsset(request)) {
        event.respondWith(cacheAndReturn(request));
    }
});
