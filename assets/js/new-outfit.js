jQuery(document).ready(function() {
	var _URL = window.URL || window.webkitURL

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
					file: {
						extension: 'jpg,jpeg,png',
						type: 'image/jpeg,image/png',
						message: 'Choose a valid JPG/JPEG/PNG file'
					},
					callback: {
						message: 'Wrong answer',
						callback: function(value, validator, $field) {
							image = new Image()
							image.src = _URL.createObjectURL(document.getElementById("thumb").files[0])
							
							image.onload = function() {
								if (this.width < 767 || this.height < 500) {
									console.log('small')
								}
							}

							return false
						}
					}
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
	 * Submit Outfit
	 *
	 * @since: 1.0.0
	 */
	jQuery('#new-outfit-form').on('submit', function(e) {
		if (e.isDefaultPrevented()) {
			return
		} else {
			var form_data = new FormData(this)
			form_data.append('security', object.nonce)

			jQuery.ajax({
				type: 'POST',
				url: object.ajaxurl + '?action=wc_outfit_post_outfit',
				data: form_data,
				cache: false,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.status == 'success') {
						var time = new Date()
						time.setHours(time.getHours() + 1)
						document.cookie = 'wc_outfit_success=true; expires=' + time.setHours(time.getHours() + 1) + '; path=/'
						window.location.replace(object.myaccount_url + 'outfits')
					}
				}
			});

			return false;
		}
	})

	/**
	 * Load Products from category
	 *
	 * @since: 1.0.0
	 */
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

				// product-nav
				jQuery('.product-nav').removeClass('hidden')
				jQuery('.product-nav .prev').attr('data-page', 0).addClass('disabled')

				if (data.term.next) {
					jQuery('.product-nav .next').attr('data-page', 2).removeClass('disabled')
				} else {
					jQuery('.product-nav .next').attr('data-page', 1).addClass('disabled')
				}
			} else {
				jQuery('.product-list').empty().html('<p>Nothing found</p>')

				// product-nav
				jQuery('.product-nav').addClass('hidden')
			}
		})
	})

	/**
	 * Load Products from category - Pagination
	 *
	 * @since: 1.0.0
	 */
	jQuery('.product-nav').on('click', 'a', function(e) {
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

			// product-nav
			if ((parseInt(page) - 1) == 0) {
				jQuery('.product-nav .prev').attr('data-page', 0).addClass('disabled')
			} else {
				jQuery('.product-nav .prev').attr('data-page', (parseInt(page) - 1)).removeClass('disabled')
			}

			if (data.term.next) {
				jQuery('.product-nav .next').attr('data-page', (parseInt(page) + 1)).removeClass('disabled')
			} else {
				jQuery('.product-nav .next').attr('data-page', parseInt(page)).addClass('disabled')
			}
		})
	})

	/**
	 * Push Product
	 *
	 * @since: 1.0.0
	 */
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
			jQuery('.selected-products .ids').val(JSON.stringify(ids))

			// add selected product
			var src = jQuery(this).parent('.item').find('img').attr('src')
			jQuery('.selected-products>.row').append('<div class="col-sm-4 col-xs-6"><div class="item"><img src="' + src + '"/><a href="#" class="close" data-id="' + id + '"></a><a href="#" class="switch inactive" data-id="' + id + '"></a></div></div>')

			if (ids.length > 0) {
				jQuery('.selected-products').removeClass('empty')
			}

			jQuery('#new-outfit-form').bootstrapValidator('revalidateField', 'ids')
		}
	})

	/**
	 * Pop Product
	 *
	 * @since: 1.0.0
	 */
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

		if (ids.length == 0) {
			jQuery('#ids').val('')
			jQuery('.selected-products').addClass('empty')
		} else {
			jQuery('#ids').val(JSON.stringify(ids))
		}

		jQuery('#new-outfit-form').bootstrapValidator('revalidateField', 'ids')
	})
})