<?php
/**
 * WooCommerce Blocks Integration for InPost Paczkomaty.
 * Registers scripts for the block-based cart and checkout.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

class Inpost_Paczkomaty_Blocks_Integration implements IntegrationInterface {

	/**
	 * Plugin version used for script versioning.
	 *
	 * @var string
	 */
	private $version = '1.0.41';

	/**
	 * Returns the integration's unique name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'inpost_paczkomaty';
	}

	/**
	 * Initializes the integration - registers scripts and styles.
	 *
	 * @return void
	 */
	public function initialize() {
		// Register InPost GeoWidget SDK (loaded in footer to avoid render-blocking)
		wp_register_script(
			'inpost-geowidget-sdk',
			'https://geowidget.easypack24.net/js/sdk-for-javascript.js',
			[],
			null,
			true
		);

		// Register block integration script
		wp_register_script(
			'inpost-paczkomaty-blocks',
			INPOST_PACZKOMATY_PLUGIN_URL . '/js/paczkomat-blocks.js',
			[
				'wp-element',
				'wp-data',
				'wp-plugins',
				'wc-blocks-checkout',
				'jquery',
				'inpost-geowidget-sdk',
			],
			$this->version,
			true
		);

		// Pass AJAX URL and nonce to the block script
		wp_localize_script(
			'inpost-paczkomaty-blocks',
			'inpostBlocksData',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'inpost_paczkomaty_nonce' ),
			]
		);

		// Register and enqueue InPost GeoWidget styles
		wp_register_style(
			'inpost-geowidget-css',
			'https://geowidget.easypack24.net/css/easypack.css',
			[],
			null
		);
		wp_enqueue_style( 'inpost-geowidget-css' );
	}

	/**
	 * Returns script handles to enqueue in the frontend (cart/checkout) context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return [ 'inpost-paczkomaty-blocks' ];
	}

	/**
	 * Returns script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return [];
	}

	/**
	 * Returns key-value pairs passed to the integration script as window data.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		];
	}
}

