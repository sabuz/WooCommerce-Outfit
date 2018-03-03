<?php

namespace Xim_Woo_Outfit\Traits;

trait Database {

	protected $table = 'wc_outfit_post_likes';

	/**
	 * Install db table 'post_likes'.
	 *
	 * @since    1.0.0
	 */
	function install_db() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . $this->table;

		// Create table if not exists
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta("CREATE TABLE $table_name (
				id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
				post_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
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

		$table_name = $wpdb->prefix . $this->table;
		$user = get_current_user_id();

		if ($this->is_liked_outfit($post_id)) {
			$wpdb->delete($table_name, array('post_id' => $post_id, 'user_id' => $user));
		} else {
			$wpdb->insert($table_name, array('post_id' => $post_id, 'user_id' => $user, 'created_at' => current_time('mysql')));
		}
	}

	/**
	 * Fetch num post like from table 'wc_outfit_post_likes'.
	 *
	 * @since    1.0.0
	 */
	function get_num_post_like_db($post_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

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

		$table_name = $wpdb->prefix . $this->table;
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

		$table_name = $wpdb->prefix . $this->table;

		return $wpdb->get_col("SELECT post_id FROM $table_name WHERE user_id = $user_id");
	}

	// Deprecated
	function most_liked_outfits($order = 'most-liked-day') {
		global $wpdb;
		
		$liked_table = $wpdb->prefix . $this->table;
		$post_table = $wpdb->prefix . 'posts';

		if ($order == 'most-liked-day') {
			$date = date('Y-m-d 00:00:01');
		} else {
			$date = date('Y-m-d 00:00:01', strtotime('last week'));
		}

		$liked = $wpdb->get_col("SELECT post_id, COUNT(post_id) AS `likes` FROM $liked_table GROUP BY post_id ORDER BY likes DESC, created_at DESC");

		$liked_today = $wpdb->get_col("SELECT post_id, COUNT(post_id) AS `likes` FROM $liked_table WHERE created_at > '$date' GROUP BY post_id ORDER BY likes DESC, created_at DESC");

		// $posts = $wpdb->get_col("SELECT ID FROM $post_table WHERE post_type = '$type' ORDER BY post_date DESC");

		$reminder_liked = array_diff($liked, $liked_today);
		// $reminder_unliked = array_diff($posts, $reminder_liked);
		// $reminder_unliked = array_diff($reminder_unliked, $liked_today);

		return array_merge($liked_today, $reminder_liked);
	}
}