// Update url
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
	var has_history = false
	var current = parseInt(jQuery('.wc-outfit-gallery-pagination .button').attr('data-current'))
	var max = parseInt(jQuery('.wc-outfit-gallery-pagination .button').attr('data-max'))

	// Pagination handler
	if (max == 0 || current == max) {
		jQuery('.wc-outfit-gallery-pagination .button').remove()
	}

	// Initialize isotope
	jQuery('.wc-outfit-gallery-content').isotope({
		itemSelector: '.wc-outfit-gallery-item',
		columnWidth: '.col-sm-4',
		percentPosition: true,
		sortBy: 'original-order'
	})

	// Outfit modal
	jQuery('.wc-outfit-gallery').on('click', '.wc-outfit-gallery-item-thumb', function(e) {
		view = jQuery(this).closest('.wc-outfit-gallery-item').attr('data-id')

		// update url
		has_history = true
		updateUrl('Style Gallery', wc_outfit_tr_obj.style_gallery_url + '?view=' + view)

		jQuery.get(wc_outfit_tr_obj.ajax_url + '?action=wc_outfit_single_outfit_modal', {
			view: view,
			security: wc_outfit_tr_obj.nonce
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
					navText: ['<span class="wc-outfit-icon wc-outfit-icon-angle-left">', '<span class="wc-outfit-icon wc-outfit-icon-angle-right">'],
					lazyLoad: true
				})
			}, 100)
		})
	})

	// Update url on close modal
	jQuery('#wc-outfit-modal').on('click', '.close', function() {
		if (has_history == true) {
			updateUrl('Style Gallery', history.back())
		} else {
			updateUrl('Style Gallery', wc_outfit_tr_obj.style_gallery_url)
		}
	})

	// Follow models
	jQuery(document).on('click', '.wc-outfit-follow-btn', function(e) {
		e.preventDefault()

		var user_id = jQuery(this).attr('data-id')

		jQuery.get(wc_outfit_tr_obj.ajax_url + '?action=wc_outfit_follow_people', {
			user_id: user_id,
			security: wc_outfit_tr_obj.nonce
		}).done(function(data) {
			jQuery('.wc-outfit-num-follower').html(data + ' Followers')
			jQuery('.wc-outfit-gallery-header .wc-outfit-follow-btn').text(jQuery('.wc-outfit-gallery-header .wc-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
			jQuery('.modal .wc-outfit-follow-btn').text(jQuery('.modal  .wc-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = wc_outfit_tr_obj.myaccount_url
			}
		})
	})

	// Post like
	jQuery(document).on('click', '.wc-outfit-rating-heart', function(e) {
		e.preventDefault()

		var target = jQuery(this)

		jQuery.post(wc_outfit_tr_obj.ajax_url + '?action=wc_outfit_post_like', {
			post_id: target.attr('data-id'),
			security: wc_outfit_tr_obj.nonce
		}).done(function(data) {
			target.toggleClass('enabled').siblings('.wc-outfit-rating-count').html(data)
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = wc_outfit_tr_obj.myaccount_url
			}
		})
	})

	// Ajax pagination
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
				security: wc_outfit_tr_obj.nonce
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

			jQuery.get(wc_outfit_tr_obj.ajax_url + '?action=wc_outfit_style_gallery', obj).done(function(data) {
				var data = jQuery(data).filter('div')

				jQuery('.wc-outfit-gallery-content .row').append(data)

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