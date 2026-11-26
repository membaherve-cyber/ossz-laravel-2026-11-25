/* OSSZ Collections service worker — offline shell.
 *
 * Every cached URL below is relative (or a same-origin asset), so the worker
 * works identically on localhost, osszcollection.com and any subfolder mount.
 */
const VERSION = 'ossz-v3';
// v3: switch static assets from cache-first to stale-while-revalidate.
// v2 used cache-first, which meant a returning visitor could keep a stale
// /js/app.js (or css) forever — the app appeared "wired to the old site"
// (dead clicks, broken nav) even though the server had moved on. SWR serves
// the cached copy instantly but always refreshes it in the background, so
// asset updates reach everyone within one load.
// v2 itself had bumped the version to evict caches left by the pre-cookie-fix
// worker; v3 repeats that eviction for any visitor stuck on v2.
const OFFLINE_URL = '/offline';

// Static assets cached on install (stale-while-revalidate afterwards).
const PRECACHE = [
    OFFLINE_URL,
    '/css/app.css',
    '/js/app.js',
    '/icons/favicon-32.png',
    '/icons/apple-touch-icon.png',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/logo-ossz.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(VERSION).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return; // never cache POST forms/CSRF

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return; // same-origin only
    // Never cache admin or dynamic API responses.
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) return;

    // 1. Static assets: stale-while-revalidate.
    //    Serve the cached copy immediately, refresh it in the background so
    //    the NEXT load is guaranteed fresh (cache-first here is what wedged
    //    returning visitors on stale JS in v2).
    if (/\.(css|js|png|jpg|jpeg|webp|avif|gif|svg|ico|woff2?)$/.test(url.pathname)) {
        event.respondWith(
            caches.open(VERSION).then(async (cache) => {
                const hit = await cache.match(request);
                const network = fetch(request)
                    .then((fresh) => {
                        if (fresh.ok) cache.put(request, fresh.clone());
                        return fresh;
                    })
                    .catch(() => hit || Response.error());
                return hit || network;
            })
        );
        return;
    }

    // 2. Pages: network-first with offline fallback.
    event.respondWith(
        fetch(request)
            .then((response) => {
                if (response.ok && request.mode === 'navigate') {
                    caches.open(VERSION).then((cache) => cache.put(request, response.clone()));
                }
                return response;
            })
            .catch(async () => {
                const hit = await caches.match(request);
                if (hit) return hit;
                if (request.mode === 'navigate') {
                    const offline = await caches.match(OFFLINE_URL);
                    if (offline) return offline;
                }
                return Response.error();
            })
    );
});
