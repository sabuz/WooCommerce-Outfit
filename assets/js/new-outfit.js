jQuery(document).ready(function() {
	/**
	 * Validator rules
	 *
	 * @since: 1.0.0
	 */
	jQuery('#newOutfitForm').bootstrapValidator({
		fields: {
			thumb: {
				validators: {
					notEmpty: {
						message: 'Outfit photo is required'
					},
					// file: {
					// 	extension: 'jpg,jpeg,png',
					// 	type: 'image/jpeg,image/png',
					// 	message: 'Please choose a JPG/JPEG/PNG file'
					// }
				}
			},

			ids: {
				excluded: false,
				validators: {
					notEmpty: {
						message: 'Products are required'
					}
				}
			},
		}
	})

	/**
	 * Media Upload Handle
	 *
	 * @since: 1.0.0
	 */
	var file_frame

	jQuery('#frontend-button').on('click', function(e) {
		e.preventDefault()

		// if the file_frame has already been created, just reuse it
		if (file_frame) {
			file_frame.open()
			return
		}

		file_frame = wp.media.frames.file_frame = wp.media({
			title: $(this).data('uploader_title'),
			button: {
				text: jQuery(this).data('uploader_button_text'),
			},
			multiple: false
		})

		file_frame.on('select', function() {
			attachment = file_frame.state().get('selection').first().toJSON()

			// do something with the file here
			console.log(attachment)
			jQuery('#thumb').val(attachment.id)
			jQuery('#newOutfitForm').bootstrapValidator('revalidateField', 'thumb')
		})

		file_frame.open()
	})

	/**
	 * Submit Outfit
	 *
	 * @since: 1.0.0
	 */
	jQuery('#newOutfitForm').on('submit', function(e) {
		if (e.isDefaultPrevented()) {
			return
		} else {
			var formData = jQuery(this).serialize()

			jQuery.ajax({
				url: object.ajaxurl + '?action=wc_outfit_post_outfit',
				type: 'POST',
				data: {
					form_data: formData,
					security: object.nonce
				},
				success: function(response) {
					if (response.status == 'success') {
						var time = new Date()
						time.setHours(time.getHours() + 1)
						document.cookie = 'wc_outfit_success=true; expires=' + time.setHours(time.getHours() + 1) + '; path=/'
						window.location.replace(object.myaccount_url + 'outfits')
					}
				},
			})

			return false
		}
	})

	/**
	 * Load Products from category
	 *
	 * @since: 1.0.0
	 */
	jQuery('.selectId').on('change', function(e) {
		e.preventDefault()
		jQuery('#products').fadeTo("slow", 0.5)

		var cat_id = jQuery(this).val()

		jQuery.get(object.ajaxurl + '?action=wc_outfit_get_products_by_cat', {
			cat: cat_id,
			security: object.nonce
		}).done(function(data) {
			jQuery('#products').fadeTo('slow', 1)
			jQuery('#products').empty()

			for (var i in data) {
				var html = '<a class="col-sm-4 item" data-id="' + data[i].id + '"><img src="' + data[i].thumb + '"><h3>' + data[i].title + '</h3></a>'
				jQuery("#products").prepend(html)
			}
		})
	})

	/**
	 * Push Product
	 *
	 * @since: 1.0.0
	 */
	var ids = []
	jQuery('#products').on('click', 'a', function(e) {
		e.preventDefault()
		id = jQuery(this).attr('data-id')
		var index = 0
		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return 1
			}
		})

		if (index == 0) {
			var src = jQuery(this).find('img').attr('src')
			jQuery('.chosen>.row').append('<div class="item"><img src="' + src + '"/><a class="close" data-id="' + id + '"></a></div>')
			ids.push({
				id: parseInt(id),
				labels: 1
			})
			jQuery('#ids').val(JSON.stringify(ids))
			jQuery('#newOutfitForm').bootstrapValidator('revalidateField', 'ids')
		}
	})

	/**
	 * Pop Product
	 *
	 * @since: 1.0.0
	 */
	jQuery('.chosen').on('click', 'a', function() {
		id = jQuery(this).attr('data-id')
		jQuery(this).closest('.item').remove()

		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return j
			}
		})

		ids.splice(index, 1)
		if (ids.length == 0) {
			jQuery('#ids').val('')
		} else {
			jQuery('#ids').val(JSON.stringify(ids))
		}

		jQuery('#newOutfitForm').bootstrapValidator('revalidateField', 'ids')
	})
})