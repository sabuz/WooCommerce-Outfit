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
		if ($post_id) {
			$post_time = get_the_time('U', $post_id);
		} else {
			$post_time = get_the_time('U');
		}

		return human_time_diff($post_time, current_time('timestamp')) . ' ago';
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
		// $followers = get_user_meta($user_id, 'followers', true) ?: array();
		$followers = $this->get_followers($user_id);

		return count($followers);
	}

	// Get following count
	function get_num_following($user_id) {
		// $following = get_user_meta($user_id, 'following', true) ?: array();
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

		return add_query_arg($args, get_the_permalink(get_option('wc-outfit-page-id')));
	}

	// Like button html.
	function like_button_html($post_id) {
		$content = '<div class="wc-outfit-rating">
			<a href="#" class="wc-outfit-rating-heart ' . (!$this->is_liked_outfit($post_id) ?: 'enabled') . '" data-id="' . $post_id . '"><i class="fa fa-heart"></i></a>
			<span class="wc-outfit-rating-count">' . $this->get_num_post_like($post_id) . '</span>
		</div>';

		return $content;
	}

	// Share button html.
	function share_buttons_html($post_id, $url = null) {
		if ($url == null) {
			$url = home_url('style-gallery/?view=' . $post_id);
		}

		$content = '<div class="wc-outfit-social-share">
			<a href="http://www.facebook.com/sharer.php?u=' . esc_url($url) . '" target="_blank" class="fa fa-facebook"></a>
			<a href="http://pinterest.com/pin/create/button/?url=' . esc_url($url) . '&media=' . $this->get_outfit_thumbnail($post_id, 'product-thumb') . '&description=' . get_the_title($post_id) . '" target="_blank" class="fa fa-pinterest-p"></a>
			<a href="http://www.tumblr.com/share/link?url=' . esc_url($url) . '" target="_blank" class="fa fa-tumblr"></a>
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

	function modal_tags($post_id) {
		$content = '';
		$tags = wp_get_post_terms($_GET['view'], 'outfit_tags');

		foreach ($tags as $tag) {
			$content .= '<a href="' . home_url(get_option('wc-outfit-page-slug') . '/?tag=' . $tag->slug) . '" target="_blank">' . $tag->name . '</a>';
		}

		return $content;
	}
}

?>