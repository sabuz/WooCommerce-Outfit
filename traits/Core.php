<?php

namespace Xim_Woo_Outfit\Traits;

trait Core {
	/**
	 * Load the required js files for this plugin.
	 *
	 * @since    1.0.0
	 */
	function wc_outfit_enqueue_script() {

		// Add new outfit
		wp_register_style('bootstrapValidator', plugin_dir_url(__FILE__) . '../css/bootstrapValidator.min.css');
		wp_register_script('bootstrapValidator', plugin_dir_url(__FILE__) . '../js/bootstrapValidator.min.js', array(), false, true);
		wp_register_script('filepicker', plugin_dir_url(__FILE__) . '../js/jquery.filepicker.js', array(), false, true);
		wp_register_script('new-outfit', plugin_dir_url(__FILE__) . '../js/new-outfit.js', array(), false, true);
		wp_localize_script('new-outfit', 'object', ['ajaxurl' => admin_url('admin-ajax.php')]);

		// Style Gallery
		wp_register_script('arctext', plugin_dir_url(__FILE__) . '../js/jquery.arctext.js', array(), false, true);
		wp_register_script('infinite', plugin_dir_url(__FILE__) . '../js/infinite.js', array(), false, true);
		wp_register_script('imgLoaded', plugin_dir_url(__FILE__) . '../js/imagesloaded.pkgd.min.js', array(), false, true);
		wp_register_script('isotope', plugin_dir_url(__FILE__) . '../js/isotope.pkgd.min.js', array(), false, true);
		wp_register_script('style-gallery', plugin_dir_url(__FILE__) . '../js/style-gallery.js', array(), false, true);
		wp_localize_script('style-gallery', 'object', ['ajaxurl' => admin_url('admin-ajax.php'), 'homeurl' => home_url()]);

		wp_enqueue_style('wc-bootstrap', plugin_dir_url(__FILE__) . '../css/bootstrap.css');
		wp_enqueue_style('owlCarousel', plugin_dir_url(__FILE__) . '../css/owl.carousel.css');
		wp_enqueue_script('wc-bootstrap', plugin_dir_url(__FILE__) . '../js/bootstrap.js');
		wp_enqueue_script('owlCarousel', plugin_dir_url(__FILE__) . '../js/owl.carousel.js');
		wp_enqueue_style('wc-style', plugin_dir_url(__FILE__) . '../css/style.css');
	}

/**
 * Plugins init hook.
 *
 * @since    1.0.0
 *
 */
	function wc_outfit_init($taxonomy) {
		add_rewrite_endpoint('outfit/new-outfit', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('outfit', EP_ROOT | EP_PAGES);

		// Register post type: outfit
		register_post_type('outfit',
			array(
				'labels' => array(
					'name' => __('Outfits'),
					'all_items' => __('All Outfits'),
					'singular_name' => __('Outfit'),
				),
				'public' => true,
				'exclude_from_search' => true,
				'supports' => array('title', 'author', 'thumbnail'),
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
 * Change page template - not finalized yet.
 *
 */
// function wc_outfit_page_template($page_template) {
	// if ( is_page( 'new-outfit' ) ) {
	//     $page_template = dirname( __FILE__ ) . '/page-template/new-outfit.php';
	// }

	// if (is_page('all-outfits')) {
	// 	$page_template = dirname(__FILE__) . '/page-template/page-outfit.php';
	// }

	// if (is_page('style-gallery-static')) {
	// 	$page_template = dirname(__FILE__) . '/page-template/page-style-gallery.php';
	// }

	// return $page_template;
	// }
	//add_filter('page_template', 'wc_outfit_page_template');

/**
 * Change body class -  not finalized yet.
 *
 */
	function wc_outfit_body_classes($classes) {
		if (is_page('new-outfit')) {
			$classes[] = 'couture-outfit';
		}

		if (is_page('style-gallery')) {
			$classes[] = 'couture-gallery';
		}

		return $classes;
	}

/**
 * Disable single view.
 *
 */
	function wc_outfit_disable_single() {
		$queried_post_type = get_query_var('post_type');
		if (is_single() && 'outfit' == $queried_post_type) {
			wp_redirect(home_url(), 301);
			exit;
		}
	}

/**
 * Remove custom post meta on delete.
 *
 */
	function wc_outfit_remove_cp_meta_on_delete($postid) {
		if (get_post_type($postid) == 'outfit') {
			wp_delete_attachment(get_post_thumbnail_id($postid), true);
			delete_post_meta($postid, 'featured');
			delete_post_meta($postid, 'products');
			delete_post_meta($postid, 'likes');
		}
	}

	// If WooCommerce not activated, throw error and deactive the plugin
	protected function throw_notice_and_deactive() {
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

	public function wk_new_menu_items($items) {
		$items['outfit'] = __('Outfit', 'xim');
		return $items;
	}

	public function endpoint_content() {
		echo do_shortcode('[all-outfit]');
		// https://gist.github.com/neilgee/13ac00c86c903c4ab30544b2b76c483c
	}

	public function sub_endpoint_content() {
		echo do_shortcode('[new-outfit]');
	}

	public function endpoint_title($title) {
		global $wp_query;

		$is_endpoint = isset($wp_query->query_vars['outfit']);

		if ($is_endpoint && in_the_loop()) {
			$title = 'Outfit';
		}

		return $title;
	}
}