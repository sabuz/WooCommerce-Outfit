<?php

/*******************************************************
 *
 * Custom Post Type Metabox.
 *
 ******************************************************/
function wc_outfit_register_meta_boxes() {
	add_meta_box('couture-hooked-products', __('Used Products', 'couture'), 'wc_outfit_mb_hooked_products_callback', 'outfit');
	add_meta_box('couture-featured', __('Featured Post', 'couture'), 'wc_outfit_mb_featured_products_callback', 'outfit', 'side');
}
add_action('add_meta_boxes', 'wc_outfit_register_meta_boxes');

function wc_outfit_mb_hooked_products_callback($post) {
	$products = get_post_meta($post->ID, 'products', true);
	$content = '';

	$content .= '<div class="couture-metabox">';

	$content .= '<div class="selected-product">';
	$content .= '<div class="row">';
	if (!empty($products)) {
		foreach (json_decode($products) as $product) {
			$content .= '<div class="col-6">';
			$content .= '<img src="' . wc_outfit_post_thumb_by_id($product->id, 'product-thumb') . '">';
			$content .= '<a class="close" data-id="' . $product->id . '"></a>';
			$content .= '<span class="switch ' . ($product->labels == 1 ? 'active' : 'inactive') . '" data-id="' . $product->id . '"></span>';
			$content .= '</div>';
		}
	}
	$content .= '</div>';
	$content .= '</div>';

	$content .= '<div class="row">';
	$content .= '<div class="col-1">';
	$content .= '<select class="selectId">';
	$content .= '<option selected disabled>Choose a category</option>';
	foreach (wc_outfit_product_cats() as $cat) {
		$content .= '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
	}
	$content .= '</select>';
	$content .= '</div>';
	$content .= '</div>';

	$content .= '<div class="row">';
	$content .= '<div id="products" class="products"></div>';
	$content .= '</div>';
	$content .= '</div>';

	$content .= '<input type="hidden" name="ids" id="ids" value=' . $products . '>';

	echo $content;
}

function wc_outfit_mb_featured_products_callback($post) {
	$value = get_post_meta($post->ID, 'featured', true);

	echo '<div class="couture-metabox">';
	echo '<label for="featured">';
	echo '<input type="checkbox" name="featured" id="featured" ' . (!empty($value) ? "checked" : "") . '/>';
	echo __('Make this post featured ?', 'prfx-textdomain');
	echo '</label>';
	echo '</div>';
}

function wc_outfit_update_metabox($post_id, $post) {
	global $post_type;

	if (wp_is_post_revision($post_id)) {
		return;
	}

	if ($post->post_type == 'outfit') {
		if (isset($_POST['featured'])) {
			update_post_meta($post->ID, 'featured', 'yes');
		} else {
			update_post_meta($post->ID, 'featured', '');
		}

		if (isset($_POST['ids'])) {
			update_post_meta($post->ID, 'products', $_POST['ids']);
		}
	}
}
add_action('save_post', 'wc_outfit_update_metabox', 1, 2);

function wc_outfit_custom_thumb_boxes() {
	remove_meta_box('postimagediv', 'outfit', 'side');
	add_meta_box('postimagediv', __('Outfit Image'), 'post_thumbnail_meta_box', 'outfit', 'normal', 'low');
}
add_action('do_meta_boxes', 'wc_outfit_custom_thumb_boxes');

function wc_outfit_metabox_styles() {
	global $post_type;

	if ($post_type == 'outfit') {
		wp_enqueue_style('metabox', plugin_dir_url(__FILE__) . '../css/metabox.css');

		wp_enqueue_script('metabox', plugin_dir_url(__FILE__) . '../js/metabox.js', array(), false, true);
		wp_localize_script('metabox', 'object', ['ajaxurl' => admin_url('admin-ajax.php')]);
	}
}
add_action('admin_enqueue_scripts', 'wc_outfit_metabox_styles');
?>