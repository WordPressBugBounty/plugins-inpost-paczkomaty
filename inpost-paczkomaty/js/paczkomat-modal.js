

jQuery(document).ready(function ($) {

    // Safely render the selected paczkomat info without using innerHTML,
    // so data coming from the InPost GeoWidget SDK can never inject markup.
    function renderSelectedPaczkomat(point) {
        var container = document.getElementById('selected-paczkomat');
        if (!container) {
            return;
        }
        container.textContent = '';
        container.appendChild(document.createTextNode('Wybrany paczkomat: '));
        container.appendChild(document.createElement('br'));
        container.appendChild(document.createTextNode(point.name || ''));
        container.appendChild(document.createElement('br'));
        container.appendChild(document.createTextNode(point.address.line1 || ''));
        container.appendChild(document.createElement('br'));
        container.appendChild(document.createTextNode(point.address.line2 || ''));
    }

    function sendPaczkomatSelection(point) {
        var data = {
            action: 'set_paczkomat',
            nonce: ajax_options.nonce,
            paczkomat_name: point.name,
            paczkomat_address1: point.address.line1,
            paczkomat_address2: point.address.line2,
            paczkomat_post_code: point.address_details.post_code,
            paczkomat_city: point.address_details.city,
            paczkomat_street: point.address_details.street,
            paczkomat_building_number: point.address_details.building_number,
            paczkomat_flat_number: point.address_details.flat_number,
        };

        $.post(ajax_options.admin_ajax_url, data, function (response) {

        });
    }

    window.easyPackAsyncInit = function () {
        easyPack.init({
            defaultLocale: 'pl',
            mapType: 'osm',
            searchType: 'osm',
            points: {
                types: ['parcel_locker']
            },
            map: {
                initialTypes: ['parcel_locker']
            }
        });

    };

    $(".select-paczkomat-button").click(function () {

        easyPack.modalMap(function (point, modal) {
            modal.closeModal();
            renderSelectedPaczkomat(point);
            if (point) {
                $(".select-paczkomat-button").text("Zmień paczkomat");
                sendPaczkomatSelection(point);
            }
        }, {width: 500, height: 600});
    });


    // used in cart when shipping method was changed
    $( document.body ).on( 'updated_cart_totals', function(){
        window.easyPackAsyncInit = function () {
            easyPack.init({
                defaultLocale: 'pl',
                mapType: 'osm',
                searchType: 'osm',
                points: {
                    types: ['parcel_locker']
                },
                map: {
                    initialTypes: ['parcel_locker']
                }
            });

        };

        $(".select-paczkomat-button").click(function () {

            easyPack.modalMap(function (point, modal) {
                modal.closeModal();
                renderSelectedPaczkomat(point);
                if (point) {
                    $(".select-paczkomat-button").text("Zmień paczkomat");
                    sendPaczkomatSelection(point);
                }
            }, {width: 500, height: 600});
        });

    });


    // used in checkout
    $( document.body ).on('updated_checkout', function(){
        window.easyPackAsyncInit = function () {
            easyPack.init({
                defaultLocale: 'pl',
                mapType: 'osm',
                searchType: 'osm',
                points: {
                    types: ['parcel_locker']
                },
                map: {
                    initialTypes: ['parcel_locker']
                }
            });

        };

        $(".select-paczkomat-button").click(function () {

            easyPack.modalMap(function (point, modal) {
                modal.closeModal();
                renderSelectedPaczkomat(point);
                if (point) {
                    console.log(point);
                    $(".select-paczkomat-button").text("Zmień paczkomat");
                    sendPaczkomatSelection(point);
                }
            }, {width: 500, height: 600});
        });

    });



});
