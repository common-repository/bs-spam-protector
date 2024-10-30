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

	$(function() {
		// Initialization
		let upNonce = 0;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: bs_vars.ajaxUrl,
			data: {
				action: 'bs_get_validation_meta',
			},
			beforeSend: function () {

			},
			success: function(data) {
				if (data.status === 'ok') {
					upNonce = data.nonce;
					$('form.wpcf7-form').append('<input type="hidden" value="' + upNonce + '" name="bs_hf_nonce">');
					$('form.wpcf7-form').append('<input type="hidden" value="' + data.expiration + '" name="bs_hf_expiration">');
					$('form.wpcf7-form').append('<input type="hidden" value="" name="bs_hf_validation_key" class="bs_hf-validation-key">');
					$('form.wpcf7-form').append('<input type="hidden" value="" name="bs_hf_form_id" class="bs_hf-form-id">');
				} else {
					$('form.wpcf7-form').append('<input type="hidden" value="" name="bs_hf_nonce">');
					$('form.wpcf7-form').append('<input type="hidden" value="" name="bs_hf_expiration">');
					$('form.wpcf7-form').append('<input type="hidden" value="" name="bs_hf_validation_key" class="bs_hf-validation-key">');
					$('form.wpcf7-form').append('<input type="hidden" value="" name="bs_hf_form_id" class="bs_hf-form-id">');
				}
			},
			error: function(error) {
				console.log(error);
			}
		});

		// Getting the validation code
		let validationCodesSent = [];
		$('form.wpcf7-form input').on('focus', function() {
			getValidationKey(this);
		});

		$('form.wpcf7-form textarea').on('focus', function() {
			getValidationKey(this);
		});

		function getValidationKey(elemOnFocus) {
			const formId = $(elemOnFocus).closest("div.wpcf7.js").attr('id');
			$(elemOnFocus).closest('form.wpcf7-form').find('.bs_hf-form-id').val(formId);

			if (typeof validationCodesSent[formId] !== 'undefined' && validationCodesSent[formId] === true)
				return;

			const upExpiration = $(elemOnFocus).closest('form.wpcf7-form').find('[name="bs_hf_expiration"]').val();
			const upNonce = $(elemOnFocus).closest('form.wpcf7-form').find('[name="bs_hf_nonce"]').val();

			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: bs_vars.ajaxUrl,
				data: {
					action: 'bs_get_validation_key',
					nonce: upNonce,
					form_id: formId,
					expiration: upExpiration,
				},
				beforeSend: function () {
					$(elemOnFocus).closest('form.wpcf7-form').find('input.wpcf7-submit').attr('disable', 'true');
				},
				success: function (data) {
					$(elemOnFocus).closest('form.wpcf7-form').find('.bs_hf-validation-key').val(data.validationKey);
					if (data.status === 'ok') {
						validationCodesSent[formId] = true;
					} else {
						validationCodesSent[formId] = false;
					}
				},
				error: function (error) {
					console.log(error);
					validationCodesSent[formId] = false;
				},
				complete: function() {
					$(elemOnFocus).closest('form.wpcf7-form').find('input.wpcf7-submit').attr('disable', 'false');
				}
			});
		}
	});

})( jQuery );
