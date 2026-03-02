// Export buttons
var $exportWithFiltersCheckbox = jQuery('#exportWithFilters');
var $exportCsvBtn = jQuery('#exportCsvBtn');
var $exportXlsxBtn = jQuery('#exportXlsxBtn');

jQuery(document).ready(function () {
	// Add new row
	jQuery(document).on('click', '.btn-action-add', handleAddRow);

	// Remove row
	jQuery(document).on('click', '.btn-action-remove', handleRemoveRow);

	// Hide notifications after 3 seconds or when clicked
	function fadeOutAndRemove(element) {
		element.fadeOut({
			duration: 500,
			complete: function () {
				jQuery(this).remove();
			},
		});
	}
	jQuery('.notification').on('click', function () {
		fadeOutAndRemove(jQuery(this));
	});
	setTimeout(function () {
		fadeOutAndRemove(jQuery('.notification:not(.sticky)'));
	}, 3000);

	// Confirm delete
	jQuery('.form-delete').on('submit', function (e) {
		if (!confirm('Are you sure you want to delete this record?')) {
			e.preventDefault();
		}
	});

	// Count up
	jQuery('.count-up').each(function () {
		var thisElem = jQuery(this).children('span[data-no]');
		var num = thisElem.data('no');
		var decimals = 2;
		if( thisElem.data('not-amount') !== undefined ) {
			decimals = 0;
		}
		// If the number is a string, remove the commas
		if (typeof num === 'string') {
			var num = parseFloat(num.replace(/,/g, ''));
		}
		// Create an intersection observer
		var observer = new IntersectionObserver(function (entries) {
			// If the element is in view, start the count up
			if (entries[0].isIntersecting) {
				var countUpOptions = {
					useEasing : true,
					separator : '.',
					decimal : ',', 
				};
				new CountUp(thisElem[0], '0', num, decimals, 1.5, countUpOptions).start();
				// Once the animation has started, we don't need the observer anymore
				observer.disconnect();
			}
		});
		// Start observing the element
		observer.observe(thisElem[0]);
	});

	// Add labels to table cells for mobile
	jQuery('.responsive-table table').each(function() {
		jQuery(this).find('tbody tr:not(:first)').each(function() {
			jQuery(this).find('td').each(function(index) {
				var thText = jQuery(this).closest('table').find('th').eq(index).text();
				if (!thText) {
					thText = 'Actions';
				}
				jQuery(this).prepend('<span class="td-label">' + thText + '</span>');
			});
		});
	});
	
	// Highlight row and show labels on mobile
	jQuery('.responsive-table table tbody tr').on('click', function() {
		if (jQuery(window).width() < 1200) {
			var $this = jQuery(this);
			if ($this.hasClass('highlight')) {
				$this.removeClass('highlight');
				$this.removeClass('toggle-labels');
			} else {
				jQuery('.responsive-table table tbody tr').removeClass('highlight');
				jQuery('.responsive-table table tbody tr').removeClass('toggle-labels');
				$this.addClass('highlight');
				if (!$this.is(':first-child')) {
					$this.addClass('toggle-labels');
				}
			}
		}
	});

	// Sortable table arrows indicator
	jQuery('.table-sort th').on('click', function() {
		var $this = jQuery(this);
		// Default sort is ascending, if you want to sort descending, add a class of 'sort-desc' to the th
		var sort = 'sort-asc';
		if ($this.hasClass('sort-asc')) {
			sort = 'sort-desc';
		}
		$this.removeClass('sort-asc sort-desc').addClass(sort);
	});

	// Tippy tooltips
	jQuery('[data-tippy-content]').each(function() {
		tippy(this, {
			content: jQuery(this).attr('data-tippy-content'),
			placement: jQuery(this).attr('data-tippy-placement') || 'top'
		});
	});

	// Select2 for multiple select fields
    jQuery('.select2-multiple').select2({
		placeholder: function(){
        	jQuery(this).data('placeholder');
		}
    });

	// Handle select2 remove all button
	setupSelect2RemoveAll();

	// Update export URLs
	updateExportUrls();

	// Event listeners for export buttons
	$exportWithFiltersCheckbox.on('change', updateExportUrls);
});

function handleAddRow() {
	var btn = jQuery(this);
	var multipleRows = btn.parents('.rows');
	var thisContent = btn.closest('.cols').clone();

	thisContent.find('input').val('');
	thisContent.appendTo(multipleRows);

	multipleRows
		.find('.btn-action-add:not(:last)')
		.addClass('btn-action-remove')
		.removeClass('btn-action-add')
		.html('<i class="las la-minus"></i>');
}

function handleRemoveRow() {
	var btn = jQuery(this);
	var multipleRows = btn.parents('.rows');

	if (multipleRows.find('.cols').length > 1) {
		btn.closest('.cols').remove();
	}
}

// Function to setup Select2 with a "Remove all selected options" button
// This function checks if the select2-multiple has any selected options and adds a button to remove them.
// It also handles the click event on the button to clear the selected options.
function setupSelect2RemoveAll() {
	function toggleRemoveAllButton($select) {
		var $container = $select.next('.select2-container');
		var $button = $container.next('.select2-remove-selected-options');
		if ($select.val() && $select.val().length > 0) {
			$button.show();
		} else {
			$button.hide();
		}
	}

	jQuery('.select2-multiple').each(function() {
		toggleRemoveAllButton(jQuery(this));
	});

	jQuery('.select2-multiple').on('select2:select select2:unselect', function() {
		toggleRemoveAllButton(jQuery(this));
	});

	jQuery(document).on('click', '.select2-remove-selected-options', function() {
		var $container = jQuery(this).prev('.select2-container');
		var $select = $container.prev('.select2-multiple');
		$select.val(null).trigger('change');
		jQuery(this).hide();
	});
}

// Update export URLs based on selected filters
function updateExportUrls() {
	var withFilters = $exportWithFiltersCheckbox.prop('checked');
	var currentParams = new URLSearchParams(window.location.search);

	currentParams.delete('page');
	currentParams.set('withFilters', withFilters);

	var baseExportUrl = jQuery('#exportBaseUrl').data('base-url');
	var csvUrl = baseExportUrl + '?' + currentParams.toString();
	$exportCsvBtn.attr('href', csvUrl);
	$exportXlsxBtn.attr('href', csvUrl + '&format=xlsx');
}