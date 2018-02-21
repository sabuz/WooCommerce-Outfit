<?php

namespace Xim_Woo_Outfit\Traits;

trait Helper {
	use Database;
	
	// Post time inverval
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
		$author_id = get_post_field('post_author', $post_id);

		return get_user_meta($author_id);
	}

	// Get follower count
	function get_follower_count($user_id) {
		$followers = get_user_meta($user_id, 'followers', true) ?: array();

		return count($followers);
	}

	// Get following count
	function get_following_count($user_id) {
		$following = get_user_meta($user_id, 'following', true) ?: array();

		return count($following);
	}

	// Check if following a user
	function is_following($user_id) {
		$logged_user = get_current_user_id();

		if ($logged_user) {
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

	//
	// The following Functions are not finalized yet.
	//
	function user_gallery_link() {
		return home_url('style-gallery/?user=') . get_current_user_id();
	}
	// add_shortcode('user_gallery_link', 'user_gallery_link');

	function user_gallery_link_by_id($user) {
		return home_url('style-gallery/?user=') . $user;
	}

	function user_gallery_likes_by_id($user) {
		return home_url('style-gallery/?user=') . $user . '&page=likes';
	}

	// public function myaccount_join_permalink($path) {
	// 	return esc_url(get_permalink(get_option('woocommerce_myaccount_page_id')) . $path);
	// }

	function outfit_like_button_html($post_id, $count = true) {
		$content = '';
		$content .= '<div class="post-like">';

		if ($this->is_liked_outfit($post_id)) {
			$content .= '<a href="#" class="like-btn enabled" data-id="' . $post_id . '">';
		} else {
			$content .= '<a href="#" class="like-btn" data-id="' . $post_id . '">';
		}
		$content .= '<i class="fa fa-heart"></i></a>';
		if ($count == true) {
			$content .= '<span class="count">' . self::get_post_like_count($post_id) . '</span>';
		}
		$content .= '</div>';

		return $content;
	}

	function outfit_share_button_html($post_id, $url = null) {
		if ($url == null) {
			$url = home_url('style-gallery/?view=' . $post_id);
		}

		$content = '';
		$content .= '<div class="social-share">';
		$content .= '<a href="http://pinterest.com/pin/create/button/?url=' . esc_url($url) . '&media=' . $this->get_outfit_thumbnail($post_id, 'product-thumb') . '&description=' . get_the_title($post_id) . '" target="_blank" class="fa fa-pinterest-p"></a>';
		$content .= '<a href="http://www.tumblr.com/share/link?url=' . esc_url($url) . '" target="_blank" class="fa fa-tumblr"></a>';
		$content .= '<a href="http://www.facebook.com/sharer.php?u=' . esc_url($url) . '" target="_blank" class="fa fa-facebook"></a>';
		$content .= '</div>';

		return $content;
	}

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

	function modal_hooked_products($post_id) {
		$products = get_post_meta($post_id, 'products', true);
		$content = '';

		// $content .= '<div class="owl-carousel">';
		if (!empty($products)) {
			foreach (json_decode($products) as $product) {
				$content .= '<div class="item"><a href="' . get_permalink($product->id) . '">';
				$content .= '<img src="' . $this->get_outfit_thumbnail($product->id, 'product-thumb') . '">';
				$content .= '<div class="ribbon ' . ($product->labels == 1 ? 'captured' : '') . '">' . ($product->labels == 1 ? 'Captured' : 'Similar') . '</div>';
				$content .= '<h4 class="title">' . get_the_title($product->id) . '</h4></a></div>';
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

	// Return outfit thumbnail url by post-id and thumb size
	function get_outfit_thumbnail($post_id, $size = 'full') {
		$url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);

		return $url[0];
	}
}

?>