// Select Product Category
jQuery('.selectId').on('change', function(e) {
	e.preventDefault();
	jQuery('#products').fadeTo("slow", 0.5);

	var cat_id = jQuery(this).val();

	jQuery.get(object.ajaxurl + '?action=wc_outfit_products_by_cat', {
		cat: cat_id,
		security: object.nonce
	}).done(function(data) {
		jQuery('#products').fadeTo('slow', 1);
		jQuery('#products').empty();

		for (var i in data) {
			var html = '<a class="col-6" data-id="' + data[i].id + '"><img src="' + data[i].thumb + '"></a>';
			jQuery("#products").prepend(html);
		}
	});
});

// Select Product
var ids = jQuery('#ids').val();
if (ids.length > 0) {
	ids = JSON.parse(ids);
} else {
	var ids = [];
}

jQuery('#products').on('click', 'a', function(e) {
	e.preventDefault();
	id = jQuery(this).attr('data-id');
	var index = 0;
	var index = jQuery.map(ids, function(i, j) {
		if (i.id == id) {
			return 1;
		}
	});

	if (index == 0) {
		var src = jQuery(this).find('img').attr('src');
		jQuery('.selected-product>.row').append('<div class="col-6"><img src="' + src + '"/><a class="close" data-id="' + id + '"></a><span class="switch inactive" data-id="' + id + '"></span></div>');
		ids.push({
			id: parseInt(id),
			labels: 0
		});
		jQuery('#ids').val(JSON.stringify(ids));
	}
});

// Product Remove
jQuery('.selected-product').on('click', '.close', function() {
	id = jQuery(this).attr('data-id');
	jQuery(this).parent('.col-6').remove();

	var index = jQuery.map(ids, function(i, j) {
		if (i.id == id) {
			return j;
		}
	});

	ids.splice(index, 1);
	jQuery('#ids').val(JSON.stringify(ids));
});

// Switch
jQuery(document).on('click', '.switch', function() {
	id = jQuery(this).attr('data-id');
	jQuery(this).toggleClass('active inactive');
	var index = jQuery.map(ids, function(i, j) {
		if (i.id == id) {
			return j;
		}
	});

	ids[index].labels ^= 1;
	jQuery('#ids').val(JSON.stringify(ids));
});