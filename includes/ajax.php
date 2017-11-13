<?php

/*******************************************************
 *
 * Ajax Callback Functions.
 *
 ******************************************************/
function ajax_products_by_cat() {
	$user = wp_get_current_user();
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $_REQUEST['cat'],
				'operator' => 'IN',
			),
		),
		'fields' => 'ids',
	);
	$data = new WP_Query($args);
	$json = array();

	if ($data->posts) {
		foreach ($data->posts as $id) {
			if (current_user_can('manage_options') || wc_customer_bought_product($user->user_email, $user->ID, $id)) {
				$post_data = array();
				$post_data['id'] = $id;
				$post_data['title'] = get_the_title($id);
				$post_data['thumb'] = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'product-thumb');
				$post_data['thumb'] = $post_data['thumb'][0];

				array_push($json, $post_data);
			}
		}
	}

	wp_send_json($json);
}
add_action("wp_ajax_products_by_cat", "ajax_products_by_cat");

function nopriv_ajax_products_by_cat() {
	$json = array();
	wp_send_json($json);
}
add_action("wp_ajax_nopriv_products_by_cat", "nopriv_ajax_products_by_cat");

function ajax_post_like() {
	wc_outfit_post_like($_REQUEST['post_id'], $_REQUEST['post_type']);
	update_post_meta($_REQUEST['post_id'], 'likes', wc_outfit_get_post_like_count_db($_REQUEST['post_id']));
	echo wc_outfit_get_post_like_count($_REQUEST['post_id']);

	die();
}
add_action("wp_ajax_post_like", "ajax_post_like");

function nopriv_ajax_post_like() {
	echo home_url('my-account');
	die();
}
add_action("wp_ajax_nopriv_post_like", "nopriv_ajax_post_like");

function ajax_follow_people() {
	$user = get_current_user_id();

	if ($user) {
		$followers = get_user_meta($_REQUEST['user_id'], 'followers', true);
		$following = get_user_meta($user, 'following', true);

		if (in_array($user, $followers)) {
			$followers = array_diff($followers, [$user]);
		} else {
			$followers[] = $user;
		}

		if (in_array($_REQUEST['user_id'], $following)) {
			$following = array_diff($following, [$_REQUEST['user_id']]);
		} else {
			$following[] = $_REQUEST['user_id'];
		}

		update_user_meta($_REQUEST['user_id'], 'followers', $followers);
		update_user_meta($user, 'following', $following);
		echo count($followers);
	}

	die();
}
add_action("wp_ajax_follow_people", "ajax_follow_people");

function nopriv_ajax_follow_people() {
	echo home_url('my-account');
	die();
}
add_action("wp_ajax_nopriv_follow_people", "nopriv_ajax_follow_people");

function ajax_list_follower() {
	$data = array();

	if ($_REQUEST['user']) {
		$followers = get_user_meta($_REQUEST['user'], 'followers', true);

		foreach ($followers as $key => $value) {
			$author_data = wc_outfit_get_author_data($value);
			$data[$value] = $author_data['nickname'][0];
		}
	}

	wp_send_json($data);
}
add_action("wp_ajax_list_follower", "ajax_list_follower");
add_action("wp_ajax_nopriv_list_follower", "ajax_list_follower");

function ajax_list_following() {
	$data = array();

	if ($_REQUEST['user']) {
		$following = get_user_meta($_REQUEST['user'], 'following', true);

		foreach ($following as $key => $value) {
			$author_data = wc_outfit_get_author_data($value);
			$data[$value] = $author_data['nickname'][0];
		}
	}

	wp_send_json($data);
}
add_action("wp_ajax_list_following", "ajax_list_following");
add_action("wp_ajax_nopriv_list_following", "ajax_list_following");

