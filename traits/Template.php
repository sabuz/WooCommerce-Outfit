<?php

namespace Woocommerce_Outfit\Traits;

use WP_Query;

trait Template {
	/**
	 * Outfits shortcode.
	 *
	 * @since    1.0.0
	 */
	function template_outfits() {
		$query = new WP_Query(array(
			'post_type' => 'outfit',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'author' => get_current_user_id(),
		));

		$html = '';

		if (isset($_COOKIE['woo_outfit_success']) && $_COOKIE['woo_outfit_success'] == 'true') {
			$html .= '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">' . __('Outfit submitted successfully.', 'woocommerce-outfit') . '</div>

			<script>
				document.cookie="woo_outfit_success=;expires=expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/"
			</script>';
		}

		if ($query->have_posts()) {
			$html .= '<p>' . sprintf(__('To add more outfit photos, go to <a href="%1$s">new outfit</a>', 'woocommerce-outfit'), esc_url(wc_get_endpoint_url('outfits/new-outfit'))) . '</p>

			<table class="table">
				<thead>
					<tr>
						<th>' . __('ID', 'woocommerce-outfit') . '</th>
						<th>' . __('Title', 'woocommerce-outfit') . '</th>
						<th>' . __('Date', 'woocommerce-outfit') . '</th>
						<th>' . __('Status', 'woocommerce-outfit') . '</th>
						<th></th>
					</tr>
				</thead>

				<tbody>';
					while ($query->have_posts()) {
						$query->the_post();
						$html .= '<tr>
							<td>' . get_the_ID() . '</td>
							<td>' . the_title('', '', false) . '</td>
							<td>' . get_the_date() . '</td>
							<td>' . ucfirst(get_post_status()) . '</td>
							<td><a href="' . get_the_permalink() . '" target="_blank">' . __('View', 'woocommerce-outfit') . '</a></td>
						</tr>';
					}
					
					wp_reset_postdata();

				$html .= '</tbody>
			</table>';
		} else {
			$html .= '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="' . esc_url(wc_get_endpoint_url('outfits/new-outfit')) . '">' . __('Add new', 'woocommerce-outfit') . '</a>
				' . __('No outfits available yet.', 'woocommerce-outfit') . '
			</div>';
		}

		return $html;
	}

	/**
	 * New outfit shortcode.
	 *
	 * @since    1.0.0
	 */
	function template_new_outfit($atts, $content = null) {
		// enqueue styles
		wp_enqueue_style('woo-outfit-icon');
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('select2');
		wp_enqueue_style('new-outfit');

		// enqueue scripts
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('jquery-validate');
		wp_enqueue_script('select2');
		wp_enqueue_script('new-outfit');

		$terms = get_terms(array(
			'taxonomy' => 'outfit_tags',
			'hide_empty' => false,
		));

		$terms = (empty($terms) ? array() : $terms);

		$html = '<form id="new-outfit-form" method="post">
			<div class="form-group">
				<label>' . __('Outfit Image', 'woocommerce-outfit') . '</label>

				<div class="row">
					<div class="col-sm-12">
 						<input type="file" name="thumb" id="thumb">
 						<span class="woo-outfit-icon woo-outfit-icon-question-circle" data-toggle="popover" data-placement="left" data-content="' . get_option('woo-outfit-submission-guideline') . '"></span>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label>' . __('Used Products', 'woocommerce-outfit') . '</label>

				<div class="selected-products empty">
					<div class="row">

					</div>
				</div>
				
				<input type="hidden" name="ids" id="ids" value="">
			</div>

			<div class="form-group">
				<div class="select-cat-wrap">
					<select class="select-cat">
						<option></option>';
						foreach ($this->get_product_cats() as $cat) {
							$html .= '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
						}
					$html .= '</select>
				</div>

				<div class="product-list">

				</div>

				<div class="product-nav hidden">
					<a href="#" class="prev" data-page="0">&lt; Prev</a>
					<a href="#" class="next" data-page="0">Next &gt;</a>
				</div>
			</div>';

			if (get_option('woo-outfit-tagging', 'on') && get_option('woo-outfit-customer-tagging', 'on')) {
				$html .= '<div class="form-group">
					<label>' . __('Tags', 'woocommerce-outfit') . '</label>

					<select name="tags[]" class="select-tag" multiple="multiple">';
						foreach ($terms as $term) {
							$html .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
						}
					$html .= '</select>
				</div>';
			}

			$html .= '<input type="submit" id="submit" value="' . __('Add Outfit', 'woocommerce-outfit') . '">
		</form>';

		return $html;
	}

