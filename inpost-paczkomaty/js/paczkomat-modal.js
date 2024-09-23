

jQuery(document).ready(function ($) {
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
            document.getElementById('selected-paczkomat').innerHTML = 'Wybrany paczkomat: <br>' + point.name + '<br>' + point.address.line1 + '<br>' + point.address.line2;
            if (point) {
                $(".select-paczkomat-button").text("Zmień paczkomat");
                var data = {
                    action: 'set_paczkomat',
                    paczkomat_name: point.name,
                    paczkomat_address1: point.address.line1,
                    paczkomat_address2: point.address.line2,
                    paczkomat_post_code: point.address_details.post_code,
                    paczkomat_city: point.address_details.city,
                    paczkomat_street: point.address_details.street,
                    paczkomat_building_number: point.address_details.building_number,
                    paczkomat_flat_number: point.address_details.flat_number,
                }

                $.post(ajax_options.admin_ajax_url, data, function (response) {

                });

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
                document.getElementById('selected-paczkomat').innerHTML = 'Wybrany paczkomat: <br>' + point.name + '<br>' + point.address.line1 + '<br>' + point.address.line2;
                if (point) {
                    $(".select-paczkomat-button").text("Zmień paczkomat");
                    var data = {
                        action: 'set_paczkomat',
                        paczkomat_name: point.name,
                        paczkomat_address1: point.address.line1,
                        paczkomat_address2: point.address.line2,
                        paczkomat_post_code: point.address_details.post_code,
                        paczkomat_city: point.address_details.city,
                        paczkomat_street: point.address_details.street,
                        paczkomat_building_number: point.address_details.building_number,
                        paczkomat_flat_number: point.address_details.flat_number,
                    }

                    $.post(ajax_options.admin_ajax_url, data, function (response) {

                    });

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
                document.getElementById('selected-paczkomat').innerHTML = 'Wybrany paczkomat: <br>' + point.name + '<br>' + point.address.line1 + '<br>' + point.address.line2;
                if (point) {
                    console.log(point);
                    $(".select-paczkomat-button").text("Zmień paczkomat");

                    var data = {
                        action: 'set_paczkomat',
                        paczkomat_name: point.name,
                        paczkomat_address1: point.address.line1,
                        paczkomat_address2: point.address.line2,
                        paczkomat_post_code: point.address_details.post_code,
                        paczkomat_city: point.address_details.city,
                        paczkomat_street: point.address_details.street,
                        paczkomat_building_number: point.address_details.building_number,
                        paczkomat_flat_number: point.address_details.flat_number,
                    }


                    $.post(ajax_options.admin_ajax_url, data, function (response) {

                    });

                }
            }, {width: 500, height: 600});
        });

    });



});