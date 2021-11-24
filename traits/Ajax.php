<?php

namespace Woocommerce_Outfit\Traits;

use WP_Query;

trait Ajax {
	/**
	 * Get products list by category.
	 *
	 * @since    1.0.0
	 */
	function ajax_get_products_by_cat() {
		check_ajax_referer('woo_outfit_nonce', 'security');

		$json = array('products' => array());
		$user = wp_get_current_user();
		$term = get_term(intval($_POST['cat']), 'product_cat');
		$data = new WP_Query(array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => 12,
			'paged' => intval($_POST['page']),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => intval($_POST['cat']),
					'operator' => 'IN',
				),
			),
			'fields' => 'ids',
		));

		$json['term'] = array('count' => $term->count, 'next' => (intval($_POST['page']) * 12 >= $term->count ? false : true));

		if ($data->posts) {
			foreach ($data->posts as $key => $id) {
				if ((get_option('woo-outfit-bought-only', 'on') && !wc_customer_bought_product($user->user_email, $user->ID, $id))) {
					continue;
				}

				$product = wc_get_product($id);

				$json['products'][$key]['id'] = $id;
				$json['products'][$key]['title'] = $product->get_title();
				$json['products'][$key]['thumb'] = $this->get_outfit_thumbnail($id, 'product-thumb');
				$json['products'][$key]['price_html'] = $product->get_price_html();
			}
		}

		wp_send_json($json, 200);
	}

	function nopriv_ajax_get_products_by_cat() {
		wp_send_json(array(), 401);
	}

	/**
	 * Toggle post like.
	 *
	 * @since    1.0.0
	 */
	function ajax_post_like() {
		check_ajax_referer('woo_outfit_nonce', 'security');

		$post_id = intval($_POST['post_id']);
		$count = $this->toggle_post_like($post_id);

		update_post_meta($post_id, 'likes', $count);
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
		check_ajax_referer('woo_outfit_nonce', 'security');

		wp_send_json($this->toggle_follow_profile(intval($_POST['user_id'])), 200);
	}

	function nopriv_ajax_follow_people() {
		wp_send_json(get_permalink(get_option('woocommerce_myaccount_page_id')), 401);
	}

	/**
	 * Single outfit modal data.
	 *
	 * @since    1.0.0
	 */
	function ajax_outfit_modal() {
		check_ajax_referer('woo_outfit_nonce', 'security');

		$content = '';
		$view = isset($_GET['view']) ? intval($_GET['view']) : null;
		$pagination = isset($_GET['pagination']) ? boolval($_GET['pagination']) : false;

		if ($view) {
			$author = $this->get_outfit_author_id($view);

			$content .= '<div class="modal-body clearfix">
				<div class="woo-outfit-modal-thumb">
					<img src="' . $this->get_outfit_thumbnail($view) . '" />

					' . ( $pagination ? '<a href="#" class="outfit-prev" data-id=""><span class="woo-outfit-icon woo-outfit-icon-angle-left"></span></a><a href="#" class="outfit-next" data-id=""><span class="woo-outfit-icon woo-outfit-icon-angle-right"></span></a>' : '') . '
				</div>

				<div class="woo-outfit-modal-details">
					<div class="woo-outfit-modal-author-data clearfix">
						<a class="outfit-author-name" href="' . $this->get_user_gallery_link($author) . '">
							' . ucwords(get_the_author_meta('display_name', $author)) . '
						</a>';

						if ($author != get_current_user_id()) {
							$content .= '<a href="#" class="woo-outfit-follow-btn" data-id="' . $author . '">' . ($this->is_following($author) ? __('Unfollow', 'woo-outfit') : __('Follow', 'woo-outfit')) . '</a>';
						}

						$content .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="woo-outfit-modal-hooked-products owl-carousel">
						' . $this->modal_hooked_products($view) . '
					</div>

					' . $this->modal_tags($view) . '

					<div class="woo-outfit-modal-footer-info">
						<span class="woo-outfit-meta-time">' . __('Added ', 'woo-outfit') . $this->outfit_posted_ago($view) . '</span>
						' . $this->like_button_html($view) . '
						' . $this->share_buttons_html($view) . '
					</div>
				</div>
			</div>';
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
		check_ajax_referer('woo_outfit_nonce', 'security');

		$content = '';
		$paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
		$tag = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';
		$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
		$user = isset($_GET['user']) ? intval($_GET['user']) : null;

		if (!empty($user)) {
			if (!empty($page) && $page == 'likes') {
				$ids = $this->get_liked_outfits($user);

				if (empty($ids)) {
					$args = array();
				} else {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('woo-outfit-ppq', 9),
						'order' => 'desc',
						'post__in' => $ids,
						'paged' => $paged,
					);
				}
			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => get_option('woo-outfit-ppq', 9),
					'order' => 'desc',
					'author' => $user,
					'paged' => $paged,
				);
			}
		} elseif (!empty($tag)) {
			$args = array(
				'post_type' => 'outfit',
				'post_status' => 'publish',
				'posts_per_page' => get_option('woo-outfit-ppq', 9),
				'order' => 'desc',
				'tax_query' => array(
					array(
						'taxonomy' => 'outfit_tags',
						'field' => 'slug',
						'terms' => $tag,
					),
				),
				'paged' => $paged,
			);
		} else {
			if (!empty($page)) {
				if ($page == 'feat') {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('woo-outfit-ppq', 9),
						'order' => 'desc',
						'meta_query' => array(
							array(
								'key' => 'featured',
								'value' => 'yes',
							),
						),
						'paged' => $paged,
					);
				} elseif ($page == 'following') {
					if (is_user_logged_in()) {
						$data = $this->get_followings(get_current_user_id());

						if (!empty($data)) {
							$args = array(
								'post_type' => 'outfit',
								'post_status' => 'publish',
								'posts_per_page' => get_option('woo-outfit-ppq', 9),
								'order' => 'desc',
								'author__in' => $data,
								'paged' => $paged,
							);
						}
					} else {
						$args = array();
					}
				}
			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => get_option('woo-outfit-ppq', 9),
					'order' => 'desc',
					'paged' => $paged,
				);
			}
		}

		$query = new WP_Query($args);

		while ($query->have_posts()) {
			$query->the_post();
			$image = $this->get_outfit_thumbnail($query->post->ID, 'woo-outfit-style-gallery', false);
			
			$content .= '<div class="woo-outfit-gallery-item col-sm-4" data-id="' . $query->post->ID . '">
				<div class="woo-outfit-gallery-item-inner-wrap">
					<div class="woo-outfit-gallery-item-thumb-wrap" data-width="' . $image[1] . '" data-height="' . $image[2] . '">
						<img src="' . $image[0] . '" class="woo-outfit-gallery-item-thumb" />
					</div>

					<div class="woo-outfit-gallery-item-footer clearfix">
						<div class="pull-left">
							<a class="woo-outfit-meta-author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
								' . ucwords(get_the_author_meta('display_name')) . '</a>
							<p class="woo-outfit-meta-time">' . $this->outfit_posted_ago() . '</p>
						</div>
						<div class="pull-right">
							' . $this->like_button_html($query->post->ID) . '
						</div>
					</div>
				</div>
			</div>';
		}
		
		echo $content;
		die();
	}

	/**
	 * Submit new outfit.
	 *
	 * @since    1.0.0
	 */
	function ajax_post_outfit() {
		check_ajax_referer('woo_outfit_nonce', 'security');
		
		$response = array();

		if (!isset($_POST['ids'])) {
			$response[] = __('Products are required.', 'woo-outfit');
		}

		if (!file_exists($_FILES['thumb']['tmp_name'])) {
			$response[] = __('Outfit photo is required.', 'woo-outfit');
		}

		if (count($response) == 0) {
			$args = array(
				'post_title' => '',
				'post_type' => 'outfit',
				'post_status' => (get_option('woo-outfit-verify-submission', 'on') ? 'pending' : 'publish')
			);

			$post_id = wp_insert_post($args);

			if ($post_id) {
				// Including file library if not exist
				if (!function_exists('wp_handle_upload')) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				// Uploading file to server
				$movefile = wp_handle_upload($_FILES['thumb'], ['test_form' => false]);

				// If uploading success & no error
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
					$attach_id = wp_insert_attachment($attachment, $filename, $post_id);

					// If attachment success
					if ($attach_id) {
						require_once ABSPATH . 'wp-admin/includes/image.php';

						// Updating attachment metadata
						$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
						wp_update_attachment_metadata($attach_id, $attach_data);

						set_post_thumbnail($post_id, $attach_id);
						wp_set_object_terms($post_id, (array) $_POST['tags'], 'outfit_tags');
						update_post_meta($post_id, 'products', strval($_POST['ids']));
						wp_update_post(['ID' => $post_id, 'post_title' => 'Outfit ' . $post_id]);
					}

					$response['status'] = 'success';
				} else {
					$response['status'] = $movefile['error'];
				}
			} else {
				$response['status'] = 'success';
			}
		}

		wp_send_json($response, 200);
	}
}

?>