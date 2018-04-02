jQuery(document).ready(function() {
	// Carousel
	jQuery('.outfit .owl-carousel').owlCarousel({
		loop: false,
		margin: 10,
		nav: true,
		items: 3
	})
})

// Outfit Modal
jQuery('.outfit').on('click', '.grid-item', function() {
	view = jQuery(this).attr('data-id')
	next = jQuery(this).parent().next().find('.item').attr('data-id')
	prev = jQuery(this).parent().prev().find('.item').attr('data-id')
	console.log(view)

	jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
		view: view,
		pagination: true,
		security: object.nonce
	}).done(function(data) {
		// jQuery('#productModal .modal-content').empty().append(jQuery(data))

		// jQuery('#productModal').modal({
		// 	backdrop: 'static'
		// })

		// jQuery("#producMtodal .products").trigger('destroy.owl.carousel')

		// setTimeout(function() {
		// 	jQuery("#productModal .products").owlCarousel({
		// 		items: 3,
		// 		margin: 20,
		// 		nav: true,
		// 		lazyLoad: true
		// 	})
		// }, 150)

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

		jQuery('#next').attr('data-id', next)
		jQuery('#prev').attr('data-id', prev)

	})
})

jQuery(document).on('click', '#prev, #next', function() {
	post_id = jQuery(this).attr('data-id')

	target = jQuery('.outfit').find('[data-id=' + post_id + ']')
	next = jQuery(target).parent().next().find('.item').attr('data-id')
	prev = jQuery(target).parent().prev().find('.item').attr('data-id')

	jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
		view: post_id,
		pagination: true,
		security: object.nonce
	}).done(function(data) {
		jQuery('#productModal .modal-content').empty().append(jQuery(data))
		jQuery("#productModal .products").trigger('destroy.owl.carousel')

		setTimeout(function() {
			jQuery("#productModal .products").owlCarousel({
				items: 3,
				margin: 20,
				nav: true,
				lazyLoad: true
			})
		}, 150)

		jQuery('#next').attr('data-id', next)
		jQuery('#prev').attr('data-id', prev)
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
				// window.location.href = data
				jQuery('#loginModal').modal({
					backdrop: 'static'
				})
			}
		})
	} else {
		window.location.href = jQuery(this).attr('href')
	}
})
