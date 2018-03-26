jQuery(document).ready(function() {
	/**
	 * Validator rules
	 *
	 * @since: 1.0.0
	 */
	jQuery('#new-outfit-form').bootstrapValidator({
		fields: {
			thumb: {
				excluded: false,
				validators: {
					notEmpty: {
						message: 'Outfit photo is required'
					},
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
	 * Init Select2
	 *
	 * @since: 1.0.0
	 */
	jQuery('.select-cat').select2({
		placeholder: 'Select a category'
	})

	jQuery('.select-tag').select2({
		width: '100%'
	})


	/**
	 * Media Upload Handle
	 *
	 * @since: 1.0.0
	 */
	var file_frame

	jQuery('#new-outfit-form').on('click', '#upload-button', function(e) {
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
			jQuery('#placeholder').val(attachment.filename)
			jQuery('#thumb').val(attachment.id)
			jQuery('#new-outfit-form').bootstrapValidator('revalidateField', 'thumb')
		})

		file_frame.open()
	})

	/**
	 * Submit Outfit
	 *
	 * @since: 1.0.0
	 */
	jQuery('#new-outfit-form').on('submit', function(e) {
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
	// jQuery('.selectId').on('change', function(e) {
	// 	e.preventDefault()
	// 	jQuery('#products').empty()

	// 	var cat_id = jQuery(this).val()

	// 	jQuery.get(object.ajaxurl + '?action=wc_outfit_get_products_by_cat', {
	// 		cat: cat_id,
	// 		security: object.nonce
	// 	}).done(function(data) {
	// 		var count = 0
	// 		var html = ''

	// 		for (var i in data) {
	// 			if (count == 0 || count % 3 == 0) {
	// 				html += '<div class="row">'
	// 			}

	// 			html += '<a class="col-sm-4 item" data-id="' + data[i].id + '"><img src="' + data[i].thumb + '"><h3>' + data[i].title + '</h3></a>'

	// 			if ((count != 0 && count % 2 == 0) || (count == data.length - 1)) {
	// 				html += '</div>'
	// 			}

	// 			count += 1
	// 		}

	// 		jQuery("#products").prepend(html).fadeTo('slow', 1)
	// 	})
	// })
	jQuery('.select-cat').on('change', function(e) {
		e.preventDefault()

		var cat_id = jQuery(this).val()
		var html = ''
		var count = 0

		jQuery.get(object.ajaxurl + '?action=wc_outfit_get_products_by_cat', {
			cat: cat_id,
			page: 1,
			security: object.nonce
		}).success(function(data) {
			if (data.products) {
				for (var i in data.products) {
					if (count == 0 || count % 3 == 0) {
						html += '<div class="row">'
					}

					html += '<div class="col-sm-4 col-xs-6"><div class="item"><img src="' + data.products[i].thumb + '"/><h4 class="product-title">' + data.products[i].title + '</h4><p class="price">' + data.products[i].price_html + '</p><a class="button" data-id="' + data.products[i].id + '">Select</a></div></div>'

					if ((count != 0 && count % 2 == 0) || (count == data.length - 1)) {
						html += '</div>'
					}

					count += 1
				}


				jQuery('.product-list').empty().html(html)

				// pagination
				jQuery('.pagination').removeClass('hidden')
				jQuery('.pagination .prev').attr('data-page', 0).addClass('disabled')

				if (data.term.next) {
					jQuery('.pagination .next').attr('data-page', 2).removeClass('disabled')
				} else {
					jQuery('.pagination .next').attr('data-page', 1).addClass('disabled')
				}
			} else {
				jQuery('.product-list').empty().html('<p>Nothing found</p>')

				// pagination
				jQuery('.pagination').addClass('hidden')
			}
		})
	})

	$('.pagination').on('click', 'a', function(e) {
		e.preventDefault()

		var cat_id = jQuery('.select-cat').val()
		var page = jQuery(this).attr('data-page')
		var count = 0
		var html = ''

		jQuery.get(object.ajaxurl + '?action=wc_outfit_get_products_by_cat', {
			cat: cat_id,
			page: page,
			security: object.nonce
		}).done(function(data) {
			for (var i in data.products) {
				if (count == 0 || count % 3 == 0) {
					html += '<div class="row has-col">'
				}

				html += '<div class="col-sm-4 col-xs-6"><div class="item"><img src="' + data.products[i].thumb + '"/><h4 class="product-title">' + data.products[i].title + '</h4><p class="price">' + data.products[i].price_html + '</p><a class="button" data-id="' + data.products[i].id + '">Select</a></div></div>'

				if ((count != 0 && count % 2 == 0) || (count == data.length - 1)) {
					html += '</div>'
				}

				count += 1
			}

			jQuery('.product-list').empty().html(html)

			// pagination
			if ((parseInt(page) - 1) == 0) {
				jQuery('.pagination .prev').attr('data-page', 0).addClass('disabled')
			} else {
				jQuery('.pagination .prev').attr('data-page', (parseInt(page) - 1)).removeClass('disabled')
			}

			if (data.term.next) {
				jQuery('.pagination .next').attr('data-page', (parseInt(page) + 1)).removeClass('disabled')
			} else {
				jQuery('.pagination .next').attr('data-page', parseInt(page)).addClass('disabled')
			}
		})
	})

	/**
	 * Push Product
	 *
	 * @since: 1.0.0
	 */
	// var ids = []
	// jQuery('#products').on('click', 'a', function(e) {
	// 	e.preventDefault()
	// 	id = jQuery(this).attr('data-id')
	// 	var index = 0
	// 	var index = jQuery.map(ids, function(i, j) {
	// 		if (i.id == id) {
	// 			return 1
	// 		}
	// 	})

	// 	if (index == 0) {
	// 		var src = jQuery(this).find('img').attr('src')
	// 		jQuery('.chosen>.row').append('<div class="item"><img src="' + src + '"/><a class="close" data-id="' + id + '"></a></div>')
	// 		ids.push({
	// 			id: parseInt(id),
	// 			labels: 1
	// 		})
	// 		jQuery('#ids').val(JSON.stringify(ids))
	// 		jQuery('#new-outfit-form').bootstrapValidator('revalidateField', 'ids')
	// 	}
	// })

	var ids = []

	jQuery('.product-list').on('click', '.button', function(e) {
		e.preventDefault()

		var id = jQuery(this).attr('data-id')
		var index = 0
		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return 1
			}
		})

		if (index == 0) {
			// push id
			ids.push({
				id: parseInt(id),
				labels: 0
			})
			jQuery('.selected-products .ids').val(JSON.stringify(ids))

			// add selected product
			var src = jQuery(this).parent('.item').find('img').attr('src')
			jQuery('.selected-products>.row').append('<div class="col-sm-4 col-xs-6"><div class="item"><img src="' + src + '"/><a href="#" class="close" data-id="' + id + '"></a><a href="#" class="switch inactive" data-id="' + id + '"></a></div></div>')

			if (ids.length > 0) {
				$('.selected-products').removeClass('empty')
			}
		}
	})

	/**
	 * Pop Product
	 *
	 * @since: 1.0.0
	 */
	// jQuery('.chosen').on('click', 'a', function() {
	// 	id = jQuery(this).attr('data-id')
	// 	jQuery(this).closest('.item').remove()

	// 	var index = jQuery.map(ids, function(i, j) {
	// 		if (i.id == id) {
	// 			return j
	// 		}
	// 	})

	// 	ids.splice(index, 1)
	// 	if (ids.length == 0) {
	// 		jQuery('#ids').val('')
	// 	} else {
	// 		jQuery('#ids').val(JSON.stringify(ids))
	// 	}

	// 	jQuery('#new-outfit-form').bootstrapValidator('revalidateField', 'ids')
	// })
	jQuery('.selected-products').on('click', '.close', function(e) {
		e.preventDefault()

		id = jQuery(this).attr('data-id')
		jQuery(this).parents('.col-sm-4').remove()

		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return j
			}
		})

		ids.splice(index, 1)
		jQuery('.selected-products .ids').val(JSON.stringify(ids))

		if (ids.length == 0) {
			$('.selected-products').addClass('empty')
		}
	})
})