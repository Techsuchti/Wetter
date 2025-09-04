const CACHE='ta-v1';
self.addEventListener('install',e=>{
  e.waitUntil(
    caches.open(CACHE).then(c=>c.addAll([
      '/','/pwa/widget.html','/assets/style.css'
    ]))
  );
});
self.addEventListener('fetch',e=>{
  e.respondWith(caches.match(e.request).then(r=>r||fetch(e.request)));
});
// Push-Nachricht
self.addEventListener('push',e=>{
  const data=e.data.json();
  self.registration.showNotification(data.title,{
    body:data.body,
    icon:'/img/icon-192.png',
    badge:'/img/icon-96.png',
    vibrate:[200,100,200]
  });
});
// 30-min-Sync (Chrome)
self.addEventListener('periodicsync',e=>{
  if(e.tag==='tornado-update'){
    e.waitUntil(
      fetch('/backend/modules/summary.php?lat=<user>&lon=<user>')
        .then(r=>r.json())
        .then(d=>self.registration.showNotification(
          `Tornado-Risiko ${d.risk}%`,{body:d.text,icon:'/img/icon-192.png'}))
    );
  }
});