	/**
	 * Style gallery shortcode.
	 *
	 * @since    1.0.0
	 */
	function template_style_gallery() {
		global $post;
		// enqueue styles
		wp_enqueue_style('woo-outfit-icon');
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('owl-carousel');
		wp_enqueue_style('outfit-modal');
		wp_enqueue_style('style-gallery');

		// enqueue scripts
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('owl-carousel');
		wp_enqueue_script('isotope');
		wp_enqueue_script('style-gallery');

		echo '<div class="woo-outfit-gallery">
			<div class="woo-outfit-gallery-header">';
				echo '<h2 class="woo-outfit-gallery-header-title">' . __('Style Gallery', 'woocommerce-outfit') . '</h2>';

				if (isset($_GET['tags'])) {
					$term = get_term_by('slug', $_GET['tags'], 'outfit_tags');

					echo '<h4 class="woo-outfit-gallery-header-subtitle">' . $term->name . '</h4>';
				} elseif (isset($_GET['user'])) {
					$current = (isset($_GET['page']) ? $_GET['page'] : 'photos');

					echo '<h4 class="woo-outfit-gallery-header-subtitle">' . ucwords(get_the_author_meta('display_name', $_GET['user'])) . ($_GET['user'] != get_current_user_id() ? '<a href="#" class="woo-outfit-follow-btn" data-id="' . $_GET['user'] . '">' . ($this->is_following($_GET['user']) ? __('Unfollow', 'woocommerce-outfit') : __('Follow', 'woocommerce-outfit')) . '</a>' : '') . '</h4>';

					echo '<div class="woo-outfit-gallery-header-btn-group">
						<a href="' . $this->get_user_gallery_link($_GET['user']) . '" class="' . ($current == 'photos' ? 'active' : '') . '">' . __('Photos', 'woocommerce-outfit') . '</a>
						<a href="' . $this->get_user_gallery_link($_GET['user'], 'likes') . '" class="' . ($current == 'likes' ? 'active' : '') . '">' . __('Liked Looks', 'woocommerce-outfit') . '</a>
						<a href="' . $this->get_user_gallery_link($_GET['user'], 'follower') . '" class="woo-outfit-num-follower" data-user="' . $_GET['user'] . '">' . $this->get_num_followers($_GET['user']) . __(' Followers', 'woocommerce-outfit') . '</a>
						<a href="' . $this->get_user_gallery_link($_GET['user'], 'following') . '" class="woo-outfit-num-following" data-user="' . $_GET['user'] . '">' . $this->get_num_following($_GET['user']) . __(' Following', 'woocommerce-outfit') . '</a>
					</div>';
				} else {
					$current = (isset($_GET['page']) ? $_GET['page'] : 'all');

					echo '<h4 class="woo-outfit-gallery-header-subtitle">' . __('Inspire and Admire', 'woocommerce-outfit') . '</h4>';

					echo '<div class="woo-outfit-gallery-header-btn-group">
						<a href="' . get_the_permalink(get_option('woo-outfit-page-id')) . '" class="' . ($current == 'all' ? 'active' : '') . '">' . __('All', 'woocommerce-outfit') . '</a>
						<a href="' . add_query_arg('page', 'following', get_the_permalink(get_option('woo-outfit-page-id'))) . '" class="' . ($current == 'following' ? 'active' : '') . '">' . __('Following', 'woocommerce-outfit') . '</a>
						<a href="' . add_query_arg('page', 'feat', get_the_permalink(get_option('woo-outfit-page-id'))) . '" class="' . ($current == 'feat' ? 'active' : '') . '">' . __('Featured', 'woocommerce-outfit') . '</a>
					</div>';
				}
			echo '</div>'; //.woo-outfit-gallery-header

			// Query
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			$followers = array();
			$followings = array();

			if (isset($_GET['user'])) {
				if (@$_GET['page'] == 'follower') {
					$args = array();
					$followers = $this->get_followers($_GET['user']);
				} elseif (@$_GET['page'] == 'following') {
					$args = array();
					$followings = $this->get_followings($_GET['user']);
				} elseif (@$_GET['page'] == 'likes') {
					$ids = $this->get_liked_outfits($_GET['user']);
					
					if (empty($ids)) {
						$args = array();
					} else {
						$args = array(
							'post_type' => 'outfit',
							'post_status' => 'publish',
							'posts_per_page' => get_option('woo-outfit-ppq', 9),
							'order' => 'desc',
							'post__in' => $ids,
							'paged' => $paged,
						);
					}
				} else {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('woo-outfit-ppq', 9),
						'order' => 'desc',
						'author' => $_GET['user'],
						'paged' => $paged,
					);
				}
			} elseif (isset($_GET['tags'])) {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => get_option('woo-outfit-ppq', 9),
					'order' => 'desc',
					'tax_query' => array(
						array(
							'taxonomy' => 'outfit_tags',
							'field' => 'slug',
							'terms' => $_GET['tags'],
						),
					),
					'paged' => $paged,
				);
			} else {
				if (isset($_GET['page'])) {
					if ($_GET['page'] == 'feat') {
						$args = array(
							'post_type' => 'outfit',
							'post_status' => 'publish',
							'posts_per_page' => get_option('woo-outfit-ppq', 9),
							'order' => 'desc',
							'meta_query' => array(
								array(
									'key' => 'featured',
									'value' => 'yes',
								),
							),
							'paged' => $paged,
						);
					} elseif ($_GET['page'] == 'following') {
						if (is_user_logged_in()) {
							$data = $this->get_followings(get_current_user_id());

							if (empty($data)) {
								$args = array();
							} else {
								$args = array(
									'post_type' => 'outfit',
									'post_status' => 'publish',
									'posts_per_page' => get_option('woo-outfit-ppq', 9),
									'order' => 'desc',
									'author__in' => $data,
									'paged' => $paged,
								);
							}
						} else {
							$args = array();
						}
					}
				} else {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('woo-outfit-ppq', 9),
						'order' => 'desc',
						'paged' => $paged,
					);
				}
			}

			$query = new WP_Query($args);

			if ($query->have_posts()) {
				echo '<div class="woo-outfit-gallery-content">
					<div class="row">';
						while ($query->have_posts()) {
							$query->the_post();

							echo '<div class="woo-outfit-gallery-item col-sm-4" data-id="' . $post->ID . '">
								<div class="woo-outfit-gallery-item-inner-wrap">
									<img src="' . $this->get_outfit_thumbnail($post->ID, 'woo-outfit-style-gallery') . '" class="woo-outfit-gallery-item-thumb" />

									<div class="woo-outfit-gallery-item-footer clearfix">
										<div class="pull-left">
											<a class="woo-outfit-meta-author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
												' . ucwords(get_the_author_meta('display_name')) . '</a>
											<p class="woo-outfit-meta-time">' . $this->outfit_posted_ago() . '</p>
										</div>
										<div class="pull-right">
											' . $this->like_button_html($post->ID) . '
										</div>
									</div>
								</div>
							</div>';
						}

						wp_reset_postdata();

					echo '</div>
				</div>'; //.woo-outfit-gallery-content

				echo '<div class="woo-outfit-gallery-pagination">
					<button class="button" data-current="1" data-max="'. $query->max_num_pages .'"
						' . (isset($_GET['user']) ? 'data-user="' . $_GET['user'] . '"' : '') . (isset($_GET['page'])? 'data-page="' . $_GET['page'] . '"' : '') . (isset($_GET['tags']) ? 'data-tag="' . $_GET['tags'] . '"' : '') .'>' . __('Load More', 'woocommerce-outfit') . '</button>
				</div>';
			} else {
				if (empty($followings) && @$_GET['page'] == 'following') {
					echo '<div class="woo-outfit-no-content-found">
						<p>' . __('You are not following anyone!', 'woocommerce-outfit') . '</p>
						<p>' . __('Let\'s fix that by start following some style gallery stars!', 'woocommerce-outfit') . '</p>
					</div>';
				}
			}

			if (!empty($followers)) {
				echo '<div class="row">';
					foreach ($followers as $follower) {
						echo '<div class="col-sm-3">
							<div class="woo-outfit-gallery-user-item">
								' . get_avatar($follower, '100') . '
								<h4><a href="' . esc_url($this->get_user_gallery_link($follower)) . '">' . ucwords(get_the_author_meta('display_name', $follower)) . '</a></h4>
							</div>
						</div>';
					}
				echo '</div>';
			}

			if (!empty($followings)) {
				echo '<div class="row">';
					foreach ($followings as $following) {
						echo '<div class="col-sm-3">
							<div class="woo-outfit-gallery-user-item">
								' . get_avatar($following, '100') . '
								<h4><a href="' . esc_url($this->get_user_gallery_link($following)) . '">' . ucwords(get_the_author_meta('display_name', $following)) . '</a></h4>
							</div>
						</div>';
					}
				echo '</div>';
			}

		echo '</div>'; //.woo-outfit-gallery
	}

	/**
	 * Single product carousel.
	 *
	 * @since    1.0.0
	 */
	function template_single_product_listing() {
		global $post;
		// enqueue styles
		wp_enqueue_style('woo-outfit-icon');
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('owl-carousel');
		wp_enqueue_style('outfit-modal');
		wp_enqueue_style('single-product');

		// enqueue scripts
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('owl-carousel');
		wp_enqueue_script('single-product');

		$query = new WP_Query(array(
			'post_type' => 'outfit',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'products',
					'value' => $post->ID,
					'compare' => 'LIKE',
				),
			),
		));

		if ($query->have_posts()) {
			echo '<div class="woo-outfit-single-carousel">
				<h3>' . __('Outfit Photos', 'woocommerce-outfit') . '</h3>

				<div class="owl-carousel">';
				while ($query->have_posts()) {
					$query->the_post();
					
					echo '<div class="woo-outfit-gallery-item" data-id="' . $query->post->ID . '">
						<div class="woo-outfit-gallery-item-inner-wrap">
							<img src="' . $this->get_outfit_thumbnail($query->post->ID, 'woo-outfit-single-listing') . '" class="woo-outfit-gallery-item-thumb" />

							<div class="woo-outfit-gallery-item-footer clearfix">
								<div class="pull-left">
									<a class="woo-outfit-meta-author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
										' . ucwords(get_the_author_meta('display_name')) . '</a>
									<p class="woo-outfit-meta-time">' . $this->outfit_posted_ago() . '</p>
								</div>
								<div class="pull-right">
									' . $this->like_button_html($query->post->ID) . '
								</div>
							</div>
						</div>
					</div>';
				}

				wp_reset_postdata();
				
				echo '</div>
			</div>';
		}
	}
}