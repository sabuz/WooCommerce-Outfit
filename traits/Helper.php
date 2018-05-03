<?php

namespace Xim_Woo_Outfit\Traits;

trait Helper {
	use Database;

	// Return outfit thumbnail url by post-id and thumb size
	function get_outfit_thumbnail($post_id, $size = 'full') {
		$url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);

		return @$url[0];
	}

	// Post time interval
	function outfit_posted_ago($post_id = null) {
		$post_time = get_the_time('U', $post_id);

		return sprintf(__("%s ago", 'xim'), human_time_diff($post_time, current_time('timestamp')));
	}

	// Get outfit author by post id
	function get_outfit_author_id($post_id) {
		return get_post_field('post_author', $post_id);
	}

	// Get outfit author data
	function get_outfit_author_data($post_id) {
		return get_user_meta(get_post_field('post_author', $post_id));
	}

	// Get post like count
	function get_num_post_like($post_id) {
		$count = get_post_meta($post_id, 'likes', true);
		return !empty($count) ? $count : 0;
	}

	// Product Category Helper Functions
	function get_product_cats() {
		$args = array(
			'taxonomy' => 'product_cat',
			'orderby' => 'name',
			'show_count' => 0,
			'pad_counts' => 0,
			'hierarchical' => 1,
			'hide_empty' => 0,
		);

		return get_categories($args);
	}

	// Like button html.
	function like_button_html($post_id) {
		$content = '<div class="woo-outfit-rating">
			<a href="#" class="woo-outfit-rating-heart ' . (!$this->is_liked_outfit($post_id) ?: 'enabled') . '" data-id="' . $post_id . '"><i class="woo-outfit-icon woo-outfit-icon-heart"></i></a>
			<span class="woo-outfit-rating-count">' . $this->get_num_post_like($post_id) . '</span>
		</div>';

		return $content;
	}

	// Modal hooked products.
	function modal_hooked_products($post_id) {
		$products = get_post_meta($post_id, 'products', true);
		$content = '';

		if (!empty($products)) {
			foreach (json_decode($products) as $product) {
				$p = wc_get_product($product->id);

				$content .= '<div class="item">
					<a href="' . get_permalink($product->id) . '">
						<img src="' . $this->get_outfit_thumbnail($product->id, 'product-thumb') . '">
						<div class="ribbon ' . ($product->labels == 1 ? 'captured' : '') . '">' . ($product->labels == 1 ? __('Captured', 'xim') : __('Similar', 'xim')) . '</div>
						<h4 class="title">' . get_the_title($product->id) . '</h4>
					</a>
					<div class="price">' . $p->get_price_html() . '</div>
				</div>';
			}
		}

		return $content;
	}
}

?>