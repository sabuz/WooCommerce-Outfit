jQuery(document).ready(function() {
	// Init Carousel
	jQuery('.single-product-carousel .owl-carousel').owlCarousel({
		loop: false,
		margin: 10,
		items: 3,
		nav: true,
		navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">']
	})

	// Outfit Modal
	jQuery('.single-product-carousel').on('click', '.grid-item', function() {
		view = jQuery(this).attr('data-id')
		next = jQuery(this).parent().next().find('.grid-item').attr('data-id')
		prev = jQuery(this).parent().prev().find('.grid-item').attr('data-id')
		console.log(next)
		console.log(prev)

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: view,
			pagination: true,
			security: object.nonce
		}).done(function(data) {
			jQuery('#outfit-modal .modal-content').empty().append(jQuery(data))

			jQuery('#outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#outfit-modal .hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#outfit-modal .hooked-products").owlCarousel({
					items: 2,
					margin: 10,
					nav: true,
					navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">'],
					lazyLoad: true
				})
			}, 150)

			jQuery('.outfit-prev').attr('data-id', prev)
			jQuery('.outfit-next').attr('data-id', next)
		})
	})

	// Modal Pagination
	jQuery('#outfit-modal').on('click', '.outfit-prev, .outfit-next', function(e) {
		e.preventDefault()

		post_id = jQuery(this).attr('data-id')
		target = jQuery('.single-product-carousel').find('[data-id=' + post_id + ']')
		next = jQuery(target).parent().next().find('.grid-item').attr('data-id')
		prev = jQuery(target).parent().prev().find('.grid-item').attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: post_id,
			pagination: true,
			security: object.nonce
		}).done(function(data) {
			jQuery('#outfit-modal .modal-content').empty().append(jQuery(data))

			jQuery('#outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#outfit-modal .hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#outfit-modal .hooked-products").owlCarousel({
					items: 2,
					margin: 10,
					nav: true,
					navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">'],
					lazyLoad: true
				})
			}, 150)

			jQuery('.outfit-prev').attr('data-id', prev)
			jQuery('.outfit-next').attr('data-id', next)
		})
	})
})

// Like
jQuery(document).on('click', '.like-btn', function(e) {
	e.preventDefault()

	var pointer = jQuery(this)

	jQuery.post(object.ajaxurl + '?action=wc_outfit_post_like', {
		post_id: pointer.attr('data-id'),
		security: object.nonce
	}).done(function(response) {
		pointer.toggleClass('enabled').siblings('.count').html(response)
	}).fail(function(xhr) {
		if (xhr.status == 401) {
			window.location.href = object.myaccount_url
		}
	})
})

// Follow
jQuery(document).on('click', '.medal', function(e) {
	e.preventDefault()

	user_id = jQuery(this).attr('data-id')

	if (jQuery.isNumeric(user_id)) {
		jQuery.get(object.ajaxurl + '?action=wc_outfit_follow_people', {
			user_id: user_id,
			security: object.nonce
		}).done(function(data) {
			if (jQuery.isNumeric(data)) {
				jQuery('.follower').html(data + ' Followers')
				jQuery('.modal .medal').text(jQuery('.modal .medal').text() == 'Follow' ? 'Unfollow' : 'Follow')
				jQuery('.medal span strong').text(jQuery('.medal span strong').text() == 'Follow' ? 'Unfollow' : 'Follow')
			} else {
				window.location.href = data
			}
		})
	} else {
		window.location.href = jQuery(this).attr('href')
	}
})