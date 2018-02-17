<?php

namespace Xim_Woo_Outfit\Traits;

// use Xim_Woo_Outfit\Traits\Database;

/**
 * Define helper functions for this plugin.
 *
 * @since    1.0.0
 */

trait Helper {
	use Database;
	// Post time inverval
	function wc_outfit_time_ago($post_id = null) {
		if ($post_id) {
			$post_time = get_the_time('U', $post_id);
		} else {
			$post_time = get_the_time('U');
		}

		return human_time_diff($post_time, current_time('timestamp')) . ' ago';
	}

// Get author by post id
	function wc_outfit_get_post_author($post_id) {
		return get_post_field('post_author', $post_id);
	}

// Get author data
	function wc_outfit_get_author_data($author_id) {
		return get_user_meta($author_id);
	}

// Get author data
	function wc_outfit_get_post_author_data($post_id) {
		$author_id = get_post_field('post_author', $post_id);

		return get_user_meta($author_id);
	}

// Get follower count
	function wc_outfit_get_follower_count($user_id) {
		$followers = get_user_meta($user_id, 'followers', true);

		return count($followers);
	}

// Get following count
	function wc_outfit_get_following_count($user_id) {
		$following = get_user_meta($user_id, 'following', true);

		return count($following);
	}

// Check if following a user
	function wc_outfit_is_following($user_id) {
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
	function wc_outfit_product_cats() {
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
	//
	//
	// The following Functions are not finalized yet.
	//
	//
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

	function wc_outfit_post_like_button($post, $count = true) {
		$content = '';
		$content .= '<div class="post-like">';

		if ($this->wc_outfit_is_liked($post)) {
			$content .= '<a href="#" class="like-btn enabled" data-id="' . $post . '">';
		} else {
			$content .= '<a href="#" class="like-btn" data-id="' . $post . '">';
		}
		$content .= '<i class="fa fa-heart"></i></a>';
		if ($count == true) {
			$content .= '<span class="count">' . self::wc_outfit_get_post_like_count($post) . '</span>';
		}
		$content .= '</div>';

		return $content;
	}

	function wc_outfit_outfit_share_button($post, $url = null) {
		if ($url == null) {
			$url = home_url('style-gallery/?view=' . $post);
		}

		$content = '';
		$content .= '<div class="social-share">';
		$content .= '<a href="http://pinterest.com/pin/create/button/?url=' . esc_url($url) . '&media=' . $this->wc_outfit_post_thumb_by_id($post, 'product-thumb') . '&description=' . get_the_title($post) . '" target="_blank" class="fa fa-pinterest-p"></a>';
		$content .= '<a href="http://www.tumblr.com/share/link?url=' . esc_url($url) . '" target="_blank" class="fa fa-tumblr"></a>';
		$content .= '<a href="http://www.facebook.com/sharer.php?u=' . esc_url($url) . '" target="_blank" class="fa fa-facebook"></a>';
		$content .= '</div>';

		return $content;
	}

	function wc_outfit_hooked_product($post_id, $limit) {
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

	function wc_outfit_modal_hooked_product($post) {
		$products = get_post_meta($post, 'products', true);
		$content = '';

		// $content .= '<div class="owl-carousel">';
		if (!empty($products)) {
			foreach (json_decode($products) as $product) {
				$content .= '<div class="item"><a href="' . get_permalink($product->id) . '">';
				$content .= '<img src="' . $this->wc_outfit_post_thumb_by_id($product->id, 'product-thumb') . '">';
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

// Functions
	function wc_outfit_post_thumb_by_id($post, $size = 'full') {
		$url = wp_get_attachment_image_src(get_post_thumbnail_id($post), $size);

		return $url[0];
	}
}

?>