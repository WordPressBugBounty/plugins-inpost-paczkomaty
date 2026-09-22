

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

    // Delegated handler: the button is re-rendered by WooCommerce on
    // updated_cart_totals / updated_checkout, so bind once on the body.
    $(document.body).on('click', '.select-paczkomat-button', function (e) {
        e.preventDefault();

        InpostMap.open(function (point) {
            renderSelectedPaczkomat(point);
            $(".select-paczkomat-button").text("Zmień paczkomat");
            sendPaczkomatSelection(point);
        });
    });

});
