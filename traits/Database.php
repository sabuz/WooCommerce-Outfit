<?php

namespace Xim_Woo_Outfit\Traits;

trait Database {

	protected $table_post_likes = 'woo_outfit_post_likes';

	/**
	 * Install db table 'post_likes' and 'community'.
	 *
	 * @since    1.0.0
	 */
	function install_db() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Create 'post_likes' table if not exists
		$table_name = $wpdb->prefix . $this->table_post_likes;
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			dbDelta("CREATE TABLE $table_name (
				id bigint(20) UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL,
				post_id bigint(20) UNSIGNED NOT NULL,
				created_at datetime NOT NULL
			) $charset_collate;");
		}
	}

	/**
	 * Insert/delete post like.
	 *
	 * @since    1.0.0
	 */
	function toggle_post_like($post_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_post_likes;
		$user = get_current_user_id();

		// insert or delete
		if ($this->is_liked_outfit($post_id)) {
			$wpdb->delete($table_name, array('post_id' => $post_id, 'user_id' => $user));
		} else {
			$wpdb->insert($table_name, array('post_id' => $post_id, 'user_id' => $user, 'created_at' => current_time('mysql')));
		}

		// fetch count from db
		$wpdb->get_results("SELECT * FROM $table_name WHERE post_id = $post_id");

		return $wpdb->num_rows;
	}

	/**
	 * Check if a post is liked by user.
	 *
	 * @since    1.0.0
	 */
	function is_liked_outfit($post_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_post_likes;
		$user = get_current_user_id();

		$wpdb->get_results("SELECT * FROM $table_name WHERE post_id = $post_id AND user_id = $user");

		if ($wpdb->num_rows > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Get liked outfits post_id of a user.
	 *
	 * @since    1.0.0
	 */
	function get_liked_outfits($user_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_post_likes;

		return $wpdb->get_col("SELECT post_id FROM $table_name WHERE user_id = $user_id");
	}
}