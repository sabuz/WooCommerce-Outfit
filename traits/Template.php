<?php

namespace Xim_Woo_Outfit\Traits;

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

		if (isset($_COOKIE['wc_outfit_success']) && $_COOKIE['wc_outfit_success'] == 'true') {
			$html .= '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">' . __('Outfit submitted successfully.', 'xim') . '</div>

			<script>
				document.cookie="wc_outfit_success=;expires=expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/"
			</script>';
		}

		if ($query->have_posts()) {
			$html .= '<p>' . sprintf(__('To add more outfit photos, go to <a href="%1$s">new outfit</a>', 'xim'), esc_url(wc_get_endpoint_url('outfits/new-outfit'))) . '</p>

			<table>
				<thead>
					<tr>
						<th>' . __('ID', 'xim') . '</th>
						<th>' . __('Title', 'xim') . '</th>
						<th>' . __('Date', 'xim') . '</th>
						<th>' . __('Status', 'xim') . '</th>
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
							<td>' . get_post_status() . '</td>
							<td><a href="' . get_the_permalink() . '" target="_blank">' . __('View', 'xim') . '</a></td>
						</tr>';
			}
			$html .= '</tbody>
			</table>';

			wp_reset_postdata();
		} else {
			$html .= '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="' . esc_url(wc_get_endpoint_url('outfits/new-outfit')) . '">' . __('Add new', 'xim') . '</a>
				' . __('No outfits available yet.', 'xim') . '
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
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('bootstrap-validator');
		wp_enqueue_style('select2');
		wp_enqueue_style('new-outfit');

		// enqueue scripts
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('bootstrap-validator');
		wp_enqueue_script('select2');
		wp_enqueue_script('new-outfit');

		wp_enqueue_media();

		$atts = shortcode_atts(array(), $atts);

		$terms = get_terms(array(
			'taxonomy' => 'outfit_tags',
			'hide_empty' => false,
		));

		$terms = (empty($terms) ? array() : $terms);

		$html = '<form id="new-outfit-form" method="post">
			<div class="form-group">
				<label>' . __('Outfit Image', 'xim') . '</label>

				<div class="row">
					<div class="col-sm-12">
 						<input type="file" name="thumb" id="thumb">
					</div>
				</div>
			</div>

			<div class="form-group">
				<label>' . __('Used Products', 'xim') . '</label>

				<div class="selected-products empty">
					<div class="row">

					</div>
					<input type="hidden" name="ids" class="ids" value="">
				</div>
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
			</div>

			<div class="form-group">
				<label>' . __('Tags', 'xim') . '</label>

				<select name="tags[]" class="select-tag" multiple="multiple">';
					foreach ($terms as $term) {
						$html .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
					}
				$html .= '</select>
			</div>

			<input type="submit" value="' . __('Add Outfit', 'xim') . '">
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

		wp_enqueue_style('bootstrap');
		wp_enqueue_style('owlCarousel');
		wp_enqueue_style('outfit-modal');
		wp_enqueue_style('style-gallery');

		wp_enqueue_script('bootstrap');
		wp_enqueue_script('owlCarousel');
		wp_enqueue_script('imgLoaded');
		wp_enqueue_script('isotope');
		wp_enqueue_script('style-gallery');

		echo '<div class="wc-outfit-gallery">
			<div class="wc-outfit-gallery-header">';
				echo '<h2 class="wc-outfit-gallery-header-title">' . __('Style Gallery', 'xim') . '</h2>';

				if (isset($_GET['tag'])) {
					$term = get_term_by('slug', $_GET['tag'], 'outfit_tags');

					echo '<h4 class="wc-outfit-gallery-header-subtitle">' . $term->name . '</h4>';
				} elseif (isset($_GET['user'])) {
					$current = (isset($_GET['page']) ? $_GET['page'] : 'photos');

					echo '<h4 class="wc-outfit-gallery-header-subtitle">' . ucwords(get_the_author_meta('display_name', $_GET['user'])) . ($_GET['user'] != get_current_user_id() ? '<a href="#" class="wc-outfit-follow-btn" data-id="' . $_GET['user'] . '">' . ($this->is_following($_GET['user']) ? __('Unfollow', 'xim') : __('Follow', 'xim')) . '</a>' : '') . '</h4>';

					echo '<div class="wc-outfit-gallery-header-btn-group">
						<a href="' . $this->get_user_gallery_link($_GET['user']) . '" class="' . ($current == 'photos' ? 'active' : '') . '">' . __('Photos', 'xim') . '</a>
						<a href="' . $this->get_user_gallery_link($_GET['user'], 'likes') . '" class="' . ($current == 'likes' ? 'active' : '') . '">' . __('Liked Looks', 'xim') . '</a>
						<a href="' . $this->get_user_gallery_link($_GET['user'], 'follower') . '" class="wc-outfit-num-follower" data-user="' . $_GET['user'] . '">' . $this->get_num_followers($_GET['user']) . __(' Followers', 'xim') . '</a>
						<a href="' . $this->get_user_gallery_link($_GET['user'], 'following') . '" class="wc-outfit-num-following" data-user="' . $_GET['user'] . '">' . $this->get_num_following($_GET['user']) . __(' Following', 'xim') . '</a>
					</div>';
				} else {
					$current = (isset($_GET['page']) ? $_GET['page'] : 'all');

					echo '<h4 class="wc-outfit-gallery-header-subtitle">' . __('Inspire and Admire', 'xim') . '</h4>';

					echo '<div class="wc-outfit-gallery-header-btn-group">
						<a href="' . get_the_permalink(get_option('wc-outfit-page-id')) . '" class="' . ($current == 'all' ? 'active' : '') . '">All</a>
						<a href="' . add_query_arg('page', 'following', get_the_permalink(get_option('wc-outfit-page-id'))) . '" class="' . ($current == 'following' ? 'active' : '') . '">Following</a>
						<a href="' . add_query_arg('page', 'feat', get_the_permalink(get_option('wc-outfit-page-id'))) . '" class="' . ($current == 'feat' ? 'active' : '') . '">Featured</a>
					</div>';
				}
			echo '</div>'; //.wc-outfit-gallery-header

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
							'posts_per_page' => get_option('posts_per_page'),
							'order' => 'desc',
							'post__in' => $ids,
							'paged' => $paged,
						);
					}
				} else {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => get_option('posts_per_page'),
						'order' => 'desc',
						'author' => $_GET['user'],
						'paged' => $paged,
					);
				}
			} elseif (isset($_GET['tag'])) {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => get_option('posts_per_page'),
					'order' => 'desc',
					'tax_query' => array(
						array(
							'taxonomy' => 'outfit_tags',
							'field' => 'slug',
							'terms' => $_GET['tag'],
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
							'posts_per_page' => get_option('posts_per_page'),
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
									'posts_per_page' => get_option('posts_per_page'),
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
						'posts_per_page' => get_option('posts_per_page'),
						'order' => 'desc',
						'paged' => $paged,
					);
				}
			}

			$query = new WP_Query($args);

			if ($query->have_posts()) {
				echo '<div class="row">
					<div class="wc-outfit-gallery-content">';
						while ($query->have_posts()) {
							$query->the_post();

							echo '<div class="wc-outfit-gallery-item col-sm-4" data-id="' . $post->ID . '">
								<div class="wc-outfit-gallery-item-inner-wrap">
									<img src="' . $this->get_outfit_thumbnail($post->ID) . '" class="wc-outfit-gallery-item-thumb" />

									<div class="wc-outfit-gallery-item-footer clearfix">
										<div class="pull-left">
											<a class="wc-outfit-meta-author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
												' . ucwords(get_the_author_meta('display_name')) . '</a>
											<p class="wc-outfit-meta-time">' . $this->outfit_posted_ago() . '</p>
										</div>
										<div class="pull-right">
											' . $this->like_button_html($post->ID) . '
										</div>
									</div>
								</div>
							</div>';
						}
					echo '</div>'; //.wc-outfit-gallery-content

					echo '<div class="wc-outfit-gallery-pagination">
						<button class="button" data-current="1" data-max="'. $query->max_num_pages .'"
							' . (isset($_GET['user']) ? 'data-user="' . $_GET['user'] . '"' : '') . (isset($_GET['page'])? 'data-page="' . $_GET['page'] . '"' : '') . (isset($_GET['tag']) ? 'data-tag="' . $_GET['tag'] . '"' : '') .'>' . __('Load More', 'xim') . '</button>
					</div>';
				echo '</div>'; //.row
			} else {
				if (empty($followings) && @$_GET['page'] == 'following') {
					echo '<div class="wc-outfit-no-content-found">
						<p>' . __('You are not following anyone!', 'xim') . '</p>
						<p>' . __('Let\'s fix that by start following some style gallery stars!', 'xim') . '</p>
					</div>';
				}
			}

			if (!empty($followers)) {
				echo '<div class="row">';
					foreach ($followers as $follower) {
						echo '<div class="col-sm-3">
							<div class="wc-outfit-gallery-user-item">
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
							<div class="wc-outfit-gallery-user-item">
								' . get_avatar($following, '100') . '
								<h4><a href="' . esc_url($this->get_user_gallery_link($following)) . '">' . ucwords(get_the_author_meta('display_name', $following)) . '</a></h4>
							</div>
						</div>';
					}
				echo '</div>';
			}

		echo '</div>'; //.wc-outfit-gallery

		wp_reset_postdata();

		// Outfit Modal
		if (isset($_GET['view'])) {
			$author = $this->get_outfit_author_id($_GET['view']);

			echo '<div class="modal" id="wc-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-body clearfix">
							<div class="wc-outfit-modal-thumb">
								<img src="' . $this->get_outfit_thumbnail($_GET['view']) . '" />
							</div>

							<div class="wc-outfit-modal-details">
								<div class="wc-outfit-modal-author-data clearfix">
									<a class="outfit-author-name" href="' . $this->get_user_gallery_link($author) . '">
										' . ucwords(get_the_author_meta('display_name', $author)) . '
									</a>';

									if ($author != get_current_user_id()) {
										echo '<a href="#" class="wc-outfit-follow-btn" data-id="' . $author . '">' . ($this->is_following($author) ? __('Unfollow', 'xim') : __('Follow', 'xim')) . '</a>';
									}

									echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>

								<div class="wc-outfit-modal-hooked-products owl-carousel">
									' . $this->modal_hooked_products($_GET['view']) . '
								</div>

								' . $this->modal_tags($_GET['view']) . '

								<div class="wc-outfit-modal-footer-info">
									<span class="wc-outfit-meta-time">' . __('Added ', 'xim') . $this->outfit_posted_ago($_GET['view']) . '</span>

									' . $this->like_button_html($_GET['view']) . '
									' . $this->share_buttons_html($_GET['view']) . '
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery(\'#wc-outfit-modal\').modal({
						backdrop: \'static\',
						show: true
					});

					jQuery("#wc-outfit-modal .wc-outfit-modal-hooked-products").owlCarousel({
						items: 2,
						margin: 10,
						nav:true,
						navText: [\'<span class="fa fa-angle-left">\', \'<span class="fa fa-angle-right">\']
					});
				})
			</script>';

		} else {
			echo '<div class="modal" id="wc-outfit-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">

					</div>
				</div>
			</div>';
		}
	}

	function template_single_product_listing() {
		global $post;

		wp_enqueue_style('bootstrap');
		wp_enqueue_style('owlCarousel');
		wp_enqueue_style('outfit-modal');
		wp_enqueue_style('single-product');
		
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('owlCarousel');
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
			echo '<div class="wc-outfit-single-carousel">
				<h2>' . __('Explore Shop & Outfit Photos', 'xim') . '</h2>

				<div class="owl-carousel">';
				while ($query->have_posts()) {
					$query->the_post();
					
					echo '<div class="wc-outfit-gallery-item" data-id="' . $query->post->ID . '">
						<div class="wc-outfit-gallery-item-inner-wrap">
							<img src="' . $this->get_outfit_thumbnail($query->post->ID) . '" class="wc-outfit-gallery-item-thumb" />

							<div class="wc-outfit-gallery-item-footer clearfix">
								<div class="pull-left">
									<a class="wc-outfit-meta-author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
										' . ucwords(get_the_author_meta('display_name')) . '</a>
									<p class="wc-outfit-meta-time">' . $this->outfit_posted_ago() . '</p>
								</div>
								<div class="pull-right">
									' . $this->like_button_html($query->post->ID) . '
								</div>
							</div>
						</div>
					</div>';
				}
				echo '</div>
			</div>';
		}

		wp_reset_postdata();
	}
}