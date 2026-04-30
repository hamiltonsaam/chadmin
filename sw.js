/* ============================================================
   sw.js — Compliance Portal Service Worker
   Cache strategy:
     - App shell (CSS, JS, fonts): Cache-First (fast loads)
     - Navigation requests (PHP pages): Network-First with offline fallback
     - Everything else: Network-First, no cache
   ============================================================ */

const CACHE_VERSION = 'chadmin-v1';
const CACHE_SHELL   = 'chadmin-shell-v1';

// App shell assets — cache on install
const PRECACHE_URLS = [
  '/chadmin/views/theme/assets/css/styles.css',
  '/chadmin/views/theme/assets/js/script.js',
  '/chadmin/views/theme/layout/logo.png',
];

// ── Install: pre-cache app shell ────────────────────────────
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_SHELL).then(cache => {
      return cache.addAll(PRECACHE_URLS).catch(() => {
        // Silently continue if any asset is missing
      });
    })
  );
});

// ── Activate: delete old caches ─────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(k => k !== CACHE_VERSION && k !== CACHE_SHELL)
          .map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

// ── Fetch: route requests ────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle same-origin requests
  if (url.origin !== location.origin) return;

  // App shell assets — Cache-First
  if (
    request.destination === 'style' ||
    request.destination === 'script' ||
    request.destination === 'image' ||
    request.destination === 'font'
  ) {
    event.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
          if (!response || response.status !== 200) return response;
          const clone = response.clone();
          caches.open(CACHE_SHELL).then(cache => cache.put(request, clone));
          return response;
        });
      })
    );
    return;
  }

  // Navigation (PHP pages) — Network-First with offline fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() =>
        caches.match('/chadmin/index.php').then(r =>
          r || new Response(
            '<h1 style="font-family:sans-serif;padding:2rem">You are offline. Please reconnect to use the Compliance Portal.</h1>',
            { headers: { 'Content-Type': 'text/html' } }
          )
        )
      )
    );
    return;
  }
});
