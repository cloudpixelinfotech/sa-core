(function ($) {
	"use strict";
	jQuery(document).on('click', '#export-documents-reminder', function(e) {
		e.preventDefault();
		var ajax_data = {
			'action': 'download_documents_reminder_data',
		};
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: sa_core.ajax_url, 
			data: ajax_data,
			beforeSend: function() {
			},
			success: function(data) {
				if (data.success == 1) {
					window.location.href = data.csvurl;
					// delete file after download
					jQuery.ajax({
						type: 'POST',
						url: ajaxurl, 
						data: {'action': 'remove_file', 'file' : data.csvurl},
						success: function(data) {
						}
					});
				}
			}
		});
	});
})(this.jQuery);