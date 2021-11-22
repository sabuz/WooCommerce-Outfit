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

jQuery(window).on('load', function() {
	// Initialize isotope
	jQuery('.woo-outfit-gallery-content .row').isotope({
		itemSelector: '.woo-outfit-gallery-item',
		columnWidth: '.col-sm-4',
		percentPosition: true,
		sortBy: 'original-order'
	})
})

jQuery(window).on('resize', function() {
	jQuery('.woo-outfit-gallery-item-thumb-wrap').each(function(e) {
		jQuery(this).css('height', (jQuery(this).outerWidth() / jQuery(this).data('width')) * jQuery(this).data('height'))
	})
})

jQuery(document).ready(function() {
	var has_history = false
	var current = parseInt(jQuery('.woo-outfit-gallery-pagination .button').data('current'))
	var max = parseInt(jQuery('.woo-outfit-gallery-pagination .button').data('max'))

	// Pagination handler
	if (max == 0 || current == max) {
		jQuery('.woo-outfit-gallery-pagination .button').remove()
	}

	// Outfit modal
	jQuery('.woo-outfit-gallery').on('click', '.woo-outfit-gallery-item-thumb', function(e) {
		view = jQuery(this).closest('.woo-outfit-gallery-item').data('id')

		// update url
		has_history = true
		updateUrl('Style Gallery', woo_outfit_tr_obj.style_gallery_url + '?view=' + view)

		jQuery.get(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_single_outfit_modal', {
			view: view,
			pagination: false,
			security: woo_outfit_tr_obj.nonce
		}).done(function(data) {
			jQuery('#woo-outfit-modal .modal-content').empty().append(jQuery(data))

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
		})
	})

	// Modal pagination
	jQuery('#woo-outfit-modal').on('click', '.outfit-prev, .outfit-next', function (e) {
		e.preventDefault()

		var post_id = jQuery(this).data('id')

		if (post_id.length > 0) {
			var target = jQuery('.woo-outfit-single-carousel').find('[data-id=' + post_id + ']')
			var next = jQuery(target).parent().next().find('.woo-outfit-gallery-item').data('id')
			var prev = jQuery(target).parent().prev().find('.woo-outfit-gallery-item').data('id')

			jQuery.get(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_single_outfit_modal', {
				view: post_id,
				pagination: true,
				security: woo_outfit_tr_obj.nonce
			}).done(function (data) {
				jQuery('#woo-outfit-modal .modal-content').empty().html(jQuery(data))

				jQuery('#woo-outfit-modal').modal({
					backdrop: 'static'
				})

				jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").trigger('destroy.owl.carousel')

				setTimeout(function () {
					jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").owlCarousel({
						items: 2,
						margin: 10,
						nav: true,
						navText: ['<span class="woo-outfit-icon woo-outfit-icon-angle-left">', '<span class="woo-outfit-icon woo-outfit-icon-angle-right">'],
						lazyLoad: true
					})
				}, 100)

				jQuery('.outfit-prev').data('id', prev)
				jQuery('.outfit-next').data('id', next)
			})
		}
	})

	// Update url on close modal
	jQuery('#woo-outfit-modal').on('click', '.close', function() {
		if (has_history == true) {
			updateUrl('Style Gallery', history.back())
		} else {
			updateUrl('Style Gallery', woo_outfit_tr_obj.style_gallery_url)
		}
	})

	// Follow models
	jQuery(document).on('click', '.woo-outfit-follow-btn', function(e) {
		e.preventDefault()

		var user_id = jQuery(this).data('id')

		jQuery.post(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_follow_people', {
			user_id: user_id,
			security: woo_outfit_tr_obj.nonce
		}).done(function(data) {
			jQuery('.woo-outfit-num-follower').html(data + ' Followers')
			jQuery('.woo-outfit-gallery-header .woo-outfit-follow-btn').text(jQuery('.woo-outfit-gallery-header .woo-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
			jQuery('.modal .woo-outfit-follow-btn').text(jQuery('.modal  .woo-outfit-follow-btn').text() == 'Follow' ? 'Unfollow' : 'Follow')
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = woo_outfit_tr_obj.myaccount_url
			}
		})
	})

	// Post like
	jQuery(document).on('click', '.woo-outfit-rating-heart', function(e) {
		e.preventDefault()

		var target = jQuery(this)

		jQuery.post(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_post_like', {
			post_id: target.data('id'),
			security: woo_outfit_tr_obj.nonce
		}).done(function(data) {
			target.toggleClass('enabled').siblings('.woo-outfit-rating-count').html(data)
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = woo_outfit_tr_obj.myaccount_url
			}
		})
	})

	// Ajax pagination
	jQuery('.woo-outfit-gallery-pagination').on('click', '.button', function() {
		var target = jQuery(this)
		var current = jQuery(this).data('current')
		var max = jQuery(this).data('max')
		var user = jQuery(this).data('user')
		var page = jQuery(this).data('page')
		var tag = jQuery(this).data('tag')

		target.addClass('loading')

		if (current <= max) {
			current = parseInt(current) + 1

			var obj = {
				paged: current,
				security: woo_outfit_tr_obj.nonce
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

			jQuery.get(woo_outfit_tr_obj.ajax_url + '?action=woo_outfit_style_gallery', obj).done(function(data) {
				var data = jQuery(data).filter('div')

				jQuery('.woo-outfit-gallery-content .row').append(data)

				jQuery('.woo-outfit-gallery-item-thumb-wrap').each(function(e) {
					jQuery(this).css('height', (jQuery(this).outerWidth() / jQuery(this).data('width')) * jQuery(this).data('height'))
				})

				jQuery('.woo-outfit-gallery-content .row').isotope('appended', data).isotope('layout')

				target.removeClass('loading')

				if (current != max) {
					jQuery('.woo-outfit-gallery-pagination .button').data('current', current)
				} else {
					jQuery('.woo-outfit-gallery-pagination .button').remove()
				}
			})
		} else {
			jQuery('.woo-outfit-gallery-pagination .button').remove()
		}
	})

})