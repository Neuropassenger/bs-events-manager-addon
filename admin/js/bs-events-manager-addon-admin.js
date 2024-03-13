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
		$('.bs_copy_field_from_free_template_button').on('click', function() {
			const inputField = $(this).closest('td').find('input');
			const textareaField = $(this).closest('td').find('textarea');

			if (inputField === undefined) {
				textareaField.text();
			} else {
				inputField.text($('.bs_owner-pending-email-free tr input[type=text]'));
			}
		});

		$("a.bs_settings-page-navigation-link").click(function () {
			var elementClick = $(this).attr("href");
			var destination = $(elementClick).offset().top - 50;
			$('html,body').animate( {scrollTop: destination}, 500 );
			return false;
		});

		$('.bs_copy_field_from_free_template_button').on('click', function(e) {
			e.preventDefault();
			const button = $(this);
			const fieldColumn = $(button).closest('td').attr('class');
			const templateType = $(button).closest('table').attr('class').split(' ')[3];
			const fromField = $($(document).find(`table.${templateType}`)[0]).find(`td.${fieldColumn}`).children()[0];
			const toField = $((button).closest('td')).children()[0];

			if (confirm(bsAdminTranslations.confirmDeletion) == true) {
				$(toField).val($(fromField).val());
			}
		});

		$('.bs_clear_field_button').on('click', function(e) {
			e.preventDefault();
			const button = $(this);
			const toField = $((button).closest('td')).children()[0];

			if (confirm(bsAdminTranslations.confirmDeletion) == true) {
				$(toField).val('');
			}
		});
	});

})( jQuery );
