<?php

namespace Xim_Woo_Outfit\Traits;

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
		if (get_transient('wc_outfit_flush_rewrite_rules_flag') == true) {
			flush_rewrite_rules();
			set_transient('wc_outfit_flush_rewrite_rules_flag', false, 86400);
		}

		// Register post type: outfit
		register_post_type('outfit',
			array(
				'labels' => array(
					'name' => __('Outfits', 'xim'),
					'singular_name' => __('Outfit', 'xim'),
					'add_new_item' => __('Add New Outfit', 'xim'),
					'new_item' => __('New Outfit', 'xim'),
					'edit_item' => __('Edit Outfit', 'xim'),
					'view_item' => __('View Outfit', 'xim'),
					'all_items' => __('All Outfits', 'xim'),
					'search_items' => __('Search Outfits', 'xim'),
					'not_found' => __('No Outfits found.', 'xim'),
					'not_found_in_trash' => __('No Outfits found in Trash.', 'xim'),
				),
				'public' => true,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'rewrite' => false,
				'supports' => array('title', 'author', 'thumbnail'),
				'taxonomies' => array('outfit_tags'),
				'menu_icon' => 'dashicons-camera',
			)
		);

		// Add image size
		add_image_size('wc-outfit-single-listing', 300, 320, true);
	}

	/**
	 * Load the required assets for this plugin.
	 *
	 * @since    1.0.0
	 */
	function enqueue_scripts() {
		// style
		wp_register_style('wc-outfit-icon', plugin_dir_url(__FILE__) . '../assets/css/wc-outfit-icon.css');
		wp_register_style('bootstrap', plugin_dir_url(__FILE__) . '../assets/css/bootstrap.min.css');
		wp_register_style('owl-carousel', plugin_dir_url(__FILE__) . '../assets/css/owl.carousel.min.css');
		wp_register_style('select2', plugin_dir_url(__FILE__) . '../assets/css/select2.min.css');

		wp_register_style('new-outfit', plugin_dir_url(__FILE__) . '../assets/css/new-outfit.css');
		wp_register_style('outfit-modal', plugin_dir_url(__FILE__) . '../assets/css/modal.css');
		wp_register_style('single-product', plugin_dir_url(__FILE__) . '../assets/css/single-product.css');

		// script
		wp_register_script('bootstrap', plugin_dir_url(__FILE__) . '../assets/js/bootstrap.min.js', array(), false, true);
		wp_register_script('jquery-validate', plugin_dir_url(__FILE__) . '../assets/js/jquery.validate.min.js', array(), false, true);
		wp_register_script('owl-carousel', plugin_dir_url(__FILE__) . '../assets/js/owl.carousel.min.js', array(), false, true);
		wp_register_script('select2', plugin_dir_url(__FILE__) . '../assets/js/select2.min.js', array(), false, true);

		wp_register_script('new-outfit', plugin_dir_url(__FILE__) . '../assets/js/new-outfit.js', array(), false, true);
		wp_register_script('single-product', plugin_dir_url(__FILE__) . '../assets/js/single-product.js', array(), false, true);

		// localize
		wp_localize_script('new-outfit', 'wc_outfit_tr_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'outfits_url' => wc_get_endpoint_url('outfits'),
			'nonce' => wp_create_nonce('wc_outfit_nonce'),
			'thumb_req' => __('Outfit photo is required', 'xim'),
			'invalid_thumb' => __('Choose a valid JPG/JPEG/PNG file', 'xim'),
			'size_exceed' => sprintf(__("File size must be less than %d MB", 'xim'), ini_get('upload_max_filesize')),
			'ids_req' => __('Products are required', 'xim'),
			'select_placeholder' => __('Select a category', 'xim'),
			'upload_limit' => ini_get('upload_max_filesize'),
		));

		wp_localize_script('single-product', 'wc_outfit_tr_obj', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'myaccount_url' => get_permalink(get_option('woocommerce_myaccount_page_id')),
			'nonce' => wp_create_nonce('wc_outfit_nonce'),
			'num_items' => get_option('wc-outfit-single-num-item', 4),
		));
	}

	/**
	 * Remove outfit attachment on delete.
	 *
	 * @since    1.0.0
	 */
	function before_delete_post($post_id) {
		if (get_option('wc-outfit-cleanup-gallery', 'on') && get_post_type($post_id) == 'outfit') {
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
		$items = array_splice($items, 0, count($items) - 1) + array($this->all_outfit_endpoint => __('Outfits', 'xim')) + $items;
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
			$title = __('Outfits', 'xim');
		} elseif (isset($wp_query->query_vars[$this->new_outfit_endpoint]) && in_the_loop()) {
			$title = __('Add New Outfit', 'xim');
		}

		return $title;
	}

	/**
	 * wp_footer hook
	 *
	 * @since    1.0.0
	 */
	function wp_footer() {
		global $post;

		if (is_product()) {
			echo '<div class="modal" id="wc-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">

					</div>
				</div>
			</div>';
		}
	}
}