function ajax_outfit_modal() {
	$content = '';

	if ($_REQUEST['view']) {
		$author_id = wc_outfit_get_post_author($_REQUEST['view']);
		$author_data = wc_outfit_get_author_data($author_id);
		// $author_data = wc_outfit_get_author_data($value);

		$content = '<div class="modal-body clearfix">';
		$content .= '<div class="thumb">';
		$content .= '<img src="' . wc_outfit_post_thumb_by_id($_REQUEST['view']) . '" />';
		$content .= '</div>';

		$content .= '<div class="details">';
		$content .= '<div class="author clearfix">';
		$content .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		$content .= '<a href="' . user_gallery_link_by_id($author_id) . '" class="name">';
		$content .= $author_data['nickname'][0];
		$content .= '</a>';

		if ($author_id != get_current_user_id()) {
			$content .= '<a href="#" class="medal" data-id="' . $author_id . '">';
			if (wc_outfit_is_following($author_id)) {
				$content .= 'Unfollow';
			} else {
				$content .= 'Follow';
			}
			$content .= '</a>';
		}
		$content .= '</div>';

		// $content .= '<div class="measurement">';
		// $content .= '<table class="table">';
		// $content .= '<tr>';
		// $content .= '<th>' . __('Height', 'couture') . '</th>';
		// $content .= '<th>' . __('Bra', 'couture') . '</th>';
		// $content .= '<th>' . __('Waist', 'couture') . '</th>';
		// $content .= '<th>' . __('Hips', 'couture') . '</th>';
		// $content .= '<th>' . __('Shoe', 'couture') . '</th>';
		// $content .= '</tr>';
		// $content .= '<tr>';

		// foreach (modal_measurements($author) as $key => $val) {
		// 	$content .= '<td>' . $val . '</td>';
		// }

		// $content .= '</tr>';
		// $content .= '</table>';
		// $content .= '</div>';

		$content .= '<div class="products owl-carousel">' . wc_outfit_modal_hooked_product($_REQUEST['view']) . '</div>';

		$content .= '<div class="tags">';

		$tags = wp_get_post_categories($_GET['view']);
		foreach ($tags as $tag) {
			$tag = get_category($tag);
			$content .= '<a href="' . home_url('style-gallery/?cat=' . strtolower($tag->name)) . '">' . $tag->name . '</a>';
		}
		$content .= '</div>';

		$content .= '<div class="info">';
		$content .= '<div class="pull-left">';
		$content .= '<span class="time">' . __('Added ', 'couture') . wc_outfit_time_ago($_REQUEST['view']) . '</span>';
		$content .= '</div>';

		$content .= '<div class="pull-right">';
		$content .= wc_outfit_outfit_share_button($_REQUEST['view']);
		$content .= wc_outfit_post_like_button($_REQUEST['view']);
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';

		if ($_REQUEST['pagination']) {
			$content .= '<div class="modal-footer">
				<a id="prev" data-id="">&laquo;</a>
				See More
				<a id="next" data-id="">&raquo;</a>
			</div>';
		}

	}

	echo $content;
	die();
}
add_action("wp_ajax_outfit_modal", "ajax_outfit_modal");
add_action("wp_ajax_nopriv_outfit_modal", "ajax_outfit_modal");

