<?php

/**
 * @package           WC_Outfit
 * @version           1.0.0
 * @link              http://sabuz.me
 *
 * Plugin Name:       WC Outfit
 * Plugin URI:        http://sabuz.me/wc-outfit
 * Description:       WC Outfit enables your woocommerce shop to add customer outfit photo.
 * Version:           1.0.0
 * Author:            Nazmul Islam Sabuz
 * Author URI:        http://sabuz.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

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
require_once dirname(__file__) . '/includes/shortcode.php';

/**
 * Including the functions for admin menu page and options.
 *
 * @since    1.0.0
 */
if (is_admin()) {
	// require_once dirname(__file__) . '/admin/wp_stickit_admin.php';
}

/**
 * Activation hook - fired while activating the plugin.
 *
 * @since    1.0.0
 */
register_activation_hook(__FILE__, 'wc_outfit_install_db');
