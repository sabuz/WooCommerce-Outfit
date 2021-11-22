jQuery(document).ready(function() {
	// Form validator
	jQuery.validator.setDefaults({
		ignore: [],
	})

	jQuery.validator.addMethod('fileSize', function(value, element, param) {
		return this.optional(element) || (element.files[0].size <= param)
	}, woo_outfit_tr_obj.size_exceed)

	jQuery('#new-outfit-form').validate({
		rules: {
			thumb: {
				required: true,
				extension: 'jpg,jpeg,png',
				fileSize: (parseInt(woo_outfit_tr_obj.upload_limit) * 1024) * 1024,
			},
			ids: {
				required: true
			},
		},
		messages: {
			thumb: {
				required: woo_outfit_tr_obj.thumb_req,
				extension: woo_outfit_tr_obj.invalid_thumb
			},
			ids: {
				required: woo_outfit_tr_obj.ids_req
			}
		}
	})

	jQuery('#new-outfit-form #thumb').on('change', function(e) {
		jQuery(this).valid()
	})

	// Init Select2
	jQuery('.select-cat').select2({
		placeholder: woo_outfit_tr_obj.select_placeholder
	})

	jQuery('.select-tag').select2({
		width: '100%'
	})

	// Init popover
	jQuery('[data-toggle="popover"]').popover({
		animation: false,
		html: true,
		trigger: 'hover'
	})

	// Submit Outfit
	jQuery('#new-outfit-form').on('submit', function(e) {
		if (jQuery(this).valid()) {
			var form_data = new FormData(this)
			form_data.append('security', woo_outfit_tr_obj.nonce)
			jQuery('#submit', this).val('Submitting...')

			jQuery.ajax({
				type: 'POST',
				url: woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_post_outfit',
				data: form_data,
				cache: false,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.status == 'success') {
						var time = new Date()
						time.setHours(time.getHours() + 1)
						document.cookie = 'woo_outfit_success=true; expires=' + time.setHours(time.getHours() + 1) + '; path=/'
						window.location.replace(woo_outfit_tr_obj.outfits_url)
					}
				},
				error: function(response) {
					jQuery('#new-outfit-form #submit').val('Add Outfit')
				}
			})

			return false
		}
	})

	// Load Products from category
	jQuery('.select-cat').on('change', function(e) {
		e.preventDefault()

		var cat_id = jQuery(this).val()
		var html = ''
		var count = 0

		jQuery.post(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_get_products_by_cat', {
			cat: cat_id,
			page: 1,
			security: woo_outfit_tr_obj.nonce
		}).success(function(data) {
			if (data.products) {
				for (var i in data.products) {
					if (count == 0 || count % 3 == 0) {
						html += '<div class="row">'
					}

					html += '<div class="col-xs-4"><div class="item"><img src="' + data.products[i].thumb + '"/><h4 class="product-title">' + data.products[i].title + '</h4><p class="price">' + data.products[i].price_html + '</p><a class="button" data-id="' + data.products[i].id + '">Select</a></div></div>'

					if ((count != 0 && count % 2 == 0) || (count == data.length - 1)) {
						html += '</div>'
					}

					count += 1
				}


				jQuery('.product-list').empty().html(html)

				// product-nav
				jQuery('.product-nav').removeClass('hidden')
				jQuery('.product-nav .prev').data('page', 0).addClass('disabled')

				if (data.term.next) {
					jQuery('.product-nav .next').data('page', 2).removeClass('disabled')
				} else {
					jQuery('.product-nav .next').data('page', 1).addClass('disabled')
				}
			} else {
				jQuery('.product-list').empty().html('<p>Nothing found</p>')

				// product-nav
				jQuery('.product-nav').addClass('hidden')
			}
		})
	})

	// Load Products from category - Pagination
	jQuery('.product-nav').on('click', 'a', function(e) {
		e.preventDefault()

		var cat_id = jQuery('.select-cat').val()
		var page = jQuery(this).data('page')
		var count = 0
		var html = ''

		jQuery.post(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_get_products_by_cat', {
			cat: cat_id,
			page: page,
			security: woo_outfit_tr_obj.nonce
		}).done(function(data) {
			for (var i in data.products) {
				if (count == 0 || count % 3 == 0) {
					html += '<div class="row has-col">'
				}

				html += '<div class="col-xs-4"><div class="item"><img src="' + data.products[i].thumb + '"/><h4 class="product-title">' + data.products[i].title + '</h4><p class="price">' + data.products[i].price_html + '</p><a class="button" data-id="' + data.products[i].id + '">Select</a></div></div>'

				if ((count != 0 && count % 2 == 0) || (count == data.length - 1)) {
					html += '</div>'
				}

				count += 1
			}

			jQuery('.product-list').empty().html(html)

			// product-nav
			if ((parseInt(page) - 1) == 0) {
				jQuery('.product-nav .prev').data('page', 0).addClass('disabled')
			} else {
				jQuery('.product-nav .prev').data('page', (parseInt(page) - 1)).removeClass('disabled')
			}

			if (data.term.next) {
				jQuery('.product-nav .next').data('page', (parseInt(page) + 1)).removeClass('disabled')
			} else {
				jQuery('.product-nav .next').data('page', parseInt(page)).addClass('disabled')
			}
		})
	})

	// Push Product
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
				labels: 1
			})
			jQuery('#new-outfit-form #ids').val(JSON.stringify(ids))

			// add selected product
			var src = jQuery(this).parent('.item').find('img').attr('src')
			jQuery('.selected-products>.row').append('<div class="col-xs-4"><div class="item"><img src="' + src + '"/><a href="#" class="close" data-id="' + id + '"></a><a href="#" class="switch inactive" data-id="' + id + '"></a></div></div>')

			if (ids.length > 0) {
				jQuery('.selected-products').removeClass('empty')
			}

			jQuery('#new-outfit-form #ids').valid()
		}
	})

	// Pop Product
	jQuery('.selected-products').on('click', '.close', function(e) {
		e.preventDefault()

		id = jQuery(this).attr('data-id')
		jQuery(this).parents('.col-xs-4').remove()

		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return j
			}
		})

		ids.splice(index, 1)

		if (ids.length == 0) {
			jQuery('.selected-products').addClass('empty')
			jQuery('#new-outfit-form #ids').val('').valid()
		} else {
			jQuery('#new-outfit-form #ids').val(JSON.stringify(ids))
		}
	})

	// Switch Product Mode
	jQuery('.selected-products').on('click', '.switch', function(e) {
		e.preventDefault()
	})
})