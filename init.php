<?php

/**
 * @package           WoocommerceOutfit
 * @version           1.0.0
 * @link              http://sabuz.me
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
 * Including the functions for admin menu page and options.
 *
 * @since    1.0.0
 */
// if (is_admin()) {
// require_once dirname(__file__) . '/admin/wp_stickit_admin.php';
// }

/**
 * Activation Class - fire once while activating the plugin.
 *
 * @since    1.0.0
 */
class Xim_Woo_Outfit_Activation {
	use Xim_Woo_Outfit\Traits\Database;

	function __construct() {
		$this->install_db();
		flush_rewrite_rules();
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
	use Xim_Woo_Outfit\Traits\Helper;
	use Xim_Woo_Outfit\Traits\Hooks;
	use Xim_Woo_Outfit\Traits\Metabox;
	use Xim_Woo_Outfit\Traits\Ajax;
	use Xim_Woo_Outfit\Traits\Template_Shortcode;

	function __construct() {
		$this->throw_notice_and_deactive();

		// Hooks
		add_action('wp_enqueue_scripts', array($this, 'wc_outfit_enqueue_script'));
		add_action('init', array($this, 'wc_outfit_init'));
		add_action('template_redirect', array($this, 'wc_outfit_disable_single'));
		add_action('before_delete_post', array($this, 'wc_outfit_remove_cp_meta_on_delete'));
		add_filter('body_class', array($this, 'wc_outfit_body_classes'));

		// Metabox
		add_action('add_meta_boxes', array($this, 'wc_outfit_register_meta_boxes'));

		add_action('save_post', array($this, 'wc_outfit_update_metabox'), 1, 2);
		add_action('do_meta_boxes', array($this, 'wc_outfit_custom_thumb_boxes'));
		add_action('admin_enqueue_scripts', array($this, 'wc_outfit_metabox_styles'));

		// Ajax
		add_action("wp_ajax_products_by_cat", array($this, 'ajax_products_by_cat'));
		add_action("wp_ajax_nopriv_products_by_cat", array($this, 'nopriv_ajax_products_by_cat'));
		add_action("wp_ajax_post_like", array($this, 'ajax_post_like'));
		add_action("wp_ajax_nopriv_post_like", array($this, 'nopriv_ajax_post_like'));
		add_action("wp_ajax_follow_people", array($this, 'ajax_follow_people'));
		add_action("wp_ajax_nopriv_follow_people", array($this, 'nopriv_ajax_follow_people'));
		add_action("wp_ajax_list_follower", array($this, 'ajax_list_follower'));

		add_action("wp_ajax_nopriv_list_follower", array($this, 'ajax_list_follower'));
		add_action("wp_ajax_list_following", array($this, 'ajax_list_following'));

		add_action("wp_ajax_nopriv_list_following", array($this, 'ajax_list_following'));
		add_action("wp_ajax_outfit_modal", array($this, 'ajax_outfit_modal'));

		add_action("wp_ajax_nopriv_outfit_modal", array($this, 'ajax_outfit_modal'));
		add_action('wp_ajax_style_gallery', array($this, 'wc_outfit_ajax_style_gallery'));

		add_action('wp_ajax_nopriv_style_gallery', array($this, 'wc_outfit_ajax_style_gallery'));
		add_action('wp_ajax_ajax_upload', array($this, 'ajax_upload'));

		// Shortcodes
		add_shortcode('new-outfit', array($this, 'new_outfit_shortcode'));
		add_shortcode('all-outfit', array($this, 'all_outfit_shortcode'));
		add_shortcode('style-gallery', array($this, 'style_gallery_shortcode'));
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
}
add_action('plugins_loaded', function () {
	new Xim_Woo_Outfit_Init;
});



function wk_custom_endpoint() {
  add_rewrite_endpoint( 'custom', EP_ROOT | EP_PAGES );
}
 
add_action( 'init', 'wk_custom_endpoint' );


add_filter( 'woocommerce_account_menu_items', 'wk_new_menu_items' );
 
/**
* Insert the new endpoint into the My Account menu.
*
* @param array $items
* @return array
*/
function wk_new_menu_items( $items ) {
    $items[ 'custom' ] = __( 'Custom', 'webkul' );
    return $items;
}


$endpoint = 'custom';
 
add_action( 'woocommerce_account_' . $endpoint .  '_endpoint', 'wk_endpoint_content' );
 
function wk_endpoint_content() {
    //content goes here
    echo 'content goes here';    
    // https://gist.github.com/neilgee/13ac00c86c903c4ab30544b2b76c483c
}