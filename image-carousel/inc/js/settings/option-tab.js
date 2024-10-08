var icploader, cachestatus;

jQuery(document).ready(function ($) {

	var ytIsLoaded = false;

	// Animate Settings Page on Load
	$(".panelloader").removeClass("ploader").hide();
	$("#page-settings").fadeIn(1000);

	// Apply WP ColorPicker
	$('#icp_gallery_overlay_color').wpColorPicker();

	// Checkbox event
	$('input[type=checkbox]').change(function () {

		if ($(this).is(":checked")) {

			$(this).val('active');
			$('.' + $(this).attr('id')).val('active');

			return;

		} else {

			$(this).val('off');
			$('.' + $(this).attr('id')).val('off');

			return;

		}

	});


	// Submit handler
	$(".icp_form_submit").bind("click", function (e) {

		e.preventDefault();

		$('.icp_save_status').hide();

		var formData = $('#' + $(this).data('formname')).serializeArray();

		icp_form_save(formData, $(this).data('formname'), $(this).data('nonce'), $(this));

	});


	// SAVE DATA AJAX	
	function icp_form_save(fdata, fid, nnc, smbt) {

		$('.set_' + fid).css('color', '#148919');
		$('.set_' + fid).hide();

		window.clearTimeout(icploader);

		$(smbt).attr('disabled', 'disabled');
		$('#loader_' + fid).addClass('button_loading');

		loading(fid);

		dat = {};
		dat['action'] = 'icp_ajax_save_settings';
		dat['fieldsdata'] = fdata;
		dat['security'] = nnc;

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: dat,

			success: function (response) {

				switch (response.ok) {

					case true:

						icp_cleanup(smbt, fid);

						break;

					default:

						alert('Failed! Please refresh the page and try again.');
						icp_cleanup(smbt, fid);

				}

				// end success-		
			}

			// end ajax
		});


	}

	// Restore element to default
	function icp_cleanup(smbt, fid) {

		$('#overlay').remove();
		$(smbt).removeAttr('disabled');
		$('#loader_' + fid).removeClass('button_loading');
		$('.set_' + fid).fadeIn(500);

		icploader = window.setTimeout(function () {

			$('.set_' + fid).fadeOut();

		}, 4000);
	};


	// Create animate on Save
	function loading(fid) {
		// add the overlay with loading image to the page
		var over = '<div id="overlay">' +
			'<img id="loading">' +
			'</div>';
		$(over).appendTo('#' + fid);
	};


	// Setting page tab items 
	$(".icp_tab_items").bind("click", function (e) {

		var tabcon = $(this).find('a').attr('href');

		$(tabcon).hide().fadeIn(500);

	});


	$('body').on('click', '.icp_free_plugins', function (event) {

		$(".icploader").removeClass("icploaderclass");
		$('#icp_free_plugins_container').empty().hide();
		$(".icploader").fadeIn(300).addClass("icploaderclass");
		icp_get_wp_free_plugins($(this));

		event.preventDefault();

	});

	$('body').on('click', '#icp-docs', function (event) {

		if (!ytIsLoaded) {
			$(".ty-iframe").attr('src', function (index, attr) {
				return attr;
			});
		}

		ytIsLoaded = true;
		event.preventDefault();

	});

	// Purge Cache
	$('#icp_purge_cache').on('click', function (e) {

		window.clearTimeout(cachestatus);

		if ($(this).is('[disabled=disabled]') == true)

			return false;

		e.preventDefault();

		$(this).attr('disabled', 'disabled');
		$('#loader_icp_purge').addClass('button_loading');
		$('#cache_act_status').hide();

		icp_clear_cache(jQuery(this));

	});


	// Purge Cache AJAX
	function icp_clear_cache(elmt) {

		dat = {};
		dat['action'] = 'icp_clear_cache_ajax';
		dat['security'] = elmt.data('nonce');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: dat,

			success: function (response) {

				switch (response.ok) {

					case true:

						$('#loader_icp_purge').removeClass('button_loading');
						$('#cache_act_status').fadeIn(1000);
						$('#cache_total, #cache_size').text('none');

						cachestatus = window.setTimeout(function () {

							$('#cache_act_status').fadeOut();

						}, 4000);


						break;

					default:

						alert('Cannot Complete Your Request, please refresh the page and try again.');
						elmt.removeAttr('disabled');
						$('#loader_icp_purge').removeClass('button_loading');
						$('#cache_act_status').hide();


				}

				// end success-		
			}

			// end ajax
		});

	}


	// RETRIEVE FREE PLUGINS LIST
	function icp_get_wp_free_plugins(elmnt) {

		dat = {};
		dat['action'] = 'icp_free_plugins_page';
		dat['security'] = elmnt.data('nonce');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: dat,

			success: function (response) {


				if (response) {

					$(".icploader").removeClass("icploaderclass").hide();
					$('#icp_free_plugins_container').html(response).fadeIn(1000);

				} else {

					alert('Failed! Please refresh the page and try again.');

				}

			}

			// end ajax
		});

	}

});

// Option tabs
jQuery(function ($) {
	$("#option-tree-settings-api").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
	$("#option-tree-settings-api li").removeClass("ui-corner-top").addClass("ui-corner-left");
});