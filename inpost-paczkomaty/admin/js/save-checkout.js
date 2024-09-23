jQuery(document).ready(function ($) {

    $('#shortcode_cart_checkout').on('click', function (e) {
        e.preventDefault();
        if (confirm(custom_ajax_object.message)) {
            $.ajax({
                type: 'POST',
                url: custom_ajax_object.ajax_url,
                data: {
                    action: 'save_shortcode_cart_checkout_ajax',
                    // Dodaj tutaj dodatkowe dane, jeśli są potrzebne
                },
                success: function (response) {
                    console.log('AJAX request successful');
                    // Obsługa odpowiedzi z serwera
                },
                error: function (errorThrown) {
                    console.log('AJAX request error: ' + errorThrown);
                    // Obsługa błędu zapytania AJAX
                }
            });
        } else {
            return false;
        }

    });
});