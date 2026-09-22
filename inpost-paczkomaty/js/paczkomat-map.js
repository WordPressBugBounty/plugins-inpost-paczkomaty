/**
 * InPost Paczkomaty – version-agnostic map API.
 *
 * Exposes window.InpostMap.open( callback ), which opens either the legacy
 * GeoWidget v4 (easyPack SDK, no token) or GeoWidget v5 (<inpost-geowidget>,
 * token required), depending on the plugin settings passed in inpostMapData.
 *
 * The callback always receives a point in the v4 shape:
 *   { name, address: { line1, line2 }, address_details: { post_code, city, street, building_number, flat_number } }
 */
(function (window, document) {
    'use strict';

    var data = window.inpostMapData || {};
    var i18n = data.i18n || {};

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    /**
     * Normalize a point from either widget version to the v4 shape the rest of
     * the plugin (JS and PHP AJAX handler) expects.
     *
     * @param {Object} point
     * @returns {Object}
     */
    function normalizePoint(point) {
        var address = point.address || {};
        var details = point.address_details || {};

        var street   = [ details.street, details.building_number ].filter( Boolean ).join( ' ' );
        var postCity = [ details.post_code, details.city ].filter( Boolean ).join( ' ' );

        return {
            name: point.name || '',
            address: {
                line1: address.line1 || street,
                line2: address.line2 || postCity
            },
            address_details: {
                post_code:       details.post_code || '',
                city:            details.city || '',
                street:          details.street || '',
                building_number: details.building_number || '',
                flat_number:     details.flat_number || ''
            }
        };
    }

    // -------------------------------------------------------------------------
    // GeoWidget v4 (easyPack SDK)
    // -------------------------------------------------------------------------

    var v4Initialized = false;

    function initV4() {
        if ( v4Initialized || typeof window.easyPack === 'undefined' ) {
            return;
        }
        window.easyPack.init({
            defaultLocale: 'pl',
            mapType:       'osm',
            searchType:    'osm',
            points: { types: ['parcel_locker'] },
            map:    { initialTypes: ['parcel_locker'] }
        });
        v4Initialized = true;
    }

    function openV4(callback) {
        if ( typeof window.easyPack === 'undefined' ) {
            console.error( 'InPost GeoWidget SDK is not loaded.' );
            return;
        }

        initV4();

        window.easyPack.modalMap( function (point, modal) {
            modal.closeModal();
            if ( point ) {
                callback( normalizePoint( point ) );
            }
        }, { width: 500, height: 600 });
    }

    // -------------------------------------------------------------------------
    // GeoWidget v5 (<inpost-geowidget> web component)
    // -------------------------------------------------------------------------

    // Built once and reused: the widget registers a window "message" listener it
    // never removes, so creating a new element per open would leak listeners.
    var v5Overlay  = null;
    var v5Callback = null;

    function closeV5() {
        if ( v5Overlay ) {
            v5Overlay.classList.remove( 'is-open' );
        }
        v5Callback = null;
        document.removeEventListener( 'keydown', onV5Keydown );
    }

    function onV5Keydown(e) {
        if ( e.key === 'Escape' ) {
            closeV5();
        }
    }

    // Called by the widget through its "onpoint" attribute (must be global).
    window.inpostPaczkomatyOnPoint = function (point) {
        var callback = v5Callback;
        closeV5();
        if ( callback && point ) {
            callback( normalizePoint( point ) );
        }
    };

    function buildV5() {
        var overlay = document.createElement( 'div' );
        overlay.className = 'inpost-map-overlay';
        overlay.setAttribute( 'role', 'dialog' );
        overlay.setAttribute( 'aria-modal', 'true' );
        overlay.setAttribute( 'aria-label', i18n.title || '' );

        var box = document.createElement( 'div' );
        box.className = 'inpost-map-modal';

        var header = document.createElement( 'div' );
        header.className = 'inpost-map-header';

        var title = document.createElement( 'p' );
        title.className = 'inpost-map-title';
        title.textContent = i18n.title || '';

        var close = document.createElement( 'button' );
        close.type = 'button';
        close.className = 'inpost-map-close';
        close.setAttribute( 'aria-label', i18n.close || 'Close' );
        close.textContent = '×';
        close.addEventListener( 'click', closeV5 );

        var widget = document.createElement( 'inpost-geowidget' );
        widget.setAttribute( 'token', data.token || '' );
        widget.setAttribute( 'language', data.language || 'pl' );
        widget.setAttribute( 'config', 'parcelCollect' );
        widget.setAttribute( 'country', data.countries || 'PL' );
        widget.setAttribute( 'onpoint', 'inpostPaczkomatyOnPoint' );

        // Click on the dimmed backdrop closes the modal.
        overlay.addEventListener( 'click', function (e) {
            if ( e.target === overlay ) {
                closeV5();
            }
        });

        header.appendChild( title );
        header.appendChild( close );
        box.appendChild( header );
        box.appendChild( widget );
        overlay.appendChild( box );
        document.body.appendChild( overlay );

        return overlay;
    }

    function openV5(callback) {
        if ( !window.customElements || !window.customElements.get( 'inpost-geowidget' ) ) {
            console.error( 'InPost GeoWidget v5 is not loaded.' );
            return;
        }

        if ( !v5Overlay ) {
            v5Overlay = buildV5();
        }

        v5Callback = callback;
        v5Overlay.classList.add( 'is-open' );
        document.addEventListener( 'keydown', onV5Keydown );
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    if ( data.version !== 'v5' ) {
        // The v4 SDK polls for this hook after loading.
        window.easyPackAsyncInit = initV4;
    }

    window.InpostMap = {
        /**
         * Open the paczkomat picker.
         *
         * @param {Function} callback Receives the selected point (v4 shape).
         */
        open: function (callback) {
            if ( data.version === 'v5' ) {
                openV5( callback );
            } else {
                openV4( callback );
            }
        }
    };

})( window, document );
