/**
 * Incassoos Web-App script
 *
 * @package Incassoos
 * @subpackage Web-App
 */
(function( angular, $, _ ){
	var getCurrentUser, getIndexBy, getConsumersByGroup, sumProductAmount, sumProductPrice, triggerElementChanged, __consumerNone = {
		id: 0,
		name: 'Select Consumer',
		avatar: 'https://www.gravatar.com/avatar/?d=mm&f=y',
		group: false
	};

	// App init
	angular.module( 'incassoos', [
		'ngOnlineStatus'
		// 'as.sortable' // ng-sortable/
	] )

	// Run setup
	.run( ['$rootScope', '$log', function( $rootScope, $log ) {

		// Bind $log to the rootscope
		$rootScope.$log = $log;
	}])

	// Form controller
	.controller( 'AppController', [ '$scope', '$http', '$window', '$timeout', '$online', function( $scope, $http, $window, $timeout, $online ) {
		var $appForm, $receiptConsumer, $receiptProducts, $editProduct;

		// Offline notice
		$scope.$watch( function() {
			return $online.is();
		}, function( val, _val ) {
			if ( val !== _val ) {
				$('body').toggleClass( 'offline', ! val );
			}
		});

		// Setup app collection globals
		$scope.consumers = $scope.groups = [];
		$scope.products = Array.apply(null, {length: 30}).map(Number.call, Number);

		/**
		 * Return the Product object with parsed default properties
		 *
		 * @param  {Object} p Input product
		 * @return {Object}   Parsed product
		 */
		function parseProduct( p ) {
			var hex;

			// Parse hex to rgb
			if ( p.color ) {
				hex = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(p.color);
				p.color = hex ? [parseInt(hex[1], 16), parseInt(hex[2], 16), parseInt(hex[3], 16)] : false;
			}

			return _.defaults( p, {
				name:      'Unknown',
				price:     0,
				color:     false,
				_color:    p.color ? 'rgba(' + p.color.join() + ',.2)' : false,
				getColor:  function() {
					return this.color ? 'rgb(' + this.color.join() + ')' : false;
				},
				timestamp: Date.now(),
				createdBy: getCurrentUser(),
				_edit:     {},
				isEdited:  false
			});
		}

		/**
		 * Overload Array.push on the product collection to ensure each
		 * product's structure.
		 *
		 * @return {Array} Result of Array.prototype.push()
		 */
		$scope.products.push = function() {
			return Array.prototype.push.apply(this, _.map( arguments, parseProduct ));
		};

		/**
		 * Overload Array.unshift on the product collection to ensure each
		 * product's structure.
		 *
		 * @return {Array} Result of Array.prototype.unshift()
		 */
		$scope.products.unshift = function() {
			return Array.prototype.unshift.apply(this, _.map( arguments, parseProduct ));
		};

		/**
		 * Return whether the screen is smaller than 600 pixels
		 *
		 * @return {Boolean} A small screen is used
		 */
		$scope.isLargeScreen = function() {
			return window.innerWidth >= 600;
		};

		/** Setup *************************************************************/

		/**
		 * Reset the current consumer and group app globals to None
		 *
		 * @return {void}
		 */
		$scope.resetConsumer = function() {
			$scope.consumer = __consumerNone;
			$scope.group    = __consumerNone.group;
		};
		$scope.resetConsumer();

		/**
		 * Fetch consumer and product data remotely
		 */
		$http.get( '../../assets/js/dummy-data.json' )
			.then( function( resp ) {

				// Append custom consumer profiles
				resp.data.consumers.push( {
					id: 999999,
					name: 'Guest',
					isEditable: false
				}, {
					id: 999998,
					name: 'On the House',
					isEditable: false
				} );

				// Setup default data
				_.each( resp.data.consumers, function( consumer ) {
					consumer.avatar     = consumer.avatar || __consumerNone.avatar;
					consumer.show       = ( typeof consumer.show !== 'undefined' ) ? !! consumer.show : true;
					consumer.group      = consumer.group || false;
					consumer.limit      = consumer.limit > 0 ? consumer.limit : false;
					consumer.isEditable = ( typeof consumer.isEditable !== 'undefined' ) ? !! consumer.isEditable : true;
				});

				// Store data in app collection globals
				$scope.occasions     = resp.data.occasions;
				$scope.occasionTypes = resp.data.occasionTypes;
				$scope.consumers     = resp.data.consumers;
				$scope.groups   = _.sortBy( _.uniq( resp.data.consumers.map( function( u ) { return u.group; }), function( u ) { return u.id; }), 'id' ).reverse();				
				$scope.products = _.map( resp.data.products, function( v ) {
					return parseProduct( _.defaults( v, {
						timestamp: false,
						createdBy: false
					}) );
				});

				// Define page elements
				$appForm         = $('#app-input-form');
				$receiptConsumer = $appForm.find('#receipt-consumer');
				$receiptProducts = $appForm.find('#receipt-products');
				$editProduct     = $appForm.find('#product-details');

				// Remove loading status
				$('body').removeClass('loading');
				$('#select-consumers').removeClass('loading');

				// Occasion Selector datepicker
				if ( 'function' === typeof $.fn.datepicker ) {
					var $occasionDate = $('#new-occasion-datepicker'),
					    $toggler = $('#new-occasion-date');

					$toggler.on( 'click', function() {
						$occasionDate.toggleClass('closed');
						// Disabling tabbing over closed datepicker anchors is somewhat
						// difficult because the entire date table is rebuilt on select
						// and therefore do not equal the anchors in `$occasionDate`.
					});

					$('<span></span>').appendTo( $occasionDate ).datepicker({
						dateFormat: 'dd/mm/yy',
						onSelect: function( dateStr ) {
							$scope.$apply( function() {
								$scope.occasion.date = dateStr;
								$toggler.click().focus();
							});
						}
					});
				}

				$scope.toggleOccasionSelector();

			}).catch( function( err ) {
				console.log( err );
			});

		/**
		 * Return whether we're viewing the Home screen
		 *
		 * @return {Boolean} We're viewing
		 */
		$scope.isHome = function() {
			return ! $scope.shouldShowConsumers() && ! $scope.viewingReceipt() && ! $scope.viewingOrders();
		};

		/**
		 * Return whether the given event was triggered on a list group
		 *
		 * @param  {Object}  event Event data
		 * @return {Boolean}       Triggered on a list group
		 */
		function eventOnListGroup( event ) {
			return event && event.originalEvent && angular.element(event.target).parents('ol.list-group').length;
		}

		/** About *************************************************************/

		$scope.viewingAbout = false;

		/**
		 * Toggle the opening of the About window
		 *
		 * @param  {Object} event Event data
		 * @return {void}
		 */
		$scope.toggleAbout = function( event ) {

			// Bail when event data was passed and a form element was clicked
			if ( eventOnListGroup( event ) ) {
				return;
			}

			$scope.viewingAbout = ! $scope.viewingAbout;
		};

		/**
		 * Return whether the About window should be disabled
		 *
		 * @return {Boolean} Disable about
		 */
		$scope.disableAbout = function() {
			return $scope.viewingOccasionSelector || $scope.viewingConsumer() || $scope.viewingProduct() || $scope.shouldShowOrder();
		};

		/** Occasion **********************************************************/

		/**
		 * Return the date formatted as dd/mm/yyyy
		 *
		 * @param  {Object} date Optional. Date object. Defaults to now.
		 * @return {String}      Formatted date
		 */
		function dateFormat( date ) {
			date = date || new Date();

			var dd = date.getDate(), mm = date.getMonth() + 1;
			dd = dd < 10 ? '0' + dd : dd;
			mm = mm < 10 ? '0' + mm : mm;

			// Return dd/mm/yyyy
			return dd + '/' + mm + '/' + date.getFullYear();
		}

		$scope.viewingOccasionSelector = false;
		$scope.currentOccasion = {};
		$scope.occasion = {
			_new: true,
			title: 'Sociëteit',
			date: dateFormat(),
			type: '1', // Match select-option value
			id: '0' // Match select-option value
		};

		/**
		 * Return whether the current Occasion is loaded
		 *
		 * @return {Boolean} Occasion is loaded
		 */
		$scope.haveOccasion = function() {
			return $scope.currentOccasion.id;
		};

		/**
		 * Toggle the Occasion Selector into view
		 *
		 * @return {void}
		 */
		$scope.toggleOccasionSelector = function() {
			$scope.viewingOccasionSelector = ! $scope.viewingOccasionSelector;
		};

		/**
		 * Register whether a new or existing Occasion will be set by
		 * hooking into the Occasion tab state change trigger.
		 *
		 * @param  {Object} event Event data
		 * @param  {String} state Tab state
		 * @return {void}
		 */
		$scope.$on( 'state-triggered-occasion', function( event, state ) {

			// Run digest cycle bc it's not triggered just by the change
			$scope.$apply( function() {
				$scope.occasion._new = ( state === 'create' );
			});
		});

		/**
		 * Return whether the Occasion Selector cannot be submitted
		 *
		 * @return {Boolean} Is submission disabled?
		 */
		$scope.occasionDisabled = function() {
			return ( $scope.occasion._new && ( ! +$scope.occasion.type || ! $scope.occasion.date ) )
				|| ( ! $scope.occasion._new && ! +$scope.occasion.id );
		};

		/**
		 * Handle form submit action for the Occasion Selector
		 *
		 * @return {void}
		 */
		$scope.submitOccasion = function() {
			var res, title;

			// Set loading status
			$('body').addClass('loading');

			// Create occasion
			if ( $scope.occasion._new ) {
				title = _.find( $scope.occasionTypes, function( t ) { return t.id === +$scope.occasion.type }).name;
				title += ' (' + $scope.occasion.date + ')';
				res = { id: 4, title: { rendered: title } };

			// Load existing occasion orders
			} else {
				title = _.find( $scope.occasions, function( o ) { return o.id === +$scope.occasion.id; }).name;
				res = { id: +$scope.occasion.id, title: { rendered: title } };
			}

			// Setup current occasion
			$scope.currentOccasion.id   = res.id;
			$scope.currentOccasion.name = res.title.rendered;

			console.log( res );

			// Remove loading status after n ms
			$timeout( function() {

				$scope.toggleOccasionSelector();
				$('body').removeClass('loading');
			}, 1000 );
		};

		/** Consumer ******************************************************/

		/**
		 * Return whether to show the consumer
		 *
		 * @param  {Object}  consumer Consumer object
		 * @return {Boolean}      Show the consumer
		 */
		$scope.showConsumer = function( consumer ) {

			/**
			 * In the settings mode always show the consumer, else depend on
			 * the `show` property.
			 */
			return $scope.inSettingsMode() || consumer.show;
		};

		/**
		 * Set a consumer to the current selected consumer
		 *
		 * @param  {Object} consumer Consumer object
		 * @return {void}
		 */
		$scope.selectConsumer = function( consumer ) {

			// When in Settings mode, toggle show status
			if ( $scope.inSettingsMode() ) {
				consumer.show = ! consumer.show;
				return;
			}

			$scope.consumer = consumer;
			$scope.group = consumer.group;

			/**
			 * Assume that when the selection flow is `products > consumer`, the next
			 * interaction is to submit the Receipt. So open the Receipt view.
			 */
			if ( $scope.haveReceipt() ) {
				$scope.toggleReceipt( true );
			}

			$scope.closePanel();
		};

		/**
		 * Listen for changes in the selected consumer
		 *
		 * @return {void}
		 */
		$scope.$watch( 'consumer', function() {
			triggerElementChanged( $receiptConsumer );
		});

		/**
		 * Return whether the/a consumer is selected
		 *
		 * @param  {Object}  consumer Optional. Consumer object. Defaults to the current consumer.
		 * @return {Boolean}      Consumer is selected.
		 */
		$scope.isConsumerSelected = function( consumer ) {

			// Default to the current consumer
			if ( typeof consumer === 'undefined' ) {
				return ! _.isEmpty( $scope.consumer ) && $scope.isConsumerSelected( $scope.consumer );
			}

			// Bail when consumer None is selected
			if ( __consumerNone === $scope.consumer ) {
				return false;
			}

			return $scope.consumer === consumer;
		};

		/**
		 * Return whether the/a group is selected
		 *
		 * Used for group-based consumer filtering
		 *
		 * @param  {Object}  group Optional. Group object. Defaults to the current group.
		 * @return {Boolean}       Group is selected.
		 */
		$scope.isGroupSelected = function( group ) {

			// Default to the current group
			if ( typeof group === 'undefined' ) {
				return $scope.group && $scope.isGroupSelected( $scope.group );
			}

			// Bail when $scope is a flat group
			if ( ! group ) {
				return false;
			}

			return $scope.group === group;
		};

		/**
		 * Return whether the consumer has no group
		 *
		 * @param  {Object}  consumer Optional. Consumer object. Defaults to the current consumer.
		 * @return {Boolean}      Consumer has no group.
		 */
		$scope.isConsumerGroupless = function( consumer ) {
			if ( typeof consumer === 'undefined' ) {
				consumer = $scope.consumer;
			}

			return ! consumer.group || consumer.group.isFlat;
		};

		/** Consumer Panels ***************************************************/

		// Setup consumer panel globals
		$scope.consumerPanel = '';

		/**
		 * Toggle the consumer panel for the current context
		 *
		 * @return {void}
		 */
		$scope.togglePanel = function() {

			// Bail when in Settings mode and viewing a current product
			if ( $scope.inSettingsMode() && $scope.viewingProduct() ) {
				return;
			} else if ( $scope.shouldShowConsumers() ) {
				// Close settings as well
				( $scope.haveReceipt() || $scope.isLargeScreen() ) && $scope.closeSettingsMode();
				$scope.consumerPanel = '';

			} else if ( $scope.isGroupSelected() ) {
				$scope.toggleConsumersPanel();
			} else {
				$scope.toggleGroupsPanel();
			}
		};

		/**
		 * Open the consumer panel
		 *
		 * @return {void}
		 */
		$scope.openPanel = function() {
			! $scope.shouldShowConsumers() && $scope.togglePanel();
		};

		/**
		 * Close the consumer panel
		 *
		 * @return {void}
		 */
		$scope.closePanel = function() {
			$scope.shouldShowConsumers() && $scope.togglePanel();
		};

		/**
		 * Toggle the Consumers consumer panel
		 *
		 * @return {void}
		 */
		$scope.toggleConsumersPanel = function() {

			// Toggle groups instead when a flat group/consumer is selected
			if ( $scope.group.isFlat ) {
				$scope.toggleGroupsPanel();
				return;
			}

			$scope.consumerPanel = ( $scope.consumerPanel !== '_consumers' ? '_consumers' : '' );
		};

		/**
		 * Toggle the Groups consumer panel
		 *
		 * @return {void}
		 */
		$scope.toggleGroupsPanel = function() {
			$scope.consumerPanel = ( $scope.consumerPanel !== '_groups' ? '_groups' : '' );
		};

		/**
		 * Return whether we're viewing the Consumers consumer panel
		 *
		 * @return {Boolean} We're viewing
		 */
		$scope.isConsumersPanel = function() {
			return $scope.shouldShowConsumers( '_consumers' );
		};

		/**
		 * Return whether we're viewing the Groups consumer panel
		 *
		 * @return {Boolean} We're viewing
		 */
		$scope.isGroupsPanel = function() {
			return $scope.shouldShowConsumers( '_groups' );
		};

		/**
		 * Return whether we're viewing the consumer panel
		 *
		 * @param  {String}  type Optional. Consumer panel type. Defaults to any panel.
		 * @return {Boolean}      We're viewing
		 */
		$scope.shouldShowConsumers = function( type ) {

			// Default to being just open
			if ( typeof type === 'undefined' ) {
				return $scope.consumerPanel !== '';
			}

			return $scope.consumerPanel === type;
		};

		$scope.consumerSearchOpen = false;

		/**
		 * Return whether we're searching consumers
		 *
		 * @return {Boolean} We're searching
		 */
		$scope.searchingConsumers = function() {
			return $scope.consumerSearchOpen;
		};

		/**
		 * Toggle focus on the consumer search input field
		 *
		 * @param  {Object|Boolean} $event Event object data or force open (true) or close (false).
		 */
		$scope.toggleConsumerSearch = function( $event ) {

			// Toggle it
			if ( 'boolean' !== typeof $event ) {
				$event.preventDefault();
				$scope.consumerSearchOpen = ! $scope.consumerSearchOpen;
			} else {
				$scope.consumerSearchOpen = !! $event;
			}

			// 
			if ( $scope.consumerSearchOpen ) {
				$timeout( function() {
					$event.target.previousElementSibling.focus();
				});
			} else {
				$event.target && $event.target.previousElementSibling.blur();
				$scope.consumerSearch = void 0;
			}
		};

		/** Products **********************************************************/

		// Setup receipt globals
		$scope.receipt = [];
		$scope.receiptScreen = false;

		/**
		 * Add a single count to the Receipt's product
		 *
		 * @param  {Object} product Product object
		 * @return {void}
		 */
		$scope.addProductToReceipt = function( product ) {

			// When in Settings mode, toggle current product
			if ( $scope.inSettingsMode() ) {
				$scope.toggleCurrentProduct( product );
				return;
			}

			$scope.bumpProductOnReceipt( product, 1 );
		};

		/**
		 * Subtract a single count from the Receipt's product
		 *
		 * @param  {Object} product Product object
		 * @return {void}
		 */
		$scope.subtractProductFromReceipt = function( product ) {
			$scope.bumpProductOnReceipt( product, -1 );
		};

		/**
		 * Bump product count on the Receipt
		 *
		 * @param  {Object} product Product object
		 * @param  {Number} bump    Optional. Value to bump with. Defaults to 1.
		 * @return {void}
		 */
		$scope.bumpProductOnReceipt = function( product, bump ) {
			var i = getIndexBy( product, $scope.receipt );

			// Parse bump value
			bump = bump && parseInt( bump, 0 ) || 1;

			// Increment when already added
			if ( -1 !== i ) {
				$scope.receipt[ i ].amount += bump;

				// Remove when 0
				if ( 0 === $scope.receipt[ i ].amount ) {
					$scope.receipt.splice( i, 1 );
				}

			// Add new
			} else {
				product.amount = bump;
				$scope.receipt.unshift( product ); // Prepend to receipt list
			}
		};

		/**
		 * Listen for changes in the selected receipt products collection
		 *
		 * By default, only the set of items is watched, not the values in the
		 * collection themselves. Using the `json` format filter enables to listen
		 * for changes in the item properties, like changing product amounts.
		 *
		 * @return {void}
		 */
		$scope.$watchCollection( 'receipt | json', function() {

			// Only change element when the list is not empty
			if ( 0 !== $scope.receipt.length ) {
				triggerElementChanged( $receiptProducts );
			}
		});

		/**
		 * Return whether the product is selected (in the Receipt)
		 *
		 * @param  {Object}  product Product object
		 * @param  {Array}   list    Optional. Array of products. Defaults to the Receipt
		 * @param  {String}  key     Optional. Product key to match for. Defaults to `$$hashKey`.
		 * @return {Boolean}         Product is selected in the list
		 */
		$scope.isProductSelected = function( product, list, key ) {
			list = list || $scope.receipt;
			return -1 !== getIndexBy( product, list, key );
		};

		/**
		 * Return the product amount that is selected (in the Receipt)
		 *
		 * @param  {Object} product Product object
		 * @param  {Array}  list    Optional. Array of products. Defaults to the Receipt.
		 * @param  {String} key     Optional. Product key to match for. Defaults to `$$hashKey`.
		 * @return {Number}         Product amount that is selected in the list
		 */
		$scope.getProductSelected = function( product, list, key ) {
			list = list || $scope.receipt;
			return $scope.isProductSelected( product, list, key ) && list[ getIndexBy( product, list, key ) ].amount || 0;
		};

		/**
		 * Return the total amount of all selected products in the Receipt
		 *
		 * @return {Number} Total product amount
		 */
		$scope.getReceiptAmount = function() {
			return sumProductAmount( $scope.receipt );
		};

		/**
		 * Return the total price of all selected products in the Receipt
		 *
		 * @return {Float} Total product price
		 */
		$scope.getReceiptPrice = function() {
			return sumProductPrice( $scope.receipt );
		};

		/**
		 * Return whether there are no selected products in the Receipt
		 *
		 * @return {Boolean} Receipt is empty
		 */
		$scope.isReceiptEmpty = function() {
			return _.isEmpty( $scope.receipt ) || _.isEmpty( _.filter( $scope.receipt, function( r ) { return 0 !== r.amount; }) );
		};

		/**
		 * Return whether there are selected products in the Receipt
		 *
		 * @return {Boolean} Receipt is not empty
		 */
		$scope.haveReceipt = function() {
			return ! $scope.isReceiptEmpty();
		};

		/**
		 * Return whether we're viewing the Receipt
		 *
		 * @return {Boolean} We're viewing
		 */
		$scope.viewingReceipt = function() {

			// Close receipt without products
			if ( $scope.isReceiptEmpty() && $scope.receiptScreen ) {
				$scope.toggleReceipt( false );
			}

			// On larger screens, receipt is always visible when products are selected
			if ( $scope.haveReceipt() && $scope.isLargeScreen() ) {
				return true;
			}

			return $scope.receiptScreen;
		};

		/**
		 * Toggle the Receipt into view
		 *
		 * @param  {Boolean} force Optional. Whether to force open (true) or close (false) the Receipt.
		 * @return {void}
		 */
		$scope.toggleReceipt = function( force ) {

			// Close consumer panel when open
			if ( $scope.shouldShowConsumers() ) {
				$scope.closePanel();
				return;
			}

			$scope.receiptScreen = ( typeof force !== 'undefined' ) ? !! force : ! $scope.receiptScreen;
		};

		/**
		 * Clear the product selections and close the current Receipt
		 *
		 * @return {void}
		 */
		$scope.resetReceipt = function() {
			$scope.receipt = [];
			$scope.receiptScreen = false;
		};

		/**
		 * Clear all current selections
		 *
		 * @return {void}
		 */
		$scope.resetSelections = function() {
			$scope.resetReceipt();
			$scope.resetConsumer();
			$scope.toggleConsumerSearch( false );
		};

		/**
		 * Register a new order from the current Receipt selections
		 *
		 * @return {void}
		 */
		$scope.submitReceipt = function() {
			var order = _.defaults( _.pick( $scope.consumer, 'id', 'name' ), { products: [] } );

			// Grab all selected products
			_.each( $scope.receipt, function( r ) {
				order.products.push( _.pick( r, 'id', 'name', 'amount', 'price' ) );
			});

			// Create new Order
			$scope.orders.push( order );

			// Clear the Receipt
			$scope.resetSelections();
		};

		/** Product Details ***************************************************/

		$scope.currentProduct = { _edit: {} };

		/**
		 * Toggle the selected Product into current view
		 *
		 * @param  {Object} product Product details or event data
		 * @return {void}
		 */
		$scope.toggleCurrentProduct = function( product ) {

			// Bail when event data was passed and a form element was clicked
			if ( eventOnListGroup( product ) ) {
				return;
			}

			if ( 'undefined' === typeof product || product.originalEvent ) {
				// Empty edit scope of the product
				$scope.currentProduct._edit = {};
				// Remove current product entirely
				$scope.currentProduct = { _edit: {} };
			} else {
				$scope.currentProduct = product;

				$scope.currentProduct._edit.name = product.name;
				$scope.currentProduct._edit.price = product.price;
			}
		};

		/**
		 * Return wheter we're editing or creating a product
		 *
		 * @return {Boolean} Are we viewing a product?
		 */
		$scope.viewingProduct = function() {
			return $scope.editingProduct() || $scope.creatingProduct();
		};

		/**
		 * Return whether we're editing an existing product
		 *
		 * @return {Boolean} Editing an existing product
		 */
		$scope.editingProduct = function() {
			return ! $scope.currentProduct.isNew && $scope.currentProduct.$$hashKey;
		};

		/**
		 * Return whether the current product is updateble
		 *
		 * @return {Boolean} Product is updateble
		 */
		$scope.editingCurrentProduct = function() {
			return ! _.isEmpty( $scope.currentProduct._edit.name ) && ( 'undefined' === typeof $scope.currentProduct._edit.price || ! _.isEmpty( $scope.currentProduct._edit.price ) );
		};

		/**
		 * Return whether we're creating a new product
		 *
		 * @return {Boolean} Creating a new product
		 */
		$scope.creatingProduct = function() {
			return $scope.currentProduct.isNew && ! $scope.currentProduct.$$hashKey;
		};

		/**
		 * Construct context for new product creation
		 *
		 * @return {void}
		 */
		$scope.setupNewProduct = function() {
			$scope.currentProduct.isNew = true;
			$scope.currentProduct._edit = {
				name: 'New Product',
				price: null
			};

			// Focus on first input field
			if ( ! $window.getSelection().toString() ) {
				/**
				 * Wrapped in a $timeout call to execute after the following $digest cycle,
				 * which fires the visibility of #product-details. Before that, the hidden
				 * state of the fields block the focus trigger.
				 */
				$timeout( function() {
					$editProduct.find(':input').first().focus();
				});
			}
		};

		/**
		 * Add the created product to the product queue
		 *
		 * @return {void}
		 */
		$scope.saveCurrentProduct = function() {

			// When in Edit mode
			if ( $scope.editingProduct() ) {
				$scope.currentProduct.name  = $scope.currentProduct._edit.name;
				$scope.currentProduct.price = $scope.currentProduct._edit.price;

			// When in Create mode
			} else {

				// Append product to queue
				$scope.products.push({
					name:  $scope.currentProduct._edit.name,
					price: $scope.currentProduct._edit.price
				});
			}

			// Reset the current product
			$scope.toggleCurrentProduct();
		};

		/** Consumer Details **************************************************/

		$scope.currentConsumer = { _edit: {} };

		/**
		 * Toggle the selected Consumer into current view
		 *
		 * @param  {Object} consumer Consumer details or event data
		 * @return {void}
		 */
		$scope.toggleCurrentConsumer = function( consumer ) {

			// Bail when event data was passed and a form element was clicked
			if ( eventOnListGroup( consumer ) || ( $scope.inSettingsMode() && consumer && ! consumer.isEditable ) ) {
				return;
			}

			if ( 'undefined' === typeof consumer || consumer.originalEvent ) {
				// Empty edit scope of the consumer
				$scope.currentConsumer._edit = {};
				// Remove current consumer entirely
				$scope.currentConsumer = { _edit: {} };
			} else {
				$scope.currentConsumer = consumer;
				$scope.currentConsumer.products = $scope.currentConsumerGetProducts();

				$scope.currentConsumer._edit = {};
				$scope.currentConsumer._edit.limit = consumer.limit;
				$scope.currentConsumer._edit.show = consumer.show;
			}
		};

		/**
		 * Return wheter we're editing or creating a consumer
		 *
		 * @return {Boolean} Are we viewing a consumer?
		 */
		$scope.viewingConsumer = function() {
			return $scope.currentConsumer.$$hashKey;
		};

		/**
		 * Return whether the current consumer is updateble
		 *
		 * @return {Boolean} Consumer is updateble
		 */
		$scope.editingCurrentConsumer = function() {
			return ( $scope.currentConsumer._edit.limit !== $scope.currentConsumer.limit || $scope.currentConsumer._edit.show !== $scope.currentConsumer.show );
		};

		/**
		 * Add the created consumer to the consumer queue
		 *
		 * @return {void}
		 */
		$scope.saveCurrentConsumer = function() {

			// When in Edit mode
			if ( $scope.editingCurrentConsumer() ) {
				$scope.currentConsumer.limit = $scope.currentConsumer._edit.limit;
				$scope.currentConsumer.show  = $scope.currentConsumer._edit.show;
			}

			// Reset the current consumer
			$scope.toggleCurrentConsumer();
		};

		/**
		 * Return the list of total consumer's registered orders
		 *
		 * @return {Array} Consumer's total products
		 */
		$scope.currentConsumerGetProducts = function() {
			var products = [], i;

			if ( $scope.viewingConsumer() ) {
				_.each( $scope.orders, function( c ) {

					// Skip when not the consumer's order
					if ( c.consumerId !== $scope.currentConsumer.id ) {
						return;
					}

					// Extract order products
					_.each( c.products, function( p ) {
						i = getIndexBy( p, products, 'name', 'price' );

						if ( -1 !== i ) {
							products[ i ].amount += p.amount;
						} else {
							products.push( angular.copy( p ) );
						}
					});
				});
			}

			return products;
		};

		/**
		 * Return the total value of the consumer's registered orders
		 *
		 * @return {Float} Consumer's total order value
		 */
		$scope.currentConsumerGetTotal = function() {
			return sumProductPrice( $scope.currentConsumer.products );
		};

		/** Orders ******************************************************/

		// Setup order globals
		$scope.orders = [];
		$scope.ordersScreen = false;
		$scope.currentOrder = { _edit: {} };
		$scope.editingOrder = false;
		$scope.editingOrderConsumer = false;
		$scope.editOrderThreshold = 1000 * 60 * 1; // 1 minute

		/**
		 * Overload Array.push on the order collection to ensure each
		 * order's structure.
		 *
		 * @return {Array} Result of Array.prototype.push()
		 */
		$scope.orders.push = function() {
			return Array.prototype.push.apply( this, _.map( arguments, function( t ) {
				return {
					consumerId:   t.consumerId || t.id || 0,
					consumerName: t.consumerName || t.name || 'Unknown',
					timestamp:    Date.now(),
					createdBy:    getCurrentUser(),
					products:     t.products || [],
					_edit:        {},
					isEdited:     t.isEdited || false
				};
			}) );
		};

		/**
		 * Return the count of registered orders
		 *
		 * @return {Number} Order count
		 */
		$scope.getOrdersCount = function() {
			return $scope.orders.length || undefined;
		};

		/**
		 * Toggle the Order History into view
		 *
		 * @return {void}
		 */
		$scope.toggleOrders = function( force ) {

			// Bail toggling for large screens
			if ( $scope.isLargeScreen() ) {
				return;
			}

			force || ( force = true );
			$scope.ordersScreen = ! $scope.ordersScreen;

			// Close current order
			if ( ! $scope.ordersScreen ) {
				$scope.toggleCurrentOrder();
			}
		};

		/**
		 * Return whether we're viewing the Order History
		 *
		 * @return {Boolean} We're viewing
		 */
		$scope.viewingOrders = function() {
			return !! $scope.ordersScreen;
		};

		/**
		 * Return whether any order has been registered
		 *
		 * @return {Boolean} Orders are registered
		 */
		$scope.haveOrders = function() {
			return ! _.isEmpty( $scope.orders );
		};

		/**
		 * Toggle the selected Order into current view
		 *
		 * @param  {Object} order Order object or event data
		 * @return {void}
		 */
		$scope.toggleCurrentOrder = function( order ) {

			// Bail when editing an order or it was deleted or it is an edit
			if ( $scope.editingCurrentOrder() ) {
				return;
			}

			// Bail when event data was passed and a form element was clicked
			if ( order && order.originalEvent && angular.element(order.target).parents('ol.list-group').length ) {
				return;
			}

			if ( typeof order === 'undefined' || order.originalEvent ) {
				$scope.currentOrder = { _edit: {} };
			} else {
				$scope.currentOrder = order;
			}
		};

		/**
		 * Return whether we're currently viewing an Order
		 *
		 * @return {Boolean} We're viewing
		 */
		$scope.shouldShowOrder = function() {
			return ( 'consumerId' in $scope.currentOrder );
		};

		/**
		 * Return whether we're editing the current Order
		 *
		 * @param  {Boolean} strict Optional. Whether to check for actual edits.
		 * @return {Boolean}        We're editing
		 */
		$scope.editingCurrentOrder = function( strict ) {
			strict = !! ( strict || false );
			strict = strict && _.isEmpty( $scope.currentOrder._edit.products ) && _.isEmpty( $scope.currentOrder._edit.consumer );

			return !! $scope.editingOrder && ! strict;
		};

		/**
		 * Determine whether the Order is editable.
		 *
		 * @param  {Object} order Optional. Order object with timestamp. Defaults to the current Order's timestamp.
		 * @return {Boolean} Order is editable
		 */
		$scope.isOrderEditable = function( order ) {
			order = order || $scope.currentOrder;

			return Math.abs( Date.now() - order.timestamp ) < $scope.editOrderThreshold;
		};

		/**
		 * Toggle the current Order for editing
		 *
		 * @param  {Boolean} force Optional. Whether to force open (true) or close (false) the panel.
		 * @return {void}
		 */
		$scope.toggleEditCurrentOrder = function( force ) {

			// (Re)set the edit property
			$scope.currentOrder._edit = {};

			// Force close the edit consumer panel
			$scope.toggleOrderConsumerPanel( false );

			// Toggle the Edit mode
			$scope.editingOrder = ( typeof force !== 'undefined' ) ? !! force : ! $scope.editingOrder;
		};

		/**
		 * Return whether the product should be shown in the current Order
		 *
		 * @param  {Object}  product Product object
		 * @return {Boolean}         Show the product
		 */
		$scope.showOrderProduct = function( product ) {
			return ! $scope.editingCurrentOrder() ? ( -1 !== getIndexBy( product, $scope.currentOrder.products || [], 'name' ) ) : true;
		};

		/**
		 * Add a single count to the current Order's product
		 *
		 * @param  {Object} product Product object
		 * @return {void}
		 */
		$scope.addProductToOrder = function( product ) {
			if ( $scope.editingCurrentOrder() ) {
				$scope.bumpProductOnOrder( product, 1 );
			}
		};

		/**
		 * Subtract a single count from the current Orders's product
		 *
		 * @param  {Object} product Product object
		 * @return {void}
		 */
		$scope.subtractProductFromOrder = function( product ) {
			if ( $scope.editingCurrentOrder() ) {
				$scope.bumpProductOnOrder( product, -1 );
			}
		};

		/**
		 * Bump product count on the current Order
		 *
		 * @param  {Object} product Product object
		 * @param  {Number} bump    Optional. Value to bump with. Defaults to 1.
		 * @return {void}
		 */
		$scope.bumpProductOnOrder = function( product, bump ) {
			var products, i;

			// Ensure default products property
			_.defaults( $scope.currentOrder._edit, { products: [] });

			// Assign vars
			products = $scope.currentOrder._edit.products,
			i = getIndexBy( product, products, 'name' );

			// Parse bump value
			bump = bump && parseInt( bump, 0 ) || 1;

			// Increment when already added
			if ( -1 !== i ) {
				products[ i ].amount += bump;

				// Remove when 0
				if ( 0 === products[ i ].amount ) {
					products.splice( i, 1 );
				}

			// Add new
			} else {

				// Setup editable product
				product = angular.copy( product );
				product.amount = bump;
				products.push( product );
			}
		};

		/**
		 * Return the products to list in the current Order screen
		 *
		 * @return {Array} List of products
		 */
		$scope.getOrderProducts = function() {
			return $scope.editingCurrentOrder() ? $scope.products : $scope.currentOrder.products;
		};

		/**
		 * Return the product amount of the current Order's product
		 *
		 * @param  {Object} product Product object
		 * @return {Number}         Selected product amount
		 */
		$scope.getOrderProductAmount = function( product ) {
			return $scope.getProductSelected( product, $scope.currentOrder.products, 'name' ) + $scope.getProductSelected( product, $scope.currentOrder._edit.products, 'name' );
		};

		/**
		 * Return the total product amount of the (current) Order
		 *
		 * @param  {Object} order Optional. Order object. Defaults to the current Order.
		 * @return {Number}             Total product amount
		 */
		$scope.sumOrderAmount = function( order, withEdit ) {
			order = order || $scope.currentOrder;
			withEdit = withEdit || false;
			return sumProductAmount( order.products ) + ( withEdit ? sumProductAmount( order._edit.products ) : 0 );
		};

		/**
		 * Return the total product price of the (current) Order
		 *
		 * @param  {Object} order Optional. Order object. Defaults to the current Order.
		 * @return {Float}              Total product price
		 */
		$scope.sumOrderPrice = function( order ) {
			order = order || $scope.currentOrder;
			return sumProductPrice( order.products ) + sumProductPrice( order._edit.products );
		};

		/**
		 * Toggle the consumer panel for the current Order
		 *
		 * @param  {Boolean} force Optional. Whether to force open (true) or close (false) the panel.
		 * @return {void}
		 */
		$scope.toggleOrderConsumerPanel = function( force ) {

			// Bail when not editing the current Order
			if ( ! $scope.editingCurrentOrder() ) {
				return;
			}

			$scope.editingOrderConsumer = ( typeof force !== 'undefined' ) ? !! force : ! $scope.editingOrderConsumer;
		};

		/**
		 * Return the consumer name of the current Order
		 *
		 * @return {String} Consumer name
		 */
		$scope.getOrderConsumerName = function() {
			return ( 'consumer' in $scope.currentOrder._edit ) && $scope.currentOrder._edit.consumer.name || $scope.currentOrder.consumerName;
		};

		/**
		 * Return whether the given consumer matches the current Order consumer
		 *
		 * @param  {Object}  consumer Consumer object
		 * @return {Boolean}      Consumer is selected
		 */
		$scope.isOrderConsumerSelected = function( consumer ) {
			return ! _.isEmpty( $scope.currentOrder._edit.consumer ) ? ( $scope.currentOrder._edit.consumer === consumer ) : $scope.currentOrder.consumerId === consumer.id;
		};

		/**
		 * Set the consumer edit for the current Order
		 *
		 * @param  {Object} consumer Consumer object
		 * @return {void}
		 */
		$scope.selectOrderConsumer = function( consumer ) {

			// When in Settings mode, toggle show status
			if ( $scope.inSettingsMode() ) {
				consumer.show = ! consumer.show;
				return;
			}

			$scope.currentOrder._edit.consumer = ( consumer.id !== $scope.currentOrder.consumerId ) ? consumer : {};
			$scope.toggleOrderConsumerPanel();
		};

		/**
		 * Execute an edit operation on the current order
		 *
		 * @return {void}
		 */
		$scope.editOrder = function() {
			var products = $scope.currentOrder.products;

			// Bail when nothing was edited or cannot edit
			if ( ! $scope.editingCurrentOrder( true ) || ! $scope.isOrderEditable() ) {
				return;
			}

			// Merge edits with the order's products
			_.each( $scope.currentOrder._edit.products, function( p ) {
				var i = getIndexBy( p, products, 'name' );

				// Increment when already added
				if ( -1 !== i ) {
					products[ i ].amount += p.amount;

					// Remove when 0
					if ( 0 === products[ i ].amount ) {
						products.splice( i, 1 );
					}

				// Add new
				} else {

					// Setup editable product
					p = angular.copy( p );
					p.amount = 1;
					products.push( p );
				}
			});

			// Change selected consumer
			if ( ! _.isEmpty( $scope.currentOrder._edit.consumer ) ) {
				$scope.currentOrder.consumerId = $scope.currentOrder._edit.consumer.id;
				$scope.currentOrder.consumerName = $scope.currentOrder._edit.consumer.name;
			}

			// Define edit status
			$scope.currentOrder.isEdited = true;
		};

		/** Actions ***********************************************************/

		/**
		 * Execute the logic for the primary action button
		 *
		 * @return {void}
		 */
		$scope.doActionPrimary = function() {

			// When viewing the Receipt
			if ( $scope.viewingReceipt() ) {

				// When a consumer is selected
				if ( $scope.isConsumerSelected() ) {

					// Submit the Receipt
					$scope.submitReceipt();
				} else {
					
					// Open consumer panel
					$scope.toggleConsumersPanel();
				}

			// When viewing the current Order
			} else if ( $scope.shouldShowOrder() ) {

				// When in Edit mode
				if ( $scope.editingCurrentOrder( true ) ) {

					// Perform edit
					$scope.editOrder();

					// Close Edit mode
					$scope.toggleEditCurrentOrder();

				// When is editable
				} else if ( $scope.isOrderEditable() ) {

					// Open Edit mode
					$scope.toggleEditCurrentOrder();

				// Close current Order
				} else {
					$scope.toggleCurrentOrder();
				}

			// When in Settings mode
			} else if ( $scope.inSettingsMode() ) {

				// When in View/Edit product mode
				if ( $scope.viewingProduct() ) {
					$scope.saveCurrentProduct();

				// When in View/Edit consumer mode
				} else if ( $scope.viewingConsumer() ) {
					$scope.saveCurrentConsumer();

				// When in plain mode
				} else {
					$scope.setupNewProduct();
				}

			// Otherwise, toggle the Order History
			} else {

				if ( $scope.haveReceipt() ) {
					$scope.toggleReceipt();
				} else {
					$scope.toggleOrders();
				}
			}
		};

		/**
		 * Execute the logic for the secondary action button
		 *
		 * @return {void}
		 */
		$scope.doActionSecondary = function() {

			// When viewing the Order History
			if ( $scope.shouldShowOrder() ) {

				// When in Edit mode
				if ( $scope.editingCurrentOrder() ) {

					// Close Edit mode
					$scope.toggleEditCurrentOrder();

				} else {

					// Close currenet Order
					$scope.toggleCurrentOrder();
				}

			// When in Settings mode
			} else if ( $scope.inSettingsMode() ) {

				// When in Create/Edit product mode
				if ( $scope.viewingProduct() ) {
					$scope.toggleCurrentProduct();

				// When in Create/Edit consumer mode
				} else if ( $scope.viewingConsumer() ) {
					$scope.toggleCurrentConsumer();
				}

			// When the Receipt has products
			} else if ( $scope.haveReceipt() ) {

				// Cancel the Receipt
				$scope.resetSelections();
			}
		};

		/**
		 * Trigger actions on keyboard input
		 *
		 * NB: ng-keypress doesn't detect Esc or Backspace events so use ng-keydown/up
		 *
		 * @param  {Object} e Event data
		 * @return {void}
		 */
		$scope.keyboardInput = function( e ) {
			var isInput = document.activeElement && -1 !== ['input', 'textarea'].indexOf( document.activeElement.tagName.toLowerCase() );

			function actionPrimary() {

				// Bail when viewing consumers
				if ( $scope.shouldShowConsumers() ) {
					return;
				}

				$scope.doActionPrimary();
			}

			function actionSecondary() {

				// Close about window
				if ( $scope.viewingAbout ) {
					$scope.toggleAbout();
					return;
				}

				// Close consumer panel
				if ( $scope.shouldShowConsumers() ) {
					$scope.closePanel();
					return;
				}

				// Close orders panel
				if ( $scope.viewingOrders() && ! $scope.shouldShowOrder() ) {
					$scope.toggleOrders();
					return;
				}

				// Open about window
				if ( $scope.isHome() ) {
					$scope.toggleAbout();
					return;
				}

				$scope.doActionSecondary();
			}

			// Map actions to key codes
			var map = {
				// Backspace
				8: isInput || actionSecondary,
				// Enter
				13: actionPrimary,
				// Escape
				27: isInput && function() { document.activeElement.blur(); } || actionSecondary,
				// Space
				32: isInput || actionPrimary,
				// A for About
				65: isInput || function() {
					$scope.toggleAbout();
				},
				// C for Orders
				67: isInput || function() {
					// Bail when in settings mode or viewing consumers
					$scope.viewingAbout || $scope.inSettingsMode() || $scope.shouldShowConsumers() || $scope.toggleOrders();
				},
				// S for Settings
				83: isInput || function() {
					! $scope.disableSettings() && $scope.toggleSettingsMode();
				},
				// U for Consumers
				85: isInput || function() {
					// Bail when viewing orders
					$scope.viewingAbout || $scope.viewingOrders() || $scope.togglePanel();
				}
			};

			// Execute mapped action when not inside an input element
			map[ e.which ] && 'function' === typeof map[ e.which ] && map[ e.which ]();
		};

		/**
		 * Return the label text for the primary action button
		 *
		 * @return {String} Label text
		 */
		$scope.labelActionPrimary = function() {
			var label = 'History';

			// When items are selected
			if ( $scope.haveReceipt() ) {
				label = 'Process';
			}

			// When viewing the Receipt
			if ( $scope.viewingReceipt() ) {
				label = $scope.isConsumerSelected() ? 'Submit' : 'Select a Consumer';
			}

			// When in Settings mode
			if ( $scope.inSettingsMode() ) {
				label = $scope.viewingProduct() ? 'Submit' : 'Add new Product';
			}

			// When viewing the Order History
			if ( $scope.viewingOrders() ) {
				label = 'Back';
			}

			// When viewing the current Order
			if ( $scope.shouldShowOrder() ) {
				label = $scope.editingCurrentOrder() ? 'Update' : 'Edit';
			}

			return label;
		};

		/**
		 * Return the label text for the secondary action button
		 *
		 * @return {String} Label text
		 */
		$scope.labelActionSecondary = function() {
			var label = 'Clear';

			// When in Settings mode
			if ( $scope.inSettingsMode() ) {
				label = 'Cancel';
			}

			// When viewing the current Order
			if ( $scope.shouldShowOrder() ) {
				label = $scope.editingCurrentOrder() ? 'Cancel' : 'Close';
			}

			return label;
		};

		/**
		 * Return the glyphicon sub-classname for the primary action button icon
		 *
		 * @return {String} Glyphicon sub-classname
		 */
		$scope.iconActionPrimary = function() {
			var icon = 'th-list';

			if ( $scope.haveReceipt() ) {
				icon = 'chevron-right';
			}

			// When viewing the Receipt
			if ( $scope.viewingReceipt() ) {
				icon = $scope.isConsumerSelected() ? 'ok' : 'user';
			}

			// When in Settings mode
			if ( $scope.inSettingsMode() ) {
				icon = $scope.viewingProduct() ? 'ok' : 'plus';
			}

			// When viewing the Order History
			if ( $scope.viewingOrders() ) {
				icon = 'chevron-down';
			}

			// When viewing the current Order
			if ( $scope.shouldShowOrder() ) {
				icon = $scope.editingCurrentOrder( true ) ? 'ok' : 'pencil';
			}

			return icon;
		};

		/**
		 * Return the glyphicon sub-classname for the secondary action button icon
		 *
		 * @return {String} Glyphicon sub-classname
		 */
		$scope.iconActionSecondary = function() {
			var icon = 'chevron-down';

			// When viewing the Receipt
			if ( $scope.haveReceipt() ) {
				icon = 'remove';
			}

			// When viewing the Receipt
			if ( $scope.haveReceipt() ) {
				icon = 'remove';
			}

			// When editing the current Order
			if ( $scope.shouldShowOrder() && $scope.editingCurrentOrder() ) {
				icon = 'remove';
			}

			return icon;
		};

		/**
		 * Return the classname for the primary action button icon
		 *
		 * @return {String} Classname
		 */
		$scope.classActionPrimary = function() {
			var className = 'alert-info';

			// When viewing the Receipt
			if ( $scope.haveReceipt() || $scope.viewingReceipt() ) {
				className = 'alert-success';
			}

			// When in Settings mode
			if ( $scope.inSettingsMode() ) {

				// When in Create/Edit mode
				if ( $scope.viewingProduct() ) {
					className = $scope.editingCurrentProduct() ? 'alert-success' : 'hidden';
				}
			}

			// When viewing the current Order
			if ( $scope.shouldShowOrder() ) {

				// When in Edit mode
				if ( $scope.editingCurrentOrder() ) {
					className = $scope.editingCurrentOrder( true ) && $scope.isOrderEditable() ? 'alert-success' : 'hidden';
				} else {
					className = $scope.isOrderEditable() ? 'alert-warning' : 'hidden';
				}
			}

			return className;
		};

		/**
		 * Return the classname for the secondary action button icon
		 *
		 * @return {String} Classname
		 */
		$scope.classActionSecondary = function() {
			var className = 'hidden';

			// When the Receipt has products and it's in view
			if ( $scope.haveReceipt() && ( $scope.isHome() || $scope.viewingReceipt() ) ) {
				className = 'alert-danger';
			}

			// When in Settings mode
			if ( $scope.inSettingsMode() ) {

				// When in Create/Edit mode
				if ( $scope.viewingProduct() ) {
					className = 'alert-danger';
				}
			}

			// Viewing the current Order
			if ( $scope.shouldShowOrder() ) {
				className = $scope.editingCurrentOrder() ? 'alert-danger' : 'hidden';
			}

			return className;
		};

		/** Settings **********************************************************/

		// Setup settings globals
		$scope.settingsMode = false;

		/**
		 * Return whether we're viewing in Settings mode
		 *
		 * @return {Boolean} In Settings mode
		 */
		$scope.inSettingsMode = function() {
			return ! $scope.disableSettings() && $scope.settingsMode;
		};

		/**
		 * Toggle the Settings mode
		 *
		 * @param  {Boolean} force Optional. Whether to force open (true) or close (false) the settings.
		 * @return {void}
		 */
		$scope.toggleSettingsMode = function( force ) {
			$scope.settingsMode = ( typeof force !== 'undefined' ) ? !! force : ! $scope.settingsMode;

			// Unset current product details
			if ( ! $scope.settingsMode ) {
				$scope.toggleCurrentProduct();
			}

			// Open/close consumer panel for large screens
			if ( $scope.isLargeScreen() ) {
				$scope.settingsMode ? $scope.openPanel() : $scope.isReceiptEmpty() && $scope.closePanel();
			}
		};

		/**
		 * Close the Settings mode
		 *
		 * @return {void}
		 */
		$scope.closeSettingsMode = function() {
			$scope.inSettingsMode() && $scope.toggleSettingsMode();
		};

		/**
		 * Return whether settings should be disabled
		 *
		 * @return {Boolean} Disable settings
		 */
		$scope.disableSettings = function() {
			return $scope.viewingAbout
				|| $scope.viewingOccasionSelector
				|| $scope.viewingOrders()
				|| $scope.shouldShowOrder()
				|| ( $scope.haveReceipt() && ! $scope.shouldShowConsumers() )
				|| ! $online.is();
		};
	}])

	/**
	 * Defines <inc-consumer-list-items> element directive
	 *
	 * Scope attributes: {
	 *     @type {Array}    groups    List of group objects
	 *     @type {Array}    consumers     List of consumer objects
	 *     @type {Function} filter    Optional. Filter method used whether to show the item in the list
	 *     @type {Function} selector  Handler method when a consumer item is clicked
	 *     @type {Function} selected  Optional. Method to indicate whether the consumer item is selected
	 * }
	 */
	.directive( 'incConsumerListItems', function() {
		return {
			restrict: 'E',
			templateUrl: 'inc-consumer-list-items.ng.html',
			replace: true,
			scope: {
				groups:    '=',
				consumers: '=',
				filter:    '&?',
				selector:  '&?',
				selected:  '&?',
				presser:   '&?',
				searcher:  '=?'
			},
			controller: [ '$scope', function( $scope ) {

				/**
				 * Filter method to return items as per the filter
				 *
				 * @param  {Object}  item Item object
				 * @return {Boolean}      Include the item
				 */
				$scope.listFilter = function( item ) {
					return ( typeof $scope.filter !== 'undefined' ) ? $scope.filter({a: item}) : true;
				};

				/**
				 * Filter method to return items matching the search tokens
				 *
				 * @param  {Object}  item Item object
				 * @return {Boolean}      Include the item
				 */
				$scope.searchFilter = function( item ) {

					// Bail when the searcher is empty
					if ( ! !! $scope.searcher ) {
						return true;
					}

					// Parse target and search text
					var s = foldAccents( item.name ).toLowerCase(),
					    q = foldAccents( $scope.searcher ).toLowerCase();

					// Try to find each char from `q` in `s`
					for ( var i = 0, j = q.length; i < j; i++ ) {
						if ( -1 === s.indexOf( q.charAt( i ) ) ) {
							return false;
						}

						// Continue earch on leftover text
						s = s.substr( s.indexOf( q.charAt( i ) ) + 1 );
					}

					return true;
				};
			}]
		};
	})

	/**
	 * Defines <inc-product-list-items> element directive
	 *
	 * Scope attributes: {
	 *     @type {Array}    products  List of product objects
	 *     @type {Function} filter    Optional. Filter method used whether to show the item in the list
	 *     @type {Function} selector  Handler method when a consumer item is clicked
	 *     @type {Function} selected  Optional. Method to indicate whether the consumer item is selected
	 * }
	 */
	.directive( 'incProductListItems', function() {
		return {
			restrict: 'E',
			templateUrl: 'inc-product-list-items.ng.html',
			replace: true,
			scope: {
				products:        '=',
				filter:          '&?',
				selector:        '&?',
				counter:         '&?',
				actionSecondary: '&?',
				iconSecondary:   '@?',
				// sortable:        '@'
			},
			controller: [ '$scope', function( $scope ) {

				// Default secondary action icon
				$scope.iconSecondary || ( $scope.iconSecondary = 'minus' );

				/**
				 * Select method which checks the selected item and runs the provided logic
				 *
				 * @param  {Object} item   The selected item
				 * @param  {Object} $event Event data
				 * @return {void}
				 */
				$scope.select = function( item, $event ) {
					$event.stopPropagation();

					// Bail when this is not a proper product
					if ( ! item.name ) {
						return;
					}

					// Run selector method
					( 'undefined' !== typeof $scope.selector ) && $scope.selector({a: item});
				};

				/**
				 * Count method which returns the counted items
				 *
				 * @param  {Object} item The selected item
				 * @return {Number}      Item count
				 */
				$scope.count = function( item ) {

					if ( ! item.name ) {
						return;
					}

					// Run counter method
					return ( 'undefined' !== typeof $scope.counter ) ? $scope.counter({a: item}) : item.amount;
				};

				/**
				 * Deselect method which checks the selected item and runs the provided secondary logic
				 *
				 * @param  {Object} item   The selected item
				 * @param  {Object} $event Event data
				 * @return {void}
				 */
				$scope.deselect = function( item, $event ) {
					$event.stopPropagation();

					// Bail when this is not a proper product
					if ( ! item.name ) {
						return;
					}

					// Run actionSecondary method
					( 'undefined' !== typeof $scope.actionSecondary ) && $scope.actionSecondary({a: item});
				};

				/**
				 * Filter method to return items as per the filter
				 *
				 * @param  {Object}  item Item object
				 * @return {Boolean}      Include the item
				 */
				$scope.listFilter = function( item ) {
					return ( 'undefined' !== typeof $scope.filter ) ? $scope.filter({a: item}) : true;
				};

				/**
				 * Return whether the item collection is not empty
				 *
				 * @return {Boolean} Having items
				 */
				$scope.haveItems = function() {
					return $scope.products && $scope.products[0] && 'name' in $scope.products[0];
				};
			}]
		};
	})

	/**
	 * Defines custom filter to return consumers by group
	 */
	.filter( 'filterConsumersByGroup', function() {
		// Return defined filter function
		return getConsumersByGroup;
	})

	/**
	 * Defines select-on-focus attribute directive
	 *
	 * Fires a select-all on the input's content when focussed. This is only
	 * available for inputs of type text|search|url|tel|password as per the HTML spec.
	 */
	.directive( 'selectOnFocus', function() {
		return {
			restrict: 'A',
			link: function( $scope, $element ) {
				$element.on( 'focus', function() {
					this.setSelectionRange( 0, this.value.length );
				});
			}
		};
	})

	/**
	 * Defines on-long-press attribute directive
	 *
	 * Evaluates provided logic on long pressing the element. Do
	 * not apply on form submit elements.
	 */
	.directive( 'onLongPress', [ '$timeout', '$parse', function( $timeout, $parse ) {
		return {
			restrict: 'A',
			compile: function( $element, attrs ) {
				var fn = $parse( attrs.onLongPress );

				// Return link function
				return function( $scope, $el ) {
					var timeoutHandler, pressing;

					// Hook logic to run after 600 ms
					$el.on( 'touchstart mousedown', function( e ) {
						pressing = false;
						timeoutHandler = $timeout( function() {
							pressing = true;
							$scope.$apply( function() {
								fn( $scope, { $event: e });
							});
						}, 600 );
					});

					// Unhook logic
					$el.on( 'touchend touchmove mouseup click mouseleave', function( e ) {

						// Block all related events. This is especially
						// needed to block 'click' events from firing, when
						// the `timeoutHandler` is already cleared.
						if ( pressing ) {
							e.preventDefault();
							e.stopImmediatePropagation();
						}

						$timeout.cancel( timeoutHandler );
					});
				};
			}
		};
	}])

	/**
	 * Defines select-on-focus attribute directive
	 *
	 * Fires a select-all on the input's content when focussed.
	 */
	.directive( 'stringToPrice', [ '$filter', function( $filter ) {
		return {
			restrict: 'A',
			require: 'ngModel',
			link: function( $scope, $element, attrs, ngModel ) {

				// Format the value from the model into the input
				ngModel.$formatters.push( function( v ) {
					return parseFloat( v ) || 0;
				});

				// Parse the value from the input into the model
				ngModel.$parsers.push( function( v ) {
					return '' + $filter( 'number' )( v, 2 );
				});
			}
		};
	}])

	/**
	 * Defines state-trigger attribute directive
	 *
	 * Broadcasts the state when selected
	 */
	.directive( 'stateTrigger', [ '$rootScope', function( $rootScope ) {
		return {
			restrict: 'A',
			link: function( $scope, $element, attrs ) {
				var domain = attrs.stateTrigger.split( ':' )[1] && attrs.stateTrigger.split( ':' )[0] || 'default',
				    state  = attrs.stateTrigger.split( ':' )[1] || attrs.stateTrigger;

				// Click handler to trigger the state
				$element.on( 'click', function() {
					$rootScope.$broadcast( 'state-triggered-' + domain, state );
				});

				// Tab listener
				$scope.$on( 'state-triggered-' + domain, function( event, newTab ) {
					$element.toggleClass( 'current', newTab === state );
				});
			}
		};
	}])

	/**
	 * Defines state-target attribute directive
	 *
	 * Listens for broadcasted state
	 */
	.directive( 'stateTarget', function() {
		return {
			restrict: 'A',
			link: function( $scope, $element, attrs ) {
				var domain = attrs.stateTarget.split( ':' )[1] && attrs.stateTarget.split( ':' )[0] || 'default',
				    state  = attrs.stateTarget.split( ':' )[1] || attrs.stateTarget;

				// Tab listener
				$scope.$on( 'state-triggered-' + domain, function( event, newTab ) {
					$element
						.toggleClass( 'state-active', newTab === state )
						.toggleClass( 'state-inactive', newTab !== state );
				});
			}
		};
	});

	/**
	 * Defines <product-details> element directive
	 */
	// .directive( 'productDetails', function() {
	// 	return {
	// 		restrict: 'E',
	// 		templateUrl: 'product-details.ng.html',
	// 		replace: true,
	// 		controller: [ '$scope', function( $scope ) {
	// 			// $scope.product = $scope.
	// 			console.log( $scope );
	// 		}]
	// 	};
	// });

	/** Helper Functions **/

	/**
	 * Return array items by identical group id
	 *
	 * @param  {Array}  items List of consumer objects
	 * @param  {Object} group Group object with id property
	 * @return {Array}        Filtered list of consumer objects
	 */
	getConsumersByGroup = function( items, group ) {
		if ( typeof group === 'undefined' ) {
			return items;
		}

		var filtered = [];
		for ( var i = 0, j = items.length; i < j; i++ ) {
			if ( items[i].group.id === group.id ) {
				filtered.push( items[i] );
			}
		}

		return filtered;
	};

	/**
	 * Helper method to return the logged-in consumer
	 *
	 * @return {Object} Consumer object
	 */
	getCurrentUser = function() {
		return { id: 80, name: 'Laurens' };
	};

	/**
	 * Helper method to get the `array`'s index for `obj`
	 *
	 * @param  {Object} obj   Any object with an identifier property
	 * @param  {Array}  array Array of similar objects as `obj`
	 * @param  {String} props Following parameters. Property name(s) of the object's identifier. Defaults to '$$hashKey'.
	 * @return {Number}       Index number of the `obj`'s position within `array`
	 */
	getIndexBy = function( obj, array ) {
		var props = {};

		// Define matcher object
		if ( arguments.length > 2 && arguments[2] ) {
			_.toArray( arguments ).slice(2).forEach( function( prop ) {
				props[ prop ] = obj[ prop ];
			});
		} else {
			props = { $$hashKey: obj.$$hashKey };
		}

		return array.findIndex( function( e ) { return _.matcher( props )( e ); });
	};

	/**
	 * Return the total amount of products in the collection of products
	 *
	 * @param  {Array} products List of product objects
	 * @return {Number}         Total product amount
	 */
	sumProductAmount = function( products ) {
		return _.isEmpty( products ) ? 0 : _.reduce( products.map( function( r ) { return r.amount; }), function( memo, a ) { return memo + a; });
	};

	/**
	 * Return the total price of products in the collection of products
	 *
	 * @param  {Array} products List of product objects
	 * @return {Float}          Total product price
	 */
	sumProductPrice = function( products ) {
		return _.isEmpty( products ) ? 0 : _.reduce( products.map( function( r ) { return r.amount * r.price; }), function( memo, a ) { return memo + a; });
	};

	/**
	 * Change the class on a given element to trigger a change animation
	 *
	 * @param  {Object} element jQuery element
	 * @return {void}
	 */
	triggerElementChanged = function( element ) {

		// Element is not found
		if ( ! element || ! element[0] || ! element.removeClass || ! element.addClass ) {
			return;
		}

		/**
		 * Trigger animation on value change. To do this, force a reflow in between
		 * toggling the animation class.
		 * 
		 * @link https://css-tricks.com/restart-css-animation/
		 */
		element.removeClass('changed');
		void element[0].offsetWidth;
		element.addClass('changed');
	};

})( angular, jQuery, _ );
