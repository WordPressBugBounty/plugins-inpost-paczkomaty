<?php
/**
 * WooCommerce Blocks checkout integration for InPost Paczkomaty.
 *
 * Loaded only when INPOST_PACZKOMATY_CLASSIC_CHECKOUT is false (block checkout).
 * Registers the JS integration via IntegrationInterface and handles
 * order validation + meta saving through the Store API hook.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ---------------------------------------------------------------------------
// Helper: rejestruje skrypty (bez enqueuowania) – używany w obu ścieżkach.
// ---------------------------------------------------------------------------

function inpost_paczkomaty_register_block_scripts() {
	if ( wp_script_is( 'inpost-paczkomaty-blocks', 'registered' ) ) {
		return; // already registered – avoid duplicates
	}

	wp_register_script(
		'inpost-geowidget-sdk',
		'https://geowidget.easypack24.net/js/sdk-for-javascript.js',
		[],
		null,
		true
	);

	wp_register_script(
		'inpost-paczkomaty-blocks',
		INPOST_PACZKOMATY_PLUGIN_URL . '/js/paczkomat-blocks.js',
		[ 'wp-element', 'wp-data', 'wp-plugins', 'wc-blocks-checkout', 'jquery', 'inpost-geowidget-sdk' ],
		'1.0.42',
		true
	);

	wp_localize_script(
		'inpost-paczkomaty-blocks',
		'inpostBlocksData',
		[ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ]
	);

	wp_register_style(
		'inpost-geowidget-css',
		'https://geowidget.easypack24.net/css/easypack.css',
		[],
		null
	);
}

// ---------------------------------------------------------------------------
// Główna ścieżka: IntegrationInterface (WC Blocks 9.6+ i starsze warianty).
// ---------------------------------------------------------------------------

$_ip_blocks_cb = function ( $integration_registry ) {
	inpost_paczkomaty_register_block_scripts();

	if ( ! class_exists( 'Inpost_Paczkomaty_Blocks_Integration' ) ) {
		require_once INPOST_PACZKOMATY_PLUGIN_PATH . 'includes/class-inpost-blocks-integration.php';
	}

	$integration_registry->register( new Inpost_Paczkomaty_Blocks_Integration() );
};

// Aktualna nazwa hooka (WC Blocks 9.6+)
add_action( 'woocommerce_blocks_checkout_block_registration', $_ip_blocks_cb );
add_action( 'woocommerce_blocks_cart_block_registration',     $_ip_blocks_cb );
// Stara nazwa z prefiksem experimental (WC Blocks < 9.6)
add_action( '__experimental_woocommerce_blocks_checkout_block_registration', $_ip_blocks_cb );
add_action( '__experimental_woocommerce_blocks_cart_block_registration',     $_ip_blocks_cb );
unset( $_ip_blocks_cb );

// ---------------------------------------------------------------------------
// Fallback: bezpośrednie enqueue przez wp_enqueue_scripts na stronach cart/checkout.
// Gwarantuje załadowanie skryptu nawet gdy IntegrationInterface nie zadziała.
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_checkout() && ! is_cart() ) {
		return;
	}

	inpost_paczkomaty_register_block_scripts();

	wp_enqueue_script( 'inpost-paczkomaty-blocks' );
	wp_enqueue_style( 'inpost-geowidget-css' );
} );

// ---------------------------------------------------------------------------
// Validate paczkomat selection and save order meta via Store API.
// Fires before payment processing; throw RouteException to abort the order.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_store_api_checkout_order_processed', 'inpost_paczkomaty_blocks_checkout_order_processed' );

function inpost_paczkomaty_blocks_checkout_order_processed( $order ) {
	$chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );

	// Only act when InPost Paczkomaty is the selected shipping method.
	if ( ! isset( $chosen_shipping[0] ) ) {
		return;
	}

	$method_parts = explode( ':', $chosen_shipping[0] );

	if ( $method_parts[0] !== 'inpost_paczkomaty' ) {
		return;
	}

	$selected_name = WC()->session->get( 'paczkomat_name' );

	// Block order placement when no paczkomat has been selected.
	if ( empty( $selected_name ) ) {
		$error_message = __( 'Nie wybrano paczkomatu. Wybierz paczkomat lub zmień formę wysyłki.', 'inpost-paczkomaty' );

		if ( class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
				'inpost_no_paczkomat_selected',
				$error_message,
				400
			);
		}

		// Fallback for older WooCommerce versions that do not ship RouteException.
		wc_add_notice( $error_message, 'error' );

		return;
	}

	// Retrieve all paczkomat address data from the session.
	$selected_address1  = WC()->session->get( 'paczkomat_address1' );
	$selected_address2  = WC()->session->get( 'paczkomat_address2' );
	$selected_post_code = WC()->session->get( 'paczkomat_post_code' );
	$selected_city      = WC()->session->get( 'paczkomat_city' );

	$settings                = get_option( 'inpost_paczkomaty_options' );
	$ip_selected_as_shipping = isset( $settings['ip_selected_as_shipping'] ) && $settings['ip_selected_as_shipping'] === 'yes';

	$val = $selected_name . ', ' . $selected_address1 . ', ' . $selected_address2;

	// Delegate to shared save helper.
	inpost_paczkomaty_save_paczkomat_to_order( $order, $selected_name, $selected_address1, $selected_address2, $selected_post_code, $selected_city, $ip_selected_as_shipping );
}

// ---------------------------------------------------------------------------
// Fallback hook: woocommerce_checkout_order_created fires for block checkout
// in WooCommerce 5.0+ even when the Store API hook doesn't fire.
// Only runs in REST context to avoid double-saving with classic checkout mode.
// ---------------------------------------------------------------------------

add_action( 'woocommerce_checkout_order_created', function ( $order ) {
	if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	// Prevent double-save if primary hook already ran.
	if ( $order->get_meta( '_inpost_paczkomat_saved' ) ) {
		return;
	}

	// Confirm InPost is the shipping method from the order items.
	$is_inpost = false;
	foreach ( $order->get_items( 'shipping' ) as $shipping_item ) {
		if ( $shipping_item->get_method_id() === 'inpost_paczkomaty' ) {
			$is_inpost = true;
			break;
		}
	}

	if ( ! $is_inpost || ! WC()->session ) {
		return;
	}

	$selected_name      = WC()->session->get( 'paczkomat_name' );
	$selected_address1  = WC()->session->get( 'paczkomat_address1' );
	$selected_address2  = WC()->session->get( 'paczkomat_address2' );
	$selected_post_code = WC()->session->get( 'paczkomat_post_code' );
	$selected_city      = WC()->session->get( 'paczkomat_city' );

	$settings                = get_option( 'inpost_paczkomaty_options' );
	$ip_selected_as_shipping = isset( $settings['ip_selected_as_shipping'] ) && $settings['ip_selected_as_shipping'] === 'yes';

	inpost_paczkomaty_save_paczkomat_to_order( $order, $selected_name, $selected_address1, $selected_address2, $selected_post_code, $selected_city, $ip_selected_as_shipping );
} );

// ---------------------------------------------------------------------------
// Helper: saves paczkomat data as order meta.
// Uses update_meta_data (creates or updates) – works correctly with both
// classic post-meta and HPOS (High-Performance Order Storage).
// ---------------------------------------------------------------------------

function inpost_paczkomaty_save_paczkomat_to_order( $order, $name, $address1, $address2, $post_code, $city, $as_shipping = false ) {
	if ( empty( $name ) ) {
		return;
	}

	$val = $name . ', ' . $address1 . ', ' . $address2;

	$order->update_meta_data( 'Wybrany paczkomat', $val );
	$order->update_meta_data( '_paczkomat_id', $name );
	$order->update_meta_data( 'paczkomat_key', $name );
	$order->update_meta_data( 'delivery_point_name', $name );
	$order->update_meta_data( 'delivery_point_city', $city );
	$order->update_meta_data( 'delivery_point_postcode', $post_code );
	$order->update_meta_data( 'delivery_point_address', $address1 );

	if ( $as_shipping ) {
		$order->update_meta_data( '_shipping_address_1', $address1 );
		$order->update_meta_data( '_shipping_address_2', $name );
		$order->update_meta_data( '_shipping_city', $city );
		$order->update_meta_data( '_shipping_postcode', $post_code );
	}

	// Internal flag – prevents double-save across hooks.
	$order->update_meta_data( '_inpost_paczkomat_saved', '1' );
	$order->save();
}

