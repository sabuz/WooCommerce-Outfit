<?php

namespace Woocommerce_Outfit\Traits;

trait Core {
	/**
	 * Plugins init hook.
	 *
	 * @since    1.0.0
	 */
	function init() {
		// Add rewrite rules
		add_rewrite_endpoint($this->new_outfit_endpoint, EP_ROOT | EP_PAGES);
		add_rewrite_endpoint($this->all_outfit_endpoint, EP_ROOT | EP_PAGES);

		// flush rewrite rules
		if (get_transient('woo_outfit_flush_rewrite_rules_flag') == true) {
			flush_rewrite_rules();
			set_transient('woo_outfit_flush_rewrite_rules_flag', false, 86400);
		}

		// Set required variable
		$this->style_gallery_url = get_the_permalink(get_option('woo-outfit-page-id'));

		// Register post type: outfit
		register_post_type('outfit',
			array(
				'labels' => array(
					'name' => __('Outfits', 'woo-outfit'),
					'singular_name' => __('Outfit', 'woo-outfit'),
					'add_new_item' => __('Add New Outfit', 'woo-outfit'),
					'new_item' => __('New Outfit', 'woo-outfit'),
					'edit_item' => __('Edit Outfit', 'woo-outfit'),
					'view_item' => __('View Outfit', 'woo-outfit'),
					'all_items' => __('All Outfits', 'woo-outfit'),
					'search_items' => __('Search Outfits', 'woo-outfit'),
					'not_found' => __('No Outfits found.', 'woo-outfit'),
					'not_found_in_trash' => __('No Outfits found in Trash.', 'woo-outfit'),
				),
				'public' => true,
				'exclude_from_search' => true,
				'rewrite' => false,
				'supports' => array('title', 'author', 'thumbnail'),
				'taxonomies' => array('outfit_tags'),
				'menu_icon' => 'dashicons-camera',
			)
		);

		// Register custom taxonomy: outfit_tags.
		if (get_option('woo-outfit-tagging', 'on')) {
			register_taxonomy(
				'outfit_tags',
				'outfit',
				array(
					'show_ui' => true,
					'show_tagcloud' => true,
					'rewrite' => false,
					'show_admin_column' => true,
				)
			);
		}

		// Add image size
		add_image_size('woo-outfit-single-listing', 300, 320, true);
		add_image_size('woo-outfit-style-gallery', 660, 9999, false);
	}

	/**
	 * Load the required assets for this plugin.
	 *
	 * @since    1.0.0
	 */
	function enqueue_scripts() {
		// style
		wp_register_style('woo-outfit-icon', plugin_dir_url(__FILE__) . '../assets/css/woo-outfit-icon.css');
		wp_register_style('bootstrap', plugin_dir_url(__FILE__) . '../assets/css/bootstrap.min.css');
		wp_register_style('owl-carousel', plugin_dir_url(__FILE__) . '../assets/css/owl.carousel.min.css');
		wp_register_style('select2', plugin_dir_url(__FILE__) . '../assets/css/select2.min.css');

		wp_register_style('new-outfit', plugin_dir_url(__FILE__) . '../assets/css/new-outfit.css');
		wp_register_style('outfit-modal', plugin_dir_url(__FILE__) . '../assets/css/modal.css');
		wp_register_style('single-product', plugin_dir_url(__FILE__) . '../assets/css/single-product.css');
		wp_register_style('style-gallery', plugin_dir_url(__FILE__) . '../assets/css/style-gallery.css');

		// script
		wp_register_script('bootstrap', plugin_dir_url(__FILE__) . '../assets/js/bootstrap.min.js', array(), false, true);
		wp_register_script('jquery-validate', plugin_dir_url(__FILE__) . '../assets/js/jquery.validate.min.js', array(), false, true);
		wp_register_script('owl-carousel', plugin_dir_url(__FILE__) . '../assets/js/owl.carousel.min.js', array(), false, true);
		wp_register_script('select2', plugin_dir_url(__FILE__) . '../assets/js/select2.min.js', array(), false, true);
		wp_register_script('isotope', plugin_dir_url(__FILE__) . '../assets/js/isotope.pkgd.min.js', array(), false, true);

		wp_register_script('new-outfit', plugin_dir_url(__FILE__) . '../assets/js/new-outfit.js', array(), false, true);
		wp_register_script('single-product', plugin_dir_url(__FILE__) . '../assets/js/single-product.js', array(), false, true);
		wp_register_script('style-gallery', plugin_dir_url(__FILE__) . '../assets/js/style-gallery.js', array(), false, true);

		// localize
		wp_localize_script('new-outfit', 'woo_outfit_tr_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'outfits_url' => wc_get_endpoint_url('outfits'),
			'nonce' => wp_create_nonce('woo_outfit_nonce'),
			'thumb_req' => __('Outfit photo is required', 'woo-outfit'),
			'invalid_thumb' => __('Choose a valid JPG/JPEG/PNG file', 'woo-outfit'),
			'size_exceed' => sprintf(__("File size must be less than %d MB", 'woo-outfit'), ini_get('upload_max_filesize')),
			'ids_req' => __('Products are required', 'woo-outfit'),
			'select_placeholder' => __('Select a category', 'woo-outfit'),
			'upload_limit' => ini_get('upload_max_filesize'),
		));

