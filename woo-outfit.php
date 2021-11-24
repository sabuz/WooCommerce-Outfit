<?php

/**
 * Plugin Name:       Outfit Photos for WooCommerce
 * Description:       Outfit Photos for WooCommerce is one of a kind plugin which will enable your customers to submit their photos to the related bought products.
 * Version:           2.1.0
 * Author:            Nazmul Sabuz
 * Author URI:        https://profiles.wordpress.org/nazsabuz/
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
class Woocommerce_Outfit_Activation {
	use Woocommerce_Outfit\Traits\Database;
	use Woocommerce_Outfit\Traits\Core;

	function __construct() {
		// install database
		$this->install_db();

		// install pages
		$this->install_pages();

		// set flush rewrite flag enabled
		set_transient('woo_outfit_flush_rewrite_rules_flag', true, 86400);
	}
}

register_activation_hook(__FILE__, function () {
	// If WooCommerce is activated, initiate the activation class
	if (class_exists('WooCommerce')) {
		new Woocommerce_Outfit_Activation;
	}
});

/**
 * Init class - fired when plugins are loaded.
 *
 * @since    1.0.0
 */
class Woocommerce_Outfit_Init {
	use Woocommerce_Outfit\Traits\Core;
	use Woocommerce_Outfit\Traits\Helper;
	use Woocommerce_Outfit\Traits\Metabox;
	use Woocommerce_Outfit\Traits\Ajax;
	use Woocommerce_Outfit\Traits\Template;
	use Woocommerce_Outfit\Admin\Admin;

	public $all_outfit_endpoint = 'outfits';
	public $new_outfit_endpoint = 'outfits/new-outfit';
	public $style_gallery_url;

	function __construct() {
		// Add translation support
		load_plugin_textdomain('woo-outfit', false, basename(dirname(__FILE__)) . '/languages');

		// Core
		add_action('init', array($this, 'init'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		add_action('before_delete_post', array($this, 'before_delete_post'));
		add_filter('wp_footer', array($this, 'wp_footer'));
		add_filter('wp_head', array($this, 'wp_head'));
		add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 2);
		add_filter('term_link', array($this, 'filter_term_link'), 10, 3);

		add_action('woocommerce_account_' . $this->new_outfit_endpoint . '_endpoint', array($this, 'new_outfit_endpoint_content'));
		add_action('woocommerce_account_' . $this->all_outfit_endpoint . '_endpoint', array($this, 'outfits_endpoint_content'));
		add_filter('woocommerce_account_menu_items', array($this, 'myaccount_menu_items'));
		add_filter('the_title', array($this, 'filter_endpoints_title'), 10, 2);

		// Ajax
		add_action('wp_ajax_woo_outfit_get_products_by_cat', array($this, 'ajax_get_products_by_cat'));
		add_action('wp_ajax_nopriv_woo_outfit_get_products_by_cat', array($this, 'nopriv_ajax_get_products_by_cat'));
		add_action('wp_ajax_woo_outfit_post_like', array($this, 'ajax_post_like'));
		add_action('wp_ajax_nopriv_woo_outfit_post_like', array($this, 'nopriv_ajax_post_like'));
		add_action('wp_ajax_woo_outfit_follow_people', array($this, 'ajax_follow_people'));
		add_action('wp_ajax_nopriv_woo_outfit_follow_people', array($this, 'nopriv_ajax_follow_people'));
		add_action('wp_ajax_woo_outfit_single_outfit_modal', array($this, 'ajax_outfit_modal'));
		add_action('wp_ajax_nopriv_woo_outfit_single_outfit_modal', array($this, 'ajax_outfit_modal'));
		add_action('wp_ajax_woo_outfit_style_gallery', array($this, 'ajax_style_gallery'));
		add_action('wp_ajax_nopriv_woo_outfit_style_gallery', array($this, 'ajax_style_gallery'));
		add_action('wp_ajax_woo_outfit_post_outfit', array($this, 'ajax_post_outfit'));

		// Templates
		add_shortcode('outfits', array($this, 'template_outfits'));
		add_shortcode('new-outfit', array($this, 'template_new_outfit'));
		add_shortcode('style-gallery', array($this, 'template_style_gallery'));
		add_action(get_option('woo-outfit-single-position', 'woocommerce_after_single_product_summary'), array($this, 'template_single_product_listing'));

		if (is_admin()) {
			// Admin Page
			add_action('admin_menu', array($this, 'admin_menu'));

			// Metabox
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
			add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
			add_action('save_post', array($this, 'update_meta_on_submit'), 1, 2);
			add_action('do_meta_boxes', array($this, 're_init_thumb_box'));
		}
	}
}

add_action('plugins_loaded', function () {
	// If WooCommerce is activated, initiate the plugin, else throw error and deactive the plugin
	if (class_exists('WooCommerce')) {
		new Woocommerce_Outfit_Init;
	} else {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Deactivate the plugin
		deactivate_plugins(plugin_basename(__FILE__));

		// Remove success notice
		unset($_GET['activate']);

		// Throw an error in the wordpress admin
		add_action('admin_notices', function () {
			$class = 'notice notice-error is-dismissible';
			$message = '<strong>' . __('Outfit Photos for WooCommerce', 'woo-outfit') . '</strong> ' . __('requires', 'woo-outfit') . ' <strong>' . __('WooCommerce', 'woo-outfit') . '</strong> ' . __('plugin to be installed and activated.', 'woo-outfit');
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
		});
	}
});