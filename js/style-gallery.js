// Masnory
jQuery(window).load(function() {
	jQuery('.grid-item').each(function() {
		jQuery(this).find('.gal-header').css('height', jQuery(this).find('.gal-img').outerHeight());
	});

	jQuery('.grid').isotope({
		itemSelector: '.grid-item',
		columnWidth: '.col-sm-4',
		percentPosition: true,
		sortBy: 'original-order'
	});
});

// Sort
// jQuery('.sort').change(function() {
// 	var orderBy = jQuery(this).val();
// 	window.location.href = object.homeurl + '/style-gallery/?order=' + orderBy;
// });

// var myParam = location.search.split('order=')[1];
// if (typeof myParam !== typeof undefined && myParam !== false) {
// 	jQuery('.sort').val(myParam);
// }

// Page Title
jQuery('.page-title').arctext({
	radius: 1000
});

jQuery('.page-title').on('click', function() {
	window.location.href = object.homeurl + '/style-gallery/';
});

// Image Hover
jQuery(document).on('hover', '.gal-header', function(e) {
	jQuery(this).find('.gal-product').toggleClass('expanded');
});

// Like
jQuery(document).on('click', '.like-btn', function(e) {
	e.preventDefault();

	var pointer = jQuery(this);
	post_id = pointer.attr('data-id');

	jQuery.get(object.ajaxurl + '?action=wc_outfit_post_like', {
		post_id: post_id,
		post_type: 'outfit'
	}).done(function(data) {
		if (jQuery.isNumeric(data)) {
			pointer.siblings('.count').html(data);
			pointer.toggleClass('enabled');
		} else {
			// window.location.href = data;
			jQuery('#loginModal').modal({
				backdrop: 'static'
			});
		}
	});
});

// Share Button
jQuery(document).on('click', '.bubble-btn', function(e) {
	e.preventDefault();
	jQuery(this).siblings('.bubble-content').toggleClass('show');
});

// Follow
jQuery(document).on('click', '.medal', function(e) {
	target = jQuery(this);
	user_id = target.attr('data-id');

	if (jQuery.isNumeric(user_id)) {
		e.preventDefault();

		jQuery.get(object.ajaxurl + '?action=wc_outfit_follow_people', {
			user_id: user_id
		}).done(function(data) {
			if (jQuery.isNumeric(data)) {
				jQuery('.follower').html(data + ' Followers');

				if (jQuery('.medal span strong').text() == 'Follow') {
					jQuery('.medal span strong').text('Unfollow');
				} else if (jQuery('.medal span strong').text() == 'Unfollow') {
					jQuery('.medal span strong').text('Follow');
				}

				if (target.hasClass('medal-big') != true) {
					jQuery(target).text(target.text() == 'Follow' ? 'Unfollow' : 'Follow');
				}
			} else {
				// window.location.href = data;
				jQuery('#loginModal').modal({
					backdrop: 'static'
				});
			}
		});
	} else {
		if (jQuery(this).hasAttr('data-target')) {
			e.preventDefault();
		}
	}
});

// Follower List
jQuery('.follower').on('click', function(e) {
	e.preventDefault();
	user = jQuery(this).attr('data-user');

	jQuery('.list-group').empty();

	jQuery.get(object.ajaxurl + '?action=wc_outfit_list_follower', {
		user: user
	}).done(function(data) {
		jQuery.each(data, function(i, j) {
			jQuery('.list-group').append('<li class="list-group-item"><a href="' + object.homeurl + '/style-gallery/?user=' + i + '">' + j + '</a></li>');
		});
	});
});

// Following List
jQuery('.following').on('click', function(e) {
	e.preventDefault();
	user = jQuery(this).attr('data-user');

	jQuery('.list-group').empty();

	jQuery.get(object.ajaxurl + '?action=wc_outfit_list_following', {
		user: user
	}).done(function(data) {
		jQuery.each(data, function(i, j) {
			jQuery('.list-group').append('<li class="list-group-item"><a href="' + object.homeurl + '/style-gallery/?user=' + i + '">' + j + '</a></li>');
		});
	});
});

// Modal
jQuery(document).on('click', '.gal-header', function() {
	view = jQuery(this).closest('.grid-item').attr('data-id');

	jQuery.get(object.ajaxurl + '?action=wc_outfit_single_outfit_modal', {
		view: view
	}).done(function(data) {
		jQuery('#productModal .modal-content').empty().append(jQuery(data));

		jQuery('#productModal').modal({
			backdrop: 'static'
		});

		jQuery("#productModal .products").trigger('destroy.owl.carousel');

		setTimeout(function() {
			jQuery("#productModal .products").owlCarousel({
				items: 3,
				margin: 20,
				nav: true,
				lazyLoad: true
			});
		}, 150);
	});
});


// Infinite Scroll
jQuery('.more').on('click', '.button', function() {
	var target = jQuery(this);
	target.addClass('loading');
	current = jQuery(this).attr('data-current');
	max = jQuery(this).attr('data-max');

	order = jQuery(this).attr('data-order');
	user = jQuery(this).attr('data-user');
	page = jQuery(this).attr('data-page');
	cat = jQuery(this).attr('data-cat');

	if (current <= max) {
		current = parseInt(current) + 1;

		var obj = {
			paged: current
		}

		if (typeof order !== typeof undefined && order !== false) {
			obj.order = order;
		}

		if (typeof user !== typeof undefined && user !== false) {
			obj.user = parseInt(user);
		}

		if (typeof page !== typeof undefined && page !== false) {
			obj.page = page;
		}

		if (typeof cat !== typeof undefined && cat !== false) {
			obj.cat = cat;
		}

		jQuery.get(object.ajaxurl + '?action=wc_outfit_style_gallery', obj).done(function(data) {
			data = jQuery(data).filter('div');

			jQuery('.grid').append(data);

			jQuery('.grid').imagesLoaded(function() {
				jQuery('.grid-item').each(function() {
					jQuery(this).find('.gal-header').css('height', jQuery(this).find('.gal-img').outerHeight());
				});
				jQuery('.grid').isotope('appended', data).isotope('layout');
			});

			target.removeClass('loading');

			if (current != max) {
				jQuery('.more .button').attr('data-current', current);
			} else {
				jQuery('.more .button').remove();
			}
		});
	} else {
		jQuery('.more .button').remove();
	}
});

//check if there is any pagination
jQuery(document).ready(function() {
	current = parseInt(jQuery('.more .button').attr('data-current'));
	max = parseInt(jQuery('.more .button').attr('data-max'));

	if (max == 0 || current == max) {
		jQuery('.more .button').remove();
	}
});

// History
function ChangeUrl(page, url) {
	if (typeof(history.pushState) != "undefined") {
		var obj = {
			Page: page,
			Url: url
		};
		history.pushState(obj, obj.Page, obj.Url);
	} else {
		alert("Browser does not support HTML5.");
	}
}

var hasHistory = false;
jQuery(document).on('click', '.gal-header', function() {
	hasHistory = true;
	post_id = jQuery(this).closest('.grid-item').attr('data-id');
	ChangeUrl("Title", object.homeurl + '/style-gallery/?view=' + post_id);
});

jQuery(document).on('click', '#productModal .close', function() {
	if (hasHistory == true) {
		ChangeUrl("Title", history.back());
	} else {
		ChangeUrl('Title', object.homeurl + '/style-gallery');
	}
});