		wp_localize_script('single-product', 'woo_outfit_tr_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'myaccount_url' => get_permalink(get_option('woocommerce_myaccount_page_id')),
			'nonce' => wp_create_nonce('woo_outfit_nonce'),
			'num_items' => get_option('woo-outfit-single-num-item', 4),
		));

		wp_localize_script('style-gallery', 'woo_outfit_tr_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'home_url' => home_url(),
			'style_gallery_url' => $this->style_gallery_url,
			'myaccount_url' => get_permalink(get_option('woocommerce_myaccount_page_id')),
			'nonce' => wp_create_nonce('woo_outfit_nonce'),
		));
	}

	/**
	 * Filter outfit permalink.
	 *
	 * @since    1.0.0
	 */
	function filter_post_type_link($url, $post) {
		if ($post->post_type == 'outfit') {
			return add_query_arg('view', $post->ID, $this->style_gallery_url);
		}

		return $url;
	}

	/**
	 * Filter outfit tags permalink.
	 *
	 * @since    1.0.0
	 */
	function filter_term_link($url, $term, $taxonomy) {
		if ($taxonomy == 'outfit_tags') {
			return add_query_arg('tags', $term->slug, $this->style_gallery_url);
		}

		return $url;
	}

	/**
	 * Remove outfit attachment on delete.
	 *
	 * @since    1.0.0
	 */
	function before_delete_post($post_id) {
		if (get_option('woo-outfit-cleanup-gallery', 'on') && get_post_type($post_id) == 'outfit') {
			// delete post attachment
			wp_delete_attachment(get_post_thumbnail_id($post_id), true);
		}
	}

	/**
	 * Add new outfit menu on woocommerce customer dashboard page.
	 *
	 * @since    1.0.0
	 */
	function myaccount_menu_items($items) {
		$items = array_splice($items, 0, count($items) - 1) + array($this->all_outfit_endpoint => __('Outfits', 'woo-outfit')) + $items;
		return $items;
	}

	/**
	 * Generate outfits endpoint content.
	 *
	 * @since    1.0.0
	 */
	function outfits_endpoint_content() {
		echo do_shortcode('[outfits]');
	}

	/**
	 * Generate new-outfit endpoint content.
	 *
	 * @since    1.0.0
	 */
	function new_outfit_endpoint_content() {
		echo do_shortcode('[new-outfit]');
	}

	/**
	 * Generate title for custom endpoints.
	 *
	 * @since    1.0.0
	 */
	function filter_endpoints_title($title) {
		global $wp_query;

		if (isset($wp_query->query_vars[$this->all_outfit_endpoint]) && in_the_loop()) {
			$title = __('Outfits', 'woo-outfit');
		} elseif (isset($wp_query->query_vars[$this->new_outfit_endpoint]) && in_the_loop()) {
			$title = __('Add New Outfit', 'woo-outfit');
		}

		return $title;
	}

	/**
	 * wp_head hook
	 *
	 * @since    1.0.0
	 */
	function wp_head() {
		global $post;

		if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'style-gallery')) {
			if (isset($_GET['view'])) {
				$view = intval($_GET['view']);
				echo '<meta property="fb:app_id" content="' . get_option('woo-outfit-fb-app-id') . '" />
				<meta property="og:url" content="' . get_the_permalink() . '?view=' . $view . '" />
				<meta property="og:type" content="website" />
				<meta property="og:title" content="' . get_the_title($view) . '" />
				<meta property="og:description" content="" />
				<meta property="og:image" content="' . $this->get_outfit_thumbnail($view, 'product-thumb') . '" />';
			}
		}
	}

	/**
	 * wp_footer hook
	 *
	 * @since    1.0.0
	 */
	function wp_footer() {
		global $post;

		if (is_product()) {
			echo '<div class="modal" id="woo-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">

					</div>
				</div>
			</div>';
		}

		if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'style-gallery')) {
			// Outfit Modal
			if (isset($_GET['view'])) {
				$view = intval($_GET['view']);
				$author = intval($this->get_outfit_author_id($view));

				echo '<div class="modal" id="woo-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
							<div class="modal-body clearfix">
								<div class="woo-outfit-modal-thumb">
									<img src="' . $this->get_outfit_thumbnail($view) . '" />
								</div>

								<div class="woo-outfit-modal-details">
									<div class="woo-outfit-modal-author-data clearfix">
										<a class="outfit-author-name" href="' . $this->get_user_gallery_link($author) . '">
											' . ucwords(get_the_author_meta('display_name', $author)) . '
										</a>';

										if ($author != get_current_user_id()) {
											echo '<a href="#" class="woo-outfit-follow-btn" data-id="' . $author . '">' . ($this->is_following($author) ? esc_html__('Unfollow', 'woo-outfit') : esc_html__('Follow', 'woo-outfit')) . '</a>';
										}

										echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>

									<div class="woo-outfit-modal-hooked-products owl-carousel">
										' . $this->modal_hooked_products($view) . '
									</div>

									' . $this->modal_tags($view) . '

									<div class="woo-outfit-modal-footer-info">
										<span class="woo-outfit-meta-time">' . esc_html__('Added ', 'woo-outfit') . $this->outfit_posted_ago($view) . '</span>

										' . $this->like_button_html($view) . '
										' . $this->share_buttons_html($view) . '
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery(\'#woo-outfit-modal\').modal({
							backdrop: \'static\',
							show: true
						});

						jQuery("#woo-outfit-modal .woo-outfit-modal-hooked-products").owlCarousel({
							items: 2,
							margin: 10,
							nav:true,
							navText: [\'<span class="woo-outfit-icon woo-outfit-icon-angle-left">\', \'<span class="woo-outfit-icon woo-outfit-icon-angle-right">\']
						});
					})
				</script>';
			} else {
				echo '<div class="modal" id="woo-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">

						</div>
					</div>
				</div>';
			}
		}
	}

	/**
	 * Install style-gallery page on activation
	 *
	 * @since    1.0.0
	 */
	function install_pages() {
		$post = get_post(get_option('woo-outfit-page-id'));

		if (empty($post)) {
			$post_id = wp_insert_post(array(
				'post_title' => __('Style Gallery', 'woo-outfit'),
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_content' => '[style-gallery]',
			));

			update_option('woo-outfit-page-id', $post_id);
		} else if ($post->post_status == 'trash') {
			wp_untrash_post($post->ID);
		}
	}

}