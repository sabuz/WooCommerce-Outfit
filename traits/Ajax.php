<?php

namespace Xim_Woo_Outfit\Traits;

use WP_Query;

trait Ajax {

	/**
	 * Get products list by category.
	 *
	 * @since    1.0.0
	 */
	function ajax_get_products_by_cat() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		$json = array();
		$user = wp_get_current_user();
		$term = get_term($_REQUEST['cat'], 'product_cat');
		$data = new WP_Query(array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => 12,
			'paged' => intval($_REQUEST['page']),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $_REQUEST['cat'],
					'operator' => 'IN',
				),
			),
			'fields' => 'ids',
		));

		$json['term'] = array('count' => $term->count, 'next' => (intval($_REQUEST['page']) * 12 >= $term->count ? false : true));

		if ($data->posts) {
			foreach ($data->posts as $key => $id) {
				if (current_user_can('manage_options') || wc_customer_bought_product($user->user_email, $user->ID, $id)) {
					$product = wc_get_product($id);

					$json['products'][$key]['id'] = $id;
					$json['products'][$key]['title'] = $product->get_title();
					$json['products'][$key]['thumb'] = $this->get_outfit_thumbnail($id, 'product-thumb');
					$json['products'][$key]['price_html'] = $product->get_price_html();
				}
			}
		}

		wp_send_json($json);
	}

	function nopriv_ajax_get_products_by_cat() {
		wp_send_json(array());
	}

	/**
	 * Toggle post like.
	 *
	 * @since    1.0.0
	 */
	function ajax_post_like() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		$count = $this->toggle_post_like($_REQUEST['post_id']);
		update_post_meta($_REQUEST['post_id'], 'likes', $count);
		wp_send_json($count, 200);
	}

	function nopriv_ajax_post_like() {
		wp_send_json(get_permalink(get_option('woocommerce_myaccount_page_id')), 401);
	}

	/**
	 * Follow people.
	 *
	 * @since    1.0.0
	 */
	function ajax_follow_people() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		// $user = get_current_user_id();
		// $followers = get_user_meta($_REQUEST['user_id'], 'followers', true) ?: array();
		// $following = get_user_meta($user, 'following', true) ?: array();

		// if (in_array($user, $followers)) {
		// 	$followers = array_diff($followers, array($user));
		// } else {
		// 	$followers[] = $user;
		// }

		// if (in_array($_REQUEST['user_id'], $following)) {
		// 	$following = array_diff($following, array($_REQUEST['user_id']));
		// } else {
		// 	$following[] = $_REQUEST['user_id'];
		// }

		// update_user_meta($_REQUEST['user_id'], 'followers', $followers);
		// update_user_meta($user, 'following', $following);
		;

		// wp_send_json(count($followers));
		wp_send_json($this->toggle_follow_profile($_REQUEST['user_id']), 200);


	}

	function nopriv_ajax_follow_people() {
		wp_send_json(get_permalink(get_option('woocommerce_myaccount_page_id')), 401);
	}

	/**
	 * List follower.
	 *
	 * @since    1.0.0
	 */
	function ajax_list_follower() {
		check_ajax_referer('wc_outfit_nonce', 'security');
		
		$data = array();

		if ($_REQUEST['user']) {
			// $followers = get_user_meta($_REQUEST['user'], 'followers', true) ?: array();
			$followers = $this->get_followers($_REQUEST['user']) ?: array();

			foreach ($followers as $key => $value) {
				$author_data = get_user_meta($value);
				$data[$value] = $author_data['nickname'][0];
			}
		}

		wp_send_json($data);
	}

	/**
	 * List following.
	 *
	 * @since    1.0.0
	 */
	function ajax_list_following() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		$data = array();

		if ($_REQUEST['user']) {
			// $following = get_user_meta($_REQUEST['user'], 'following', true) ?: array();
			$following = $this->get_followings($_REQUEST['user']) ?: array();

			foreach ($following as $key => $value) {
				$author_data = get_user_meta($value);
				$data[$value] = $author_data['nickname'][0];
			}
		}

		wp_send_json($data);
	}

	/**
	 * Single outfit modal data.
	 *
	 * @since    1.0.0
	 */
	function ajax_outfit_modal() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		$content = '';

		if ($_REQUEST['view']) {
			$author = $this->get_outfit_author_id($_REQUEST['view']);
			$author_data = get_user_meta($author);

			$content .= '<div class="modal-body clearfix">
				<div class="thumb">
					<img src="' . $this->get_outfit_thumbnail($_REQUEST['view']) . '" />
				</div>

				<div class="details">
					<div class="author clearfix">
						<a class="name" href="' . $this->get_user_gallery_link($author) . '">
							' . ucfirst($author_data['nickname'][0]) . '
						</a>';

						if ($author != get_current_user_id()) {
							$content .= '<a href="#" class="medal" data-id="' . $author . '">' . ($this->is_following($author) ? __('Unfollow', 'xim') : __('Follow', 'xim')) . '</a>';
						}

						$content .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="hooked-products owl-carousel">
						' . $this->modal_hooked_products($_REQUEST['view']) . '
					</div>

					<div class="tags">' . $this->modal_tags($_REQUEST['view']) . '</div>

					<div class="footer-info">
						<span class="time">' . __('Added ', 'xim') . $this->outfit_posted_ago($_REQUEST['view']) . '</span>

						' . $this->like_button_html($_REQUEST['view']) . '
						' . $this->share_buttons_html($_REQUEST['view']) . '
					</div>
				</div>
			</div>';

			// if ($_REQUEST['pagination']) {
			// 	$content .= '<div class="modal-footer">
			// 	<a id="prev" data-id="">&laquo;</a>
			// 	See More
			// 	<a id="next" data-id="">&raquo;</a>
			// </div>';
			// }

		}

		echo $content;
		die();
	}

	/**
	 * Load outfit post data on request.
	 *
	 * @since    1.0.0
	 */
	function ajax_style_gallery() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		if ($_REQUEST['user']) {
			if (@$_REQUEST['page'] == 'likes') {
				$ids = $this->get_liked_outfits($_REQUEST['user']);
				if (!empty($ids)) {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('posts_per_page'),
						'order' => 'desc',
						'post__in' => $ids,
						'paged' => $_REQUEST['paged'],
					);
				}
			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => get_option('posts_per_page'),
					'order' => 'desc',
					'author' => $_REQUEST['user'],
					'paged' => $_REQUEST['paged'],
				);
			}
		} elseif ($_REQUEST['cat']) {
			$args = array(
				'post_type' => 'outfit',
				'post_status' => 'publish',
				'posts_per_page' => get_option('posts_per_page'),
				'order' => 'desc',
				'tax_query' => array(
					array(
						'taxonomy' => 'outfit_categories',
						'field' => 'slug',
						'terms' => $_REQUEST['cat'],
					),
				),
				'paged' => $_REQUEST['paged'],
			);
		} else {
			if ($_REQUEST['page']) {
				if ($_REQUEST['page'] == 'feat') {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('posts_per_page'),
						'order' => 'desc',
						'meta_query' => array(
							array(
								'key' => 'featured',
								'value' => 'yes',
							),
						),
						'paged' => $_REQUEST['paged'],
					);
				} elseif ($_REQUEST['page'] == 'following') {
					if (is_user_logged_in()) {
						$data = get_user_meta(get_current_user_id(), 'following', true);

						if (!empty($data)) {
							$args = array(
								'post_type' => 'outfit',
								'post_status' => 'publish',
								'posts_per_page' => get_option('posts_per_page'),
								'order' => 'desc',
								'author__in' => $data,
								'paged' => $_REQUEST['paged'],
							);
						}
					}
				}
			} else {
				if (@$_REQUEST['order'] == 'most-liked') {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('posts_per_page'),
						'meta_key' => 'likes',
						'orderby' => 'meta_value_num',
						'paged' => $_REQUEST['paged'],
					);

				} elseif (@$_REQUEST['order'] == 'most-liked-day' || @$_REQUEST['order'] == 'most-liked-week') {
					$ids = $this->most_liked_outfits($_REQUEST['order']);
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('posts_per_page'),
						'post__in' => $ids,
						'orderby' => 'post__in',
						'paged' => $_REQUEST['paged'],
					);

				} else {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('posts_per_page'),
						'order' => 'desc',
						'paged' => $_REQUEST['paged'],
					);
				}
			}
		}

		$query = new WP_Query($args);

		while ($query->have_posts()) {
			$query->the_post();
			$author_data = $this->get_outfit_author_data($post->ID);
			
			echo '<div class="grid-item col-sm-4" data-id="' . $query->post->ID . '">
				<div class="gal-item-inner-wrap">
					<img src="' . $this->get_outfit_thumbnail($query->post->ID) . '" class="gal-item-thumb" />

					<div class="gal-item-footer clearfix">
						<div class="pull-left">
							<a class="author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
								' . $author_data['nickname'][0] . '</a>
							<p class="time">' . $this->outfit_posted_ago() . '</p>
						</div>
						<div class="pull-right">
							' . $this->like_button_html($query->post->ID) . '
						</div>
					</div>
				</div>
			</div>';
		}
		
		die();
	}

	/**
	 * Submit new outfit.
	 *
	 * @since    1.0.0
	 */
	function ajax_post_outfit() {
		check_ajax_referer('wc_outfit_nonce', 'security');
		
		$data = $response = array();
		parse_str($_REQUEST['form_data'], $data);

		if (empty($data['ids'])) {
			$response['message'][] = __('Products are required.', 'xim');
		}

		if (empty($data['thumb'])) {
			$response['message'][] = __('Outfit photo is required.', 'xim');
		}

		if (count($response) == 0) {
			$post_id = wp_insert_post(array(
				'post_title' => '',
				'post_type' => 'outfit',
				'post_status' => 'pending',
			));

			if ($post_id) {
				wp_set_object_terms($post_id, $data['tags'], 'outfit_tags');
				set_post_thumbnail($post_id, $data['thumb']);
				add_post_meta($post_id, 'products', $data['ids']);
				wp_update_post(['ID' => $post_id, 'post_title' => 'Outfit ' . $post_id]);

				$response['message'][] = 'Outfit submitted successfully.';
				$response['status'] = 'success';
			}
		} else {
			$response['status'] = 'failed';
		}

		wp_send_json($response);
	}
}

?>