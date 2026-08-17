<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Whitelist-sanitize the plugin options array before it is saved to the DB.
 * Unknown keys are dropped; each known key is coerced to its expected type.
 *
 * @param mixed $input Raw value submitted via options.php.
 *
 * @return array Sanitized options.
 */
function inpost_paczkomaty_sanitize_options( $input ) {
	$output = array();

	if ( ! is_array( $input ) ) {
		return $output;
	}

	$yes_no_fields = array(
		'ip_selected_as_shipping',
		'ip_select_show_logo',
		'ip_select_weight_limit',
		'ip_select_dimensions_limit',
	);

	foreach ( $yes_no_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$output[ $field ] = ( 'yes' === $input[ $field ] ) ? 'yes' : 'no';
		}
	}

	if ( isset( $input['ip_select_weight_limit_result'] ) ) {
		$output['ip_select_weight_limit_result'] = in_array( $input['ip_select_weight_limit_result'], array( 'hide', 'split' ), true )
			? $input['ip_select_weight_limit_result']
			: 'hide';
	}

	if ( isset( $input['ip_checkout_mode_override'] ) ) {
		$output['ip_checkout_mode_override'] = in_array( $input['ip_checkout_mode_override'], array( 'block', 'classic' ), true )
			? $input['ip_checkout_mode_override']
			: 'block';
	}

	$numeric_fields = array(
		'ip_select_weight_limit_value',
		'ip_select_dimensions_limit_width',
		'ip_select_dimensions_limit_height',
		'ip_select_dimensions_limit_length',
	);

	foreach ( $numeric_fields as $field ) {
		if ( isset( $input[ $field ] ) && '' !== $input[ $field ] ) {
			$output[ $field ] = absint( $input[ $field ] );
		}
	}

	if ( isset( $input['ip_select_show_logo_img'] ) ) {
		$output['ip_select_show_logo_img'] = esc_url_raw( $input['ip_select_show_logo_img'] );
	}

	return $output;
}

