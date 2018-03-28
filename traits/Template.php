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
						<input id="upload-button" type="file" value="' . __('Select Image', 'xim') . '">
						
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
		wp_enqueue_script('bootstrap');

		wp_enqueue_script('arctext');
		wp_enqueue_script('infinite');
		wp_enqueue_script('imgLoaded');
		wp_enqueue_script('isotope');
		wp_enqueue_script('style-gallery');

		//page-header start
		if (isset($_GET['cat'])) {
			$catData = get_term_by('slug', $_GET['cat'], 'outfit_tags');

			echo '<h4 class="page-subtitle">' . ucwords($catData->name) . '</h4>';
			echo '<div class="page-bar">';
			echo '<strong>' . __('BE THE STYLIST: ', 'xim') . '</strong>' . __('Make it work at the office with posh professional styles!', 'xim');
			echo '</div>';
		} elseif (isset($_GET['user'])) {
			$author_data = get_user_meta($_GET['user']);

			echo '<h4 class="page-subtitle">' . $author_data['nickname'][0] . '</h4>';

			echo '<div class="tab-btn">';
			if (isset($_GET['page'])) {
				$current = $_GET['page'];
			} else {
				$current = 'photos';
			}
			echo '<a href="' . $this->get_user_gallery_link($_GET['user']) . '" class="' . ($current == 'photos' ? 'active' : '') . '">Photos</a>';
			echo '<a href="' . $this->get_user_gallery_link($_GET['user'], 'likes') . '" class="' . ($current == 'likes' ? 'active' : '') . '">Liked Looks</a>';
			echo '<a href="#" class="follower" data-user="' . $_GET['user'] . '" data-toggle="modal" data-target="#fanModal">' . $this->get_num_followers($_GET['user']) . ' Followers</a>';
			echo '<a href="#" class="following" data-user="' . $_GET['user'] . '" data-toggle="modal" data-target="#fanModal">' . $this->get_num_following($_GET['user']) . ' Following</a>';
			echo '</div>';
		} else {
			echo '<h4 class="page-subtitle">' . __('Inspire and Admire', 'xim') . '</h4>';

			echo '<div class="page-bar">';
			echo '<strong>' . __('BE THE STYLIST: ', 'xim') . '</strong>' . __('Make it work at the office with posh professional styles!', 'xim');
			echo '</div>';

			echo '<div class="tab-btn">';
			if (isset($_GET['page'])) {
				$current = $_GET['page'];
			} else {
				$current = 'all';
			}

			echo '<div class="filter">';
			echo '<a href="' . home_url('style-gallery') . '" class="' . ($current == 'all' ? 'active' : '') . '">All</a>';
			if (is_user_logged_in()) {
				echo '<a href="' . home_url('style-gallery/?page=following') . '" class="' . ($current == 'following' ? 'active' : '') . '">Following</a>';
			} else {
				echo '<a href="#" data-toggle="modal" data-target="#loginModal">Following</a>';
			}
			echo '<a href="' . home_url('style-gallery/?page=feat') . '" class="' . ($current == 'feat' ? 'active' : '') . '">Featured</a>';
			echo '</div>';
			echo '</div>';
		}

		if (isset($_GET['user'])) {
			if ($_GET['user'] != get_current_user_id()) {
				echo '<a href="#" class="medal medal-big" data-id="' . $_GET['user'] . '">';
				echo '<span><strong>';
				if ($this->is_following($_GET['user'])) {
					echo 'Unfollow';
				} else {
					echo 'Follow';
				}
				echo '</strong></span>';
				echo '</a>';
			} else {
				if (is_user_logged_in()) {
					echo '<a href="' . esc_url(wc_get_endpoint_url('new-outfit', '', wc_get_page_permalink('myaccount'))) . '" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
				} else {
					echo '<a href="#" data-toggle="modal" data-target="#loginModal" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
				}
			}
		} else {
			if (is_user_logged_in()) {
				echo '<a href="' . esc_url(wc_get_endpoint_url('new-outfit', '', wc_get_page_permalink('myaccount'))) . '" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
			} else {
				echo '<a href="#" data-toggle="modal" data-target="#loginModal" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
			}
		}
		//page-header end

		// Content begin
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		if (isset($_GET['user'])) {
			if (@$_GET['page'] == 'likes') {
				$ids = $this->get_liked_outfits($_GET['user']);
				if (!empty($ids)) {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => 9,
						'order' => 'desc',
						'post__in' => $ids,
						'paged' => $paged,
					);
				} else {
					$args = array();
				}

			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'author' => $_GET['user'],
					'paged' => $paged,
				);
			}
		} elseif (isset($_GET['cat'])) {
			$args = array(
				'post_type' => 'outfit',
				'post_status' => 'publish',
				'posts_per_page' => 9,
				'order' => 'desc',
				'tax_query' => array(
					array(
						'taxonomy' => 'outfit_tags',
						'field' => 'slug',
						'terms' => $_GET['cat'],
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
						'posts_per_page' => 9,
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
						$data = get_user_meta(get_current_user_id(), 'following', true);

						if (!empty($data)) {
							$args = array(
								'post_type' => 'outfit',
								'post_status' => 'publish',
								'posts_per_page' => 9,
								'order' => 'desc',
								'author__in' => $data,
								'paged' => $paged,
							);
						} else {
							$args = array();
						}

					} else {
						wp_redirect(home_url('my-account'));
					}
				}
			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'paged' => $paged,
				);
			}
		}

		$query = new WP_Query($args);

		echo '<div class="row">';
		if ($query->have_posts()) {
			echo '<div class="grid">';
			while ($query->have_posts()): $query->the_post();
				$author_data = $this->get_outfit_author_data($post->ID);
				echo '<div class="grid-item col-sm-4" data-id="' . $post->ID . '">
						<div class="gal-header">
							<div class="gal-product clearfix">
								<ul>
									' . $this->hooked_products($post->ID, 4) . '
								</ul>
							</div>
							<a class="gal-thumb clearfix">
								<img src="' . $this->get_outfit_thumbnail($post->ID) . '" class="gal-img" />
							</a>
						</div>
						<div class="gal-footer clearfix">
							<div class="pull-left">
								<a class="author" href="' . $this->get_user_gallery_link(get_the_author_meta('ID')) . '">
									' . $author_data['nickname'][0] . '</a>
								<span class="time">' . $this->outfit_posted_ago() . '</span>
							</div>
							<div class="pull-right">
								<div class="gal-bubble">
									<a href="#" class="bubble-btn"><i class="fa fa-share"></i></a>

									<div class="bubble-content">
										' . $this->share_buttons_html($post->ID) . '
									</div>
								</div>

								' . $this->like_button_html($post->ID) . '
							</div>
						</div>
					</div>';
			endwhile;
			echo '</div>';

			// echo '<div class="more">
			// 	<button class="button" data-current="1" data-max="'. $query->max_num_pages .'"
			// 		' (isset($_GET['order']) ? 'data-order=' . $_GET['order'] : '')
			// 		(isset($_GET['user']) ? 'data-user=' . $_GET['user']: '')
			// 		(isset($_GET['page'])? 'data-page=' . $_GET['page'] : '')
			// 		(isset($_GET['cat']) ? 'data-cat=' . $_GET['cat'] : '') .'>Load More</button>
			// </div>';
		} else {
			if (@$_GET['page'] == 'following') {
				echo '<div class="not-following">
				<div class="col-sm-12">
					<p>' . __('You aren\'t following anyone -- let\'s fix that!') . '</p>
					<p>' . __('Start Following Some Style Gallery Stars') . '</p>
				</div>

				<div class="col-sm-12">
					<div class="row">';
				get_template_part('inc/not-following');
				echo '</div>
				</div>
			</div>';
			}
		}

		wp_reset_postdata();

		echo '</div>';

		// User modal
		if (isset($_GET['user'])) {
			$author_data = get_user_meta($_GET['user']);
			echo '<div class="modal fade" id="fanModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="modalLabel">
								' . $author_data['nickname'][0] . '
							</h4>
						</div>
						<div class="modal-body">
							<ul class="list-group">
							</ul>
						</div>
					</div>
				</div>
			</div>';
		}

		// Product modal
		if (isset($_GET['view'])) {
			$author = $this->get_outfit_author_id($_GET['view']);
			$author_data = get_user_meta($author);

			echo '<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-body clearfix">
						<div class="thumb">
							<img src="' . $this->get_outfit_thumbnail($_GET['view']) . '" />
						</div>

						<div class="details">
							<div class="author clearfix">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>

								<a class="name" href="' . $this->get_user_gallery_link($author) . '">
									' . $author_data['nickname'][0] . '
								</a>';

			if ($author != get_current_user_id()) {
				echo '<a href="#" class="medal" data-id="' . $author . '">';
				if ($this->is_following($author)) {
					echo 'Unfollow';
				} else {
					echo 'Follow';
				}
				echo '</a>';
			}

			echo '</div>


							<div class="products owl-carousel">
								' . $this->modal_hooked_products($_GET['view']) . '
							</div>

							<div class="tags">';

			$tags = wp_get_post_categories($_GET['view']);

			foreach ($tags as $tag) {
				$tag = get_category($tag);
				echo '<a href="' . home_url('style-gallery/?cat=' . strtolower($tag->name)) . '">' . $tag->name . '</a>';
			}

			echo '</div>

							<div class="info">
								<div class="pull-left">
									<span class="time">' . __('Added ', 'xim') . $this->outfit_posted_ago($_GET['view']) . '</span>
								</div>

								<div class="pull-right">
									' . $this->share_buttons_html($_GET['view']) . '
									' . $this->like_button_html($_GET['view']) . '
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(\'#productModal\').modal({
					backdrop: \'static\',
					show: true
				});

				jQuery("#productModal .products").owlCarousel({
					items: 3,
					margin: 10,
					nav:true
				});
			})
		</script>';

		} else {
			echo '<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">

				</div>
			</div>
		</div>';
		}

	}

	function template_single_product_listing() {
		global $post;

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
			echo '<div class="single-product-carousel outfit">
				<h2>' . __('Explore Shop & Outfit Photos', 'xim') . '</h2>

				<div class="owl-carousel">';
			while ($query->have_posts()): $query->the_post();
				echo '<div class="item" data-id="' . get_the_ID() . '"><img src="' . $this->get_outfit_thumbnail(get_the_ID(), 'product-thumb') . '"></div>';
			endwhile;
			echo '</div>
			</div>';
		}

		wp_reset_postdata();
	}
}