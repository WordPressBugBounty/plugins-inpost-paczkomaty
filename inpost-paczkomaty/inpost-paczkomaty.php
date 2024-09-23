<?php
/**
 * Plugin Name: Inpost Paczkomaty
 * Description: Plugin do obsługi paczkomatów inpost w woocommerce.
 * Version: 1.0.34
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
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	function inpost_paczkomaty_shipping_method() {
		if ( ! class_exists( 'inpost_paczkomaty_shipping_method' ) ) {
			class inpost_paczkomaty_shipping_method extends WC_Shipping_Method {
				/**
				 * Requires option.
				 *
				 * @var string
				 */
				public $requires = '';

				/**
				 * ignore_discounts
				 *
				 * @var boolean
				 */
				public $ignore_discounts = false;

				/**
				 * cost
				 *
				 * @var double
				 */
				public $cost = '';

				/**
				 * fee_cost
				 *
				 * @var double
				 */
				protected string $fee_cost = '';

				/**
				 * type
				 *
				 * @var boolean
				 */
				public $type = '';

				/**
				 * Min amount to be valid.
				 *
				 * @var integer
				 */
				public $min_amount = 0;

				/**
				 * Max amount to be valid.
				 *
				 * @var integer
				 */
				public $max_amount = 0;

				public $informations = '';

				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
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
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
					// Load the settings API
					$this->instance_form_fields = include 'includes/settings-inpost-paczkomaty.php';
					$this->title                = $this->get_option( 'title' );
					$this->tax_status           = $this->get_option( 'tax_status' );
					$this->requires             = $this->get_option( 'requires' );
					$this->cost                 = $this->get_option( 'cost' );
					$this->min_amount           = $this->get_option( 'min_amount', 0 );
					$this->max_amount           = $this->get_option( 'max_amount', 0 );
					$this->ignore_discounts     = $this->get_option( 'ignore_discounts' );

					$this->type = $this->get_option( 'type', 'class' );

					add_action( 'woocommerce_update_options_shipping_' . $this->id, array(
						$this,
						'process_admin_options'
					) );
					add_action( 'admin_footer', array(
						'inpost_paczkomaty_shipping_method',
						'enqueue_admin_js'
					), 10 ); // Priority needs to be higher than wc_print_js (25).
				}

				/**
				 * See if free shipping is available based on the package and cart.
				 *
				 * @param array $package Shipping package.
				 *
				 * @return bool
				 */
				public function is_available( $package ) {
					$has_met_min_amount         = false;
					$has_met_max_amount         = false;
					$has_met_min_and_max_amount = false;


					//Weight limit
					$ip_settings = get_option( 'inpost_paczkomaty_options' );


					if ( in_array( $this->requires, array( 'min_amount' ), true ) ) {
						$total = WC()->cart->get_displayed_subtotal();

						$total = NumberUtil::round( $total, wc_get_price_decimals() );

						if ( 'no' === $this->ignore_discounts ) {
							$total = $total - WC()->cart->get_discount_total();
						}

						if ( $total >= $this->min_amount ) {
							$has_met_min_amount = true;
						}
					}

					if ( in_array( $this->requires, array( 'max_amount' ), true ) ) {
						$total = WC()->cart->get_displayed_subtotal();

						$total = NumberUtil::round( $total, wc_get_price_decimals() );

						if ( 'no' === $this->ignore_discounts ) {
							$total = $total - WC()->cart->get_discount_total();
						}

						if ( $total <= $this->max_amount ) {
							$has_met_max_amount = true;
						}
					}

					if ( in_array( $this->requires, array( 'min_and_max_amount' ), true ) ) {
						$total = WC()->cart->get_displayed_subtotal();

						$total = NumberUtil::round( $total, wc_get_price_decimals() );

						if ( 'no' === $this->ignore_discounts ) {
							$total = $total - WC()->cart->get_discount_total();
						}

						if ( $total <= $this->max_amount && $total >= $this->min_amount ) {
							$has_met_min_and_max_amount = true;
						}
					}


					//Dimensions limit


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

					if ( isset( $ip_settings['ip_select_weight_limit'] ) && $ip_settings['ip_select_weight_limit'] == 'yes' ) {
						if ( isset( $ip_settings['ip_select_weight_limit_value'] ) && ! empty( $ip_settings['ip_select_weight_limit_value'] ) && $ip_settings['ip_select_weight_limit_value'] > 0 ) {
							$total_weight = WC()->cart->cart_contents_weight;
							if ( isset( $ip_settings['ip_select_weight_limit_result'] ) && $ip_settings['ip_select_weight_limit_result'] == 'hide' ) {
								if ( $total_weight > $ip_settings['ip_select_weight_limit_value'] ) {
									$is_available = false;
								}
							}
						}
					}


					if ( isset( $ip_settings['ip_select_dimensions_limit'] ) && $ip_settings['ip_select_dimensions_limit'] == 'yes' ) {
						$cart       = WC()->cart;
						$cart_items = $cart->get_cart();


						foreach ( $cart_items as $cart_item ) {
							// Pobranie obiektu produktu dla danego elementu koszyka
							$product = $cart_item['data'];

							if ( isset( $ip_settings['ip_select_dimensions_limit_width'] ) && ! empty( $ip_settings['ip_select_dimensions_limit_width'] ) ) {
								$dimensions_limit_width = $ip_settings['ip_select_dimensions_limit_width'];
								$width                  = $product->get_width();
								if ( $width > $dimensions_limit_width ) {
									$is_available = false;
									break;
								}
							}

							if ( isset( $ip_settings['ip_select_dimensions_limit_height'] ) && ! empty( $ip_settings['ip_select_dimensions_limit_height'] ) ) {
								$dimensions_limit_height = $ip_settings['ip_select_dimensions_limit_height'];
								$height                  = $product->get_height();
								if ( $height > $dimensions_limit_height ) {
									$is_available = false;
									break;
								}
							}

							if ( isset( $ip_settings['ip_select_dimensions_limit_length'] ) && ! empty( $ip_settings['ip_select_dimensions_limit_length'] ) ) {
								$dimensions_limit_length = $ip_settings['ip_select_dimensions_limit_length'];
								$length                  = $product->get_length();
								if ( $length > $dimensions_limit_length ) {
									$is_available = false;
									break;
								}
							}
						}


					}

					return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
				}


				/**
				 * Enqueue JS to handle free shipping options.
				 *
				 * Static so that's enqueued only once.
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
					if ( 'max_amount' === $( el ).val()  ) {
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
					var ignoreDiscountField = $( '#woocommerce_free_shipping_ignore_discounts', form ).closest( 'tr' );
					if ( 'min_and_max_amount' === $( el ).val()  ) {
					    minAmountField.show();	
						maxAmountField.show();					
					} 
				}
				

				$( document.body ).on( 'change', '#woocommerce_inpost_paczkomaty_requires', function() {
					wcInpostPaczkomatyShowHideMinAmountField( this );
					wcInpostPaczkomatyShowHideMaxAmountField( this );
					wcInpostPaczkomatyShowHideMinMaxAmountField( this );
				});

				// Change while load.
				$( '#woocommerce_inpost_paczkomaty_requires' ).trigger( 'change' );
				$( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
					if ( 'wc-modal-shipping-method-settings' === target ) {
						wcInpostPaczkomatyShowHideMinAmountField( $( '#wc-backbone-modal-dialog #woocommerce_inpost_paczkomaty_requires', evt.currentTarget ) );
						wcInpostPaczkomatyShowHideMaxAmountField( $( '#wc-backbone-modal-dialog #woocommerce_inpost_paczkomaty_requires', evt.currentTarget ) );
						wcInpostPaczkomatyShowHideMinMaxAmountField( $( '#wc-backbone-modal-dialog #woocommerce_inpost_paczkomaty_requires', evt.currentTarget ) );
					}
					
				} );
			});"
					);
				}

				/**
				 * Define settings field for this shipping
				 * @return void
				 */
				/**
				 * Evaluate a cost from a sum/string.
				 *
				 * @param string $sum Sum of shipping.
				 * @param array $args Args, must contain `cost` and `qty` keys. Having `array()` as default is for back compat reasons.
				 *
				 * @return string
				 */

				protected function evaluate_cost( $sum, $args = array() ) {
					// Add warning for subclasses.
					if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
						wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
					}

					include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

					// Allow 3rd parties to process shipping cost arguments.
					$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
					$locale         = localeconv();
					$decimals       = array(
						wc_get_price_decimal_separator(),
						$locale['decimal_point'],
						$locale['mon_decimal_point'],
						','
					);
					$this->fee_cost = $args['cost'];

					// Expand shortcodes.
					add_shortcode( 'fee', array( $this, 'fee' ) );

					$sum = do_shortcode(
						str_replace(
							array(
								'[qty]',
								'[cost]',
							),
							array(
								$args['qty'],
								$args['cost'],
							),
							$sum
						)
					);

					remove_shortcode( 'fee', array( $this, 'fee' ) );

					// Remove whitespace from string.
					$sum = preg_replace( '/\s+/', '', $sum );

					// Remove locale from string.
					$sum = str_replace( $decimals, '.', $sum );

					// Trim invalid start/end characters.
					$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

					// Do the math.
					return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
				}

				/**
				 * Work out fee (shortcode).
				 *
				 * @param array $atts Attributes.
				 *
				 * @return string
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
				 * Calculate the shipping costs.
				 *
				 * @param array $package Package of items from cart.
				 */
				public function calculate_shipping( $package = array() ) {
					$rate = array(
						'id'      => $this->get_rate_id(),
						'label'   => $this->title,
						'cost'    => 0,
						'package' => $package,
					);


					// Calculate the costs.
					$has_costs = true; // True when a cost is set. False if all costs are blank strings.
					$cost      = $this->get_option( 'cost' );
					$qty       = $this->get_package_item_qty( $package );


					$ip_settings = get_option( 'inpost_paczkomaty_options' );
					if ( isset( $ip_settings['ip_select_weight_limit'] ) ) {
						$weight_limit_checkbox = $ip_settings['ip_select_weight_limit'];
						if ( isset( $weight_limit_checkbox ) && $weight_limit_checkbox == 'yes' ) {
							if ( isset( $ip_settings['ip_select_weight_limit_value'] ) && ! empty( $ip_settings['ip_select_weight_limit_value'] ) ) {
								$weight_limit_value = $ip_settings['ip_select_weight_limit_value'];
								$total_weight       = WC()->cart->cart_contents_weight;
								if ( isset( $ip_settings['ip_select_weight_limit_result'] ) && $ip_settings['ip_select_weight_limit_result'] == 'split' ) {
									if ( $total_weight > $weight_limit_value ) {
										$quantity = ceil( $total_weight / $weight_limit_value );
										$qty      = $quantity;
										$cost     = $cost * $quantity;
									}
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

					// Add shipping class costs.
					$shipping_classes = WC()->shipping()->get_shipping_classes();

					if ( ! empty( $shipping_classes ) ) {
						$found_shipping_classes = $this->find_shipping_classes( $package );
						$highest_class_cost     = 0;

						foreach ( $found_shipping_classes as $shipping_class => $products ) {
							// Also handles BW compatibility when slugs were used instead of ids.
							$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
							$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option( 'class_cost_' . $shipping_class_term->term_id, $this->get_option( 'class_cost_' . $shipping_class, '' ) ) : $this->get_option( 'no_class_cost', '' );

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
								$highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
							}
						}

						if ( 'order' === $this->type && $highest_class_cost ) {
							$rate['cost'] += $highest_class_cost;
						}
					}
					if ( $has_costs ) {
						$this->add_rate( $rate );
					}

					/**
					 * Developers can add additional flat rates based on this one via this action since @version 2.4.
					 *
					 * Previously there were (overly complex) options to add additional rates however this was not user.
					 * friendly and goes against what Flat Rate Shipping was originally intended for.
					 */
					do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
				}


				/**
				 * Get items in package.
				 *
				 * @param array $package Package of items from cart.
				 *
				 * @return int
				 */
				public function get_package_item_qty( $package ) {
					$total_quantity = 0;
					foreach ( $package['contents'] as $item_id => $values ) {
						if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
							$total_quantity += $values['quantity'];
						}
					}

					return $total_quantity;
				}

				/**
				 * Finds and returns shipping classes and the products with said class.
				 *
				 * @param mixed $package Package of items from cart.
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
				 * Sanitize the cost field.
				 *
				 * @param string $value Unsanitized value.
				 *
				 * @return string
				 * @throws Exception Last error triggered.
				 * @since 3.4.0
				 */
				public function sanitize_cost( $value ) {
					$value = is_null( $value ) ? '' : $value;
					$value = wp_kses_post( trim( wp_unslash( $value ) ) );
					$value = str_replace( array(
						get_woocommerce_currency_symbol(),
						html_entity_decode( get_woocommerce_currency_symbol() )
					), '', $value );
					// Thrown an error on the front end if the evaluate_cost will fail.
					$dummy_cost = $this->evaluate_cost(
						$value,
						array(
							'cost' => 1,
							'qty'  => 1,
						)
					);
					if ( false === $dummy_cost ) {
						throw new Exception( WC_Eval_Math::$last_error );
					}

					return $value;
				}
			}
		}
	}

	add_action( 'woocommerce_shipping_init', 'inpost_paczkomaty_shipping_method' );

	function inpost_paczkomaty_add_inpost_shipping_method( $methods ) {
		$methods['inpost_paczkomaty'] = 'inpost_paczkomaty_shipping_method';

		return $methods;
	}

	//include JS from inpost, required to show map at Cart Page
	add_action( 'woocommerce_before_cart', 'inpost_paczkomaty_styles_and_scripts_before_cart' );
	function inpost_paczkomaty_styles_and_scripts_before_cart() {
		wp_enqueue_script( 'inpost_js', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js' );

		//set protocol
		if ( isset( $_SERVER['HTTPS'] ) ) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		$admin_ajax_url = admin_url( 'admin-ajax.php', $protocol );


		wp_register_script( 'paczkomat_modal', plugins_url( 'js/paczkomat-modal.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'paczkomat_modal', 'ajax_options', array( 'admin_ajax_url' => $admin_ajax_url ) );
		wp_enqueue_script( 'paczkomat_modal', plugins_url( 'js/paczkomat-modal.js', __FILE__ ), array( 'jquery' ) );

		wp_enqueue_style( 'inpost_paczkomaty_inpost_css', 'https://geowidget.easypack24.net/css/easypack.css' );
	}

	//include JS from inpost, required to show map at Checkout Page
	add_action( 'woocommerce_before_checkout_form', 'inpost_paczkomaty_styles_and_scripts_before_checkout' );
	function inpost_paczkomaty_styles_and_scripts_before_checkout() {
		wp_enqueue_script( 'inpost_js', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js' );

		//set protocol
		if ( isset( $_SERVER['HTTPS'] ) ) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		$admin_ajax_url = admin_url( 'admin-ajax.php', $protocol );

		wp_register_script( 'paczkomat_modal', plugins_url( 'js/paczkomat-modal.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'paczkomat_modal', 'ajax_options', array( 'admin_ajax_url' => $admin_ajax_url ) );
		wp_enqueue_script( 'paczkomat_modal', plugins_url( 'js/paczkomat-modal.js', __FILE__ ), array( 'jquery' ) );

		wp_enqueue_style( 'inpost_paczkomaty_inpost_css', 'https://geowidget.easypack24.net/css/easypack.css' );
	}

	add_action( 'woocommerce_checkout_process', 'paczkomaty_inpost_validation_checkout' );

	function paczkomaty_inpost_validation_checkout() {
		$selected_shipping = WC()->session->get( 'chosen_shipping_methods' );
		if ( isset( $selected_shipping ) && ! empty( $selected_shipping ) ) {
			$selected      = explode( ':', $selected_shipping[0] );
			$selected_name = WC()->session->get( 'paczkomat_name' );

			if ( $selected[0] == 'inpost_paczkomaty' && ( ! isset( $selected_name ) || empty( $selected_name ) ) ) {
				wc_add_notice( __( 'Nie wybrano paczkomatu. Wybierz paczkomat lub zmień formę wysyłki.' ), 'error' );
			}
		}
	}

	// add the action
	add_action( 'woocommerce_after_shipping_rate', 'inpost_paczkomaty_action_woocommerce_checkout_before_order_review', 10, 2 );
	// Paczkomaty on Checkout page
	do_action( 'inpost_paczkomaty_woocommerce_checkout_order_review' );
	function inpost_paczkomaty_action_woocommerce_checkout_before_order_review( $shipping ) {
		$settings = get_option( 'inpost_paczkomaty_options' );
		if ( isset( $settings['ip_select_show_logo'] ) ) {
			$settings_show_logo = $settings['ip_select_show_logo'] == 'yes' ? 'yes' : 'no';
			if ( isset( $settings['ip_select_show_logo_img'] ) && ! empty( $settings['ip_select_show_logo_img'] ) ) {
				$settings_show_logo_img = $settings['ip_select_show_logo_img'];
				if ( $settings_show_logo == 'yes' ) {
					if ( isset( $settings_show_logo_img ) && ! empty( $settings_show_logo_img ) ) {
						if ( $shipping->method_id == "inpost_paczkomaty" ) {
							$allowed_html_img = array(
								'div' => array(),
								'img' => array(
									'src'   => array(),
									'class' => array(),
									"width" => array(),
								),
							);
							echo wp_kses( '<div><img width="100px" src="' . $settings_show_logo_img . '" class="paczkomat-logo"></div>', $allowed_html_img );
						}
					}

				}
			}
		}


		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping_methods = explode( ':', $chosen_shipping_methods[0] );
		if ( $chosen_shipping_methods[0] == 'inpost_paczkomaty' && $shipping->method_id == 'inpost_paczkomaty' ) {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'paczkomat_modal' );

			$selected_name     = WC()->session->get( 'paczkomat_name' );
			$selected_address1 = WC()->session->get( 'paczkomat_address1' );
			$selected_address2 = WC()->session->get( 'paczkomat_address2' );


			if ( empty( $selected_name ) ) {
				$allowed_html = array(
					'div'    => array(),
					'button' => array(
						'type'  => array(),
						'class' => array(),
					),
				);


				echo wp_kses( '<div><button type="button" class="btn button select-paczkomat-button">Wybierz paczkomat</button></div>', $allowed_html );


				$allowed_html = array(
					'div' => array(
						'id' => array(),
					),
				);
				echo wp_kses( '<div id="selected-paczkomat"></div>', $allowed_html );
			} else {
				$allowed_html = array(
					'div'    => array(),
					'button' => array(
						'type'  => array(),
						'class' => array(),
					),
				);
				echo wp_kses( '<div><button type="button" class="btn button select-paczkomat-button">Zmień paczkomat</button></div>', $allowed_html );

				$allowed_html = array(
					'div' => array(
						'id' => array(),
					),
					'br'  => array(),
				);
				echo wp_kses( '<div id="selected-paczkomat"> Wybrany paczkomat: <br>' . $selected_name . '<br>' . $selected_address1 . '<br>' . $selected_address2 . '</div>', $allowed_html );
			}
		}
	}

	/**
	 * Update the order meta with field value
	 */
	add_action( 'woocommerce_checkout_update_order_meta', 'inpost_paczkomaty_checkout_field_update_order_meta' );
	function inpost_paczkomaty_checkout_field_update_order_meta( $order_id ) {

		$settings                = get_option( 'inpost_paczkomaty_options' );
		$ip_selected_as_shipping = $settings['ip_selected_as_shipping'] == 'yes' ? 'yes' : 'no';

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( isset( $chosen_shipping_methods[0] ) ) {
			$chosen_shipping_methods = explode( ':', $chosen_shipping_methods[0] );
			if ( $chosen_shipping_methods[0] == 'inpost_paczkomaty' ) {

				;
				$selected_name                    = WC()->session->get( 'paczkomat_name' );
				$selected_address1                = WC()->session->get( 'paczkomat_address1' );
				$selected_address2                = WC()->session->get( 'paczkomat_address2' );
				$selected_address_post_code       = WC()->session->get( 'paczkomat_post_code' );
				$selected_address_city            = WC()->session->get( 'paczkomat_city' );
				$selected_address_street          = WC()->session->get( 'paczkomat_street' );
				$selected_address_building_number = WC()->session->get( 'paczkomat_building_number' );
				$selected_address_flat_number     = WC()->session->get( 'paczkomat_flat_number' );


				$order = wc_get_order( $order_id );

				$val = $selected_name . ', ' . $selected_address1 . ', ' . $selected_address2;

				if ( ! empty( $val ) ) {
//					update_post_meta( $order_id, 'Wybrany paczkomat', $val );
					$order->add_meta_data( 'Wybrany paczkomat', $val, true );
				}
				if ( ! empty( $selected_name ) ) {
//					update_post_meta( $order_id, '_paczkomat_id', $selected_name );
//					update_post_meta( $order_id, 'paczkomat_key', $selected_name );
					$order->add_meta_data( '_paczkomat_id', $selected_name, true );
					$order->add_meta_data( 'paczkomat_key', $selected_name, true );
				}

//				update_post_meta( $order_id, 'delivery_point_name', $selected_name );
//				update_post_meta( $order_id, 'delivery_point_city', $selected_address_city );
//				update_post_meta( $order_id, 'delivery_point_postcode', $selected_address_post_code );
//				update_post_meta( $order_id, 'delivery_point_address', $selected_address1 );


				$order->add_meta_data( 'delivery_point_name', $selected_name, true );
				$order->add_meta_data( 'delivery_point_city', $selected_address_city, true );
				$order->add_meta_data( 'delivery_point_postcode', $selected_address_post_code, true );
				$order->add_meta_data( 'delivery_point_address', $selected_address1, true );


				if ( $ip_selected_as_shipping == 'yes' ) {
//					update_post_meta( $order_id, '_shipping_address_1', $selected_address1 );
//					update_post_meta( $order_id, '_shipping_address_2', $selected_name );
//					update_post_meta( $order_id, '_shipping_city', $selected_address_city );
//					update_post_meta( $order_id, '_shipping_postcode', $selected_address_post_code );

					$order->add_meta_data( '_shipping_address_1', $selected_address1, true );
					$order->add_meta_data( '_shipping_address_2', $selected_name, true );
					$order->add_meta_data( '_shipping_city', $selected_address_city, true );
					$order->add_meta_data( '_shipping_postcode', $selected_address_post_code, true );


				}
				$order->save();
			}
		}

	}

	/**
	 * Display field value on the order edit page
	 */
	add_action( 'woocommerce_admin_order_data_after_shipping_address', 'inpost_paczkomaty_checkout_field_display_admin_order_meta', 10, 1 );

	function inpost_paczkomaty_checkout_field_display_admin_order_meta( $order ) {
		if ( is_array( $order->get_items( 'shipping' ) ) && ! empty( $order->get_items( 'shipping' ) ) ) {
			$items              = $order->get_items( 'shipping' );
			$selected_method_id = reset( $items );
			$selected_method_id = $selected_method_id->get_method_id();

			if ( $selected_method_id == 'inpost_paczkomaty' ) {
				echo __( 'Selected Paczkomat', 'inpost-paczkomaty' ) . ': ' . esc_attr( $order->get_meta( 'Wybrany paczkomat' ) );
			}
		}
	}

	/**
	 * Add a paczkomat field to the emails
	 */

	function custom_woocommerce_email_order_meta_fields( $fields, $order ) {
		if ( is_array( $order->get_items( 'shipping' ) ) && ! empty( $order->get_items( 'shipping' ) ) ) {
			$items              = $order->get_items( 'shipping' );
			$selected_method_id = reset( $items );
			$selected_method_id = $selected_method_id->get_method_id();

			if ( $selected_method_id == 'inpost_paczkomaty' ) {
				$fields['meta_key'] = array(
					'label' => __( 'Paczkomat' ),
					'value' => $order->get_meta( 'Wybrany paczkomat' ),
				);

				return $fields;
			}

			return $fields;
		}

		return $fields;
	}


	add_filter( 'woocommerce_get_order_item_totals', 'custom_woocommerce_email_order_meta_fields', 10, 3 );

	/**
	 * init cart
	 */
	add_action( 'woocommerce_before_cart', 'inpost_paczkomaty_initCart' );

	function inpost_paczkomaty_initCart() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'paczkomat_modal' );
	}

	//ajax set selected paczkomat
	add_action( 'wp_ajax_set_paczkomat', 'inpost_paczkomaty_set_paczkomat' );
	add_action( 'wp_ajax_nopriv_set_paczkomat', 'inpost_paczkomaty_set_paczkomat' );
	function inpost_paczkomaty_set_paczkomat() {

		$cart              = WC()->cart->cart_contents;
		$cart['paczkomat'] = sanitize_text_field( $_POST['paczkomat_name'] );
		//sanitize fields
		$paczkomat = sanitize_text_field( $_POST['paczkomat_name'] );
		$adres1    = sanitize_text_field( $_POST['paczkomat_address1'] );
		$adres2    = sanitize_text_field( $_POST['paczkomat_address2'] );
		$adres3    = sanitize_text_field( $_POST['paczkomat_post_code'] );
		$adres4    = sanitize_text_field( $_POST['paczkomat_city'] );
		$adres5    = sanitize_text_field( $_POST['paczkomat_street'] );
		$adres6    = sanitize_text_field( $_POST['paczkomat_building_number'] );
		$adres7    = sanitize_text_field( $_POST['paczkomat_flat_number'] );


		unset( $_POST['paczkomat_name'] );
		unset( $_POST['paczkomat_address1'] );
		unset( $_POST['paczkomat_address2'] );
		unset( $_POST['paczkomat_post_code'] );
		unset( $_POST['paczkomat_city'] );
		unset( $_POST['paczkomat_street'] );
		unset( $_POST['paczkomat_building_number'] );
		unset( $_POST['paczkomat_flat_number'] );

		//validate if not empty
		if ( empty( $paczkomat ) || empty( $adres1 ) ) {
			die;
		}


		if ( $paczkomat ) {
			WC()->session->set( 'paczkomat_name', $paczkomat );
			WC()->session->set( 'paczkomat_address1', $adres1 );
			WC()->session->set( 'paczkomat_address2', $adres2 );
			WC()->session->set( 'paczkomat_post_code', $adres3 );
			WC()->session->set( 'paczkomat_city', $adres4 );
			WC()->session->set( 'paczkomat_street', $adres5 );
			WC()->session->set( 'paczkomat_building_number', $adres6 );
			WC()->session->set( 'paczkomat_flat_number', $adres7 );

			//unset variables
			unset( $paczkomat );
			unset( $adres1 );
			unset( $adres2 );
			unset( $adres3 );
			unset( $adres4 );
			unset( $adres5 );
			unset( $adres6 );
			unset( $adres7 );

			return 'success';
		} else {
			return 'failed';
		}

		die;
	}

	add_filter( 'woocommerce_shipping_methods', 'inpost_paczkomaty_add_inpost_shipping_method' );

	add_action( 'init', 'wpdocs_load_textdomain' );

	function wpdocs_load_textdomain() {
		load_plugin_textdomain( 'inpost-paczkomaty', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	include( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );

}

