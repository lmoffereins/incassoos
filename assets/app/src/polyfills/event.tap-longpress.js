/**
 * Polyfill for 'tap' and 'longpress' custom DOM events
 *
 * To prevent 'click' events following 'longpress' events, use the 'tap' event instead.
 *
 * @author Laurens Offereins
 * @link https://github.com/john-doherty/long-press/blob/master/src/long-press.js
 */
(function( window, document ) {
	var timer = null, el, emit, long, short, cancel,
	    starters = ["mousedown", "touchstart"],
	    tappers = ["mouseup", "touchcancel", "touchend"],
	    cancellers = ["mouseout", "touchmove", "mousewheel", "wheel", "scroll"],
	    timerDuration = 800;

	/**
	 * Emit a custom event of the given name on the DOM element
	 *
	 * @param  {String} name Event name
	 * @return {Void}
	 */
	emit = function( name ) {
		this.dispatchEvent(new CustomEvent(name, { bubbles: true, cancelable: true }));
	};

	/**
	 * Emit the long 'longpress' event
	 *
	 * @return {Void}
	 */
	long = function() {
		emit.call(el, "longpress");
		cancel();
	};

	/**
	 * Emit the short 'tap' event
	 *
	 * @param  {Object} e Event data
	 * @return {Void}
	 */
	short = function( e ) {
		e = (e && e.target) || el;
		(null !== timer) && (el === e) && emit.call(el, "tap");
		cancel();
	};

	/**
	 * Clear the timer
	 *
	 * @return {Void}
	 */
	cancel = function() {
		clearTimeout(timer);
		timer = null;
	};

	/**
	 * Setup listeners for when to start the timer towards the 'longpress' event
	 */
	starters.forEach( function( i ) {
		document.addEventListener(i, function( e ) {
			el = e.target;
			timer = setTimeout(long, timerDuration);
		});
	});

	/**
	 * Setup listeners for when to short-circuit the timer
	 */
	tappers.forEach( function( i ) {
		document.addEventListener(i, short);
	});

	/**
	 * Setup listeners for when to cancel the timer
	 */
	cancellers.forEach( function( i ) {
		document.addEventListener(i, cancel);
	});

	/**
	 * Polyfill the CustomEvent constructor
	 */
	if ("initCustomEvent" in document.createEvent("CustomEvent")) {
		window.CustomEvent = function( name, params ) {
			var e = document.createEvent("CustomEvent");
			params = params || { bubbles: false, cancelable: false, detail: undefined };
			return e.initCustomEvent(name, params.bubbles, params.cancelable, params.detail), e;
		};

		window.CustomEvent.prototype = window.Event.prototype;
	}

	/**
	 * Setup keyboard listener for when to start the timer 
	 *
	 * NOTE: the 'keydown' event fires rapidly on key hold
	 */
	document.addEventListener("keydown", function( e ) {

		// Focussed element, pressing space bar
		if (32 === (e.which || e.keyCode)) {
			if (! timer) {
				el = document.activeElement;
				timer = setTimeout(long, timerDuration);
			}
		} else {
			cancel();
		}
	});

	/**
	 * Setup keyboard listener for when to either short-circuit or cancel the timer
	 */
	document.addEventListener("keyup", function( e ) {

		// Focussed element, pressing space bar
		if (el === document.activeElement && (32 === (e.which || e.keyCode))) {
			short();
		} else {
			cancel();
		}
	});
})(window, document);
