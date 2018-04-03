// History
function ChangeUrl(page, url) {
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
	// History Handler
	var hasHistory = false

	jQuery('.wc-outfit-gallery').on('click', '.item-thumb', function() {
		hasHistory = true
		post_id = jQuery(this).closest('.wc-outfit-gallery-item').attr('data-id')
		ChangeUrl('Title', object.homeurl + '/style-gallery/?view=' + post_id)
	})

	jQuery('#wc-outfit-modal').on('click', '.close', function() {
		if (hasHistory == true) {
			ChangeUrl('Title', history.back())
		} else {
			ChangeUrl('Title', object.homeurl + '/style-gallery')
		}
	})

	// Pagination Handler
	current = parseInt(jQuery('.wc-outfit-gallery-pagination .button').attr('data-current'))
	max = parseInt(jQuery('.wc-outfit-gallery-pagination .button').attr('data-max'))

	if (max == 0 || current == max) {
		jQuery('.wc-outfit-gallery-pagination .button').remove()
	}

	// Init isotope
	jQuery('.wc-outfit-gallery-content').isotope({
		itemSelector: '.wc-outfit-gallery-item',
		columnWidth: '.col-sm-4',
		percentPosition: true,
		sortBy: 'original-order'
	})

	// Follow
	jQuery(document).on('click', '.wc-outfit-follow-btn', function(e) {
		e.preventDefault()

		var target = jQuery(this)
		var user_id = target.attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_follow_people', {
			user_id: user_id,
			security: object.nonce
		}).done(function(data) {
			jQuery('.follower').html(data + ' Followers')

			if (jQuery('.wc-outfit-follow-btn span strong').text() == 'Follow') {
				jQuery('.wc-outfit-follow-btn span strong').text('Unfollow')
			} else if (jQuery('.wc-outfit-follow-btn span strong').text() == 'Unfollow') {
				jQuery('.wc-outfit-follow-btn span strong').text('Follow')
			}

			if (target.hasClass('wc-outfit-follow-btn-big') != true) {
				jQuery(target).text(target.text() == 'Follow' ? 'Unfollow' : 'Follow')
			}

		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = object.myaccount_url
			}
		})
	})

	// Modal
	jQuery('.wc-outfit-gallery').on('click', '.item-thumb', function(e) {
		view = jQuery(this).closest('.wc-outfit-gallery-item').attr('data-id')

		jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
			view: view,
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
		})
	})

	// Like
	jQuery(document).on('click', '.wc-outfit-rating-heart', function(e) {
		e.preventDefault()

		var pointer = jQuery(this)

		jQuery.post(object.ajaxurl + '?action=wc_outfit_post_like', {
			post_id: pointer.attr('data-id'),
			security: object.nonce
		}).done(function(data) {
			pointer.toggleClass('enabled').siblings('.count').html(data)
		}).fail(function(xhr) {
			if (xhr.status == 401) {
				window.location.href = object.myaccount_url
			}
		})
	})

	// Infinite Scroll
	jQuery('.wc-outfit-gallery-pagination').on('click', '.button', function() {
		var target = jQuery(this)
		target.addClass('loading')
		current = jQuery(this).attr('data-current')
		max = jQuery(this).attr('data-max')

		order = jQuery(this).attr('data-order')
		user = jQuery(this).attr('data-user')
		page = jQuery(this).attr('data-page')
		cat = jQuery(this).attr('data-cat')

		if (current <= max) {
			current = parseInt(current) + 1

			var obj = {
				paged: current,
				security: object.nonce
			}

			if (typeof order !== typeof undefined && order !== false) {
				obj.order = order
			}

			if (typeof user !== typeof undefined && user !== false) {
				obj.user = parseInt(user)
			}

			if (typeof page !== typeof undefined && page !== false) {
				obj.page = page
			}

			if (typeof cat !== typeof undefined && cat !== false) {
				obj.cat = cat
			}

			jQuery.get(object.ajaxurl + '?action=wc_outfit_style_gallery', obj).done(function(data) {
				data = jQuery(data).filter('div')

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

// Follower List
// jQuery('.follower').on('click', function(e) {
// 	e.preventDefault()
// 	user = jQuery(this).attr('data-user')

// 	jQuery('.list-group').empty()

// 	jQuery.get(object.ajaxurl + '?action=wc_outfit_list_follower', {
// 		user: user,
// 		security: object.nonce
// 	}).done(function(data) {
// 		jQuery.each(data, function(i, j) {
// 			jQuery('.list-group').append('<li class="list-group-item"><a href="' + object.homeurl + '/style-gallery/?user=' + i + '">' + j + '</a></li>')
// 		})
// 	})
// })

// Following List
// jQuery('.following').on('click', function(e) {
// 	e.preventDefault()
// 	user = jQuery(this).attr('data-user')

// 	jQuery('.list-group').empty()

// 	jQuery.get(object.ajaxurl + '?action=wc_outfit_list_following', {
// 		user: user,
// 		security: object.nonce
// 	}).done(function(data) {
// 		jQuery.each(data, function(i, j) {
// 			jQuery('.list-group').append('<li class="list-group-item"><a href="' + object.homeurl + '/style-gallery/?user=' + i + '">' + j + '</a></li>')
// 		})
// 	})
// })