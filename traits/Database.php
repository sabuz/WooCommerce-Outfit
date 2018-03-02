<?php

namespace Xim_Woo_Outfit\Traits;

trait Database {

	/**
	 * Install db table 'post_likes'.
	 *
	 * @since    1.0.0
	 */
	public function install_db() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'wc_outfit_post_likes';

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

	public function toggle_post_like($post_id, $post_type) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wc_outfit_post_likes';
		$user = get_current_user_id();

		if ($this->is_liked_outfit($post_id)) {
			$wpdb->delete($table_name, ['postid' => $post_id, 'user' => $user]);
		} else {
			$wpdb->insert($table_name, ['postid' => $post_id, 'post_type' => $post_type, 'user' => $user, 'created_at' => current_time('mysql')]);
		}
	}

	function get_post_like_count($post_id) {
		$count = get_post_meta($post_id, 'likes', true);
		return !empty($count) ? $count : 0;
	}

	function get_post_like_count_db($post_id) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wc_outfit_post_likes';

		$wpdb->get_results("SELECT * FROM $table_name WHERE postid = $post_id");

		return $wpdb->num_rows;
	}

	// Deprecated
	function most_liked_outfits($order = 'most-liked-day', $type = 'outfit') {
		global $wpdb;
		$liked_table = $wpdb->prefix . 'wc_outfit_post_likes';
		$post_table = $wpdb->prefix . 'posts';

		if ($order == 'most-liked-day') {
			$date = date('Y-m-d 00:00:01');
		} else {
			$date = date('Y-m-d 00:00:01', strtotime('last week'));
		}

		$liked = $wpdb->get_col("SELECT postid, COUNT(postid) AS `likes` FROM $liked_table WHERE post_type = '$type' GROUP BY postid ORDER BY likes DESC, created_at DESC");

		$liked_today = $wpdb->get_col("SELECT postid, COUNT(postid) AS `likes` FROM $liked_table WHERE created_at > '$date' AND post_type = '$type' GROUP BY postid ORDER BY likes DESC, created_at DESC");

		// $posts = $wpdb->get_col("SELECT ID FROM $post_table WHERE post_type = '$type' ORDER BY post_date DESC");

		$reminder_liked = array_diff($liked, $liked_today);
		// $reminder_unliked = array_diff($posts, $reminder_liked);
		// $reminder_unliked = array_diff($reminder_unliked, $liked_today);

		return array_merge($liked_today, $reminder_liked);
	}

	function is_liked_outfit($post_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wc_outfit_post_likes';
		$user = get_current_user_id();

		$wpdb->get_results("SELECT * FROM $table_name WHERE postid = $post_id AND user = $user");

		if ($wpdb->num_rows > 0) {
			return true;
		}

		return false;
	}

	function get_liked_outfit_by_user($user_id) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wc_outfit_post_likes';

		return $wpdb->get_col("SELECT postid FROM $table_name WHERE user = $user_id AND post_type = 'outfit'");
	}
}