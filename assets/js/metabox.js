jQuery(document).ready(function() {
	// Init select2
	jQuery('.wc-outfit-mb .select-cat').select2({
		placeholder: 'Select a category'
	})

	// Select Product Category
	jQuery('.wc-outfit-mb .select-cat').on('change', function(e) {
		e.preventDefault()

		var cat_id = jQuery(this).val()
		var html = ''

		jQuery.get(object.ajaxurl + '?action=wc_outfit_get_products_by_cat', {
			cat: cat_id,
			page: 1,
			security: object.nonce
		}).success(function(data) {
			jQuery('.product-list').empty()

			for (var i in data) {
				html += '<div class="col-4" data-id="' + data[i].id + '"><img src="' + data[i].thumb + '"></div>'
			}

			jQuery(".product-list").prepend(html)

			html = '<div class="pagination"><a href="#" class="prev" data-page="1">Prev</a><a href="#" class="next" data-page="2">Next</a></div>'
			jQuery("#products").after(html)
			console.log(data)
		})
	})

	// pagination
	$(document).on('click', '.pagination a', function(e) {
		e.preventDefault()
		// jQuery('#products').fadeTo("slow", 0.5)

		var cat_id = jQuery('#select-cat').val()
		var page = jQuery(this).data('page')
		var html = ''

		jQuery.get(object.ajaxurl + '?action=wc_outfit_get_products_by_cat', {
			cat: cat_id,
			page: page,
			security: object.nonce
		}).done(function(data) {
			// jQuery('#products').fadeTo('slow', 1)
			jQuery('#products').empty()

			for (var i in data) {
				html += '<a class="col-4" data-id="' + data[i].id + '"><img src="' + data[i].thumb + '"></a>'
			}

			jQuery("#products").prepend(html)
			jQuery('.pagination .prev').attr('data-page', (parseInt(page) - 1))
			jQuery('.pagination .next').attr('data-page', (parseInt(page) + 1))
		})
	})

	// Select Product
	var ids = jQuery('#ids').val()
	if (ids.length > 0) {
		ids = JSON.parse(ids)
	} else {
		var ids = []
	}

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
			jQuery('.selected-products>.row').append('<div class="col-4"><img src="' + src + '"/><a class="close" data-id="' + id + '"></a><span class="switch inactive" data-id="' + id + '"></span></div>')
			ids.push({
				id: parseInt(id),
				labels: 0
			})
			jQuery('#ids').val(JSON.stringify(ids))

			if (ids.length > 0) {
				$('.selected-products').removeClass('empty')
			}
		}
	})

	// Product Remove
	jQuery('.selected-products').on('click', '.close', function() {
		id = jQuery(this).attr('data-id')
		jQuery(this).parent('.col-4').remove()

		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return j
			}
		})

		ids.splice(index, 1)
		jQuery('#ids').val(JSON.stringify(ids))

		if (ids.length == 0) {
			$('.selected-products').addClass('empty')
		}
	})

	// Switch
	jQuery(document).on('click', '.switch', function() {
		id = jQuery(this).attr('data-id')
		jQuery(this).toggleClass('active inactive')
		var index = jQuery.map(ids, function(i, j) {
			if (i.id == id) {
				return j
			}
		})

		ids[index].labels ^= 1
		jQuery('#ids').val(JSON.stringify(ids))
	})
})