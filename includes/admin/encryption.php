<?php

/**
 * Incassoos Admin Encryption Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Page ****************************************************************/

/**
 * Output the contents of the Encryption admin page
 *
 * @since 1.0.0
 */
function incassoos_admin_encryption_page() { ?>

	<div class="page-information-box">
		<h1 class="page-title"><?php incassoos_admin_the_page_title(); ?></h1>

		<?php if ( ! incassoos_is_encryption_supported() ) : ?>

		<div class="encryption-not-supported">
			<p><?php _e( 'Encryption helps with securing <strong>sensitive data</strong> by obscuring the data when registering it in your database. If the database would ever get compromised, there is no value in the obscured data. Only using a <strong>password</strong> can the original sensitive data be accessed again.', 'incassoos' ); ?></p>

			<div class="notice notice-warning inline">
				<p><?php printf( esc_html__( 'Encryption is currently not supported on %s.', 'incassoos' ), site_url() ); ?></p>
			</div>

			<p><?php printf( esc_html__( "To be able to use encryption in Incassoos on %s, please update the website's PHP version to 7.2 or greater or make sure the site uses WordPress 5.2 or greater.", 'incassoos' ), site_url() ); ?></p>

			<p class="step-navigation">
				<a class="button button-primary alignright" href="<?php echo add_query_arg( 'page', 'incassoos' ); ?>"><?php esc_html_e( 'Return to dashboard', 'incassoos' ); ?></a>
			</p>
		</div>

		<?php else : ?>

		<form method="post" class="encryption-form">
			<script>
				jQuery( 'body' )
					.on( 'click', '.step-item .next-step', function( event ) {
						var dfd = jQuery.Deferred(),
						    ajaxData = event.target.getAttribute( 'data-ajax-data' ),
						    ajaxThen = event.target.getAttribute( 'data-ajax-then' ),
						    $step = jQuery( '.active-step' );

						if ( ajaxData ) {
							ajaxData = JSON.parse( ajaxData );

							// Indicate loading
							$step.find( '.spinner' ).addClass( 'is-active' );
							$step.find( '.step-navigation button' ).attr( 'disabled', true );

							// Process dynamic AJAX data. Retreive data from input with specified id selector
							for ( var i in ajaxData ) {
								if ( ajaxData[i].startsWith( '#' ) ) {
									ajaxData[i] = $step.find( ajaxData[i] ).val();
								}
							}

							// Post AJAX action
							jQuery.ajax({
								type: 'POST',
								url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
								data: ajaxData,
								dataType: 'json'
							}).always( function( resp ) {
								resp = resp || { success: false, data: [{ message: '<?php esc_html_e( 'An unknown error occurred.', 'incassoos' ); ?>' }] };

								// Check success or fail
								if ( ! resp.success ) {
									dfd.reject( resp.data[0].message );
								} else {
									if ( ajaxThen ) {
										ajaxThen = JSON.parse( ajaxThen );

										// Assume target and value are found
										jQuery( ajaxThen.target ).val( ajaxThen.jsonPath.replace( '$.', '' ).split( '.' ).reduce( function( accumulator, key ) {
											return accumulator && accumulator[ key ];
										}, resp ));
									}

									dfd.resolve();
								}
							});
						} else {
							dfd.resolve();
						}

						// When dfd is released
						dfd.always( function( message ) {

							// Indicate loading stopped
							$step.find( '.spinner' ).removeClass( 'is-active' );
							$step.find( '.step-navigation button' ).attr( 'disabled', false );

							// Report message when provided
							if ( message ) {
								alert( message );

							// Continue stepping otherwise
							} else {
								$step.removeClass( 'active-step' )
									.next().addClass( 'active-step' );
							}
						});
					})
					.on( 'click', '.step-item .prev-step', function() {
						jQuery( '.active-step' ).removeClass( 'active-step' ).prev().addClass( 'active-step' );
					})
					.on( 'click', '#toggle-password-visibility', function() {
						var $this = jQuery( '#decryption-key' );
						$this.attr( 'type', $this.is( ':password' ) ? 'text' : 'password' ).next().toggleClass( 'password' );
					});
			</script>

			<?php if ( ! incassoos_is_encryption_enabled() ) :

				// Get the wizard steps
				$wizard = incassoos_admin_get_enable_encryption_steps();

				// Load clipboard script
				wp_enqueue_script( 'clipboard' );
				wp_add_inline_script( 'clipboard', '
					(new ClipboardJS( "#copy-decryption-key" ))
						.on( "success", function( event ) {
							// Reset focus to trigger element
							event.clearSelection();
							jQuery( event.trigger ).focus();
						});'
				);
			?>

			<p><?php printf( esc_html__( 'This page provides a wizard for enabling data encryption for sensitive data in Incassoos on %s.', 'incassoos' ), site_url() ); ?></p>

			<?php else :

				// Get the steps
				$wizard = incassoos_admin_get_disable_encryption_steps();
			?>

			<p><?php printf( esc_html__( 'This page provides details of the encryption that is enabled for sensitive data in Incassoos on %s.', 'incassoos' ), site_url() ); ?></p>

			<?php endif; ?>

			<ul class="encryption-step-items">
				<?php foreach ( $wizard as $step => $args ) :
					$class = 'step-item';
					if ( 'welcome' === $step ) {
						$class .= ' active-step';
					}
				?>

				<li id="encryption-step-<?php echo $step; ?>" class="<?php echo $class; ?>">
					<?php if ( isset( $args['title'] ) ) : ?>
					<h2 class="step-title"><?php echo $args['title']; ?></h2>
					<?php endif; ?>

					<?php if ( isset( $args['description'] ) ) : ?>
					<div class="step-description">
						<?php echo $args['description']; ?>
					</div>
					<?php endif; ?>

					<p class="step-navigation">
						<?php

						// Setup next/prev buttons
						$next = wp_parse_args( isset( $args['next'] ) ? $args['next'] : array(), array(
							'label'    => isset( $args['next_label'] )    ? $args['next_label']    : esc_html__( 'Continue', 'incassoos' ),
							'class'    => isset( $args['next_class'] )    ? $args['next_class']    : 'button-primary',
							'url'      => isset( $args['next_url'] )      ? $args['next_url']      : false,
							'ajaxData' => isset( $args['next_ajaxData'] ) ? $args['next_ajaxData'] : '',
							'ajaxThen' => isset( $args['next_ajaxThen'] ) ? $args['next_ajaxThen'] : ''
						));
						$next_class     = "button alignright {$next['class']}";
						$next_ajax_data = ! empty( $next['ajaxData'] ) ? htmlentities( json_encode( $next['ajaxData'] ), ENT_QUOTES, 'UTF-8' ) : '';
						$next_ajax_then = ! empty( $next['ajaxThen'] ) ? htmlentities( json_encode( $next['ajaxThen'] ), ENT_QUOTES, 'UTF-8' ) : '';

						$prev = wp_parse_args( isset( $args['prev'] ) ? $args['prev'] : array(), array(
							'label'    => isset( $args['prev_label'] )    ? $args['prev_label']    : '',
							'class'    => isset( $args['prev_class'] )    ? $args['prev_class']    : ''
						));
						$prev_class = "button alignleft {$prev['class']}";

						?>

						<?php if ( ! empty( $next['url'] ) ) : ?>
							<a class="<?php echo $next_class; ?>" href="<?php echo esc_attr( $next['url'] ); ?>"><?php echo esc_html( $next['label'] ); ?></a>
						<?php elseif ( ! empty( $next['ajaxData'] ) ) : ?>
							<button type="button" class="<?php echo $next_class; ?> next-step" data-ajax-data="<?php echo $next_ajax_data; ?>" data-ajax-then="<?php echo $next_ajax_then; ?>"><?php echo esc_html( $next['label'] ); ?></button>
							<span class="spinner"></span>
						<?php else : ?>
							<button type="button" class="<?php echo $next_class; ?> next-step"><?php echo esc_html( $next['label'] ); ?></button>
						<?php endif; ?>

						<?php if ( ! empty( $prev['label'] ) ) : ?>
							<button type="button" class="<?php echo $prev_class; ?> prev-step"><?php echo esc_html( $prev['label'] ); ?></button>
						<?php endif; ?>
					</p>
				</li>

				<?php endforeach; ?>
			</ul>

		</form>

		<?php endif; ?>
	</div>

	<?php
}

