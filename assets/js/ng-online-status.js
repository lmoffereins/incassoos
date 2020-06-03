/**
 * Angular Online Status service module
 *
 * @since 1.0.0
 */
(function( angular ) {
	angular.module( 'ngOnlineStatus', [] )
		.service( '$online', [ '$window', '$rootScope', function( $window, $rootScope ) {
			var online = $window.navigator && $window.navigator.onLine || false;

			this.is = function() {
				return online;
			};

			$window.addEventListener( 'online', function() {
				online = true;
				$rootScope.$digest();
			}, true );

			$window.addEventListener( 'offline', function() {
				online = false;
				$rootScope.$digest();
			}, true );
		}]);
})( angular );
