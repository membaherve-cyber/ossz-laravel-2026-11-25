/* OSSZ Collections service worker — offline shell.
 *
 * Every cached URL below is relative (or a same-origin asset), so the worker
 * works identically on localhost, osszcollection.com and any subfolder mount.
 */
const VERSION = 'ossz-v2';
// v2: force eviction of caches left by older workers after the
// scheme-aware session-cookie / login fix deploy (mirrors the Next.js
// worker's version-bump behaviour so wedged visitors get a clean slate).
const OFFLINE_URL = '/offline';

// Static assets cached on install (cache-first afterwards).
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

    // 1. Static assets: cache-first (images, css, js, icons).
    if (/\.(css|js|png|jpg|jpeg|webp|avif|gif|svg|ico|woff2?)$/.test(url.pathname)) {
        event.respondWith(
            caches.open(VERSION).then(async (cache) => {
                const hit = await cache.match(request);
                if (hit) return hit;
                try {
                    const fresh = await fetch(request);
                    if (fresh.ok) cache.put(request, fresh.clone());
                    return fresh;
                } catch (err) {
                    return hit || Response.error();
                }
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