function inpost_settings_init() {

	// Register a new setting for "inpost_paczkomaty_settings" page.
	register_setting(
		'inpost_paczkomaty_settings',
		'inpost_paczkomaty_options',
		array( 'sanitize_callback' => 'inpost_paczkomaty_sanitize_options' )
	);

	// Register a new section in the "inpost_paczkomaty_settings" page.
	add_settings_section(
		'inpost_section_developers',
		__( 'Settings', 'inpost-paczkomaty' ), 'inpost_paczkomaty_settings_section_developers_callback',
		'inpost_paczkomaty_settings'
	);

	// Detected checkout mode info (read-only, auto-detected from page content).
	add_settings_field(
		'ip_detected_checkout_mode',
		__( 'Tryb checkout (wykryty automatycznie)', 'inpost-paczkomaty' ),
		'ip_detected_checkout_mode_cb',
		'inpost_paczkomaty_settings',
		'inpost_section_developers'
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
 * Field showing the auto-detected checkout mode.
 * Handles three states: block, classic, or conflict (mixed setup).
 * In conflict state a manual override radio is rendered inline.
 */
function ip_detected_checkout_mode_cb() {
	$checkout_page_id = (int) get_option( 'woocommerce_checkout_page_id' );
	$cart_page_id     = (int) get_option( 'woocommerce_cart_page_id' );

	$checkout_post = $checkout_page_id > 0 ? get_post( $checkout_page_id ) : null;
	$cart_post     = $cart_page_id > 0     ? get_post( $cart_page_id )     : null;

	$checkout_has_block = $checkout_post && has_block( 'woocommerce/checkout', $checkout_post );
	$cart_has_block     = $cart_post     && has_block( 'woocommerce/cart',     $cart_post );

	// Determine overall mode.
	if ( $checkout_has_block && $cart_has_block ) {
		$mode = 'block';
	} elseif ( ! $checkout_has_block && ! $cart_has_block ) {
		$mode = 'classic';
	} else {
		$mode = 'conflict';
	}

	if ( 'block' === $mode ) {
		?>
		<span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #00a32a; color: #fff; font-weight: 600;">
			<?php esc_html_e( '✅ Checkout blokowy (WooCommerce Blocks)', 'inpost-paczkomaty' ); ?>
		</span>
		<p class="description"><?php esc_html_e( 'Strona checkoutu i/lub koszyka używa bloków WooCommerce. Selektor paczkomatu pojawi się automatycznie w sekcji wysyłki po wybraniu metody InPost Paczkomaty.', 'inpost-paczkomaty' ); ?></p>
		<?php
	} elseif ( 'classic' === $mode ) {
		?>
		<span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #2271b1; color: #fff; font-weight: 600;">
			<?php esc_html_e( '🔷 Checkout klasyczny (shortcode)', 'inpost-paczkomaty' ); ?>
		</span>
		<p class="description"><?php esc_html_e( 'Strony checkoutu i koszyka używają klasycznych shortcode\'ów ([woocommerce_checkout] / [woocommerce_cart]). Selektor paczkomatu jest wstrzykiwany przez hooki PHP. Aby przełączyć na checkout blokowy, zastąp zawartość strony blokami WooCommerce w edytorze lub użyj przycisku "Przywróć" poniżej.', 'inpost-paczkomaty' ); ?></p>
		<?php
	} else {
		// Conflict: one page is block, the other is classic.
		$options  = get_option( 'inpost_paczkomaty_options' );
		$override = isset( $options['ip_checkout_mode_override'] ) ? $options['ip_checkout_mode_override'] : 'block';

		$cart_label     = $cart_has_block
			? '<span style="color:#00a32a;font-weight:600;">' . esc_html__( 'Blokowy', 'inpost-paczkomaty' ) . '</span>'
			: '<span style="color:#2271b1;font-weight:600;">' . esc_html__( 'Klasyczny', 'inpost-paczkomaty' ) . '</span>';
		$checkout_label = $checkout_has_block
			? '<span style="color:#00a32a;font-weight:600;">' . esc_html__( 'Blokowy', 'inpost-paczkomaty' ) . '</span>'
			: '<span style="color:#2271b1;font-weight:600;">' . esc_html__( 'Klasyczny', 'inpost-paczkomaty' ) . '</span>';
		?>
		<span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #d63638; color: #fff; font-weight: 600;">
			<?php esc_html_e( '⚠️ Wykryto konflikt', 'inpost-paczkomaty' ); ?>
		</span>

		<p style="margin-top: 8px;">
			<strong><?php esc_html_e( 'Strona koszyka', 'inpost-paczkomaty' ); ?>:</strong>
			<?php echo wp_kses( $cart_label, array( 'span' => array( 'style' => array() ) ) ); ?>
			&nbsp;|&nbsp;
			<strong><?php esc_html_e( 'Strona checkout', 'inpost-paczkomaty' ); ?>:</strong>
			<?php echo wp_kses( $checkout_label, array( 'span' => array( 'style' => array() ) ) ); ?>
		</p>

		<p class="description">
			<?php esc_html_e( 'Twój koszyk i strona checkout używają różnych trybów (jeden używa bloków WooCommerce, drugi klasycznych shortcode\'ów). Wybierz, który tryb ma być używany przez wtyczkę, lub ujednolić obie strony.', 'inpost-paczkomaty' ); ?>
		</p>

		<fieldset style="margin-top: 10px; border: 1px solid #ddd; padding: 10px 14px; border-radius: 4px; background: #fff8f0;">
			<legend style="font-weight: 600; padding: 0 4px;"><?php esc_html_e( 'Ręczny wybór trybu', 'inpost-paczkomaty' ); ?></legend>
			<label style="display: block; margin-bottom: 8px;">
				<input type="radio" name="inpost_paczkomaty_options[ip_checkout_mode_override]" value="block" <?php checked( $override, 'block' ); ?>>
				<?php esc_html_e( '✅ Używaj trybu blokowego (WooCommerce Blocks)', 'inpost-paczkomaty' ); ?>
			</label>
			<label style="display: block;">
				<input type="radio" name="inpost_paczkomaty_options[ip_checkout_mode_override]" value="classic" <?php checked( $override, 'classic' ); ?>>
				<?php esc_html_e( '🔷 Używaj trybu klasycznego (shortcode)', 'inpost-paczkomaty' ); ?>
			</label>
			<p class="description" style="margin-top: 8px;">
				<?php esc_html_e( 'To ustawienie jest aktywne tylko gdy wykryto konflikt. Aby usunąć potrzebę ręcznego wyboru, ustaw obie strony (koszyk i checkout) w tym samym trybie.', 'inpost-paczkomaty' ); ?>
			</p>
		</fieldset>
		<?php
	}
}

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
	wp_enqueue_script( 'save-checkout-script', plugin_dir_url( __FILE__ ) . 'js/save-checkout.js', array( 'jquery' ), '1.0.41', true );
	wp_localize_script( 'save-checkout-script', 'custom_ajax_object', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'inpost_paczkomaty_restore_checkout' ),
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
	echo '<input type="hidden" id="' . esc_attr( $args['label_for'] ) . '" name="inpost_paczkomaty_options[' . esc_attr( $args['label_for'] ) . ']" value="' . esc_attr( $val ) . '">';

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
		<?php inpost_paczkomaty_render_support_card(); ?>
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

/**
 * Add "Settings" and "Support" links to the plugin row on the Plugins screen.
 * The Settings link is the main entry point users look for after activation.
 *
 * @param array $links Existing action links.
 *
 * @return array
 */
function inpost_paczkomaty_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=inpost_paczkomaty_settings' ) ),
		esc_html__( 'Ustawienia', 'inpost-paczkomaty' )
	);

	$donate_link = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer" style="color:#b8860b;font-weight:600;">☕ %s</a>',
		esc_url( 'https://suppi.pl/damian-ziarnik' ),
		esc_html__( 'Wesprzyj rozwój wtyczki', 'inpost-paczkomaty' )
	);

	// Settings first so it sits next to Deactivate.
	array_unshift( $links, $settings_link );
	$links[] = $donate_link;

	return $links;
}

