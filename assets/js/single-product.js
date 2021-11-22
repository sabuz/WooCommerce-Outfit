jQuery(document).ready(function() {
	// Init outfit carousel
	jQuery('.woo-outfit-single-carousel .owl-carousel').owlCarousel({
		loop: false,
		margin: 10,
		items: woo_outfit_tr_obj.num_items,
		nav: true,
		navText: ['<span class="woo-outfit-icon woo-outfit-icon-angle-left">', '<span class="woo-outfit-icon woo-outfit-icon-angle-right">']
	})

	// Outfit modal
	jQuery('.woo-outfit-single-carousel').on('click', '.woo-outfit-gallery-item-thumb', function() {
		var view = jQuery(this).parents('.woo-outfit-gallery-item').attr('data-id')
		var next = jQuery(this).parents('.owl-item').next().find('.woo-outfit-gallery-item').attr('data-id')
		var prev = jQuery(this).parents('.owl-item').prev().find('.woo-outfit-gallery-item').attr('data-id')

		jQuery.get(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_single_outfit_modal', {
			view: view,
			pagination: true,
			security: woo_outfit_tr_obj.nonce
		}).done(function(data) {
			jQuery('#woo-outfit-modal .modal-content').empty().html(jQuery(data))

			jQuery('#woo-outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").owlCarousel({
					items: 2,
					margin: 10,
					nav: true,
					navText: ['<span class="woo-outfit-icon woo-outfit-icon-angle-left">', '<span class="woo-outfit-icon woo-outfit-icon-angle-right">'],
					lazyLoad: true
				})
			}, 100)

			jQuery('.outfit-prev').attr('data-id', prev)
			jQuery('.outfit-next').attr('data-id', next)
		})
	})

	// Modal pagination
	jQuery('#woo-outfit-modal').on('click', '.outfit-prev, .outfit-next', function(e) {
		e.preventDefault()

		var post_id = jQuery(this).attr('data-id')

		if (post_id.length > 0) {
			var target = jQuery('.woo-outfit-single-carousel').find('[data-id=' + post_id + ']')
			var next = jQuery(target).parent().next().find('.woo-outfit-gallery-item').attr('data-id')
			var prev = jQuery(target).parent().prev().find('.woo-outfit-gallery-item').attr('data-id')

			jQuery.get(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_single_outfit_modal', {
				view: post_id,
				pagination: true,
				security: woo_outfit_tr_obj.nonce
			}).done(function(data) {
				jQuery('#woo-outfit-modal .modal-content').empty().html(jQuery(data))

				jQuery('#woo-outfit-modal').modal({
					backdrop: 'static'
				})

				jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").trigger('destroy.owl.carousel')

				setTimeout(function() {
					jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").owlCarousel({
						items: 2,
						margin: 10,
						nav: true,
						navText: ['<span class="woo-outfit-icon woo-outfit-icon-angle-left">', '<span class="woo-outfit-icon woo-outfit-icon-angle-right">'],
						lazyLoad: true
					})
				}, 100)

				jQuery('.outfit-prev').attr('data-id', prev)
				jQuery('.outfit-next').attr('data-id', next)
			})
		}
	})

	// Post like
	jQuery(document).on('click', '.woo-outfit-rating-heart', function(e) {
		e.preventDefault()

		var target = jQuery(this)

		jQuery.post(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_post_like', {
			post_id: target.attr('data-id'),
			security: woo_outfit_tr_obj.nonce
		}).done(function(response) {
			target.toggleClass('enabled').siblings('.woo-outfit-rating-count').html(response)
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = woo_outfit_tr_obj.myaccount_url
			}
		})
	})

	// Follow models
	jQuery(document).on('click', '.woo-outfit-follow-btn', function(e) {
		e.preventDefault()

		var user_id = jQuery(this).attr('data-id')

		jQuery.post(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_follow_people', {
			user_id: user_id,
			security: woo_outfit_tr_obj.nonce
		}).done(function(data) {
			jQuery('.woo-outfit-follow-btn').text(jQuery('.woo-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = woo_outfit_tr_obj.myaccount_url
			}
		})
	})
})