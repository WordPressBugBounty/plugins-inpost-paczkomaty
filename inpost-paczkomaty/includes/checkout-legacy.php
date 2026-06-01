<?php
/**
 * Legacy checkout integration for InPost Paczkomaty.
 *
 * Loaded only when INPOST_PACZKOMATY_LEGACY_MODE is true (classic shortcode cart/checkout).
 * All functions use INPOST_PACZKOMATY_PLUGIN_URL instead of plugins_url( ..., __FILE__ )
 * so relative paths remain correct regardless of where this file lives.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ---------------------------------------------------------------------------
// Enqueue InPost GeoWidget SDK and modal script on the legacy cart page.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_before_cart', 'inpost_paczkomaty_styles_and_scripts_before_cart' );

function inpost_paczkomaty_styles_and_scripts_before_cart() {
	wp_enqueue_script( 'inpost_js', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js' );

	// Detect current protocol to build the correct admin-ajax URL.
	$protocol       = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
	$admin_ajax_url = admin_url( 'admin-ajax.php', $protocol );

	$modal_js_url = INPOST_PACZKOMATY_PLUGIN_URL . '/js/paczkomat-modal.js';

	wp_register_script( 'paczkomat_modal', $modal_js_url, array( 'jquery' ) );
	wp_localize_script( 'paczkomat_modal', 'ajax_options', array( 'admin_ajax_url' => $admin_ajax_url ) );
	wp_enqueue_script( 'paczkomat_modal', $modal_js_url, array( 'jquery' ) );

	wp_enqueue_style( 'inpost_paczkomaty_inpost_css', 'https://geowidget.easypack24.net/css/easypack.css' );
}

// ---------------------------------------------------------------------------
// Enqueue InPost GeoWidget SDK and modal script on the legacy checkout page.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_before_checkout_form', 'inpost_paczkomaty_styles_and_scripts_before_checkout' );

function inpost_paczkomaty_styles_and_scripts_before_checkout() {
	wp_enqueue_script( 'inpost_js', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js' );

	$protocol       = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
	$admin_ajax_url = admin_url( 'admin-ajax.php', $protocol );

	$modal_js_url = INPOST_PACZKOMATY_PLUGIN_URL . '/js/paczkomat-modal.js';

	wp_register_script( 'paczkomat_modal', $modal_js_url, array( 'jquery' ) );
	wp_localize_script( 'paczkomat_modal', 'ajax_options', array( 'admin_ajax_url' => $admin_ajax_url ) );
	wp_enqueue_script( 'paczkomat_modal', $modal_js_url, array( 'jquery' ) );

	wp_enqueue_style( 'inpost_paczkomaty_inpost_css', 'https://geowidget.easypack24.net/css/easypack.css' );
}

// ---------------------------------------------------------------------------
// Validate paczkomat selection before the legacy checkout form is processed.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_checkout_process', 'paczkomaty_inpost_validation_checkout' );

function paczkomaty_inpost_validation_checkout() {
	$selected_shipping = WC()->session->get( 'chosen_shipping_methods' );

	if ( isset( $selected_shipping ) && ! empty( $selected_shipping ) ) {
		$selected      = explode( ':', $selected_shipping[0] );
		$selected_name = WC()->session->get( 'paczkomat_name' );

		if ( $selected[0] === 'inpost_paczkomaty' && ( ! isset( $selected_name ) || empty( $selected_name ) ) ) {
			wc_add_notice( __( 'Nie wybrano paczkomatu. Wybierz paczkomat lub zmień formę wysyłki.' ), 'error' );
		}
	}
}

// ---------------------------------------------------------------------------
// Render logo and paczkomat selector button after the inpost shipping rate row.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_after_shipping_rate', 'inpost_paczkomaty_action_woocommerce_checkout_before_order_review', 10, 2 );

// Fire custom action (kept for backward compatibility with any external hooks).
do_action( 'inpost_paczkomaty_woocommerce_checkout_order_review' );

function inpost_paczkomaty_action_woocommerce_checkout_before_order_review( $shipping ) {
	$settings = get_option( 'inpost_paczkomaty_options' );

	// Optionally show the InPost logo next to the shipping rate.
	if ( isset( $settings['ip_select_show_logo'] ) ) {
		$settings_show_logo = ( $settings['ip_select_show_logo'] === 'yes' ) ? 'yes' : 'no';

		if ( $settings_show_logo === 'yes'
		     && isset( $settings['ip_select_show_logo_img'] )
		     && ! empty( $settings['ip_select_show_logo_img'] )
		     && $shipping->method_id === 'inpost_paczkomaty'
		) {
			$allowed_html_img = array(
				'div' => array(),
				'img' => array(
					'src'   => array(),
					'class' => array(),
					'width' => array(),
				),
			);
			echo wp_kses(
				'<div><img width="100px" src="' . $settings['ip_select_show_logo_img'] . '" class="paczkomat-logo"></div>',
				$allowed_html_img
			);
		}
	}

	// Show the paczkomat picker only when inpost is the active shipping method.
	$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
	$chosen_parts            = explode( ':', $chosen_shipping_methods[0] );

	if ( $chosen_parts[0] !== 'inpost_paczkomaty' || $shipping->method_id !== 'inpost_paczkomaty' ) {
		return;
	}

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'paczkomat_modal' );

	$selected_name     = WC()->session->get( 'paczkomat_name' );
	$selected_address1 = WC()->session->get( 'paczkomat_address1' );
	$selected_address2 = WC()->session->get( 'paczkomat_address2' );

	$allowed_html_button = array(
		'div'    => array(),
		'button' => array(
			'type'  => array(),
			'class' => array(),
		),
	);

	if ( empty( $selected_name ) ) {
		echo wp_kses(
			'<div><button type="button" class="btn button select-paczkomat-button">Wybierz paczkomat</button></div>',
			$allowed_html_button
		);
		echo wp_kses( '<div id="selected-paczkomat"></div>', array( 'div' => array( 'id' => array() ) ) );
	} else {
		echo wp_kses(
			'<div><button type="button" class="btn button select-paczkomat-button">Zmień paczkomat</button></div>',
			$allowed_html_button
		);

		$allowed_html_info = array(
			'div' => array( 'id' => array() ),
			'br'  => array(),
		);
		echo wp_kses(
			'<div id="selected-paczkomat"> Wybrany paczkomat: <br>' . $selected_name . '<br>' . $selected_address1 . '<br>' . $selected_address2 . '</div>',
			$allowed_html_info
		);
	}
}

// ---------------------------------------------------------------------------
// Save selected paczkomat data as order meta on legacy checkout submission.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_checkout_update_order_meta', 'inpost_paczkomaty_checkout_field_update_order_meta' );

function inpost_paczkomaty_checkout_field_update_order_meta( $order_id ) {
	$settings                = get_option( 'inpost_paczkomaty_options' );
	$ip_selected_as_shipping = isset( $settings['ip_selected_as_shipping'] ) && $settings['ip_selected_as_shipping'] === 'yes';

	$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

	if ( ! isset( $chosen_shipping_methods[0] ) ) {
		return;
	}

	$method_parts = explode( ':', $chosen_shipping_methods[0] );

	if ( $method_parts[0] !== 'inpost_paczkomaty' ) {
		return;
	}

	$selected_name            = WC()->session->get( 'paczkomat_name' );
	$selected_address1        = WC()->session->get( 'paczkomat_address1' );
	$selected_address2        = WC()->session->get( 'paczkomat_address2' );
	$selected_post_code       = WC()->session->get( 'paczkomat_post_code' );
	$selected_city            = WC()->session->get( 'paczkomat_city' );
	$selected_street          = WC()->session->get( 'paczkomat_street' );
	$selected_building_number = WC()->session->get( 'paczkomat_building_number' );
	$selected_flat_number     = WC()->session->get( 'paczkomat_flat_number' );

	$order = wc_get_order( $order_id );
	$val   = $selected_name . ', ' . $selected_address1 . ', ' . $selected_address2;

	if ( ! empty( $val ) ) {
		$order->add_meta_data( 'Wybrany paczkomat', $val, true );
	}

	if ( ! empty( $selected_name ) ) {
		$order->add_meta_data( '_paczkomat_id', $selected_name, true );
		$order->add_meta_data( 'paczkomat_key', $selected_name, true );
	}

	$order->add_meta_data( 'delivery_point_name', $selected_name, true );
	$order->add_meta_data( 'delivery_point_city', $selected_city, true );
	$order->add_meta_data( 'delivery_point_postcode', $selected_post_code, true );
	$order->add_meta_data( 'delivery_point_address', $selected_address1, true );

	if ( $ip_selected_as_shipping ) {
		$order->add_meta_data( '_shipping_address_1', $selected_address1, true );
		$order->add_meta_data( '_shipping_address_2', $selected_name, true );
		$order->add_meta_data( '_shipping_city', $selected_city, true );
		$order->add_meta_data( '_shipping_postcode', $selected_post_code, true );
	}

	$order->save();
}

// ---------------------------------------------------------------------------
// Ensure jQuery and the paczkomat modal script are queued on the cart page.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_before_cart', 'inpost_paczkomaty_initCart' );

function inpost_paczkomaty_initCart() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'paczkomat_modal' );
}

