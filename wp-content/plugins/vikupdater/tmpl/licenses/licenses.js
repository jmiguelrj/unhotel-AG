(function($, w) {
	'use strict';

	let addingTerm = false;

	$(function() {

		/**
		 * Adds an event handler to the quick edit link.
		 *
		 * @return  void
		 */
		$(document).on('click', '#the-list .editinline', function(event) {
			let boxData = $(this).closest('td').find('.hidden[id^=inline_]');

			let data = {
				product: boxData.find('.product').text().trim(),
				license: boxData.find('.license').text().trim(),
			};

			$('#license-product').val(data.product);
			$('#license-code').val(data.license);
		});

		/**
		 * Adds an event handler to the delete licenses from the table.
		 *
		 * Cancels default event handling and event bubbling.
		 *
		 * @return  bool  Always returns false to cancel the default event handling.
		 */
		$(document).on('click', '#the-list .delete-license', function(event) {
			if (typeof showNotice === 'undefined') {
				return true;
			}

			// confirms the deletion, a negative response means the deletion must not be executed
			let response = showNotice.warn();

			if (!response) {
				event.preventDefault();
				return false;
			}

			return true;
		});

		/**
		 * Adds an event handler to the form submit on the term overview page.
		 *
		 * Cancels default event handling and event bubbling.
		 *
		 * @return  bool
		 */
		$('#submit').on('click', function(event) {
			const form = $(this).parents('form');

			let valid = true;

			if ($('#license-product').val().length == 0) {
				valid = false;

				$('#license-product').closest('.form-field').addClass('form-invalid');
			} else {
				$('#license-product').closest('.form-field').removeClass('form-invalid');
			}

			if ($('#license-code').val().length == 0) {
				valid = false;

				$('#license-code').closest('.form-field').addClass('form-invalid');
			} else {
				$('#license-code').closest('.form-field').removeClass('form-invalid');
			}

			if (!valid) {
				event.preventDefault();
				return false;
			}

			 return true;
		});

		/**
		 * Removes the invalid status from the required fields properly filled in.
		 * 
		 * @return  void
		 */
		$('#license-product, #license-code').on('change', function() {
			const formField = $(this).closest('.form-field');

			if (formField.hasClass('form-invalid') && $(this).val().length) {
				formField.removeClass('form-invalid');
			}
		});

	});

})(jQuery, window);