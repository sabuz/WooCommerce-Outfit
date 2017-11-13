// Select Product Category
jQuery('.selectId').on('change', function(e) {
	e.preventDefault();
	jQuery('#products').fadeTo("slow", 0.5);

	var cat_id = jQuery(this).val();

	jQuery.get(object.ajaxurl + '?action=products_by_cat', {
		cat: cat_id
	}).done(function(data) {
		jQuery('#products').fadeTo('slow', 1);
		jQuery('#products').empty();

		for (var i in data) {
			var html = '<a class="col-sm-4 item" data-id="' + data[i].id + '"><img src="' + data[i].thumb + '"><h3>' + data[i].title + '</h3></a>';
			jQuery("#products").prepend(html);
		}
	});
});

// Select Product
var ids = [];
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
		jQuery('.chosen>.row').append('<div class="item"><img src="' + src + '"/><a class="close" data-id="' + id + '"></a></div>');
		ids.push({
			id: parseInt(id),
			labels: 1
		});
		jQuery('#ids').val(JSON.stringify(ids));
		jQuery('#newOutfitForm').bootstrapValidator('revalidateField', 'ids');
	}
});

// Product Remove
jQuery('.chosen').on('click', 'a', function() {
	id = jQuery(this).attr('data-id');
	jQuery(this).closest('.item').remove();

	var index = jQuery.map(ids, function(i, j) {
		if (i.id == id) {
			return j;
		}
	});

	ids.splice(index, 1);
	if (ids.length == 0) {
		jQuery('#ids').val('');
	} else {
		jQuery('#ids').val(JSON.stringify(ids));
	}

	jQuery('#newOutfitForm').bootstrapValidator('revalidateField', 'ids');
});

// Filepicker
jQuery("input[type='file'].filepicker").filepicker();

var _URL = window.URL || window.webkitURL;

jQuery(".filepicker").change(function(e) {
	var image, file;

	if ((file = this.files[0])) {
		image = new Image();
		image.src = _URL.createObjectURL(file);
		image.onload = function() {
			if (this.width < 767 || this.height < 500) {
				alert("Your photo is too small. Please choose a higher resolution photo.");
				jQuery('.filepicker').val('');
				jQuery('.filepicker-preview').empty();
				jQuery('#newOutfitForm').bootstrapValidator('revalidateField', 'thumb');
			}
		};
	}
});

// Validator
jQuery('#newOutfitForm').bootstrapValidator({
	fields: {
		thumb: {
			validators: {
				notEmpty: {
					message: 'Outfit Thumbnail is required'
				},
				file: {
					extension: 'jpg,jpeg,png',
					type: 'image/jpeg,image/png',
					message: 'Please choose a JPG/JPEG/PNG file'
				}
			}
		},

		ids: {
			excluded: false,
			validators: {
				notEmpty: {
					message: 'Products are required'
				}
			}
		},
	}
});

// Submit
jQuery('#newOutfitForm').on('submit', function(e) {
	if (e.isDefaultPrevented()) {
		return
	} else {
		var formData = new FormData(jQuery(this)[0]);

	    jQuery.ajax({
	        url: object.ajaxurl + '?action=ajax_upload',
	        type: 'POST',
	        data: formData,
	        success: function (data) {
	            console.log(data)
	        },
	        cache: false,
	        contentType: false,
	        processData: false
	    });

	    return false;
	}
})