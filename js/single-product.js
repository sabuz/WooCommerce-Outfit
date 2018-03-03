jQuery(document).ready(function() {
	// Carousel
	jQuery('.outfit .owl-carousel').owlCarousel({
		loop: false,
		margin: 3,
		nav: true,
		items: 5
	});

	// Outfit Modal
	jQuery('.outfit').on('click', '.item', function() {
		view = jQuery(this).attr('data-id');
		next = jQuery(this).parent().next().find('.item').attr('data-id');
		prev = jQuery(this).parent().prev().find('.item').attr('data-id');

		jQuery.get(object.ajaxurl + '?action=outfit_modal', {
			view: view,
			pagination: true
		}).done(function(data) {
			jQuery('#productModal .modal-content').empty().append(jQuery(data));

			jQuery('#productModal').modal({
				backdrop: 'static'
			});

			jQuery("#producMtodal .products").trigger('destroy.owl.carousel');

			setTimeout(function() {
				jQuery("#productModal .products").owlCarousel({
					items: 3,
					margin: 20,
					nav: true,
					lazyLoad: true
				});
			}, 150);

			jQuery('#next').attr('data-id', next);
			jQuery('#prev').attr('data-id', prev);

		});
	});
})