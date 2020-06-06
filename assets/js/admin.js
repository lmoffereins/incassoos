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

	var $consumerBox = $( '#incassoos_collection_consumers, #incassoos_occasion_consumers' );

	$consumerBox
		// Toggling item details
		.on( 'click', '.open-details', function() {
			$( this ).toggleClass( 'opened' );
			$consumerBox.find( '.open-details' ).not( this ).removeClass( 'opened' );
		});

	// Datepicker
	if ( 'function' === typeof $.fn.datepicker ) {
		$( '.datepicker' ).datepicker({ dateFormat: 'dd-mm-yy' });
	}

	/** Collection ******************************************************/

	var $colDtlsBox = $( '#incassoos_collection_details' );

	$colDtlsBox
		// Collecting action
		.on( 'click', '#collecting-action .button', function() {
			$colDtlsBox.find( '#major-publishing-actions .spinner' ).addClass( 'is-active' );
		});

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
		actDtlsBoxUpdateTotal();
	});

	$actPartBox
		// Show (un)limit searched items
		.on( 'keyup change search input', '#participant-search', function() {

			// Create regex from search string
			var reg = new RegExp( this.value.replace( / /g, '' ), 'gi' );

			// Unhide all, filter unmatched usernames, and hide those
			$actPtcptList
				.find( '.activity-participant' ).removeClass( 'search-hide' ).find( '.title' ).filter( function( i, el ) {
					return ! el.innerHTML.replace( / /g, '' ).match( reg );
				}).parents( '.activity-participant' ).addClass( 'search-hide' );

			toggleGroups({
				show: this.value.length,
				filterBy: '.activity-participant:not(.search-hide)',
				toggleClassName: 'search-hidden'
			});
		})

		// Show (un)limit selected items
		.on( 'click', '#show-selected', function() {
			$actPtcptList
				.toggleClass( 'showing-selected' )
				.find( '#show-selected' )
				.text( $actPtcptList.hasClass( 'showing-selected' ) ? l10n.showSelectedAll : l10n.showSelectedOnly );

			toggleGroups();
		})

		// Reverse groups order
		.on( 'click', '#reverse-group-order', function() {
			var $list = $actPtcptList.find( '.groups' );

			// Move the list items in a reversed order back into their parent
			$list.append( $list.find( '.group' ).get().reverse() );
		})

		// Toggle list columns
		.on( 'click', '#toggle-list-columns', function() {
			$actPtcptList.toggleClass( 'no-list-columns' );
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
				$actPtcptList.find( '.activity-participant .select-user' ).attr( 'checked', select );
			} else {
				$actPtcptList.find( '.activity-participant .select-user' ).filter( function( i, el ) {
					return ( -1 !== $(el).attr( 'data-matches' ).split( ',' ).indexOf( filter ) ) !== exclude;
				}).attr( 'checked', select );
			}

			// Update count and total
			actPartBoxUpdateCount();
			toggleGroups();
			actDtlsBoxUpdateTotal();
		})

		// Keep selected count
		.on( 'change', '.activity-participant .select-user', function() {

			// Update count and total
			actPartBoxUpdateCount();
			actDtlsBoxUpdateTotal();
		})

		// Toggle group users selection
		.on( 'click', '.select-group-users', function() {
			var $el = $(this),
			    selected = 'true' === $el.attr( 'data-selected' ) || false;

			// Set the data-selected property
			$el.attr( 'data-selected', ! selected )

				// Toggle the users
				.parents( '.group' ).first().find( '.activity-participant .select-user' ).attr( 'checked', ! selected );

			// Update count and total
			actPartBoxUpdateCount();
			actDtlsBoxUpdateTotal();
		})

		// Toggle custom price input
		.on( 'click', '.toggle-custom-price', function() {
			actPtcptOpenCustomPrice( this );
		})

		// Custom price changed
		.on( 'change', '.custom-price', function() {
			this.value = parseFloatFormat( this.value );

			// Update total
			actDtlsBoxUpdateTotal();
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
		actDtlsBoxUpdateTotal();
	}

	/**
	 * Update the Activity details metabox total value
	 *
	 * @since 1.0.0
	 *
	 * @return {Void}
	 */
	function actDtlsBoxUpdateTotal() {
		var $selected = $actPtcptList.find( '.activity-participant .select-user:checked' ),
		    total = 0, $listItem;

		// Calculate total from (custom) prices
		$.each( $selected, function( i, el ) {
			$listItem = $(el).parents( '.activity-participant' ).first();

			total += $listItem.hasClass( 'has-custom-price' )
				? parseFloat( $listItem.find( '.custom-price' ).val() || 0 )
				: parseFloat( $actPriceField.val() || 0 );
		});

		// Replace total text
		$actTotalField.find( '.new-value' ).html( parseCurrencyFormat( total ) );
	}

	/**
	 * Update the Activity participant metabox heading counter
	 *
	 * @since 1.0.0
	 *
	 * @return {Void}
	 */
	function actPartBoxUpdateCount() {
		$actPartBox.find( 'h2 .count' ).text( '(' + $actPtcptList.find( '.activity-participant .select-user:checked' ).length + ')' );
	}

	/**
	 * Toggle visibility of Activity participant groups
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} options Toggle options.
	 * @return {Void}
	 */
	function toggleGroups( options ) {

		// Setup defaults
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
	    $inlineEdit = $consumersPage.find( '#inlineedit' ),
	    $cnsmrList = $consumersPage.find( '.incassoos-item-list' );

	$consumersPage
		// Open inline edit
		.on( 'click', 'li:not(.toggled) > .name', function() {
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
		.on( 'click', '.submit .cancel', function() {

			// Focus parent again
			$( this ).parents( 'li' ).find( '.name' ).focus();

			consumersRemoveInlineEdit();
		})

		// Show (un)limit searched items
		.on( 'keyup change search input', '#consumer-search', function() {

			// Create regex from search string
			var reg = new RegExp( this.value.replace( / /g, '' ), 'gi' );

			// Unhide all, filter unmatched usernames, and hide those
			$cnsmrList
				.find( '.consumer' ).removeClass( 'search-hide' ).find( '.name' ).filter( function( i, el ) {
					return ! el.innerHTML.replace( / /g, '' ).match( reg );
				}).parents( '.consumer' ).addClass( 'search-hide' );

			toggleGroups({
				$parent: $cnsmrList,
				show: this.value.length,
				filterBy: '.consumer:not(.search-hide)',
				toggleClassName: 'search-hidden'
			});
		})

		// Show (un)limit visible items
		.on( 'click', '#show-visible', function() {
			$cnsmrList
				.toggleClass( 'showing-visible' )
				.find( '#show-visible' )
				.text( $cnsmrList.hasClass( 'showing-visible' ) ? l10n.showVisibleAll : l10n.showVisibleOnly );

			toggleGroups({
				$parent: $cnsmrList,
				show: $cnsmrList.is( 'showing-visible' ),
				filterBy: '.consumer:not(.noshow)',
				toggleClassName: 'hidden'
			});
		})

		// Reverse groups order
		.on( 'click', '#reverse-group-order', function() {
			var $list = $cnsmrList.find( '.groups' );

			// Move the list items in a reversed order back into their parent
			$list.append( $list.find( '.group' ).get().reverse() );
		});

	/**
	 * Remove inline editing from the users page
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	function consumersRemoveInlineEdit() {
		$consumersPage.find( 'li' ).removeClass( 'toggled' ).find( '.inline-edit' ).remove();
	}
});
