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

	var $consumerBox = $( '#incassoos_collection_consumers, #incassoos_occasion_consumers, #incassoos_activity_participants, body.incassoos_page_incassoos-consumers' ),
	    $consumerList = $consumerBox.find( '.incassoos-item-list' );

	$consumerList
		// Toggling item details
		.on( 'click', '.open-details', function() {
			$( this ).toggleClass( 'opened' );
			$consumerList.find( '.open-details' ).not( this ).removeClass( 'opened' );
		})

		// Show (un)limit searched items
		.on( 'keyup change search input', '#consumer-search', function() {

			// Create regex from search string
			var reg = new RegExp( this.value.replace( / /g, '' ), 'gi' );

			// Unhide all, filter unmatched usernames, and hide those
			$consumerList
				.find( '.consumer' ).removeClass( 'search-hide' ).find( '.consumer-name' ).filter( function( i, el ) {
					return ! el.innerHTML.replace( / /g, '' ).match( reg );
				}).parents( '.consumer' ).addClass( 'search-hide' );

			toggleGroups({
				$parent: $consumerList,
				show: this.value.length,
				filterBy: '.consumer:not(.search-hide)',
				toggleClassName: 'search-hidden'
			});
		})

		// Reverse groups order
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

	/** Collection ******************************************************/

	var $colDtlsBox = $( '#incassoos_collection_details' ),
	    $colActBox = $( '#incassoos_collection_activities' ),
	    $colOccBox = $( '#incassoos_collection_occasions' ),
	    $colTotalField = $colDtlsBox.find( '#collection-total' ).prepend( '<span class="new-value value"></span>' );

	$colDtlsBox
		// Collecting action
		.on( 'click', '#collecting-action .button', function() {
			$colDtlsBox.find( '#major-publishing-actions .spinner' ).addClass( 'is-active' );
		});

	// Keep selected count
	$colActBox.on( 'click', '.select-activity', function() {

		// Update count and total
		updateCount({
			$box: $colActBox,
			countBy: '.select-activity:checked'
		});
		updateTotal({
			$totalField: $colTotalField,
			calculator: calculateColTotal
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
			calculator: calculateColTotal
		});
	});

	/**
	 * Calculate the collection's total
	 *
	 * @since 1.0.0
	 *
	 * @return {Float} Collection total
	 */
	function calculateColTotal() {
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
	    $actTotalField = $actDtlsBox.find( '#activity-total' ).prepend( '<span class="new-value"></span>' ),
	    $actPartBox = $( '#incassoos_activity_participants' ),
	    $actPtcptList = $actPartBox.find( '.incassoos-item-list' ),
	    $listItem;

	// Price changes
	$actPriceField.on( 'change', function() {

		// Parse float value
		this.value = parseFloatFormat( this.value );

		// Update default prices
		$actPartBox.find( '.activity-participant .custom-price' ).attr( 'placeholder', this.value );

		// Update total
		updateTotal();
	});

	$actPtcptList
		// Quick select participants
		.on( 'change', '#participant-quick-select', function() {
			var filterVal = $(this).val(),
				// Deselect when filter leads with an underscore
			    select = ( 0 !== filterVal.indexOf( '_' ) ),
			    filter = select ? filterVal : filterVal.substring( 1 ),
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
				$actPtcptList.find( '.activity-participant .select-user' ).prop( 'checked', select );
			} else {
				$actPtcptList.find( '.activity-participant .select-user' ).filter( function( i, el ) {
					return ( -1 !== $(el).attr( 'data-matches' ).split( ',' ).indexOf( filter ) ) !== exclude;
				}).prop( 'checked', select );
			}

			// Update count and total
			updateCount();
			toggleGroups();
			updateTotal();
		})

		// Show (un)limit selected items
		.on( 'click', '#show-selected', function() {
			$actPtcptList
				.toggleClass( 'showing-selected' )
				.find( '#show-selected' )
				.text( $actPtcptList.hasClass( 'showing-selected' ) ? l10n.showSelectedAll : l10n.showSelectedOnly );

			toggleGroups();
		})

		// Keep selected count
		.on( 'change', '.activity-participant .select-user', function() {

			// Update count and total
			updateCount();
			updateTotal();
		})

		// Toggle group users selection
		.on( 'click', '.select-group-users', function() {
			var $el = $(this),
			    selected = 'true' === $el.attr( 'data-selected' );

			// Set the data-selected property
			$el.attr( 'data-selected', ! selected )

				// Toggle the users
				.parents( '.group' ).first().find( '.activity-participant .select-user' ).prop( 'checked', ! selected );

			// Update count and total
			updateCount();
			updateTotal();
		})

		// Toggle custom price input
		.on( 'click', '.toggle-custom-price', function() {
			actPtcptOpenCustomPrice( this );
		})

		// Custom price changed
		.on( 'change', '.custom-price', function() {
			this.value = parseFloatFormat( this.value );

			// Update total
			updateTotal();
		});

	/**
	 * Open or toggle the custom price field
	 *
	 * @since 1.0.0
	 *
	 * @param  {HTMLElement} el    The element associated with the custom price.
	 * @param  {Boolean}     force Optional. True to force open the price, False to toggle. Defaults to false.
	 */
	function actPtcptOpenCustomPrice( el, force ) {
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
			calculator: calculateOrdCount
		});
		updateTotal({
			$totalField: $ordTotalField,
			calculator: calculateOrdTotal
		});
	});

	/**
	 * Calculate the order's product count
	 *
	 * @since 1.0.0
	 *
	 * @return {Float} Order product count
	 */
	function calculateOrdCount() {
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
	function calculateOrdTotal() {
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

		// Calculate count
		if ('function' === typeof options.calculator) {
			count = options.calculator();
		} else {
			count = options.$box.find( options.countBy ).length;
		}

		options.$box.find( 'h2 .count' ).text( '(' + count + ')' );
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
		options.$parent         = options.$parent || $actPtcptList;
		options.show            = options.show || $actPtcptList.is( '.showing-selected' );
		options.filterBy        = options.filterBy || '.select-user:checked';
		options.toggleClassName = options.toggleClassName || 'hidden';

		options.$parent
			.find( '.group' )
			.removeClass( options.toggleClassName ) // Reset visibility
			.filter( function( i, el ) {
				return ! $(el).find( options.filterBy ).length;
			})
			.toggleClass( options.toggleClassName, options.show ); // Set visibility
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

	/**
	 * Taxonomy List Table
	 */

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

	/**
	 * Consumers Page
	 */

	var $consumersPage = $( 'body.incassoos_page_incassoos-consumers' ),
	    $inlineEdit = $consumersPage.find( '#inlineedit' );

	$consumersPage
		// Open inline edit
		.on( 'click', '.consumer:not(.toggled) > .consumer-name', function() {
			consumersRemoveInlineEdit();

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
		.on( 'click', '#show-visible', function() {
			$consumerList
				.toggleClass( 'showing-visible' )
				.find( '#show-visible' )
				.text( $consumerList.hasClass( 'showing-visible' ) ? l10n.showVisibleAll : l10n.showVisibleOnly );

			toggleGroups({
				$parent: $consumerList,
				show: $consumerList.is( 'showing-visible' ),
				filterBy: '.consumer:not(.noshow)',
				toggleClassName: 'hidden'
			});
		});

	/**
	 * Remove inline editing from the users page
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	function consumersRemoveInlineEdit() {
		$consumersPage.find( '.consumer' ).removeClass( 'toggled' ).find( '.inline-edit' ).remove();
	}
});
