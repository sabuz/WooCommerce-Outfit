jQuery(document).ready(function() {
	// Init Carousel
	jQuery('.wc-outfit-single-carousel .owl-carousel').owlCarousel({
		loop: false,
		margin: 10,
		items: 3,
		nav: true,
		navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">']
	})

	// Outfit Modal
	jQuery('.wc-outfit-single-carousel').on('click', '.wc-outfit-gallery-item-thumb', function() {
		view = jQuery(this).parents('.wc-outfit-gallery-item').attr('data-id')
		next = jQuery(this).parents('.owl-item').next().find('.wc-outfit-gallery-item').attr('data-id')
		prev = jQuery(this).parents('.owl-item').prev().find('.wc-outfit-gallery-item').attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: view,
			pagination: true,
			security: object.nonce
		}).done(function(data) {
			jQuery('#wc-outfit-modal .modal-content').empty().append(jQuery(data))

			jQuery('#wc-outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#wc-outfit-modal .wc-outfit-hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#wc-outfit-modal .wc-outfit-hooked-products").owlCarousel({
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
	jQuery('#wc-outfit-modal').on('click', '.outfit-prev, .outfit-next', function(e) {
		e.preventDefault()

		post_id = jQuery(this).attr('data-id')
		target = jQuery('.wc-outfit-single-carousel').find('[data-id=' + post_id + ']')
		next = jQuery(target).parent().next().find('.wc-outfit-gallery-item').attr('data-id')
		prev = jQuery(target).parent().prev().find('.wc-outfit-gallery-item').attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: post_id,
			pagination: true,
			security: object.nonce
		}).done(function(data) {
			jQuery('#wc-outfit-modal .modal-content').empty().append(jQuery(data))

			jQuery('#wc-outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#wc-outfit-modal .wc-outfit-hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#wc-outfit-modal .wc-outfit-hooked-products").owlCarousel({
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

	// Like
	jQuery(document).on('click', '.wc-outfit-rating-heart', function(e) {
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
	jQuery(document).on('click', '.wc-outfit-follow-btn', function(e) {
		e.preventDefault()

		user_id = jQuery(this).attr('data-id')

		if (jQuery.isNumeric(user_id)) {
			jQuery.get(object.ajaxurl + '?action=wc_outfit_follow_people', {
				user_id: user_id,
				security: object.nonce
			}).done(function(data) {
				if (jQuery.isNumeric(data)) {
					jQuery('.follower').html(data + ' Followers')
					jQuery('.modal .wc-outfit-follow-btn').text(jQuery('.modal .wc-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
					jQuery('.wc-outfit-follow-btn span strong').text(jQuery('.wc-outfit-follow-btn span strong').text() == 'Follow' ? 'Unfollow' : 'Follow')
				} else {
					window.location.href = data
				}
			})
		} else {
			window.location.href = jQuery(this).attr('href')
		}
	})
})