add_filter( 'plugin_action_links_' . INPOST_PACZKOMATY_PLUGIN_BASENAME, 'inpost_paczkomaty_plugin_action_links' );

/**
 * Add review and paid-support links to the plugin meta row (under the description).
 *
 * @param array  $meta        Existing meta links.
 * @param string $plugin_file Plugin file the row belongs to.
 *
 * @return array
 */
function inpost_paczkomaty_plugin_row_meta( $meta, $plugin_file ) {
	if ( INPOST_PACZKOMATY_PLUGIN_BASENAME !== $plugin_file ) {
		return $meta;
	}

	$meta[] = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">⭐ %s</a>',
		esc_url( 'https://wordpress.org/support/plugin/inpost-paczkomaty/reviews/' ),
		esc_html__( 'Oceń wtyczkę', 'inpost-paczkomaty' )
	);

	$meta[] = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">🛠️ %s</a>',
		esc_url( 'https://grainsoft.pl/#kontakt' ),
		esc_html__( 'Płatne wsparcie', 'inpost-paczkomaty' )
	);

	return $meta;
}

add_filter( 'plugin_row_meta', 'inpost_paczkomaty_plugin_row_meta', 10, 2 );

/**
 * Render the support / review card shown on the plugin settings page.
 * Part of the page content rather than an admin notice, so it never
 * interrupts other screens and does not need dismissing.
 */
function inpost_paczkomaty_render_support_card() {
	?>
    <div class="inpost-support-card">
        <div class="inpost-support-card__text">
            <h2>❤️ <?php esc_html_e( 'Dziękuję za korzystanie z mojej wtyczki', 'inpost-paczkomaty' ); ?></h2>
            <p>
				<?php esc_html_e( 'Wtyczka jest darmowa i rozwijana w moim wolnym czasie.', 'inpost-paczkomaty' ); ?>
				<?php esc_html_e( 'Potrzebujesz pomocy z konfiguracją? Oferuję płatne wsparcie.', 'inpost-paczkomaty' ); ?>
            </p>
        </div>
        <div class="inpost-support-card__actions">
            <a class="button button-primary" href="https://wordpress.org/support/plugin/inpost-paczkomaty/reviews/"
               target="_blank" rel="noopener noreferrer">
                ⭐ <?php esc_html_e( 'Oceń wtyczkę', 'inpost-paczkomaty' ); ?>
            </a>
            <a class="button" href="https://suppi.pl/damian-ziarnik" target="_blank" rel="noopener noreferrer">
                ☕ <?php esc_html_e( 'Wesprzyj rozwój wtyczki', 'inpost-paczkomaty' ); ?>
            </a>
            <a class="button" href="https://grainsoft.pl/#kontakt" target="_blank" rel="noopener noreferrer">
                🛠️ <?php esc_html_e( 'Płatne wsparcie', 'inpost-paczkomaty' ); ?>
            </a>
        </div>
    </div>
    <style>
        .inpost-support-card {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin: 20px 0;
            padding: 18px 22px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-left: 4px solid #7f54b3;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        }

        .inpost-support-card__text {
            flex: 1 1 260px;
        }

        .inpost-support-card__text h2 {
            margin: 0 0 4px;
            font-size: 15px;
        }

        .inpost-support-card__text p {
            margin: 0;
            color: #50575e;
        }

        .inpost-support-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
    </style>
	<?php
}


function save_shortcode_cart_checkout_ajax_handler() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Brak uprawnień.', 'inpost-paczkomaty' ) ), 403 );
	}

	check_ajax_referer( 'inpost_paczkomaty_restore_checkout', 'nonce' );

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
		wp_send_json_success( array( 'message' => __( 'Sukces! Sprawdź teraz swój koszyk oraz checkout.', 'inpost-paczkomaty' ) ) );
	}
	wp_die();
}

add_action( 'wp_ajax_save_shortcode_cart_checkout_ajax', 'save_shortcode_cart_checkout_ajax_handler' );

?>
