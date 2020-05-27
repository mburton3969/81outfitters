var cacheName = 'wkpos2.1';
var filesToCache = [];

var filesToCache = [

];

self.addEventListener('install', function(e) {
  console.log('Installing Service Worker');

});

self.addEventListener('activate', function(e) {
  console.log('Activating Service Worker');

});

self.addEventListener('fetch', function(e) {
  e.respondWith(
    caches.match(e.request).then(function(response) {
      return response || fetch(e.request);
    })
  );
});
