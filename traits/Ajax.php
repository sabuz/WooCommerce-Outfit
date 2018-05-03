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
		check_ajax_referer('woo_outfit_nonce', 'security');

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

		$count = $this->toggle_post_like($_REQUEST['post_id']);
		update_post_meta($_REQUEST['post_id'], 'likes', $count);
		wp_send_json($count, 200);
	}

	function nopriv_ajax_post_like() {
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

		if ($_REQUEST['view']) {
			$author = $this->get_outfit_author_id($_REQUEST['view']);

			$content .= '<div class="modal-body clearfix">
				<div class="woo-outfit-modal-thumb">
					<img src="' . $this->get_outfit_thumbnail($_REQUEST['view']) . '" />

					' . ($_REQUEST['pagination'] ? '<a href="#" class="outfit-prev" data-id=""><span class="woo-outfit-icon woo-outfit-icon-angle-left"></span></a><a href="#" class="outfit-next" data-id=""><span class="woo-outfit-icon woo-outfit-icon-angle-right"></span></a>' : '') . '
				</div>

				<div class="woo-outfit-modal-details">
					<div class="woo-outfit-modal-author-data clearfix">
						<a class="outfit-author-name" href="#">
							' . ucwords(get_the_author_meta('display_name', $author)) . '
						</a>';

						$content .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="woo-outfit-modal-hooked-products owl-carousel">
						' . $this->modal_hooked_products($_REQUEST['view']) . '
					</div>

					<div class="woo-outfit-modal-footer-info">
						<span class="woo-outfit-meta-time">' . __('Added ', 'xim') . $this->outfit_posted_ago($_REQUEST['view']) . '</span>

						' . $this->like_button_html($_REQUEST['view']) . '
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

		if (!isset($_REQUEST['ids'])) {
			$response[] = __('Products are required.', 'xim');
		}

		if (!file_exists($_FILES['thumb']['tmp_name'])) {
			$response[] = __('Outfit photo is required.', 'xim');
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
						update_post_meta($post_id, 'products', $_REQUEST['ids']);
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