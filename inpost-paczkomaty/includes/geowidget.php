<?php
/**
 * InPost GeoWidget loader shared by classic and block checkout.
 *
 * Two widget versions are supported:
 *   v4 – legacy easyPack SDK (geowidget.easypack24.net). No token, Poland only. Default.
 *   v5 – current <inpost-geowidget> web component. Requires a GeoWidget token,
 *        supports multiple countries (international host).
 *
 * Every script that opens the map depends on the 'inpost-paczkomaty-map' handle
 * and calls window.InpostMap.open( callback ) – it never talks to the SDK directly.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Countries available in the v5 (international) GeoWidget.
 *
 * @return array Country code => label.
 */
function inpost_paczkomaty_geowidget_countries() {
	return array(
		'PL' => __( 'Polska', 'inpost-paczkomaty' ),
		'AT' => __( 'Austria', 'inpost-paczkomaty' ),
		'BE' => __( 'Belgia', 'inpost-paczkomaty' ),
		'FR' => __( 'Francja', 'inpost-paczkomaty' ),
		'ES' => __( 'Hiszpania', 'inpost-paczkomaty' ),
		'NL' => __( 'Holandia', 'inpost-paczkomaty' ),
		'LU' => __( 'Luksemburg', 'inpost-paczkomaty' ),
		'DE' => __( 'Niemcy', 'inpost-paczkomaty' ),
		'PT' => __( 'Portugalia', 'inpost-paczkomaty' ),
		'HU' => __( 'Węgry', 'inpost-paczkomaty' ),
		'GB' => __( 'Wielka Brytania', 'inpost-paczkomaty' ),
		'IT' => __( 'Włochy', 'inpost-paczkomaty' ),
	);
}

/**
 * Effective GeoWidget configuration.
 *
 * Falls back to v4 when v5 is selected but no token has been saved, so the
 * store never ends up without a working map.
 *
 * @return array{version:string,token:string,countries:string[]}
 */
function inpost_paczkomaty_geowidget_config() {
	$options = get_option( 'inpost_paczkomaty_options' );
	$options = is_array( $options ) ? $options : array();

	$version   = isset( $options['ip_geowidget_version'] ) && 'v5' === $options['ip_geowidget_version'] ? 'v5' : 'v4';
	$token     = isset( $options['ip_geowidget_token'] ) ? (string) $options['ip_geowidget_token'] : '';
	$countries = isset( $options['ip_geowidget_countries'] ) && is_array( $options['ip_geowidget_countries'] )
		? array_values( array_intersect( $options['ip_geowidget_countries'], array_keys( inpost_paczkomaty_geowidget_countries() ) ) )
		: array();

	if ( empty( $countries ) ) {
		$countries = array( 'PL' );
	}

	if ( 'v5' === $version && '' === $token ) {
		$version = 'v4';
	}

	return array(
		'version'   => $version,
		'token'     => $token,
		'countries' => $countries,
	);
}

/**
 * GeoWidget v5 language derived from the site locale.
 *
 * @return string
 */
function inpost_paczkomaty_geowidget_language() {
	$language  = strtolower( substr( determine_locale(), 0, 2 ) );
	$supported = array( 'pl', 'en', 'de', 'es', 'fr', 'it', 'nl', 'pt', 'uk' );

	return in_array( $language, $supported, true ) ? $language : 'en';
}

/**
 * Register the GeoWidget SDK, its stylesheet and the shared map script.
 *
 * Handles registered here:
 *   inpost-geowidget-sdk  – v4 easyPack SDK or v5 web component.
 *   inpost-geowidget-css  – v4 easypack.css or the v5 modal stylesheet.
 *   inpost-paczkomaty-map – window.InpostMap, the version-agnostic API.
 */
function inpost_paczkomaty_register_geowidget() {
	if ( wp_script_is( 'inpost-paczkomaty-map', 'registered' ) ) {
		return;
	}

	$config = inpost_paczkomaty_geowidget_config();

	if ( 'v5' === $config['version'] ) {
		// The Polish host has no country support, so it is used only for a PL-only
		// setup (Manager Paczek token). Any foreign country needs the international host.
		$sdk_url = array( 'PL' ) === $config['countries']
			? 'https://geowidget.inpost.pl/inpost-geowidget.js'
			: 'https://geowidget.inpost-group.com/inpost-geowidget.js';

		wp_register_script( 'inpost-geowidget-sdk', $sdk_url, array(), null, true );
		wp_register_style( 'inpost-geowidget-css', INPOST_PACZKOMATY_PLUGIN_URL . '/css/paczkomat-map.css', array(), '1.0.43' );
	} else {
		wp_register_script( 'inpost-geowidget-sdk', 'https://geowidget.easypack24.net/js/sdk-for-javascript.js', array(), null, true );
		wp_register_style( 'inpost-geowidget-css', 'https://geowidget.easypack24.net/css/easypack.css', array(), null );
	}

	wp_register_script(
		'inpost-paczkomaty-map',
		INPOST_PACZKOMATY_PLUGIN_URL . '/js/paczkomat-map.js',
		array( 'inpost-geowidget-sdk' ),
		'1.0.43',
		true
	);

	wp_localize_script(
		'inpost-paczkomaty-map',
		'inpostMapData',
		array(
			'version'   => $config['version'],
			'token'     => 'v5' === $config['version'] ? $config['token'] : '',
			'countries' => implode( ',', $config['countries'] ),
			'language'  => inpost_paczkomaty_geowidget_language(),
			'i18n'      => array(
				'close' => __( 'Zamknij', 'inpost-paczkomaty' ),
				'title' => __( 'Wybierz paczkomat', 'inpost-paczkomaty' ),
			),
		)
	);
}
