<?php

namespace Woocommerce_Outfit\Traits;

trait Helper {
	use Database;

	// Return outfit thumbnail url by post-id and thumb size
	function get_outfit_thumbnail($post_id, $size = 'full', $url = true) {
		$data = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);

		return ($url == true ? $data[0] : $data);
	}

	// Post time interval
	function outfit_posted_ago($post_id = null) {
		$post_time = get_the_time('U', $post_id);

		return sprintf(__("%s ago", 'woo-outfit'), human_time_diff($post_time, current_time('timestamp')));
	}

	// Get outfit author by post id
	function get_outfit_author_id($post_id) {
		return get_post_field('post_author', $post_id);
	}

	// Get outfit author data
	function get_outfit_author_data($post_id) {
		return get_user_meta(get_post_field('post_author', $post_id));
	}

	// Get follower count
	function get_num_followers($user_id) {
		$followers = $this->get_followers($user_id);

		return count($followers);
	}

	// Get following count
	function get_num_following($user_id) {
		$following = $this->get_followings($user_id);

		return count($following);
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

	// User gallery page link.
	function get_user_gallery_link($user = null, $page = null) {
		$args = array();

		$args['user'] = ($user ? $user : get_current_user_id());

		if ($page) {
			$args['page'] = $page;
		}

		return add_query_arg($args, get_the_permalink(get_option('woo-outfit-page-id')));
	}

	// Like button html.
	function like_button_html($post_id) {
		$content = '<div class="woo-outfit-rating">
			<a href="#" class="woo-outfit-rating-heart ' . (!$this->is_liked_outfit($post_id) ?: 'enabled') . '" data-id="' . $post_id . '"><i class="woo-outfit-icon woo-outfit-icon-heart"></i></a>
			<span class="woo-outfit-rating-count">' . $this->get_num_post_like($post_id) . '</span>
		</div>';

		return $content;
	}

	// Share button html.
	function share_buttons_html($post_id, $url = null) {
		if ($url == null) {
			$url = home_url('style-gallery/?view=' . $post_id);
		}

		$content = '<div class="woo-outfit-social-share">
			<a href="http://www.facebook.com/sharer.php?u=' . esc_url($url) . '" target="_blank" class="woo-outfit-icon woo-outfit-icon-facebook"></a>
			<a href="http://pinterest.com/pin/create/button/?url=' . esc_url($url) . '&media=' . $this->get_outfit_thumbnail($post_id, 'product-thumb') . '&description=' . get_the_title($post_id) . '" target="_blank" class="woo-outfit-icon woo-outfit-icon-pinterest-p"></a>
			<a href="http://www.tumblr.com/share/link?url=' . esc_url($url) . '" target="_blank" class="woo-outfit-icon woo-outfit-icon-tumblr"></a>
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
						<div class="ribbon ' . ($product->labels == 1 ? 'captured' : '') . '">' . ($product->labels == 1 ? __('Captured', 'woo-outfit') : __('Similar', 'woo-outfit')) . '</div>
						<h4 class="title">' . get_the_title($product->id) . '</h4>
					</a>
					<div class="price">' . $p->get_price_html() . '</div>
				</div>';
			}
		}

		return $content;
	}

	// Modal tags.
	function modal_tags($post_id) {
		$tags = wp_get_post_terms($post_id, 'outfit_tags');
		$content = '';

		if (!empty($tags)) {
			$content .= '<div class="woo-outfit-modal-tags">';

			foreach ($tags as $tag) {
				$content .= '<a href="' . esc_url(add_query_arg('tags', $tag->slug, get_the_permalink(get_option('woo-outfit-page-id')))) . '" target="_blank">' . $tag->name . '</a>';
			}

			$content .= '</div>';
		}

		return $content;
	}
}

?>