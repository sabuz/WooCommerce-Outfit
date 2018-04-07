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
			set_transient('wc_outfit_flush_rewrite_rules_flag', false, 604800);
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
				'exclude_from_search' => true,
				'rewrite' => false,
				'supports' => array('title', 'author', 'thumbnail'),
				'taxonomies' => array('outfit_tags'),
				'menu_icon' => 'dashicons-camera',
			)
		);

		// Register custom taxonomy: outfit_tags.
		if (get_option('wc-outfit-tagging', 'on')) {
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

		// Allow customer to upload files
		$role = get_role('customer');
		$role->add_cap('upload_files');
	}

	function filter_post_type_link($url, $post) {
		if (get_post_type($post) == 'outfit') {
			return add_query_arg('view', $post->ID, get_the_permalink(get_option('wc-outfit-page-id')));
		}

		return $url;
	}

	function filter_term_link($url, $term, $taxonomy) {
		if ($taxonomy == 'outfit_tags') {
			return add_query_arg('tags', $term->slug, get_the_permalink(get_option('wc-outfit-page-id')));
		}

		return $url;
	}

	/**
	 * Load the required assets for this plugin.
	 *
	 * @since    1.0.0
	 */
	function enqueue_scripts() {
		// style
		wp_register_style('bootstrap', plugin_dir_url(__FILE__) . '../assets/css/bootstrap.min.css');
		wp_register_style('bootstrap-validator', plugin_dir_url(__FILE__) . '../assets/css/bootstrapValidator.min.css');
		wp_register_style('owlCarousel', plugin_dir_url(__FILE__) . '../assets/css/owl.carousel.css');
		wp_register_style('select2', plugin_dir_url(__FILE__) . '../assets/css/select2.min.css');
		
		wp_register_style('new-outfit', plugin_dir_url(__FILE__) . '../assets/css/new-outfit.css');
		wp_register_style('outfit-modal', plugin_dir_url(__FILE__) . '../assets/css/modal.css');
		wp_register_style('single-product', plugin_dir_url(__FILE__) . '../assets/css/single-product.css');
		wp_register_style('style-gallery', plugin_dir_url(__FILE__) . '../assets/css/style-gallery.css');
		
		// script
		wp_register_script('bootstrap', plugin_dir_url(__FILE__) . '../assets/js/bootstrap.min.js', array(), false, true);
		wp_register_script('bootstrap-validator', plugin_dir_url(__FILE__) . '../assets/js/bootstrapValidator.min.js', array(), false, true);
		wp_register_script('owlCarousel', plugin_dir_url(__FILE__) . '../assets/js/owl.carousel.js', array(), false, true);
		wp_register_script('select2', plugin_dir_url(__FILE__) . '../assets/js/select2.min.js', array(), false, true);
		wp_register_script('imgLoaded', plugin_dir_url(__FILE__) . '../assets/js/imagesloaded.pkgd.min.js', array(), false, true);
		wp_register_script('isotope', plugin_dir_url(__FILE__) . '../assets/js/isotope.pkgd.min.js', array(), false, true);
		
		wp_register_script('new-outfit', plugin_dir_url(__FILE__) . '../assets/js/new-outfit.js', array(), false, true);
		wp_register_script('single-product', plugin_dir_url(__FILE__) . '../assets/js/single-product.js', array(), false, true);
		wp_register_script('style-gallery', plugin_dir_url(__FILE__) . '../assets/js/style-gallery.js', array(), false, true);

		// localize
		wp_localize_script('new-outfit', 'object', array('ajaxurl' => admin_url('admin-ajax.php'), 'myaccount_url' => get_permalink(get_option('woocommerce_myaccount_page_id')), 'nonce' => wp_create_nonce('wc_outfit_nonce')));
		wp_localize_script('single-product', 'object', array('ajaxurl' => admin_url('admin-ajax.php'), 'homeurl' => home_url(), 'nonce' => wp_create_nonce('wc_outfit_nonce')));
		wp_localize_script('style-gallery', 'object', array('ajax_url' => admin_url('admin-ajax.php'), 'home_url' => home_url(), 'style_gallery_url' => get_the_permalink(get_option('wc-outfit-page-id')), 'myaccount_url' => get_permalink(get_option('woocommerce_myaccount_page_id')), 'nonce' => wp_create_nonce('wc_outfit_nonce')));
	}

	/**
	 * Remove outfit post data on delete.
	 *
	 * @since    1.0.0
	 */
	function before_delete_post($post_id) {
		if (get_post_type($post_id) == 'outfit') {
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
			$title = 'Outfits';
		} elseif (isset($wp_query->query_vars[$this->new_outfit_endpoint]) && in_the_loop()) {
			$title = 'Add New Outfit';
		}

		return $title;
	}

	/**
	 * Push class to body on style gallery page.
	 *
	 * @since    1.0.0
	 */
	function filter_body_class($classes) {
		global $post;

		// if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'style-gallery')) {
		// 	$classes[] = 'wc-outfit-gallery';
		// }

		return $classes;
	}

	/**
	 * Ensure users only see their own media
	 *
	 * @since    1.0.0
	 */
	function ajax_query_attachments_args($query) {
		// admins get to see everything
		if (!current_user_can('manage_options')) {
			$query['author'] = get_current_user_id();
		}

		return $query;
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
				echo '<meta property="fb:app_id" content="' . get_option('wc-outfit-fb-app-id') . '" />
				<meta property="og:url" content="' . get_the_permalink() . '?view=' . $_GET['view'] . '" />
				<meta property="og:type" content="website" />
				<meta property="og:title" content="' . get_the_title($_GET['view']) . '" />
				<meta property="og:description" content="" />
				<meta property="og:image" content="' . $this->get_outfit_thumbnail($_GET['view'], 'product-thumb') . '" />';
			}
		}
	}

	/**
	 * wp_footer hook
	 *
	 * @since    1.0.0
	 */
	function wp_footer() {
		if (is_product()) {
			echo '<div class="modal" id="wc-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">

					</div>
				</div>
			</div>';
		}
	}

	/**
	 * Install style-gallery page on activation
	 *
	 * @since    1.0.0
	 */
	function install_pages() {
		$post_id = null;
		$post = get_post(get_option('wc-outfit-page-id'));

		if (empty($post)) {
			$post_id = wp_insert_post(array(
				'post_title' => __('Style Gallery', 'xim'),
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_content' => '[style-gallery]',
			));
		} else if ($post->post_status == 'trash') {
			wp_untrash_post($post->ID);

			$post_id = $post->ID;
		} else {
			$post_id = $post->ID;
		}

		if (!empty($post_id)) {
			update_option('wc-outfit-page-id', $post_id);
		}
	}

}