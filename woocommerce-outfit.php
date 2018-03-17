<?php

/**
 * @package           Xim_Woo_Outfit
 * @version           1.0.0
 * @link              https://ximdevs.com/
 *
 * Plugin Name:       WooCommerce Outfit
 * Description:       WooCommerce Outfit enables your customers to submit outfit photos.
 * Version:           1.0.0
 * Author:            XimDevs
 * Author URI:        https://ximdevs.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Including composer autoload.
 *
 * @since    1.0.0
 */
require_once 'vendor/autoload.php';

/**
 * Activation Class - fire once while activating the plugin.
 *
 * @since    1.0.0
 */
class Xim_Woo_Outfit_Activation {
	use Xim_Woo_Outfit\Traits\Database;
	use Xim_Woo_Outfit\Traits\Core;

	function __construct() {
		// install database
		$this->install_db();

		// install pages
		$this->install_pages();

		// set flush rewrite flag enabled
		set_transient('wc_outfit_flush_rewrite_rules_flag', true, 604800);
	}
}

register_activation_hook(__FILE__, function () {
	/**
	 * If WooCommerce is activated, initiate the activation class
	 *
	 * @since    1.0.0
	 */
	if (class_exists('WooCommerce')) {
		new Xim_Woo_Outfit_Activation;
	}
});

/**
 * Deactivation Class - fire once while deactivating the plugin.
 *
 * @since    1.0.0
 */
class Xim_Woo_Outfit_Deactivation {

	function __construct() {
		$this->remove_cap();
	}

	// remove upload capability
	function remove_cap() {
		$role = get_role('customer');
		$role->remove_cap('upload_files');
	}
}

register_deactivation_hook(__FILE__, function () {
	new Xim_Woo_Outfit_Deactivation;
});

/**
 * Initial class - fired when plugins are loaded.
 *
 * @since    1.0.0
 */
class Xim_Woo_Outfit_Init {
	use Xim_Woo_Outfit\Traits\Core;
	use Xim_Woo_Outfit\Traits\Helper;
	use Xim_Woo_Outfit\Traits\Metabox;
	use Xim_Woo_Outfit\Traits\Ajax;
	use Xim_Woo_Outfit\Traits\Template;
	use Xim_Woo_Outfit\Admin\Admin;

	public $all_outfit_endpoint = 'outfits';
	public $new_outfit_endpoint = 'outfits/new-outfit';

	function __construct() {
		// Add translation support
		load_plugin_textdomain('xim', false, basename(dirname(__FILE__)) . '/languages');

		// Core
		add_action('init', array($this, 'init'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		// add_action('template_redirect', array($this, 'template_redirect'));
		// add_filter('get_sample_permalink_html', array($this, 'remove_sample_permalink_html'));
		// add_filter('post_row_actions', array($this, 'filter_post_row_actions'), 10, 2);
		add_action('before_delete_post', array($this, 'before_delete_post'));
		add_filter('body_class', array($this, 'filter_body_class'));
		add_filter('ajax_query_attachments_args', array($this, 'ajax_query_attachments_args'));
		add_filter('wp_footer', array($this, 'wp_footer'));
		add_filter('wp_head', array($this, 'wp_head'));
		add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 2);
		add_filter('term_link', array($this, 'filter_term_link'), 10, 3);

		add_action('woocommerce_account_' . $this->new_outfit_endpoint . '_endpoint', array($this, 'new_outfit_endpoint_content'));
		add_action('woocommerce_account_' . $this->all_outfit_endpoint . '_endpoint', array($this, 'outfits_endpoint_content'));
		add_filter('woocommerce_account_menu_items', array($this, 'myaccount_menu_items'));
		add_filter('the_title', array($this, 'filter_endpoints_title'), 10, 2);

		// Metabox
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
		add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
		add_action('save_post', array($this, 'update_meta_on_submit'), 1, 2);
		add_action('do_meta_boxes', array($this, 're_init_thumb_box'));

		// Ajax
		add_action('wp_ajax_wc_outfit_get_products_by_cat', array($this, 'ajax_get_products_by_cat'));
		add_action('wp_ajax_nopriv_wc_outfit_get_products_by_cat', array($this, 'nopriv_ajax_get_products_by_cat'));
		add_action('wp_ajax_wc_outfit_post_like', array($this, 'ajax_post_like'));
		add_action('wp_ajax_nopriv_wc_outfit_post_like', array($this, 'nopriv_ajax_post_like'));
		add_action('wp_ajax_wc_outfit_follow_people', array($this, 'ajax_follow_people'));
		add_action('wp_ajax_nopriv_wc_outfit_follow_people', array($this, 'nopriv_ajax_follow_people'));
		add_action('wp_ajax_wc_outfit_list_follower', array($this, 'ajax_list_follower'));
		add_action('wp_ajax_nopriv_wc_outfit_list_follower', array($this, 'ajax_list_follower'));
		add_action('wp_ajax_wc_outfit_list_following', array($this, 'ajax_list_following'));
		add_action('wp_ajax_nopriv_wc_outfit_list_following', array($this, 'ajax_list_following'));
		add_action('wp_ajax_wc_outfit_single_outfit_modal', array($this, 'ajax_outfit_modal'));
		add_action('wp_ajax_nopriv_wc_outfit_single_outfit_modal', array($this, 'ajax_outfit_modal'));
		add_action('wp_ajax_wc_outfit_style_gallery', array($this, 'ajax_style_gallery'));
		add_action('wp_ajax_nopriv_wc_outfit_style_gallery', array($this, 'ajax_style_gallery'));
		add_action('wp_ajax_wc_outfit_post_outfit', array($this, 'ajax_post_outfit'));

		// Templates
		add_shortcode('outfits', array($this, 'template_outfits'));
		add_shortcode('new-outfit', array($this, 'template_new_outfit'));
		add_shortcode('style-gallery', array($this, 'template_style_gallery'));
		add_action('woocommerce_after_single_product', array($this, 'template_single_product_listing'));

		// Admin Page
		if (is_admin()) {
			add_action('admin_menu', array($this, 'admin_menu'));
		}
	}
}

add_action('plugins_loaded', function () {
	if (class_exists('WooCommerce')) {
		/**
		 * If WooCommerce is activated, initiate the plugin
		 *
		 * @since    1.0.0
		 */
		new Xim_Woo_Outfit_Init;

	} else {
		/**
		 * If WooCommerce is not activated, throw error and deactive the plugin
		 *
		 * @since    1.0.0
		 */
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
});