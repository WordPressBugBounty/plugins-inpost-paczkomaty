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
	private $version = '1.0.43';

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
		// Registers the GeoWidget SDK (v4 or v5), the shared map script and the block script.
		inpost_paczkomaty_register_block_scripts();

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

