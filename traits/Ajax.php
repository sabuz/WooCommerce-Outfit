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
		$data = new WP_Query(array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => 2,
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

		if ($data->posts) {
			foreach ($data->posts as $key => $id) {
				if (current_user_can('manage_options') || wc_customer_bought_product($user->user_email, $user->ID, $id)) {
					$product = wc_get_product($id);

					$json[$key]['id'] = $id;
					$json[$key]['title'] = $product->get_title();
					$json[$key]['thumb'] = $this->get_outfit_thumbnail($id, 'product-thumb');
					$json[$key]['price_html'] = $product->get_price_html();
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
			$author_id = $this->get_outfit_author_id($_REQUEST['view']);
			$author_data = get_user_meta($author_id);

			$content = '<div class="modal-body clearfix">';
			$content .= '<div class="thumb">';
			$content .= '<img src="' . $this->get_outfit_thumbnail($_REQUEST['view']) . '" />';
			$content .= '</div>';

			$content .= '<div class="details">';
			$content .= '<div class="author clearfix">';
			$content .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$content .= '<a href="' . $this->get_user_gallery_link($author_id) . '" class="name">';
			$content .= $author_data['nickname'][0];
			$content .= '</a>';

			if ($author_id != get_current_user_id()) {
				$content .= '<a href="#" class="medal" data-id="' . $author_id . '">';
				if ($this->is_following($author_id)) {
					$content .= 'Unfollow';
				} else {
					$content .= 'Follow';
				}
				$content .= '</a>';
			}
			$content .= '</div>';

			// $content .= '<div class="measurement">';
			// $content .= '<table class="table">';
			// $content .= '<tr>';
			// $content .= '<th>' . __('Height', 'couture') . '</th>';
			// $content .= '<th>' . __('Bra', 'couture') . '</th>';
			// $content .= '<th>' . __('Waist', 'couture') . '</th>';
			// $content .= '<th>' . __('Hips', 'couture') . '</th>';
			// $content .= '<th>' . __('Shoe', 'couture') . '</th>';
			// $content .= '</tr>';
			// $content .= '<tr>';

			// foreach (modal_measurements($author) as $key => $val) {
			// 	$content .= '<td>' . $val . '</td>';
			// }

			// $content .= '</tr>';
			// $content .= '</table>';
			// $content .= '</div>';

			$content .= '<div class="products owl-carousel">' . $this->modal_hooked_products($_REQUEST['view']) . '</div>';

			$content .= '<div class="tags">';

			$tags = wp_get_post_categories($_GET['view']);
			foreach ($tags as $tag) {
				$tag = get_category($tag);
				$content .= '<a href="' . home_url('style-gallery/?cat=' . strtolower($tag->name)) . '">' . $tag->name . '</a>';
			}
			$content .= '</div>';

			$content .= '<div class="info">';
			$content .= '<div class="pull-left">';
			$content .= '<span class="time">' . __('Added ', 'couture') . $this->outfit_posted_ago($_REQUEST['view']) . '</span>';
			$content .= '</div>';

			$content .= '<div class="pull-right">';
			$content .= $this->share_buttons_html($_REQUEST['view']);
			$content .= $this->like_button_html($_REQUEST['view']);
			$content .= '</div>';
			$content .= '</div>';
			$content .= '</div>';
			$content .= '</div>';

			if ($_REQUEST['pagination']) {
				$content .= '<div class="modal-footer">
				<a id="prev" data-id="">&laquo;</a>
				See More
				<a id="next" data-id="">&raquo;</a>
			</div>';
			}

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
						'posts_per_page' => 9,
						'order' => 'desc',
						'post__in' => $ids,
						'paged' => $_REQUEST['paged'],
					);
				}
			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'author' => $_REQUEST['user'],
					'paged' => $_REQUEST['paged'],
				);
			}
		} elseif ($_REQUEST['cat']) {
			$args = array(
				'post_type' => 'outfit',
				'post_status' => 'publish',
				'posts_per_page' => 9,
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
						'posts_per_page' => 9,
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
								'posts_per_page' => 9,
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
						'posts_per_page' => 9,
						'meta_key' => 'likes',
						'orderby' => 'meta_value_num',
						'paged' => $_REQUEST['paged'],
					);

				} elseif (@$_REQUEST['order'] == 'most-liked-day' || @$_REQUEST['order'] == 'most-liked-week') {
					$ids = $this->most_liked_outfits($_REQUEST['order']);
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => 9,
						'post__in' => $ids,
						'orderby' => 'post__in',
						'paged' => $_REQUEST['paged'],
					);

				} else {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => 9,
						'order' => 'desc',
						'paged' => $_REQUEST['paged'],
					);
				}
			}
		}

		$query = new WP_Query($args);

		while ($query->have_posts()): $query->the_post();
			$author_data = $this->get_outfit_author_data($post->ID);
			echo '<div class="grid-item col-sm-4" data-id="' . $query->post->ID . '">
				<div class="gal-header">
					<div class="gal-product clearfix">
						<ul>
							' . $this->hooked_products($query->post->ID, 4) . '
						</ul>
					</div>
					<a class="gal-thumb clearfix">
						<img src="' . $this->get_outfit_thumbnail($query->post->ID) . '" class="gal-img" />
					</a>
				</div>
				<div class="gal-footer clearfix">
					<div class="pull-left">
						<a class="author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">' . $author_data['nickname'][0] . '</a>
						<span class="time">' . $this->outfit_posted_ago() . '</span>
					</div>
					<div class="pull-right">
						<div class="gal-bubble">
							<a href="#" class="bubble-btn"><i class="fa fa-share"></i></a>

							<div class="bubble-content">
								' . $this->share_buttons_html($query->post->ID) . '
							</div>
						</div>

						' . $this->like_button_html($query->post->ID) . '
					</div>
				</div>
			</div>';
		endwhile;
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