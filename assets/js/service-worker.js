/**
 * Incassoos App Service Worker
 *
 * @package Incassoos
 * @subpackage App
 */

// See http://blog.88mph.io/2017/07/28/understanding-service-workers/

// In script
if ( navigator.serviceWorker ) {
	navigator.serviceWorker.register( 'service-worker.js', { scope: '/incassoos' } )
		.then( function( resp ) {
			console.log( resp );
		})
		.catch( function( err ) {
			console.log( err );
		});
}

var cacheName  = 'incassoos-app-01',
    cachePaths = [
    	'/',

    ];

// Install/update event
self.addEventListener( 'install', function( event ) {

	// Process this before resolving the event
	event.waitUntill( function() {

		// Cache files
		// `caches` is a global CacheStorage object
		caches.open( cacheName ).then( function( cache ) {
			return cache.addAll( cachePaths );
		});
	});
});

// Fetch event. Intercepting requests
self.addEventListener( 'fetch', function( event ) {

	// Return what to fetch
	event.respondWith(
		// Open our cache
		caches.open( cacheName )
			.then( function( cache ) {
				// Check whether this request is in the cache
				return cache.match( event.request );
			})
			.then( function( resp ) {
				// Return the cached data or fetch it over the network
				return resp || fetch( event.request );
			})
	);
});
