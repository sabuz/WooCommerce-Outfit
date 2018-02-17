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

require_once 'vendor/autoload.php';

use Xim_Woo_Outfit\Traits\Template_Shortcode;
use Xim_Woo_Outfit\Traits\Test as MyTest;

/**
 * Including the core plugin functions.
 *
 * @since    1.0.0
 */
require_once dirname(__file__) . '/includes/helper.php';
require_once dirname(__file__) . '/includes/ajax.php';
require_once dirname(__file__) . '/includes/functions.php';
require_once dirname(__file__) . '/includes/metabox.php';
require_once dirname(__file__) . '/includes/db.php';
// require_once dirname(__file__) . '/includes/shortcode.php';

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

	function __construct() {
		$this->install_db();
	}

	protected function install_db() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wc_outfit_post_likes';
		$charset_collate = $wpdb->get_charset_collate();

		// Create table if not exists
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql = "CREATE TABLE $table_name (
				id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
				postid bigint(20) NOT NULL,
				post_type varchar(20) NOT NULL,
				user bigint(20) NOT NULL,
				created_at datetime NOT NULL
			) $charset_collate;";
			dbDelta($sql);
		}
	}
}
register_activation_hook(__FILE__, function () {
	new Xim_Woo_Outfit_Activation;
});

/**
 * Bootstrap class - fired when plugins are loaded.
 *
 * @since    1.0.0
 */
class Xim_Woo_Outfit_Init {
	use Template_Shortcode;

	function __construct() {
		$this->throw_notice_and_deactive();

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