/**
 * InPost Paczkomaty – WooCommerce Block Checkout & Cart Integration.
 *
 * Injects a paczkomat selector button into the block-based cart and checkout
 * when the InPost Paczkomaty shipping method is selected.
 * Does NOT require JSX / a build step – uses wp.element.createElement directly.
 */
(function (wp, wc) {
    'use strict';

    // Guard: ensure required WP / WC globals are present.
    if ( !wp || !wc ) {
        console.warn( '[InPost] wp or wc not available' );
        return;
    }

    var blocksCheckout = wc.blocksCheckout || {};
    var registerPlugin = wp.plugins ? wp.plugins.registerPlugin : null;
    var el             = wp.element ? wp.element.createElement : null;
    var useState       = wp.element ? wp.element.useState : null;
    var useEffect      = wp.element ? wp.element.useEffect : null;
    var Fragment       = wp.element ? wp.element.Fragment : null;
    var useSelect      = wp.data ? wp.data.useSelect : null;

    if ( !registerPlugin || !el || !useState || !useEffect || !useSelect ) {
        console.warn( '[InPost] Required wp APIs not available:', {
            registerPlugin: !!registerPlugin, el: !!el,
            useState: !!useState, useEffect: !!useEffect, useSelect: !!useSelect
        } );
        return;
    }

    // ExperimentalOrderShippingPackages = renders inside shipping section (preferred).
    // ExperimentalOrderMeta             = renders in order totals sidebar (fallback).
    var SlotShipping = blocksCheckout.ExperimentalOrderShippingPackages || null;
    var SlotMeta     = blocksCheckout.ExperimentalOrderMeta             || null;
    var Slot         = SlotShipping || SlotMeta;

    if ( !Slot ) {
        console.warn( '[InPost] No WooCommerce Blocks slot found. Keys:', Object.keys( blocksCheckout ) );
        return;
    }

    console.log( '[InPost] Using slot:', SlotShipping ? 'ExperimentalOrderShippingPackages' : 'ExperimentalOrderMeta (fallback)' );

    // Track GeoWidget initialization state
    var easyPackInitialized = false;

    /**
     * Initialize the InPost GeoWidget SDK.
     * Called on first use to avoid premature initialization.
     */
    function initEasyPack() {
        if ( easyPackInitialized || typeof easyPack === 'undefined' ) {
            return;
        }
        easyPack.init({
            defaultLocale: 'pl',
            mapType:       'osm',
            searchType:    'osm',
            points: { types: ['parcel_locker'] },
            map:    { initialTypes: ['parcel_locker'] }
        });
        easyPackInitialized = true;
    }

    // Define async init callback so the SDK can call it after loading
    window.easyPackAsyncInit = initEasyPack;

    /**
     * Check whether the inpost_paczkomaty shipping method is currently selected.
     * WC Blocks store may use camelCase (methodId) OR snake_case (method_id)
     * depending on the WC Blocks version – we check both.
     *
     * @param {Array} shippingRates
     * @returns {boolean}
     */
    function checkInpostSelected( shippingRates ) {
        if ( !shippingRates || !shippingRates.length ) {
            return false;
        }
        var found = false;
        shippingRates.forEach( function (pkg) {
            // Package rates can be under shippingRates (camelCase) or shipping_rates (snake_case).
            var rates = pkg.shippingRates || pkg.shipping_rates || [];
            rates.forEach( function (rate) {
                var methodId = rate.methodId || rate.method_id || '';
                if ( rate.selected && methodId === 'inpost_paczkomaty' ) {
                    found = true;
                }
            });
        });
        return found;
    }

    /**
     * Paczkomat selector React component.
     * Renders the "Choose paczkomat" button and selected paczkomat info
     * when InPost shipping method is active.
     */
    function InpostPaczkomatSelector() {
        // Subscribe to WC cart store to detect selected shipping method
        var cartData = useSelect( function ( select ) {
            return select( 'wc/store/cart' ).getCartData();
        });

        var paczkomatState       = useState( null );
        var selectedPaczkomat    = paczkomatState[0];
        var setSelectedPaczkomat = paczkomatState[1];

        // Initialize GeoWidget SDK and restore previously selected paczkomat from session.
        // Without this, a page refresh would clear the React state even though
        // the WooCommerce PHP session still holds the selection.
        useEffect( function () {
            initEasyPack();

            jQuery.post( inpostBlocksData.ajaxUrl, { action: 'get_paczkomat_session', nonce: inpostBlocksData.nonce }, function ( response ) {
                if ( response && response.success && response.data && response.data.name ) {
                    setSelectedPaczkomat({
                        name: response.data.name,
                        address: {
                            line1: response.data.address1,
                            line2: response.data.address2,
                        },
                        address_details: {
                            post_code:       response.data.post_code,
                            city:            response.data.city,
                            street:          response.data.street,
                            building_number: response.data.building_number,
                            flat_number:     response.data.flat_number,
                        }
                    });
                }
            });
        }, [] );

        var rates        = cartData ? ( cartData.shippingRates || cartData.shipping_rates || [] ) : [];
        var inpostActive = checkInpostSelected( rates );

        console.log( '[InPost] shippingRates:', rates, '| inpostActive:', inpostActive );

        // Only render when InPost shipping is selected
        if ( !inpostActive ) {
            return null;
        }

        /**
         * Open the InPost modalMap for paczkomat selection.
         *
         * @param {Event} e
         */
        function openPaczkomatMap( e ) {
            e.preventDefault();

            if ( typeof easyPack === 'undefined' ) {
                console.error( 'InPost GeoWidget SDK is not loaded.' );
                return;
            }

            initEasyPack();

            easyPack.modalMap( function ( point, modal ) {
                modal.closeModal();
                setSelectedPaczkomat( point );

                // Persist selection to WooCommerce session via existing AJAX handler
                jQuery.post( inpostBlocksData.ajaxUrl, {
                    action:                   'set_paczkomat',
                    nonce:                    inpostBlocksData.nonce,
                    paczkomat_name:           point.name,
                    paczkomat_address1:       point.address.line1,
                    paczkomat_address2:       point.address.line2,
                    paczkomat_post_code:      point.address_details.post_code,
                    paczkomat_city:           point.address_details.city,
                    paczkomat_street:         point.address_details.street,
                    paczkomat_building_number: point.address_details.building_number,
                    paczkomat_flat_number:    point.address_details.flat_number,
                });
            }, { width: 500, height: 600 });
        }

        return el(
            Fragment,
            null,
            el(
                'div',
                {
                    className: 'inpost-paczkomat-block-wrapper',
                    style: { marginTop: '12px', marginBottom: '8px' }
                },
                // Select / change button
                el(
                    'button',
                    {
                        type:      'button',
                        className: 'wc-block-components-button wp-element-button button select-paczkomat-button',
                        onClick:   openPaczkomatMap,
                        style:     { marginBottom: '8px' }
                    },
                    selectedPaczkomat ? 'Zmień paczkomat' : 'Wybierz paczkomat'
                ),
                // Show selected paczkomat details
                selectedPaczkomat
                    ? el(
                        'div',
                        {
                            className: 'inpost-selected-paczkomat',
                            style:     { padding: '8px', background: '#f0f0f1', borderRadius: '4px', fontSize: '13px' }
                        },
                        el( 'strong', null, 'Wybrany paczkomat: ' ),
                        el( 'br', null ),
                        selectedPaczkomat.name,
                        el( 'br', null ),
                        selectedPaczkomat.address.line1,
                        el( 'br', null ),
                        selectedPaczkomat.address.line2
                    )
                    : null
            )
        );
    }

    /**
     * Plugin render wrapper – injects selector into the chosen slot.
     */
    function InpostShippingPlugin() {
        return el( Slot, null, el( InpostPaczkomatSelector, null ) );
    }

    // Register for the WooCommerce Checkout block
    registerPlugin( 'inpost-paczkomat-checkout', {
        render: InpostShippingPlugin,
        scope:  'woocommerce-checkout'
    });

    // Register for the WooCommerce Cart block (shipping visible in cart sidebar)
    registerPlugin( 'inpost-paczkomat-cart', {
        render: InpostShippingPlugin,
        scope:  'woocommerce-cart'
    });

    console.log( '[InPost] registerPlugin called for woocommerce-checkout and woocommerce-cart' );

})( window.wp, window.wc );

