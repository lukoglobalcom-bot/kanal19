// Kanał 19 – service worker: aplikacja działa jak natywna, a bez internetu pokazuje stronę z numerem 112.
const CACHE = 'k19-v2';
const OFFLINE = '/offline.html';

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll([OFFLINE, '/images/icon-192.png'])));
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))));
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;

  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).catch(() => caches.match(OFFLINE)));
    return;
  }
  // Pliki zbudowane (CSS/JS z hashem) i ikony – najpierw z pamięci
  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/')) {
    e.respondWith(caches.match(req).then((hit) => hit || fetch(req).then((res) => {
      const copy = res.clone();
      caches.open(CACHE).then((c) => c.put(req, copy));
      return res;
    })));
  }
});

// Powiadomienia SOS w pobliżu
self.addEventListener('push', (e) => {
  let d = {};
  try { d = e.data ? e.data.json() : {}; } catch (err) { d = {title: 'Kanał 19', body: e.data && e.data.text()}; }
  e.waitUntil(self.registration.showNotification(d.title || 'Kanał 19', {
    body: d.body || '', tag: d.tag, renotify: true, requireInteraction: true,
    icon: '/images/icon-192.png', badge: '/images/icon-192.png', vibrate: [300, 150, 300, 150, 300],
    data: {url: d.url || '/mapa'},
  }));
});

self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  const url = (e.notification.data && e.notification.data.url) || '/mapa';
  e.waitUntil(self.clients.matchAll({type: 'window', includeUncontrolled: true}).then((list) => {
    for (const c of list) { if ('focus' in c) { c.navigate(url); return c.focus(); } }
    return self.clients.openWindow(url);
  }));
});
