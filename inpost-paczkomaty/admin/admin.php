<?php


function inpost_settings_init() {

	// Register a new setting for "inpost_paczkomaty_settings" page.
	register_setting( 'inpost_paczkomaty_settings', 'inpost_paczkomaty_options' );

	// Register a new section in the "inpost_paczkomaty_settings" page.
	add_settings_section(
		'inpost_section_developers',
		__( 'Settings', 'inpost-paczkomaty' ), 'inpost_paczkomaty_settings_section_developers_callback',
		'inpost_paczkomaty_settings'
	);

	// Register a new field in the "inpost_section_developers" section, inside the "inpost_paczkomaty_settings" page.
	add_settings_field(
		'ip_selected_as_shipping', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Save selected paczkomat as shipping address', 'inpost-paczkomaty' ),
		'ip_selected_as_shipping_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_selected_as_shipping',
		)
	);
	add_settings_field(
		'ip_select_show_logo', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Show logo in cart and checkout', 'inpost-paczkomaty' ),
		'ip_select_show_logo_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_show_logo',
		)
	);
	add_settings_field( // Option 1
		'ip_select_show_logo_img', // Option ID
		__( 'Logo', 'inpost-paczkomaty' ),
		'ip_select_show_logo_img_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array( // The $args
			'label_for' => 'ip_select_show_logo_img'
		)
	);

	add_settings_field(
		'ip_select_weight_limit', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Weight limit', 'inpost-paczkomaty' ),
		'ip_select_weight_limit_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_weight_limit',
		)
	);

	add_settings_field(
		'ip_select_weight_limit_value', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Max weight (kg)', 'inpost-paczkomaty' ),
		'ip_select_weight_limit_value_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_weight_limit_value',
		)
	);
	add_settings_field(
		'ip_select_weight_limit_result', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Weight limit result', 'inpost-paczkomaty' ),
		'ip_select_weight_limit_result_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_weight_limit_result',
		)
	);

	add_settings_field(
		'ip_select_dimensions_limit', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Dimensions limit', 'inpost-paczkomaty' ),
		'ip_select_dimensions_limit_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_dimensions_limit',
		)
	);
	add_settings_field(
		'ip_select_dimensions_limit_width', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Max width (cm)', 'inpost-paczkomaty' ),
		'ip_dimensions_limit_width_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_dimensions_limit_width',
		)
	);
	add_settings_field(
		'ip_select_dimensions_limit_height', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Max height (cm)', 'inpost-paczkomaty' ),
		'ip_dimensions_limit_height_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_dimensions_limit_height',
		)
	);
	add_settings_field(
		'ip_select_dimensions_limit_length', // As of WP 4.6 this value is used only internally.
		// Use $args' label_for to populate the id inside the callback.
		__( 'Max length (cm)', 'inpost-paczkomaty' ),
		'ip_dimensions_limit_length_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array(
			'label_for' => 'ip_select_dimensions_limit_length',
		)
	);

	add_settings_field( // Option 1
		'save_shortcode_cart_checkout', // Option ID
		__( 'Restore the classic view of the cart and checkout', 'inpost-paczkomaty' ),
		'save_shortcode_cart_checkout_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers',
		array( // The $args
			'label_for' => 'save_shortcode_cart_checkout'
		)
	);

}

/**
 * Register our inpost_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'inpost_settings_init' );


/**
 * Developers section callback function.
 *
 * @param array $args The settings array, defining title, id, callback.
 */
function inpost_paczkomaty_settings_section_developers_callback( $args ) {
	?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Inpost Paczkomaty settings', 'inpost-paczkomaty' ); ?></p>
	<?php
}

function ip_select_dimensions_limit_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'inpost_paczkomaty_options' );
	?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="inpost_paczkomaty_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="no" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'No', 'inpost-paczkomaty' ); ?>
        </option>
        <option value="yes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'yes', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'Yes', 'inpost-paczkomaty' ); ?>
        </option>

    </select>
    <p class="description">
		<?php esc_html_e( 'If you select "Yes", shipping method will be influenced by dimensions.', 'inpost-paczkomaty' ); ?>
    </p>


	<?php
}

function ip_dimensions_limit_width_cb( $args ) {
	$options = get_option( 'inpost_paczkomaty_options' );

	$val = '';
	if ( isset( $options[ $args['label_for'] ] ) && ! empty( $options[ $args['label_for'] ] ) ) {
		$val = $options[ $args['label_for'] ];
	}

	// Wyświetlenie pola input typu number, gdzie będzie przechowywana wartość opcji
	echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="inpost_paczkomaty_options[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $val ) . '" min="0" step="1">'; // Zmień 'nazwa_twojej_opcji'

}

