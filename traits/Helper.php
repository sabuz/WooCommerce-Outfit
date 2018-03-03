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
		$followers = get_user_meta($user_id, 'followers', true) ?: array();

		return count($followers);
	}

	// Get following count
	function get_num_following($user_id) {
		$following = get_user_meta($user_id, 'following', true) ?: array();

		return count($following);
	}

	// Get post like count
	function get_num_post_like($post_id) {
		$count = get_post_meta($post_id, 'likes', true);
		return !empty($count) ? $count : 0;
	}

	// Check if following a user
	function is_following($user_id) {
		if ($logged_user = get_current_user_id()) {
			$following_users = get_user_meta($logged_user, 'following', true);

			if (!empty($following_users)) {
				if (in_array($user_id, $following_users)) {
					return true;
				}
			}
		}

		return false;
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
		if (!$user) {
			$user = get_current_user_id();
		}

		if ($page) {
			$page = '&page=' . $page;
		}

		return home_url('style-gallery/?user=') . $user . $page;
	}

	// Like button html.
	function like_button_html($post_id, $count = true) {
		$content = '<div class="post-like">';

		if ($this->is_liked_outfit($post_id)) {
			$content .= '<a href="#" class="like-btn enabled" data-id="' . $post_id . '">';
		} else {
			$content .= '<a href="#" class="like-btn" data-id="' . $post_id . '">';
		}
		$content .= '<i class="fa fa-heart"></i></a>';
		if ($count == true) {
			$content .= '<span class="count">' . $this->get_num_post_like($post_id) . '</span>';
		}
		$content .= '</div>';

		return $content;
	}

	// Share button html.
	function share_buttons_html($post_id, $url = null) {
		if ($url == null) {
			$url = home_url('style-gallery/?view=' . $post_id);
		}

		$content = '<div class="social-share">
			<a href="http://pinterest.com/pin/create/button/?url=' . esc_url($url) . '&media=' . $this->get_outfit_thumbnail($post_id, 'product-thumb') . '&description=' . get_the_title($post_id) . '" target="_blank" class="fa fa-pinterest-p"></a>
			<a href="http://www.tumblr.com/share/link?url=' . esc_url($url) . '" target="_blank" class="fa fa-tumblr"></a>
			<a href="http://www.facebook.com/sharer.php?u=' . esc_url($url) . '" target="_blank" class="fa fa-facebook"></a>
		</div>';

		return $content;
	}

	// Hooked products.
	function hooked_products($post_id, $limit) {
		$products = get_post_meta($post_id, 'products', true);
		$content = '';

		if (!empty($products)) {
			$products = json_decode($products);
			foreach (array_slice($products, 0, $limit) as $product) {
				$src = wp_get_attachment_image_src(get_post_thumbnail_id($product->id), 'product-thumb');
				$content .= '<li><a href="' . get_permalink($product->id) . '" target="_blank"><img src="' . $src[0] . '"></a></li>';
			}
		}

		return $content;
	}

	// Modal hooked products.
	function modal_hooked_products($post_id) {
		$products = get_post_meta($post_id, 'products', true);
		$content = '';

		// $content .= '<div class="owl-carousel">';
		if (!empty($products)) {
			foreach (json_decode($products) as $product) {
				$content .= '<div class="item"><a href="' . get_permalink($product->id) . '">
					<img src="' . $this->get_outfit_thumbnail($product->id, 'product-thumb') . '">
					<div class="ribbon ' . ($product->labels == 1 ? 'captured' : '') . '">' . ($product->labels == 1 ? 'Captured' : 'Similar') . '</div>
					<h4 class="title">' . get_the_title($product->id) . '</h4></a>
				</div>';
				//$content .= '<div class="price">' . wc_outfit_get_product_price($product->id) . '</div></a></div>';
			}
		}
		// $content .= '</div>';

		return $content;
	}

	// function modal_measurements($user) {
	// 	$data = get_userdata($user);
	// 	$arr = array();

	// 	if ($data->pvt_height != 'true' && !empty($data->height_ft) && !empty($data->height_in)) {
	// 		$arr['height'] = $data->height_ft . '\'' . $data->height_in . '\'\'';
	// 	} else {
	// 		$arr['height'] = '--';
	// 	}

	// 	if ($data->pvt_bra != 'true' && !empty($data->bra_size) && !empty($data->bra_cup)) {
	// 		$arr['bra'] = $data->bra_size . $data->bra_cup;
	// 	} else {
	// 		$arr['bra'] = '--';
	// 	}

	// 	if ($data->pvt_waist != 'true' && !empty($data->waist)) {
	// 		$arr['waist'] = $data->waist;
	// 	} else {
	// 		$arr['waist'] = '--';
	// 	}

	// 	if ($data->pvt_hips != 'true' && !empty($data->hips)) {
	// 		$arr['hips'] = $data->hips;
	// 	} else {
	// 		$arr['hips'] = '--';
	// 	}

	// 	if ($data->pvt_shoe != 'true' && !empty($data->shoe)) {
	// 		$arr['shoe'] = $data->shoe;
	// 	} else {
	// 		$arr['shoe'] = '--';
	// 	}

	// 	return $arr;
	// }

	// function modal_tags() {
	// 	$tags = wp_get_post_categories();

	// 	foreach ($tags as $tag) {

	// 	}
	// }
}

?>