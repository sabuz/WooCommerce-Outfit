<?php

namespace Xim_Woo_Outfit\Traits;

trait Core {

	/**
	 * Plugins init hook.
	 *
	 * @since    1.0.0
	 */
	function init($taxonomy) {
		// Add rewrite rules
		add_rewrite_endpoint($this->new_outfit_endpoint, EP_ROOT | EP_PAGES);
		add_rewrite_endpoint($this->all_outfit_endpoint, EP_ROOT | EP_PAGES);

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
				'taxonomies' => array('outfit_cats'),
			)
		);

		// Register custom taxonomy: outfit_cats.
		register_taxonomy(
			'outfit_cats',
			'outfit',
			array(
				'show_ui' => true,
				'show_tagcloud' => false,
				'hierarchical' => true,
				'rewrite' => array('slug' => 'style-gallery/' . $taxonomy),
				'show_admin_column' => true,
			)
		);
	}

	/**
	 * Load the required assets for this plugin.
	 *
	 * @since    1.0.0
	 */
	function enqueue_scripts() {
		// Add new outfit
		wp_register_style('bootstrapValidator', plugin_dir_url(__FILE__) . '../css/bootstrapValidator.min.css');
		wp_register_script('bootstrapValidator', plugin_dir_url(__FILE__) . '../js/bootstrapValidator.min.js', array(), false, true);
		wp_register_script('filepicker', plugin_dir_url(__FILE__) . '../js/jquery.filepicker.js', array(), false, true);
		wp_register_script('new-outfit', plugin_dir_url(__FILE__) . '../js/new-outfit.js', array(), false, true);
		wp_localize_script('new-outfit', 'object', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('wc_outfit_nonce')));

		// Style Gallery
		wp_register_script('single-product', plugin_dir_url(__FILE__) . '../js/single-product.js', array(), false, true);
		wp_register_script('arctext', plugin_dir_url(__FILE__) . '../js/jquery.arctext.js', array(), false, true);
		wp_register_script('infinite', plugin_dir_url(__FILE__) . '../js/infinite.js', array(), false, true);
		wp_register_script('imgLoaded', plugin_dir_url(__FILE__) . '../js/imagesloaded.pkgd.min.js', array(), false, true);
		wp_register_script('isotope', plugin_dir_url(__FILE__) . '../js/isotope.pkgd.min.js', array(), false, true);
		wp_register_script('style-gallery', plugin_dir_url(__FILE__) . '../js/style-gallery.js', array(), false, true);
		wp_localize_script('single-product', 'object', array('ajaxurl' => admin_url('admin-ajax.php'), 'homeurl' => home_url(), 'nonce' => wp_create_nonce('wc_outfit_nonce')));
		wp_localize_script('style-gallery', 'object', array('ajaxurl' => admin_url('admin-ajax.php'), 'homeurl' => home_url(), 'nonce' => wp_create_nonce('wc_outfit_nonce')));

		wp_enqueue_style('wc-bootstrap', plugin_dir_url(__FILE__) . '../css/bootstrap.css');
		wp_enqueue_style('owlCarousel', plugin_dir_url(__FILE__) . '../css/owl.carousel.css');
		wp_enqueue_script('wc-bootstrap', plugin_dir_url(__FILE__) . '../js/bootstrap.js');
		wp_enqueue_script('owlCarousel', plugin_dir_url(__FILE__) . '../js/owl.carousel.js');
		wp_enqueue_style('wc-style', plugin_dir_url(__FILE__) . '../css/style.css');
	}

	/**
	 * Disable single outfit view.
	 *
	 * @since    1.0.0
	 */
	function disable_single_outfit_view() {
		if (is_single() && get_query_var('post_type') == 'outfit') {
			wp_redirect(home_url('404'), 301);
			exit;
		}
	}

	/**
	 * Remove single outfit permalink html.
	 *
	 * @since    1.0.0
	 */
	function remove_sample_permalink_html($permalink) {
		return;
	}

	/**
	 * Remove outfit quick view.
	 *
	 * @since    1.0.0
	 */
	function filter_post_row_actions($actions, $post) {
		if ($post->post_type == 'outfit') {
			unset($actions['view']);
		}

		return $actions;
	}

	/**
	 * Remove outfit post data on delete.
	 *
	 * @since    1.0.0
	 */
	function remove_post_data_on_delete($post_id) {
		if (get_post_type($post_id) == 'outfit') {
			// delete post attachment
			wp_delete_attachment(get_post_thumbnail_id($post_id), true);

			// delete post meta
			delete_post_meta($post_id, 'featured');
			delete_post_meta($post_id, 'products');
			delete_post_meta($post_id, 'likes');
		}
	}

	/**
	 * If WooCommerce is not activated, throw error and deactive the plugin
	 *
	 * @since    1.0.0
	 */
	function throw_notice_and_deactive() {
		if (!class_exists('WooCommerce')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			// Deactivate the plugin
			deactivate_plugins(plugin_basename(__FILE__));

			// Remove success notice
			unset($_GET['activate']);

			// Throw an error in the wordpress admin
			add_action('admin_notices', function () {
				$class = 'notice notice-error is-dismissible';
				$message = __('<strong>WooCommerce Outfit</strong> requires <strong>WooCommerce</strong> plugin to be installed and activated.', 'xim');
				printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
			});
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

	function filter_body_class($classes) {
		global $post;

		if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'style-gallery')) {
			$classes[] = 'outfit-gallery';
		}

		return $classes;
	}

	function inject_footer_content() {
		if (is_product()) {
			echo '<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">

					</div>
				</div>
			</div>';
		}
	}

	/**
	 * This filter insures users only see their own media
	 */
	function filter_media($query) {
		// admins get to see everything
		if (!current_user_can('manage_options')) {
			$query['author'] = get_current_user_id();
		}

		return $query;
	}

}