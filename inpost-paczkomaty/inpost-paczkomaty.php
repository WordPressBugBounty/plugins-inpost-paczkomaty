<?php
/**
 * Plugin Name: Inpost Paczkomaty
 * Description: Plugin do obsługi paczkomatów inpost w woocommerce.
 * Version: 1.0.35
 * Author: Damian Ziarnik
 * Author URI: https://grainsoft.pl/
 * Text Domain: inpost-paczkomaty
 * Domain Path: /languages
 **/

use Automattic\WooCommerce\Utilities\NumberUtil;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * Bootstrap only when WooCommerce is active.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// -------------------------------------------------------------------------
	// Plugin-wide constants (available in all included files).
	// -------------------------------------------------------------------------

	define( 'INPOST_PACZKOMATY_PLUGIN_URL', plugins_url( '', __FILE__ ) );
	define( 'INPOST_PACZKOMATY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	// Resolve checkout mode. Default: block mode for new installations (option not yet saved).
	// Sites that previously saved 'yes' explicitly continue to use legacy mode.
	$_ip_options    = get_option( 'inpost_paczkomaty_options' );
	$_ip_legacy_raw = isset( $_ip_options['ip_use_legacy_checkout'] ) ? $_ip_options['ip_use_legacy_checkout'] : 'no';
	define( 'INPOST_PACZKOMATY_LEGACY_MODE', ( $_ip_legacy_raw !== 'no' ) );
	unset( $_ip_options, $_ip_legacy_raw );

	// -------------------------------------------------------------------------
	// Shipping method class – shared between both checkout modes.
	// -------------------------------------------------------------------------

	add_action( 'woocommerce_shipping_init', 'inpost_paczkomaty_shipping_method' );

	function inpost_paczkomaty_shipping_method() {
		if ( ! class_exists( 'inpost_paczkomaty_shipping_method' ) ) {
			class inpost_paczkomaty_shipping_method extends WC_Shipping_Method {

				/** @var string */
				public $requires = '';

				/** @var bool */
				public $ignore_discounts = false;

				/** @var float|string */
				public $cost = '';

				/** @var float|string */
				protected string $fee_cost = '';

				/** @var string */
				public $type = '';

				/** @var int */
				public $min_amount = 0;

				/** @var int */
				public $max_amount = 0;

				/** @var string */
				public $informations = '';

				/**
				 * Constructor.
				 *
				 * @param int $instance_id Shipping zone instance ID.
				 */
				public function __construct( $instance_id = 0 ) {
					$this->id                 = 'inpost_paczkomaty';
					$this->instance_id        = absint( $instance_id );
					$this->method_title       = __( 'Inpost Paczkomaty', 'inpost-paczkomaty' );
					$this->method_description = __( 'Paczkomaty inpost shipping method', 'inpost-paczkomaty' );
					$this->supports           = array(
						'shipping-zones',
						'instance-settings',
						'instance-settings-modal',
					);
					$this->init();
					$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
				}

				/**
				 * Initialize settings.
				 */
				public function init() {
					$this->init_form_fields();
					$this->init_settings();

					// Load instance-level form fields from the dedicated settings file.
					$this->instance_form_fields = include INPOST_PACZKOMATY_PLUGIN_PATH . 'includes/settings-inpost-paczkomaty.php';

					$this->title            = $this->get_option( 'title' );
					$this->tax_status       = $this->get_option( 'tax_status' );
					$this->requires         = $this->get_option( 'requires' );
					$this->cost             = $this->get_option( 'cost' );
					$this->min_amount       = $this->get_option( 'min_amount', 0 );
					$this->max_amount       = $this->get_option( 'max_amount', 0 );
					$this->ignore_discounts = $this->get_option( 'ignore_discounts' );
					$this->type             = $this->get_option( 'type', 'class' );

					add_action(
						'woocommerce_update_options_shipping_' . $this->id,
						array( $this, 'process_admin_options' )
					);

					// Priority must be higher than wc_print_js (25).
					add_action( 'admin_footer', array( 'inpost_paczkomaty_shipping_method', 'enqueue_admin_js' ), 10 );
				}

				/**
				 * Determine if this shipping method is available for the given package.
				 *
				 * @param array $package Cart shipping package.
				 *
				 * @return bool
				 */
				public function is_available( $package ) {
					$has_met_min_amount         = false;
					$has_met_max_amount         = false;
					$has_met_min_and_max_amount = false;

					$ip_settings = get_option( 'inpost_paczkomaty_options' );

					if ( in_array( $this->requires, array( 'min_amount' ), true ) ) {
						$total = WC()->cart->get_displayed_subtotal();
						$total = NumberUtil::round( $total, wc_get_price_decimals() );
						if ( 'no' === $this->ignore_discounts ) {
							$total -= WC()->cart->get_discount_total();
						}
						$has_met_min_amount = ( $total >= $this->min_amount );
					}

					if ( in_array( $this->requires, array( 'max_amount' ), true ) ) {
						$total = WC()->cart->get_displayed_subtotal();
						$total = NumberUtil::round( $total, wc_get_price_decimals() );
						if ( 'no' === $this->ignore_discounts ) {
							$total -= WC()->cart->get_discount_total();
						}
						$has_met_max_amount = ( $total <= $this->max_amount );
					}

					if ( in_array( $this->requires, array( 'min_and_max_amount' ), true ) ) {
						$total = WC()->cart->get_displayed_subtotal();
						$total = NumberUtil::round( $total, wc_get_price_decimals() );
						if ( 'no' === $this->ignore_discounts ) {
							$total -= WC()->cart->get_discount_total();
						}
						$has_met_min_and_max_amount = ( $total >= $this->min_amount && $total <= $this->max_amount );
					}

					switch ( $this->requires ) {
						case 'min_amount':
							$is_available = $has_met_min_amount;
							break;
						case 'max_amount':
							$is_available = $has_met_max_amount;
							break;
						case 'min_and_max_amount':
							$is_available = $has_met_min_and_max_amount;
							break;
						default:
							$is_available = true;
							break;
					}

					// Weight limit check.
					if ( isset( $ip_settings['ip_select_weight_limit'] ) && $ip_settings['ip_select_weight_limit'] === 'yes' ) {
						if ( ! empty( $ip_settings['ip_select_weight_limit_value'] ) && $ip_settings['ip_select_weight_limit_value'] > 0 ) {
							$total_weight = WC()->cart->cart_contents_weight;
							if ( isset( $ip_settings['ip_select_weight_limit_result'] ) && $ip_settings['ip_select_weight_limit_result'] === 'hide' ) {
								if ( $total_weight > $ip_settings['ip_select_weight_limit_value'] ) {
									$is_available = false;
								}
							}
						}
					}

					// Dimensions limit check.
					if ( isset( $ip_settings['ip_select_dimensions_limit'] ) && $ip_settings['ip_select_dimensions_limit'] === 'yes' ) {
						foreach ( WC()->cart->get_cart() as $cart_item ) {
							$product = $cart_item['data'];

							if ( ! empty( $ip_settings['ip_select_dimensions_limit_width'] ) ) {
								if ( $product->get_width() > $ip_settings['ip_select_dimensions_limit_width'] ) {
									$is_available = false;
									break;
								}
							}

							if ( ! empty( $ip_settings['ip_select_dimensions_limit_height'] ) ) {
								if ( $product->get_height() > $ip_settings['ip_select_dimensions_limit_height'] ) {
									$is_available = false;
									break;
								}
							}

							if ( ! empty( $ip_settings['ip_select_dimensions_limit_length'] ) ) {
								if ( $product->get_length() > $ip_settings['ip_select_dimensions_limit_length'] ) {
									$is_available = false;
									break;
								}
							}
						}
					}

					return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
				}

				/**
				 * Enqueue admin JS for the shipping method settings modal.
				 * Declared static so it is enqueued only once.
				 */
				public static function enqueue_admin_js() {
					wc_enqueue_js(
						"jQuery( function( $ ) {

				function wcInpostPaczkomatyShowHideMinAmountField( el ) {
					var form = $( el ).closest( 'form' );
					var minAmountField = $( '#woocommerce_inpost_paczkomaty_min_amount', form ).closest( 'tr' );
					var ignoreDiscountField = $( '#woocommerce_free_shipping_ignore_discounts', form ).closest( 'tr' );
					if ( 'min_amount' === $( el ).val() ) {
						minAmountField.show();
						ignoreDiscountField.show();
					} else {
						minAmountField.hide();
						ignoreDiscountField.hide();
					}
				}

				function wcInpostPaczkomatyShowHideMaxAmountField( el ) {
					var form = $( el ).closest( 'form' );
					var maxAmountField = $( '#woocommerce_inpost_paczkomaty_max_amount', form ).closest( 'tr' );
					var ignoreDiscountField = $( '#woocommerce_free_shipping_ignore_discounts', form ).closest( 'tr' );
					if ( 'max_amount' === $( el ).val() ) {
						maxAmountField.show();
						ignoreDiscountField.show();
					} else {
						maxAmountField.hide();
						ignoreDiscountField.hide();
						ignoreDiscountField.show();
					}
				}

				function wcInpostPaczkomatyShowHideMinMaxAmountField( el ) {
					var form = $( el ).closest( 'form' );
					var maxAmountField = $( '#woocommerce_inpost_paczkomaty_max_amount', form ).closest( 'tr' );
					var minAmountField = $( '#woocommerce_inpost_paczkomaty_min_amount', form ).closest( 'tr' );
					if ( 'min_and_max_amount' === $( el ).val() ) {
						minAmountField.show();
						maxAmountField.show();
					}
				}

				$( document.body ).on( 'change', '#woocommerce_inpost_paczkomaty_requires', function() {
					wcInpostPaczkomatyShowHideMinAmountField( this );
					wcInpostPaczkomatyShowHideMaxAmountField( this );
					wcInpostPaczkomatyShowHideMinMaxAmountField( this );
				});

				$( '#woocommerce_inpost_paczkomaty_requires' ).trigger( 'change' );

				$( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
					if ( 'wc-modal-shipping-method-settings' === target ) {
						wcInpostPaczkomatyShowHideMinAmountField( $( '#wc-backbone-modal-dialog #woocommerce_inpost_paczkomaty_requires', evt.currentTarget ) );
						wcInpostPaczkomatyShowHideMaxAmountField( $( '#wc-backbone-modal-dialog #woocommerce_inpost_paczkomaty_requires', evt.currentTarget ) );
						wcInpostPaczkomatyShowHideMinMaxAmountField( $( '#wc-backbone-modal-dialog #woocommerce_inpost_paczkomaty_requires', evt.currentTarget ) );
					}
				});
			});"
					);
				}

				/**
				 * Evaluate a cost expression string.
				 *
				 * @param string $sum  Expression to evaluate.
				 * @param array  $args Must contain 'cost' and 'qty' keys.
				 *
				 * @return string|float
				 */
				protected function evaluate_cost( $sum, $args = array() ) {
					if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
						wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
					}

					include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

					$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
					$locale         = localeconv();
					$decimals       = array(
						wc_get_price_decimal_separator(),
						$locale['decimal_point'],
						$locale['mon_decimal_point'],
						',',
					);
					$this->fee_cost = $args['cost'];

					add_shortcode( 'fee', array( $this, 'fee' ) );

					$sum = do_shortcode(
						str_replace(
							array( '[qty]', '[cost]' ),
							array( $args['qty'], $args['cost'] ),
							$sum
						)
					);

					remove_shortcode( 'fee', array( $this, 'fee' ) );

					$sum = preg_replace( '/\s+/', '', $sum );
					$sum = str_replace( $decimals, '.', $sum );
					$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

					return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
				}

				/**
				 * Handle the [fee] shortcode inside cost expressions.
				 *
				 * @param array $atts Shortcode attributes.
				 *
				 * @return float
				 */
				public function fee( $atts ) {
					$atts = shortcode_atts(
						array(
							'percent' => '',
							'min_fee' => '',
							'max_fee' => '',
						),
						$atts,
						'fee'
					);

					$calculated_fee = 0;

					if ( $atts['percent'] ) {
						$calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
					}

					if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
						$calculated_fee = $atts['min_fee'];
					}

					if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
						$calculated_fee = $atts['max_fee'];
					}

					return $calculated_fee;
				}

				/**
				 * Calculate shipping cost for the given package.
				 *
				 * @param array $package Cart package.
				 */
				public function calculate_shipping( $package = array() ) {
					$rate = array(
						'id'      => $this->get_rate_id(),
						'label'   => $this->title,
						'cost'    => 0,
						'package' => $package,
					);

					$has_costs = true;
					$cost      = $this->get_option( 'cost' );
					$qty       = $this->get_package_item_qty( $package );

					// Apply weight-based cost splitting when configured.
					$ip_settings = get_option( 'inpost_paczkomaty_options' );

					if ( isset( $ip_settings['ip_select_weight_limit'] ) && $ip_settings['ip_select_weight_limit'] === 'yes' ) {
						if ( ! empty( $ip_settings['ip_select_weight_limit_value'] ) ) {
							$weight_limit_value = $ip_settings['ip_select_weight_limit_value'];
							$total_weight       = WC()->cart->cart_contents_weight;

							if ( isset( $ip_settings['ip_select_weight_limit_result'] ) && $ip_settings['ip_select_weight_limit_result'] === 'split' ) {
								if ( $total_weight > $weight_limit_value ) {
									$quantity = ceil( $total_weight / $weight_limit_value );
									$qty      = $quantity;
									$cost     = $cost * $quantity;
								}
							}
						}
					}

					if ( '' !== $cost ) {
						$has_costs    = true;
						$rate['cost'] = $this->evaluate_cost(
							$cost,
							array(
								'qty'  => $qty,
								'cost' => $package['contents_cost'],
							)
						);
					}

					// Add per-shipping-class costs.
					$shipping_classes = WC()->shipping()->get_shipping_classes();

					if ( ! empty( $shipping_classes ) ) {
						$found_shipping_classes = $this->find_shipping_classes( $package );
						$highest_class_cost     = 0;

						foreach ( $found_shipping_classes as $shipping_class => $products ) {
							$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
							$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id
								? $this->get_option( 'class_cost_' . $shipping_class_term->term_id, $this->get_option( 'class_cost_' . $shipping_class, '' ) )
								: $this->get_option( 'no_class_cost', '' );

							if ( '' === $class_cost_string ) {
								continue;
							}

							$has_costs  = true;
							$class_cost = $this->evaluate_cost(
								$class_cost_string,
								array(
									'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
									'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
								)
							);

							if ( 'class' === $this->type ) {
								$rate['cost'] += $class_cost;
							} else {
								$highest_class_cost = max( $class_cost, $highest_class_cost );
							}
						}

						if ( 'order' === $this->type && $highest_class_cost ) {
							$rate['cost'] += $highest_class_cost;
						}
					}

					if ( $has_costs ) {
						$this->add_rate( $rate );
					}

					do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
				}

				/**
				 * Count shippable items in the package.
				 *
				 * @param array $package Cart package.
				 *
				 * @return int
				 */
				public function get_package_item_qty( $package ) {
					$total_quantity = 0;

					foreach ( $package['contents'] as $values ) {
						if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
							$total_quantity += $values['quantity'];
						}
					}

					return $total_quantity;
				}

				/**
				 * Group package contents by shipping class.
				 *
				 * @param array $package Cart package.
				 *
				 * @return array
				 */
				public function find_shipping_classes( $package ) {
					$found_shipping_classes = array();

					foreach ( $package['contents'] as $item_id => $values ) {
						if ( $values['data']->needs_shipping() ) {
							$found_class = $values['data']->get_shipping_class();

							if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
								$found_shipping_classes[ $found_class ] = array();
							}

							$found_shipping_classes[ $found_class ][ $item_id ] = $values;
						}
					}

					return $found_shipping_classes;
				}

				/**
				 * Sanitize a cost field value entered in the settings form.
				 *
				 * @param string $value Raw value.
				 *
				 * @return string
				 * @throws Exception When the expression cannot be evaluated.
				 */
				public function sanitize_cost( $value ) {
					$value = is_null( $value ) ? '' : $value;
					$value = wp_kses_post( trim( wp_unslash( $value ) ) );
					$value = str_replace(
						array( get_woocommerce_currency_symbol(), html_entity_decode( get_woocommerce_currency_symbol() ) ),
						'',
						$value
					);

					$dummy_cost = $this->evaluate_cost( $value, array( 'cost' => 1, 'qty' => 1 ) );

					if ( false === $dummy_cost ) {
						throw new Exception( WC_Eval_Math::$last_error );
					}

					return $value;
				}
			}
		}
	}

	// -------------------------------------------------------------------------
	// Register the shipping method with WooCommerce.
	// -------------------------------------------------------------------------

	add_filter( 'woocommerce_shipping_methods', 'inpost_paczkomaty_add_inpost_shipping_method' );

	function inpost_paczkomaty_add_inpost_shipping_method( $methods ) {
		$methods['inpost_paczkomaty'] = 'inpost_paczkomaty_shipping_method';

		return $methods;
	}

	// -------------------------------------------------------------------------
	// AJAX handler – saves the selected paczkomat to the WooCommerce session.
	// Shared by both legacy and block checkout modes.
	// -------------------------------------------------------------------------

	add_action( 'wp_ajax_set_paczkomat', 'inpost_paczkomaty_set_paczkomat' );
	add_action( 'wp_ajax_nopriv_set_paczkomat', 'inpost_paczkomaty_set_paczkomat' );

	function inpost_paczkomaty_set_paczkomat() {
		$paczkomat = sanitize_text_field( $_POST['paczkomat_name'] ?? '' );
		$adres1    = sanitize_text_field( $_POST['paczkomat_address1'] ?? '' );
		$adres2    = sanitize_text_field( $_POST['paczkomat_address2'] ?? '' );
		$adres3    = sanitize_text_field( $_POST['paczkomat_post_code'] ?? '' );
		$adres4    = sanitize_text_field( $_POST['paczkomat_city'] ?? '' );
		$adres5    = sanitize_text_field( $_POST['paczkomat_street'] ?? '' );
		$adres6    = sanitize_text_field( $_POST['paczkomat_building_number'] ?? '' );
		$adres7    = sanitize_text_field( $_POST['paczkomat_flat_number'] ?? '' );

		if ( empty( $paczkomat ) || empty( $adres1 ) ) {
			wp_die( '', '', 400 );
		}

		WC()->session->set( 'paczkomat_name', $paczkomat );
		WC()->session->set( 'paczkomat_address1', $adres1 );
		WC()->session->set( 'paczkomat_address2', $adres2 );
		WC()->session->set( 'paczkomat_post_code', $adres3 );
		WC()->session->set( 'paczkomat_city', $adres4 );
		WC()->session->set( 'paczkomat_street', $adres5 );
		WC()->session->set( 'paczkomat_building_number', $adres6 );
		WC()->session->set( 'paczkomat_flat_number', $adres7 );

		wp_send_json_success( array( 'status' => 'ok' ) );
	}

	// AJAX handler – returns the currently selected paczkomat from the session.
	// Used by the block checkout JS to restore state after a page refresh.
	add_action( 'wp_ajax_get_paczkomat_session', 'inpost_paczkomaty_get_paczkomat_session' );
	add_action( 'wp_ajax_nopriv_get_paczkomat_session', 'inpost_paczkomaty_get_paczkomat_session' );

	function inpost_paczkomaty_get_paczkomat_session() {
		$name = WC()->session->get( 'paczkomat_name' );

		if ( empty( $name ) ) {
			wp_send_json_success( array( 'name' => null ) );
		}

		wp_send_json_success( array(
			'name'            => $name,
			'address1'        => WC()->session->get( 'paczkomat_address1' ),
			'address2'        => WC()->session->get( 'paczkomat_address2' ),
			'post_code'       => WC()->session->get( 'paczkomat_post_code' ),
			'city'            => WC()->session->get( 'paczkomat_city' ),
			'street'          => WC()->session->get( 'paczkomat_street' ),
			'building_number' => WC()->session->get( 'paczkomat_building_number' ),
			'flat_number'     => WC()->session->get( 'paczkomat_flat_number' ),
		) );
	}

	// -------------------------------------------------------------------------
	// Admin order page – display selected paczkomat under the shipping address.
	// Shared by both checkout modes.
	// -------------------------------------------------------------------------

	add_action( 'woocommerce_admin_order_data_after_shipping_address', 'inpost_paczkomaty_checkout_field_display_admin_order_meta', 10, 1 );

	function inpost_paczkomaty_checkout_field_display_admin_order_meta( $order ) {
		if ( is_array( $order->get_items( 'shipping' ) ) && ! empty( $order->get_items( 'shipping' ) ) ) {
			$items              = $order->get_items( 'shipping' );
			$selected_method_id = reset( $items );
			$selected_method_id = $selected_method_id->get_method_id();

			if ( $selected_method_id === 'inpost_paczkomaty' ) {
				echo esc_html( __( 'Selected Paczkomat', 'inpost-paczkomaty' ) ) . ': ' . esc_attr( $order->get_meta( 'Wybrany paczkomat' ) );
			}
		}
	}

	// -------------------------------------------------------------------------
	// Order emails – append paczkomat info to the order totals table.
	// Shared by both checkout modes.
	// -------------------------------------------------------------------------

	add_filter( 'woocommerce_get_order_item_totals', 'inpost_paczkomaty_email_order_meta_fields', 10, 3 );

	function inpost_paczkomaty_email_order_meta_fields( $fields, $order ) {
		if ( ! is_array( $order->get_items( 'shipping' ) ) || empty( $order->get_items( 'shipping' ) ) ) {
			return $fields;
		}

		$items              = $order->get_items( 'shipping' );
		$selected_method_id = reset( $items );
		$selected_method_id = $selected_method_id->get_method_id();

		if ( $selected_method_id === 'inpost_paczkomaty' ) {
			$fields['meta_key'] = array(
				'label' => __( 'Paczkomat' ),
				'value' => $order->get_meta( 'Wybrany paczkomat' ),
			);
		}

		return $fields;
	}

	// -------------------------------------------------------------------------
	// Translations.
	// -------------------------------------------------------------------------

	add_action( 'init', 'inpost_paczkomaty_load_textdomain' );

	function inpost_paczkomaty_load_textdomain() {
		load_plugin_textdomain( 'inpost-paczkomaty', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	// -------------------------------------------------------------------------
	// Load the correct checkout integration file based on the configured mode.
	//   Legacy mode  → includes/checkout-legacy.php
	//   Block mode   → includes/checkout-blocks.php
	// -------------------------------------------------------------------------

	if ( INPOST_PACZKOMATY_LEGACY_MODE ) {
		require_once INPOST_PACZKOMATY_PLUGIN_PATH . 'includes/checkout-legacy.php';
	} else {
		require_once INPOST_PACZKOMATY_PLUGIN_PATH . 'includes/checkout-blocks.php';
	}

	// -------------------------------------------------------------------------
	// Admin settings panel.
	// -------------------------------------------------------------------------

	require_once INPOST_PACZKOMATY_PLUGIN_PATH . 'admin/admin.php';
}