function wc_outfit_ajax_style_gallery() {
	if ($_REQUEST['user']) {
		if (@$_REQUEST['page'] == 'likes') {
			$ids = wc_outfit_get_likes_by_user($_REQUEST['user']);
			if (!empty($ids)) {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'post__in' => $ids,
					'paged' => $_REQUEST['paged'],
				);
			}
		} else {
			$args = array(
				'post_type' => 'outfit',
				'post_status' => 'publish',
				'posts_per_page' => 9,
				'order' => 'desc',
				'author' => $_REQUEST['user'],
				'paged' => $_REQUEST['paged'],
			);
		}
	} elseif ($_REQUEST['cat']) {
		$args = array(
			'post_type' => 'outfit',
			'post_status' => 'publish',
			'posts_per_page' => 9,
			'order' => 'desc',
			'tax_query' => array(
				array(
					'taxonomy' => 'outfit_categories',
					'field' => 'slug',
					'terms' => $_REQUEST['cat'],
				),
			),
			'paged' => $_REQUEST['paged'],
		);
	} else {
		if ($_REQUEST['page']) {
			if ($_REQUEST['page'] == 'feat') {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'meta_query' => array(
						array(
							'key' => 'featured',
							'value' => 'yes',
						),
					),
					'paged' => $_REQUEST['paged'],
				);
			} elseif ($_REQUEST['page'] == 'following') {
				if (is_user_logged_in()) {
					$data = get_user_meta(get_current_user_id(), 'following', true);

					if (!empty($data)) {
						$args = array(
							'post_type' => 'outfit',
							'post_status' => 'publish',
							'posts_per_page' => 9,
							'order' => 'desc',
							'author__in' => $data,
							'paged' => $_REQUEST['paged'],
						);
					}
				}
			}
		} else {
			if (@$_REQUEST['order'] == 'most-liked') {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'meta_key' => 'likes',
					'orderby' => 'meta_value_num',
					'paged' => $_REQUEST['paged'],
				);

			} elseif (@$_REQUEST['order'] == 'most-liked-day' || @$_REQUEST['order'] == 'most-liked-week') {
				$ids = wc_outfit_most_liked($_REQUEST['order']);
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'post__in' => $ids,
					'orderby' => 'post__in',
					'paged' => $_REQUEST['paged'],
				);

			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'paged' => $_REQUEST['paged'],
				);
			}
		}
	}

	$query = new WP_Query($args);

	while ($query->have_posts()): $query->the_post();
		echo '<div class="grid-item col-sm-4" data-id="' . $query->post->ID . '">
			<div class="gal-header">
				<div class="gal-product clearfix">
					<ul>
						' . wc_outfit_hooked_product($query->post->ID, 4) . '
					</ul>
				</div>
				<a class="gal-thumb clearfix">
					<img src="' . wc_outfit_post_thumb_by_id($query->post->ID) . '" class="gal-img" />
				</a>
			</div>
			<div class="gal-footer clearfix">
				<div class="pull-left">
					<a class="author" href="' . user_gallery_link_by_id(get_the_author_meta('ID')) . '">' . wc_outfit_author_name_by_id(get_the_author_meta('ID')) . '</a>
					<span class="time">' . wc_outfit_time_ago() . '</span>
				</div>
				<div class="pull-right">
					<div class="gal-bubble">
						<a href="#" class="bubble-btn"><i class="fa fa-share"></i></a>

						<div class="bubble-content">
							' . wc_outfit_outfit_share_button($query->post->ID) . '
						</div>
					</div>

					' . wc_outfit_post_like_button($query->post->ID) . '
				</div>
			</div>
		</div>';
	endwhile;
	die();
}
add_action('wp_ajax_style_gallery', 'wc_outfit_ajax_style_gallery');
add_action('wp_ajax_nopriv_style_gallery', 'wc_outfit_ajax_style_gallery');

// Ajax Upload

function ajax_upload() {
	$msg = array();

	if (!isset($_REQUEST['ids'])) {
		$msg[] = 'Products are required';
	}

	if (!file_exists($_FILES['thumb']['tmp_name'])) {
		$msg[] = 'Thumbnail are required';
	}

	if (count($msg) == 0) {
		$post_args = array(
			'post_title' => '',
			'post_type' => 'outfit',
			'post_status' => 'pending',
		);

		$post_id = wp_insert_post($post_args);

		if ($post_id) {

			// Including file library if not exist
			if (!function_exists('wp_handle_upload')) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			// Uploading file to server
			$movefile = wp_handle_upload($_FILES['thumb'], ['test_form' => false]);

			// If uploading success & No error
			if ($movefile && !isset($movefile['error'])) {
				$filename = $movefile['file'];
				$filetype = wp_check_filetype(basename($filename), null);
				$wp_upload_dir = wp_upload_dir();

				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
					'post_mime_type' => $filetype['type'],
					'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_content' => '',
					'post_status' => 'inherit',
				);

				// Adding file to media
				$attach_id = wp_insert_attachment($attachment, $filename);

				// If attachment success
				if ($attach_id) {
					require_once ABSPATH . 'wp-admin/includes/image.php';

					// Updating attachment metadata
					$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
					wp_update_attachment_metadata($attach_id, $attach_data);

					set_post_thumbnail($post_id, $attach_id);
					add_post_meta($post_id, 'products', $_REQUEST['ids']);
					wp_update_post(['ID' => $post_id, 'post_title' => 'Outfit ' . $post_id]);
				}

				$msg[] = 'Success';
			} else {
				$msg[] = $movefile['error'];
			}
		}
	}

	wp_send_json($msg);
}
add_action('wp_ajax_ajax_upload', 'ajax_upload');
?>