(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
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

    $(window).load(function () {
        let isAllTemplatesButtonActivated = false;
        const generateAllTemplatesButton = `<button class="button bs_generate-all-email-templates-button">${bsEEVars.generateAllTemplatesButton}</button>`;
        if ($('select[name=custom_attendee_form]').length == 0) {
            $('select[name=custom_booking_form]').closest('div').append(generateAllTemplatesButton);
        } else {
            $('select[name=custom_attendee_form]').closest('div').append(generateAllTemplatesButton);
        }

        const generateSingleTemplateButton = `<button class="button bs_generate-single-email-template-button">${bsEEVars.generateSingleTemplateButton}</button>`;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxUrl,
            data: {
                action: 'get_em_payment_gateways',
                security: bsEEVars.getPaymentMethodNonce
            },
            success: function(data) {
                data.forEach(element => {
                    const formWrapper = $('#em_custom_email-' + element);

                    let gatewayPrefix = '';
                    if ( element !== 'default' ) {
                        gatewayPrefix = element + '-';
                    }

                    let afterTarget;

                    // Owner
                    afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}admin][0][status]']`).closest('div').children('div > strong')
                    $(generateSingleTemplateButton).insertAfter(afterTarget);

                    afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}admin][1][status]']`).closest('div').children('div > strong')
                    $(generateSingleTemplateButton).insertAfter(afterTarget);

                    afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}admin][3][status]']`).closest('div').children('div > strong')
                    $(generateSingleTemplateButton).insertAfter(afterTarget);

                    // Awaiting Payment for the offline or direct deposit gateways
                    if (element === 'offline' || element === 'direct_deposit') {
                        afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}admin][5][status]']`).closest('div').children('div > strong')
                        $(generateSingleTemplateButton).insertAfter(afterTarget);
                    }

                    // Attendee
                    afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}user][0][status]']`).closest('div').children('div > strong')
                    $(generateSingleTemplateButton).insertAfter(afterTarget);

                    afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}user][1][status]']`).closest('div').children('div > strong')
                    $(generateSingleTemplateButton).insertAfter(afterTarget);

                    afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}user][3][status]']`).closest('div').children('div > strong')
                    $(generateSingleTemplateButton).insertAfter(afterTarget);

                    // Awaiting Payment for the offline or direct deposit gateways
                    if (element === 'offline' || element === 'direct_deposit') {
                        afterTarget = formWrapper.find(`select[name='em_custom_email[${gatewayPrefix}user][5][status]']`).closest('div').children('div > strong')
                        $(generateSingleTemplateButton).insertAfter(afterTarget);
                    }
                });

                $('.bs_generate-single-email-template-button').on('click', function(e) {
                    e.preventDefault();

                    if (isAllTemplatesButtonActivated === false) {
                        if (alert(bsEEVars.generateSingleTemplateButtonAlert) == false)
                            return;
                    }

                    const templateButton = $(this);
                    const fieldNameAttr = $(this).closest('div').children('select').attr('name');
                    const fieldParams = fieldNameAttr.split(/\[|]/g);
                    const targetFieldSubject = $(this).closest('div').find(`input[name='${fieldParams[0]}[${fieldParams[1]}][${fieldParams[3]}][subject]']`);
                    const targetFieldMessage = $(this).closest('div').find(`textarea[name='${fieldParams[0]}[${fieldParams[1]}][${fieldParams[3]}][message]']`);
                    const gatewayUserIndexes = fieldParams[1].split('-');

                    let data = {
                        action: 'generate_single_email_template',
                        templateType: fieldParams[3],
                        bookingFormId: $('select[name=custom_booking_form]').val(),
                        attendeeFormId: $('select[name=custom_attendee_form]').val(),
                        locationType: $('select.em-location-types-select[name=location_type]').val(),
                        security: bsEEVars.generateEmailTemplateNonce
                    };

                    if (gatewayUserIndexes.length > 1) {
                        data = {
                            receiver: gatewayUserIndexes[1],
                            gateway: gatewayUserIndexes[0],
                            ...data
                        };
                    } else {
                        data = {
                            receiver: gatewayUserIndexes[0],
                            gateway: 'default',
                            ...data
                        };
                    }

                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: ajaxUrl,
                        data,
                        success: function (response) {
                            targetFieldSubject.val(response.generatedSubject);
                            targetFieldMessage.val(response.generatedMessage);

                            if (response.result == 'ok') {
                                $(templateButton).closest('div').children('select').val('1');
                                $(templateButton).closest('div').children('select').css('color', 'green');
                                $(templateButton).closest('div').children('div.emp-cet-vals').show();
                            }
                        },
                        error: function (error) {
                            console.log({error});
                        }
                    });
                });
            },
            error: function (error) {
                console.log({error});
            }
        });

        $('button.bs_generate-all-email-templates-button').on('click', function(e) {
            e.preventDefault();

            if (alert(bsEEVars.generateAllTemplatesButtonAlert) == false)
                return;

            isAllTemplatesButtonActivated = true;
            $('button.bs_generate-single-email-template-button').click();
            isAllTemplatesButtonActivated = false;
        });
    });

})( jQuery );
