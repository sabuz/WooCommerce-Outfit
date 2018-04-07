jQuery(document).ready(function() {
	/**
	 * Init outfit carousel
	 *
	 * @since: 1.0.0
	 */
	jQuery('.wc-outfit-single-carousel .owl-carousel').owlCarousel({
		loop: false,
		margin: 10,
		items: 3,
		nav: true,
		navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">']
	})

	/**
	 * Outfit modal
	 *
	 * @since: 1.0.0
	 */
	jQuery('.wc-outfit-single-carousel').on('click', '.wc-outfit-gallery-item-thumb', function() {
		var view = jQuery(this).parents('.wc-outfit-gallery-item').attr('data-id')
		var next = jQuery(this).parents('.owl-item').next().find('.wc-outfit-gallery-item').attr('data-id')
		var prev = jQuery(this).parents('.owl-item').prev().find('.wc-outfit-gallery-item').attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: view,
			pagination: true,
			security: object.nonce
		}).done(function(data) {
			jQuery('#wc-outfit-modal .modal-content').empty().html(jQuery(data))

			jQuery('#wc-outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#wc-outfit-modal .wc-outfit-modal-hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#wc-outfit-modal .wc-outfit-modal-hooked-products").owlCarousel({
					items: 2,
					margin: 10,
					nav: true,
					navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">'],
					lazyLoad: true
				})
			}, 100)

			jQuery('.outfit-prev').attr('data-id', prev)
			jQuery('.outfit-next').attr('data-id', next)
		})
	})

	/**
	 * Modal pagination
	 *
	 * @since: 1.0.0
	 */
	jQuery('#wc-outfit-modal').on('click', '.outfit-prev, .outfit-next', function(e) {
		e.preventDefault()

		var post_id = jQuery(this).attr('data-id')
		var target = jQuery('.wc-outfit-single-carousel').find('[data-id=' + post_id + ']')
		var next = jQuery(target).parent().next().find('.wc-outfit-gallery-item').attr('data-id')
		var prev = jQuery(target).parent().prev().find('.wc-outfit-gallery-item').attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: post_id,
			pagination: true,
			security: object.nonce
		}).done(function(data) {
			jQuery('#wc-outfit-modal .modal-content').empty().html(jQuery(data))

			jQuery('#wc-outfit-modal').modal({
				backdrop: 'static'
			})

			jQuery("#wc-outfit-modal .wc-outfit-modal-hooked-products").trigger('destroy.owl.carousel')

			setTimeout(function() {
				jQuery("#wc-outfit-modal .wc-outfit-modal-hooked-products").owlCarousel({
					items: 2,
					margin: 10,
					nav: true,
					navText: ['<span class="fa fa-angle-left">', '<span class="fa fa-angle-right">'],
					lazyLoad: true
				})
			}, 100)

			jQuery('.outfit-prev').attr('data-id', prev)
			jQuery('.outfit-next').attr('data-id', next)
		})
	})

	/**
	 * Post like
	 *
	 * @since: 1.0.0
	 */
	jQuery(document).on('click', '.wc-outfit-rating-heart', function(e) {
		e.preventDefault()

		var target = jQuery(this)

		jQuery.post(object.ajaxurl + '?action=wc_outfit_post_like', {
			post_id: target.attr('data-id'),
			security: object.nonce
		}).done(function(response) {
			target.toggleClass('enabled').siblings('.wc-outfit-rating-count').html(response)
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = object.myaccount_url
			}
		})
	})

	/**
	 * Follow models
	 *
	 * @since: 1.0.0
	 */
	jQuery(document).on('click', '.wc-outfit-follow-btn', function(e) {
		e.preventDefault()

		var user_id = jQuery(this).attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_follow_people', {
			user_id: user_id,
			security: object.nonce
		}).done(function(data) {
			jQuery('.wc-outfit-follow-btn').text(jQuery('.wc-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = object.myaccount_url
			}
		})
	})
})