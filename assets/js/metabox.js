jQuery(document).ready(function() {

	/**
	 * Init Select2
	 *
	 * @since: 1.0.0
	 */
	jQuery('.select-cat').select2({
		placeholder: 'Select a category'
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
					if (count == 0 || count % 4 == 0) {
						html += '<div class="row has-col">'
					}

					html += '<div class="col"><div class="item"><img src="' + data.products[i].thumb + '"/><h4 class="product-title">' + data.products[i].title + '</h4><p class="price">' + data.products[i].price_html + '</p><a class="button" data-id="' + data.products[i].id + '">Select</a></div></div>'

					if ((count != 0 && count % 3 == 0) || (count == data.length - 1)) {
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

	/**
	 * Load Products from category - Pagination
	 *
	 * @since: 1.0.0
	 */
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
				if (count == 0 || count % 4 == 0) {
					html += '<div class="row has-col">'
				}

				html += '<div class="col"><div class="item"><img src="' + data.products[i].thumb + '"/><h4 class="product-title">' + data.products[i].title + '</h4><p class="price">' + data.products[i].price_html + '</p><a class="button" data-id="' + data.products[i].id + '">Select</a></div></div>'

				if ((count != 0 && count % 3 == 0) || (count == data.length - 1)) {
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
	var ids = jQuery('.selected-products .ids').val()
	if (ids.length > 0) {
		ids = JSON.parse(ids)
	} else {
		ids = []
	}

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
			jQuery('.selected-products>.row').append('<div class="col"><div class="item"><img src="' + src + '"/><a href="#" class="close" data-id="' + id + '"></a><a href="#" class="switch inactive" data-id="' + id + '"></a></div></div>')

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
	jQuery('.selected-products').on('click', '.close', function(e) {
		e.preventDefault()

		id = jQuery(this).attr('data-id')
		jQuery(this).parents('.col').remove()

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

	/**
	 * Switch Product Mode
	 *
	 * @since: 1.0.0
	 */
	jQuery('.selected-products').on('click', '.switch', function(e) {
		e.preventDefault()

		id = jQuery(this).attr('data-id')
		jQuery(this).toggleClass('active inactive')

		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return j
			}
		})

		ids[index].labels ^= 1
		jQuery('.selected-products .ids').val(JSON.stringify(ids))
	})
})