(function( $ ) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $( window ).load(function() {
        const selectElem = $('section.em-booking-form-section-confirm select.em-payment-gateway-options');
        selectElem.prepend('<option value="unselected" disabled>- Wählen Sie eine Zahlungsmethode aus -</option>');
        // Получаем элемент формы бронирования
        const booking_form = $('form.em-booking-form[name="booking-form"]');

        // Добавляем обработчик события 'em_booking_intent_updated'
        booking_form.on("em_booking_intent_updated", function(e) {
            if (selectElem.is(":visible")) {
                // Если элемент виден
                selectElem.val('unselected');
            } else {
                // Если элемент не виден, отменить выбор
                selectElem.prop('selectedIndex', 1);
            }
        });
    });

})( jQuery );