/**
 * Return the admin steps for enabling encryption
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_enable_encryption_steps'
 * @return array Steps for enabling encryption
 */
function incassoos_admin_get_enable_encryption_steps() {
	return (array) apply_filters( 'incassoos_admin_get_enable_encryption_steps', array(

		// Welcome
		'welcome'   => array(
			'description' =>
				'<div class="notice notice-warning inline"><p>' . esc_html__( 'Encryption is currently not enabled for Incassoos.', 'incassoos' ) . '</p></div>' .
				'<p>' . esc_html__( 'Your system meets the requirements for setting up encryption in Incassoos. With encryption enabled, the following data will be encrypted when it is saved:', 'incassoos' ) . '</p>' .
				'<ul>' . array_reduce( incassoos_admin_get_encryptable_data(), function( $carry, $item ) {
					return $carry . '<li>' . $item . '</li>';
				}, '' ) . '</ul>' .
				'<p>' . esc_html__( 'The following button will start the wizard for enabling encryption.', 'incassoos' ) . '</p>',
			'next_label'  => esc_html__( 'Start wizard', 'incassoos' )
		),

		// Start
		'start' => array(
			'title'       => esc_html__( 'Why encrypt data?', 'incassoos' ),
			'description' =>
				'<p>' . __( 'When your website works with personal details or other <strong>sensitive data</strong>, security is important. Measures should be put in place to prevent data from getting into the wrong hands. However, in the unlikely event that unpermitted persons do gain access to this data, it is best that the data should be of no use to them.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'Encryption helps with securing <strong>sensitive data</strong> by obscuring the data when registering it in your database. If the database would ever get compromised, there is no value in the obscured data. Only using a <strong>password</strong> can the original sensitive data be accessed again.', 'incassoos' ) . '</p>',
			'prev_label'  => esc_html__( 'Back', 'incassoos' ),
			'next_label'  => esc_html__( 'Continue', 'incassoos' )
		),

		// How it works
		'how-it-works' => array(
			'title'       => esc_html__( 'How it works', 'incassoos' ),
			'description' =>
				'<p>' . esc_html__( 'Encryption obscures data by way of scrambling (encryption) and unscrambling (decryption) the values. Incassoos works with public-key encryption. This means that data is scrambled using a known password, while unscrambling requires a secret password. The encryption passwords (keys) will be generated for you.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'The known <strong>public key</strong> is used when data is saved or updated in the system. The key will be stored in the database. There is no security issue when the public key is discovered, because it is only used for scrambling data.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'The secret <strong>private key</strong> is used for decrypting data and should be stored separately from the system. It is required when you need to access the original values in the system. It is important that this key should not be compromised.', 'incassoos' ) . '</p>',
			'prev_label'  => esc_html__( 'Back', 'incassoos' ),
			'next_label'  => esc_html__( 'Continue', 'incassoos' )
		),

		// Enabling encryption
		'generate-keys' => array(
			'title'       => esc_html__( 'Enabling encryption', 'incassoos' ),
			'description' =>
				'<p>' . esc_html__( 'As mentioned, to setup encryption a set of encryption keys will be generated. The keys are the passwords for the encryption and decryption process.', 'incassoos' ) . '</p>' .
				'<ol>' .
					'<li>' . __( 'The <strong>public key</strong> will be stored in the database.', 'incassoos' ) . '</li>' .
					'<li>' . __( 'The <strong>private key</strong> will be shown on screen for you to store separately from the system.', 'incassoos' ) . '</li>' .
				'</ol>' .
				'<p>' . esc_html__( 'After the keys are generated, the registered sensitive data will be encrypted as well.', 'incassoos' ) . '</p>' .
				'<p>' . sprintf( esc_html__( 'Click the %1$s button to generate the keys and enable encryption in Incassoos on %2$s.', 'incassoos' ), "'" . esc_html__( 'Enable encryption', 'incassoos' ) . "'", site_url() ) . '</p>',
			'prev_label'  => esc_html__( 'Back', 'incassoos' ),
			'next'        => array(
				'label'    => esc_html__( 'Enable encryption', 'incassoos' ),
			    'ajaxData' => array(
			    	'action'   => 'incassoos_enable_encryption',
			    	'_wpnonce' => wp_create_nonce( 'incassoos_enable_encryption_nonce' )
			    ),
			    'ajaxThen' => array(
			    	'jsonPath' => '$.data.decryptionKey',
			    	'target'   => '#decryption-key'
			    )
			)
		),

		// Activated
		'activated' => array(
			'title'       => esc_html__( 'Your private key', 'incassoos' ),
			'description' =>
				'<p>' . esc_html__( 'The encryption keys were successfully generated! The public key is stored in the database, while the following is the private key:', 'incassoos' ) . '</p>' .
				'<label>' .
					'<input type="text" id="decryption-key" class="regular-text" value="" />' .
					'<button type="button" id="copy-decryption-key" class="button button-in-input hide-if-no-js" data-clipboard-target="#decryption-key" title="' . esc_html__( 'Copy to clipboard', 'incassoos' ) . '"><span class="screen-reader-text">' . esc_html__( 'Copy to clipboard', 'incassoos' ) . '</span></button>' .
				'</label>' .
				'<p>' . esc_html__( 'Treat the private key as a password and store it in a secure place. When access to the encrypted data is required, this password must be provided. The key is also required as a password when you should decide to disable encryption.', 'incassoos' ) . '</p>' .
				'<div class="notice notice-info inline"><p>' . esc_html__( 'NOTE: The private key is only available on this screen and cannot be retreived afterwards.', 'incassoos' ) . '</p></div>',
			'next_label'  => esc_html__( 'Continue', 'incassoos' )
		),

		// Finish
		'finish' => array(
			'title'       => esc_html__( 'Encryption is enabled', 'incassoos' ),
			'description' => 
				'<p>' . sprintf( esc_html__( 'Encryption is successfully enabled for Incassoos on %s.', 'incassoos' ), site_url() ) . '</p>' .
				'<p>' . esc_html__( 'You can close the wizard.', 'incassoos' ) . '</p>',
			'next'        => array(
				'label' => esc_html__( 'Close wizard', 'incassoos' ),
			    'url'   => add_query_arg( 'page', 'incassoos-encryption' )
			)
		)
	) );
}

/**
 * Return the admin steps for disabling encryption
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_disable_encryption_steps'
 * @return array Steps for disabling encryption
 */
function incassoos_admin_get_disable_encryption_steps() {
	return (array) apply_filters( 'incassoos_admin_get_disable_encryption_steps', array(

		// Welcome
		'welcome' => array(
			'description' =>
				'<div class="notice notice-success inline"><p>' . esc_html__( 'Encryption is currently enabled for Incassoos.', 'incassoos' ) . '</p></div>' .
				'<p>' . __( 'The data is encrypted using the <strong>public key</strong> that has been generated and stored in the database. The secret <strong>private key</strong> is used for decrypting the data. This key should be saved separately from the system.', 'incassoos' ) . '</p>' .
				'<p>' . esc_html__( 'The following data is encrypted when it is saved:', 'incassoos' ) . '</p>' .
				'<ul>' . array_reduce( incassoos_admin_get_encryptable_data(), function( $carry, $item ) {
					return $carry . '<li>' . $item . '</li>';
				}, '' ) . '</ul>' .
				'<p>' . esc_html__( 'The following button will start the wizard for disabling encryption.', 'incassoos' ) . '</p>',
			'next' => array(
				'label' => esc_html__( 'Start wizard', 'incassoos' ),
				'class' => 'button-secondary'
			)
		),

		// Start
		'start' => array(
			'title'       => esc_html__( 'Disabling encryption', 'incassoos' ),
			'description' =>
				'<p>' . __( 'When your website works with personal details or other <strong>sensitive data</strong>, security is important. Measures should be put in place to prevent data from getting into the wrong hands. However, in the unlikely event that unpermitted persons do gain access to this data, it is best that the data should be of no use to them.', 'incassoos' ) . '</p>' .
				'<p>' . esc_html__( 'However, when encryption is not relevant for your use case or you have any other reason to stop encrypting sensitive data, the encryption can be disabled.', 'incassoos' ) . '</p>',
			'prev_label'  => esc_html__( 'Back', 'incassoos' ),
			'next_label'  => esc_html__( 'Continue', 'incassoos' ),
		),

		// Start
		'are-you-sure' => array(
			'title'       => esc_html__( 'Are you sure?', 'incassoos' ),
			'description' =>
				'<p>' . esc_html__( 'To confirm your decision to disable encryption, please provide the current decryption key below. This is both a security check and a requirement for decrypting the currently encrypted data.', 'incassoos' ) . '</p>' .
				'<label>' .
					'<input type="password" id="decryption-key" name="decryption_key" class="regular-text" placeholder="' . esc_attr__( 'Enter decryption key', 'incassoos' ) . '" />' .
					'<button type="button" id="toggle-password-visibility" class="button button-in-input password hide-if-no-js" title="' . esc_html__( 'Toggle password visibility', 'incassoos' ) . '"><span class="screen-reader-text">' . esc_html__( 'Toggle password visibility', 'incassoos' ) . '</span></button>' .
				'</label>' .
				'<p>' . sprintf( esc_html__( 'With the decryption key provided, click the %1$s button to disable encryption in Incassoos on %2$s.', 'incassoos' ), "'" . esc_html__( 'Disable encryption', 'incassoos' ) . "'", site_url() ) . '</p>',
			'prev_label'  => esc_html__( 'Back', 'incassoos' ),
			'next'        => array(
				'label'    => esc_html__( 'Disable encryption', 'incassoos' ),
			    'ajaxData' => array(
			    	'action'         => 'incassoos_disable_encryption',
			    	'decryption_key' => '#decryption-key',
			    	'_wpnonce'       => wp_create_nonce( 'incassoos_disable_encryption_nonce' )
			    )
			)
		),

		// Finish
		'finish' => array(
			'title'       => esc_html__( 'Encryption is disabled', 'incassoos' ),
			'description' => 
				'<p>' . sprintf( esc_html__( 'Encryption is successfully disabled for Incassoos on %s.', 'incassoos' ), site_url() ) . '</p>' .
				'<p>' . esc_html__( 'The following data have been restored to their original values:', 'incassoos' ) . '</p>' .
				'<ul>' . array_reduce( incassoos_admin_get_encryptable_data(), function( $carry, $item ) {
					return $carry . '<li>' . $item . '</li>';
				}, '' ) . '</ul>' .
				'<p>' . esc_html__( 'You can close the wizard.', 'incassoos' ) . '</p>',
			'next_label'  => esc_html__( 'Close wizard', 'incassoos' ),
			'next_url'    => add_query_arg( 'page', 'incassoos-encryption' )
		)
	) );
}

/**
 * Return the names of data that is encryptable
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_encryptable_data'
 * @return array Encryptable data names
 */
function incassoos_admin_get_encryptable_data() {
	return apply_filters( 'incassoos_admin_get_encryptable_data', array(
		'incassoos-iban-organization'             => esc_html__( 'The IBAN of the organization', 'incassoos' ),
		'incassoos-sepa-creditor-id-organization' => esc_html__( 'The SEPA Creditor Identifier of the organization', 'incassoos' ),
		'incassoos-iban-consumers'                => esc_html__( 'The IBAN of all consumers', 'incassoos' )
	) );
}

/** AJAX ****************************************************************/

/**
 * Ajax action for enabling encryption
 *
 * @since 1.0.0
 */
function incassoos_admin_ajax_enable_encryption() {

	// Check the ajax nonce
	check_ajax_referer( 'incassoos_enable_encryption_nonce' );

	// Enable encryption
	$enabled = incassoos_enable_encryption();

	// Enabling failed
	if ( is_wp_error( $enabled ) || ! $enabled ) {
		wp_send_json_error( $enabled );

	// Enabling was successfull
	} else {
		wp_send_json_success( array( 'decryptionKey' => $enabled ) );
	}
}

/**
 * Ajax action for disabling encryption
 *
 * @since 1.0.0
 */
function incassoos_admin_ajax_disable_encryption() {

	// Check the ajax nonce
	check_ajax_referer( 'incassoos_disable_encryption_nonce' );

	// Require decryption key
	$decryption_key = isset( $_REQUEST['decryption_key'] ) ? $_REQUEST['decryption_key'] : false;
	if ( ! $decryption_key ) {
		wp_send_json_error( new WP_Error(
			'incassoos_no_decryption_key',
			esc_html__( 'The provided decryption key is empty.', 'incassoos' )
		) );
	}

	// Disable encryption
	$disabled = incassoos_disable_encryption( $decryption_key );

	// Disabling failed
	if ( is_wp_error( $disabled ) || ! $disabled ) {
		wp_send_json_error( $disabled );

	// Disabling was successfull
	} else {
		wp_send_json_success( $disabled );
	}
}
