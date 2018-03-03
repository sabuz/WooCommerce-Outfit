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
			'posts_per_page' => -1,
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
			foreach ($data->posts as $id) {
				if (current_user_can('manage_options') || wc_customer_bought_product($user->user_email, $user->ID, $id)) {
					$post_data = array();
					$post_data['id'] = $id;
					$post_data['title'] = get_the_title($id);
					$post_data['thumb'] = $this->get_outfit_thumbnail($id, 'product-thumb');

					array_push($json, $post_data);
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

		$this->toggle_post_like($_REQUEST['post_id'], $_REQUEST['post_type']);
		update_post_meta($_REQUEST['post_id'], 'likes', $this->get_num_post_like_db($_REQUEST['post_id']));

		wp_send_json($this->get_num_post_like($_REQUEST['post_id']));
	}

	function nopriv_ajax_post_like() {
		echo get_permalink(get_option('woocommerce_myaccount_page_id'));
		die();
	}

	/**
	 * Follow people.
	 *
	 * @since    1.0.0
	 */
	function ajax_follow_people() {
		check_ajax_referer('wc_outfit_nonce', 'security');

		$user = get_current_user_id();
		$followers = get_user_meta($_REQUEST['user_id'], 'followers', true) ?: array();
		$following = get_user_meta($user, 'following', true) ?: array();

		if (in_array($user, $followers)) {
			$followers = array_diff($followers, array($user));
		} else {
			$followers[] = $user;
		}

		if (in_array($_REQUEST['user_id'], $following)) {
			$following = array_diff($following, array($_REQUEST['user_id']));
		} else {
			$following[] = $_REQUEST['user_id'];
		}

		update_user_meta($_REQUEST['user_id'], 'followers', $followers);
		update_user_meta($user, 'following', $following);

		wp_send_json(count($followers));
	}

	function nopriv_ajax_follow_people() {
		echo get_permalink(get_option('woocommerce_myaccount_page_id'));
		die();
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
			$followers = get_user_meta($_REQUEST['user'], 'followers', true) ?: array();

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
			$following = get_user_meta($_REQUEST['user'], 'following', true) ?: array();

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
						<a class="author" href="' . user_gallery_link_by_id(get_the_author_meta('ID')) . '">' . wc_outfit_author_name_by_id(get_the_author_meta('ID')) . '</a>
						<span class="time">' . outfit_posted_ago() . '</span>
					</div>
					<div class="pull-right">
						<div class="gal-bubble">
							<a href="#" class="bubble-btn"><i class="fa fa-share"></i></a>

							<div class="bubble-content">
								' . share_buttons_html($query->post->ID) . '
							</div>
						</div>

						' . like_button_html($query->post->ID) . '
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
		
		$msg = array();

		if (!isset($_REQUEST['ids'])) {
			$msg[] = 'Products are required';
		}

		if (!file_exists($_FILES['thumb']['tmp_name'])) {
			$msg[] = 'Thumbnail are required';
		}

		if (count($msg) == 0) {
			$post_args = array(
				'post_title' => '',
				'post_type' => 'outfit',
				'post_status' => 'pending',
			);

			$post_id = wp_insert_post($post_args);

			if ($post_id) {

				// Including file library if not exist
				if (!function_exists('wp_handle_upload')) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				// Uploading file to server
				$movefile = wp_handle_upload($_FILES['thumb'], ['test_form' => false]);

				// If uploading success & No error
				if ($movefile && !isset($movefile['error'])) {
					$filename = $movefile['file'];
					$filetype = wp_check_filetype(basename($filename), null);
					$wp_upload_dir = wp_upload_dir();

					$attachment = array(
						'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
						'post_mime_type' => $filetype['type'],
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_status' => 'inherit',
					);

					// Adding file to media
					$attach_id = wp_insert_attachment($attachment, $filename);

					// If attachment success
					if ($attach_id) {
						require_once ABSPATH . 'wp-admin/includes/image.php';

						// Updating attachment metadata
						$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
						wp_update_attachment_metadata($attach_id, $attach_data);

						set_post_thumbnail($post_id, $attach_id);
						add_post_meta($post_id, 'products', $_REQUEST['ids']);
						wp_update_post(['ID' => $post_id, 'post_title' => 'Outfit ' . $post_id]);
					}

					$msg[] = 'Success';
				} else {
					$msg[] = $movefile['error'];
				}
			}
		}

		wp_send_json($msg);
	}
}

?>