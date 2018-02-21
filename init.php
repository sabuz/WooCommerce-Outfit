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

	function __construct() {
		flush_rewrite_rules();
		$this->install_db();
	}
}
register_activation_hook(__FILE__, function () {
	new Xim_Woo_Outfit_Activation;
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
	use Xim_Woo_Outfit\Traits\Template_Shortcode;

	public $all_outfit_endpoint = 'outfits';
	public $new_outfit_endpoint = 'outfits/new-outfit';

	function __construct() {
		// Core
		$this->throw_notice_and_deactive();

		add_action('woocommerce_account_' . $this->new_outfit_endpoint . '_endpoint', array($this, 'new_outfit_endpoint_content'));
		add_action('woocommerce_account_' . $this->all_outfit_endpoint . '_endpoint', array($this, 'outfits_endpoint_content'));
		add_filter('woocommerce_account_menu_items', array($this, 'add_myaccount_menu_items'));
		add_filter('the_title', array($this, 'filter_endpoints_title'), 10, 2);

		add_action('init', array($this, 'init'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('template_redirect', array($this, 'disable_single_outfit_view'));
		add_filter('get_sample_permalink_html', array($this, 'remove_sample_permalink_html'));
		add_filter('post_row_actions', array($this, 'filter_post_row_actions'), 10, 2);
		add_action('before_delete_post', array($this, 'remove_post_data_on_delete'));
		add_filter('body_class', array($this, 'filter_body_class'));

		// Metabox
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
		add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
		add_action('save_post', array($this, 'update_meta_on_submit'), 1, 2);
		add_action('do_meta_boxes', array($this, 're_init_thumb_box'));

		// Ajax
		add_action('wp_ajax_wc_outfit_products_by_cat', array($this, 'ajax_get_products_by_cat'));
		add_action('wp_ajax_nopriv_wc_outfit_products_by_cat', array($this, 'nopriv_ajax_get_products_by_cat'));
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

		// Shortcodes
		add_shortcode('outfits', array($this, 'outfits_shortcode'));
		add_shortcode('new-outfit', array($this, 'new_outfit_shortcode'));
		add_shortcode('style-gallery', array($this, 'style_gallery_shortcode'));

		/**
		 * Including the functions for admin menu page and options.
		 *
		 * @since    1.0.0
		 */
		// if (is_admin()) {
		// require_once dirname(__file__) . '/admin/wp_stickit_admin.php';
		// }
	}
}
add_action('plugins_loaded', function () {
	new Xim_Woo_Outfit_Init;
});