function ip_dimensions_limit_length_cb( $args ) {
	$options = get_option( 'inpost_paczkomaty_options' );

	$val = '';
	if ( isset( $options[ $args['label_for'] ] ) && ! empty( $options[ $args['label_for'] ] ) ) {
		$val = $options[ $args['label_for'] ];
	}

	// Wyświetlenie pola input typu number, gdzie będzie przechowywana wartość opcji
	echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="inpost_paczkomaty_options[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $val ) . '" min="0" step="1">'; // Zmień 'nazwa_twojej_opcji'

}

function ip_dimensions_limit_height_cb( $args ) {
	$options = get_option( 'inpost_paczkomaty_options' );

	$val = '';
	if ( isset( $options[ $args['label_for'] ] ) && ! empty( $options[ $args['label_for'] ] ) ) {
		$val = $options[ $args['label_for'] ];
	}

	// Wyświetlenie pola input typu number, gdzie będzie przechowywana wartość opcji
	echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="inpost_paczkomaty_options[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $val ) . '" min="0" step="1">'; // Zmień 'nazwa_twojej_opcji'

}

function ip_select_weight_limit_result_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'inpost_paczkomaty_options' );

	?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="inpost_paczkomaty_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="hide" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'hide', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'Hide shipping method', 'inpost-paczkomaty' ); ?>
        </option>
        <option value="split" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'split', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'Split into few packages', 'inpost-paczkomaty' ); ?>
        </option>

    </select>
    <p class="description">
		<?php esc_html_e( 'What to do if all items across weight limit', 'inpost-paczkomaty' ); ?>
    </p>
	<?php
}

function ip_select_weight_limit_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'inpost_paczkomaty_options' );

	?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="inpost_paczkomaty_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="no" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'No', 'inpost-paczkomaty' ); ?>
        </option>
        <option value="yes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'yes', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'Yes', 'inpost-paczkomaty' ); ?>
        </option>

    </select>
    <p class="description">
		<?php esc_html_e( 'If you select "Yes", shipping method will be influenced by weight.', 'inpost-paczkomaty' ); ?>
    </p>

	<?php
}

function ip_select_weight_limit_value_cb( $args ) {
	$options = get_option( 'inpost_paczkomaty_options' );

	$val = '';
	if ( isset( $options[ $args['label_for'] ] ) && ! empty( $options[ $args['label_for'] ] ) ) {
		$val = $options[ $args['label_for'] ];
	}
	// Wyświetlenie pola input typu number, gdzie będzie przechowywana wartość opcji
	echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="inpost_paczkomaty_options[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $val ) . '" min="0" step="1">'; // Zmień 'nazwa_twojej_opcji'

}

