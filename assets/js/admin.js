/**
 * Incassoos Admin scripts
 *
 * @package Incassoos
 * @subpackage Administration
 */

/* global incAdminL10n */
jQuery(document).ready( function($) {

	var settings = incAdminL10n && incAdminL10n.settings || {},
		formatCurrency = settings.formatCurrency || {},
	    l10n = incAdminL10n && incAdminL10n.l10n || {};

	/**
	 * Single Post Metaboxes
	 */

	var $incNotice = $( '.incassoos-notice' ).on( 'click', '.button-link', function() {
		$incNotice.toggleClass( 'open' ).find( '.button-link' ).text( $incNotice.hasClass( 'open' ) ? l10n.toggleCloseErrors : l10n.toggleOpenErrors );
	});

	/** Generic *********************************************************/

	var $consumerBox = $( '#incassoos_collection_consumers, #incassoos_activity_participants, #incassoos_occasion_consumers, body.incassoos_page_incassoos-consumers' ),
	    $detailsBox = $( '#incassoos_collection_details, #incassoos_activity_details, #incassoos_occasion_details, #incassoos_order_details, #incassoos_product_details' ),
	    $consumerList = $consumerBox.find( '.incassoos-item-list' ),
	    consumerHiddenMatches = '.showing-default-items .hide-by-default, .search-hide, .hide-in-list';

	$detailsBox
		// Publishing action
		.on( 'click', '.publishing-action .button', function() {
			$detailsBox.find( '#misc-publishing-actions .spinner' ).addClass( 'is-active' );
		})

		// Action selection
		.on( 'change', '#post-action-type', function() {
			var dataset = $( this ).find( '[value="' + this.value + '"]' )[0].dataset;

			// Show confirmation input when it is required
			if ( !! parseInt( dataset.requireConfirmation ) ) {
				$detailsBox.find( '.publishing-notice' ).addClass( 'require-confirmation' );
			} else {
				$detailsBox.find( '.publishing-notice' ).removeClass( 'require-confirmation' );
			}

			// Show decryption key input when it is required
			if ( !! parseInt( dataset.requireDecryptionKey ) ) {
				$detailsBox.find( '.publishing-notice' ).addClass( 'require-decryption-key' );
			} else {
				$detailsBox.find( '.publishing-notice' ).removeClass( 'require-decryption-key' );
			}
		});

	$consumerList
		// Toggling item details
		.on( 'click', '.open-details', function() {
			$( this ).toggleClass( 'opened' );
			$consumerList.find( '.open-details' ).not( this ).removeClass( 'opened' );
		})

		// Focus search when tapping list header
		.on( 'click', '.item-list-header', function( event ) {
			if ( ! $consumerList.find( '.item-list-header' ).find( event.target ).length ) {
				$consumerList.find( '.item-list-header > .list-search' ).focus();
			}
		})

		// Show (un)limit searched items
		.on( 'keyup change search input', '.list-search', function() {

			// Get list to search from data attribute or default to consumer list
			var $searchList = $( this.dataset.list || $consumerList );

			// Create regex from search string
			var reg = new RegExp( this.value.replace( / /g, '' ), 'gi' );

			// Unhide all, filter unmatched usernames, and hide those
			$searchList
				.find( '.consumer' ).removeClass( 'search-hide' ).find( '.consumer-name' ).filter( function( i, el ) {
					return ! el.innerHTML.replace( / /g, '' ).match( reg );
				}).parents( '.consumer' ).addClass( 'search-hide' );

			toggleGroups({
				$parent: $searchList
			});
		})

		// Quick select items
		.on( 'change', '.quick-select', function() {
			var filterVal = $(this).val(),

			    // Deselect when filter leads with an underscore
			    selected = ( 0 !== filterVal.indexOf( '_' ) ),

			    // Get filter name corrected for deselecting
			    filter = selected ? filterVal : filterVal.substring( 1 ),

			    // Exclude items when filter leads with an exclamation mark
			    exclude = ( 0 === filter.indexOf( '!' ) );

			// Correct filter when excluding
			filter = exclude ? filter.substring( 1 ) : filter;

			// Bail when selecting no value
			if ( '-1' === filter ) {
				return;
			}

			// Default for All and None
			if ( 'all' === filter ) {
				$consumerList.find( '.consumer' ).not( consumerHiddenMatches ).find( '.select-user' ).prop( 'checked', selected );

				// Update group selection toggle states
				$consumerList.find( '.select-group-users' ).attr( 'data-selected', selected );
			} else {
				$consumerList.find( '.consumer' ).not( consumerHiddenMatches ).find( '.select-user' ).filter( function( i, el ) {
					return ( -1 !== $(el).attr( 'data-matches' ).split( ',' ).indexOf( filter ) ) !== exclude;
				}).prop( 'checked', selected );
			}

			// Update count and total
			updateCount();
			toggleGroups();
			updateTotal();
		})

		// Toggle group selection
		.on( 'click', '.select-group-users', function() {
			var $el = $(this),
			    selected = 'true' === $el.attr( 'data-selected' ) || $consumerList.is( '.showing-selected' );

			// Set the data-selected property
			$el.attr( 'data-selected', ! selected )

				// Toggle the users
				.parents( '.group' ).first().find( '.consumer' ).not( consumerHiddenMatches ).find( '.select-user' ).prop( 'checked', ! selected );

			// Update count and total
			updateCount();
			toggleGroups();
			updateTotal();
		})

		// Reverse group order
		.on( 'click', '#reverse-group-order', function() {
			var $list = $consumerList.find( '.groups' );

			// Move the list items in a reversed order back into their parent
			$list.append( $list.find( '.group' ).get().reverse() );
		})

		// Toggle list columns
		.on( 'click', '#toggle-list-columns', function() {
			$consumerList.toggleClass( 'no-list-columns' );
		});

	// Datepicker
	if ( 'function' === typeof $.fn.datepicker ) {
		$( '.datepicker' ).datepicker({ dateFormat: 'dd-mm-yy' });
	}

	// Trigger the first auto-download when present
	$( 'body.incassoos [data-autostart-download]' ).first().each( function() {

		// Only trigger for in-site links
		if ( this.href && settings.siteUrl === this.href.substr(0, settings.siteUrl.length) ) {
			window.location = this.href;
		}
	});

	/** Collection ******************************************************/

	var $colDtlsBox = $( '#incassoos_collection_details' ),
	    $colActBox = $( '#incassoos_collection_activities' ),
	    $colOccBox = $( '#incassoos_collection_occasions' ),
	    $colTotalField = $colDtlsBox.find( '#collection-total' ).prepend( '<span class="new-value value"></span>' );

	// Keep selected count
	$colActBox.on( 'click', '.select-activity', function() {

		// Update count and total
		updateCount({
			$box: $colActBox,
			countBy: '.select-activity:checked'
		});
		updateTotal({
			$totalField: $colTotalField,
			calculator: calculateCollectionTotal
		});
	});

	// Keep selected count
	$colOccBox.on( 'click', '.select-occasion', function() {

		// Update count and total
		updateCount({
			$box: $colOccBox,
			countBy: '.select-occasion:checked'
		});
		updateTotal({
			$totalField: $colTotalField,
			calculator: calculateCollectionTotal
		});
	});

	/**
	 * Calculate the collection's total
	 *
	 * @since 1.0.0
	 *
	 * @return {Float} Collection total
	 */
	function calculateCollectionTotal() {
		var calculated = 0, $listItem;

		// Calculate total from activities
		$.each( $colActBox.find( '.select-activity:checked' ), function( i, el ) {
			$listItem = $(el).parents( '.collection-activity' ).first();
			calculated += parseFormattedCurrency( $listItem.find( '.activity-total' ).text() || 0 );
		});

		// Calculate total from occasions
		$.each( $colOccBox.find( '.select-occasion:checked' ), function( i, el ) {
			$listItem = $(el).parents( '.collection-occasion' ).first();
			calculated += parseFormattedCurrency( $listItem.find( '.occasion-total' ).text() || 0 );
		});

		return calculated;
	}

	/** Activity ********************************************************/

	var $actDtlsBox = $( '#incassoos_activity_details' ),
	    $actPriceField = $actDtlsBox.find( 'input#price' ),
	    $actCountField = $actDtlsBox.find( '#activity-participant-count' ).prepend( '<span class="new-value"></span>' ),
	    $actTotalField = $actDtlsBox.find( '#activity-total' ).prepend( '<span class="new-value"></span>' ),
	    $actPartBox = $( '#incassoos_activity_participants' ),
	    $actPtcptList = $actPartBox.find( '.incassoos-item-list' ),
	    $addParticipant = $actPtcptList.find( '#addparticipant' ),
	    $listItem;

	// Price changes
	$actPriceField.on( 'change', function() {

		// Parse float value
		this.value = parseFloatFormat( this.value );

		// Update default prices
		$actPartBox.find( '.activity-participant .custom-price' ).attr( 'placeholder', this.value );

		// Update total
		updateTotal();
		toggleGroups();
	});

	$actPtcptList
		// Show (un)limit selected items
		.on( 'click', '#show-selected', function() {
			$actPtcptList
				.toggleClass( 'showing-selected' )
				.find( '#show-selected' )
				.text( $actPtcptList.hasClass( 'showing-selected' ) ? l10n.showSelectedAll : l10n.showSelectedOnly );

			toggleGroups();
		})

		// Open add participant
		.on( 'click', '.add-participant-container:not(.adding-participant) #open-add-participant', function() {
			$actPtcptList
				.find( '.add-participant-container' ).addClass( 'adding-participant' )
				.append( $addParticipant.find( '.add-participant' ).clone().show() )
				.find( '.list-search' ).focus().end()

				// Collect all hidden list items on the page and list them in the box
				// TODO: This doesn't scale well for large amounts of items.
				.find( '.item-list' ).append( $actPtcptList.find( '.consumer.hide-in-list' ).parents( '.group' ).clone().removeClass([ 'hide-in-list', 'hidden' ]) )
					.find( '.sublist-header .title' ).replaceWith( function() {
						return $( '<span class="title"></span>' ).append( $( this ).contents() );
					}).end()
					.find( '.consumer:not(.hide-in-list' ).remove().end()
					.find( '.consumer' ).removeClass([ 'hide-in-list', 'search-hide' ]).find( '.item-content' ).replaceWith( function() {
						return $( '<button type="button" class="button-link consumer-name title"></button>' ).append( $( this ).find( '.consumer-name' ).contents() );
					});
		})

		// Close add participant
		.on( 'click', '.add-participant-container.adding-participant #open-add-participant', function() {
			activityRemoveAddParticipant();
		})

		// Add participant and apply selection
		.on( 'click', '.add-participant .consumer', function() {
			var $this = $( this );
			$actPtcptList
				.find( '.sublist #' + this.id ).removeClass([ 'hide-in-list', 'search-hide' ]).find( '.select-user' ).attr( 'checked', true ).trigger( 'change' )
				.parents( '.group' ).removeClass([ 'hide-in-list', 'hidden' ]);

			// Remove group when this was the only item left
			if ( $this.is( ':only-child' ) ) {
				$this.parents( '.group' ).addClass( 'hide-in-list' );
			} else {
				$this.addClass( 'hide-in-list' );
			}
		})

		// Keep selected count
		.on( 'change', '.select-user', function() {

			// Update count and total
			updateCount();
			updateTotal();
		})

		// Toggle custom price input
		.on( 'click', '.toggle-custom-price', function() {
			activityParticipantOpenCustomPrice( this );
		})

		// Custom price changed
		.on( 'change', '.custom-price', function() {
			this.value = parseFloatFormat( this.value );

			// Update total
			updateTotal();
		});

	$(document)
		// Close add participant
		.on( 'keyup mouseup', function( event ) {

			// Pressed Escape
			if ( 'keyup' === event.type && 27 === event.keyCode ) {
				activityRemoveAddParticipant();
			} else {
				var $box = $actPtcptList.find( '.add-participant-container.adding-participant .add-participant, .add-participant-container.adding-participant #open-add-participant' );

				// Clicked outside of box
				if ( 'mouseup' === event.type && ! $box.is( event.target ) && ! $box.has( event.target ).length ) {
					activityRemoveAddParticipant();
				}
			}
		});

	/**
	 * Remove adding participants from the activity page
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	function activityRemoveAddParticipant() {
		$actPtcptList.find( '.add-participant-container' ).removeClass( 'adding-participant' ).find( '.add-participant' ).remove();
	}

	/**
	 * Open or toggle the custom price field
	 *
	 * @since 1.0.0
	 *
	 * @param  {HTMLElement} el    The element associated with the custom price.
	 * @param  {Boolean}     force Optional. True to force open the price, False to toggle. Defaults to false.
	 */
	function activityParticipantOpenCustomPrice( el, force ) {
		force = force || false;

		$listItem = $actPtcptList.find( el ).parents( '.activity-participant' ).first();
		$listItem[ force ? 'addClass' : 'toggleClass' ]( 'has-custom-price' );

		var $customPrice = $listItem.find( '.custom-price' );

		// Clear input field
		if ( ! $listItem.hasClass( 'has-custom-price' ) ) {
			$customPrice.val( '' ).prop( 'disabled', true );
		} else {
			$customPrice.prop( 'disabled', false );

			// Focus. Setting selection range is not supported for number type
			if ( $customPrice[0] !== el ) {
				$customPrice.focus();
			}
		}

		// Update total
		updateTotal();
	}

	/** Order ***********************************************************/

	var $ordDtlsBox = $( '#incassoos_order_details' ),
	    $ordConsumerField = $ordDtlsBox.find( '#consumer_id' ),
	    $ordTotalField = $ordDtlsBox.find( '#order-total' ).prepend( '<span class="new-value"></span>' ),
	    $ordPrdBox = $( '#incassoos_order_products' );

	// Auto-suggest for users
	$ordConsumerField.suggest( $ordConsumerField.data( 'ajax-url' ), {
		minchars: 1, // Allow single-digit user IDs
		onSelect: function() {
			var value = this.value;
			$ordConsumerField.val( value.substr( 0, value.indexOf( ' ' ) ) );
		}
	});

	// Keep selected count
	$ordPrdBox.on( 'change', '.order-product .value', function( event ) {

		// Set the product's is-selected class
		$ordPrdBox.find( event.target ).parents( '.order-product' ).first().toggleClass( 'is-selected', !! parseInt( event.target.value ) );

		// Update count and total
		updateCount({
			$box: $ordPrdBox,
			calculator: calculateOrderCount
		});
		updateTotal({
			$totalField: $ordTotalField,
			calculator: calculateOrderTotal
		});
	});

	/**
	 * Calculate the order's product count
	 *
	 * @since 1.0.0
	 *
	 * @return {Float} Order product count
	 */
	function calculateOrderCount() {
		var calculated = 0;

		// Calculate total from activities
		$.each( $ordPrdBox.find( '.order-product' ), function( i, el ) {
			calculated += Math.abs( parseInt( $ordPrdBox.find( el ).find( '.value' ).val() ) );
		});

		return calculated;
	}

	/**
	 * Calculate the order's total
	 *
	 * @since 1.0.0
	 *
	 * @return {Float} Order total
	 */
	function calculateOrderTotal() {
		var calculated = 0, $listItem;

		// Calculate total from activities
		$.each( $ordPrdBox.find( '.order-product' ), function( i, el ) {
			$listItem = $ordPrdBox.find( el );
			calculated += parseFormattedCurrency( $listItem.find( '.price' ).text() || 0 ) * $listItem.find( '.value' ).val();
		});

		return calculated;
	}

	/** Generic methods *************************************************/

	/**
	 * Update the details metabox total value
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} options Update options.
	 * @return {Void}
	 */
	function updateTotal( options ) {
		var total = 0;

		// Setup defaults for Activity details
		options = options || {};
		options.calculator = options.calculator || function() {
			var calculated = 0, $listItem;

			// Calculate total from (custom) prices
			$.each( $actPtcptList.find( '.activity-participant .select-user:checked' ), function( i, el ) {
				$listItem = $(el).parents( '.activity-participant' ).first();

				calculated += $listItem.hasClass( 'has-custom-price' )
					? parseFloat( $listItem.find( '.custom-price' ).val() || 0 )
					: parseFloat( $actPriceField.val() || 0 );
			});

			return calculated;
		};
		options.$totalField = options.$totalField || $actTotalField;

		// Calculate total
		if ( 'function' === typeof options.calculator ) {
			total = options.calculator();
		}

		// Replace total text
		options.$totalField.find( '.new-value' ).html( parseCurrencyFormat( total ) );
	}

	/**
	 * Update the metabox's heading counter
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} options Update options.
	 * @return {Void}
	 */
	function updateCount( options ) {
		var count;

		// Setup deafults for Activity participants
		options = options || {};
		options.$box = options.$box || $actPartBox;
		options.countBy = options.countBy || '.activity-participant .select-user:checked';
		options.$countField = options.$countField || $actCountField;

		// Calculate count
		if ('function' === typeof options.calculator) {
			count = options.calculator();
		} else {
			count = options.$box.find( options.countBy ).length;
		}

		options.$box.find( 'h2 .count' ).text( '(' + count + ')' );
		options.$countField.find( '.new-value' ).html( count );
	}

	/**
	 * Toggle visibility of user groups
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} options Toggle options.
	 * @return {Void}
	 */
	function toggleGroups( options ) {

		// Setup defaults for Activity participants
		options = options || {};
		options.$parent = options.$parent || $consumerList;

		options.$parent
			.find( '.group' )
			.removeClass( 'hidden' ) // Reset visibility for all
			.filter( function() {
				return ! $( this ).find( '.consumer' ).not( consumerHiddenMatches ).filter( function() {
					var $el = $( this );
					return $el.is( '.showing-selected .consumer' ) ? ! $el.find( '.select-user:not(:checked)' ).length : true; // Account for selected items when showing selected
				}).length;
			})
			.toggleClass( 'hidden', true ); // Set visibility
	}

	/**
	 * Parse a numeric value and return a formatted currency
	 *
	 * @since 1.0.0
	 *
	 * @param  {Number|Float} value  Numeric value
	 * @param  {String}       format Optional. Currency format. Defaults to defined currency format.
	 * @return {String}              Formatted currency
	 */
	function parseCurrencyFormat( value, format ) {

		// Parse to string
		value = parseFloat( value ).toFixed( formatCurrency && formatCurrency.decimals || 2 )
			// Format decimal point
			.replace( '.', formatCurrency && formatCurrency.decimal_point || ',' )
			// Format thousands separator
			.replace( /\B(?=(\d{3})+(?!\d))/g, formatCurrency && formatCurrency.thousands_sep || '.' );

		// Default format
		format = format || formatCurrency && formatCurrency.format || '%s';

		return format.replace( '%s', value );
	}

	/**
	 * Parse a formatted value and return a numeric value
	 *
	 * @since 1.0.0
	 *
	 * @param  {String}              Formatted currency
	 * @return {Number|Float} value  Numeric value
	 */
	function parseFormattedCurrency( value ) {
		var reg = new RegExp( '[^0123456789' + ( formatCurrency ? formatCurrency.decimal_point : ',' ) + ']', 'g' );

		// Parse from string
		value = String( value ).replace( reg, '' ).replace( formatCurrency ? formatCurrency.decimal_point : ',', '.' );

		return parseFloat( value );
	}

	/**
	 * Parse a numeric value and return a formatted float
	 *
	 * @since 1.0.0
	 *
	 * @param  {Number|Float} value  Numeric value
	 * @return {String}              Formatted float
	 */
	function parseFloatFormat( value ) {
		return parseFloat( value ).toFixed( formatCurrency && formatCurrency.decimals || 2 );
	}

	/** Taxonomy List Table *********************************************/

	// Setup term quick edit
	if ( 'undefined' !== typeof inlineEditTax ) {

		// Term default
		$( 'body.taxonomy-' + settings.ids.occasionType ).find( '#inlineedit fieldset:first-child .inline-edit-col' )
			.append( '<label><span class="title">' + l10n.termMetaDefault + '</span><span class="input-text-wrap"><input type="checkbox" name="term-default" value="1" /></span></label>' );

		/* global inlineEditTax */
		var wp_inline_edit_term = inlineEditTax.edit;
		inlineEditTax.edit = function( id ) {
			wp_inline_edit_term.apply( this, arguments );

			// Extend logic here
			id = ( typeof( id ) === 'object' ) ? this.getId( id ) : id;

            // Apply logic when this is the default
            if ( +id === settings.occasionTypeDefault ) {
				$( ':input[name="term-default"]', '.inline-editor' ).prop( 'checked', true );
            }
		};
	}

	/** Consumers Page **************************************************/

	var $consumersPage = $( 'body.incassoos_page_incassoos-consumers' ),
	    $bulkEdit = $consumersPage.find( '#bulkedit' ),
	    $inlineEdit = $consumersPage.find( '#inlineedit' );

	$consumersPage
		// Open bulk edit
		.on( 'click', '.incassoos-item-list:not(.bulk-editing) #toggle-bulk-edit', function() {
			consumersRemoveInlineEdit();
			$consumersPage
				.find( '.incassoos-item-list' ).addClass( 'bulk-editing' )
				.find( '#toggle-bulk-edit' ).text( l10n.toggleCloseBulkEdit ).after( $bulkEdit.find( '.bulk-edit' ).clone().show() );
		})

		// Close bulk edit
		.on( 'click', '.incassoos-item-list.bulk-editing #toggle-bulk-edit', function() {
			consumersRemoveBulkEdit();
			$consumersPage.find( '.select-user' ).prop( 'checked', false );
		})

		// Open inline edit
		.on( 'click', '.consumer:not(.toggled) > .consumer-name', function( event ) {
			consumersRemoveInlineEdit();

			// When in bulk edit mode, ignore inline edit
			if ( $consumersPage.find( '.incassoos-item-list.bulk-editing' ).length ) {
				// When not selecting the checkbox, toggle it
				if ( event.target.className !== 'select-user' ) {
					var $cb = $( this ).find( '.select-user' );
					$cb.prop( 'checked', ! $cb.prop( 'checked' ) );
				}

				return;
			}

			var $user   = $( this ).parent( 'li' ).addClass( 'toggled' ),
			    $inline = $user.append( $inlineEdit.find( '.inline-edit' ).clone() ).find( '.inline-edit' ),
			    col, val;

			// Set user ID
			$inline.find( '[name="user"]' ).val( $user.find( '.user-id' ).text() );

			// Set user fields
			for ( var field in settings.consumersFields ) {
				col = settings.consumersFields[ field ];
				val = $user.find( '.user-' + field ).text();

				col.type = col.type || 'text';

				switch ( col.type ) {
					case 'checkbox' :
						field = col.options.length <= 1 ? field : field + '[]';
						$.each( val.split( ',' ), function( i, v ) {
							$inline.find( '[name="' + field + '"][value="' + v + '"]' ).prop( 'checked', true );
						});
						break;
					case 'radio' :
						$inline.find( '[name="' + field + '"][value="' + val + '"]' ).prop( 'checked', true );
						break;
					case 'select' :
						$inline.find( '[name="' + field + '"] option[value="' + val + '"]' ).prop( 'selected', true );
						break;
					case 'textarea' :
						$inline.find( '[name="' + field + '"]' ).text( val );
						break;
					default :
						$inline.find( '[name="' + field + '"]' ).val( val );
						break;
				}

				$(document).trigger( 'incassoos.users-inlineedit', [ field ] );
			}

			$inline.show();
		})

		// Save inline edit
		.on( 'click', '.submit .save', function() {
			$( this ).next( '.spinner' ).addClass( 'is-active' );
		})

		// Cancel inline edit
		.on( 'click', '.consumer.toggled .consumer-name, .submit .cancel', function() {

			// Focus parent again
			$( this ).parents( '.consumer' ).find( '.consumer-name' ).focus();

			consumersRemoveInlineEdit();
		})

		// Show (un)limit visible items
		.on( 'click', '#show-default-items', function() {
			$consumerList
				.toggleClass( 'showing-default-items' )
				.find( '#show-default-items' )
				.text( $consumerList.hasClass( 'showing-default-items' ) ? l10n.showDefaultItemsAll : l10n.showDefaultItemsOnly );

			toggleGroups();
		})

		// Export consumers: open popup when decryption key is required
		.on( 'click', '.export-consumers-wrapper.require-decryption-key-wrapper #export-consumers', function( e ) {
			var $parent = $( this ).parents( '.export-consumers-wrapper' );

			// Continue the submit decryption key is provided
			if ( $parent.is( '.opened' ) && '' !== $parent.find( 'input[name="export-decryption-key"]').val() ) {
				$parent.prev( '.spinner' ).addClass( 'is-active' );
				return;
			}

			// Do not start export yet
			e.preventDefault();

			// Toggle visibility of other inputs
			$parent.toggleClass( 'opened' ).find( 'input:first-of-type' ).focus();
		});

	/**
	 * Remove bulk editing from the consumers page
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	function consumersRemoveBulkEdit() {
		$consumersPage.find( '.incassoos-item-list' ).removeClass( 'bulk-editing' ).find( '#toggle-bulk-edit' ).text( l10n.toggleOpenBulkEdit ).next( '.bulk-edit' ).remove();
	}

	/**
	 * Remove inline editing from the consumers page
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	function consumersRemoveInlineEdit() {
		$consumersPage.find( '.consumer' ).removeClass( 'toggled' ).find( '.inline-edit' ).remove();
	}

	/** Settings Page ***************************************************/

	var $settingsPage = $( 'body.incassoos_page_incassoos-settings' );

	$settingsPage
		// Open popup for decryption key when decrypting option value
		.on( 'click', '.decrypt-option-value-wrapper:not(.opened) .decrypt-option-value', function() {
			var $parent = $( this ).parents( '.decrypt-option-value-wrapper' ).addClass( 'opened' );

			$parent
				.find( 'input' ).focus()
				.next( '.decrypt-option-value' ).text( settings.decryptOptionButtonLabel );
		})

		// Decrypt option value
		.on( 'click', '.decrypt-option-value-wrapper.opened .decrypt-option-value', function() {
			var $this = $( this ),
			    decryptionKey = $this.prev( 'input' ).val(),
			    optionName = this.getAttribute( 'data-option-name' );

			// Toggle UI when input was empty
			if ( '' === decryptionKey ) {
				$this.parents( '.decrypt-option-value-wrapper' ).toggleClass( 'opened' ).find( '.decrypt-option-value' ).focus().text( settings.decryptOptionButtonLabelAlt );
				return;
			}

			// Start spinning
			$this.next( '.spinner' ).addClass( 'is-active' );

			// Post AJAX action
			jQuery.ajax({
				type: 'POST',
				url: settings.adminAjaxUrl,
				data: {
					action: 'incassoos_decrypt_option_value',
					_wpnonce: settings.decryptOptionNonce,
					decryption_key: decryptionKey,
					option_name: optionName
				},
				dataType: 'json'
			}).always( function( resp ) {
				resp = ( resp && resp.hasOwnProperty( 'success' ) && resp.hasOwnProperty( 'data' ) ) ? resp : { success: false, data: [{ message: settings.unknownError }] };

				if ( ! resp.success ) {
					alert( resp.data[0].message );
				} else {

					// Replace input value of the option
					$settingsPage.find( '[name="' + optionName + '"]' ).attr( 'value', resp.data.decryptedOptionValue ).focus();

					// Remove decryption key inputs
					$this.parents( '.decrypt-option-value-wrapper' ).remove();
				}
			});
		});
});
