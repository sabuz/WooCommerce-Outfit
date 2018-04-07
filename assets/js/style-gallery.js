// History
function updateUrl(page, url) {
	if (typeof(history.pushState) != "undefined") {
		var obj = {
			Page: page,
			Url: url
		}
		history.pushState(obj, obj.Page, obj.Url)
	} else {
		alert("Browser does not support HTML5.")
	}
}

jQuery(document).ready(function() {
	var hasHistory = false
	var current = parseInt(jQuery('.wc-outfit-gallery-pagination .button').attr('data-current'))
	var max = parseInt(jQuery('.wc-outfit-gallery-pagination .button').attr('data-max'))

	/**
	 * Pagination handler
	 *
	 * @since: 1.0.0
	 */
	if (max == 0 || current == max) {
		jQuery('.wc-outfit-gallery-pagination .button').remove()
	}

	/**
	 * Initialize isotope
	 *
	 * @since: 1.0.0
	 */
	jQuery('.wc-outfit-gallery-content').isotope({
		itemSelector: '.wc-outfit-gallery-item',
		columnWidth: '.col-sm-4',
		percentPosition: true,
		sortBy: 'original-order'
	})

	/**
	 * Outfit modal
	 *
	 * @since: 1.0.0
	 */
	jQuery('.wc-outfit-gallery').on('click', '.wc-outfit-gallery-item-thumb', function(e) {
		view = jQuery(this).closest('.wc-outfit-gallery-item').attr('data-id')

		// update url
		hasHistory = true
		updateUrl('Style Gallery', object.style_gallery_url + '?view=' + view)

		jQuery.get(object.ajax_url + '?action=wc_outfit_single_outfit_modal', {
			view: view,
			security: object.nonce
		}).done(function(data) {
			jQuery('#wc-outfit-modal .modal-content').empty().append(jQuery(data))

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
		})
	})

	/**
	 * Update url on close modal
	 *
	 * @since: 1.0.0
	 */
	jQuery('#wc-outfit-modal').on('click', '.close', function() {
		if (hasHistory == true) {
			updateUrl('Style Gallery', history.back())
		} else {
			updateUrl('Style Gallery', object.style_gallery_url)
		}
	})

	/**
	 * Follow models
	 *
	 * @since: 1.0.0
	 */
	jQuery(document).on('click', '.wc-outfit-follow-btn', function(e) {
		e.preventDefault()

		var user_id = jQuery(this).attr('data-id')

		jQuery.get(object.ajax_url + '?action=wc_outfit_follow_people', {
			user_id: user_id,
			security: object.nonce
		}).done(function(data) {
			jQuery('.wc-outfit-num-follower').html(data + ' Followers')
			jQuery('.wc-outfit-gallery-header .wc-outfit-follow-btn').text(jQuery('.wc-outfit-gallery-header .wc-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
			jQuery('.modal .wc-outfit-follow-btn').text(jQuery('.modal  .wc-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = object.myaccount_url
			}
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

		jQuery.post(object.ajax_url + '?action=wc_outfit_post_like', {
			post_id: target.attr('data-id'),
			security: object.nonce
		}).done(function(data) {
			target.toggleClass('enabled').siblings('.wc-outfit-rating-count').html(data)
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = object.myaccount_url
			}
		})
	})

	/**
	 * Ajax pagination
	 *
	 * @since: 1.0.0
	 */
	jQuery('.wc-outfit-gallery-pagination').on('click', '.button', function() {
		var target = jQuery(this)
		var current = jQuery(this).attr('data-current')
		var max = jQuery(this).attr('data-max')
		var user = jQuery(this).attr('data-user')
		var page = jQuery(this).attr('data-page')
		var tag = jQuery(this).attr('data-tag')

		target.addClass('loading')

		if (current <= max) {
			current = parseInt(current) + 1

			var obj = {
				paged: current,
				security: object.nonce
			}

			if (typeof user !== typeof undefined && user !== false) {
				obj.user = parseInt(user)
			}

			if (typeof page !== typeof undefined && page !== false) {
				obj.page = page
			}

			if (typeof tag !== typeof undefined && tag !== false) {
				obj.tag = tag
			}

			jQuery.get(object.ajax_url + '?action=wc_outfit_style_gallery', obj).done(function(data) {
				var data = jQuery(data).filter('div')

				jQuery('.wc-outfit-gallery-content').append(data)

				jQuery('.wc-outfit-gallery-content').imagesLoaded(function() {
					jQuery('.wc-outfit-gallery-content').isotope('appended', data).isotope('layout')
				})

				target.removeClass('loading')

				if (current != max) {
					jQuery('.wc-outfit-gallery-pagination .button').attr('data-current', current)
				} else {
					jQuery('.wc-outfit-gallery-pagination .button').remove()
				}
			})
		} else {
			jQuery('.wc-outfit-gallery-pagination .button').remove()
		}
	})
})