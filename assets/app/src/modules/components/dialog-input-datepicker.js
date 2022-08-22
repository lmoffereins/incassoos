/**
 * Dialog for Dropdown Input Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"q",
	"@easepick/core",
	"@easepick/kbd-plugin",
	"@easepick/core/dist/index.css",
	"lodash",
	"services",
	"./util/basic-dialog",
	"./../templates/dialog-input-datepicker.html"
], function( Q, easepick, easepickKbd, easepickCss, _, services, basicDialog, tmpl ) {
	/**
	 * Holds a reference to the delay service
	 *
	 * @type {Object}
	 */
	var delayService = services.get("delay"),

	/**
	 * Holds a reference to the l10n service
	 *
	 * @type {Object}
	 */
	l10nService = services.get("l10n"),

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	shortcutsService = services.get("shortcuts");

	return {
		props: {
			dialog: {
				type: Object,
				default: function() {
					return {};
				}
			}
		},
		template: tmpl,
		components: {
			basicDialog: basicDialog
		},
		data: function() {
			return {
				selected: this.dialog.selected,
				picker: null
			};
		},
		methods: {
			/**
			 * Make this the selected value
			 *
			 * @return {Void}
			 */
			select: function( id ) {
				this.selected = id;
			},

			/**
			 * Confirm and close the dialog
			 *
			 * Runs the onConfirm callback, then removes the dialog.
			 *
			 * @return {Void}
			 */
			confirm: function() {
				var self = this;

				Q.Promisify(this.dialog.onConfirm(this.selected)).then( function() {
					self.$destroy();
				});
			},

			/**
			 * Close the dialog
			 *
			 * Triggering destroy callbacks is done in the basicDialog component.
			 *
			 * @return {Void}
			 */
			close: function() {
				this.$destroy();
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this;

			// Reset options
			this.dialog = _.defaults(this.dialog, {
				title: "Generic.SelectDate",
				content: "",
				onConfirm: _.noop
			});

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({

					// Confirm the dialog
					"enter": function() {
						self.confirm();
					}
				})
			);
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {
			var self = this;

			/**
			 * Create a new inline datepicker
			 */
			this.picker = new easepick.easepick.create({

				// Calendar will be inserted after this element
				element: this.$refs.datepicker,

				// The active value
				date: this.selected,

				// Apply inline logic
				inline: true,

				// Use module's default styling
				css: easepickCss,

				// Use active language
				lang: l10nService.getLanguageAlias("easepick"),

				// Remove default navigation icons
				locale: {
					nextMonth: "",
					previousMonth: ""
				},

				// Remove document click handler
				documentClick: false,

				// Expand functionality
				plugins: [

					// Add support for keyboard navigation
					easepickKbd.KbdPlugin
				],

				/**
				 * Augment default setup of Easepicker
				 *
				 * @param  {Object} picker Easepicker instance
				 * @return {Void}
				 */
				setup: function( picker ) {
					var initialRender = true;

					// Show datepicker
					picker.ui.wrapper.style.display = "";

					// Set element part attribute for style manipulation in Shadow DOM
					picker.ui.container.setAttribute("part", "container");

					/**
					 * Set the calendar's context date to the first day of the month
					 *
					 * This counters a bug where, if today is the 31st of the month, navigating
					 * to the previous month still results in the same month.
					 */
					picker.calendars[0].subtract(picker.calendars[0].getDate() - 1, "day");

					/**
					 * Act when the calendar is rendered
					 */
					picker.on("render", function( event ) {
						var today, selected, prevBtn, nextBtn;

						/**
						 * Only execute on container render
						 * 
						 * Easepick renders all elements twice, first for the Main view,
						 * then for the Container view.
						 */
						if ("Container" !== event.detail.view) {
							return;
						}

						// Set element part attributes for style manipulation in Shadow DOM
						picker.ui.container.querySelector("main").setAttribute("part", "main");
						picker.ui.container.querySelector(".calendar").setAttribute("part", "calendar");
						picker.ui.container.querySelector(".header").setAttribute("part", "header");
						picker.ui.container.querySelector(".month-name span").setAttribute("part", "month-name-span");
						picker.ui.container.querySelectorAll(".day").forEach(el => el.setAttribute("part", "day"));

						// Navigation
						prevBtn = picker.ui.container.querySelector(".header > button:first-of-type");
						prevBtn.setAttribute("part", "nav-button nav-prev-button");
						prevBtn.setAttribute("title", l10nService.get("Generic.PreviousMonth"));
						nextBtn = picker.ui.container.querySelector(".header > button:last-of-type");
						nextBtn.setAttribute("part", "nav-button nav-next-button");
						nextBtn.setAttribute("title", l10nService.get("Generic.NextMonth"));
						
						// Today might not be rendered
						today = picker.ui.container.querySelector(".today");
						today && today.setAttribute("part", "day today");
						today && today.setAttribute("title", l10nService.get("Generic.Today"));

						// Selected might not be rendered
						selected = picker.ui.container.querySelector(".selected");
						selected && selected.setAttribute("part", "day selected");
						selected && selected.setAttribute("title", l10nService.get("Generic.SelectedDate"));

						// When this is the initial render...
						if (initialRender) {
							initialRender = false;

							// Open with focus on the selected day, otherwise today
							selected ? selected.focus() : today && today.focus();
						}
					});

					/**
					 * Act when a date is selected
					 */
					picker.on("click", function( event ) {

						// Get the element's part identifier
						var part = event.target.getAttribute("part");

						/**
						 * Focus is lost on the 'click' event, so reset it
						 *
						 * Wrap in zero delay to skip a tick and execute after the
						 * calendar is re-rendered.
						 *
						 * Elements that require focus:
						 * - nav-prev-button
						 * - nav-next-button
						 * - selected
						 */
						delayService(0).then( function() {
							/**
							 * For navigation only handle clicks from the calendar's custom events
							 *
							 * Generic click events (Event) are also dispatched from the
							 * KbdPlugin for navigating with arrow keys between months,
							 * but those events have their own focus resolutions.
							 */
							var isPointerEvent = event instanceof PointerEvent;

							// Reset focus on the nav-prev-button
							if (-1 !== part.indexOf("nav-prev-button") && isPointerEvent) {
								picker.ui.container.querySelector(".header > button:first-of-type").focus();

							// Reset focus on the nav-next-button
							} else if (-1 !== part.indexOf("nav-next-button") && isPointerEvent) {
								picker.ui.container.querySelector(".header > button:last-of-type").focus();

							// Reset focus on the day that was selected
							// Focus of the selected day is handled in the KbdPlugin, but only
							// when `options.autoApply` is false.
							} else if (-1 !== part.indexOf("day")) {
								picker.ui.container.querySelector(".selected").focus();
							}
						});
					});

					/**
					 * Act when a date is selected
					 */
					picker.on("select", function( event ) {
						self.select(event.detail.date);
					});
				}
			});

			// When the language is set outside the dialog, update the calendar
			this.$registerUnobservable(
				l10nService.on("set", function( language ) {

					// Render calendar with new language setting
					self.picker.options.lang = l10nService.getLanguageAlias("easepick");
					self.picker.renderAll();
				})
			);
		},

		/**
		 * Act before the component is destroyed
		 *
		 * @return {Void}
		 */
		beforeDestroy: function() {
			this.picker.destroy();
		}
	};
});