/**
 * Pill field callbakc function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function ip_select_show_logo_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'inpost_paczkomaty_options' );

	?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="inpost_paczkomaty_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="no" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'No', 'inpost-paczkomaty' ); ?>
        </option>
        <option value="yes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'yes', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'Yes', 'inpost-paczkomaty' ); ?>
        </option>

    </select>
    <p class="description">
		<?php esc_html_e( 'If you select "Yes", logo will shown in cart and checkout.', 'inpost-paczkomaty' ); ?>
    </p>

	<?php
}

/**
 * Pill field callbakc function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function ip_selected_as_shipping_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'inpost_paczkomaty_options' );

	?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="inpost_paczkomaty_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="no" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'No', 'inpost-paczkomaty' ); ?>
        </option>
        <option value="yes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'yes', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'Yes', 'inpost-paczkomaty' ); ?>
        </option>

    </select>
    <p class="description">
		<?php esc_html_e( 'If you select "Yes", when client make new order and choose Inpost Paczkomaty as shipping method, in his shipping address will be selected paczkomat.', 'inpost-paczkomaty' ); ?>
    </p>

	<?php
}


function save_shortcode_cart_checkout_cb( $args ) {
	$option_value = get_option( 'inpost_paczkomaty_options' ); //
	echo '<input type="button" class="button" variant="primary" id="shortcode_cart_checkout" value="' . __( "Restore", "inpost-paczkomaty" ) . '"></input>';

	$message = __( "Are you sure? This will overwrite your cart and checkout settings and change them to the classic cart and checkout. It is recommended to make a backup!", "inpost-paczkomaty" );
	wp_enqueue_script( 'save-checkout-script', plugin_dir_url( __FILE__ ) . 'js/save-checkout.js', array( 'jquery' ), '1.0', true );
	wp_localize_script( 'save-checkout-script', 'custom_ajax_object', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'message'  => $message
	) );
	?>

	<?php
}

function ip_select_show_logo_img_cb( $args ) {  //  Callback

	$option_value = get_option( 'inpost_paczkomaty_options' ); //

	if ( isset( $option_value[ $args['label_for'] ] ) && ! empty( $option_value[ $args['label_for'] ] ) ) {
		echo '<div id="image_preview">';
		echo '<img src="' . esc_url( $option_value[ $args['label_for'] ] ) . '" style="max-width: 200px; height: auto;" />';
		echo '</div>';
		echo '<input type="button" id="remove_image_button" class="button" value="Usuń obraz">';
	}
	// Wyświetlenie podglądu wybranego obrazu
	echo '<div id="image_preview"></div>';

	$val = '';
	if ( isset( $option_value[ $args['label_for'] ] ) && ! empty( $option_value[ $args['label_for'] ] ) ) {
		$val = $option_value[ $args['label_for'] ];
	}

	// Wyświetlenie pola input typu hidden, w którym będzie przechowywana wartość wybranej opcji
	echo '<input type="hidden" id="' . $args['label_for'] . '" name="inpost_paczkomaty_options[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $val ) . '">';

	// Wyświetlenie przycisku do otwierania media uploadera
	echo '<input type="button" id="upload_image_button" class="button" value="Wybierz obraz">';

	// Dodanie przycisku do usuwania obrazu
	echo '<input type="button" id="remove_image_button" class="button" value="Usuń obraz" style="display:none;">';
	wp_enqueue_media();
	// Skrypt JavaScript do obsługi media uploadera
	?>

    <script>
        jQuery(document).ready(function ($) {
            $('#upload_image_button').click(function (e) {
                e.preventDefault();

                var custom_uploader = wp.media({
                    title: 'Choose image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false // Ustaw na true, jeśli chcesz wybrać wiele obrazów
                });

                custom_uploader.on('select', function () {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $('#ip_select_show_logo_img').val(attachment.url);
                    $('#image_preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
                    $('#remove_image_button').show(); // Pokazanie przycisku usuwania obrazu
                });

                custom_uploader.open();
            });
            // Obsługa usuwania obrazu
            $('#remove_image_button').click(function (e) {
                e.preventDefault();

                $('#ip_select_show_logo_img').val(''); // Wyczyszczenie wartości ukrytego pola przechowującego ścieżkę obrazu
                $('#image_preview').html(''); // Usunięcie podglądu obrazu
                $(this).hide(); // Ukrycie przycisku usuwania obrazu
            });
        });
    </script>
	<?php
}

/**
 * Add the top level menu page.
 */
function inpost_paczkomaty_settings_page() {
	add_submenu_page(
		'woocommerce',
		'Inpost Paczkomaty Settings',
		'Inpost Paczkomaty',
		'manage_options',
		'inpost_paczkomaty_settings',
		'inpost_paczkomaty_options_page_html'
	);
}


/**
 * Register our inpost_paczkomaty_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'inpost_paczkomaty_settings_page' );


/**
 * Top level menu callback function
 */
function inpost_paczkomaty_options_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	// add error/update messages
	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved', 'inpost-paczkomaty' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'wporg_messages' );
	?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "inpost_paczkomaty_settings"
			settings_fields( 'inpost_paczkomaty_settings' );
			// output setting sections and their fields
			// (sections are registered for "inpost_paczkomaty_settings", each field is registered to a specific section)
			do_settings_sections( 'inpost_paczkomaty_settings' );
			// output save settings button
			submit_button( 'Save Settings' );
			?>
        </form>
    </div>
	<?php
}

function review_plugin_admin_notice__success() {
	$screen = get_current_screen();
	if ( $screen->id !== 'woocommerce_page_inpost_paczkomaty_settings' ) {
		return;
	}
	?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( "Thank you very much for using my plugin! If you like it and you're satisfied, please leave a review <a href='https://wordpress.org/support/plugin/inpost-paczkomaty/reviews/'>Here</a>. ", 'inpost-paczkomaty' ) ?></p>
    </div>
	<?php
}

add_action( 'admin_notices', 'review_plugin_admin_notice__success' );


function save_shortcode_cart_checkout_ajax_handler() {

	if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$cart_page  = get_option( 'woocommerce_cart_page_id' );
		$cart_array = array(
			'ID'           => $cart_page,
			'post_content' => '[woocommerce_cart]',
		);
		$update     = wp_update_post( $cart_array );

		$checkout_page  = get_option( 'woocommerce_checkout_page_id' );
		$checkout_array = array(
			'ID'           => $checkout_page,
			'post_content' => '[woocommerce_checkout]',
		);
		$update         = wp_update_post( $checkout_array );
		wp_send_json_success( array( 'message' => _e( 'Sukcess! sprawdź teraz swój koszyk oraz checkout', 'inpost-paczkomaty' ) ) );
		wp_die();
	}
	wp_die();
}

add_action( 'wp_ajax_save_shortcode_cart_checkout_ajax', 'save_shortcode_cart_checkout_ajax_handler' );

?>
